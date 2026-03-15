<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/listener-api.php';

$valid_games = [
    'valorant'  => 'Valorant',
    'crossfire' => 'CrossFire',
    'dota2'     => 'Dota 2',
];

$ref = trim($_GET['ref'] ?? '');
$type = $_GET['type'] ?? 'team';

if (empty($ref)) {
    header('Location: ' . base_url());
    exit;
}

// Look up registration
$registration = null;
$game_name = '';
$amount = 0;
$description = '';

if ($type === 'solo') {
    $stmt = $pdo->prepare("SELECT * FROM solo_players WHERE ref_code = ?");
    $stmt->execute([$ref]);
    $registration = $stmt->fetch();
    if ($registration) {
        $game_name = $valid_games[$registration['game']] ?? $registration['game'];
        $amount = 100.00;
        $description = "Solo: {$registration['player_name']} - $game_name";
    }
} else {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE ref_code = ?");
    $stmt->execute([$ref]);
    $registration = $stmt->fetch();
    if ($registration) {
        $game_name = $valid_games[$registration['game']] ?? $registration['game'];
        $amount = 500.00;
        $description = "Team: {$registration['team_name']} - $game_name";
    }
}

if (!$registration) {
    flash('error', 'Registration not found.');
    header('Location: ' . base_url());
    exit;
}

// Already approved? Go to success page
if ($registration['status'] === 'approved') {
    $_SESSION['ref_code'] = $ref;
    flash('success', 'Your payment has already been confirmed!');
    header('Location: ' . base_url("success.php?type=$type&game={$registration['game']}"));
    exit;
}

// ── AJAX: Check payment status ──
if (isset($_GET['action']) && $_GET['action'] === 'check') {
    header('Content-Type: application/json');

    $result = listenerCheckPayment($ref);

    if ($result && !empty($result['paid'])) {
        // Auto-approve the registration
        if ($type === 'solo') {
            $pdo->prepare("UPDATE solo_players SET status = 'approved', payment_proof = 'GCASH-AUTO' WHERE ref_code = ? AND status = 'pending'")
                ->execute([$ref]);
        } else {
            $pdo->prepare("UPDATE teams SET status = 'approved', payment_proof = 'GCASH-AUTO' WHERE ref_code = ? AND status = 'pending'")
                ->execute([$ref]);
        }
        echo json_encode(['paid' => true, 'sender' => $result['sender'] ?? '', 'phone' => $result['phone'] ?? '']);
    } else {
        echo json_encode(['paid' => false]);
    }
    exit;
}

// ── AJAX: Upload proof fallback ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_proof'])) {
    $upload_path = '';
    $payment_note = trim($_POST['payment_note'] ?? '');
    $has_file = isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK;

    if (!$has_file && $payment_note === '') {
        flash('error', 'Please upload a file or provide a note.');
        header("Location: " . base_url("ticket.php?ref=$ref&type=$type"));
        exit;
    }

    if ($has_file) {
        $file = $_FILES['payment_proof'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!in_array($file['type'], $allowed) || $file['size'] > 5 * 1024 * 1024) {
            flash('error', 'Invalid file. Use JPG/PNG/WebP/PDF under 5MB.');
            header("Location: " . base_url("ticket.php?ref=$ref&type=$type"));
            exit;
        }
        $upload_dir = __DIR__ . '/uploads/payment_proofs';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = strtolower($ref) . '_' . time() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], "$upload_dir/$filename");
        $upload_path = "uploads/payment_proofs/$filename";
    } else {
        $upload_path = "NOTE: $payment_note";
    }

    if ($type === 'solo') {
        $pdo->prepare("UPDATE solo_players SET payment_proof = ? WHERE ref_code = ?")->execute([$upload_path, $ref]);
    } else {
        $pdo->prepare("UPDATE teams SET payment_proof = ? WHERE ref_code = ?")->execute([$upload_path, $ref]);
    }

    $_SESSION['ref_code'] = $ref;
    flash('success', "Proof submitted! We'll review and confirm shortly.");
    header("Location: " . base_url("success.php?type=$type&game={$registration['game']}"));
    exit;
}

// ── Create order on Listener API (if not already created) ──
$orderResult = listenerCreateOrder($ref, $amount, $description);
$orderActive = false;
$slotBusy = false;
$retryAfter = 0;

if ($orderResult) {
    if (!empty($orderResult['success'])) {
        $orderActive = true;
    } elseif (($orderResult['error'] ?? '') === 'slot_busy') {
        $slotBusy = true;
        $retryAfter = $orderResult['retry_after'] ?? 60;
    } elseif (strpos($orderResult['error'] ?? '', 'already exists') !== false) {
        // Order was created on a previous page load — check if it's still pending
        $existing = listenerGetOrder($ref);
        if ($existing && !empty($existing['order'])) {
            $status = $existing['order']['status'] ?? '';
            if ($status === 'pending') {
                $orderActive = true;
            } elseif ($status === 'paid') {
                // Already paid! Auto-approve and redirect
                if ($type === 'solo') {
                    $pdo->prepare("UPDATE solo_players SET status = 'approved', payment_proof = 'GCASH-AUTO' WHERE ref_code = ? AND status = 'pending'")
                        ->execute([$ref]);
                } else {
                    $pdo->prepare("UPDATE teams SET status = 'approved', payment_proof = 'GCASH-AUTO' WHERE ref_code = ? AND status = 'pending'")
                        ->execute([$ref]);
                }
                $_SESSION['ref_code'] = $ref;
                flash('success', 'Payment confirmed!');
                header("Location: " . base_url("success.php?type=$type&game={$registration['game']}"));
                exit;
            }
        }
    }
}

$pageTitle = "Payment — $game_name";
$pageDescription = "Complete your payment for $game_name tournament registration.";
$flash = get_flash();

require_once __DIR__ . '/includes/header.php';
?>

<div class="ticket-container">
    <a href="<?= base_url() ?>" class="back-link">
        <i class="bi bi-arrow-left"></i> Back to games
    </a>

    <div class="ticket-card">
        <div class="ticket-header">
            <div class="ticket-ref"><?= htmlspecialchars($ref) ?></div>
            <h2><?= htmlspecialchars($game_name) ?></h2>
            <p class="subtitle">
                <?php if ($type === 'solo'): ?>
                    Solo Entry — <?= htmlspecialchars($registration['player_name']) ?>
                <?php else: ?>
                    Team — <?= htmlspecialchars($registration['team_name']) ?>
                <?php endif; ?>
            </p>
        </div>

        <?php if ($flash): ?>
            <div class="alert-custom alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?>">
                <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle' : 'check-circle' ?>"></i>
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <?php if ($slotBusy): ?>
            <!-- Slot busy — another payment in progress for same amount -->
            <div class="ticket-status ticket-waiting" id="slotBusy">
                <div class="ticket-status-icon"><i class="bi bi-hourglass-split"></i></div>
                <h3>Another player is paying this amount</h3>
                <p>Please wait a moment. The slot will open shortly.</p>
                <div class="ticket-countdown" id="slotTimer">
                    Retrying in <strong id="slotSeconds"><?= $retryAfter ?></strong>s...
                </div>
                <div style="margin-top:1.5rem;">
                    <p style="color:var(--text-muted); font-size:0.85rem;">Or use the options below while waiting.</p>
                </div>
            </div>
        <?php elseif ($orderActive): ?>
            <!-- Order active — waiting for GCash payment -->
            <div class="ticket-status ticket-waiting" id="paymentWaiting">
                <div class="ticket-pulse-ring"></div>
                <div class="ticket-status-icon"><i class="bi bi-phone"></i></div>
                <h3>Send Payment via GCash</h3>

                <div class="ticket-amount">
                    &#8369;<?= number_format($amount, 2) ?>
                </div>

                <div class="ticket-gcash-number">
                    <i class="bi bi-phone-fill"></i>
                    <span>0927 872 8916</span>
                </div>

                <div class="ticket-qr" style="margin:1rem auto; text-align:center;">
                    <div id="gcashQR" style="display:inline-block; background:#fff; padding:12px; border-radius:12px;"></div>
                    <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.5rem;">Scan with GCash app</div>
                </div>

                <div class="ticket-instructions">
                    <p>Open your GCash app and send <strong>exactly &#8369;<?= number_format($amount, 2) ?></strong> to the number above.</p>
                    <p>Payment will be <strong>automatically detected</strong> — this page updates in real time.</p>
                </div>

                <div class="ticket-detecting">
                    <div class="ticket-spinner"></div>
                    <span>Waiting for payment...</span>
                </div>

                <div class="ticket-timer">
                    <i class="bi bi-clock"></i>
                    <span id="orderTimer">5:00</span> remaining
                </div>
            </div>

            <!-- Success state (hidden, shown by JS) -->
            <div class="ticket-status ticket-success" id="paymentSuccess" style="display:none;">
                <div class="ticket-status-icon ticket-check"><i class="bi bi-check-circle-fill"></i></div>
                <h3>Payment Confirmed!</h3>
                <p id="paymentSender"></p>
                <p>Redirecting...</p>
            </div>
        <?php else: ?>
            <!-- API unavailable or order expired -->
            <div class="ticket-status ticket-waiting">
                <div class="ticket-status-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <h3>Auto-detection unavailable</h3>
                <p>Please upload payment proof or pay on-site instead.</p>
            </div>
        <?php endif; ?>

        <!-- Fallback options -->
        <div class="ticket-fallback">
            <div class="ticket-fallback-title">Other Payment Options</div>

            <div class="ticket-option">
                <div class="ticket-option-header" onclick="toggleProofUpload()">
                    <i class="bi bi-upload"></i> Upload Payment Proof
                    <i class="bi bi-chevron-down" id="proofChevron"></i>
                </div>
                <div class="ticket-option-body" id="proofUploadForm" style="display:none;">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="upload_proof" value="1">
                        <div class="mb-3">
                            <input type="file" name="payment_proof" class="form-control" accept="image/*,.pdf">
                            <div class="form-text" style="font-size:0.8rem; margin-top:0.4rem; color: var(--text-muted);">
                                Screenshot of your GCash transfer. JPG, PNG, WebP, or PDF. Max 5MB.
                            </div>
                        </div>
                        <div class="mb-3">
                            <textarea name="payment_note" class="form-control" rows="2" placeholder="Or explain: e.g. Will send proof later, paid via someone else..."></textarea>
                        </div>
                        <button type="submit" class="btn-submit" style="font-size:0.9rem; padding:0.65rem 1.5rem;">
                            <i class="bi bi-check-circle"></i> Submit Proof
                        </button>
                    </form>
                </div>
            </div>

            <div class="ticket-option">
                <div class="ticket-option-header">
                    <i class="bi bi-shop"></i> Pay On-Site
                </div>
                <div class="ticket-option-body" style="display:block; padding:0.75rem 1rem;">
                    <p style="margin:0; color:var(--text-muted); font-size:0.85rem;">
                        Pay <strong>&#8369;<?= number_format($amount, 2) ?></strong> in person at
                        <strong>Hide Out Cybernet Cafe</strong> before the tournament.
                        Your registration is saved — just give your code: <strong><?= htmlspecialchars($ref) ?></strong>
                    </p>
                </div>
            </div>

            <div style="text-align:center; margin-top:1rem;">
                <a href="<?= base_url("success.php?type=$type&game={$registration['game']}") ?>"
                   style="color:var(--text-muted); font-size:0.85rem; text-decoration:underline;">
                    Skip — I'll pay later
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var orderActive = <?= $orderActive ? 'true' : 'false' ?>;
    var slotBusy = <?= $slotBusy ? 'true' : 'false' ?>;
    var ref = <?= json_encode($ref) ?>;
    var type = <?= json_encode($type) ?>;
    var game = <?= json_encode($registration['game']) ?>;
    var baseUrl = <?= json_encode(base_url()) ?>;
    var checkUrl = <?= json_encode(base_url("ticket.php?ref=$ref&type=$type&action=check")) ?>;
    var successUrl = <?= json_encode(base_url("success.php?type=$type&game={$registration['game']}")) ?>;
    var pollInterval;

    // ── Payment polling ──
    if (orderActive) {
        // Start 5-minute countdown
        var timeLeft = 300;
        var timerEl = document.getElementById('orderTimer');
        var countdownInterval = setInterval(function() {
            timeLeft--;
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                clearInterval(pollInterval);
                if (timerEl) timerEl.textContent = '0:00';
                location.reload();
                return;
            }
            var m = Math.floor(timeLeft / 60);
            var s = timeLeft % 60;
            if (timerEl) timerEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        }, 1000);

        // Poll every 3 seconds
        pollInterval = setInterval(function() {
            fetch(checkUrl)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.paid) {
                        clearInterval(pollInterval);
                        clearInterval(countdownInterval);
                        showSuccess(data.sender, data.phone);
                    }
                })
                .catch(function() {});
        }, 3000);
    }

    // ── Slot busy auto-retry ──
    if (slotBusy) {
        var slotLeft = <?= $retryAfter ?>;
        var slotEl = document.getElementById('slotSeconds');
        var slotInterval = setInterval(function() {
            slotLeft--;
            if (slotLeft <= 0) {
                clearInterval(slotInterval);
                location.reload();
                return;
            }
            if (slotEl) slotEl.textContent = slotLeft;
        }, 1000);
    }

    function showSuccess(sender, phone) {
        var waiting = document.getElementById('paymentWaiting');
        var success = document.getElementById('paymentSuccess');
        var senderEl = document.getElementById('paymentSender');

        if (waiting) waiting.style.display = 'none';
        if (success) success.style.display = 'block';
        if (senderEl && sender) {
            senderEl.textContent = 'From ' + sender + (phone ? ' ' + phone : '');
        }

        setTimeout(function() {
            window.location.href = successUrl;
        }, 2500);
    }

    // ── Proof upload toggle ──
    window.toggleProofUpload = function() {
        var form = document.getElementById('proofUploadForm');
        var chevron = document.getElementById('proofChevron');
        if (form.style.display === 'none') {
            form.style.display = 'block';
            chevron.classList.replace('bi-chevron-down', 'bi-chevron-up');
        } else {
            form.style.display = 'none';
            chevron.classList.replace('bi-chevron-up', 'bi-chevron-down');
        }
    };
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
(function() {
    var el = document.getElementById('gcashQR');
    if (!el) return;
    var qr = qrcode(0, 'M');
    qr.addData('09278728916');
    qr.make();
    el.innerHTML = qr.createSvgTag(5, 0);
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

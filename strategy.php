<?php
session_start();

// Password protection
$STRATEGY_PASSWORD = 'argonar2026';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $STRATEGY_PASSWORD) {
        $_SESSION['strategy_auth'] = true;
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $error = 'Incorrect password.';
}

if (empty($_SESSION['strategy_auth'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strategy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#0f172a;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:Inter,sans-serif;}</style>
</head>
<body>
    <div class="card" style="max-width:380px;width:100%;">
        <div class="card-body p-4 text-center">
            <h5 class="fw-bold mb-3">Enter Password</h5>
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="password" name="password" class="form-control mb-3" placeholder="Password" autofocus required>
                <button type="submit" class="btn btn-dark w-100">Access</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php exit; endif; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Argonar Marketing Strategy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --border: #334155; --accent: #3b82f6; --text: #e2e8f0; --muted: #94a3b8; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; line-height: 1.7; }
        .container { max-width: 860px; margin: 0 auto; padding: 2rem 1.5rem; }
        h1 { font-size: 2rem; font-weight: 800; margin-bottom: .5rem; }
        h2 { font-size: 1.4rem; font-weight: 700; color: var(--accent); margin-top: 2.5rem; margin-bottom: 1rem; padding-bottom: .5rem; border-bottom: 1px solid var(--border); }
        h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: .5rem; }
        .subtitle { color: var(--muted); font-size: .95rem; margin-bottom: 2rem; }
        .card-dark { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; }
        .step-num { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: var(--accent); color: #fff; font-weight: 700; font-size: .85rem; border-radius: 50%; margin-right: .75rem; flex-shrink: 0; }
        .step { display: flex; align-items: flex-start; margin-bottom: 1.25rem; }
        .step-content { flex: 1; }
        .step-content strong { display: block; margin-bottom: .25rem; }
        .step-content p, .step-content ul { color: var(--muted); font-size: .9rem; margin-bottom: 0; }
        .step-content ul { padding-left: 1.25rem; margin-top: .25rem; }
        .step-content ul li { margin-bottom: .25rem; }
        .badge-pill { background: rgba(59,130,246,0.15); color: var(--accent); font-size: .8rem; padding: .25rem .75rem; border-radius: 20px; font-weight: 600; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin: 1rem 0; }
        .stat-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; text-align: center; }
        .stat-card .num { font-size: 1.5rem; font-weight: 800; color: var(--accent); }
        .stat-card .label { font-size: .8rem; color: var(--muted); margin-top: .25rem; }
        .post-template { background: #0f172a; border: 1px solid var(--border); border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem; font-size: .9rem; }
        .post-template .tag { color: var(--accent); font-weight: 700; font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; margin-bottom: .5rem; }
        .post-template .caption { white-space: pre-wrap; color: var(--muted); }
        .checklist { list-style: none; padding: 0; }
        .checklist li { padding: .5rem 0; border-bottom: 1px solid var(--border); color: var(--muted); font-size: .9rem; }
        .checklist li:last-child { border-bottom: none; }
        .checklist li::before { content: '☐'; margin-right: .75rem; color: var(--accent); }
        .boost-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        .boost-table th { color: var(--accent); font-weight: 600; text-align: left; padding: .6rem; border-bottom: 2px solid var(--border); font-size: .8rem; text-transform: uppercase; letter-spacing: .5px; }
        .boost-table td { padding: .6rem; border-bottom: 1px solid var(--border); color: var(--muted); }
        .boost-table tr:last-child td { border-bottom: none; }
        .highlight { background: rgba(59,130,246,0.1); border-left: 3px solid var(--accent); padding: .75rem 1rem; border-radius: 0 8px 8px 0; margin: 1rem 0; font-size: .9rem; color: var(--muted); }
        .highlight strong { color: var(--text); }
        code { background: rgba(59,130,246,0.15); color: var(--accent); padding: .1rem .4rem; border-radius: 4px; font-size: .85rem; }
        a { color: var(--accent); }
        .toc { margin-bottom: 2rem; }
        .toc a { color: var(--muted); text-decoration: none; display: block; padding: .3rem 0; font-size: .9rem; }
        .toc a:hover { color: var(--accent); }
        @media (max-width: 576px) { h1 { font-size: 1.5rem; } h2 { font-size: 1.2rem; } .stat-grid { grid-template-columns: 1fr 1fr; } }
    </style>
</head>
<body>
<div class="container">

<h1>Argonar Marketing Strategy</h1>
<p class="subtitle">3 simple steps: Post, Boost, Done.</p>

<div class="card-dark" style="padding:1.5rem;">
    <h3 style="margin-top:0;color:var(--accent);font-size:1rem;"><i class="fas fa-download me-2"></i>Download All Assets</h3>
    <div style="display:flex;gap:1.5rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.25rem;">
        <div style="text-align:center;">
            <img src="images/fb/profile_512.png" alt="Profile" style="width:100px;height:100px;border-radius:12px;border:2px solid var(--border);">
            <div style="font-size:.7rem;color:var(--muted);margin-top:.4rem;">Profile (512x512)</div>
        </div>
        <div style="text-align:center;">
            <img src="images/fb/cover_1640x856.png" alt="Cover" style="width:240px;height:auto;border-radius:8px;border:2px solid var(--border);">
            <div style="font-size:.7rem;color:var(--muted);margin-top:.4rem;">Cover (1640x856)</div>
        </div>
        <div style="text-align:center;">
            <img src="images/fb/post_tools_1080.png" alt="Post" style="width:100px;height:100px;border-radius:8px;border:2px solid var(--border);object-fit:cover;">
            <div style="font-size:.7rem;color:var(--muted);margin-top:.4rem;">Post (1080x1080)</div>
        </div>
    </div>
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <a href="images/fb/profile_512.png" download="argonar_profile.png" style="background:var(--accent);padding:.45rem 1rem;border-radius:8px;color:#fff;text-decoration:none;font-size:.8rem;font-weight:600;"><i class="fas fa-download me-1"></i> Profile</a>
        <a href="images/fb/cover_1640x856.png" download="argonar_cover.png" style="background:var(--accent);padding:.45rem 1rem;border-radius:8px;color:#fff;text-decoration:none;font-size:.8rem;font-weight:600;"><i class="fas fa-download me-1"></i> Cover</a>
        <a href="images/fb/post_tools_1080.png" download="argonar_post.png" style="background:var(--accent);padding:.45rem 1rem;border-radius:8px;color:#fff;text-decoration:none;font-size:.8rem;font-weight:600;"><i class="fas fa-download me-1"></i> Post</a>
    </div>
</div>

<!-- ============================================== -->
<h2 id="post"><i class="fas fa-pen-fancy me-2"></i>Step 1 — Post This on Your Page</h2>

<div class="highlight">
    Post at <strong>12 PM – 1 PM</strong> or <strong>7 PM – 9 PM</strong> (when Filipino professionals are online). Tuesday–Thursday works best.
</div>

<div class="post-template">
    <div class="caption">All construction tools. One subscription. 💪

🔹 BOQ Generator - auto-compute quantities, amounts, markup & VAT. Export to Excel.
🔹 Rebar Cutting List - optimize bar cuts, minimize waste, track stock lengths.
🔹 Structural Estimate - concrete, steel & formwork cost breakdown with contingency.
🔹 Architectural Estimate - masonry, tiling, painting, roofing, plastering, ceiling & doors/windows.
🔹 Document Generator - scope of work, material requisition, progress reports & change orders.
🔹 Excel Templates - ready-made BOQ, cost estimate, schedule, daily report & requisition sheets.

All tools export to Excel. Access from any device — phone, tablet, or laptop.

Start with a ₱20 daily pass or go unlimited with ₱500/month.

Sign up → argonar.co

#ConstructionTools #AffordableSoftware #ConstructionPH #CivilEngineering #EngineerLife</div>
</div>
<p style="color:var(--muted);font-size:.85rem;">Copy the text above, attach the <strong>Post Image</strong>, and publish.</p>

<!-- ============================================== -->
<h2 id="boost"><i class="fas fa-rocket me-2"></i>Step 2 — Boost the Post</h2>

<div class="card-dark">
    <div class="step">
        <span class="step-num">1</span>
        <div class="step-content">
            <strong>Click "Boost Post" on the post you just published</strong>
            <p>Choose goal: <code>Get more website visitors</code> → URL: <code>https://argonar.co/login.php</code></p>
        </div>
    </div>
    <div class="step">
        <span class="step-num">2</span>
        <div class="step-content">
            <strong>Set the Audience (use these exact settings)</strong>
        </div>
    </div>
</div>

<h3>Targeting Settings (Copy Exactly)</h3>
<div class="card-dark">
    <div style="margin-bottom:1.5rem;">
        <strong style="color:var(--accent);font-size:.85rem;text-transform:uppercase;letter-spacing:.5px;">Location</strong>
        <div style="background:#0f172a;border:1px solid var(--border);border-radius:8px;padding:1rem;margin-top:.5rem;">
            <p style="color:var(--muted);font-size:.9rem;margin:0;">Philippines</p>
            <p style="color:var(--muted);font-size:.8rem;margin:.25rem 0 0;">Or narrow to: Metro Manila, Cebu, Davao, Pampanga, Cavite, Laguna, Bulacan</p>
        </div>
    </div>

    <div style="margin-bottom:1.5rem;">
        <strong style="color:var(--accent);font-size:.85rem;text-transform:uppercase;letter-spacing:.5px;">Age & Gender</strong>
        <div style="background:#0f172a;border:1px solid var(--border);border-radius:8px;padding:1rem;margin-top:.5rem;">
            <p style="color:var(--muted);font-size:.9rem;margin:0;"><strong style="color:var(--text);">Age:</strong> 22 – 45</p>
            <p style="color:var(--muted);font-size:.9rem;margin:.25rem 0 0;"><strong style="color:var(--text);">Gender:</strong> All</p>
        </div>
    </div>

    <div style="margin-bottom:1.5rem;">
        <strong style="color:var(--accent);font-size:.85rem;text-transform:uppercase;letter-spacing:.5px;">Interests (type these in the search box)</strong>
        <div style="background:#0f172a;border:1px solid var(--border);border-radius:8px;padding:1rem;margin-top:.5rem;">
            <p style="color:var(--muted);font-size:.8rem;margin:0 0 .75rem;">In the Detailed Targeting field, type each one and select it from the dropdown. Add as many as you can find.</p>
            <p style="color:var(--text);font-size:.85rem;margin:0 0 .5rem;font-weight:600;">Industry & Profession:</p>
            <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                <code>Construction</code>
                <code>Civil engineering</code>
                <code>Engineering</code>
                <code>Architecture</code>
                <code>Construction engineering</code>
                <code>Project management</code>
            </div>
            <p style="color:var(--text);font-size:.85rem;margin:1rem 0 .5rem;font-weight:600;">Software:</p>
            <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                <code>AutoCAD</code>
                <code>Microsoft Excel</code>
            </div>
            <p style="color:var(--muted);font-size:.8rem;margin:1rem 0 0;">Note: Meta consolidated some niche interests in 2025. If an interest doesn't appear when you search, skip it. The ones above are confirmed available as of March 2026.</p>
        </div>
    </div>

    <div>
        <strong style="color:var(--accent);font-size:.85rem;text-transform:uppercase;letter-spacing:.5px;">Advantage+ Detailed Targeting</strong>
        <div style="background:#0f172a;border:1px solid var(--border);border-radius:8px;padding:1rem;margin-top:.5rem;">
            <p style="color:var(--muted);font-size:.85rem;margin:0;">Leave <strong style="color:var(--text);">Advantage+ Detailed Targeting</strong> turned ON. This lets Meta automatically expand your reach to people likely to convert, even beyond the interests you selected.</p>
        </div>
    </div>
</div>

<div class="highlight">
    <strong>Estimated audience size:</strong> ~2-5 million people. Good range — not too broad, not too narrow.
</div>

<!-- ============================================== -->
<h2 id="budget"><i class="fas fa-chart-line me-2"></i>Step 3 — Budget</h2>

<div class="card-dark">
    <div class="step">
        <span class="step-num">3</span>
        <div class="step-content">
            <strong>Set Budget: ₱200/day for 7 days</strong>
            <p>Total: ₱1,400 for the first boost. This gives enough data to see results.</p>
        </div>
    </div>
    <div class="step">
        <span class="step-num">4</span>
        <div class="step-content">
            <strong>Click Boost — you're done!</strong>
        </div>
    </div>
</div>

<h3>What to Expect</h3>
<div class="stat-grid">
    <div class="stat-card">
        <div class="num">₱9</div>
        <div class="label">Avg Cost Per Click</div>
    </div>
    <div class="stat-card">
        <div class="num">~670</div>
        <div class="label">Clicks / Month at ₱200/day</div>
    </div>
    <div class="stat-card">
        <div class="num">30-60</div>
        <div class="label">Est. Signups / Month</div>
    </div>
</div>

<div class="highlight">
    <strong>Philippines is one of the cheapest markets for Facebook Ads.</strong> Avg CPC is ₱9 vs ₱63 global — your peso goes 5-6x further.
</div>

<hr style="border-color:var(--border);margin:3rem 0 1.5rem;">
<p style="color:var(--muted);font-size:.8rem;text-align:center;">
    Based on PH Facebook Ads benchmarks (CPM ₱75, CPC ₱9). Actual results vary by creative quality and targeting.
    <br>Last updated: March 2026
</p>

</div>
</body>
</html>

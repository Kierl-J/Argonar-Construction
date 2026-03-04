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
<p class="subtitle">Facebook Page Setup, Content, & Paid Ads Playbook for the Philippines Construction Market</p>

<div class="card-dark toc">
    <strong style="font-size:.85rem;color:var(--accent);text-transform:uppercase;letter-spacing:.5px;">Contents</strong>
    <a href="#phase1">Phase 1 — Create the Facebook Page</a>
    <a href="#phase2">Phase 2 — Content Strategy & Post Templates</a>
    <a href="#phase3">Phase 3 — Boosting Posts & Ads</a>
    <a href="#phase4">Phase 4 — Budget & Reach Estimates</a>
    <a href="#phase5">Phase 5 — Growth Playbook</a>
</div>

<!-- ============================================== -->
<h2 id="phase1"><i class="fas fa-flag me-2"></i>Phase 1 — Create the Facebook Page</h2>

<div class="card-dark">
    <div class="step">
        <span class="step-num">1</span>
        <div class="step-content">
            <strong>Create Page</strong>
            <p>Go to <a href="https://www.facebook.com/pages/create" target="_blank">facebook.com/pages/create</a>. Pick <code>Business or Brand</code>.</p>
        </div>
    </div>
    <div class="step">
        <span class="step-num">2</span>
        <div class="step-content">
            <strong>Page Name & Category</strong>
            <ul>
                <li><strong>Name:</strong> <code>Argonar Construction</code></li>
                <li><strong>Category:</strong> Search "Software" → select <code>Software Company</code>. Also add <code>Construction Company</code> as secondary.</li>
                <li><strong>Bio:</strong> <em>"Construction tools for Filipino engineers & contractors. BOQ, estimates, cutting lists — all in one app."</em></li>
            </ul>
        </div>
    </div>
    <div class="step">
        <span class="step-num">3</span>
        <div class="step-content">
            <strong>Profile Photo & Cover</strong>
            <ul>
                <li><strong>Profile:</strong> Use the Argonar logo (1:1, 512x512px minimum)</li>
                <li><strong>Cover:</strong> Create a 1640x856px banner showing the app dashboard or a hero shot: laptop with BOQ Generator open, overlaid text: <em>"Construction Tools Made Easy — argonar.co"</em></li>
                <li>Change the cover photo quarterly for a promotional bump</li>
            </ul>
            <div style="display:flex;gap:.75rem;margin-top:1rem;flex-wrap:wrap;">
                <a href="images/fb/profile_512.png" download="argonar_profile_512.png" class="btn btn-sm btn-primary" style="background:var(--accent);border:none;padding:.5rem 1rem;border-radius:8px;color:#fff;text-decoration:none;font-size:.85rem;font-weight:600;">
                    <i class="fas fa-download me-1"></i> Download Profile Photo (512x512)
                </a>
                <a href="images/fb/cover_1640x856.png" download="argonar_cover_1640x856.png" class="btn btn-sm btn-primary" style="background:var(--accent);border:none;padding:.5rem 1rem;border-radius:8px;color:#fff;text-decoration:none;font-size:.85rem;font-weight:600;">
                    <i class="fas fa-download me-1"></i> Download Cover Photo (1640x856)
                </a>
            </div>
            <div style="display:flex;gap:1.5rem;margin-top:1.25rem;flex-wrap:wrap;">
                <div style="text-align:center;">
                    <img src="images/fb/profile_512.png" alt="Profile" style="width:120px;height:120px;border-radius:12px;border:2px solid var(--border);">
                    <div style="font-size:.75rem;color:var(--muted);margin-top:.4rem;">Profile Photo</div>
                </div>
                <div style="text-align:center;">
                    <img src="images/fb/cover_1640x856.png" alt="Cover" style="width:320px;height:auto;border-radius:8px;border:2px solid var(--border);">
                    <div style="font-size:.75rem;color:var(--muted);margin-top:.4rem;">Cover Photo</div>
                </div>
            </div>
        </div>
    </div>
    <div class="step">
        <span class="step-num">4</span>
        <div class="step-content">
            <strong>Fill Out Page Info</strong>
            <ul>
                <li><strong>Website:</strong> <code>https://argonar.co</code></li>
                <li><strong>Phone / Email:</strong> Add business contact</li>
                <li><strong>Hours:</strong> Set to "Always Open" (it's a web app)</li>
                <li><strong>CTA Button:</strong> Click "Add Button" → <code>Sign Up</code> → link to <code>https://argonar.co/login.php</code></li>
                <li><strong>Username:</strong> Set to <code>@argonarconstruction</code> (creates facebook.com/argonarconstruction)</li>
            </ul>
        </div>
    </div>
    <div class="step">
        <span class="step-num">5</span>
        <div class="step-content">
            <strong>Final Touches</strong>
            <ul>
                <li>Invite friends & existing users to Like the page</li>
                <li>Enable Messenger for customer inquiries</li>
                <li>Post at least 3 pieces of content before boosting (avoids "empty page" impression)</li>
            </ul>
        </div>
    </div>
</div>

<!-- ============================================== -->
<h2 id="phase2"><i class="fas fa-pen-fancy me-2"></i>Phase 2 — Content Strategy & Post Templates</h2>

<h3>Posting Schedule</h3>
<div class="highlight">
    Post <strong>3-4 times per week</strong>. Best times for Filipino professionals: <strong>12 PM – 1 PM</strong> (lunch break) and <strong>7 PM – 9 PM</strong> (after work). Tuesday, Wednesday, and Thursday perform best.
</div>

<h3>Page Post Image (1080x1080)</h3>
<div class="card-dark">
    <div style="text-align:center;margin-bottom:1rem;">
        <img src="images/fb/post_tools_1080.png" alt="All Tools Post" style="width:300px;height:300px;border-radius:8px;border:2px solid var(--border);object-fit:cover;">
    </div>
    <div style="text-align:center;">
        <a href="images/fb/post_tools_1080.png" download="argonar_post.png" style="background:var(--accent);border:none;padding:.6rem 1.5rem;border-radius:8px;color:#fff;text-decoration:none;font-size:.9rem;font-weight:600;display:inline-block;">
            <i class="fas fa-download me-1"></i> Download Post Image
        </a>
    </div>
</div>

<h3>Content Mix (Weekly)</h3>
<div class="stat-grid">
    <div class="stat-card">
        <div class="num">2x</div>
        <div class="label">Feature Demos / How-To</div>
    </div>
    <div class="stat-card">
        <div class="num">1x</div>
        <div class="label">Social Proof / Testimonial</div>
    </div>
    <div class="stat-card">
        <div class="num">1x</div>
        <div class="label">Industry Tip / Engagement</div>
    </div>
</div>

<h3>Ready-to-Use Post Templates</h3>

<div class="post-template">
    <div class="tag">Feature Demo — BOQ Generator</div>
    <div class="caption">Tired of manual BOQ computations in Excel? 😤

Argonar's BOQ Generator auto-calculates everything — quantities, amounts, markups, VAT — and exports to Excel in one click.

✅ Auto-compute amounts
✅ Markup & VAT built in
✅ Export to Excel instantly
✅ Access anywhere on any device

Try it now 👉 argonar.co

#Construction #BOQ #CivilEngineering #Philippines #ConstructionTools #EngineerLife</div>
</div>

<div class="post-template">
    <div class="tag">Feature Demo — Structural Estimate</div>
    <div class="caption">Engineers, how long does your structural cost estimate take? ⏱️

With Argonar, just input your quantities — concrete, steel, formwork — and get an instant cost breakdown with contingency.

🏗️ 3 categories: Concrete, Steel, Formwork
📊 Auto-contingency calculation
📥 Export to Excel

Start estimating smarter → argonar.co

#StructuralEngineering #CostEstimate #ConstructionPH #Argonar</div>
</div>

<div class="post-template">
    <div class="tag">Feature Demo — Architectural Estimate</div>
    <div class="caption">Architectural finishes estimate on one page? Yes, please! 🏠

Masonry, tiling, painting, roofing, plastering, ceiling, doors & windows — all categories in a single estimate with auto-totals.

Try the Architectural Estimate tool → argonar.co

#Architecture #CostEstimate #ConstructionPH #BuildingDesign</div>
</div>

<div class="post-template">
    <div class="tag">Feature Demo — Document Generator</div>
    <div class="caption">Stop making documents from scratch every time. 📄

Argonar's Document Generator creates:
📋 Scope of Work
📦 Material Requisition
📊 Progress Report
🔄 Change Order

Fill in, export to Excel. Done.

argonar.co

#ConstructionDocuments #ProjectManagement #ConstructionPH</div>
</div>

<div class="post-template">
    <div class="tag">Social Proof / Testimonial</div>
    <div class="caption">Here's what Engr. [Name] from [City] said after using Argonar for their project:

"[Insert testimonial — e.g. 'Naka-save talaga ng time. Dati 2-3 hours yung BOQ, ngayon 15 minutes na lang.']"

Join 100+ engineers using Argonar → argonar.co

#ConstructionPH #EngineerLife #Testimonial</div>
</div>

<div class="post-template">
    <div class="tag">Industry Tip / Engagement</div>
    <div class="caption">Quick tip for new engineers on site 👷

Always include a 10% contingency in your cost estimates. Unexpected changes ALWAYS happen — weather delays, material price increases, design revisions.

Argonar's estimating tools have built-in contingency so you never forget.

What's the biggest unexpected cost you've encountered on a project? Drop it below 👇

#ConstructionTips #CivilEngineering #Philippines</div>
</div>

<div class="post-template">
    <div class="tag">Pricing / Promo Post</div>
    <div class="caption">All construction tools. One subscription. 💪

🔹 BOQ Generator
🔹 Rebar Cutting List
🔹 Structural Estimate
🔹 Architectural Estimate
🔹 Document Generator
🔹 Excel Templates

Start with a ₱29 daily pass or go unlimited with ₱199/month.

Sign up → argonar.co

#ConstructionTools #AffordableSoftware #ConstructionPH</div>
</div>

<h3>Visual Guidelines</h3>
<div class="card-dark">
    <ul style="color:var(--muted);font-size:.9rem;padding-left:1.25rem;">
        <li><strong>Screenshots:</strong> Use laptop/phone mockups with actual app screenshots. Clean, bright backgrounds.</li>
        <li><strong>Colors:</strong> Stick to Argonar brand — dark navy (#0f172a) + blue accent (#3b82f6) + white text.</li>
        <li><strong>Format:</strong> 1080x1080px for feed posts, 1080x1920px for Stories/Reels.</li>
        <li><strong>Video:</strong> 15-30 second screen recordings of tools in action perform best. Add captions — 85% of Facebook video is watched without sound.</li>
        <li><strong>Canva templates:</strong> Create a branded template set for consistency.</li>
    </ul>
</div>

<!-- ============================================== -->
<h2 id="phase3"><i class="fas fa-rocket me-2"></i>Phase 3 — Boosting Posts & Running Ads</h2>

<h3>Option A: Boost a Post (Simple)</h3>
<div class="card-dark">
    <div class="step">
        <span class="step-num">1</span>
        <div class="step-content">
            <strong>Pick the Right Post</strong>
            <p>Boost posts that already got organic engagement (likes, comments, shares). Feature demos and pricing posts convert best.</p>
        </div>
    </div>
    <div class="step">
        <span class="step-num">2</span>
        <div class="step-content">
            <strong>Click "Boost Post" → Set Goal</strong>
            <ul>
                <li>For awareness: <code>Get more people to engage with your post</code></li>
                <li>For signups: <code>Get more website visitors</code> → URL: <code>https://argonar.co/login.php</code></li>
            </ul>
        </div>
    </div>
    <div class="step">
        <span class="step-num">3</span>
        <div class="step-content">
            <strong>Audience Settings</strong>
            <ul>
                <li><strong>Location:</strong> Philippines (or narrow: Metro Manila, Cebu, Davao)</li>
                <li><strong>Age:</strong> 22-45</li>
                <li><strong>Interests:</strong> Civil Engineering, Construction, Architecture, Structural Engineering, Project Management, AutoCAD, Building Construction</li>
                <li><strong>Language:</strong> English and Filipino</li>
            </ul>
        </div>
    </div>
    <div class="step">
        <span class="step-num">4</span>
        <div class="step-content">
            <strong>Budget & Duration</strong>
            <ul>
                <li>Start with <strong>₱100-200/day</strong> for 5-7 days</li>
                <li>This gives enough data to see what works before scaling</li>
            </ul>
        </div>
    </div>
</div>

<h3>Option B: Meta Ads Manager (Advanced)</h3>
<div class="card-dark">
    <div class="step">
        <span class="step-num">1</span>
        <div class="step-content">
            <strong>Go to <a href="https://adsmanager.facebook.com" target="_blank">Meta Ads Manager</a></strong>
            <p>Create a Campaign → Choose <code>Traffic</code> or <code>Conversions</code> objective.</p>
        </div>
    </div>
    <div class="step">
        <span class="step-num">2</span>
        <div class="step-content">
            <strong>Create 3 Ad Sets (A/B test audiences)</strong>
            <ul>
                <li><strong>Ad Set A — Engineers:</strong> Interest: Civil Engineering, Structural Engineering, Construction Management. Age 22-35.</li>
                <li><strong>Ad Set B — Contractors:</strong> Interest: General Contractor, Building Construction, Construction Company. Age 28-45.</li>
                <li><strong>Ad Set C — Architecture:</strong> Interest: Architecture, Interior Design, Building Design. Age 22-38.</li>
            </ul>
        </div>
    </div>
    <div class="step">
        <span class="step-num">3</span>
        <div class="step-content">
            <strong>Ad Creative</strong>
            <ul>
                <li>Use 2-3 different visuals per ad set (carousel of features, video demo, single image)</li>
                <li>CTA: <code>Sign Up</code> button → <code>https://argonar.co/login.php</code></li>
                <li>Headline: "Construction Tools Made Easy" or "BOQ, Estimates & More — One App"</li>
            </ul>
        </div>
    </div>
    <div class="step">
        <span class="step-num">4</span>
        <div class="step-content">
            <strong>Retargeting (Week 2+)</strong>
            <p>Install <strong>Meta Pixel</strong> on argonar.co. Create Custom Audience of people who visited the site but didn't sign up. Run retargeting ads at ₱50-100/day — these convert at 2-3x lower cost.</p>
        </div>
    </div>
</div>

<h3>Recommended Targeting Interests</h3>
<div class="card-dark">
    <table class="boost-table">
        <thead>
            <tr><th>Category</th><th>Interest Keywords</th></tr>
        </thead>
        <tbody>
            <tr><td>Engineering</td><td>Civil Engineering, Structural Engineering, Construction Engineering, Geotechnical Engineering</td></tr>
            <tr><td>Construction</td><td>Building Construction, General Contractor, Construction Management, Construction Worker</td></tr>
            <tr><td>Architecture</td><td>Architecture, Interior Design, Building Design, Architectural Engineering</td></tr>
            <tr><td>Software</td><td>AutoCAD, SketchUp, Microsoft Excel, Project Management Software</td></tr>
            <tr><td>Education</td><td>Civil Engineering (degree), BS Architecture, Board Exam</td></tr>
        </tbody>
    </table>
</div>

<!-- ============================================== -->
<h2 id="phase4"><i class="fas fa-chart-line me-2"></i>Phase 4 — Budget & Reach Estimates</h2>

<h3>Philippines Facebook Ads Benchmarks (2025 Data)</h3>
<div class="stat-grid">
    <div class="stat-card">
        <div class="num">$0.16</div>
        <div class="label">Avg CPC (₱9)</div>
    </div>
    <div class="stat-card">
        <div class="num">$1.33</div>
        <div class="label">Avg CPM (₱75)</div>
    </div>
    <div class="stat-card">
        <div class="num">86%</div>
        <div class="label">Lower CPC vs Global</div>
    </div>
    <div class="stat-card">
        <div class="num">93%</div>
        <div class="label">Lower CPM vs Global</div>
    </div>
</div>

<div class="highlight">
    <strong>Philippines is one of the cheapest markets for Facebook Ads globally.</strong> Average CPC is ₱9 vs ₱63 global. Your peso goes 5-6x further here than in the US or EU.
</div>

<h3>Estimated Reach by Budget</h3>
<div class="card-dark">
    <table class="boost-table">
        <thead>
            <tr><th>Daily Budget</th><th>Monthly Cost</th><th>Est. Impressions/mo</th><th>Est. Clicks/mo</th><th>Est. Signups/mo</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>₱100/day</strong></td>
                <td>₱3,000</td>
                <td>~40,000</td>
                <td>~330</td>
                <td>~15-30</td>
            </tr>
            <tr>
                <td><strong>₱200/day</strong></td>
                <td>₱6,000</td>
                <td>~80,000</td>
                <td>~670</td>
                <td>~30-60</td>
            </tr>
            <tr>
                <td><strong>₱500/day</strong></td>
                <td>₱15,000</td>
                <td>~200,000</td>
                <td>~1,670</td>
                <td>~75-150</td>
            </tr>
            <tr>
                <td><strong>₱1,000/day</strong></td>
                <td>₱30,000</td>
                <td>~400,000</td>
                <td>~3,330</td>
                <td>~150-300</td>
            </tr>
        </tbody>
    </table>
    <p style="font-size:.8rem;color:var(--muted);margin-top:.75rem;">
        * Based on PH avg CPM of ₱75, CPC of ₱9, and 5-10% click-to-signup conversion rate.
        Actual results depend on targeting, creative quality, and landing page experience.
    </p>
</div>

<h3>ROI Calculator</h3>
<div class="card-dark">
    <table class="boost-table">
        <thead>
            <tr><th>Scenario</th><th>Budget/mo</th><th>Est. Signups</th><th>If 20% Subscribe (₱199/mo)</th><th>Monthly Revenue</th><th>ROI</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Conservative</td>
                <td>₱3,000</td>
                <td>15</td>
                <td>3 subs</td>
                <td>₱597</td>
                <td style="color:#ef4444">-80%</td>
            </tr>
            <tr>
                <td>Moderate</td>
                <td>₱6,000</td>
                <td>45</td>
                <td>9 subs</td>
                <td>₱1,791</td>
                <td style="color:#f59e0b">-70%</td>
            </tr>
            <tr>
                <td>Scaling</td>
                <td>₱15,000</td>
                <td>120</td>
                <td>24 subs</td>
                <td>₱4,776</td>
                <td style="color:#f59e0b">-68%</td>
            </tr>
            <tr>
                <td>Aggressive</td>
                <td>₱30,000</td>
                <td>250</td>
                <td>50 subs</td>
                <td>₱9,950</td>
                <td style="color:#f59e0b">-67%</td>
            </tr>
        </tbody>
    </table>
    <div class="highlight" style="margin-top:1rem;">
        <strong>Key insight:</strong> Month-1 ROI will be negative, and that's normal. Facebook ads for SaaS are an investment in <strong>user acquisition</strong> — subscribers who pay ₱199/month have lifetime value. A user staying 6 months = ₱1,194 LTV. Focus on acquiring users, not month-1 profit. At the ₱6,000/mo budget, you only need <strong>~30 retained monthly subscribers</strong> to break even — achievable by month 3-4 with compounding signups.
    </div>
</div>

<h3>Recommended Starting Budget</h3>
<div class="highlight">
    Start with <strong>₱200/day (₱6,000/month)</strong> for the first month. This gives enough data for meaningful optimization. Split between 2-3 ad sets to A/B test. After 2 weeks, kill the worst performer and reallocate budget to the winner.
</div>

<!-- ============================================== -->
<h2 id="phase5"><i class="fas fa-road me-2"></i>Phase 5 — Growth Playbook</h2>

<h3>Week 1-2: Foundation</h3>
<ul class="checklist">
    <li>Create Facebook page with all info filled out</li>
    <li>Design profile photo (logo) and cover image</li>
    <li>Create 3 organic posts (feature demo, industry tip, pricing)</li>
    <li>Install Meta Pixel on argonar.co</li>
    <li>Set up Meta Business Suite for scheduling</li>
</ul>

<h3>Week 3-4: Launch Paid Ads</h3>
<ul class="checklist">
    <li>Boost best-performing organic post at ₱100/day for 7 days</li>
    <li>Create 3 ad sets in Ads Manager (Engineers, Contractors, Architecture) at ₱200/day total</li>
    <li>Use 2 different creatives per ad set</li>
    <li>Monitor daily: check CTR (aim for >1.5%), CPC (aim for <₱12)</li>
</ul>

<h3>Month 2: Optimize</h3>
<ul class="checklist">
    <li>Kill ad sets with CTR < 1% or CPC > ₱15</li>
    <li>Scale winning ad sets by 20-30% budget increase</li>
    <li>Launch retargeting campaign for site visitors (₱50-100/day)</li>
    <li>Create Lookalike Audience from existing subscribers</li>
    <li>Add testimonial/social proof posts</li>
    <li>Post 3-4x per week consistently</li>
</ul>

<h3>Month 3+: Scale</h3>
<ul class="checklist">
    <li>Increase budget to ₱500/day if CPA (cost per acquisition) is sustainable</li>
    <li>Test video ads (screen recordings of tools) — video CPM is 15-30% lower</li>
    <li>Expand targeting: add AutoCAD, Excel, project management interests</li>
    <li>Run seasonal campaigns (peak construction season: Nov-May in PH)</li>
    <li>Consider Instagram ads (same Ads Manager, 84% of construction companies are on IG)</li>
    <li>Collect and post user testimonials monthly</li>
</ul>

<h3>Key Metrics to Track</h3>
<div class="card-dark">
    <table class="boost-table">
        <thead>
            <tr><th>Metric</th><th>Target</th><th>Action if Below</th></tr>
        </thead>
        <tbody>
            <tr><td>CTR (Click-Through Rate)</td><td>> 1.5%</td><td>Improve creative/copy</td></tr>
            <tr><td>CPC (Cost Per Click)</td><td>< ₱12</td><td>Refine audience targeting</td></tr>
            <tr><td>Landing → Signup Rate</td><td>> 5%</td><td>Improve landing page UX</td></tr>
            <tr><td>Signup → Subscriber Rate</td><td>> 15%</td><td>Improve onboarding / free trial</td></tr>
            <tr><td>Monthly Subscriber Churn</td><td>< 15%</td><td>Improve tool quality / add features</td></tr>
        </tbody>
    </table>
</div>

<hr style="border-color:var(--border);margin:3rem 0 1.5rem;">
<p style="color:var(--muted);font-size:.8rem;text-align:center;">
    Data sources: <a href="https://www.superads.ai/facebook-ads-costs/cpc-cost-per-click/philippines" target="_blank">Superads PH CPC</a> ·
    <a href="https://www.superads.ai/facebook-ads-costs/cpm-cost-per-mille/philippines" target="_blank">Superads PH CPM</a> ·
    <a href="https://www.spiralytics.com/blog/facebook-ads-costs-philippines/" target="_blank">Spiralytics</a> ·
    <a href="https://www.theedigital.com/blog/facebook-ads-benchmarks" target="_blank">EEDigital 2026 Benchmarks</a> ·
    <a href="https://seospecialist.com.ph/navigating-facebook-ads-costs-in-the-philippines/" target="_blank">SEO Specialist PH</a>
    <br>Last updated: March 2026
</p>

</div>
</body>
</html>

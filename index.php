<?php
require_once 'includes/helpers.php';
startSession();
$isLoggedIn = isUserLoggedIn();
$currentUser = $isLoggedIn ? currentUser() : null;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RapidAid - Emergency Ambulance Service | Fast. Reliable. Life-Saving.</title>
  <meta name="description" content="RapidAid provides 24/7 emergency ambulance services with real-time tracking, hospital integration, and subscription plans for individuals and families." />
  <style>
    html, body {
      display: block !important;
      background: #ffffff !important;
      color: #111111 !important;
      font-family: Arial, sans-serif !important;
      line-height: 1.6 !important;
      margin: 0 !important;
      padding: 20px !important;
    }

    body * {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
      background: transparent !important;
      border: none !important;
      box-shadow: none !important;
      text-shadow: none !important;
      backdrop-filter: none !important;
      -webkit-backdrop-filter: none !important;
      float: none !important;
      position: static !important;
      transform: none !important;
      animation: none !important;
    }

    .navbar,
    .nav-links,
    .nav-actions,
    .theme-toggle,
    .hamburger,
    .hero-bg-grid,
    .hero-glow,
    .hero-visual,
    .hero-card,
    .hero-badge,
    .hero-badge-dot,
    .hero-stats,
    .hero-actions,
    .btn,
    .badge,
    .metric-row,
    .metric,
    .benefit-icon,
    .about-visual,
    .loader-logo,
    .loader-bar,
    #page-loader,
    .emergency-bar,
    .contact-section {
      display: none !important;
    }

    .hero-content,
    .section,
    .container,
    .about-grid,
    .benefit-list,
    .benefit-item,
    .about-card,
    .feature-card {
      display: block !important;
      margin: 0 !important;
      padding: 0 !important;
      border: none !important;
      background: transparent !important;
      box-shadow: none !important;
    }

    h1, h2, h3, h4, h5, p, li, span, a, div {
      color: #111111 !important;
      text-decoration: none !important;
    }
  </style>
</head>
<body>
<!-- Page Loader -->
<div id="page-loader">
  <div class="loader-logo">Rapid<span>Aid</span></div>
  <div class="loader-bar"><div class="loader-bar-fill"></div></div>
  <p style="color:rgba(255,255,255,0.4);font-size:0.8rem;margin-top:8px;">Loading emergency services...</p>
</div>

<!-- Emergency Bar -->
<div class="emergency-bar">
  <p>🚨 Emergency Hotline — Available 24/7</p>
  <a href="tel:119">📞 119 / 021-500-119</a>
</div>

<!-- Navbar -->
<nav class="navbar" id="navbar">
  <a href="index.php" class="nav-logo">
    <div class="logo-icon">🚑</div>
    Rapid<span>Aid</span>
  </a>
  <ul class="nav-links" id="nav-links">
    <li><a href="#about" class="navbar-link">About</a></li>
    <li><a href="#features" class="navbar-link">Features</a></li>
    <li><a href="#how-it-works" class="navbar-link">How It Works</a></li>
    <li><a href="#pricing" class="navbar-link">Plans</a></li>
    <li><a href="#contact" class="navbar-link">Contact</a></li>
  </ul>
  <div class="nav-actions">
    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">🌙</button>
    <?php if ($isLoggedIn): ?>
      <a href="user/dashboard.php" class="btn btn-teal btn-sm">Dashboard</a>
    <?php else: ?>
      <a href="auth/login.php" class="btn btn-secondary btn-sm">Login</a>
      <a href="auth/register.php" class="btn btn-primary btn-sm">Get Started</a>
    <?php endif; ?>
    <button class="hamburger" id="hamburger">☰</button>
  </div>
</nav>

<!-- Hero Section -->
<section class="hero" id="hero">
  <div class="hero-bg-grid"></div>
  <div class="hero-glow hero-glow-1"></div>
  <div class="hero-glow hero-glow-2"></div>

  <div class="hero-content">
    <div class="hero-badge">
      <div class="hero-badge-dot"></div>
      🚑 Indonesia's #1 Emergency Platform
    </div>
    <h1>
      Emergency Help,<br />
      <span class="highlight">One Tap Away</span>
    </h1>
    <p>RapidAid connects you to the nearest ambulance in minutes. Real-time tracking, verified drivers, and hospital coordination — all in one platform.</p>
    <div class="hero-actions">
      <?php if ($isLoggedIn): ?>
        <a href="user/request-ambulance.php" class="btn btn-primary btn-lg">🚑 Request Ambulance</a>
        <a href="user/subscriptions.php" class="btn btn-secondary btn-lg">💎 View Plans</a>
      <?php else: ?>
        <a href="auth/register.php" class="btn btn-primary btn-lg">🚑 Request Ambulance</a>
        <a href="#pricing" class="btn btn-secondary btn-lg">💎 View Plans</a>
      <?php endif; ?>
    </div>
    <div class="hero-stats">
      <div class="hero-stat">
        <div class="num" data-count="2400">0</div>
        <div class="label">Emergencies Handled</div>
      </div>
      <div class="hero-stat">
        <div class="num" data-count="8">0</div>
        <div class="label">Min Avg Response</div>
      </div>
      <div class="hero-stat">
        <div class="num" data-count="15">0</div>
        <div class="label">Partner Hospitals</div>
      </div>
    </div>
  </div>

  <div class="hero-visual">
    <div class="hero-card">
      <div class="hero-card-header">
        <div>
          <div style="font-size:0.75rem;color:rgba(255,255,255,0.5);margin-bottom:4px;">ACTIVE REQUEST</div>
          <div style="font-size:0.95rem;font-weight:700;color:white;">REQ-2025-0042</div>
        </div>
        <span class="badge badge-warning">On The Way</span>
      </div>
      <div style="background:rgba(255,255,255,0.06);border-radius:12px;height:160px;display:flex;align-items:center;justify-content:center;font-size:3rem;margin-bottom:16px;">🗺️</div>
      <div style="display:flex;align-items:center;gap:12px;padding:14px;background:rgba(229,62,62,0.12);border-radius:12px;border:1px solid rgba(229,62,62,0.2);">
        <div style="width:40px;height:40px;border-radius:50%;background:var(--red);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">🚑</div>
        <div>
          <div style="font-size:0.8rem;color:rgba(255,255,255,0.5);">Driver</div>
          <div style="font-size:0.9rem;font-weight:600;color:white;">Eko Prasetyo • ⭐ 4.9</div>
        </div>
        <div style="margin-left:auto;font-family:'Syne',sans-serif;font-size:1rem;font-weight:800;color:var(--red);">~8 min</div>
      </div>
    </div>
  </div>
</section>

<!-- About Section -->
<section class="section section-dark" id="about">
  <div class="container">
    <div class="about-grid">
      <div>
        <div class="badge-red reveal">⚡ About RapidAid</div>
        <h2 class="section-title reveal" style="color:white;margin-top:16px;">Saving Lives Through<br/>Smart Technology</h2>
        <p style="color:rgba(255,255,255,0.6);margin-bottom:32px;line-height:1.8;" class="reveal">RapidAid is Indonesia's most trusted emergency ambulance platform. We bridge the gap between patients and life-saving care with cutting-edge technology, verified medical teams, and 24/7 availability.</p>
        <div class="benefit-list">
          <div class="benefit-item reveal">
            <div class="benefit-icon red">🚑</div>
            <div class="benefit-text">
              <h5>Instant Emergency Response</h5>
              <p>Request an ambulance in seconds. Our AI dispatch system finds the nearest available unit immediately.</p>
            </div>
          </div>
          <div class="benefit-item reveal">
            <div class="benefit-icon teal">📍</div>
            <div class="benefit-text">
              <h5>Live GPS Tracking</h5>
              <p>Track your ambulance in real-time from dispatch to arrival. Know exactly when help will reach you.</p>
            </div>
          </div>
          <div class="benefit-item reveal">
            <div class="benefit-icon blue">🏥</div>
            <div class="benefit-text">
              <h5>Hospital Pre-Coordination</h5>
              <p>We notify the hospital before you arrive so the medical team is ready the moment you walk in.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="about-visual reveal">
        <div class="about-card">
          <h4 style="color:white;margin-bottom:8px;">Response Performance</h4>
          <p style="color:rgba(255,255,255,0.5);font-size:0.85rem;margin-bottom:20px;">Last 30 days performance metrics</p>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div>
              <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:0.8rem;color:rgba(255,255,255,0.6);">Avg Response Time</span>
                <span style="font-size:0.8rem;font-weight:700;color:var(--teal);">8.2 min</span>
              </div>
              <div style="height:6px;background:rgba(255,255,255,0.1);border-radius:99px;"><div style="height:100%;width:82%;background:linear-gradient(90deg,var(--teal),var(--blue));border-radius:99px;"></div></div>
            </div>
            <div>
              <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:0.8rem;color:rgba(255,255,255,0.6);">Customer Satisfaction</span>
                <span style="font-size:0.8rem;font-weight:700;color:var(--red);">96.4%</span>
              </div>
              <div style="height:6px;background:rgba(255,255,255,0.1);border-radius:99px;"><div style="height:100%;width:96%;background:linear-gradient(90deg,var(--red),#ff6b6b);border-radius:99px;"></div></div>
            </div>
            <div>
              <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:0.8rem;color:rgba(255,255,255,0.6);">Fleet Availability</span>
                <span style="font-size:0.8rem;font-weight:700;color:#f6ad55;">89%</span>
              </div>
              <div style="height:6px;background:rgba(255,255,255,0.1);border-radius:99px;"><div style="height:100%;width:89%;background:linear-gradient(90deg,#f6ad55,#fc8181);border-radius:99px;"></div></div>
            </div>
          </div>
          <div class="metric-row">
            <div class="metric"><div class="num" data-count="24">0</div><div class="lbl">Ambulances</div></div>
            <div class="metric"><div class="num" data-count="15">0</div><div class="lbl">Hospitals</div></div>
            <div class="metric"><div class="num" data-count="500">0</div><div class="lbl">Members</div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="section" id="features">
  <div class="container">
    <div class="section-header section-center">
      <div class="badge-red reveal">✨ Platform Features</div>
      <h2 class="section-title reveal" style="margin-top:16px;">Everything You Need<br/>In One Platform</h2>
      <p class="section-subtitle reveal">A complete emergency healthcare ecosystem built for speed, reliability, and peace of mind.</p>
    </div>
    <div class="features-grid">
      <?php
      $features = [
        ['🚨','Emergency Ambulance Request','One-tap emergency requests with intelligent routing to the nearest available ambulance unit.','rgba(229,62,62,0.12)','#e53e3e'],
        ['📍','Real-Time Ambulance Tracking','Live GPS tracking shows your ambulance\'s exact position and estimated arrival time.','rgba(56,178,172,0.12)','#38b2ac'],
        ['🏥','Hospital Integration','Pre-notification to partner hospitals ensures the medical team is ready on your arrival.','rgba(49,130,206,0.12)','#3182ce'],
        ['💎','Subscription Membership','Monthly plans for individuals and families with priority dispatch and exclusive benefits.','rgba(246,173,85,0.12)','#d69e2e'],
        ['💳','Online Payment','Secure payments via Bank Transfer, E-Wallet (GoPay, OVO, DANA), or Credit Card.','rgba(56,161,105,0.12)','#38a169'],
        ['📋','Emergency History','Complete history of all emergency requests with status, driver, and hospital information.','rgba(229,62,62,0.12)','#e53e3e'],
        ['👤','Driver Information','View verified driver profiles, ratings, and real-time location during an emergency.','rgba(56,178,172,0.12)','#38b2ac'],
        ['⚡','Fast Response Monitoring','Track response times, monitor fleet performance, and get insights on service quality.','rgba(49,130,206,0.12)','#3182ce'],
      ];
      foreach ($features as $f): ?>
      <div class="feature-card reveal">
        <div class="feature-icon" style="background:<?= $f[3] ?>;color:<?= $f[4] ?>"><?= $f[0] ?></div>
        <h4><?= $f[1] ?></h4>
        <p><?= $f[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- How It Works -->
<section class="section section-dark" id="how-it-works">
  <div class="container">
    <div class="section-header section-center">
      <div class="badge-red reveal">🗺️ How It Works</div>
      <h2 class="section-title reveal" style="color:white;margin-top:16px;">Get Help in 5 Simple Steps</h2>
      <p class="section-subtitle reveal" style="color:rgba(255,255,255,0.55);">From registration to receiving care — RapidAid makes it seamless.</p>
    </div>
    <div class="steps-grid">
      <?php
      $steps = [
        ['01','Register','Create your free account with basic personal and health information.'],
        ['02','Choose Plan','Select a subscription plan that fits your needs and family size.'],
        ['03','Request Ambulance','Hit the emergency button and our system dispatches the nearest unit.'],
        ['04','Track Live','Follow your ambulance on a live map with real-time ETA updates.'],
        ['05','Get Care','Our team and partner hospital are ready to provide immediate assistance.'],
      ];
      foreach ($steps as $s): ?>
      <div class="step-card reveal">
        <div class="step-num"><?= $s[0] ?></div>
        <h4 style="color:white;"><?= $s[1] ?></h4>
        <p><?= $s[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Pricing Section -->
<section class="section" id="pricing">
  <div class="container">
    <div class="section-header section-center">
      <div class="badge-red reveal">💰 Subscription Plans</div>
      <h2 class="section-title reveal" style="margin-top:16px;">Simple, Transparent Pricing</h2>
      <p class="section-subtitle reveal">Choose the plan that's right for you. Cancel or upgrade anytime.</p>
    </div>
    <div class="pricing-grid">
      <?php
      $plans = db()->fetchAll("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY price ASC");
      $planIcons = ['basic' => '🛡️', 'premium' => '⚡', 'family' => '👨‍👩‍👧‍👦'];
      foreach ($plans as $plan):
        $features = json_decode($plan['features'], true) ?? [];
        $popular = $plan['is_popular'];
      ?>
      <div class="pricing-card <?= $popular ? 'popular' : '' ?> reveal">
        <?php if ($popular): ?><div class="popular-badge">Most Popular</div><?php endif; ?>
        <div class="pricing-icon" style="background:<?= $popular ? 'rgba(229,62,62,0.2)' : 'rgba(229,62,62,0.08)' ?>">
          <?= $planIcons[$plan['slug']] ?? '📦' ?>
        </div>
        <h3><?= clean($plan['name']) ?></h3>
        <p style="font-size:0.85rem;color:<?= $popular ? 'rgba(255,255,255,0.5)' : 'var(--text-muted)' ?>">
          <?= $plan['max_members'] > 1 ? 'Up to '.$plan['max_members'].' members' : 'Per person' ?>
        </p>
        <div class="price-amount"><?= formatRupiah($plan['price']) ?></div>
        <div class="price-period">/month · Billed monthly</div>
        <ul class="pricing-features">
          <?php foreach ($features as $feat): ?>
          <li><span class="check">✓</span><?= clean($feat) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= $isLoggedIn ? 'user/subscriptions.php?plan='.$plan['id'] : 'auth/register.php' ?>" 
           class="btn w-full <?= $popular ? 'btn-primary' : 'btn-outline' ?>">
          Get <?= clean($plan['name']) ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="section section-dark" id="testimonials">
  <div class="container">
    <div class="section-header section-center">
      <div class="badge-red reveal">💬 Testimonials</div>
      <h2 class="section-title reveal" style="color:white;margin-top:16px;">Trusted by Thousands</h2>
      <p class="section-subtitle reveal" style="color:rgba(255,255,255,0.55);">Real stories from people who experienced RapidAid's life-saving service.</p>
    </div>
    <div class="testimonials-grid">
      <?php
      $testimonials = [
        ['Budi Santoso', 'Surabaya', '★★★★★', '"RapidAid arrived in under 8 minutes when my father had a heart attack. The driver was professional and the hospital was already notified. It truly saved his life."'],
        ['Dewi Rahayu', 'Jakarta', '★★★★★', '"The Premium subscription is worth every rupiah. Live tracking gave me peace of mind during my mother\'s emergency. I could see exactly where the ambulance was!"'],
        ['Ahmad Fauzi', 'Bandung', '★★★★☆', '"The Family plan is perfect for us. Having all 5 members covered means we never worry about emergencies. The app is easy and customer service is excellent."'],
        ['Sari Wulandari', 'Malang', '★★★★★', '"I was skeptical at first, but after using RapidAid for my husband\'s emergency, I\'m a believer. Fast, professional, and the hospital coordination was seamless."'],
        ['Rizky Firmansyah', 'Surabaya', '★★★★★', '"As a doctor, I\'m impressed by how RapidAid handles hospital pre-notifications. By the time patients arrive, we\'re fully prepared. This is the future of emergency care."'],
        ['Linda Kusuma', 'Yogyakarta', '★★★★★', '"The Basic plan was affordable and covered my emergency perfectly. The driver was kind and professional. I\'ve already recommended it to my entire neighborhood!"'],
      ];
      foreach ($testimonials as $t): ?>
      <div class="testimonial-card reveal" style="background:rgba(255,255,255,0.04);border-color:rgba(255,255,255,0.08);">
        <div class="testimonial-stars"><?= $t[2] ?></div>
        <p style="color:rgba(255,255,255,0.65);"><?= $t[3] ?></p>
        <div class="testimonial-author">
          <div class="author-avatar"><?= substr($t[0],0,1) ?></div>
          <div>
            <div class="author-name" style="color:white;"><?= $t[0] ?></div>
            <div class="author-location"><?= $t[1] ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="section" id="faq">
  <div class="container">
    <div class="section-header section-center">
      <div class="badge-red reveal">❓ FAQ</div>
      <h2 class="section-title reveal" style="margin-top:16px;">Frequently Asked Questions</h2>
      <p class="section-subtitle reveal">Have a question? We've got answers.</p>
    </div>
    <div class="faq-list">
      <?php
      $faqs = [
        ['How quickly will the ambulance arrive?', 'Our average response time is under 10 minutes in major cities. Response time varies based on traffic and your location. Premium and Family subscribers receive priority dispatch.'],
        ['Do I need a subscription to use RapidAid?', 'No, you can use RapidAid without a subscription. However, subscribing gives you priority dispatch, live tracking, and other exclusive benefits. Emergency requests without a subscription will be processed at standard rate.'],
        ['What areas does RapidAid serve?', 'RapidAid currently operates in Surabaya, Jakarta, Bandung, Malang, and Yogyakarta. We are rapidly expanding to more cities across Indonesia.'],
        ['Are your drivers medically trained?', 'Yes. All RapidAid drivers are certified Emergency Medical Technicians (EMT) with valid licenses. Our ALS (Advanced Life Support) units carry paramedics with advanced medical training.'],
        ['Can I cancel my subscription?', 'Yes, you can cancel your subscription at any time from your dashboard. Your plan remains active until the end of the billing period. We do not offer partial refunds.'],
        ['What payment methods are accepted?', 'We accept Bank Transfer (BCA, Mandiri, BNI, BRI), E-Wallets (GoPay, OVO, DANA, ShopeePay), and major Credit Cards (Visa, Mastercard).'],
        ['What if I need to cancel an emergency request?', 'You can cancel a request within 2 minutes of submission without any penalty. After 2 minutes, a cancellation fee may apply if an ambulance has already been dispatched.'],
      ];
      foreach ($faqs as $i => $faq): ?>
      <div class="faq-item reveal">
        <button class="faq-question" aria-expanded="false">
          <?= $faq[0] ?>
          <span class="faq-icon">+</span>
        </button>
        <div class="faq-answer"><p><?= $faq[1] ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Contact -->
<section class="section contact-section" id="contact">
  <div class="container">
    <div class="section-header section-center">
      <div class="badge-red reveal">📬 Contact Us</div>
      <h2 class="section-title reveal" style="color:white;margin-top:16px;">Get In Touch</h2>
      <p class="section-subtitle reveal" style="color:rgba(255,255,255,0.55);">Our team is available 24/7 to assist you with any questions.</p>
    </div>
    <div class="contact-grid">
      <div class="contact-info reveal">
        <div class="contact-item">
          <div class="contact-icon">📞</div>
          <div>
            <div style="font-weight:600;color:white;margin-bottom:4px;">Emergency Hotline</div>
            <div style="color:rgba(255,255,255,0.5);font-size:0.875rem;">119 / 021-500-119</div>
            <div style="color:rgba(255,255,255,0.5);font-size:0.875rem;">Available 24/7</div>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-icon">✉️</div>
          <div>
            <div style="font-weight:600;color:white;margin-bottom:4px;">Email Support</div>
            <div style="color:rgba(255,255,255,0.5);font-size:0.875rem;">support@rapidaid.id</div>
            <div style="color:rgba(255,255,255,0.5);font-size:0.875rem;">Response within 2 hours</div>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-icon">📍</div>
          <div>
            <div style="font-weight:600;color:white;margin-bottom:4px;">Head Office</div>
            <div style="color:rgba(255,255,255,0.5);font-size:0.875rem;">Jl. Pemuda No. 1, Surabaya</div>
            <div style="color:rgba(255,255,255,0.5);font-size:0.875rem;">Jawa Timur, Indonesia</div>
          </div>
        </div>
        <div class="contact-item">
          <div class="contact-icon">💬</div>
          <div>
            <div style="font-weight:600;color:white;margin-bottom:4px;">WhatsApp</div>
            <div style="color:rgba(255,255,255,0.5);font-size:0.875rem;">+62 812-3456-7890</div>
            <div style="color:rgba(255,255,255,0.5);font-size:0.875rem;">Chat with our team</div>
          </div>
        </div>
      </div>
      <div class="reveal">
        <form class="contact-form" id="contact-form">
          <div class="form-group">
            <label class="form-label" style="color:rgba(255,255,255,0.7);">Your Name</label>
            <input type="text" class="form-control" placeholder="John Doe" required style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1);color:white;" />
          </div>
          <div class="form-group">
            <label class="form-label" style="color:rgba(255,255,255,0.7);">Email Address</label>
            <input type="email" class="form-control" placeholder="you@example.com" required style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1);color:white;" />
          </div>
          <div class="form-group">
            <label class="form-label" style="color:rgba(255,255,255,0.7);">Subject</label>
            <input type="text" class="form-control" placeholder="How can we help?" style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1);color:white;" />
          </div>
          <div class="form-group">
            <label class="form-label" style="color:rgba(255,255,255,0.7);">Message</label>
            <textarea class="form-control" rows="4" placeholder="Your message..." style="background:rgba(255,255,255,0.05);border-color:rgba(255,255,255,0.1);color:white;"></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-full">📨 Send Message</button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="nav-logo" style="font-size:1.4rem;">
          <div class="logo-icon">🚑</div>
          Rapid<span>Aid</span>
        </div>
        <p>Indonesia's most trusted emergency ambulance platform. Fast response, reliable service, and life-saving care — available 24/7.</p>
        <div class="footer-socials">
          <a href="#" class="social-btn">f</a>
          <a href="#" class="social-btn">in</a>
          <a href="#" class="social-btn">tw</a>
          <a href="#" class="social-btn">ig</a>
          <a href="#" class="social-btn">yt</a>
        </div>
      </div>
      <div class="footer-col">
        <h5>Services</h5>
        <a href="#">Emergency Ambulance</a>
        <a href="#">Medical Transport</a>
        <a href="#">Hospital Transfer</a>
        <a href="#">Neonatal Transport</a>
        <a href="#">Event Medical Support</a>
      </div>
      <div class="footer-col">
        <h5>Company</h5>
        <a href="#">About Us</a>
        <a href="#">Careers</a>
        <a href="#">Press</a>
        <a href="#">Blog</a>
        <a href="#">Partners</a>
      </div>
      <div class="footer-col">
        <h5>Support</h5>
        <a href="#">Help Center</a>
        <a href="#">Contact Us</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="auth/admin-login.php">Admin Login</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> RapidAid. All rights reserved.</span>
      <span>Made with ❤️ in Indonesia</span>
    </div>
  </div>
</footer>

<!-- SOS Button -->
<button class="sos-button" onclick="window.location.href='<?= $isLoggedIn ? 'user/request-ambulance.php' : 'auth/register.php' ?>'">
  <span style="font-size:1.2rem;">🚨</span>
  <span>SOS</span>
</button>

<script src="assets/js/main.js"></script>
<script>
document.getElementById('contact-form').addEventListener('submit', function(e) {
  e.preventDefault();
  showToast('Message sent! We\'ll respond within 2 hours.', 'success');
  this.reset();
});
document.getElementById('hamburger').addEventListener('click', function() {
  document.getElementById('nav-links').classList.toggle('mobile-open');
});
</script>
</body>
</html>

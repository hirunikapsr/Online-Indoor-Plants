<?php
// pages/about.php - About Online Indoor Plants
define('IS_SUBPAGE', true);
$page_title = "About Us - Online Indoor Plants";
require_once __DIR__ . '/../includes/header.php';
?>

<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color: var(--white); padding: 60px 0; text-align: center;">
  <div class="container">
    <h1 style="color:var(--white); font-size: 2.8rem; margin-bottom: 12px;">About Online Indoor Plants</h1>
    <p style="color: rgba(255,255,255,0.85); font-size: 1.15rem; max-width: 650px; margin: 0 auto;">
      Empowering city dwellers and home decorators to cultivate thriving, air-purifying botanical spaces indoors.
    </p>
  </div>
</section>

<section class="section">
  <div class="container">
    
    <div class="care-grid" style="margin-bottom: 60px;">
      <div>
        <span style="color:var(--emerald); font-weight:700; text-transform:uppercase; letter-spacing:1px;">OUR MISSION</span>
        <h2 style="font-size:2.4rem; margin:15px 0 20px;">Connecting People With Nature Indoors</h2>
        <p style="color:var(--text-muted); line-height:1.7; margin-bottom:20px;">
          Founded in 2026, <strong>Online Indoor Plants</strong> was created to make indoor plant parenthood accessible, delightful, and foolproof. We believe every home, desk, and room deserves the refreshing touch of natural foliage.
        </p>
        <p style="color:var(--text-muted); line-height:1.7;">
          Every plant in our greenhouse is hand-selected, potted in premium nutrient-rich soil, and paired with custom-designed ceramic, terracotta, or plastic planters to match your interior aesthetic.
        </p>
      </div>

      <div style="text-align:center;">
        <div style="background:var(--sage-light); padding:40px; border-radius:var(--radius-lg); display:inline-block; border:1px solid var(--border-color);">
          <img src="../image/indoor_gardening_nursery.jpg" alt="Indoor Greenhouse Nursery" style="max-height:300px; width:100%; object-fit:cover; border-radius:var(--radius-md);">
        </div>
      </div>
    </div>

    <!-- Core Pillars / Why Choose Us -->
    <div class="section-header text-center" style="margin-top:60px;">
      <h2 class="section-title">The Indoor Plant Advantage</h2>
      <p class="section-subtitle">Why thousands of plant lovers trust our online greenhouse.</p>
    </div>

    <div class="features-grid">
      <div class="feature-box">
        <div class="feature-icon"></div>
        <h3>Healthy Nursery Grown</h3><br>
        <p>Grown in optimal climate-controlled conditions by experienced horticulturists so plants arrive robust and disease-free.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon"></div>
        <h3>Custom Pot Pairings</h3><br>
        <p>Select your favorite pot material (Ceramic, Terracotta, Plastic) and colour finish (White, Black, Brown) for a custom look.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon"></div>
        <h3>30-Day Guarantee</h3><br>
        <p>We stand behind our plants. If your plant arrives damaged or sick within 30 days, we replace it free of charge.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon"></div>
        <h3>Eco-Friendly Packaging</h3><br>
        <p>100% biodegradable and recyclable packaging materials designed to protect plants and soil in transit.</p>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

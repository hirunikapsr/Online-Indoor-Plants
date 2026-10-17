<?php
// pages/contact.php - Contact & Plant Consultation Form
define('IS_SUBPAGE', true);
$page_title = "Contact Us - Online Indoor Plants";
require_once __DIR__ . '/../includes/header.php';

$msg_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg_sent = true;
}
?>

<section style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color: var(--white); padding: 50px 0; text-align: center;">
  <div class="container">
    <h1 style="color:var(--white); font-size: 2.6rem; margin-bottom: 10px;">Contact Our Botanical Experts</h1>
    <p style="color: rgba(255,255,255,0.85); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
      Have a question about plant care, pot variations, or an existing order? We are here to help!
    </p>
  </div>
</section>

<section class="section">
  <div class="container">
    
    <div class="shop-layout" style="grid-template-columns: 1fr 400px;">
      
      <!-- Contact Form -->
      <div class="form-card">
        <h2 style="margin-bottom:20px; border-bottom:2px solid var(--sage-light); padding-bottom:10px;">Send Us a Message</h2>

        <?php if ($msg_sent): ?>
          <div style="background:#d1fae5; color:#065f46; padding:15px 20px; border-radius:var(--radius-sm); margin-bottom:25px; border-left:4px solid #10b981;">
            ✓ Thank you! Your message has been sent to our plant care team. We will reply within 24 hours.
          </div>
        <?php endif; ?>

        <form method="POST" action="contact.php">
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">Your Name *</label>
              <input type="text" name="name" class="form-control" required placeholder="John Doe">
            </div>

            <div class="form-group">
              <label class="form-label">Email Address *</label>
              <input type="email" name="email" class="form-control" required placeholder="john@example.com">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Subject *</label>
            <input type="text" name="subject" class="form-control" required placeholder="e.g. Plant Care Advice / Order Query">
          </div>

          <div class="form-group">
            <label class="form-label">Your Message *</label>
            <textarea name="message" class="form-control" rows="5" required placeholder="Describe your question or plant care needs..."></textarea>
          </div>

          <button type="submit" class="btn btn-emerald" style="padding:14px 35px;">
            Submit Message ➔
          </button>
        </form>
      </div>

      <!-- Contact Info Sidebar -->
      <div>
        <div style="background:var(--white); padding:30px; border-radius:var(--radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-sm); margin-bottom:30px;">
          <h3 style="margin-bottom:20px; color:var(--primary-forest);">Greenhouse Location</h3>
          
          <div style="display:flex; gap:15px; margin-bottom:20px; align-items:flex-start;">
            <div style="font-size:1.5rem; color:var(--emerald);"></div>
            <div>
              <strong style="display:block; margin-bottom:2px;">Nursery Address</strong>
              <p style="color:var(--text-muted); font-size:0.95rem;">123 Botanical Avenue, Green District, Colombo 00100</p>
            </div>
          </div>

          <div style="display:flex; gap:15px; margin-bottom:20px; align-items:flex-start;">
            <div style="font-size:1.5rem; color:var(--emerald);"></div>
            <div>
              <strong style="display:block; margin-bottom:2px;">Telephone</strong>
              <p style="color:var(--text-muted); font-size:0.95rem;">+94 77 123 4567 / +94 11 234 5678</p>
            </div>
          </div>

          <div style="display:flex; gap:15px; align-items:flex-start;">
            <div style="font-size:1.5rem; color:var(--emerald);"></div>
            <div>
              <strong style="display:block; margin-bottom:2px;">Email Support</strong>
              <p style="color:var(--text-muted); font-size:0.95rem;">support@onlineindoorplants.com</p>
            </div>
          </div>
        </div>

        <div style="background:var(--sage-light); padding:25px; border-radius:var(--radius-md); border:1px solid var(--border-color);">
          <h4 style="margin-bottom:10px;">Support Hours</h4>
          <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:5px;">Monday - Friday: 8:00 AM - 6:00 PM</p>
          <p style="font-size:0.9rem; color:var(--text-muted);">Saturday - Sunday: 9:00 AM - 4:00 PM</p>
        </div>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

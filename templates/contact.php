<?php
$pageTitle = 'Contact RCS Graphic — Print Order Help & Bulk Quotes';
$pageDesc = 'Contact RCS Graphic for printing support, bulk quotes, design guidance, order help, WhatsApp support and delivery questions.';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';

$settingsMap = is_array($settingsMap ?? null) ? $settingsMap : [];
$bizName = htmlspecialchars($settingsMap['biz_name'] ?? 'RCS Print', ENT_QUOTES, 'UTF-8');
$bizPhoneRaw = (string)($settingsMap['biz_phone'] ?? '+91 98765 43210');
$bizPhone = htmlspecialchars($bizPhoneRaw, ENT_QUOTES, 'UTF-8');
$bizPhoneHref = htmlspecialchars(preg_replace('/\D+/', '', $bizPhoneRaw), ENT_QUOTES, 'UTF-8');
$bizWa = htmlspecialchars($settingsMap['biz_whatsapp'] ?? '919876543210', ENT_QUOTES, 'UTF-8');
$bizEmail = htmlspecialchars($settingsMap['biz_email'] ?? 'info@rcsprint.in', ENT_QUOTES, 'UTF-8');
$bizAddr = htmlspecialchars($settingsMap['biz_address'] ?? '150ft Ring Road, Rajkot - 360005, Gujarat, India', ENT_QUOTES, 'UTF-8');
$waText = rawurlencode('Hello RCS Print, I need help with a printing requirement.');
?>
<main class="contact-showcase-page">
  <section class="contact-showcase-hero">
    <div class="contact-showcase-container contact-hero-grid">
      <div class="contact-hero-copy">
        <h1>Contact Us</h1>
        <h2>We’d love to hear from you!</h2>
        <p>Have a question, need a quote, or want to discuss your printing needs? Our team is here to help you every step of the way.</p>
        <div class="contact-hero-features" aria-label="Contact support highlights">
          <div><i class="fa-solid fa-headset" aria-hidden="true"></i><span><strong>Quick Response</strong><small>We reply within 24 hours</small></span></div>
          <div><i class="fa-solid fa-shield-heart" aria-hidden="true"></i><span><strong>Trusted Support</strong><small>We’re here to help</small></span></div>
          <div><i class="fa-regular fa-thumbs-up" aria-hidden="true"></i><span><strong>100% Satisfaction</strong><small>Your happiness matters</small></span></div>
        </div>
      </div>
      <div class="contact-hero-visual" aria-label="RCS Print contact visual">
        <div class="contact-hero-paper">
          <strong><span class="rcs-word"><span>R</span><span>C</span><span>S</span></span></strong>
          <small>PRINT</small>
          <em>Your Brand<br>Our Passion<br>Perfect Impact</em>
        </div>
        <div class="contact-hero-plant" aria-hidden="true"><span></span></div>
      </div>
    </div>
  </section>

  <section class="contact-main-section">
    <div class="contact-showcase-container contact-main-grid">
      <form class="contact-message-card" id="contactQuickForm" action="https://wa.me/<?= $bizWa ?>" method="get" target="_blank">
        <h2>Send Us a Message</h2>
        <div class="contact-form-grid">
          <label>Your Name <b>*</b><input type="text" name="name" placeholder="Enter your full name" required></label>
          <label>Email Address <b>*</b><input type="email" name="email" placeholder="Enter your email" required></label>
          <label>Phone Number<input type="tel" name="phone" placeholder="Enter your phone number"></label>
          <label>Subject <b>*</b><input type="text" name="subject" placeholder="How can we help you?" required></label>
          <label class="contact-full-field">Your Message <b>*</b><textarea name="message" rows="5" placeholder="Type your message here..." required></textarea></label>
        </div>
        <button class="contact-send-btn" type="submit"><i class="fa-regular fa-paper-plane" aria-hidden="true"></i> Send Message</button>
      </form>

      <aside class="contact-touch-card">
        <h2>Get in Touch</h2>
        <p>Choose the best way to reach us.</p>
        <div class="contact-touch-list">
          <a href="tel:<?= $bizPhoneHref ?>"><i class="fa-solid fa-phone" aria-hidden="true"></i><span><strong>Call Us</strong><b><?= $bizPhone ?></b><small>Mon - Sat: 10:00 AM - 7:00 PM</small></span></a>
          <a href="https://wa.me/<?= $bizWa ?>?text=<?= $waText ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><span><strong>WhatsApp Us</strong><b><?= $bizPhone ?></b><small>We reply within minutes</small></span></a>
          <a href="mailto:<?= $bizEmail ?>"><i class="fa-regular fa-envelope" aria-hidden="true"></i><span><strong>Email Us</strong><b><?= $bizEmail ?></b><small>We reply within 24 hours</small></span></a>
          <a href="#contact-location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><strong>Visit Our Office</strong><b><?= $bizName ?></b><small><?= $bizAddr ?></small></span></a>
        </div>
      </aside>
    </div>
  </section>

  <section class="contact-benefits-section">
    <div class="contact-showcase-container contact-benefits-bar">
      <div><i class="fa-regular fa-clock" aria-hidden="true"></i><span><strong>Fast Response</strong><small>We respond within 24 hours</small></span></div>
      <div><i class="fa-solid fa-headset" aria-hidden="true"></i><span><strong>Expert Support</strong><small>Professional guidance for your printing needs</small></span></div>
      <div><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><span><strong>Secure &amp; Reliable</strong><small>Your information is safe with us</small></span></div>
      <div><i class="fa-regular fa-star" aria-hidden="true"></i><span><strong>Customer Focused</strong><small>Your satisfaction is our priority</small></span></div>
    </div>
  </section>

  <section class="contact-location-section" id="contact-location">
    <div class="contact-showcase-container">
      <div class="contact-section-title">
        <h2>Our Location</h2>
        <a href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($settingsMap['biz_address'] ?? 'Rajkot Gujarat') ?>" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Get Directions</a>
      </div>
      <div class="contact-map-card">
        <div class="contact-map-info">
          <h3><?= $bizName ?></h3>
          <p><?= $bizAddr ?></p>
          <ul>
            <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Landmark: Near Crystal Mall</li>
            <li><i class="fa-solid fa-phone" aria-hidden="true"></i> Phone: <?= $bizPhone ?></li>
            <li><i class="fa-solid fa-envelope" aria-hidden="true"></i> Email: <?= $bizEmail ?></li>
            <li><i class="fa-solid fa-clock" aria-hidden="true"></i> Mon - Sat: 10:00 AM - 7:00 PM</li>
          </ul>
          <a href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($settingsMap['biz_address'] ?? 'Rajkot Gujarat') ?>" target="_blank" rel="noopener">View on Google Maps</a>
        </div>
        <div class="contact-map-pin" aria-hidden="true"><i class="fa-solid fa-location-dot"></i><span>Rajkot</span></div>
      </div>
    </div>
  </section>

  <section class="contact-faq-section">
    <div class="contact-showcase-container contact-faq-grid">
      <div>
        <div class="contact-section-title contact-section-title-compact">
          <h2>Frequently Asked Questions</h2>
          <a href="/contact">View All FAQs</a>
        </div>
        <div class="contact-showcase-faqs">
          <details><summary>What is your minimum order quantity?</summary><p>Minimum quantity depends on the product, material and print process. Share your requirement and our team will guide you.</p></details>
          <details><summary>How long does delivery take?</summary><p>Delivery time depends on artwork approval, product type, quantity, finishing and location.</p></details>
          <details><summary>Do you offer design support?</summary><p>Yes, we can help with design guidance and artwork preparation for many print products.</p></details>
          <details><summary>Can I get a sample before placing a bulk order?</summary><p>For selected products and bulk requirements, sample or proof options can be discussed with our team.</p></details>
        </div>
      </div>
      <aside class="contact-design-offer">
        <div>
          <h2>Get <span>FREE Design</span><br>on Your First Order!</h2>
          <a href="https://wa.me/<?= $bizWa ?>?text=<?= $waText ?>" target="_blank" rel="noopener">Get Free Design</a>
        </div>
        <i class="fa-solid fa-gift" aria-hidden="true"></i>
      </aside>
    </div>
  </section>
</main>
<script>
(function(){
  const form = document.getElementById('contactQuickForm');
  if (!form) return;
  form.addEventListener('submit', function(event) {
    event.preventDefault();
    const data = new FormData(form);
    const lines = [
      'Hello <?= $bizName ?>, I want to discuss a print requirement.',
      'Name: ' + (data.get('name') || '-'),
      'Email: ' + (data.get('email') || '-'),
      'Phone: ' + (data.get('phone') || '-'),
      'Subject: ' + (data.get('subject') || '-'),
      'Message: ' + (data.get('message') || '-')
    ];
    window.open('https://wa.me/<?= $bizWa ?>?text=' + encodeURIComponent(lines.join('\n')), '_blank', 'noopener');
  });
})();
</script>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>

<?php
$pageTitle = 'Contact RCS Graphic — Print Order Help & Bulk Quotes';
$pageDesc = 'Contact RCS Graphic for printing support, bulk quotes, design guidance, order help, WhatsApp support and delivery questions.';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';

$settingsMap = is_array($settingsMap ?? null) ? $settingsMap : [];
$bizName = htmlspecialchars($settingsMap['biz_name'] ?? 'RCS Graphic', ENT_QUOTES, 'UTF-8');
$bizPhoneRaw = (string)($settingsMap['biz_phone'] ?? '+91 98765 43210');
$bizPhone = htmlspecialchars($bizPhoneRaw, ENT_QUOTES, 'UTF-8');
$bizPhoneHref = preg_replace('/\D+/', '', $bizPhoneRaw);
$bizWa = htmlspecialchars($settingsMap['biz_whatsapp'] ?? '919876543210', ENT_QUOTES, 'UTF-8');
$bizEmail = htmlspecialchars($settingsMap['biz_email'] ?? 'hello@rcsgraphic.in', ENT_QUOTES, 'UTF-8');
$bizAddr = htmlspecialchars($settingsMap['biz_address'] ?? 'Rajkot, Gujarat', ENT_QUOTES, 'UTF-8');
$waText = rawurlencode('Hello RCS Graphic, I need help with a printing requirement.');
?>
<main class="info-page contact-page">
  <section class="info-hero">
    <div class="info-container">
      <nav class="info-breadcrumb" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span>Contact Us</span></nav>
      <div class="info-hero-grid">
        <div>
          <div class="sec-ey">Contact RCS Graphic</div>
          <h1>Need help with printing, design or bulk order planning?</h1>
          <p>Reach out to our team for product selection, artwork checks, pricing guidance, order support and delivery coordination.</p>
          <div class="info-badges"><span>WhatsApp Support</span><span>Bulk Quote Help</span><span>Design Guidance</span></div>
        </div>
        <aside class="info-visual-card contact-visual-card">
          <div class="info-print-stack" aria-hidden="true">
            <img class="info-mockup info-mockup-1" src="/assets/images/sample-products/business-cards/business-cards-1.svg" alt="" loading="eager" decoding="async">
            <img class="info-mockup info-mockup-2" src="/assets/images/sample-products/brochures/brochures-1.svg" alt="" loading="lazy" decoding="async">
            <img class="info-mockup info-mockup-3" src="/assets/images/sample-products/banners/banners-1.svg" alt="" loading="lazy" decoding="async">
          </div>
          <div class="info-visual-caption">
            <strong>Fastest support</strong>
            <p>For urgent order questions, WhatsApp is usually the quickest way to reach our team.</p>
          </div>
          <div class="info-actions">
            <a class="btn btn-blue" href="https://wa.me/<?= $bizWa ?>?text=<?= $waText ?>" target="_blank" rel="noopener">Chat on WhatsApp</a>
            <a class="btn btn-outline" href="tel:<?= htmlspecialchars($bizPhoneHref, ENT_QUOTES, 'UTF-8') ?>">Call Now</a>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="info-section">
    <div class="info-container contact-grid">
      <article class="contact-card"><i class="fa-solid fa-phone" aria-hidden="true"></i><h2>Phone</h2><p><?= $bizPhone ?></p><a href="tel:<?= htmlspecialchars($bizPhoneHref, ENT_QUOTES, 'UTF-8') ?>">Call us</a></article>
      <article class="contact-card"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i><h2>WhatsApp</h2><p>Send your design brief, order details or product question.</p><a href="https://wa.me/<?= $bizWa ?>?text=<?= $waText ?>" target="_blank" rel="noopener">Start chat</a></article>
      <article class="contact-card"><i class="fa-regular fa-envelope" aria-hidden="true"></i><h2>Email</h2><p><?= $bizEmail ?></p><a href="mailto:<?= $bizEmail ?>">Email us</a></article>
      <article class="contact-card"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><h2>Address</h2><p><?= $bizAddr ?></p><a href="/shipping-policy">Delivery info</a></article>
    </div>
  </section>

  <section class="info-section info-section-soft">
    <div class="info-container contact-layout">
      <form class="contact-form" id="contactQuickForm" action="https://wa.me/<?= $bizWa ?>" method="get" target="_blank">
        <div class="sec-ey">Quick Enquiry</div>
        <h2>Tell us what you need</h2>
        <p>Use this quick form to open WhatsApp. You can then share artwork, quantity, product size, material preference and delivery details directly with our team. Clear information helps us suggest the right product and prepare a faster quote.</p>
        <label>Name<input type="text" name="name" placeholder="Your name"></label>
        <label>Phone<input type="tel" name="phone" placeholder="Your phone number"></label>
        <label>Requirement<select name="requirement"><option>Business Cards</option><option>Flyers / Pamphlets</option><option>Brochures</option><option>Banners / Posters</option><option>Stationery</option><option>Bulk / Custom Print</option></select></label>
        <label>Message<textarea name="message" rows="4" placeholder="Quantity, size, deadline or design help needed"></textarea></label>
        <button class="btn btn-blue" type="submit">Continue on WhatsApp</button>
      </form>

      <aside class="contact-help-card">
        <h2>Before contacting us</h2>
        <ul>
          <li>Keep product type and quantity ready.</li>
          <li>Share print size, paper/material preference if known.</li>
          <li>Upload final artwork or explain your design brief.</li>
          <li>Mention delivery city and expected timeline.</li>
          <li>For repeat orders, share an old bill, sample photo or previous artwork reference.</li>
          <li>For events or campaigns, tell us the deadline so production and dispatch can be planned properly.</li>
        </ul>
        <div class="contact-hours"><strong>Working Hours</strong><span>Monday - Saturday</span><span>10:00 AM - 7:00 PM</span></div>
      </aside>
    </div>
  </section>

  <section class="info-section">
    <div class="info-container info-section-head">
      <div class="sec-ey">FAQs</div>
      <h2>Common contact questions</h2>
      <div class="contact-faqs">
        <details open><summary>Can I send artwork on WhatsApp?</summary><p>Yes. You can share your artwork, logo, reference image or design brief on WhatsApp for quick review.</p></details>
        <details><summary>Do you provide design support?</summary><p>Yes, our team can guide you with basic artwork preparation and design support depending on the product and requirement.</p></details>
        <details><summary>How do I get a bulk quote?</summary><p>Share product type, quantity, size, material preference and delivery location. We will guide you with suitable options.</p></details>
      </div>
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
      'Phone: ' + (data.get('phone') || '-'),
      'Requirement: ' + (data.get('requirement') || '-'),
      'Message: ' + (data.get('message') || '-')
    ];
    window.open('https://wa.me/<?= $bizWa ?>?text=' + encodeURIComponent(lines.join('\n')), '_blank', 'noopener');
  });
})();
</script>
<?php include INCLUDE_PATH . '/partials/site-footer.php'; ?>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>

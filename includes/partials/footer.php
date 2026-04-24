<div id="chatbotWidget" class="chatbot-widget" aria-live="polite">
  <button id="chatbotToggle" class="chatbot-toggle" type="button" aria-expanded="false" aria-controls="chatbotPanel">
    💬 Ask us
  </button>

  <section id="chatbotPanel" class="chatbot-panel" hidden>
    <header class="chatbot-head">
      <strong>RCS Assistant</strong>
      <button type="button" onclick="toggleChatbot(false)" aria-label="Close chatbot">✕</button>
    </header>

    <div id="chatbotMsgs" class="chatbot-msgs">
      <div class="chatbot-msg bot">Hi! I can help with products, pricing, shipping, and checkout.</div>
    </div>

    <form id="chatbotForm" class="chatbot-form">
      <input id="chatbotInput" type="text" maxlength="500" placeholder="Type your question..." autocomplete="off">
      <button type="submit" class="btn btn-blue btn-sm">Send</button>
    </form>

    <a class="chatbot-human" href="javascript:void(0)" onclick="chatbotHandoff()">Talk to Human (WhatsApp)</a>
  </section>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</body>
</html>

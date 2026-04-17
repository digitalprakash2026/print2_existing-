<div class="toast-wrap" id="tw"></div>
<div class="pay-ov" id="payOv">
  <div class="pay-spin"></div>
  <div class="pay-txt" id="payTxt">Processing…</div>
  <div class="pay-sub">Please don't close this window</div>
</div>
<div class="cart-backdrop" id="cartBack" onclick="closeCart()"></div>

<div class="cart-drawer" id="cartDrawer">
  <div class="cart-hdr">
    <div class="cart-title">🛒 Shopping Cart</div>
    <button onclick="closeCart()" class="modal-cls">✕</button>
  </div>
  <div class="cart-body" id="cartBody">
    <div style="text-align:center;padding:40px 20px;color:var(--text2)">
      <div style="font-size:36px;margin-bottom:8px">🛒</div>
      <div>Loading cart…</div>
    </div>
  </div>
  <div class="cart-footer" id="cartFooter"></div>
</div>

<?php
$pageTitle = 'My Profile — RCS Graphic';
$currentPage = 'profile';
include INCLUDE_PATH . '/partials/head.php';
include INCLUDE_PATH . '/partials/header.php';

$profile = $profile ?? [];
$billing = $profile['billing'] ?? [];
?>
<div style="margin-top:var(--hh);min-height:calc(100vh - var(--hh));background:var(--bg);padding:32px 0 80px">
  <div class="container" style="max-width:760px">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px">
      <div>
        <div style="font-family:var(--fd);font-size:24px;font-weight:700">My Profile</div>
        <div style="font-size:13px;color:var(--text2)">Manage your account and default billing details for future invoices.</div>
      </div>
      <a href="/my-orders" class="btn btn-outline btn-sm">📋 My Orders</a>
    </div>

    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:16px;margin-bottom:16px">
      <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">👤 Basic Details</div>
      <div class="fg"><label>Full Name *</label><input id="p-name" class="fi" value="<?= htmlspecialchars($profile['name'] ?? $user['name'] ?? '') ?>"></div>
      <div class="f2">
        <div class="fg"><label>Email *</label><input id="p-email" type="email" class="fi" value="<?= htmlspecialchars($profile['email'] ?? $user['email'] ?? '') ?>"></div>
        <div class="fg"><label>Phone *</label><input id="p-phone" type="tel" class="fi" value="<?= htmlspecialchars($profile['phone'] ?? $user['phone'] ?? '') ?>"></div>
      </div>
      <div class="fg" style="margin-bottom:0"><label>Company (optional)</label><input id="p-company" class="fi" value="<?= htmlspecialchars($profile['company'] ?? $user['company'] ?? '') ?>"></div>
    </div>

    <div style="background:var(--white);border-radius:12px;border:1.5px solid var(--border);padding:16px;margin-bottom:16px">
      <div style="font-size:12px;font-weight:700;color:var(--text2);margin-bottom:12px;text-transform:uppercase;letter-spacing:.06em">🧾 Default Billing Details (GST Invoice)</div>
      <?php if (!empty($profile['migration_required'])): ?>
      <div style="background:var(--amber-bg);border:1px solid var(--amber-mid);border-radius:8px;padding:10px 12px;font-size:12px;color:var(--amber);margin-bottom:12px">
        Billing fields are not available yet. Please run the SQL migration shared in the implementation notes.
      </div>
      <?php endif; ?>
      <div class="fg"><label>Legal Business Name</label><input id="pb-legal" class="fi" value="<?= htmlspecialchars($billing['legal_name'] ?? '') ?>" placeholder="ABC Pvt Ltd"></div>
      <div class="fg"><label>GSTIN</label><input id="pb-gst" class="fi" value="<?= htmlspecialchars($billing['gst_no'] ?? '') ?>" placeholder="24ABCDE1234F1Z5" style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()"></div>
      <div class="fg"><label>Billing Address Line 1</label><input id="pb-add1" class="fi" value="<?= htmlspecialchars($billing['address_line1'] ?? '') ?>"></div>
      <div class="fg"><label>Billing Address Line 2</label><input id="pb-add2" class="fi" value="<?= htmlspecialchars($billing['address_line2'] ?? '') ?>"></div>
      <div class="f2">
        <div class="fg"><label>City</label><input id="pb-city" class="fi" value="<?= htmlspecialchars($billing['city'] ?? '') ?>"></div>
        <div class="fg"><label>State</label><input id="pb-state" class="fi" value="<?= htmlspecialchars($billing['state'] ?? '') ?>"></div>
      </div>
      <div class="fg" style="margin-bottom:0"><label>Pincode</label><input id="pb-pin" class="fi" value="<?= htmlspecialchars($billing['pincode'] ?? '') ?>"></div>
    </div>

    <div id="profErr" style="display:none;font-size:12px;color:var(--red);margin-bottom:10px"></div>
    <div id="profOk" style="display:none;font-size:12px;color:var(--green);margin-bottom:10px"></div>
    <button class="btn btn-blue btn-full" style="padding:14px;border-radius:12px" onclick="saveProfile()">💾 Save Profile</button>
  </div>
</div>

<script>
async function saveProfile() {
  const err = document.getElementById('profErr');
  const ok = document.getElementById('profOk');
  err.style.display = 'none';
  ok.style.display = 'none';

  const payload = {
    name: document.getElementById('p-name')?.value.trim() || '',
    email: document.getElementById('p-email')?.value.trim() || '',
    phone: document.getElementById('p-phone')?.value.trim() || '',
    company: document.getElementById('p-company')?.value.trim() || '',
    billing: {
      legal_name: document.getElementById('pb-legal')?.value.trim() || '',
      gst_no: (document.getElementById('pb-gst')?.value || '').trim().toUpperCase(),
      address_line1: document.getElementById('pb-add1')?.value.trim() || '',
      address_line2: document.getElementById('pb-add2')?.value.trim() || '',
      city: document.getElementById('pb-city')?.value.trim() || '',
      state: document.getElementById('pb-state')?.value.trim() || '',
      pincode: document.getElementById('pb-pin')?.value.trim() || '',
    }
  };

  if (!payload.name || !payload.email || !payload.phone) {
    err.textContent = 'Name, email and phone are required.';
    err.style.display = 'block';
    return;
  }

  try {
    const resp = await fetch('/api/profile', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': APP.csrfToken },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    });
    const data = await resp.json();
    if (!data.ok) {
      err.textContent = data.msg || 'Could not update profile.';
      err.style.display = 'block';
      return;
    }
    ok.textContent = data.migration_required
      ? 'Basic profile updated. Billing fields will work after DB migration is applied.'
      : 'Profile updated successfully.';
    ok.style.display = 'block';
  } catch (e) {
    err.textContent = 'Could not update profile right now.';
    err.style.display = 'block';
  }
}
</script>
<?php include INCLUDE_PATH . '/partials/footer.php'; ?>

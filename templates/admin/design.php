<?php
$pageTitle = 'Design Studio — RCS Admin';
$currentAdmPage = 'design';
$themeValues = \Theme\SiteTheme::load();
$themeDefaults = \Theme\SiteTheme::defaults();
$themeSchema = \Theme\SiteTheme::designSchema();
$themeFonts = \Theme\SiteTheme::fontOptions();
$themeHeadingFonts = \Theme\SiteTheme::headingFontOptions();
$themeShadows = \Theme\SiteTheme::shadowOptions();
include __DIR__ . '/layout.php';

$renderField = static function (string $key, string $label) use ($themeValues, $themeDefaults, $themeFonts, $themeHeadingFonts, $themeShadows): void {
    $meta = \Theme\SiteTheme::fieldMeta($key);
    $type = $meta['type'] ?? 'text';
    $value = (string)($themeValues[$key] ?? $themeDefaults[$key] ?? '');
    $default = (string)($themeDefaults[$key] ?? '');
    $safeKey = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
    $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $safeValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $safeDefault = htmlspecialchars($default, ENT_QUOTES, 'UTF-8');
    echo '<label class="ds-field" data-field="' . $safeKey . '">';
    echo '<span><strong>' . $safeLabel . '</strong><em>Default: ' . $safeDefault . '</em></span>';

    if ($type === 'color') {
        echo '<div class="ds-color-row"><input type="color" name="' . $safeKey . '" value="' . $safeValue . '" data-theme-input><input class="fi ds-color-text" value="' . $safeValue . '" data-color-text="' . $safeKey . '" maxlength="7"></div>';
    } elseif ($type === 'font' || $type === 'heading_font') {
        $options = $type === 'font' ? $themeFonts : $themeHeadingFonts;
        echo '<select class="fi fi-sel" name="' . $safeKey . '" data-theme-input>';
        foreach ($options as $option) {
            $selected = $option === $value ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($option, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($option, ENT_QUOTES, 'UTF-8') . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'shadow') {
        echo '<select class="fi fi-sel" name="' . $safeKey . '" data-theme-input>';
        foreach ($themeShadows as $option) {
            $selected = $option === $value ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($option, ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . ucfirst(htmlspecialchars($option, ENT_QUOTES, 'UTF-8')) . '</option>';
        }
        echo '</select>';
    } else {
        $num = (float)str_replace('px', '', $value);
        $min = (int)($meta['min'] ?? 0);
        $max = (int)($meta['max'] ?? 100);
        echo '<div class="ds-range-row"><input type="range" min="' . $min . '" max="' . $max . '" value="' . htmlspecialchars((string)$num, ENT_QUOTES, 'UTF-8') . '" name="' . $safeKey . '" data-theme-input data-px-range><output>' . $safeValue . '</output></div>';
    }
    echo '</label>';
};
?>
<div class="ds-shell" data-design-studio>
  <div class="ds-head">
    <div>
      <div class="adm-pt">🎨 Design Studio</div>
      <p class="ds-sub">Website ka color, fonts, card style, header/footer aur spacing section-wise yahin se change karo. Right side me live website preview dikhega.</p>
    </div>
    <div class="ds-actions">
      <button type="button" class="btn btn-outline" id="dsRefresh">Refresh Preview</button>
      <button type="button" class="btn btn-red" id="dsReset">Reset Default</button>
      <button type="button" class="btn btn-blue" id="dsSave">Save Design ✓</button>
    </div>
  </div>

  <div class="ds-grid">
    <aside class="ds-panel">
      <div class="ds-status" id="dsStatus">Live preview ready</div>
      <?php foreach ($themeSchema as $section => $fields): ?>
        <details class="ds-section" open>
          <summary><?= htmlspecialchars($section) ?></summary>
          <div class="ds-fields">
            <?php foreach ($fields as $key => $label) $renderField($key, $label); ?>
          </div>
        </details>
      <?php endforeach; ?>
    </aside>

    <section class="ds-preview-wrap">
      <div class="ds-preview-top">
        <div>
          <strong>Live Visual Preview</strong>
          <span>Unsaved changes preview only — Save ke baad public website par apply honge.</span>
        </div>
        <div class="ds-devices" role="group" aria-label="Preview size">
          <button type="button" class="act" data-preview-size="desktop">Desktop</button>
          <button type="button" data-preview-size="tablet">Tablet</button>
          <button type="button" data-preview-size="mobile">Mobile</button>
        </div>
      </div>
      <div class="ds-frame-shell" data-preview-frame-shell>
        <iframe id="dsPreview" src="/" title="Website design preview"></iframe>
      </div>
    </section>
  </div>
</div>

<script>
(function(){
  const root = document.querySelector('[data-design-studio]');
  if (!root) return;
  const inputs = Array.from(root.querySelectorAll('[data-theme-input]'));
  const status = document.getElementById('dsStatus');
  const frame = document.getElementById('dsPreview');
  const saveBtn = document.getElementById('dsSave');
  const resetBtn = document.getElementById('dsReset');
  const refreshBtn = document.getElementById('dsRefresh');
  let timer = null;
  let latestCss = <?= json_encode(\Theme\SiteTheme::css($themeValues), JSON_UNESCAPED_SLASHES) ?>;

  function setStatus(text, mode) {
    status.textContent = text;
    status.className = 'ds-status' + (mode ? ' ' + mode : '');
  }
  function collect() {
    const data = {};
    inputs.forEach(input => {
      data[input.name] = input.hasAttribute('data-px-range') ? input.value + 'px' : input.value;
    });
    return data;
  }
  function syncRange(input) {
    if (!input.hasAttribute('data-px-range')) return;
    const out = input.closest('.ds-range-row')?.querySelector('output');
    if (out) out.textContent = input.value + 'px';
  }
  function sendPreview(css) {
    if (!frame.contentWindow) return;
    frame.contentWindow.postMessage({type:'RCS_THEME_PREVIEW', css}, window.location.origin);
  }
  async function previewNow() {
    try {
      const res = await fetch('/admin/api/theme/preview', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify(collect())
      });
      const json = await res.json();
      if (!json.ok) throw new Error(json.msg || 'Preview failed');
      latestCss = json.css;
      sendPreview(latestCss);
      setStatus('Live preview updated', 'ok');
    } catch (err) {
      setStatus('Preview error: ' + err.message, 'bad');
    }
  }
  function schedulePreview() {
    clearTimeout(timer);
    setStatus('Updating preview…');
    timer = setTimeout(previewNow, 180);
  }
  inputs.forEach(input => {
    input.addEventListener('input', () => {
      syncRange(input);
      if (input.type === 'color') {
        const text = root.querySelector('[data-color-text="' + input.name + '"]');
        if (text) text.value = input.value.toUpperCase();
      }
      schedulePreview();
    });
    input.addEventListener('change', schedulePreview);
  });
  root.querySelectorAll('[data-color-text]').forEach(text => {
    text.addEventListener('input', () => {
      if (!/^#[0-9A-Fa-f]{6}$/.test(text.value)) return;
      const picker = root.querySelector('[name="' + text.dataset.colorText + '"]');
      if (picker) picker.value = text.value;
      schedulePreview();
    });
  });
  frame.addEventListener('load', () => setTimeout(() => sendPreview(latestCss), 250));
  refreshBtn.addEventListener('click', () => { frame.contentWindow.location.reload(); setStatus('Preview refreshed'); });
  saveBtn.addEventListener('click', async () => {
    saveBtn.disabled = true;
    setStatus('Saving design…');
    try {
      const res = await fetch('/admin/api/theme', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(collect())});
      const json = await res.json();
      if (!json.ok) throw new Error(json.msg || 'Save failed');
      latestCss = json.css;
      sendPreview(latestCss);
      setStatus('Design saved successfully ✓', 'ok');
    } catch (err) {
      setStatus('Save error: ' + err.message, 'bad');
    } finally {
      saveBtn.disabled = false;
    }
  });
  resetBtn.addEventListener('click', async () => {
    if (!confirm('Reset design to default theme?')) return;
    resetBtn.disabled = true;
    setStatus('Resetting theme…');
    try {
      const res = await fetch('/admin/api/theme/reset', {method:'POST',headers:{'Content-Type':'application/json'},body:'{}'});
      const json = await res.json();
      if (!json.ok) throw new Error(json.msg || 'Reset failed');
      Object.entries(json.theme).forEach(([key, value]) => {
        const input = root.querySelector('[name="' + key + '"]');
        if (!input) return;
        input.value = String(value).replace('px','');
        if (!input.hasAttribute('data-px-range')) input.value = value;
        syncRange(input);
        const text = root.querySelector('[data-color-text="' + key + '"]');
        if (text) text.value = value;
      });
      latestCss = json.css;
      sendPreview(latestCss);
      setStatus('Default design restored ✓', 'ok');
    } catch (err) {
      setStatus('Reset error: ' + err.message, 'bad');
    } finally {
      resetBtn.disabled = false;
    }
  });
  root.querySelectorAll('[data-preview-size]').forEach(btn => {
    btn.addEventListener('click', () => {
      root.querySelectorAll('[data-preview-size]').forEach(b => b.classList.remove('act'));
      btn.classList.add('act');
      root.querySelector('[data-preview-frame-shell]').dataset.size = btn.dataset.previewSize;
    });
  });
})();
</script>
    </div></div></div>
</body></html>

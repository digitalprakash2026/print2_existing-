<?php
$pageTitle = 'Design Studio — RCS Admin';
$currentAdmPage = 'design';
$themeValues = \Theme\SiteTheme::load();
$themeDefaults = \Theme\SiteTheme::defaults();
$themeSchema = \Theme\SiteTheme::designSchema();
$themeFonts = \Theme\SiteTheme::fontOptions();
$themeHeadingFonts = \Theme\SiteTheme::headingFontOptions();
$themeShadows = \Theme\SiteTheme::shadowOptions();
$themeFontWeights = \Theme\SiteTheme::fontWeightOptions();
$themeElementStyles = \Theme\SiteTheme::loadElementStyles();
$themeElementSchema = \Theme\SiteTheme::elementSchema();
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
      <p class="ds-sub">Right preview me kisi section, text, button ya card par click karo. Left side me us exact element ke font, color, spacing, radius aur shadow controls open ho jayenge.</p>
    </div>
    <div class="ds-actions">
      <button type="button" class="btn btn-outline" id="dsRefresh">Refresh Preview</button>
      <button type="button" class="btn btn-red" id="dsReset">Reset All</button>
      <button type="button" class="btn btn-blue" id="dsSave">Save Design ✓</button>
    </div>
  </div>

  <div class="ds-grid">
    <aside class="ds-panel">
      <div class="ds-status" id="dsStatus">Click any editable part in preview</div>

      <details class="ds-section ds-selected-section" open>
        <summary>🎯 Selected Element</summary>
        <div class="ds-selected-box">
          <div class="ds-selected-empty" id="dsSelectedEmpty">Preview me kisi highlighted element par click karein.</div>
          <div class="ds-selected-active" id="dsSelectedActive" hidden>
            <div class="ds-selected-title" id="dsSelectedTitle">Selected element</div>
            <div class="ds-selected-key" id="dsSelectedKey"></div>
            <div class="ds-fields" id="dsElementFields"></div>
            <div class="ds-mini-actions">
              <button type="button" class="btn btn-outline btn-sm" id="dsClearElement">Clear This Element</button>
            </div>
          </div>
        </div>
      </details>

      <?php foreach ($themeSchema as $section => $fields): ?>
        <details class="ds-section">
          <summary><?= htmlspecialchars($section) ?> <small>Global</small></summary>
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
          <span>Hover se editable parts highlight honge. Click se selected element controls open honge.</span>
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
  const selectedEmpty = document.getElementById('dsSelectedEmpty');
  const selectedActive = document.getElementById('dsSelectedActive');
  const selectedTitle = document.getElementById('dsSelectedTitle');
  const selectedKey = document.getElementById('dsSelectedKey');
  const elementFields = document.getElementById('dsElementFields');
  const clearElementBtn = document.getElementById('dsClearElement');
  const elementSchema = <?= json_encode($themeElementSchema, JSON_UNESCAPED_SLASHES) ?>;
  const fontOptions = <?= json_encode($themeFonts, JSON_UNESCAPED_SLASHES) ?>;
  const weightOptions = <?= json_encode($themeFontWeights, JSON_UNESCAPED_SLASHES) ?>;
  const shadowOptions = <?= json_encode($themeShadows, JSON_UNESCAPED_SLASHES) ?>;
  let elementStyles = <?= json_encode($themeElementStyles, JSON_UNESCAPED_SLASHES) ?>;
  let selectedTarget = null;
  let timer = null;
  let latestCss = <?= json_encode(\Theme\SiteTheme::css($themeValues, $themeElementStyles), JSON_UNESCAPED_SLASHES) ?>;

  function setStatus(text, mode) {
    status.textContent = text;
    status.className = 'ds-status' + (mode ? ' ' + mode : '');
  }
  function collect() {
    const data = {element_styles: elementStyles};
    inputs.forEach(input => {
      data[input.name] = input.hasAttribute('data-px-range') ? input.value + 'px' : input.value;
    });
    return data;
  }
  function syncRange(input) {
    const out = input.closest('.ds-range-row')?.querySelector('output');
    if (!out) return;
    out.textContent = input.value + (input.dataset.unit || 'px');
  }
  function sendPreview(css) {
    if (!frame.contentWindow) return;
    frame.contentWindow.postMessage({type:'RCS_THEME_PREVIEW', css}, window.location.origin);
  }
  function enableInspector() {
    if (!frame.contentWindow) return;
    frame.contentWindow.postMessage({type:'RCS_THEME_ENABLE_INSPECTOR', elements: elementSchema}, window.location.origin);
  }
  async function apiJson(url, options) {
    const res = await fetch(url, options);
    const text = await res.text();
    let json = null;
    try {
      json = text ? JSON.parse(text) : null;
    } catch (err) {
      throw new Error('Server returned non-JSON response (' + res.status + '). Please check login/session and PHP error logs.');
    }
    if (!res.ok || !json || json.ok === false) {
      throw new Error((json && json.msg) ? json.msg : ('Request failed with status ' + res.status));
    }
    return json;
  }
  async function previewNow() {
    try {
      const json = await apiJson('/admin/api/theme/preview', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify(collect())
      });
      elementStyles = json.element_styles || elementStyles;
      latestCss = json.css;
      sendPreview(latestCss);
      enableInspector();
      setStatus('Live preview updated', 'ok');
    } catch (err) {
      setStatus('Preview error: ' + err.message, 'bad');
    }
  }
  function schedulePreview() {
    clearTimeout(timer);
    setStatus('Updating preview…');
    timer = setTimeout(previewNow, 160);
  }
  function esc(value) {
    return String(value || '').replace(/[&<>"]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[ch]));
  }
  function currentValue(target, property, meta) {
    const saved = elementStyles[target] && elementStyles[target][property] ? elementStyles[target][property] : '';
    if (meta.type === 'px') return saved ? String(saved).replace('px','') : '';
    return saved;
  }
  function renderElementFields(target) {
    const schema = elementSchema[target];
    if (!schema) return;
    selectedEmpty.hidden = true;
    selectedActive.hidden = false;
    selectedTitle.textContent = schema.label;
    selectedKey.textContent = target;
    elementFields.innerHTML = '';
    Object.entries(schema.controls).forEach(([property, meta]) => {
      const value = currentValue(target, property, meta);
      const field = document.createElement('label');
      field.className = 'ds-field ds-element-field';
      field.innerHTML = '<span><strong>' + esc(meta.label) + '</strong><em>Selected</em></span>';
      if (meta.type === 'color') {
        field.innerHTML += '<div class="ds-color-row"><input type="color" value="' + esc(value || '#000000') + '" data-element-input data-prop="' + esc(property) + '"><input class="fi ds-color-text" value="' + esc(value) + '" data-element-color-text data-prop="' + esc(property) + '" placeholder="#000000" maxlength="7"></div>';
      } else if (meta.type === 'font' || meta.type === 'weight' || meta.type === 'shadow') {
        const options = meta.type === 'font' ? fontOptions : (meta.type === 'weight' ? weightOptions : shadowOptions);
        let html = '<select class="fi fi-sel" data-element-input data-prop="' + esc(property) + '"><option value="">Keep default</option>';
        options.forEach(option => { html += '<option value="' + esc(option) + '"' + (option === value ? ' selected' : '') + '>' + esc(String(option).charAt(0).toUpperCase() + String(option).slice(1)) + '</option>'; });
        field.innerHTML += html + '</select>';
      } else if (meta.type === 'number') {
        field.innerHTML += '<div class="ds-range-row"><input type="range" min="' + esc(meta.min || 1) + '" max="' + esc(meta.max || 2) + '" step="' + esc(meta.step || 0.05) + '" value="' + esc(value || 1.3) + '" data-element-input data-prop="' + esc(property) + '" data-unit=""><output>' + esc(value || 'Default') + '</output></div>';
      } else {
        field.innerHTML += '<div class="ds-range-row"><input type="range" min="' + esc(meta.min || 0) + '" max="' + esc(meta.max || 100) + '" value="' + esc(value || 0) + '" data-element-input data-prop="' + esc(property) + '" data-unit="px"><output>' + (value ? esc(value + 'px') : 'Default') + '</output></div>';
      }
      elementFields.appendChild(field);
    });
  }
  function setElementValue(property, rawValue, type) {
    if (!selectedTarget) return;
    if (!elementStyles[selectedTarget]) elementStyles[selectedTarget] = {};
    let value = rawValue;
    if (type === 'px') value = rawValue === '' ? '' : rawValue + 'px';
    if (value === '' || value === '#000000_EMPTY') {
      delete elementStyles[selectedTarget][property];
    } else {
      elementStyles[selectedTarget][property] = value;
    }
    if (Object.keys(elementStyles[selectedTarget]).length === 0) delete elementStyles[selectedTarget];
    schedulePreview();
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
  elementFields.addEventListener('input', (event) => {
    const input = event.target.closest('[data-element-input], [data-element-color-text]');
    if (!input || !selectedTarget) return;
    const prop = input.dataset.prop;
    const meta = elementSchema[selectedTarget].controls[prop];
    if (input.matches('[data-element-color-text]')) {
      if (input.value !== '' && !/^#[0-9A-Fa-f]{6}$/.test(input.value)) return;
      const picker = elementFields.querySelector('[data-element-input][data-prop="' + prop + '"]');
      if (picker && input.value) picker.value = input.value;
      setElementValue(prop, input.value, meta.type);
      return;
    }
    if (input.type === 'color') {
      const text = elementFields.querySelector('[data-element-color-text][data-prop="' + prop + '"]');
      if (text) text.value = input.value.toUpperCase();
    }
    syncRange(input);
    setElementValue(prop, input.value, meta.type);
  });
  clearElementBtn.addEventListener('click', () => {
    if (!selectedTarget) return;
    delete elementStyles[selectedTarget];
    renderElementFields(selectedTarget);
    schedulePreview();
  });
  window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin || !event.data || event.data.type !== 'RCS_THEME_ELEMENT_SELECTED') return;
    selectedTarget = event.data.target;
    renderElementFields(selectedTarget);
    setStatus('Editing: ' + (event.data.label || selectedTarget), 'ok');
  });
  frame.addEventListener('load', () => setTimeout(() => { sendPreview(latestCss); enableInspector(); }, 250));
  refreshBtn.addEventListener('click', () => { frame.contentWindow.location.reload(); setStatus('Preview refreshed'); });
  saveBtn.addEventListener('click', async () => {
    saveBtn.disabled = true;
    setStatus('Saving design…');
    try {
      const json = await apiJson('/admin/api/theme', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(collect())});
      elementStyles = json.element_styles || elementStyles;
      latestCss = json.css;
      sendPreview(latestCss);
      enableInspector();
      setStatus('Design saved successfully ✓', 'ok');
    } catch (err) {
      setStatus('Save error: ' + err.message, 'bad');
    } finally {
      saveBtn.disabled = false;
    }
  });
  resetBtn.addEventListener('click', async () => {
    if (!confirm('Reset all global and element design settings to default theme?')) return;
    resetBtn.disabled = true;
    setStatus('Resetting theme…');
    try {
      const json = await apiJson('/admin/api/theme/reset', {method:'POST',headers:{'Content-Type':'application/json'},body:'{}'});
      elementStyles = json.element_styles || {};
      Object.entries(json.theme).forEach(([key, value]) => {
        const input = root.querySelector('[name="' + key + '"]');
        if (!input) return;
        input.value = String(value).replace('px','');
        if (!input.hasAttribute('data-px-range')) input.value = value;
        syncRange(input);
        const text = root.querySelector('[data-color-text="' + key + '"]');
        if (text) text.value = value;
      });
      if (selectedTarget) renderElementFields(selectedTarget);
      latestCss = json.css;
      sendPreview(latestCss);
      enableInspector();
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

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
      <button type="button" class="btn btn-outline" id="dsUndo" disabled>Undo</button>
      <button type="button" class="btn btn-outline" id="dsRedo" disabled>Redo</button>
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
  const undoBtn = document.getElementById('dsUndo');
  const redoBtn = document.getElementById('dsRedo');
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
  let elementStyles = <?= json_encode($themeElementStyles, JSON_UNESCAPED_SLASHES) ?> || {};
  let computedStyles = {};
  let selectedTarget = null;
  let timer = null;
  let latestCss = <?= json_encode(\Theme\SiteTheme::css($themeValues, $themeElementStyles), JSON_UNESCAPED_SLASHES) ?>;
  let undoStack = [];
  let redoStack = [];
  let restoringHistory = false;

  function clone(value) { return JSON.parse(JSON.stringify(value || {})); }
  function setStatus(text, mode) {
    status.textContent = text;
    status.className = 'ds-status' + (mode ? ' ' + mode : '');
  }
  function collect() {
    const data = {element_styles: clone(elementStyles)};
    inputs.forEach(input => {
      data[input.name] = input.hasAttribute('data-px-range') ? input.value + 'px' : input.value;
    });
    return data;
  }
  function applyState(state) {
    if (!state) return;
    elementStyles = clone(state.element_styles);
    Object.entries(state).forEach(([key, value]) => {
      if (key === 'element_styles') return;
      const input = root.querySelector('[name="' + key + '"]');
      if (!input) return;
      input.value = input.hasAttribute('data-px-range') ? String(value).replace('px','') : value;
      syncRange(input);
      const text = root.querySelector('[data-color-text="' + key + '"]');
      if (text) text.value = value;
    });
    if (selectedTarget) renderElementFields(selectedTarget);
  }
  function updateHistoryButtons() {
    undoBtn.disabled = undoStack.length === 0;
    redoBtn.disabled = redoStack.length === 0;
  }
  function pushUndo() {
    if (restoringHistory) return;
    undoStack.push(clone(collect()));
    if (undoStack.length > 50) undoStack.shift();
    redoStack = [];
    updateHistoryButtons();
  }
  function undo() {
    if (!undoStack.length) return;
    restoringHistory = true;
    redoStack.push(clone(collect()));
    applyState(undoStack.pop());
    restoringHistory = false;
    updateHistoryButtons();
    schedulePreview('Undo applied');
  }
  function redo() {
    if (!redoStack.length) return;
    restoringHistory = true;
    undoStack.push(clone(collect()));
    applyState(redoStack.pop());
    restoringHistory = false;
    updateHistoryButtons();
    schedulePreview('Redo applied');
  }
  function syncRange(input) {
    const out = input.closest('.ds-range-row')?.querySelector('output');
    if (!out) return;
    const unit = input.dataset.unit || 'px';
    out.textContent = input.value === '' ? 'Default' : input.value + unit;
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
    try { json = text ? JSON.parse(text) : null; }
    catch (err) { throw new Error('Server returned non-JSON response (' + res.status + '). Please check login/session and PHP error logs.'); }
    if (!res.ok || !json || json.ok === false) throw new Error((json && json.msg) ? json.msg : ('Request failed with status ' + res.status));
    return json;
  }
  async function previewNow(message) {
    try {
      const json = await apiJson('/admin/api/theme/preview', {
        method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(collect())
      });
      elementStyles = json.element_styles || elementStyles;
      latestCss = json.css;
      sendPreview(latestCss);
      enableInspector();
      const meta = json.meta || {};
      const cssLen = meta.css_length || (latestCss ? latestCss.length : 0);
      const dropped = meta.dropped_element_style_count ? (' · dropped targets: ' + meta.dropped_element_style_count) : '';
      setStatus((message || 'Live preview updated') + ' · CSS ' + cssLen + ' chars' + dropped, 'ok');
    } catch (err) { setStatus('Preview error: ' + err.message, 'bad'); }
  }
  function schedulePreview(message) {
    clearTimeout(timer);
    setStatus('Updating preview…');
    timer = setTimeout(() => previewNow(message), 160);
  }
  function esc(value) { return String(value ?? '').replace(/[&<>"]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[ch])); }
  function pxNumber(value) {
    const num = parseFloat(String(value || '').replace('px',''));
    return Number.isFinite(num) ? String(Math.round(num * 100) / 100) : '';
  }
  function currentValue(target, property, meta) {
    const saved = elementStyles[target] && elementStyles[target][property] ? elementStyles[target][property] : '';
    if (saved) return meta.type === 'px' ? pxNumber(saved) : saved;
    const computed = computedStyles[target] && computedStyles[target][property] ? computedStyles[target][property] : '';
    if (meta.type === 'px') return pxNumber(computed);
    if (meta.type === 'font') {
      return fontOptions.includes(computed) ? computed : '';
    }
    if (meta.type === 'weight') {
      return weightOptions.includes(String(computed)) ? String(computed) : '';
    }
    if (meta.type === 'shadow') {
      return shadowOptions.includes(computed) ? computed : '';
    }
    return computed || '';
  }
  function isOverridden(target, property) {
    return !!(elementStyles[target] && elementStyles[target][property]);
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
      const overridden = isOverridden(target, property);
      const field = document.createElement('label');
      field.className = 'ds-field ds-element-field' + (overridden ? ' has-override' : '');
      const badge = overridden ? 'Override active' : (value ? 'Current style' : 'Keep default');
      field.innerHTML = '<span><strong>' + esc(meta.label) + '</strong><em>' + esc(badge) + '</em></span>';
      if (meta.type === 'color') {
        field.innerHTML += '<div class="ds-color-row"><input type="color" value="' + esc(value || '#000000') + '" data-element-input data-prop="' + esc(property) + '"><input class="fi ds-color-text" value="' + esc(value) + '" data-element-color-text data-prop="' + esc(property) + '" placeholder="#000000" maxlength="7"></div>';
      } else if (meta.type === 'font' || meta.type === 'weight' || meta.type === 'shadow') {
        const options = meta.type === 'font' ? fontOptions : (meta.type === 'weight' ? weightOptions : shadowOptions);
        let html = '<select class="fi fi-sel" data-element-input data-prop="' + esc(property) + '"><option value="">Keep default</option>';
        options.forEach(option => { html += '<option value="' + esc(option) + '"' + (option === value ? ' selected' : '') + '>' + esc(String(option).charAt(0).toUpperCase() + String(option).slice(1)) + '</option>'; });
        field.innerHTML += html + '</select>';
      } else if (meta.type === 'number') {
        field.innerHTML += '<div class="ds-range-row"><input type="range" min="' + esc(meta.min || 1) + '" max="' + esc(meta.max || 2) + '" step="' + esc(meta.step || 0.05) + '" value="' + esc(value || 1.3) + '" data-element-input data-prop="' + esc(property) + '" data-unit=""><output>' + esc(value || 'Default') + '</output></div>';
      } else if (property === 'fontSize') {
        const safeValue = value || 16;
        const presets = Array.isArray(meta.presets) ? meta.presets : [12,14,16,18,20,24,28,32,40,48,56,64];
        field.innerHTML += '<div class="ds-font-size-control"><div class="ds-range-row"><input type="range" min="' + esc(meta.min || 10) + '" max="' + esc(meta.max || 90) + '" value="' + esc(safeValue) + '" data-element-input data-prop="' + esc(property) + '" data-unit="px"><output>' + esc(safeValue) + 'px</output></div><div class="ds-number-row"><input class="fi" type="number" min="' + esc(meta.min || 10) + '" max="' + esc(meta.max || 90) + '" value="' + esc(safeValue) + '" data-element-number data-prop="' + esc(property) + '"><span>px</span></div><div class="ds-presets">' + presets.map(p => '<button type="button" data-font-preset="' + esc(p) + '" data-prop="' + esc(property) + '">' + esc(p) + '</button>').join('') + '</div></div>';
      } else {
        const safeValue = value || 0;
        field.innerHTML += '<div class="ds-range-row"><input type="range" min="' + esc(meta.min || 0) + '" max="' + esc(meta.max || 100) + '" value="' + esc(safeValue) + '" data-element-input data-prop="' + esc(property) + '" data-unit="px"><output>' + (value ? esc(value + 'px') : 'Default') + '</output></div>';
      }
      field.innerHTML += '<button type="button" class="ds-clear-prop" data-clear-prop="' + esc(property) + '">Clear override</button>';
      elementFields.appendChild(field);
    });
  }
  function setElementValue(property, rawValue, type, skipHistory) {
    if (!selectedTarget) return;
    if (!skipHistory) pushUndo();
    if (!elementStyles[selectedTarget]) elementStyles[selectedTarget] = {};
    let value = String(rawValue ?? '').trim();
    if (type === 'px') value = value === '' ? '' : value + 'px';
    const current = elementStyles[selectedTarget][property] || '';
    if (current === value) return;
    if (value === '') delete elementStyles[selectedTarget][property];
    else elementStyles[selectedTarget][property] = value;
    if (Object.keys(elementStyles[selectedTarget]).length === 0) delete elementStyles[selectedTarget];
    schedulePreview();
  }
  function clearProperty(property) {
    if (!selectedTarget || !elementStyles[selectedTarget] || !elementStyles[selectedTarget][property]) return;
    pushUndo();
    delete elementStyles[selectedTarget][property];
    if (Object.keys(elementStyles[selectedTarget]).length === 0) delete elementStyles[selectedTarget];
    renderElementFields(selectedTarget);
    schedulePreview('Override cleared');
  }

  function armGlobalUndo(input) {
    if (input.dataset.undoArmed === '1') return;
    pushUndo();
    input.dataset.undoArmed = '1';
  }
  inputs.forEach(input => {
    input.addEventListener('focus', () => armGlobalUndo(input));
    input.addEventListener('pointerdown', () => armGlobalUndo(input));
    input.addEventListener('blur', () => { input.dataset.undoArmed = ''; });
    input.addEventListener('input', () => {
      syncRange(input);
      if (input.type === 'color') {
        const text = root.querySelector('[data-color-text="' + input.name + '"]');
        if (text) text.value = input.value.toUpperCase();
      }
      schedulePreview();
    });
    input.addEventListener('change', () => {
      if (input.tagName !== 'SELECT') return;
      armGlobalUndo(input);
      schedulePreview();
      input.dataset.undoArmed = '';
    });
  });
  root.querySelectorAll('[data-color-text]').forEach(text => {
    text.addEventListener('focus', () => armGlobalUndo(text));
    text.addEventListener('pointerdown', () => armGlobalUndo(text));
    text.addEventListener('blur', () => { text.dataset.undoArmed = ''; });
    text.addEventListener('input', () => {
      if (!/^#[0-9A-Fa-f]{6}$/.test(text.value)) return;
      const picker = root.querySelector('[name="' + text.dataset.colorText + '"]');
      if (picker) picker.value = text.value;
      schedulePreview();
    });
  });
  elementFields.addEventListener('input', (event) => {
    const number = event.target.closest('[data-element-number]');
    if (number && selectedTarget) {
      const prop = number.dataset.prop;
      const range = elementFields.querySelector('[data-element-input][data-prop="' + prop + '"][type="range"]');
      if (range) { range.value = number.value; syncRange(range); }
      setElementValue(prop, number.value, elementSchema[selectedTarget].controls[prop].type);
      return;
    }
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
    if (input.type === 'range') {
      syncRange(input);
      const num = elementFields.querySelector('[data-element-number][data-prop="' + prop + '"]');
      if (num) num.value = input.value;
    }
    setElementValue(prop, input.value, meta.type);
  });
  elementFields.addEventListener('change', (event) => {
    const input = event.target.closest('[data-element-input]');
    if (!input || !selectedTarget || input.tagName !== 'SELECT') return;
    const prop = input.dataset.prop;
    const meta = elementSchema[selectedTarget].controls[prop];
    setElementValue(prop, input.value, meta.type);
  });
  elementFields.addEventListener('click', (event) => {
    const clear = event.target.closest('[data-clear-prop]');
    if (clear) { clearProperty(clear.dataset.clearProp); return; }
    const preset = event.target.closest('[data-font-preset]');
    if (preset && selectedTarget) {
      const prop = preset.dataset.prop;
      const value = preset.dataset.fontPreset;
      const range = elementFields.querySelector('[data-element-input][data-prop="' + prop + '"][type="range"]');
      const number = elementFields.querySelector('[data-element-number][data-prop="' + prop + '"]');
      if (range) { range.value = value; syncRange(range); }
      if (number) number.value = value;
      setElementValue(prop, value, 'px');
    }
  });
  clearElementBtn.addEventListener('click', () => {
    if (!selectedTarget || !elementStyles[selectedTarget]) return;
    pushUndo();
    delete elementStyles[selectedTarget];
    renderElementFields(selectedTarget);
    schedulePreview('Selected element cleared');
  });
  window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin || !event.data) return;
    if (event.data.type === 'RCS_THEME_PREVIEW_APPLIED') {
      setStatus('Preview applied in iframe · CSS ' + (event.data.cssLength || 0) + ' chars', 'ok');
      return;
    }
    if (event.data.type !== 'RCS_THEME_ELEMENT_SELECTED') return;
    selectedTarget = event.data.target;
    computedStyles[selectedTarget] = event.data.computed || {};
    renderElementFields(selectedTarget);
    setStatus('Editing: ' + (event.data.label || selectedTarget), 'ok');
  });
  frame.addEventListener('load', () => setTimeout(() => { sendPreview(latestCss); enableInspector(); }, 250));
  refreshBtn.addEventListener('click', () => { frame.contentWindow.location.reload(); setStatus('Preview refreshed'); });
  undoBtn.addEventListener('click', undo);
  redoBtn.addEventListener('click', redo);
  document.addEventListener('keydown', (event) => {
    if (!(event.ctrlKey || event.metaKey)) return;
    if (event.key.toLowerCase() === 'z' && event.shiftKey) { event.preventDefault(); redo(); }
    else if (event.key.toLowerCase() === 'z') { event.preventDefault(); undo(); }
    else if (event.key.toLowerCase() === 'y') { event.preventDefault(); redo(); }
  });
  saveBtn.addEventListener('click', async () => {
    saveBtn.disabled = true;
    setStatus('Saving design…');
    try {
      const json = await apiJson('/admin/api/theme', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(collect())});
      elementStyles = json.element_styles || elementStyles;
      latestCss = json.css;
      undoStack = [];
      redoStack = [];
      updateHistoryButtons();
      sendPreview(latestCss);
      enableInspector();
      if (selectedTarget) renderElementFields(selectedTarget);
      const meta = json.meta || {};
      const storage = meta.storage ? (' · ' + meta.storage) : '';
      const count = meta.element_style_count !== undefined ? (' · targets ' + meta.element_style_count) : '';
      setStatus('Design saved successfully ✓' + storage + count, 'ok');
    } catch (err) { setStatus('Save error: ' + err.message, 'bad'); }
    finally { saveBtn.disabled = false; }
  });
  resetBtn.addEventListener('click', async () => {
    if (!confirm('Reset all global and element design settings to default theme?')) return;
    pushUndo();
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
    } catch (err) { setStatus('Reset error: ' + err.message, 'bad'); }
    finally { resetBtn.disabled = false; }
  });
  root.querySelectorAll('[data-preview-size]').forEach(btn => {
    btn.addEventListener('click', () => {
      root.querySelectorAll('[data-preview-size]').forEach(b => b.classList.remove('act'));
      btn.classList.add('act');
      root.querySelector('[data-preview-frame-shell]').dataset.size = btn.dataset.previewSize;
    });
  });
  updateHistoryButtons();
})();
</script>
    </div></div></div>
</body></html>

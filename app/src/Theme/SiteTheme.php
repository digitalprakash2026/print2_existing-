<?php

declare(strict_types=1);

namespace Theme;

class SiteTheme
{
    public const PREFIX = 'theme_';
    public const ELEMENT_STYLES_KEY = 'theme_element_styles';

    private const FIELDS = [
        'theme_primary_color' => ['type' => 'color', 'default' => '#2563EB'],
        'theme_primary_dark' => ['type' => 'color', 'default' => '#1D4ED8'],
        'theme_accent_color' => ['type' => 'color', 'default' => '#EA580C'],
        'theme_success_color' => ['type' => 'color', 'default' => '#0F766E'],
        'theme_header_bg' => ['type' => 'color', 'default' => '#4B148C'],
        'theme_footer_bg' => ['type' => 'color', 'default' => '#4D168E'],
        'theme_body_bg' => ['type' => 'color', 'default' => '#F8FAFC'],
        'theme_text_color' => ['type' => 'color', 'default' => '#0F172A'],
        'theme_muted_color' => ['type' => 'color', 'default' => '#475569'],
        'theme_font_family' => ['type' => 'font', 'default' => 'Inter'],
        'theme_heading_font' => ['type' => 'heading_font', 'default' => 'Merriweather'],
        'theme_base_font_size' => ['type' => 'px', 'default' => '15px', 'min' => 13, 'max' => 18],
        'theme_button_radius' => ['type' => 'px', 'default' => '12px', 'min' => 4, 'max' => 24],
        'theme_card_radius' => ['type' => 'px', 'default' => '14px', 'min' => 6, 'max' => 28],
        'theme_logo_height' => ['type' => 'px', 'default' => '64px', 'min' => 44, 'max' => 86],
        'theme_nav_height' => ['type' => 'px', 'default' => '92px', 'min' => 72, 'max' => 108],
        'theme_hero_title_size' => ['type' => 'px', 'default' => '52px', 'min' => 36, 'max' => 68],
        'theme_section_spacing' => ['type' => 'px', 'default' => '64px', 'min' => 36, 'max' => 96],
        'theme_card_shadow' => ['type' => 'shadow', 'default' => 'soft'],
    ];

    private const FONTS = ['Inter', 'Poppins', 'Merriweather'];
    private const HEADING_FONTS = ['Merriweather', 'Poppins', 'Inter'];
    private const FONT_WEIGHTS = ['400', '500', '600', '700', '800', '900'];
    private const SHADOWS = [
        'none' => 'none',
        'soft' => '0 8px 20px rgba(15,23,42,.04)',
        'premium' => '0 16px 36px rgba(37,99,235,.13)',
    ];

    private const STYLE_FIELDS = [
        'fontFamily' => ['type' => 'font', 'css' => 'font-family', 'label' => 'Font family'],
        'fontSize' => ['type' => 'px', 'css' => 'font-size', 'label' => 'Font size', 'min' => 10, 'max' => 90, 'presets' => [12, 14, 16, 18, 20, 24, 28, 32, 40, 48, 56, 64]],
        'fontWeight' => ['type' => 'weight', 'css' => 'font-weight', 'label' => 'Font weight'],
        'lineHeight' => ['type' => 'number', 'css' => 'line-height', 'label' => 'Line height', 'min' => 1, 'max' => 2, 'step' => 0.05],
        'color' => ['type' => 'color', 'css' => 'color', 'label' => 'Text color'],
        'backgroundColor' => ['type' => 'color', 'css' => 'background-color', 'label' => 'Background'],
        'borderColor' => ['type' => 'color', 'css' => 'border-color', 'label' => 'Border color'],
        'borderRadius' => ['type' => 'px', 'css' => 'border-radius', 'label' => 'Border radius', 'min' => 0, 'max' => 48],
        'paddingTop' => ['type' => 'px', 'css' => 'padding-top', 'label' => 'Padding top', 'min' => 0, 'max' => 120],
        'paddingBottom' => ['type' => 'px', 'css' => 'padding-bottom', 'label' => 'Padding bottom', 'min' => 0, 'max' => 120],
        'paddingLeft' => ['type' => 'px', 'css' => 'padding-left', 'label' => 'Padding left', 'min' => 0, 'max' => 120],
        'paddingRight' => ['type' => 'px', 'css' => 'padding-right', 'label' => 'Padding right', 'min' => 0, 'max' => 120],
        'marginBottom' => ['type' => 'px', 'css' => 'margin-bottom', 'label' => 'Bottom space', 'min' => 0, 'max' => 90],
        'boxShadow' => ['type' => 'shadow', 'css' => 'box-shadow', 'label' => 'Shadow'],
    ];

    private const ELEMENTS = [
        'header.topbar' => ['label' => 'Header Topbar', 'selector' => '[data-design-target="header.topbar"],.topbar', 'controls' => ['backgroundColor', 'color', 'fontSize', 'paddingTop', 'paddingBottom']],
        'header.navbar' => ['label' => 'Header Navbar', 'selector' => '[data-design-target="header.navbar"],.navbar', 'controls' => ['backgroundColor', 'paddingTop', 'paddingBottom', 'boxShadow']],
        'header.logo' => ['label' => 'Header Logo', 'selector' => '[data-design-target="header.logo"],.brand-img', 'controls' => ['borderRadius']],
        'header.nav_links' => ['label' => 'Navigation Links', 'selector' => '[data-design-target="header.nav_links"],.nav-link', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'color']],
        'home.banner' => ['label' => 'Home Banner Slider', 'selector' => '[data-design-target="home.banner"],.banner-slider', 'controls' => ['borderRadius', 'boxShadow', 'marginBottom']],
        'home.banner.title' => ['label' => 'Banner Title', 'selector' => '[data-design-target="home.banner.title"],.bs-title', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'color', 'marginBottom']],
        'home.banner.subtitle' => ['label' => 'Banner Subtitle', 'selector' => '[data-design-target="home.banner.subtitle"],.bs-sub', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'color', 'marginBottom']],
        'home.banner.buttons' => ['label' => 'Banner Buttons', 'selector' => '[data-design-target="home.banner.buttons"],.bs-cta-primary,.bs-cta-wa', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'color', 'backgroundColor', 'borderRadius', 'paddingTop', 'paddingBottom', 'paddingLeft', 'paddingRight']],
        'site.buttons' => ['label' => 'All Buttons (Global Group)', 'selector' => '.btn,.deal-promo-btn,.deal-order-btn,.all-cat-apply-btn', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'color', 'backgroundColor', 'borderRadius', 'paddingTop', 'paddingBottom', 'paddingLeft', 'paddingRight']],
        'home.categories.section' => ['label' => 'Category Section', 'selector' => '[data-design-target="home.categories.section"],.shop-cat-section', 'controls' => ['backgroundColor', 'paddingTop', 'paddingBottom']],
        'home.categories.title' => ['label' => 'Category Section Title', 'selector' => '[data-design-target="home.categories.title"],.shop-cat-title', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'color', 'marginBottom']],
        'home.category.card' => ['label' => 'Category Cards', 'selector' => '[data-design-target="home.category.card"],.shop-cat-link,.all-cat-card', 'controls' => ['backgroundColor', 'borderColor', 'borderRadius', 'boxShadow', 'paddingTop', 'paddingBottom']],
        'home.category.name' => ['label' => 'Category Card Text', 'selector' => '[data-design-target="home.category.name"],.shop-cat-name,.all-cat-body h3', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'color']],
        'product.card' => ['label' => 'Product Cards', 'selector' => '[data-design-target="product.card"],.pc,.cat-product-card', 'controls' => ['backgroundColor', 'borderColor', 'borderRadius', 'boxShadow']],
        'product.card.title' => ['label' => 'Product Card Title', 'selector' => '[data-design-target="product.card.title"],.pc-name,.cat-product-card h3', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'color', 'marginBottom']],
        'product.card.description' => ['label' => 'Product Card Description', 'selector' => '[data-design-target="product.card.description"],.pc-desc,.cat-product-card p', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'color', 'marginBottom']],
        'product.card.price' => ['label' => 'Product Card Price', 'selector' => '[data-design-target="product.card.price"],.pc-price,.cat-price', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'color']],
        'home.deal.card' => ['label' => 'Deal Cards', 'selector' => '[data-design-target="home.deal.card"],.deal-card-link,.deal-promo-card', 'controls' => ['backgroundColor', 'borderRadius', 'boxShadow']],
        'home.blog.card' => ['label' => 'Blog Cards', 'selector' => '[data-design-target="home.blog.card"],.blog-card-link', 'controls' => ['backgroundColor', 'borderRadius', 'boxShadow']],
        'footer.section' => ['label' => 'Footer Section', 'selector' => '[data-design-target="footer.section"],.footer', 'controls' => ['backgroundColor', 'color', 'paddingTop', 'paddingBottom']],
        'footer.links' => ['label' => 'Footer Links/Text', 'selector' => '[data-design-target="footer.links"],.footer-col a,.footer-col p,.footer-contact-item,.footer-desc', 'controls' => ['fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'color']],
    ];

    public static function defaults(): array
    {
        $defaults = [];
        foreach (self::FIELDS as $key => $field) {
            $defaults[$key] = $field['default'];
        }
        return $defaults;
    }

    public static function load(): array
    {
        $theme = self::defaults();

        try {
            if (!class_exists('\\Database')) {
                return $theme;
            }

            $keys = array_keys(self::FIELDS);
            $placeholders = implode(',', array_fill(0, count($keys), '?'));
            $rows = \Database::rows("SELECT `key`, value FROM settings WHERE `key` IN ($placeholders)", $keys);
            foreach ($rows as $row) {
                $key = (string)($row['key'] ?? '');
                if (isset(self::FIELDS[$key])) {
                    $theme[$key] = self::sanitize($key, (string)($row['value'] ?? ''));
                }
            }
        } catch (\Throwable) {
            return $theme;
        }

        return $theme;
    }

    public static function sanitizeValues(array $input): array
    {
        $theme = self::defaults();
        foreach (self::FIELDS as $key => $_field) {
            if (array_key_exists($key, $input)) {
                $theme[$key] = self::sanitize($key, (string)$input[$key]);
            }
        }
        return $theme;
    }

    public static function save(array $input): array
    {
        $saved = [];
        foreach (self::FIELDS as $key => $_field) {
            if (!array_key_exists($key, $input)) {
                continue;
            }
            $value = self::sanitize($key, (string)$input[$key]);
            \Database::setSetting($key, $value);
            $saved[$key] = $value;
        }
        return $saved;
    }

    public static function reset(): array
    {
        $defaults = self::defaults();
        foreach ($defaults as $key => $value) {
            \Database::setSetting($key, $value);
        }
        return $defaults;
    }

    public static function loadElementStyles(): array
    {
        try {
            self::ensureElementStylesTable();
            $rows = \Database::rows(
                'SELECT target_key, styles_json FROM theme_element_styles WHERE is_active = 1 ORDER BY target_key ASC'
            );
            $styles = [];
            foreach ($rows as $row) {
                $decoded = json_decode((string)($row['styles_json'] ?? '{}'), true);
                if (is_array($decoded)) {
                    $styles[(string)$row['target_key']] = $decoded;
                }
            }
            if ($styles !== []) {
                return self::sanitizeElementStyles($styles);
            }
        } catch (\Throwable) {
            // Fallback to the legacy settings JSON below.
        }

        try {
            $raw = (string)\Database::setting(self::ELEMENT_STYLES_KEY, '{}');
            $decoded = json_decode($raw, true);
            return self::sanitizeElementStyles(is_array($decoded) ? $decoded : []);
        } catch (\Throwable) {
            return [];
        }
    }

    public static function saveElementStyles(array $styles): array
    {
        $clean = self::sanitizeElementStyles($styles);
        $encodedAll = json_encode($clean, JSON_UNESCAPED_SLASHES) ?: '{}';

        try {
            self::ensureElementStylesTable();
            $pdo = \Database::get();
            $pdo->beginTransaction();
            \Database::query('DELETE FROM theme_element_styles');
            foreach ($clean as $target => $values) {
                \Database::query(
                    'INSERT INTO theme_element_styles (target_key, styles_json, generated_css, is_active, updated_at) VALUES (?, ?, ?, 1, NOW())',
                    [$target, json_encode($values, JSON_UNESCAPED_SLASHES) ?: '{}', self::elementCss([$target => $values])]
                );
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            try {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            } catch (\Throwable) {}
            \Database::setSetting(self::ELEMENT_STYLES_KEY, $encodedAll);
            return $clean;
        }

        try {
            \Database::setSetting(self::ELEMENT_STYLES_KEY, $encodedAll);
        } catch (\Throwable) {
            // The dedicated table is authoritative; this legacy backup is optional.
        }
        return $clean;
    }

    public static function resetElementStyles(): array
    {
        try {
            self::ensureElementStylesTable();
            \Database::query('DELETE FROM theme_element_styles');
        } catch (\Throwable) {
            // Fall back to the legacy settings key below.
        }

        try {
            \Database::setSetting(self::ELEMENT_STYLES_KEY, '{}');
        } catch (\Throwable) {}
        return [];
    }

    public static function sanitizeElementStyles(array $styles): array
    {
        $clean = [];
        foreach ($styles as $target => $values) {
            $target = (string)$target;
            if (!isset(self::ELEMENTS[$target]) || !is_array($values)) {
                continue;
            }
            $allowed = array_flip(self::ELEMENTS[$target]['controls']);
            foreach ($values as $property => $value) {
                $property = (string)$property;
                if (!isset($allowed[$property], self::STYLE_FIELDS[$property])) {
                    continue;
                }
                $sanitized = self::sanitizeStyleValue($property, (string)$value);
                if ($sanitized !== '') {
                    $clean[$target][$property] = $sanitized;
                }
            }
        }
        return $clean;
    }

    public static function css(array $theme = [], ?array $elementStyles = null): string
    {
        $theme = array_merge(self::defaults(), $theme ?: self::load());
        $elementStyles = $elementStyles === null ? self::loadElementStyles() : self::sanitizeElementStyles($elementStyles);
        $shadow = self::SHADOWS[$theme['theme_card_shadow']] ?? self::SHADOWS['soft'];
        $font = self::fontStack($theme['theme_font_family']);
        $headingFont = self::fontStack($theme['theme_heading_font']);
        $primaryRgb = self::hexToRgb($theme['theme_primary_color']);
        $headerRgb = self::hexToRgb($theme['theme_header_bg']);
        $bodyRgb = self::hexToRgb($theme['theme_body_bg']);
        $footerStart = $theme['theme_footer_bg'];
        $footerEnd = self::adjustHex($footerStart, -24);

        return ':root{' .
            '--blue:' . $theme['theme_primary_color'] . ';' .
            '--blue2:' . $theme['theme_primary_dark'] . ';' .
            '--blue3:' . self::adjustHex($theme['theme_primary_dark'], -18) . ';' .
            '--blue-bg:rgba(' . $primaryRgb . ',.10);' .
            '--blue-mid:rgba(' . $primaryRgb . ',.26);' .
            '--orange:' . $theme['theme_accent_color'] . ';' .
            '--orange2:' . self::adjustHex($theme['theme_accent_color'], -22) . ';' .
            '--green:' . $theme['theme_success_color'] . ';' .
            '--bg:' . $theme['theme_body_bg'] . ';' .
            '--bg2:rgba(' . $bodyRgb . ',.72);' .
            '--ink:' . $theme['theme_text_color'] . ';' .
            '--text:' . $theme['theme_text_color'] . ';' .
            '--text2:' . $theme['theme_muted_color'] . ';' .
            '--fn:' . $font . ';' .
            '--fd:' . $headingFont . ';' .
            '--r:' . $theme['theme_button_radius'] . ';' .
            '--theme-button-radius:' . $theme['theme_button_radius'] . ';' .
            '--theme-card-radius:' . $theme['theme_card_radius'] . ';' .
            '--theme-card-shadow:' . $shadow . ';' .
            '--theme-logo-height:' . $theme['theme_logo_height'] . ';' .
            '--theme-nav-height:' . $theme['theme_nav_height'] . ';' .
            '--theme-hero-title-size:' . $theme['theme_hero_title_size'] . ';' .
            '--theme-section-spacing:' . $theme['theme_section_spacing'] . ';' .
            '--theme-header-bg:' . $theme['theme_header_bg'] . ';' .
            '--theme-header-rgb:' . $headerRgb . ';' .
            '--theme-footer-bg:' . $footerStart . ';' .
            '--theme-footer-end:' . $footerEnd . ';' .
            '}' .
            'html{font-size:' . $theme['theme_base_font_size'] . ';}' .
            '.site-header{--purple:var(--theme-header-bg);--purple-dark:' . self::adjustHex($theme['theme_header_bg'], -16) . ';--navbar-height:var(--theme-nav-height);}' .
            '.brand-img{height:var(--theme-logo-height);}' .
            '.btn{border-radius:var(--theme-button-radius);}' .
            '.hero{padding-top:var(--theme-section-spacing);padding-bottom:var(--theme-section-spacing);}' .
            '.hero-h1{font-size:clamp(28px,4vw,var(--theme-hero-title-size));}' .
            '.pc,.shop-cat-link,.deal-card-link,.blog-card-link,.all-cat-card,.cartp-card{border-radius:var(--theme-card-radius);box-shadow:var(--theme-card-shadow);}' .
            '.footer{background:radial-gradient(680px 180px at 4% 0%, rgba(255,255,255,.09), transparent 60%),linear-gradient(90deg,var(--theme-footer-bg) 0%,var(--theme-footer-bg) 42%,var(--theme-footer-end) 100%);}' .
            '.footer-social a{color:var(--theme-footer-bg);}' .
            self::elementCss($elementStyles);
    }

    public static function designSchema(): array
    {
        return [
            'Brand colors' => [
                'theme_primary_color' => 'Primary color',
                'theme_primary_dark' => 'Primary hover color',
                'theme_accent_color' => 'Accent color',
                'theme_success_color' => 'Success/CTA green',
            ],
            'Typography' => [
                'theme_font_family' => 'Body font',
                'theme_heading_font' => 'Heading font',
                'theme_base_font_size' => 'Base font size',
                'theme_hero_title_size' => 'Hero title size',
            ],
            'Header & footer' => [
                'theme_header_bg' => 'Header topbar color',
                'theme_footer_bg' => 'Footer background',
                'theme_logo_height' => 'Logo height',
                'theme_nav_height' => 'Navbar height',
            ],
            'Cards & spacing' => [
                'theme_body_bg' => 'Page background',
                'theme_text_color' => 'Text color',
                'theme_muted_color' => 'Muted text color',
                'theme_button_radius' => 'Button radius',
                'theme_card_radius' => 'Card radius',
                'theme_section_spacing' => 'Section spacing',
                'theme_card_shadow' => 'Card shadow',
            ],
        ];
    }

    public static function elementSchema(): array
    {
        $schema = [];
        foreach (self::ELEMENTS as $target => $element) {
            $controls = [];
            foreach ($element['controls'] as $property) {
                $field = self::STYLE_FIELDS[$property];
                $controls[$property] = [
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'min' => $field['min'] ?? null,
                    'max' => $field['max'] ?? null,
                    'step' => $field['step'] ?? null,
                    'presets' => $field['presets'] ?? null,
                ];
            }
            $schema[$target] = [
                'label' => $element['label'],
                'selector' => $element['selector'],
                'controls' => $controls,
            ];
        }
        return $schema;
    }

    public static function fieldMeta(string $key): array
    {
        return self::FIELDS[$key] ?? ['type' => 'text', 'default' => ''];
    }

    public static function fontOptions(): array
    {
        return self::FONTS;
    }

    public static function headingFontOptions(): array
    {
        return self::HEADING_FONTS;
    }

    public static function fontWeightOptions(): array
    {
        return self::FONT_WEIGHTS;
    }

    public static function shadowOptions(): array
    {
        return array_keys(self::SHADOWS);
    }

    private static function ensureElementStylesTable(): void
    {
        \Database::query(
            'CREATE TABLE IF NOT EXISTS theme_element_styles (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                target_key VARCHAR(191) NOT NULL,
                styles_json LONGTEXT NOT NULL,
                generated_css LONGTEXT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_theme_element_target (target_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private static function elementCss(array $elementStyles): string
    {
        $css = '';
        foreach ($elementStyles as $target => $styles) {
            if (!isset(self::ELEMENTS[$target]) || empty($styles)) {
                continue;
            }
            $rules = '';
            foreach ($styles as $property => $value) {
                if (!isset(self::STYLE_FIELDS[$property])) {
                    continue;
                }
                $cssProperty = self::STYLE_FIELDS[$property]['css'];
                $cssValue = self::cssStyleValue($property, (string)$value);
                if ($cssValue !== '') {
                    $rules .= $cssProperty . ':' . $cssValue . ' !important;';
                }
            }
            if ($rules !== '') {
                $css .= '/* Design Studio: ' . $target . ' */' . self::ELEMENTS[$target]['selector'] . '{' . $rules . '}';
            }
        }
        return $css;
    }

    private static function sanitize(string $key, string $value): string
    {
        $field = self::FIELDS[$key] ?? null;
        if (!$field) return '';
        $value = trim($value);

        return match ($field['type']) {
            'color' => self::sanitizeColor($value, $field['default']),
            'font' => in_array($value, self::FONTS, true) ? $value : $field['default'],
            'heading_font' => in_array($value, self::HEADING_FONTS, true) ? $value : $field['default'],
            'px' => self::sanitizePx($value, (int)$field['min'], (int)$field['max'], $field['default']),
            'shadow' => array_key_exists($value, self::SHADOWS) ? $value : $field['default'],
            default => $field['default'],
        };
    }

    private static function sanitizeStyleValue(string $property, string $value): string
    {
        $field = self::STYLE_FIELDS[$property] ?? null;
        if (!$field) return '';
        $value = trim($value);
        if ($value === '') return '';

        return match ($field['type']) {
            'color' => self::sanitizeColor($value, ''),
            'font' => in_array($value, self::FONTS, true) || in_array($value, self::HEADING_FONTS, true) ? $value : '',
            'weight' => in_array($value, self::FONT_WEIGHTS, true) ? $value : '',
            'px' => self::sanitizePx($value, (int)$field['min'], (int)$field['max'], ''),
            'number' => self::sanitizeNumber($value, (float)$field['min'], (float)$field['max']),
            'shadow' => array_key_exists($value, self::SHADOWS) ? $value : '',
            default => '',
        };
    }

    private static function cssStyleValue(string $property, string $value): string
    {
        $field = self::STYLE_FIELDS[$property] ?? null;
        if (!$field) return '';
        return match ($field['type']) {
            'font' => self::fontStack($value),
            'shadow' => self::SHADOWS[$value] ?? '',
            default => $value,
        };
    }

    private static function sanitizeColor(string $value, string $default): string
    {
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return strtoupper($value);
        }
        return $default;
    }

    private static function sanitizePx(string $value, int $min, int $max, string $default): string
    {
        if (!preg_match('/^\d+(?:\.\d+)?(?:px)?$/', $value)) {
            return $default;
        }
        $num = (float)str_replace('px', '', $value);
        $num = max($min, min($max, $num));
        $out = rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
        return $out . 'px';
    }

    private static function sanitizeNumber(string $value, float $min, float $max): string
    {
        if (!preg_match('/^\d+(?:\.\d+)?$/', $value)) {
            return '';
        }
        $num = max($min, min($max, (float)$value));
        return rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
    }

    private static function fontStack(string $font): string
    {
        return match ($font) {
            'Poppins' => "'Poppins',sans-serif",
            'Merriweather' => "'Merriweather',serif",
            default => "'Inter',sans-serif",
        };
    }

    private static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        return hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2));
    }

    private static function adjustHex(string $hex, int $amount): string
    {
        $hex = ltrim($hex, '#');
        $parts = [substr($hex, 0, 2), substr($hex, 2, 2), substr($hex, 4, 2)];
        $out = '#';
        foreach ($parts as $part) {
            $value = max(0, min(255, hexdec($part) + $amount));
            $out .= str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
        }
        return strtoupper($out);
    }
}

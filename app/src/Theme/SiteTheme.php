<?php

declare(strict_types=1);

namespace Theme;

class SiteTheme
{
    public const PREFIX = 'theme_';

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
    private const SHADOWS = [
        'none' => 'none',
        'soft' => '0 8px 20px rgba(15,23,42,.04)',
        'premium' => '0 16px 36px rgba(37,99,235,.13)',
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

    public static function css(array $theme = []): string
    {
        $theme = array_merge(self::defaults(), $theme ?: self::load());
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
            '.footer-social a{color:var(--theme-footer-bg);}';
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

    public static function shadowOptions(): array
    {
        return array_keys(self::SHADOWS);
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

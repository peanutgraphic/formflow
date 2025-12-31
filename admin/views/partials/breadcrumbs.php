<?php
/**
 * Breadcrumb Navigation Partial
 *
 * Renders a breadcrumb navigation for admin pages.
 *
 * @package FormFlow
 *
 * Usage:
 * <?php isf_breadcrumbs(['Dashboard' => 'isf-dashboard', 'Data' => 'isf-data']); ?>
 * <?php isf_breadcrumbs(['Dashboard' => 'isf-dashboard'], 'Current Page'); ?>
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render breadcrumb navigation
 *
 * @param array  $items   Array of label => page_slug pairs
 * @param string $current Optional current page label (not linked)
 * @param bool   $echo    Whether to echo or return output
 * @return string|void
 */
function isf_breadcrumbs(array $items, string $current = '', bool $echo = true) {
    $html = '<nav class="isf-breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'formflow') . '">';
    $html .= '<span class="dashicons dashicons-admin-home"></span>';

    $count = count($items);
    $i = 0;

    foreach ($items as $label => $page_slug) {
        $i++;
        $url = admin_url('admin.php?page=' . $page_slug);
        $html .= sprintf(
            '<a href="%s">%s</a>',
            esc_url($url),
            esc_html($label)
        );

        if ($i < $count || !empty($current)) {
            $html .= '<span class="isf-breadcrumb-sep">/</span>';
        }
    }

    if (!empty($current)) {
        $html .= sprintf(
            '<span class="isf-breadcrumb-current" aria-current="page">%s</span>',
            esc_html($current)
        );
    }

    $html .= '</nav>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * Get the page title with icon
 *
 * @param string $title    Page title
 * @param string $icon     Dashicon name (without 'dashicons-' prefix)
 * @param bool   $echo     Whether to echo or return
 * @return string|void
 */
function isf_page_header(string $title, string $icon = '', bool $echo = true) {
    $icon_html = '';
    if (!empty($icon)) {
        $icon_html = sprintf(
            '<span class="dashicons dashicons-%s isf-page-icon"></span>',
            esc_attr($icon)
        );
    }

    $html = sprintf(
        '<h1 class="isf-page-title">%s%s</h1>',
        $icon_html,
        esc_html($title)
    );

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

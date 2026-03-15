<?php
/**
 * Plugin Name: WP Media Archive
 * Plugin URI: https://example.com/wp-media-archive
 * Description: Strukturiertes Medienarchiv für Bilder und Tonaufnahmen mit Tag-System.
 * Version: 1.0.0
 * Author: Developer
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-media-archive
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if (! defined('ABSPATH')) {
    exit;
}

define('WPMA_VERSION', '1.0.0');
define('WPMA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPMA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPMA_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once WPMA_PLUGIN_DIR . 'includes/class-wpma-post-types.php';
require_once WPMA_PLUGIN_DIR . 'includes/class-wpma-taxonomies.php';
require_once WPMA_PLUGIN_DIR . 'includes/class-wpma-admin.php';
require_once WPMA_PLUGIN_DIR . 'includes/class-wpma-frontend.php';
require_once WPMA_PLUGIN_DIR . 'includes/class-wpma-shortcodes.php';
require_once WPMA_PLUGIN_DIR . 'includes/class-wpma-ajax.php';

final class WP_Media_Archive
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', [$this, 'loadTextdomain']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        new WPMA_Post_Types();
        new WPMA_Taxonomies();
        new WPMA_Admin();
        new WPMA_Frontend();
        new WPMA_Shortcodes();
        new WPMA_Ajax();
    }

    public function loadTextdomain(): void
    {
        load_plugin_textdomain('wp-media-archive', false, dirname(WPMA_PLUGIN_BASENAME) . '/languages');
    }

    public function enqueueFrontendAssets(): void
    {
        wp_enqueue_style(
            'wpma-frontend',
            WPMA_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            WPMA_VERSION
        );
        wp_enqueue_script(
            'wpma-frontend',
            WPMA_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            WPMA_VERSION,
            true
        );
        wp_localize_script('wpma-frontend', 'wpmaAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wpma_nonce'),
        ]);
    }

    public function enqueueAdminAssets(string $hook): void
    {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'media_archive') {
            wp_enqueue_style(
                'wpma-admin',
                WPMA_PLUGIN_URL . 'assets/css/admin.css',
                [],
                WPMA_VERSION
            );
            wp_enqueue_script(
                'wpma-admin',
                WPMA_PLUGIN_URL . 'assets/js/admin.js',
                ['jquery'],
                WPMA_VERSION,
                true
            );
        }
    }
}

register_activation_hook(__FILE__, function (): void {
    $postTypes = new WPMA_Post_Types();
    $postTypes->register();
    $taxonomies = new WPMA_Taxonomies();
    $taxonomies->register();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function (): void {
    flush_rewrite_rules();
});

add_action('plugins_loaded', function (): void {
    WP_Media_Archive::instance();
});

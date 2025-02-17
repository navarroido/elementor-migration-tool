<?php
/**
 * Plugin Name: Elementor Migration Tool
 * Plugin URI: https://your-website.com/elementor-migration-tool
 * Description: A simple tool to help migrate your WordPress site to Elementor hosting with step-by-step instructions.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://your-website.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: elementor-migration-tool
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('EMT_VERSION', '1.0.0');
define('EMT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EMT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once EMT_PLUGIN_DIR . 'includes/class-emt-admin.php';
require_once EMT_PLUGIN_DIR . 'includes/class-emt-plugin-installer.php';

// Initialize the plugin
function emt_init() {
    new EMT_Admin();
    $installer = new EMT_Plugin_Installer();
    
    // Try to install required plugins automatically
    if (current_user_can('install_plugins')) {
        $installer->install_required_plugins();
    }
}
add_action('plugins_loaded', 'emt_init');

// Activation hook
register_activation_hook(__FILE__, 'emt_activate');
function emt_activate() {
    // Schedule a one-time event to install plugins
    wp_schedule_single_event(time(), 'emt_install_required_plugins_event');
}

// Hook for scheduled installation
add_action('emt_install_required_plugins_event', 'emt_handle_scheduled_installation');
function emt_handle_scheduled_installation() {
    $installer = new EMT_Plugin_Installer();
    $installer->install_required_plugins();
} 
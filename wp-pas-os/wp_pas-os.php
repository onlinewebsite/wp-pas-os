<?php
/**
 * Plugin Name: سامانه سنجش روان - PAS-OS
 * Plugin URI: https://pas-os.com/
 * Description: افزونه اتصال به سامانه سنجش روان
 * Version: 1.0.0
 * Author: پردانش
 * Author URI: https://onlinewebsite.ir
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pas-os
 * Domain Path: /languages
 */

// Security check
if (!defined('ABSPATH') || !function_exists('add_action')) {
    exit('دسترسی غیرمجاز!');
}

// Plugin constants
define('PAS_OS_VERSION', '1.0.0');
define('PAS_OS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PAS_OS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PAS_OS_BASENAME', plugin_basename(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'PasOs_';
    $base_dir = PAS_OS_PLUGIN_DIR . 'includes/';
    
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    
    $class_name = str_replace($prefix, '', $class);
    $file = $base_dir . 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Activation/Deactivation hooks
register_activation_hook(__FILE__, ['PasOs_Activator', 'activate']);
register_deactivation_hook(__FILE__, ['PasOs_Deactivator', 'deactivate']);

// Initialize plugin
add_action('plugins_loaded', function () {
    // Load translations
    load_plugin_textdomain(
        'pas-os',
        false,
        dirname(PAS_OS_BASENAME) . '/languages/'
    );

    // Main plugin class
    if (class_exists('PasOs_Main')) {
        PasOs_Main::instance()->init();
    }
});

// Admin settings link
add_filter('plugin_action_links_' . PAS_OS_BASENAME, function ($links) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url('admin.php?page=pas-os-settings'),
        __('تنظیمات', 'pas-os')
    );
    array_unshift($links, $settings_link);
    return $links;
});

<?php

/**
 * Plugin Name:       Top Identity Verifications for WooCommerce | Trust Swiftly
 * Plugin URI:        https://docs.trustswiftly.com/web/wordpress
 * Description:       Identity Verification plugin. Trust efficiently by connecting people's verification with existing fraud solutions. Verify your customers with 15+ methods used by companies worldwide to verify id documents.
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Trust Swiftly
 * Author URI:        https://www.trustswiftly.com
 * Version:           1.1.11
 * Text Domain:       Trust Swiftly Verification
 */

use TrustswiftlyVerification\TrustVerifyPlugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/bootstrap.php';

$aPlugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ));
if(empty($aPlugins)){
    $aPlugins = [];
}
if ( in_array( 'woocommerce/woocommerce.php',  $aPlugins)&& file_exists(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php') ) {
    register_activation_hook(__FILE__, array('TrustswiftlyVerification\TrustVerifyPlugin', 'activate'));
    new TrustVerifyPlugin();
    add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'ts_add_plugin_page_settings_link');
    function ts_add_plugin_page_settings_link( $links ) {
        $links[] = '<a href="' .
            admin_url( 'admin.php?page=ts-settings' ) .
            '">' . __('Settings') . '</a>';
        return $links;
    }
}

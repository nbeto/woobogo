<?php
/**
 * Plugin Name: WooBOGO – Multibuy Discounts for WooCommerce
 * Plugin URI: https://github.com/nbeto/woobogo
 * Description: Offer multibuy discounts like Buy One Get One Free, 2-for-1, 3-for-2 and more. Lightweight and flexible WooCommerce discount rules.
 * Version: 1.0.0
 * Author: Norberto Marques
 * Author URI: https://yourwebsite.com
 * License: GPL2+
 * Text Domain: woobogo
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

register_activation_hook(__FILE__, 'woobogo_verificar_dependencias');

function woobogo_verificar_dependencias() {
	if (!is_plugin_active('woocommerce/woocommerce.php')) {
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die(
			'O plugin <strong>WooBOGO</strong> requer o WooCommerce ativo. Por favor ativa o WooCommerce primeiro.',
			'Erro de dependência',
			['back_link' => true]
		);
	}
}

if ( !defined('WOOGOBO_PLUGIN_DIR') ) {
	return;
}

if ( !function_exists('is_plugin_active') ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( !is_plugin_active('woocommerce/woocommerce.php') ) {
	return;
}

define('WOOGOBO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOOGOBO_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WOOGOBO_PLUGIN_DIR . 'includes/core.php';

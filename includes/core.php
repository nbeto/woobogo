<?php
defined('ABSPATH') || exit;

// Inclui a página de definições do admin (permanece sempre ativa)
require_once WOOGOBO_PLUGIN_DIR . 'includes/admin-settings.php';

// Carrega a regra ativa
add_action('plugins_loaded', 'woobogo_init_plugin');

function woobogo_init_plugin() {
	if (!function_exists('WC')) return;

	$plugin_ativo = get_option('woobogo_enabled', false);
	if (!$plugin_ativo) return;

	$modo = get_option('woobogo_discount_mode', '2x1');

	// Caminho da regra
	$ficheiro_regra = WOOGOBO_PLUGIN_DIR . 'includes/rules/rule-' . sanitize_title($modo) . '.php';

	if (file_exists($ficheiro_regra)) {
		include_once $ficheiro_regra;
	}
}

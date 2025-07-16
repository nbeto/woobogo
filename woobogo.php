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

define('WOOGOBO_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once WOOGOBO_PLUGIN_DIR . 'includes/core.php';

//add_action('woocommerce_before_calculate_totals', 'woobogo_marcar_produtos_gratis_visual');
function woobogo_marcar_produtos_gratis_visual($cart) {
	if (is_admin() && !defined('DOING_AJAX')) return;

	// 1. Limpa os marcadores anteriores para evitar lixo
	foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
		unset($cart->cart_contents[$cart_item_key]['bewide_preco_original']);
		unset($cart->cart_contents[$cart_item_key]['bewide_quantidade_gratis']);
	}

	// 2. Contagem total de produtos
	$total_produtos = $cart->get_cart_contents_count();
	if ($total_produtos < 2) return;

	$produtos_ordenados = [];

	// 3. Gera lista com cada unidade individual
	foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
		$preco = $cart_item['data']->get_price();
		if ($preco <= 0) continue;

		for ($i = 0; $i < $cart_item['quantity']; $i++) {
			$produtos_ordenados[] = [
				'key' => $cart_item_key,
				'price' => $preco
			];
		}
	}

	if (count($produtos_ordenados) < 2) return;

	// 4. Ordena por preço crescente
	usort($produtos_ordenados, fn($a, $b) => $a['price'] <=> $b['price']);

	// 5. Seleciona X unidades mais baratas para ficarem grátis
	$gratis_count = floor(count($produtos_ordenados) / 2);
	$marcados = [];

	for ($i = 0; $i < $gratis_count; $i++) {
		$marcados[] = $produtos_ordenados[$i]['key'];
	}

	// 6. Marca os itens que devem ficar grátis
	foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
		$unidades_para_gratis = count(array_keys($marcados, $cart_item_key));
		if ($unidades_para_gratis > 0) {
			$cart->cart_contents[$cart_item_key]['bewide_preco_original'] = $cart_item['data']->get_price();
			$cart->cart_contents[$cart_item_key]['bewide_quantidade_gratis'] = $unidades_para_gratis;
		}
	}

	// 7. Aviso (opcional)
	if ($gratis_count > 0 && (is_cart() || is_checkout())) {
		wc_add_notice("\xF0\x9F\x8E\x81 Promoção 2 Por 1 Activada: " . $gratis_count . " produto(s) ficaram grátis!", 'success');
	}
}

//add_filter('woocommerce_cart_item_subtotal', 'woobogo_mostrar_subtotal_com_desconto_visual', 10, 3);
function woobogo_mostrar_subtotal_com_desconto_visual($subtotal_html, $cart_item, $cart_item_key) {
	if (
		isset($cart_item['bewide_preco_original']) &&
		isset($cart_item['bewide_quantidade_gratis']) &&
		$cart_item['bewide_quantidade_gratis'] > 0
	) {
		$preco = $cart_item['bewide_preco_original'];
		$qtd = $cart_item['quantity'];
		$qtd_gratis = $cart_item['bewide_quantidade_gratis'];
		$qtd_paga = max(0, $qtd - $qtd_gratis);

		$subtotal_original = wc_price($preco * $qtd);
		$subtotal_com_desconto = wc_price($preco * $qtd_paga);

		return '<div style="line-height:1.4;">'
		     . '<del style="opacity:0.6; display:block;">' . $subtotal_original . '</del>'
		     . '<ins style="color:#d00;font-weight:600; display:block;">' . $subtotal_com_desconto . '</ins>'
		     . '</div>';
	}
	return $subtotal_html;
}

//add_filter('woocommerce_cart_totals_order_total_html', 'woobogo_mostrar_total_final_com_desconto');
function woobogo_mostrar_total_final_com_desconto($total_html) {
	$cart = WC()->cart;
	$desconto_total = 0;

	foreach ($cart->get_cart() as $item) {
		if (isset($item['bewide_preco_original'], $item['bewide_quantidade_gratis'])) {
			$desconto_total += $item['bewide_preco_original'] * $item['bewide_quantidade_gratis'];
		}
	}

	if ($desconto_total > 0) {
		$total_original = wc_price($cart->get_total('edit'));
		$total_final = wc_price($cart->get_total('edit') - $desconto_total);

		return '<div style="line-height:1.4;">'
		     . '<del style="opacity:0.6; display:block;">' . $total_original . '</del>'
		     . '<ins style="color:#d00;font-weight:600; display:block;">' . $total_final . '</ins>'
		     . '</div>';
	}

	return $total_html;
}

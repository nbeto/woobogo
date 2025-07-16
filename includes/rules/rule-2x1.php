<?php
defined('ABSPATH') || exit;

/**
 * Regra: 2 por 1 — o(s) produto(s) mais barato(s) ficam grátis
 */

add_action('woocommerce_before_calculate_totals', 'woobogo_aplicar_regra_2x1');
function woobogo_aplicar_regra_2x1($cart) {
	if (is_admin() && !defined('DOING_AJAX')) return;

	foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
		unset($cart->cart_contents[$cart_item_key]['woobogo_preco_original']);
		unset($cart->cart_contents[$cart_item_key]['woobogo_quantidade_gratis']);
	}

	$total_produtos = $cart->get_cart_contents_count();
	if ($total_produtos < 2) return;

	$produtos_ordenados = [];

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

	usort($produtos_ordenados, fn($a, $b) => $a['price'] <=> $b['price']);
	$gratis_count = floor(count($produtos_ordenados) / 2);
	$marcados = [];

	for ($i = 0; $i < $gratis_count; $i++) {
		$marcados[] = $produtos_ordenados[$i]['key'];
	}

	foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
		$unidades_para_gratis = count(array_keys($marcados, $cart_item_key));
		if ($unidades_para_gratis > 0) {
			$cart->cart_contents[$cart_item_key]['woobogo_preco_original'] = $cart_item['data']->get_price();
			$cart->cart_contents[$cart_item_key]['woobogo_quantidade_gratis'] = $unidades_para_gratis;
		}
	}

	if ($gratis_count > 0 && (is_cart() || is_checkout())) {
		wc_add_notice('Promoção 2 Por 1 Activada: ' . $gratis_count . ' produto(s) ficaram grátis!', 'success');
	}
}

// Subtotal com riscado
add_filter('woocommerce_cart_item_subtotal', 'woobogo_subtotal_visual', 10, 3);
function woobogo_subtotal_visual($subtotal_html, $cart_item, $cart_item_key) {
	if (
		isset($cart_item['woobogo_preco_original']) &&
		isset($cart_item['woobogo_quantidade_gratis']) &&
		$cart_item['woobogo_quantidade_gratis'] > 0
	) {
		$preco = $cart_item['woobogo_preco_original'];
		$qtd = $cart_item['quantity'];
		$qtd_gratis = $cart_item['woobogo_quantidade_gratis'];
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

// Total visual no checkout
add_filter('woocommerce_cart_totals_order_total_html', 'woobogo_total_visual');
function woobogo_total_visual($total_html) {
	$cart = WC()->cart;
	$desconto_total = 0;

	foreach ($cart->get_cart() as $item) {
		if (isset($item['woobogo_preco_original'], $item['woobogo_quantidade_gratis'])) {
			$desconto_total += $item['woobogo_preco_original'] * $item['woobogo_quantidade_gratis'];
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

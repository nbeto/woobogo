<?php
defined('ABSPATH') || exit;

// Adiciona o menu no admin
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'WooBOGO',
        'WooBOGO',
        'manage_woocommerce',
        'woobogo-settings',
        'woobogo_render_settings_page'
    );
});

// Regista as opções
add_action('admin_init', function () {
    register_setting('woobogo_settings_group', 'woobogo_enabled');
    register_setting('woobogo_settings_group', 'woobogo_discount_mode');
});

// HTML da página
function woobogo_render_settings_page() {
    ?>
    <div class="wrap woobogo-admin">
        <h1>WooBOGO – Regras de Desconto</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('woobogo_settings_group');
            do_settings_sections('woobogo_settings_group');
            ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Ativar Promoções</th>
                    <td>
                        <input type="checkbox" name="woobogo_enabled" value="1" <?php checked(get_option('woobogo_enabled'), 1); ?> />
                        <label>Ativar regras de desconto no carrinho</label>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Modo de Desconto</th>
                    <td>
                        <select name="woobogo_discount_mode">
                            <option value="2x1" <?php selected(get_option('woobogo_discount_mode'), '2x1'); ?>>2x1 (paga 1, leva 2)</option>
                            <option value="3x2" <?php selected(get_option('woobogo_discount_mode'), '3x2'); ?>>3x2 (paga 2, leva 3)</option>
                            <option value="3x1" <?php selected(get_option('woobogo_discount_mode'), '3x1'); ?>>3x1 (paga 1, leva 3)</option>
                        </select>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

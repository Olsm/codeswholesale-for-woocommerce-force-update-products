<?php
/*
Plugin Name: CodesWholesale Force update products price and stock
Description: Updates all products price and stock in Woocommerce from CodesWholesale
Version: 1.0.0
Author: Olav Småriset
*/

use CodesWholesaleFramework\Postback\UpdatePriceAndStock\UpdatePriceAndStockAction;

add_action("activated_plugin", 'force_update_price_and_stock');

function force_update_price_and_stock() {
    $args = ['status' => 'publish',
            'paginate' => true,
            'page' => 1];

    while ($args['page'] <= ($products = wc_get_products($args))->max_num_pages) {
        set_time_limit(10);
        $products = $products->products;

        foreach ($products as $product) {
            set_time_limit(10);
            update_price_and_stock_by_post_id($product->id);
        }

        $args['page']++;
    }

    deactivate_plugins( plugin_basename( __FILE__ ) );
}

/**
 * @param int $post_id
 */
function update_price_and_stock_by_post_id($post_id) {
    $productId = get_post_meta($post_id, CodesWholesaleConst::PRODUCT_CODESWHOLESALE_ID_PROP_NAME, true);

    if (!empty($productId)) {
        $action = new UpdatePriceAndStockAction(new WP_Update_Price_And_Stock(), new WP_Spread_Retriever());
        $action->setConnection(CW()->get_codes_wholesale_client());
        $action->setProductId($productId);
        $action->process();
    }
}
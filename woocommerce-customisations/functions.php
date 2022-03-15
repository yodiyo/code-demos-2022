<?php

/************************
 *
 * Woocommerce customisations.
*/

/* Reorder product summary components */

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 15 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );


/**
 *
 * Add label to price.
 *
 * @param number $price product price.
 * @param id     $product product id.
 *
 * @return string $price.
 */

function ov_variable_product_price_starting_from( $price, $product ) {

	if ( $product->is_type( 'simple' ) ) {
		return $price;
	} elseif ( $product->is_type( 'variable' ) ) {
		$prices = $product->get_variation_prices( true );

		if ( ! empty( $prices['price'] ) ) {
			$min_price     = current( $prices['price'] );
			$max_price     = end( $prices['price'] );
			$min_reg_price = current( $prices['regular_price'] );

			if ( $min_price !== $min_reg_price ) {
				$price = sprintf( '%1s %2s', wc_get_price_html_from_text(), wc_format_sale_price( wc_price( $min_reg_price ), wc_price( $min_price ) ) );
			} elseif ( $min_price !== $max_price ) {
				$price = sprintf( __( '%1s %2s' ), wc_get_price_html_from_text(), wc_price( $min_price ) );
			}
		}
	}

	return $price;
}
add_filter( 'woocommerce_get_price_html', 'ov_variable_product_price_starting_from', 10, 2 );


/**
 *
 * @snippet Upsells - move to WooCommerce Single Product
*/

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );

add_action( 'woocommerce_single_product_summary', 'woocommerce_upsell_display', 35 );

/**
 * Change Upsells h2 text.
 *
 * @return string
 */

function ov_translate_may_also_like() {
	return 'Add Options';
}
add_filter( 'woocommerce_product_upsells_products_heading', 'ov_translate_may_also_like' );


/**
 *
 * Override loop template and show quantities next to add to cart buttons.
 *
 * @param html product
 * @return html
 */

function quantity_inputs_for_woocommerce_loop_add_to_cart_link( $html, $product ) {
	if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
		$html  = '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
		$html .= woocommerce_quantity_input( array(), $product, false );
		$html .= '<button type="submit" class="button alt">' . esc_html( $product->add_to_cart_text() ) . '</button>';
		$html .= '</form>';
	}
	return $html;
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'quantity_inputs_for_woocommerce_loop_add_to_cart_link', 10, 2 );


/**
 * Use a shortcode to display product reviews.
 * Format: [product_reviews id="123"]
 *
 * If there are no reviews for a product, nothing is returned to the browser.
 *
 * @param id $atts product reviews
 * @return string
 */

function ov_product_reviews_shortcode( $atts ) {

	if ( empty( $atts ) ) {
		return '';
	}

	if ( ! isset( $atts['id'] ) ) {
		return '';
	}

	$comments = get_comments( 'post_id=' . $atts['id'] );

	if ( ! $comments ) {
		return '';
	}

	// If comments are open or we have at least one comment, load up the comment template.
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

}
add_shortcode( 'product_reviews', 'ov_product_reviews_shortcode' );


/**
 *
 * Customise product tabs.
 *
 * @param content $tabs tab content.
 * @return $tabs.
 */

function woo_custom_product_tabs( $tabs ) {

	// 1) Removing tabs

	unset( $tabs['description'] );              // Remove the description tab.
	unset( $tabs['reviews'] );                  // Remove the reviews tab.
	unset( $tabs['additional_information'] );   // Remove the additional information tab.

	// 2) Adding new tabs and set the right order.
	$prod_id                   = get_the_ID();
	$tab_content_features      = get_field( 'standard_features', $prod_id, true );
	$tab_content_tech          = get_field( 'tech_specs', $prod_id, true );
	$tab_content_performance   = get_field( 'performance', $prod_id, true );
	$tab_content_documentation = get_field( 'documentation', $prod_id, true );

	if ( ! empty( $tab_content_features ) ) {
		// Attribute standard features tab.
		$tabs['standard_feat_tab'] = array(
			'title'    => __( 'Standard Features', 'woocommerce' ),
			'priority' => 100,
			'callback' => 'woo_standard_feat_tab_content',
		);
	}

	if ( ! empty( $tab_content_tech  ) ) {
		// Attribute tech specs tab.
		$tabs['tech_spec_tab'] = array(
			'title'    => __( 'Tech Specs', 'woocommerce' ),
			'priority' => 110,
			'callback' => 'woo_tech_spec_products_tab_content',
		);
	}

	if ( ! empty( $tab_content_performance ) ) {
		// Adds performance tab.
		$tabs['performance_tab'] = array(
			'title'    => __( 'Performance', 'woocommerce' ),
			'priority' => 120,
			'callback' => 'woo_performance_products_tab_content',
		);
	}

	if ( ! empty( $tab_content_documentation ) ) {
		// Adds documentation tab.
		$tabs['documentation_tab'] = array(
			'title'    => __( 'Documentation', 'woocommerce' ),
			'priority' => 120,
			'callback' => 'woo_documentation_products_tab_content',
		);
	}


	$tabs['reviews_tab'] = array(
		'title'    => __( 'Reviews', 'woocommerce' ),
		'priority' => 120,
		'callback' => 'woo_reviews_tab_content',
	);

	return $tabs;

}
add_filter( 'woocommerce_product_tabs', 'woo_custom_product_tabs' );

function woo_standard_feat_tab_content() {
	// The new tab content.
	$prod_id = get_the_ID();
	echo '<div>' . get_field( 'standard_features', $prod_id, true ) . '</div>';
}

function woo_reviews_tab_content() {
	// The new tab content.
	$prod_id = get_the_ID();

	$output      = '<div class="o-product-reviews">';
		$output .= do_shortcode( '[product_reviews id=$prod_id]' );
	$output     .= '</div>';

	// echo '<div>' . do_shortcode( '[wpbr_collection id=$prod_id]' ) . '</div>';
	echo $output;
}

function woo_tech_spec_products_tab_content() {
	// The new tab content.
	$prod_id = get_the_ID();
	echo '<div>' . get_field( 'tech_specs', $prod_id, true ) . '</div>';
}

function woo_performance_products_tab_content() {
	// The new tab content.
	$prod_id = get_the_ID();
	echo '<div>' . get_field( 'performance', $prod_id, true ) . '</div>';
}

function woo_documentation_products_tab_content() {
	// The new tab content.
	$prod_id   = get_the_ID();
	$documents = get_field( 'documentation', $prod_id );
	?>
	<ul class="ov-list-documents fa-ul">
	<?php
	foreach ( $documents as $document ) {
		?>
		<li><span class="fa-li"><i class="fas fa-file-pdf"></i></span><a href="<?php echo $document['document']['url']; ?>" target="_blank"> <?php echo $document['document']['title']; ?></a></li>
		<?php
	}
	?>
	</ul>
	<?php
}


/**
 * Remove the breadcrumbs
 */

 function bc_remove_wc_breadcrumbs() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
}
add_action( 'init', 'bc_remove_wc_breadcrumbs' );


/**
 * Add custom link for return to shop page.
 */

if ( ! function_exists( 'return_to_shop_link' ) ) {

	function return_to_shop_link() {
		if ( ! is_shop() ) {

			$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );

			$output  = '<nav class="o-breadcrumb">';
			$output .= '<a href="' . esc_url( $shop_page_url ) . '" class="o-breadcrumb-link">< Return to Shop</a>';
			$output .= '</nav>';

			echo $output; // phpcs:ignore
		}
	}
}


/**
 * Add custom link for return to cart page.
 */

if ( ! function_exists( 'return_to_cart_link' ) ) {

	function return_to_cart_link() {
		if ( ! is_shop() ) {

			$cart_page_url = get_permalink( wc_get_page_id( 'cart' ) );

			$output  = '<nav class="o-breadcrumb">';
			$output .= '<a href="' . esc_url( $cart_page_url ) . '" class="o-breadcrumb-link">< Return to Cart</a>';
			$output .= '</nav>';

			echo $output; // phpcs:ignore
		}
	}
}

add_action( 'woocommerce_before_main_content', 'return_to_shop_link', 20, 0 );
add_action( 'woocommerce_before_cart', 'return_to_shop_link', 20, 0 );
add_action( 'woocommerce_before_checkout_form_cart_notices', 'return_to_cart_link', 20, 0 );


/**
 * Remove related products section.
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );


/**
 * Replace above with cross-sells products.
 */

function ov_add_cross_sells() {

	$crosssell_ids = get_post_meta( get_the_ID(), '_crosssell_ids' );
	$crosssell_ids = $crosssell_ids[0];

	if ( $crosssell_ids ) :
		?>

		<div class="cross-sells">
			<?php
			$heading = apply_filters( 'woocommerce_product_cross_sells_products_heading', __( 'You May Also Like&hellip;', 'woocommerce' ) );

			if ( $heading ) :
				?>
				<h2><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>

			<?php woocommerce_product_loop_start(); ?>

				<?php foreach ( $crosssell_ids as $crosssell_id ) : ?>

					<?php
						$post_object = get_post( $crosssell_id );

						setup_postdata( $GLOBALS['post'] =& $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

						wc_get_template_part( 'content', 'product' );
					?>

				<?php endforeach; ?>

			<?php woocommerce_product_loop_end(); ?>

		</div>
		<?php
	endif;

	wp_reset_postdata();

}

add_action( 'woocommerce_after_single_product_summary', 'ov_add_cross_sells', 20 );


/* ---- Checkout - Add Liftgate option ---- */

/**
 * Add custom field to the checkout page.
 */

function ov_custom_checkout_field( $checkout ) {

	echo '<div id="ov_custom_checkout_field">';

	woocommerce_form_field(
		'checkout_checkbox_liftgate',
		[
			'type'        => 'checkbox',
			'class'       => [
				'ov-checkout-checkbox form-row',
			],
			'label_class' => ['woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'],
			'input_class' => ['woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'],
			'required'    => false,
			'label'       => 'Liftgate needed',
		]
	);

	echo '</div>';
}
add_action( 'woocommerce_after_order_notes', 'ov_custom_checkout_field' );

/**
 * Update the value given in custom field.
 */

function ov_custom_checkout_field_update_order_meta( $order_id ) {

	if ( ! empty( $_POST['checkout_checkbox_liftgate'] ) ) {
		update_post_meta( $order_id, 'checkout_checkbox_liftgate', sanitize_text_field( $_POST['checkout_checkbox_liftgate'] ) );
	}

}
add_action( 'woocommerce_checkout_update_order_meta', 'ov_custom_checkout_field_update_order_meta' );

/**
 * Display field value on the backend WooCommerce order and emails.
 */

function ov_checkout_field_display_order_meta( $order ) {
	$meta_liftgate = get_post_meta( $order->get_id(), 'checkout_checkbox_liftgate', true );
	$meta_liftgate = ( $meta_liftgate ) ? 'Yes' : 'No';
	echo '<p>' . esc_html( 'Liftgate needed' ) . ': ' . esc_html( $meta_liftgate ) . '<p>';
}
add_action( 'woocommerce_order_details_after_order_table', 'ov_checkout_field_display_order_meta', 10, 1 );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'ov_checkout_field_display_order_meta', 10, 1 );
add_action( 'woocommerce_email_after_order_table', 'ov_checkout_field_display_order_meta', 10, 1 );

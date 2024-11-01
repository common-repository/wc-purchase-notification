<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wc_purchase_notification_verify_callback() {
    global $post;	
	$shop_orders = get_posts(
		array(
			'numberposts'   => -1,
			'post_status'   => 'any',
			'post_type'     => 'shop_order',
		)
	);
    
    $success = false;	
	if( $shop_orders ) {
		foreach ( $shop_orders as $shop_order ) { 
			$order_wcn = get_post_meta( $shop_order->ID, '_order_wc_purchase_notification', true );
            if( empty( $order_wcn ) ) {
                update_post_meta( $shop_order->ID, '_order_wc_purchase_notification', '1' );
                $success = true;
            }
		}
        wc_reset_postdata();
        if( $success ) {
            echo "success";
            exit();
        }
    } else {    
		wc_reset_postdata();
		echo "not-found";
		exit();
	}
}

add_action( 'wp_ajax_wc_purchase_notification_verify', 'wc_purchase_notification_verify_callback' );
add_action( 'wp_ajax_nopriv_wc_purchase_notification_verify', 'wc_purchase_notification_verify_callback' );




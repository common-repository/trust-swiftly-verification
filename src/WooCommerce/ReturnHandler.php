<?php

namespace TrustswiftlyVerification\WooCommerce;

use WP_REST_Request;

class ReturnHandler 
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerApiUrls']);
    }

    public function registerApiUrls()
    {
        register_rest_route('ts/v1', '/return', [
            'methods' => 'GET',
            'callback' => [$this, 'onReturn'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function onReturn(WP_REST_Request $request)
    {
        $verifyBeforeCheckout = ts_get_option('verify_on', Verification::VERIFY_BEFORE_CHECKOUT) == Verification::VERIFY_BEFORE_CHECKOUT;
        $orderId = $request->get_param('order_id');

        // var_dump($verifyBeforeCheckout, $verifyBeforeCheckout || ! $orderId);
        // die;
        
        if ($verifyBeforeCheckout || ! $orderId) {
            wp_safe_redirect(
                // add_query_arg(['is_verified' => 1], wc_get_checkout_url())
                wc_get_checkout_url()
            );
            die;
        }

        $order = wc_get_order($orderId);

        if (! $order) {
            wp_safe_redirect(
                // add_query_arg(['is_verified' => 1], wc_get_checkout_url())
                wc_get_checkout_url()
            );
            die;
        }

        $url = $order->get_checkout_order_received_url();

        wp_safe_redirect(
            add_query_arg(
                ['is_verified' => 1], 
                $url
            )
        );
        die;
    }

    public static function listenOn()
    {
        $sBaseURL = str_replace('http://', 'https://', get_rest_url(null, '/ts/v1/return/'));
        if(strpos($sBaseURL,'?')!==false){
            $sURL = $sBaseURL . '&order_id=[order_id]';
        }else{
            $sURL = $sBaseURL . '?order_id=[order_id]';
        }
        return $sURL;
    }
}
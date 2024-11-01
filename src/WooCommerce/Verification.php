<?php

namespace TrustswiftlyVerification\WooCommerce;

use TrustSwiftly\Exceptions\ApiException;
use TrustSwiftly\Responses\UserCreateResponse;
use TrustSwiftly\Responses\UserMagicLinkResponse;
use TrustswiftlyVerification\TrustVerifyPlugin;
use TrustswiftlyVerification\Settings\Settings;

class Verification 
{
    const USER_ALREADY_VERIFIED = 'user_already_verified';
    const OK = 'ok';

    const VERIFY_BEFORE_CHECKOUT = 'before_checkout';
    const VERIFY_AFTER_CHECKOUT = 'after_checkout';

    public function __construct()
    {
        add_filter( 'query_vars', [$this,'add_query_vars_filter'] );
        add_action('wp_ajax_ts_get_user_verification', [$this, 'getUserVerification']);
        add_action('wp_ajax_nopriv_ts_get_user_verification', [$this, 'getUserVerification']);
        add_action('wp_ajax_nopriv_ts_verifyVerification', [$this, 'verifyVerification_forBlocks']);
        add_action('wp_ajax_ts_verifyVerification', [$this, 'verifyVerification_forBlocks']);
        add_action('wp_ajax_ts_check_user_verification', [$this, 'checkUserVerification']);
        add_action('wp_ajax_nopriv_ts_check_user_verification', [$this, 'checkUserVerification']);

        add_action('woocommerce_thankyou', [$this, 'maybeRenderVerification']);
        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'displayCustomOrderData']);

        add_action('woocommerce_after_checkout_validation', [$this, 'verifyVerification'], 10, 2);

        add_filter( 'manage_edit-shop_order_columns', [$this,'ts_add_new_order_admin_list_column'] );

        add_action( 'manage_shop_order_posts_custom_column', [$this,'ts_add_new_order_admin_list_column_content'] );

        add_action( 'woocommerce_review_order_before_payment', [$this,'refresh_payment_methods'] );
        add_action('wp_footer', [$this, 'ExtraFooter'], 9999);

        // add_action('init', function() {
        //     $v = ts_is_user_verified(17);
        //     var_dump($v);
        //     die;
        // });
        

        // add_action('init', function() {
        //     if (isset($_GET['delete_ts_user'])) {
        //         $user = wp_get_current_user();
            
        //         if (! $user) {
        //             return;
        //         }

        //         $userId = get_user_meta($user->ID, '_trust_user_id', true);

        //         delete_user_meta($user->ID, '_trust_user_id');
        //         delete_user_meta($user->ID, '_trust_data');
        //         delete_user_meta($user->ID, '_trust_embed_user_id');

        //         if ($userId) {
        //             $api = ts_api();
        //             $api->userClient()->deleteUser($userId);
        //         }
        //     }
            
        // });
    }
    public function ExtraFooter(){
        echo'<input type="hidden" id="sURL" value="'.get_site_url().'">';
    }
    function refresh_payment_methods(){
        $loadScripts = Verification::shouldVerifyBeforeCheckoutAtAssets();
        if ($loadScripts){
            $chosen_payment_method = WC()->session->get('chosen_payment_method');
            $applicableMethodsData=ts_get_option('applicable_payment_methods');
            ?>
            <script type="text/javascript">
                var j_method = <?='"'. $chosen_payment_method .'"'?>;
                var availableArray = <?= json_encode($applicableMethodsData) ?>;
                (function($){
                    $( 'form.checkout' ).on( 'change', 'input[name^="payment_method"]', function(event) {
                        var payment_method = $('form.checkout').find('input[name^="payment_method"]:checked').val();
                        if(jQuery.inArray(payment_method, availableArray) !== -1)
                        {
                            TSCheckoutConfig.payment_method = payment_method;
                            window.TSVerification.init();
                        }else{
                            window.TSVerification.hideIt();
                        }
                        //set new value
                        j_method = $('form.checkout').find('input[name^="payment_method"]:checked').val();
                    });
                })(jQuery);
            </script>
            <?php
        }
    }

    public function ts_add_new_order_admin_list_column_content($column){
        global $post;
        if ( 'ts_verify_status' === $column ) {
            $order = wc_get_order($post->ID);
            $user = $order->get_user();
            $tsUserId = get_user_meta($user->ID, '_trust_user_id', true);

            if (!$tsUserId) {
                echo "Unknown";
            }

            $url = $this->getTSUserShowUrl($tsUserId);
            if (!$url) {
                echo "Unknown";
            }

            $isVerified = ts_is_user_verified($user->ID);

            if ($isVerified) {
                echo "Verified";
            } else {
                echo "Unverified";
            }
        }

    }

    public function ts_add_new_order_admin_list_column($columns){
        $reordered_columns = array();
        // Inserting columns to a specific location
        foreach( $columns as $key => $column){
            $reordered_columns[$key] = $column;
            if( $key ==  'order_status' ){
                // Inserting after "Status" column
                $reordered_columns['ts_verify_status'] = 'Verify Status';
            }
        }
        return $reordered_columns;
    }
    public function add_query_vars_filter( $vars ){
        $vars[] = "is_verified";
        return $vars;
    }
    public function verifyVerification_forBlocks(){
        $aAnswer = ['not_verified'=>0,'skip'=>0,'message'=>''];
        $email = sanitize_text_field($_POST['email']);
        
        $payment_method = sanitize_text_field($_POST['payment_method'] );
        
        if (!ts_get_option('allow_guest_checkout_verify') && !is_user_logged_in()) {
            $aAnswer['skip'] = 2;
        }
        $verifyAfterCheckout = ts_get_option('verify_on', static::VERIFY_BEFORE_CHECKOUT) == static::VERIFY_AFTER_CHECKOUT;
        if ($verifyAfterCheckout){
            $aAnswer['skip'] = 3;
        }
        
        if (is_user_logged_in()) {
            
            $user = wp_get_current_user();
            $isVerified = ts_is_user_verified($user->ID);
            // $isVerified = true;

            if (!$isVerified) {
                if (self::shouldVerifyBeforeCheckout($payment_method)) {
                    $aAnswer['message'] = __('Please complete the verification requirements by clicking the 🛡️ Trust Swiftly button prior to paying', 'trustswiftly-verification');
                } else {
                    $aAnswer['skip'] = 4;
                }
            }
        }else if (ts_get_option('allow_guest_checkout_verify') && ($email)){
            if($email){
                $user = $this->tryFindUser($email);
            }

            if ($user){
                $isVerified = ts_array_get($user, 'is_verified', false);
                if (!$isVerified) {
                    
                    if (self::shouldVerifyBeforeCheckout($payment_method)) {
                        $aAnswer['message'] = __('Please complete the verification requirements by clicking the 🛡️ Trust Swiftly button prior to paying', 'trustswiftly-verification');
                    } else {
                        $aAnswer['skip'] = 1;
                    }
                    
                }
            }
        }
        $aJSONAnswer = json_encode($aAnswer);
        header('Content-type: application/json');
        echo($aJSONAnswer);
        die();
    }
    public function verifyVerification($fields, $errors)
    {
        if (!ts_get_option('allow_guest_checkout_verify') && !is_user_logged_in()) {
            return $errors;
        }
        $verifyAfterCheckout = ts_get_option('verify_on', static::VERIFY_BEFORE_CHECKOUT) == static::VERIFY_AFTER_CHECKOUT;
        if ($verifyAfterCheckout){
            return $errors;
        }
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $isVerified = ts_is_user_verified($user->ID);

            if (!$isVerified) {
                if (self::shouldVerifyBeforeCheckout()) {
                    $errors->add(
                        'validation',
                        __('Please complete the verification requirements by clicking the 🛡️ Trust Swiftly button prior to paying', 'trustswiftly-verification')
                    );
                } else {
                    return $errors;
                }
            }
        }else if (ts_get_option('allow_guest_checkout_verify') && ($fields['billing_email'] || $fields['email'])){
            if($fields['email']){
                $user = $this->tryFindUser($fields['email']);
            }
            if($fields['billing_email']){
                $user = $this->tryFindUser($fields['billing_email']);
            }
            
            if ($user){
                $isVerified = $user['is_verified'];
                if (!$isVerified) {
                    if (self::shouldVerifyBeforeCheckout()) {
                        $errors->add(
                            'validation',
                            __('Please complete the verification requirements by clicking the 🛡️ Trust Swiftly button prior to paying', 'trustswiftly-verification')
                        );
                    } else {
                        return $errors;
                    }
                }
            }

        }

        return $errors;
    }

    public function displayCustomOrderData($order)
    {
        /** @var \WC_Order $order */
        $user = $order->get_user();
        if(!isset($user->ID)){
            return '';
        }
        $tsUserId = get_user_meta($user->ID, '_trust_user_id', true);

        if (! $tsUserId) {
            return;
        }

        $url = $this->getTSUserShowUrl($tsUserId);
        if (! $url) {
            return;
        }

        $msg = __('View User', 'trustswiftly-verifications');
        $isVerified = ts_is_user_verified($user->ID);

        printf(
            '<h3>%s</h3><a href="%s" target="_blank">%s</a> - %s', 
            'Trust Swiftly', 
            $url, 
            $msg,
            $isVerified ? __('Complete', 'trustswiftly-verification') : __('Pending', 'trustswiftly-verification')
        );
    }
    protected function CreateTheUserIfNotExists($orderId){
        if(!is_user_logged_in()){
            $order = wc_get_order($orderId);
            $email = $order->get_billing_email();
            $userId = email_exists($email);
            if(empty($userId )){
                $username = sanitize_user(current(explode('@', $email)), true);
                if (username_exists($username)) {
                    $username = $username . '_' . wp_generate_password(4, false, false);
                }
                $password = wp_generate_password();
                $userId = wp_create_user($username, $password, $email);
                $order->set_customer_id($userId);
                $order->save();

            }else{
                if(empty($order->get_user_id())){
                    $order->set_customer_id($userId);
                    $order->save(); 
                }
            }
            if(!empty($userId)){
                 $aData = get_user_meta($userId, '_trust_data', true);
                 if(empty($aData)){
                     $username = sanitize_user(current(explode('@', $email)), true);
                     $aVerifications = get_option('a_verifications');
                     $aTrustIDs = get_option('a_trust_ids');
                     if(isset($aTrustIDs[$username])){
                        if(isset($aVerifications[$username])){
                            update_user_meta($userId, '_trust_data', $aVerifications[$username]); 
                        }
                        update_user_meta($userId, '_trust_user_id', isset($aTrustIDs[$username])?$aTrustIDs[$username]:0);
                     }
                 }
            }
            
        }
    }
    public function maybeRenderVerification($orderId)
    {  
        $verifyAfterCheckout = ts_get_option('verify_on', static::VERIFY_BEFORE_CHECKOUT) == static::VERIFY_AFTER_CHECKOUT;
        if (! $verifyAfterCheckout) {
            $this->CreateTheUserIfNotExists($orderId);
            return;
        }
        if (!is_user_logged_in() && !ts_get_option('allow_guest_checkout_verify')){
            return;
        }

        if (! static::shouldVerifyAfterCheckout($orderId)) {
            return;
        }
        if(!is_user_logged_in()){
            $order = wc_get_order($orderId);
            $email = $order->get_billing_email();
            $userId = email_exists($email);
            if(empty($userId )){
                $username = sanitize_user(current(explode('@', $email)), true);
                if (username_exists($username)) {
                    $username = $username . '_' . wp_generate_password(4, false, false);
                }
                $password = wp_generate_password();
                $userId = wp_create_user($username, $password, $email);
                $order->set_customer_id($userId);
                $order->save();

            }
            $user = get_user_by( 'id', $userId );
            if(isset($user->data)){
                $user = $user->data;
            }
        }else{
            $user = wp_get_current_user();
        }
        
        $isUserAlreadyVerified = ts_is_user_verified($user->ID);
        
        if ($isUserAlreadyVerified) {
            echo ts_render('post-order-verification.php', [
                'btnImg' => TrustVerifyPlugin::pluginUrl() . 'assets/img/' . ts_get_option('btn_img', Settings::defaultBtnImg()),
                'isVerified' => true,
                'link' => null
            ]);

            return;
        }

        $trustUserId = get_user_meta($user->ID, '_trust_user_id', true);
        $trustEmbedUserId = get_user_meta($user->ID, '_trust_embed_user_id', true);
        if (! $trustUserId || !$trustEmbedUserId) {
            $userId = $this->createOrFindTsUser($user->ID, $user->user_email);

            if (! $userId) {
                return;
            }

            $trustUserId = $userId[0];
            $trustEmbedUserId = $userId[1];
            if ($trustUserId) {
                update_user_meta($user->ID, '_trust_user_id', $trustUserId);
            }
            if ($trustEmbedUserId) {
                update_user_meta($user->ID, '_trust_embed_user_id', $trustEmbedUserId);
            }
        }

        $this->updateOrderId($trustUserId, $orderId);

        $link = $this->getMagicLink($trustUserId);

        echo ts_render('post-order-verification.php', [
            'btnImg' => TrustVerifyPlugin::pluginUrl() . 'assets/img/' . ts_get_option('btn_img', Settings::defaultBtnImg()),
            'isVerified' => false,
            'link' => $link,
            'user_email'=>$user->user_email
        ]);
    }

    public function checkUserVerification(){

        $user = wp_get_current_user();

        $isUserAlreadyVerified = ts_is_user_verified($user->ID);

        if ($isUserAlreadyVerified) {
            return wp_send_json_success([
                'is_verified' => 1,
            ]);
        }

        return wp_send_json_success([
            'is_verified' => 0,
        ]);
    }

    public function getUserVerification()
    {
        check_ajax_referer('ts_action', 'nonce');

        $email = sanitize_email(ts_array_get($_POST, 'email'));
        $thank_you_page = sanitize_text_field(ts_array_get($_POST, 'thank_you_page'));

        if (! $email) {
            return wp_send_json_error([
                'message' => __('Email is required'. 'trustswiftly-verification')
            ]);
        }

        $paymentMethod = sanitize_text_field(ts_array_get($_POST, 'payment_method'));
        
        if ($thank_you_page!=="1") {
            if ($paymentMethod!=="unknown") {
                if (!static::shouldVerifyBeforeCheckout($paymentMethod)) {
                    return wp_send_json_error();
                }
            }else{
                if (!static::shouldVerifyBeforeCheckout()) {
                    return wp_send_json_error();
                }
            }
        }

        if (is_user_logged_in()) {

            $user = wp_get_current_user();
            $trustUserId = null;
            $isUserAlreadyVerified = ts_is_user_verified($user->ID);

            if ($isUserAlreadyVerified) {
                return wp_send_json_success([
                    'type' => static::USER_ALREADY_VERIFIED,
                ]);
            }

            $trustUserId = get_user_meta($user->ID, '_trust_user_id', true);
            $trustEmbedUserId = get_user_meta($user->ID, '_trust_embed_user_id', true);
            /*check whether the user exists to prevent exclusion when the user is deleted from the portal*/
            if(!empty($trustUserId)&&!$this->isUserExists($trustUserId)){
                $trustUserId = 0;
                $trustEmbedUserId = 0;
                update_user_meta($user->ID, '_trust_user_id', 0);
                update_user_meta($user->ID, '_trust_embed_user_id', 0);
            }
           
            if (! $trustUserId || ! $trustEmbedUserId) {
               
                $userId = $this->createOrFindTsUser($user->ID, $email);
                if (! $userId) {
                    return wp_send_json_error([
                        'message' => __('Error occurred', 'trustswiftly-verification')
                    ]);
                }

                $trustUserId = $userId[0];
                $trustEmbedUserId = $userId[1];
                if ($trustUserId) {
                    update_user_meta($user->ID, '_trust_user_id', $trustUserId);
                }
                if ($trustEmbedUserId) {
                    update_user_meta($user->ID, '_trust_embed_user_id', $trustEmbedUserId);
                }
            }
        } else if (ts_get_option('allow_guest_checkout_verify')) {
            $tsUser = $this->tryFindUser($email);
            $isUserAlreadyVerified = $tsUser ? ts_array_get($tsUser, 'is_verified', false) : false;
            // echo '<pre>';
            // var_dump($isUserAlreadyVerified);
            // die;
            if ($isUserAlreadyVerified) {
                return wp_send_json_success([
                    'type' => static::USER_ALREADY_VERIFIED,
                ]);
            }
            $userIds = $this->createOrFindTsUser(null, $email);
            if (! $userIds) {
                return wp_send_json_error([
                    'message' => __('Error occurred', 'trustswiftly-verification')
                ]);
            }

            $trustUserId = $userIds[0];
            $trustEmbedUserId = $userIds[1];

        }else{
            return wp_send_json_error();
        }
        
        if (ts_get_option('verification_method', Settings::defaultVerificationMethod()) == 'modal') {
            $html = $this->getModalHtml();

            return wp_send_json_success([
                'ts_user_id' => $trustUserId,
                'ts_embed_user_id' => $trustEmbedUserId,
                'type' => static::OK,
                'method' => 'modal',
                'html' => $html,
                'ts_user_signature'=>$this->getSignature($trustEmbedUserId)
            ]);
        }
         
        $link = $this->getMagicLink($trustUserId);
        
        return wp_send_json_success([
            'ts_user_id' => $trustUserId,
            'ts_embed_user_id' => $trustEmbedUserId,
            'method' => 'link',
            'type' => static::OK,
            // 'type' => static::USER_ALREADY_VERIFIED,
            'link' => $link
        ]);
    }

    public function getSignature($userId)
    {
        if (! $userId) {
            return '';
        }

        // $embedKey = 'xxxxxxx';
        // $secret = 'xxxxxxxxx';
        // $baseUrl = ts_get_option('base_url');
        $embedKey = ts_get_option('embed_key');
        $secret = ts_get_option('api_secret');

        $timestamp = time();
        $payloadString = $embedKey . $userId . $timestamp;
        $hash = hash_hmac('sha256', $payloadString, $secret);
        $signature = 't=' . $timestamp . ',v2=' . $hash;

        return $signature;

        // $api = ts_api();

        // return $api->getVerifyCredentailsSignature();
    }

    public function getModalHtml()
    {
        return '<div id="trustVerify" class="bg-light p-5 rounded ts_emb_verify" style="margin-top: 30px"></div>';
    }

    protected function createOrFindTsUser($userId, $email)
    {
        $api = ts_api();
        try {
            $user = $this->tryFindUser($email);
            if ($user) {
                return [$user['id'],$user['user_id']];
            }
            if(empty($email)){
                return false;
            }
            $params = [
                'email' => $email,
                'template_id' => ts_get_option('template_id', Settings::getDefaultTemplateId()),
                'reference_id' => $userId
            ];
            if (ts_get_option('send_verify_link',false)){
                $params['send_link']=true;
            }

            $response = $api->userClient()->createUser($params);

            if (! $response instanceof UserCreateResponse || ts_array_get($response->createArray, 'status') !== 'success') {
                return false;
            }
    
            $userId = ts_array_get($response->createArray, 'id');
            $embedUserId = ts_array_get($response->createArray, 'user_id');

            return [$userId, $embedUserId];
    
        } catch (ApiException $e) {
            
            return null;
        }
    }
    public function isUserExists($user_id){
        $api = ts_api();
        return $api->userClient()->isUserExists($user_id);
    }
    public function getMagicLink($userId)
    {
        $api = ts_api();
        try {
            $link = $api->userClient()->getMagicLink($userId);
            if (! $link instanceof UserMagicLinkResponse) {
                return null;
            }
    
            return $link->magicArray['full_url'];
        } catch (ApiException $e) {
            return null;
        }
    }

    public function tryFindUser($email)
    {
        $api = ts_api();

        $users = $api->userClient()->getAllUsers([
            'filter' => [
                // 'search' => $email
                'email' => $email
            ]
        ]);
        // return ts_array_get($users, 'data', 0);
        return ts_array_get($users['data'], 0);
    }

    public function getTSUserShowUrl($tsUserId)
    {
        $baseUrl = ts_get_option('base_url');
        if (! $baseUrl) {
            return '';
        }

        $baseUrl = untrailingslashit($baseUrl) . "/user/{$tsUserId}/show";

        return $baseUrl;
    }

    public static function shouldVerifyBeforeCheckoutAtAssets(){
        $verifyBeforeCheckout = ts_get_option('verify_on', Verification::VERIFY_BEFORE_CHECKOUT) == Verification::VERIFY_BEFORE_CHECKOUT;

        if (! $verifyBeforeCheckout) {
            return false;
        }

        $categories = (array)ts_get_option('product_categories', []);
        $comparePriceMode = ts_get_option('compare_price_mode');
        $comparePrice = ts_get_option('compare_price');
        $isPriceChecked = true;

        if ($comparePriceMode && $comparePrice>0) {
            $cartTotalPrice = (double) WC()->cart->get_total('edit');
            $comparePrice = doubleval($comparePrice);
            $isPriceChecked = false;

            if ($comparePriceMode == 'less_than') {
                $isPriceChecked = $comparePrice > $cartTotalPrice;
            }
            else {
                $isPriceChecked = $comparePrice < $cartTotalPrice;
            }
        }

        return (empty($categories) || ts_cart_has_products_in_categories($categories))
            && $isPriceChecked
            ;
    }

    public static function shouldVerifyBeforeCheckout($paymentMethod = null)
    {
        $verifyBeforeCheckout = ts_get_option('verify_on', Verification::VERIFY_BEFORE_CHECKOUT) == Verification::VERIFY_BEFORE_CHECKOUT;

        if (! $verifyBeforeCheckout) {
            return false;
        }

        $categories = (array)ts_get_option('product_categories', []);
        $comparePriceMode = ts_get_option('compare_price_mode');
        $comparePrice = ts_get_option('compare_price');
        $isPriceChecked = true;
        $applicableMethodsData=ts_get_option('applicable_payment_methods');
        if ($paymentMethod!==null){
            $chosen_payment_method = $paymentMethod;
        }else{
            $chosen_payment_method = WC()->session->get('chosen_payment_method');
        }

        $paymentMethodRestrictionFlag = true;

        if ($applicableMethodsData && count($applicableMethodsData)>0 && !in_array($chosen_payment_method,$applicableMethodsData))
        {

            $paymentMethodRestrictionFlag = false;
        }

        if ($comparePriceMode && $comparePrice>0) {
            $cartTotalPrice = (double) WC()->cart->get_total('edit');
            $comparePrice = doubleval($comparePrice);
            $isPriceChecked = false;

            if ($comparePriceMode == 'less_than') {
                $isPriceChecked = $comparePrice > $cartTotalPrice;
            } 
            else {
                $isPriceChecked = $comparePrice < $cartTotalPrice;
            }
        }

        return (empty($categories) || ts_cart_has_products_in_categories($categories))
            && $isPriceChecked && $paymentMethodRestrictionFlag
        ;
    }

    public static function shouldVerifyAfterCheckout($orderId)
    {
        $verifyAfterCheckout = ts_get_option('verify_on', Verification::VERIFY_AFTER_CHECKOUT) == Verification::VERIFY_AFTER_CHECKOUT;

        if (! $verifyAfterCheckout) {
            return false;
        }

        $order = wc_get_order($orderId);
        if (! $order) {
            return false;
        }

        $orderPaymentMethod = $order->get_payment_method();
        $applicableMethodsData=ts_get_option('applicable_payment_methods');

        $paymentMethodRestrictionFlag = true;

        if ($applicableMethodsData && count($applicableMethodsData)>0 && !in_array($orderPaymentMethod,$applicableMethodsData))
        {
            $paymentMethodRestrictionFlag = false;
        }

        $comparePriceMode = ts_get_option('compare_price_mode');
        $comparePrice = ts_get_option('compare_price');
        $isPriceChecked = true;

        if ($comparePriceMode && $comparePrice>0) {
            $orderTotalPrice = (double) $order->get_total('edit');
            $comparePrice = doubleval($comparePrice);
            $isPriceChecked = false;

            if ($comparePriceMode == 'less_than') {
                $isPriceChecked = $comparePrice > $orderTotalPrice;
            } 
            else {
                $isPriceChecked = $comparePrice < $orderTotalPrice;
            }
        }

        $categories = ts_get_option('product_categories', []);

        return (empty($categories) || ts_order_has_products_in_categories($order, $categories))
            && $isPriceChecked && $paymentMethodRestrictionFlag
        ;
    }

    public function updateOrderId($userId, $orderId)
    {
        $api = ts_api();

        try {
            $response = $api->userClient()->updateUser($userId, [
                'order_id' => $orderId
            ]);

            return true;
        } 
        catch (ApiException $e) {
            return false;
        }
    }
}
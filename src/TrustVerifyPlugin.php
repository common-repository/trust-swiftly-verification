<?php

namespace TrustswiftlyVerification;

use TrustswiftlyVerification\Api\Ipn;
use TrustswiftlyVerification\Settings\Settings;
use TrustswiftlyVerification\WooCommerce\ReturnHandler;
use TrustswiftlyVerification\WooCommerce\Verification;
class TrustVerifyPlugin
{
    /**
     * Singletons
     *
     * @var array
     */
    protected static $container = [];

    /**
     * Singleton global services
     *
     * @var array
     */
    protected static $singletons = [];

    /**
     * Plugin components
     *
     * @var array
     */
    protected $components = [
        Verification::class,
        Ipn::class,
        ReturnHandler::class,
        Settings::class,
    ];

    /**
     * Required plugins
     *
     * @var array
     */
    protected static $requiredPlugins = [
        'woocommerce/woocommerce.php' => 'WooCommerce'
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'checkRequirements']);
        if( !function_exists('is_plugin_active') ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        if (is_plugin_active('trust-swiftly-verification/trustswiftly-verification.php') && is_plugin_active('woocommerce/woocommerce.php')) {
            $this->initComponents();
            add_action('wp_enqueue_scripts', [$this, 'addAssets']);
            add_action('admin_enqueue_scripts', [$this, 'addAdminAssets']);
        }
    }

    /**
     * Activate the plugin
     */
    public static function activate()
    {
        $missingPlugins = [];

        foreach (self::$requiredPlugins as $key => $name) {

            if (! is_plugin_active($key)) {
                $missingPlugins[$key] = $name;
            }
        }

        if (empty($missingPlugins)) {
            return true;
        }

        add_action('admin_notices', function() use ($missingPlugins) {
            $names = rtrim(implode(', ', $missingPlugins), ', ');

            printf(
                '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
                __("Trustswiftly Verification requires <strong>{$names}</strong> to be active", 'trustswiftly-verification')
            );

        });
        deactivate_plugins('trust-swiftly-verification/trustswiftly-verification.php');
        unset($_GET['activate']);
    }

    /**
     * Check if all required plugins are active
     *
     * @return boolean
     */
    public function checkRequirements()
    {
        return self::activate();
    }

    /**
     * Add plugin assets
     *
     * @return void
     */
    public function addAssets()
    {
        $loadScripts = Verification::shouldVerifyBeforeCheckoutAtAssets();
        if (!$loadScripts){
            $loadScripts = ts_get_option('verify_on', Verification::VERIFY_AFTER_CHECKOUT) == Verification::VERIFY_AFTER_CHECKOUT;
        }
        $verificationMethod = ts_get_option('verification_method', Settings::defaultVerificationMethod());
        $isModal = $verificationMethod == 'modal';
        $baseUrl = ts_get_option('base_url');
        $embedKey = ts_get_option('embed_key');

        wp_enqueue_style('ts-bootstrap', 'https://cdn.trustswiftly.com/assets/css/embedded_bootstrap.css');
        wp_enqueue_style('ts-trustverify-fa-css', 'https://cdn.trustswiftly.com/assets/css/modal_fontawesome.css');
        if ($isModal) {
            wp_enqueue_script('ts-bootstrap-js', 'https://cdn.trustswiftly.com/assets/js/embedded_bootstrap.min.js', ['jquery']);
            wp_enqueue_script('ts-trustverify-js', 'https://cdn.trustswiftly.com/assets/js/trustverifyv2.min.js', ['jquery', 'ts-bootstrap-js']);
            wp_enqueue_style('ts-trustverify-css', 'https://cdn.trustswiftly.com/assets/css/trust-verify.css');
        }

        $recievedPage=is_wc_endpoint_url( 'order-received' );
        if ($loadScripts && (is_wc_endpoint_url( 'order-received' ) || is_checkout())) {
            $currentUser = wp_get_current_user();
            $jsDependecies = [
                'jquery',
            ];

            if ($isModal) {
                $jsDependecies[] = 'ts-bootstrap-js';
                $jsDependecies[] = 'ts-trustverify-js';
            }

            wp_enqueue_script('ts-checkout-js', static::pluginUrl() . 'assets/js/checkout.js', $jsDependecies, '1.0');
            wp_enqueue_script('ts-checkout-helper-js', static::pluginUrl() . 'assets/js/checkout-helper.js', $jsDependecies);

            wp_localize_script('ts-checkout-js', 'TSCheckoutConfig', [
                'is_checkout' => is_checkout(),
                'nonce' => wp_create_nonce('ts_action'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'verify_link_text' => __('Verify Your Account', 'trustswiftly-verification'),
                'is_user_verified' => ts_is_user_verified($currentUser->ID),
                'verify_before_checkout' => ts_get_option('verify_on', Verification::VERIFY_AFTER_CHECKOUT) == Verification::VERIFY_BEFORE_CHECKOUT,
                'btn_img' => self::pluginUrl() . 'assets/img/' . ts_get_option('btn_img', Settings::defaultBtnImg()),
                'verification_method' => $verificationMethod,
                'thank_you_page' => $recievedPage,
                'embed_key' => $embedKey,
                'payment_method' => "unknown",
                'signature' => $this->getSignature(
                    get_user_meta($currentUser->ID, '_trust_embed_user_id', true)
                ),
                'base_url' => $baseUrl,
            ]);
            wp_enqueue_style('ts-app-css', static::pluginUrl() . 'assets/css/app.css');
            wp_add_inline_style('ts-app-css',".ts_emb_verify{".ts_get_option('custom_css')."}");
        }
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

    /**
     * Add admin assets
     *
     * @return void
     */
    public function addAdminAssets()
    {
        // Add admin assets
        $screen = get_current_screen();

        if ($screen && $screen->id == 'toplevel_page_ts-settings') {
            
            wp_enqueue_style('ts-admin-css', static::pluginUrl() . 'assets/css/admin.css');
            wp_enqueue_style('ts-select2', static::pluginUrl() . 'assets/css/select2.min.css');
    
            wp_enqueue_script('ts-select2',  static::pluginUrl() . 'assets/js/select2.min.js', [
                'jquery'
            ]);
    
            wp_enqueue_script('ts-admin-js',  static::pluginUrl() . 'assets/js/admin.js', [
                'jquery',
                'ts-select2'
            ]);
            $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
            wp_localize_script('jquery', 'cm_settings', $cm_settings);

            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');
        }
    }

    /**
     * Init plugin components
     *
     * @return void
     */
    public function initComponents()
    {
        foreach ($this->components as $component) {
            new $component();
        }
    }

        /**
     * Get a singleton servce
     *
     * @param string $class
     * @return mixed
     */
    public static function get($class)
    {
        if (! class_exists($class) || ! in_array($class, static::$singletons)) {
            return null;
        }

        if (! isset(static::$container[$class])) {
            static::$container[$class] = new $class;
        }

        return static::$container[$class];
    }

    /**
     * Get Plugin Url
     *
     * @return string
     */
    public static function pluginUrl()
    {
        return plugin_dir_url(TS_PLUGIN_BASE_PATH);
    }

    /**
     * Get Plugin Path
     *
     * @return string
     */
    public static function pluginDir()
    {
        return plugin_dir_path(TS_PLUGIN_BASE_PATH);
    }
}

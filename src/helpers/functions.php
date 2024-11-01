<?php

if (! function_exists('ts_render')) {
    function ts_render($view, array $args = []) {
        ob_start();
        extract($args);       
        
        $templatePath = 'trustswiftly-verification/' . $view;
        $template = locate_template($templatePath);

        if ($template) {
            require $template;

            return ob_get_clean();
        }

        $file_path = ts_get_view_path($view);

        if (! $file_path) {
            return '';
        }

        require $file_path;

        return ob_get_clean();
    }
}

if (! function_exists('ts_get_view_path')) {
    function ts_get_view_path($view) {
        $file_path = TS_PLUGIN_ROOT . '/views/' . ltrim($view, '/');
 
        if (! file_exists($file_path)) {
            return '';
        }

        return $file_path;
    }
}


if (! function_exists('ts_array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function ts_array_get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }
}

if (! function_exists('ts_config')) {
    function ts_config($key, $default = null) {
        $config_path = TS_PLUGIN_ROOT . '/config/config.php';
        $theme_config_path = get_stylesheet_directory() . '/trustswiftly-verification/config/config.php';

        $config = require $config_path;
        $theme_config = [];

        if (file_exists($theme_config_path)) {
            $theme_config = require $theme_config_path; 
        }

        $config = array_merge($config, $theme_config);

        return ts_array_get($config, $key, $default);
    }
}

if (! function_exists('ts_log')) {
    function ts_log($data, $append = true, $filename = 'logs.log') {
        $dir = wp_get_upload_dir();
        $dir = $dir['basedir'] . '/trustswiftly-verification/';

        if (! file_exists($dir)) {
            mkdir($dir);
        }

        $file = $dir . $filename;

        file_put_contents($file, current_time('Y-m-d H:i:s') . ': ' . json_encode($data) . "\n", $append ? FILE_APPEND : 0);
    }
}

if (! function_exists('ts_array_first')) {
    /**
     * Return the first element in an array passing a given truth test.
     *
     * @param  array $array
     * @param  \Closure $callback
     * @param  mixed $default
     * @return mixed
     */
    function ts_array_first($array, $callback, $default = null)
    {
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }

        return $default;
    }
}

if (! function_exists('ts_user_has_role')) {
    function ts_user_has_role($roles, $user = null) {
        if (! $user && ! is_user_logged_in()) {
            return false;
        }

        if (! $user) {
            $user = wp_get_current_user();
        }

        $has_role = false;
        $roles = (array) $roles;
        
        foreach($roles as $role) {
            if (in_array($role, (array)$user->roles)) {
                $has_role = true;
                break;
            }
        }

        return $has_role;
    }
}

if (! function_exists('ts_get_user_ip')) {
    function ts_get_user_ip() {
        $ip_addr = null;

        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
        {
            if (array_key_exists($key, $_SERVER) === true)
            {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip)
                {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
                    {
                        return $ip;
                    }
                }
            }
        }

        return $ip_addr;
    }
}


if (! function_exists('ts_get_option')) {
    function ts_get_option($key, $default = null) {
        static $options = null;

        if (is_null($options)) {
            $options = get_option('trustswiftly_verification');
        }

        return !empty($options[$key]) ? $options[$key] : $default;
    }
}

if (! function_exists('ts_api')) {
    function ts_api() {
        static $tsApi = null;
        
        if (! $tsApi) {
            $apiKey = ts_get_option('api_key');
            $baseUrl = ts_get_option('base_url');
            $apiSecret = ts_get_option('api_secret');
            $embedKey = ts_get_option('embed_key');
    
            $tsApi = new TrustSwiftly\TrustSwiftly($apiKey, $baseUrl, $apiSecret, $embedKey);
        }
        
        return $tsApi;
    }
}

if (! function_exists('ts_is_user_verified')) {
    function ts_is_user_verified($id) {
        
        $user = get_user_by('ID', $id);
        if (! $user) {
            return false;
        }

        $trustData = get_user_meta($user->ID, '_trust_data', true);
        if (! $trustData) {
            return false;
        }
        
        $verifications = ts_array_get($trustData, 'required_verifications');
        if(empty($verifications)&&is_array($trustData)){
            $verifications = $trustData;
        }
        $isComplete = true;

        foreach ($verifications as $status) {
            if (!in_array(intval($status),[2,4])) {
                $isComplete = false;
            }
        }
        
        return $isComplete;
    }
}


if (! function_exists('ts_cart_has_products_in_categories')) {
    function ts_cart_has_products_in_categories(array $categories) {

        if (! WC()->cart || WC()->cart->is_empty() || empty($categories)) {
            return false;
        }

        $hasCategories = false;

        foreach (WC()->cart->get_cart() as $cartItem) {
            /** @var \WC_Product $product */
            $product = $cartItem['data'];

            if (has_term($categories, 'product_cat', $product->get_id())) {
                $hasCategories = true;
            }
        }

        return $hasCategories;
    }
}

if (! function_exists('ts_order_has_products_in_categories')) {
    function ts_order_has_products_in_categories(WC_Order $order, array $categories) {

        if (empty($categories)) {
            return false;
        }

        $hasCategories = false;

        foreach ($order->get_items() as $item) {
            $product = $item->get_data();

            if (has_term($categories, 'product_cat', $product['product_id'])) {
                $hasCategories = true;
            }
        }

        return $hasCategories;
    }
}
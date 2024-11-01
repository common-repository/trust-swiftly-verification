<?php

namespace TrustswiftlyVerification\Api;

use TrustSwiftly\TrustSwiftly;
use WP_REST_Request;

class Ipn 
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerApiUrls']);
    }

    public function registerApiUrls()
    {
        
        register_rest_route('ts/v1', '/ipn', [
            'methods' => 'POST',
            'callback' => [$this, 'listenToIpn'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function listenToIpn(WP_REST_Request $request)
    {
        $isValid = $this->verifyIpnRequest($request);

        if (! $isValid) {
            status_header(500, 'Tampered Request');
            die;
        }

        $userId = $request->get_param('reference_id');
        $userEmail = $request->get_param('email');
        $iTrustUserID = $request->get_param('trust_id');
        
        $requiredVerifications = [];
        $bCompleted = false;
        foreach($request->get_param('verifications') as $verification) {
            $requiredVerifications[$verification['id']] = $verification['status']['value'];
            if($verification['status']['value']==2||$verification['status']['value']==4){
                $bCompleted = true;
            }
        }
        if(empty($userId)&&!empty($userEmail)&&$bCompleted){
            $username = sanitize_user(current(explode('@', $userEmail)), true);
            $aVerifications = get_option('a_verifications');
            $aTrustIDs      = get_option('a_trust_ids');
            if(empty($aVerifications)){
                $aVerifications = [];
            }
            if(empty($aTrustIDs)){
                $aTrustIDs= [];
            }
            $aTrustIDs[$username] = $iTrustUserID;
            $aVerifications[$username] = $requiredVerifications;
            update_option('a_verifications',$aVerifications);
            update_option('a_trust_ids',$aTrustIDs);

        }

        $user = get_user_by('ID', $userId);
        
        // ts_log([
        //     'user' => $user->user_email,
        //     'id' => $userId
        // ], true, 'ts_ipn.log');

        if (! $user) {
            return;
        }

        
        
        update_user_meta($user->ID, '_trust_data', ['required_verifications' => $requiredVerifications]);

        // if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            ts_log([
                'signature' => $request->get_header('Signature'),
                'requiredVerifications' => $requiredVerifications,
                'request' => $request->get_params()
            ], true, 'ts_ipn.log');
        // }
    }

    protected function verifyIpnRequest(WP_REST_Request $request)
    {
        $webhookSecret = ts_get_option('webhook_secret');
        $receivedSignature = $request->get_header('Signature');
        
        try {
            $result = TrustSwiftly::verifyWebhookSignature($receivedSignature, file_get_contents("php://input"), $webhookSecret);
        } 
        catch (\Exception $e) {
            
            ts_log($e->getMessage(), true, 'ts_ipn.log');

            $result = false;
        }

        return $result;

    }

    public static function listenOn()
    {
        return str_replace('http://', 'https://', get_rest_url(null, '/ts/v1/ipn'));
    }
}
<?php

namespace TrustSwiftly;

use TrustSwiftly\Api\Statistics\StatisticsApiClient;
use TrustSwiftly\Api\Templates\TemplateApiClient;
use TrustSwiftly\Api\Users\UserApiClient;
use TrustSwiftly\Api\Validation\CredentialValidationClient;
use TrustSwiftly\Managers\ConfigManager;
use GuzzleHttp\Client as Guzzle;
use TrustSwiftly\Security\EmbedSignature;
use TrustSwiftly\Security\ValidationSignature;
use TrustSwiftly\Security\WebhookSignature;


class TrustSwiftly{

    /**
     * @var $trustConfigs
     */
    protected $trustConfigs;


    /**
     * @var Guzzle
     */
    protected $guzzle;

    /**
     *
     * @var TrustSwiftly
     */
    private static $instance;

    /**
     * @param $api_key
     * @param $base_url
     * @param null $api_secret
     * @param null $embed_key
     * @throws Exceptions\ConfigException
     */
    public function __construct($api_key,$base_url,$api_secret=null,$embed_key=null)
    {
            $configs=[
                'api_key'=>$api_key,
                'base_url'=>$base_url,
                'api_secret'=>$api_secret,
                'embed_key'=>$embed_key
            ];
            $this->setConfig($configs);
            $this->guzzle=new Guzzle();
    }

    public static function getInstance($api_key,$base_url,$api_secret=null,$embed_key=null)
    {
        if ( is_null( self::$instance ) )
        {
            self::$instance = new self($api_key,$base_url,$api_secret,$embed_key);
        }
        return self::$instance;
    }

    /**
     * @param $configs
     */
    public function setConfig($configs){
        try {
            $this->trustConfigs = new ConfigManager($configs);
        }catch (\Exception $e){
            exit($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getConfigs(){
        return $this->trustConfigs;
    }

    /**
     * @return UserApiClient
     */
    public function userClient(){
        return (new UserApiClient($this->guzzle,$this->trustConfigs));
    }

    /**
     * @return TemplateApiClient
     */
    public function templateClient(){
        return (new TemplateApiClient($this->guzzle,$this->trustConfigs));
    }

    /**
     * @return StatisticsApiClient
     */
    public function statisticsClient(){
        return (new StatisticsApiClient($this->guzzle,$this->trustConfigs));
    }

    /**
     * @return CredentialValidationClient
     */
    public function credentialValidationClient(){
        return (new CredentialValidationClient($this->guzzle,$this->trustConfigs));
    }

    /**
     * @param $user_id
     * @return string
     * @throws Exceptions\ConfigException
     * @throws Exceptions\RequiredParameterMissing
     */
    public function getEmbedSignature($user_id){
        return (new EmbedSignature($this->trustConfigs))->createSignature($user_id);
    }

    /**
     * @param $user_id
     * @return string
     * @throws Exceptions\ConfigException
     * @throws Exceptions\RequiredParameterMissing
     */
    public function getVerifyCredentailsSignature(){
        return (new ValidationSignature($this->trustConfigs))->createSignature();
    }

    /**
     * @param $receivedSignature
     * @param $content
     * @param $signatureSecret
     * @return bool
     * @throws Exceptions\RequiredParameterMissing
     */
    public static function verifyWebhookSignature($receivedSignature,$content,$signatureSecret){
        return (new WebhookSignature())->verifyWebhookSignature($receivedSignature,$content,$signatureSecret);
    }

    /**
     * @param $api_key
     * @param $base_url
     * @param null $embed_key
     * @return mixed|CredentialValidationClient
     * @throws Exceptions\ApiException
     * @throws Exceptions\ConfigException
     * @throws Exceptions\RequiredParameterMissing
     */

    public static function validateCredentials($api_key,$base_url,$api_secret = null,$embed_key = null){
        $currentObject = self::getInstance($api_key,$base_url,$api_secret,$embed_key);

        $validateCredentialsClient=$currentObject->credentialValidationClient();

        return $validateCredentialsClient->verifyCredentials([
            'embed_key'=>$embed_key,
            'signature'=>$currentObject->getVerifyCredentailsSignature(),
        ]);

    }

}
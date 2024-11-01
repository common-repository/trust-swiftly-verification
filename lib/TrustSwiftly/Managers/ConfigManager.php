<?php

namespace TrustSwiftly\Managers;


use TrustSwiftly\Exceptions\ConfigException;

class ConfigManager{

    public $baseUrl;

    public $apiKey;

    public $apiSecret = null;

    public $embedKey = null;


    /**
     * @param $configs
     * @throws ConfigException
     */
    public function __construct($configs)
    {
        if (!isset($configs['base_url'])) {
            throw new ConfigException('Base Url is Required',ConfigException::MISSING_REQUIRED_CONFIG);
        }else{
            $this->setBaseUrl($configs['base_url']);
        }

        if (!isset($configs['api_key'])) {
            throw new ConfigException('Base Url is Required',ConfigException::MISSING_REQUIRED_CONFIG);
        }else{
            $this->setApiKey($configs['api_key']);
        }

        if (isset($configs['api_secret'])) {
            $this->setApiSecret($configs['api_secret']);
        }
        if (isset($configs['embed_key'])) {
            $this->setEmbedKey($configs['embed_key']);
        }
    }

    /**
     * @param $baseUrl
     * @throws ConfigException
     */
    public function setBaseUrl($baseUrl){
        if (empty($baseUrl)){
            throw new ConfigException('Base url cannot be null or empty',ConfigException::NULL_OR_EMPTY_CONFIG);
        }
        $this->baseUrl=$baseUrl;
    }

    public function getBaseUrl(){
        return $this->baseUrl;
    }

    /**
     * @param $apiKey
     * @throws ConfigException
     */
    public function setApiKey($apiKey){
        if (empty($apiKey)){
            throw new ConfigException('Api key cannot be null or empty',ConfigException::NULL_OR_EMPTY_CONFIG);
        }
        $this->apiKey = $apiKey;
    }

    /**
     * @return mixed
     */
    public function getApiKey(){
        return $this->apiKey;
    }

    /**
     * @param $embedKey
     */
    public function setEmbedKey($embedKey){
        $this->embedKey=$embedKey;
    }

    /**
     * @return null
     */
    public function getEmbedKey(){
        return $this->embedKey;
    }

    /**
     * @param $apiSecret
     */
    public function setApiSecret($apiSecret){
        $this->apiSecret=$apiSecret;
    }

    /**
     * @return null
     */
    public function getApiSecret(){
        return $this->apiSecret;
    }

}
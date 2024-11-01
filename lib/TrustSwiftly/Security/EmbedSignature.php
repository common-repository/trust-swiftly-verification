<?php

namespace TrustSwiftly\Security;

use TrustSwiftly\Exceptions\ConfigException;
use TrustSwiftly\Exceptions\RequiredParameterMissing;
use TrustSwiftly\Managers\ConfigManager;

class EmbedSignature{

    protected $configs;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configs=$configManager;
    }

    /**
     * @param $user_id
     * @return string
     * @throws ConfigException
     * @throws RequiredParameterMissing
     */
    public function createSignature($user_id, $ignore_userId = false){
        if (!$ignore_userId && empty($user_id)){
            throw new RequiredParameterMissing("Required Parameter user_id Missing");
        }
        if (empty($this->configs->getApiSecret())){
            throw new ConfigException("Required Config api_secret Missing");
        }
        if (empty($this->configs->getEmbedKey())){
            throw new ConfigException("Required Config embed_key Missing");
        }
        $apisecret = $this->configs->getApiSecret();
        $embedKey = $this->configs->getEmbedKey();
        $timestamp = time();
        $payloadString = $embedKey . $user_id . $timestamp;
        $hash = hash_hmac('sha256', $payloadString, $apisecret);
        return 't=' . $timestamp . ',v2=' . $hash;
    }
}
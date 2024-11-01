<?php

namespace TrustSwiftly\Security;

use TrustSwiftly\Exceptions\ConfigException;
use TrustSwiftly\Exceptions\RequiredParameterMissing;
use TrustSwiftly\Managers\ConfigManager;

class ValidationSignature
{
    protected $configs;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configs=$configManager;
    }

    /**
     * @return string
     * @throws ConfigException
     * @throws RequiredParameterMissing
     */
    public function createSignature(){
        return (new EmbedSignature($this->configs))->createSignature(0,true);
    }
}
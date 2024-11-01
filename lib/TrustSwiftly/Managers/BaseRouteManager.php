<?php

namespace TrustSwiftly\Managers;

class BaseRouteManager{

    protected $base_url;

    public function __construct(ConfigManager $config)
    {
        $this->base_url=$this->getFinalBaseUrl($config->getBaseUrl());
    }

    /**
     * Final Base Url
     *
     * @param $base_url
     * @return string
     */
    public function getFinalBaseUrl($base_url){
        return $base_url;
    }


}
<?php

namespace TrustSwiftly\Api\Validation;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use TrustSwiftly\Api\Client;
use TrustSwiftly\Managers\ConfigManager;
use TrustSwiftly\Managers\Routes\ValidationApiRoutes;

class CredentialValidationClient extends Client
{
    /**
     * @var ValidationApiRoutes
     */
    protected $validation_api_routes;

    /**
     * @param GuzzleInterface $client
     * @param ConfigManager $config
     */
    public function __construct(GuzzleInterface $client, ConfigManager $config)
    {
        $this->validation_api_routes=new ValidationApiRoutes($config);
        parent::__construct($client, $config);
    }

    /**
     * @return $this|mixed
     * @throws \TrustSwiftly\Exceptions\ApiException
     */
    public function verifyCredentials($data){
        $response=$this->post($this->validation_api_routes->getVerifyCredentailsRoute(),$data);
        if ($this->checkResponse($response)){
            return json_decode($response->getResponseBody(),true);
        }
        return $this;
    }
}
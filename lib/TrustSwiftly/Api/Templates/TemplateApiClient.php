<?php
namespace TrustSwiftly\Api\Templates;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use TrustSwiftly\Api\Client;
use TrustSwiftly\Managers\ConfigManager;
use TrustSwiftly\Managers\Routes\TemplateApiRoutes;

/**
 * Template Api Client
 */
class TemplateApiClient extends Client{

    protected $template_api_routes;

    /**
     * @param GuzzleInterface $client
     * @param ConfigManager $config
     */
    public function __construct(GuzzleInterface $client, ConfigManager $config)
    {
        $this->template_api_routes=new TemplateApiRoutes($config);
        parent::__construct($client, $config);
    }

    /**
     * @return $this|mixed
     * @throws \TrustSwiftly\Exceptions\ApiException
     */
    public function getVerificationTemplates(){
        $response=$this->get($this->template_api_routes->getVerificationTemplates());
        if ($this->checkResponse($response)){
            return json_decode($response->getResponseBody(),true);
        }
        return $this;
    }
}
<?php
namespace TrustSwiftly\Api\Statistics;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use TrustSwiftly\Api\Client;
use TrustSwiftly\Managers\ConfigManager;
use TrustSwiftly\Managers\Routes\StatisticApiRoutes;

/**
 * Class for Statistics
 */
class StatisticsApiClient extends Client {
    protected $template_api_routes;

    /**
     * @param GuzzleInterface $client
     * @param ConfigManager $config
     */
    public function __construct(GuzzleInterface $client, ConfigManager $config)
    {
        $this->template_api_routes=new StatisticApiRoutes($config);
        parent::__construct($client, $config);
    }

    /**
     * @return $this|mixed
     * @throws \TrustSwiftly\Exceptions\ApiException
     */
    public function getVerificationStats(){
        $response=$this->get($this->template_api_routes->getVerificationStatistics());
        if ($this->checkResponse($response)){
            return json_decode($response->getResponseBody(),true);
        }
        return $this;
    }
}
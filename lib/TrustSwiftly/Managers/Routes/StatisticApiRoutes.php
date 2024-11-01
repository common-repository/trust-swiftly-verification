<?php

namespace TrustSwiftly\Managers\Routes;

use TrustSwiftly\Managers\BaseRouteManager;
use TrustSwiftly\Managers\Interfaces\StatisticsRoutes;

class StatisticApiRoutes extends BaseRouteManager implements StatisticsRoutes {

    /**
     * @return string
     */
    public function getVerificationStatistics(){
        return $this->base_url.self::GET_STATS;
    }
}
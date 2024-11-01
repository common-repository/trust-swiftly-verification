<?php

namespace TrustSwiftly\Managers\Routes;

use TrustSwiftly\Managers\BaseRouteManager;
use TrustSwiftly\Managers\Interfaces\ValidationRoutes;

class ValidationApiRoutes extends BaseRouteManager implements ValidationRoutes
{
    /**
     * Get verify credentials route
     * @return string
     */
    public function getVerifyCredentailsRoute(){
        return $this->base_url.self::VALIDATE_CREDENTIALS;
    }
}
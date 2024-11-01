<?php

namespace TrustSwiftly\Managers\Routes;

use TrustSwiftly\Managers\BaseRouteManager;
use TrustSwiftly\Managers\Interfaces\TemplateRoutes;

class TemplateApiRoutes extends BaseRouteManager implements TemplateRoutes{

    /**
     * @return string
     */
    public function getVerificationTemplates(){
        return $this->base_url.self::GET_TEMPLATES;
    }
}
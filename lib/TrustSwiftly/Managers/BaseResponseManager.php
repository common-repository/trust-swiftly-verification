<?php

namespace TrustSwiftly\Managers;


use TrustSwiftly\Enums\HttpStatusCodes;

class BaseResponseManager{

    /**
     * @var $statusCode
     */
    protected $statusCode;

    /**
     * @var $responseBody
     */
    protected $responseBody;

    /**
     * @var $responseHeaders
     */
    protected $responseHeaders;


    /**
     * @param $statusCode
     * @param $responseBody
     * @param $responseHeaders
     */
    public function __construct($statusCode,$responseBody,$responseHeaders)
    {
        $this->statusCode=$statusCode;
        $this->responseBody=$responseBody;
        $this->responseHeaders=$responseHeaders;
    }

    /**
     * @return bool
     */
    public function isSuccess(){
        return $this->statusCode===HttpStatusCodes::HTTP_STATUS_SUCCESS;
    }

    /**
     * @return mixed
     */
    public function getResponseBody(){
        return $this->responseBody;
    }

}
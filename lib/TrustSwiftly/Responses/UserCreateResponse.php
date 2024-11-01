<?php

namespace TrustSwiftly\Responses;


class UserCreateResponse {

    public $createArray;

    public $responseBody;

    /**
     * @param $responseBody
     */
    public function __construct($responseBody)
    {
        $this->responseBody=$responseBody;
        $this->createArray=json_decode($responseBody,true);
    }

    /**
     * @return mixed
     */
    public function getResponseArray(){
        return $this->createArray;
    }

    /**
     * @return mixed
     */
    public function getRawResponse(){
        return $this->responseBody;
    }
}
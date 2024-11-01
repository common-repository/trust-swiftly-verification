<?php

namespace TrustSwiftly\Responses;


class UserMagicLinkResponse {

    public $magicArray;

    public $responseBody;

    public $fullUrl;

    public $shortUrl;

    /**
     * @param $responseBody
     */
    public function __construct($responseBody)
    {
        $this->responseBody=$responseBody;
        $this->magicArray=json_decode($responseBody,true);
    }

    /**
     * @return mixed
     */
    public function getResponseArray(){
        return $this->magicArray;
    }

    /**
     * @return mixed
     */
    public function getRawResponse(){
        return $this->responseBody;
    }

    /**
     * @return mixed
     */
    public function getFullUrl(){
        return $this->magicArray['full_url'];
    }

    /**
     * @return mixed
     */
    public function getShortUrl(){
        return $this->magicArray['short_url'];
    }
}
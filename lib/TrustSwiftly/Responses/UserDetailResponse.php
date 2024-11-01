<?php

namespace TrustSwiftly\Responses;

use TrustSwiftly\Resources\User;

class UserDetailResponse {

    public $responseBody;

    public $user;

    public $userArray;

    /**
     * @param $responseBody
     */
    public function __construct($responseBody)
    {
        $this->responseBody=$responseBody;
        $this->userArray=json_decode($responseBody,true);
        $this->setUserObject($responseBody);
    }

    /**
     * @param $userDetails
     */
    public function setUserObject($userDetails){
        $this->user=new User($userDetails);
    }

    /**
     * @return mixed
     */
    public function user(){
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function userVerifications(){
        return $this->userArray['data']['verifications'];
    }

    /**
     * @return mixed
     */
    public function getRawResponse(){
        return $this->userArray;
    }
}
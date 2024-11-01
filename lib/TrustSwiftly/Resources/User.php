<?php

namespace TrustSwiftly\Resources;

class User{

    public $userArray;

    public $id;

    public $first_name;

    public $last_name;

    public $username;

    public $email;

    public $user_id;

    /**
     * @param $userDetails
     */
    public function __construct($userDetails)
    {
        $this->userArray=json_decode($userDetails,true);
        $this->mapUserDetailsToModel($this->userArray);
    }

    /**
     * @param $userDetails
     */
    public function mapUserDetailsToModel($userDetails){
        $this->id=$userDetails['data']['id'];
        $this->email=$userDetails['data']['email'];
        $this->first_name=$userDetails['data']['first_name'];
        $this->last_name=$userDetails['data']['last_name'];
        $this->email=$userDetails['data']['email'];
        $this->username=$userDetails['data']['username'];
        $this->user_id=$userDetails['user_id'];
    }
}
<?php

namespace TrustSwiftly\Api\Users;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use TrustSwiftly\Api\Client;
use TrustSwiftly\Exceptions\ApiException;
use TrustSwiftly\Exceptions\RequiredParameterMissing;
use TrustSwiftly\Managers\ConfigManager;
use TrustSwiftly\Managers\Routes\UserApiRoutes;
use TrustSwiftly\Responses\UserCreateResponse;
use TrustSwiftly\Responses\UserDetailResponse;
use TrustSwiftly\Responses\UserMagicLinkResponse;

/**
 * User Api Client
 */
class UserApiClient extends Client{

    /**
     * @var UserApiRoutes
     */
    protected $user_api_routes;

    /**
     * @param GuzzleInterface $client
     * @param ConfigManager $config
     */
    public function __construct(GuzzleInterface $client, ConfigManager $config)
    {
        $this->user_api_routes=new UserApiRoutes($config);
        parent::__construct($client, $config);
    }

    /**
     * @param null $filter_sort_params
     * @return $this|mixed
     * @throws ApiException
     */
    public function getAllUsers($filter_sort_params=null){
        $response=$this->get($this->user_api_routes->getAllUsersRoute(),null,$filter_sort_params);
        if ($this->checkResponse($response)){
            return json_decode($response->getResponseBody(),true);
        }
        return $this;
    }

    /**
     * @param null $user_id
     * @return $this|UserDetailResponse
     * @throws ApiException
     * @throws RequiredParameterMissing
     */
    public function getUserDetails($user_id = null){
        if (empty($user_id)){
            throw new RequiredParameterMissing("Required Parameter user_id Missing");
        }

        $response=$this->get($this->user_api_routes->getUserDetailRoute($user_id));
        if ($this->checkResponse($response)){
            $responseBody=$response->getResponseBody();
            return new UserDetailResponse($responseBody);
        }
        return $this;
    }

    /**
     * @param $data
     * @return $this|UserCreateResponse
     * @throws ApiException
     * @throws RequiredParameterMissing
     */
    public function createUser($data){
        if (empty($data['email'])){
            throw new RequiredParameterMissing("Required Parameter email Missing");
        }
        $response=$this->post($this->user_api_routes->getUserCreateRoute(),$data);
        if ($this->checkResponse($response)){
            $responseBody=$response->getResponseBody();
            return new UserCreateResponse($responseBody);
        }
        return $this;
    }

    /**
     * @param $user_id
     * @param $data
     * @return $this|UserDetailResponse
     * @throws ApiException
     * @throws RequiredParameterMissing
     */
    public function updateUser($user_id,$data){
        if (empty($user_id)){
            throw new RequiredParameterMissing("Required Parameter user_id Missing");
        }
        if (empty($data)){
            throw new RequiredParameterMissing("Please Provide Update Data");
        }
        $response=$this->patch($this->user_api_routes->getUserUpdateRoute($user_id),$data);
        if ($this->checkResponse($response)){
            $responseBody=$response->getResponseBody();
            return new UserDetailResponse($responseBody);
        }
        return $this;

    }

    /**
     * @param $user_id
     * @param $data
     * @return $this|bool
     * @throws ApiException
     * @throws RequiredParameterMissing
     */
    public function updateUserVerification($user_id,$data){
        if (empty($user_id)){
            throw new RequiredParameterMissing("Required Parameter user_id Missing");
        }
        if (empty($data)){
            throw new RequiredParameterMissing("Please Provide Update Data");
        }
        if (empty($data['verification_id'])){
            throw new RequiredParameterMissing("Required Parameter verification_id Missing");
        }
        if ( !isset($data['status']) || is_null($data['status'])){
            throw new RequiredParameterMissing("Required Parameter status Missing");
        }
        $response=$this->patch($this->user_api_routes->getUserVerifyUpdateRoute($user_id),$data);
        if ($response===200){
            return true;
        }elseif($this->checkResponse($response)){
            return true;
        }
        return $this;
    }

    /**
     * @param $user_id
     * @return bool
     * @throws ApiException
     * @throws RequiredParameterMissing
     */
    public function deleteUser($user_id){
        if (empty($user_id)){
            throw new RequiredParameterMissing("Required Parameter user_id Missing");
        }
        $response=$this->delete($this->user_api_routes->getUserDeleteRoute($user_id));
        $responseArray=json_decode($response->getResponseBody(),true);
        if (!empty($responseArray['success'])){
            return true;
        }elseif($this->checkResponse($response)){
            return true;
        }else{
            return false;
        }

    }
    /**
     * @param $user_id
     * @return bool
     */
    public function isUserExists($user_id){
        $response = $this->get($this->user_api_routes->getUserDetailRoute($user_id));
        $responseBody=$response->getResponseBody();
        if(!empty($responseBody)){
            $responseBody = json_decode($responseBody);
            if(isset($responseBody->error_message)&&isset($responseBody->error_type)&&$responseBody->error_type=='api_user_error'&&$responseBody->error_message=='User Not Found'){
                return false;
            }
        }
        return true;

    }
    /**
     * @param $user_id
     * @param null $expiration_hours
     * @return $this|UserMagicLinkResponse
     * @throws ApiException
     * @throws RequiredParameterMissing
     */
    public function getMagicLink($user_id,$expiration_hours=null){
        if (empty($user_id)){
            throw new RequiredParameterMissing("Required Parameter user_id Missing");
        }
        $response=$this->post($this->user_api_routes->getMagicLinkRoute($user_id));
        
        if ($this->checkResponse($response)){
            $responseBody=$response->getResponseBody();
            return new UserMagicLinkResponse($responseBody);
        }
        return $this;

    }
}
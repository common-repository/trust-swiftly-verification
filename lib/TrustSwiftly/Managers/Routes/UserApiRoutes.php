<?php

namespace TrustSwiftly\Managers\Routes;

use TrustSwiftly\Managers\BaseRouteManager;
use TrustSwiftly\Managers\Interfaces\UserRoutes;

class UserApiRoutes extends BaseRouteManager implements UserRoutes {

    /**
     * Get All Users
     * @return string
     */
    public function getAllUsersRoute(){
        return $this->base_url.self::GET_ALL_USERS;
    }

    /**
     * Get User Details By Id
     *
     * @param $user_id
     * @return string
     */
    public function getUserDetailRoute($user_id){
        return $this->base_url.self::GET_USER_WITH_ID.$user_id;
    }

    /**
     * Create User
     *
     * @return string
     */
    public function getUserCreateRoute(){
        return $this->base_url.self::CREATE_USER;
    }

    /**
     * Update User
     *
     * @param $user_id
     * @return string
     */
    public function getUserUpdateRoute($user_id){
        return $this->base_url.self::UPDATE_USER.$user_id;
    }

    /**
     * Update Verification
     *
     * @param $user_id
     * @return string
     */
    public function getUserVerifyUpdateRoute($user_id){
        return $this->base_url.self::UPDATE_VERIFICATION_PREFIX.$user_id.self::UPDATE_VERIFICATION_SUFFIX;
    }

    /**
     * Delete User
     *
     * @param $user_id
     * @return string
     */
    public function getUserDeleteRoute($user_id){
        return $this->base_url.self::DELETE_USER.$user_id;
    }

    /**
     * Magic Link
     *
     * @param $user_id
     * @return string
     */
    public function getMagicLinkRoute($user_id){
        return $this->base_url.self::GET_MAGIC_LINK_PREFIX.$user_id.self::GET_MAGIC_LINK_SUFFIX;
    }
}
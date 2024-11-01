<?php

namespace TrustSwiftly\Managers\Interfaces;

/**
 * User Api Routes
 */
interface UserRoutes{

    const GET_ALL_USERS='/api/users';
    const GET_USER_WITH_ID='/api/users/';
    const CREATE_USER='/api/users';
    const UPDATE_USER='/api/users/';
    const UPDATE_VERIFICATION_PREFIX='/api/users/';
    const UPDATE_VERIFICATION_SUFFIX='/verifications';
    const DELETE_USER='/api/users/';
    const GET_MAGIC_LINK_PREFIX='/api/users/';
    const GET_MAGIC_LINK_SUFFIX='/verify-url';
}
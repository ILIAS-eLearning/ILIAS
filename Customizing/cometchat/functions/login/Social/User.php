<?php
/**
 * Class Social_User is a user information holder
 */
class Social_User {

    public $network_name;

    public $timestamp;

    public $username;

    public $email;

    public $password;

    public $profile;

    function __construct() {
        $this->timestamp = time();
        $this->profile   = new Social_User_Profile();
    }
}
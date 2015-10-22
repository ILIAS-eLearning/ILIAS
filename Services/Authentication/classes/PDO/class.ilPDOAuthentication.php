<?php

require_once 'Services/Authentication/classes/PDO/interface.ilAuthInterface.php';

class ilPDOAuthentication implements ilAuthInterface {


    public function setIdle($time, $add = false)
    {
        // TODO: Implement setIdle() method.
    }

    /**
     * Set the maximum expire time
     * @param int $time Time in seconds
     * @param bool $add Add time to current expire time or not
     * @return void
     */
    public function setExpire($time, $add = false)
    {
        // TODO: Implement setExpire() method.
    }


    /**
     * Start new auth session
     * @return void
     */
    public function start()
    {
        // TODO: Implement start() method.
    }

    /**
     * Has the user been authenticated?
     *
     * Is there a valid login session. Previously this was different from
     * checkAuth() but now it is just an alias.
     *
     * @return bool  True if the user is logged in, otherwise false.
     */
    function getAuth()
    {
        // TODO
        return true;
    }

    /**
     * @return string
     */
    function getStatus()
    {
        // TODO: Implement getStatus() method.
        return '';
    }


    /**
     * @return string
     */
    function getUsername()
    {
        return 'root';
    }

    /**
     * Returns the time up to the session is valid
     *
     * @access public
     * @return integer
     */
    function sessionValidThru()
    {
        return time() + 1000000;
    }

    public function logout(){
        // TODO
    }
}
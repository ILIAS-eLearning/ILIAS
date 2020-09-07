<?php

interface ilAuthPDOInterface
{
    
    /**
     * Set maximum idle time.
     * @param int $time Time in seconds.
     * @param bool $add Add to current idle time?
     * @return void
     */
    public function setIdle($time, $add = false);

    /**
     * Set the maximum expire time
     * @param int $time Time in seconds
     * @param bool $add Add time to current expire time or not
     * @return void
     */
    public function setExpire($time, $add = false);

    /**
     * Start new auth session
     * @return void
     */
    public function start();

    /**
     * Has the user been authenticated?
     *
     * Is there a valid login session. Previously this was different from
     * checkAuth() but now it is just an alias.
     *
     * @return bool  True if the user is logged in, otherwise false.
     */
    public function getAuth();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getUsername();

    /**
     * Returns the time up to the session is valid
     *
     * @access public
     * @return integer
     */
    public function sessionValidThru();

    /**
     * @return void
     */
    public function logout();
}

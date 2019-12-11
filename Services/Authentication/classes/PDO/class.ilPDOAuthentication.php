<?php

require_once 'Services/Authentication/classes/PDO/interface.ilAuthPDOInterface.php';

/**
 * @property  _postPassword
 */
class ilPDOAuthentication implements ilAuthPDOInterface
{
    protected $_sessionName = '_authsession';
    protected $allowLogin = true;
    protected $_postUsername = 'username';
    protected $_postPassword = 'password';
    protected $advancedsecurity;
    protected $enableLogging;
    protected $regenerateSessionId;
    protected $status = '';
    protected $username = null;
    protected $password;
    protected $session;
    protected $server;
    protected $post;
    protected $cookie;


    public function __construct()
    {
        //        $started = session_start();
        //        $sess = session_id();
        //        $db_session_handler = new ilSessionDBHandler();
        //        if (!$db_session_handler->setSaveHandler())
        //        {
        //            throw new Exception("Disable save mode or set session_hanlder to \"user\"");
        //        }
        @session_start(); // Due to UnitTests we have to silence this...

        $this->session = $_SESSION[$this->_sessionName];
        $this->server = $_SERVER;
        $this->post = $_POST;
        $this->cookie = $_COOKIE;
    }


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
        // TODO SAME AS old AUTH
        $this->assignData();
        if (!$this->checkAuth() && $this->allowLogin) {
            $this->login();
        }
    }


    /**
     * @return bool
     */
    protected function checkAuth()
    {
        return isset($_SESSION['_authsession']['username']);
    }


    protected function login()
    {
        if (!empty($this->username) && $this->verifyPassword($this->username, $this->password)) {
            $this->setAuth($this->username);
        } else {
            $this->status = AUTH_WRONG_LOGIN;
        }
    }


    /**
     * Has the user been authenticated?
     *
     * Is there a valid login session. Previously this was different from
     * checkAuth() but now it is just an alias.
     *
     * @return bool  True if the user is logged in, otherwise false.
     */
    public function getAuth()
    {
        return $this->checkAuth();
    }


    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @return string
     */
    public function getUsername()
    {
        return $_SESSION['_authsession']['username'];
    }


    /**
     * Returns the time up to the session is valid
     *
     * @access public
     * @return integer
     */
    public function sessionValidThru()
    {
        return time() + 1000000;
    }


    public function logout()
    {
        $_SESSION['_authsession'] = null;
    }

    protected function assignData()
    {
        if (isset($this->post[$this->_postUsername])
            && $this->post[$this->_postUsername] != ''
        ) {
            $this->username = (get_magic_quotes_gpc() == 1 ? stripslashes($this->post[$this->_postUsername]) : $this->post[$this->_postUsername]);
        }
        if (isset($this->post[$this->_postPassword])
            && $this->post[$this->_postPassword] != ''
        ) {
            $this->password = (get_magic_quotes_gpc() == 1 ? stripslashes($this->post[$this->_postPassword]) : $this->post[$this->_postPassword]);
        }
    }


    /**
     * @param $username
     */
    private function setAuth($username)
    {
        //        session_regenerate_id(true); doesn't seem to work on PHP7

        if (!isset($_SESSION['_authsession'])) {
            $_SESSION['_authsession'] = array();
        }

        $_SESSION['_authsession']['username'] = $username;
    }


    /**
     * @param $username
     * @param $password
     * @return bool
     */
    private function verifyPassword($username, $password)
    {
        require_once 'Services/User/classes/class.ilUserPasswordManager.php';

        /**
         * @var $user ilObjUser
         */
        $user = ilObjectFactory::getInstanceByObjId(ilObjUser::_loginExists($username));
        return ilUserPasswordManager::getInstance()->verifyPassword($user, $password);
    }
}

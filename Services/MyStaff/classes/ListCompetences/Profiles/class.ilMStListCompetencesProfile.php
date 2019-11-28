<?php

/**
 * Class ilMStListCompetencesProfile
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMStListCompetencesProfile
{

    /**
     * @var string
     */
    protected $profile_title;
    /**
     * @var boolean
     */
    protected $fulfilled;
    /**
     * @var string
     */
    protected $login;
    /**
     * @var string
     */
    protected $last_name;
    /**
     * @var string
     */
    protected $first_name;
    /**
     * @var integer
     */
    protected $user_id;


    /**
     * ilMStListCompetencesProfile constructor.
     *
     * @param string $profile_title
     * @param bool   $fulfilled
     * @param string $login
     * @param string $last_name
     * @param string $first_name
     * @param int    $user_id
     */
    public function __construct(string $profile_title, bool $fulfilled, string $login, string $last_name, string $first_name, int $user_id)
    {
        $this->profile_title = $profile_title;
        $this->fulfilled = $fulfilled;
        $this->login = $login;
        $this->last_name = $last_name;
        $this->first_name = $first_name;
        $this->user_id = $user_id;
    }


    /**
     * @return string
     */
    public function getProfileTitle() : string
    {
        return $this->profile_title;
    }


    /**
     * @param string $profile_title
     */
    public function setProfileTitle(string $profile_title) : void
    {
        $this->profile_title = $profile_title;
    }


    /**
     * @return bool
     */
    public function isFulfilled() : bool
    {
        return $this->fulfilled;
    }


    /**
     * @param bool $fulfilled
     */
    public function setFulfilled(bool $fulfilled) : void
    {
        $this->fulfilled = $fulfilled;
    }


    /**
     * @return string
     */
    public function getLogin() : string
    {
        return $this->login;
    }


    /**
     * @param string $login
     */
    public function setLogin(string $login) : void
    {
        $this->login = $login;
    }


    /**
     * @return string
     */
    public function getLastName() : string
    {
        return $this->last_name;
    }


    /**
     * @param string $last_name
     */
    public function setLastName(string $last_name) : void
    {
        $this->last_name = $last_name;
    }


    /**
     * @return string
     */
    public function getFirstName() : string
    {
        return $this->first_name;
    }


    /**
     * @param string $first_name
     */
    public function setFirstName(string $first_name) : void
    {
        $this->first_name = $first_name;
    }


    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->user_id;
    }


    /**
     * @param int $user_id
     */
    public function setUserId(int $user_id) : void
    {
        $this->user_id = $user_id;
    }


}
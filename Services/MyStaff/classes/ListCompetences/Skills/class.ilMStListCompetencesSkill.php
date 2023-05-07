<?php

/**
 * Class ilMStListCompetencesSkill
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMStListCompetencesSkill
{

    /**
     * @var integer
     */
    protected $skill_node_id;
    /**
     * @var string
     */
    protected $skill_title;
    /**
     * @var string
     */
    protected $skill_level;
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
     * @var string
     */
    protected $email;
    /**
     * @var integer
     */
    protected $user_id;


    /**
     * ilMStListCompetencesSkill constructor.
     *
     * @param string $skill_title
     * @param string $skill_level
     * @param string $login
     * @param string $last_name
     * @param string $first_name
     * @param int    $user_id
     */
    public function __construct(int $skill_node_id, string $skill_title, string $skill_level, string $login, string $last_name, string $first_name, string $email, int $user_id)
    {
        $this->skill_node_id = $skill_node_id;
        $this->skill_title = $skill_title;
        $this->skill_level = $skill_level;
        $this->login = $login;
        $this->last_name = $last_name;
        $this->first_name = $first_name;
        $this->email = $email;
        $this->user_id = $user_id;
    }


    /**
     * @return int
     */
    public function getSkillNodeId() : int
    {
        return $this->skill_node_id;
    }


    /**
     * @param int $skill_node_id
     */
    public function setSkillNodeId(int $skill_node_id) : void
    {
        $this->skill_node_id = $skill_node_id;
    }


    /**
     * @return string
     */
    public function getSkillTitle() : string
    {
        return $this->skill_title;
    }


    /**
     * @param string $skill_title
     */
    public function setSkillTitle(string $skill_title) : void
    {
        $this->skill_title = $skill_title;
    }


    /**
     * @return string
     */
    public function getSkillLevel() : string
    {
        return $this->skill_level;
    }


    /**
     * @param string $skill_level
     */
    public function setSkillLevel(string $skill_level) : void
    {
        $this->skill_level = $skill_level;
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
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }


    /**
     * @param string $email
     */
    public function setEmail(string $email) : void
    {
        $this->email = $email;
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

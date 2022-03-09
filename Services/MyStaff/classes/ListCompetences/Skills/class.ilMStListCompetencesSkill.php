<?php

/**
 * Class ilMStListCompetencesSkill
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilMStListCompetencesSkill
{
    protected string $skill_title;
    protected string $skill_level;
    protected string $login;
    protected string $last_name;
    protected string $first_name;
    protected integer $user_id;

    public function __construct(
        string $skill_title,
        string $skill_level,
        string $login,
        string $last_name,
        string $first_name,
        int $user_id
    ) {
        $this->skill_title = $skill_title;
        $this->skill_level = $skill_level;
        $this->login = $login;
        $this->last_name = $last_name;
        $this->first_name = $first_name;
        $this->user_id = $user_id;
    }

    final public function getSkillTitle() : string
    {
        return $this->skill_title;
    }

    final public function setSkillTitle(string $skill_title) : void
    {
        $this->skill_title = $skill_title;
    }

    final public function getSkillLevel() : string
    {
        return $this->skill_level;
    }

    final public function setSkillLevel(string $skill_level) : void
    {
        $this->skill_level = $skill_level;
    }

    final public function getLogin() : string
    {
        return $this->login;
    }

    final public function setLogin(string $login) : void
    {
        $this->login = $login;
    }

    final public function getLastName() : string
    {
        return $this->last_name;
    }

    final public function setLastName(string $last_name) : void
    {
        $this->last_name = $last_name;
    }

    final public function getFirstName() : string
    {
        return $this->first_name;
    }

    final public function setFirstName(string $first_name) : void
    {
        $this->first_name = $first_name;
    }

    final public function getUserId() : int
    {
        return $this->user_id;
    }

    final public function setUserId(int $user_id) : void
    {
        $this->user_id = $user_id;
    }
}

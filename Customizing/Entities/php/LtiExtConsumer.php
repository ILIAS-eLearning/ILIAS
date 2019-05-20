<?php



/**
 * LtiExtConsumer
 */
class LtiExtConsumer
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $description = '';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string
     */
    private $userLanguage = '';

    /**
     * @var int
     */
    private $role = '0';

    /**
     * @var bool
     */
    private $localRoleAlwaysMember = '0';

    /**
     * @var string|null
     */
    private $defaultSkin;

    /**
     * @var bool
     */
    private $active = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return LtiExtConsumer
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return LtiExtConsumer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set prefix.
     *
     * @param string $prefix
     *
     * @return LtiExtConsumer
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set userLanguage.
     *
     * @param string $userLanguage
     *
     * @return LtiExtConsumer
     */
    public function setUserLanguage($userLanguage)
    {
        $this->userLanguage = $userLanguage;

        return $this;
    }

    /**
     * Get userLanguage.
     *
     * @return string
     */
    public function getUserLanguage()
    {
        return $this->userLanguage;
    }

    /**
     * Set role.
     *
     * @param int $role
     *
     * @return LtiExtConsumer
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set localRoleAlwaysMember.
     *
     * @param bool $localRoleAlwaysMember
     *
     * @return LtiExtConsumer
     */
    public function setLocalRoleAlwaysMember($localRoleAlwaysMember)
    {
        $this->localRoleAlwaysMember = $localRoleAlwaysMember;

        return $this;
    }

    /**
     * Get localRoleAlwaysMember.
     *
     * @return bool
     */
    public function getLocalRoleAlwaysMember()
    {
        return $this->localRoleAlwaysMember;
    }

    /**
     * Set defaultSkin.
     *
     * @param string|null $defaultSkin
     *
     * @return LtiExtConsumer
     */
    public function setDefaultSkin($defaultSkin = null)
    {
        $this->defaultSkin = $defaultSkin;

        return $this;
    }

    /**
     * Get defaultSkin.
     *
     * @return string|null
     */
    public function getDefaultSkin()
    {
        return $this->defaultSkin;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return LtiExtConsumer
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }
}

<?php



/**
 * RegErAssignments
 */
class RegErAssignments
{
    /**
     * @var int
     */
    private $assignmentId = '0';

    /**
     * @var string|null
     */
    private $domain;

    /**
     * @var int
     */
    private $role = '0';


    /**
     * Get assignmentId.
     *
     * @return int
     */
    public function getAssignmentId()
    {
        return $this->assignmentId;
    }

    /**
     * Set domain.
     *
     * @param string|null $domain
     *
     * @return RegErAssignments
     */
    public function setDomain($domain = null)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set role.
     *
     * @param int $role
     *
     * @return RegErAssignments
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
}

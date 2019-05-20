<?php



/**
 * RegRegistrationCodes
 */
class RegRegistrationCodes
{
    /**
     * @var int
     */
    private $codeId = '0';

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var int|null
     */
    private $role = '0';

    /**
     * @var int|null
     */
    private $generatedOn = '0';

    /**
     * @var int
     */
    private $used = '0';

    /**
     * @var string|null
     */
    private $roleLocal;

    /**
     * @var string|null
     */
    private $alimit;

    /**
     * @var string|null
     */
    private $alimitdt;

    /**
     * @var bool
     */
    private $regEnabled = '1';

    /**
     * @var bool
     */
    private $extEnabled = '0';


    /**
     * Get codeId.
     *
     * @return int
     */
    public function getCodeId()
    {
        return $this->codeId;
    }

    /**
     * Set code.
     *
     * @param string|null $code
     *
     * @return RegRegistrationCodes
     */
    public function setCode($code = null)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set role.
     *
     * @param int|null $role
     *
     * @return RegRegistrationCodes
     */
    public function setRole($role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return int|null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set generatedOn.
     *
     * @param int|null $generatedOn
     *
     * @return RegRegistrationCodes
     */
    public function setGeneratedOn($generatedOn = null)
    {
        $this->generatedOn = $generatedOn;

        return $this;
    }

    /**
     * Get generatedOn.
     *
     * @return int|null
     */
    public function getGeneratedOn()
    {
        return $this->generatedOn;
    }

    /**
     * Set used.
     *
     * @param int $used
     *
     * @return RegRegistrationCodes
     */
    public function setUsed($used)
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get used.
     *
     * @return int
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set roleLocal.
     *
     * @param string|null $roleLocal
     *
     * @return RegRegistrationCodes
     */
    public function setRoleLocal($roleLocal = null)
    {
        $this->roleLocal = $roleLocal;

        return $this;
    }

    /**
     * Get roleLocal.
     *
     * @return string|null
     */
    public function getRoleLocal()
    {
        return $this->roleLocal;
    }

    /**
     * Set alimit.
     *
     * @param string|null $alimit
     *
     * @return RegRegistrationCodes
     */
    public function setAlimit($alimit = null)
    {
        $this->alimit = $alimit;

        return $this;
    }

    /**
     * Get alimit.
     *
     * @return string|null
     */
    public function getAlimit()
    {
        return $this->alimit;
    }

    /**
     * Set alimitdt.
     *
     * @param string|null $alimitdt
     *
     * @return RegRegistrationCodes
     */
    public function setAlimitdt($alimitdt = null)
    {
        $this->alimitdt = $alimitdt;

        return $this;
    }

    /**
     * Get alimitdt.
     *
     * @return string|null
     */
    public function getAlimitdt()
    {
        return $this->alimitdt;
    }

    /**
     * Set regEnabled.
     *
     * @param bool $regEnabled
     *
     * @return RegRegistrationCodes
     */
    public function setRegEnabled($regEnabled)
    {
        $this->regEnabled = $regEnabled;

        return $this;
    }

    /**
     * Get regEnabled.
     *
     * @return bool
     */
    public function getRegEnabled()
    {
        return $this->regEnabled;
    }

    /**
     * Set extEnabled.
     *
     * @param bool $extEnabled
     *
     * @return RegRegistrationCodes
     */
    public function setExtEnabled($extEnabled)
    {
        $this->extEnabled = $extEnabled;

        return $this;
    }

    /**
     * Get extEnabled.
     *
     * @return bool
     */
    public function getExtEnabled()
    {
        return $this->extEnabled;
    }
}

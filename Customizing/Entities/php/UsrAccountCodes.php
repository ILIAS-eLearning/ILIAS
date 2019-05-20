<?php



/**
 * UsrAccountCodes
 */
class UsrAccountCodes
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
     * @var string|null
     */
    private $validUntil;

    /**
     * @var int|null
     */
    private $generated = '0';

    /**
     * @var int
     */
    private $used = '0';


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
     * @return UsrAccountCodes
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
     * Set validUntil.
     *
     * @param string|null $validUntil
     *
     * @return UsrAccountCodes
     */
    public function setValidUntil($validUntil = null)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * Get validUntil.
     *
     * @return string|null
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set generated.
     *
     * @param int|null $generated
     *
     * @return UsrAccountCodes
     */
    public function setGenerated($generated = null)
    {
        $this->generated = $generated;

        return $this;
    }

    /**
     * Get generated.
     *
     * @return int|null
     */
    public function getGenerated()
    {
        return $this->generated;
    }

    /**
     * Set used.
     *
     * @param int $used
     *
     * @return UsrAccountCodes
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
}

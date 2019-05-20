<?php



/**
 * AuthExtAttrMapping
 */
class AuthExtAttrMapping
{
    /**
     * @var int
     */
    private $authSrcId = '0';

    /**
     * @var string
     */
    private $attribute = '';

    /**
     * @var string
     */
    private $authMode = '';

    /**
     * @var string|null
     */
    private $extAttribute;

    /**
     * @var bool
     */
    private $updateAutomatically = '0';


    /**
     * Set authSrcId.
     *
     * @param int $authSrcId
     *
     * @return AuthExtAttrMapping
     */
    public function setAuthSrcId($authSrcId)
    {
        $this->authSrcId = $authSrcId;

        return $this;
    }

    /**
     * Get authSrcId.
     *
     * @return int
     */
    public function getAuthSrcId()
    {
        return $this->authSrcId;
    }

    /**
     * Set attribute.
     *
     * @param string $attribute
     *
     * @return AuthExtAttrMapping
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute.
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set authMode.
     *
     * @param string $authMode
     *
     * @return AuthExtAttrMapping
     */
    public function setAuthMode($authMode)
    {
        $this->authMode = $authMode;

        return $this;
    }

    /**
     * Get authMode.
     *
     * @return string
     */
    public function getAuthMode()
    {
        return $this->authMode;
    }

    /**
     * Set extAttribute.
     *
     * @param string|null $extAttribute
     *
     * @return AuthExtAttrMapping
     */
    public function setExtAttribute($extAttribute = null)
    {
        $this->extAttribute = $extAttribute;

        return $this;
    }

    /**
     * Get extAttribute.
     *
     * @return string|null
     */
    public function getExtAttribute()
    {
        return $this->extAttribute;
    }

    /**
     * Set updateAutomatically.
     *
     * @param bool $updateAutomatically
     *
     * @return AuthExtAttrMapping
     */
    public function setUpdateAutomatically($updateAutomatically)
    {
        $this->updateAutomatically = $updateAutomatically;

        return $this;
    }

    /**
     * Get updateAutomatically.
     *
     * @return bool
     */
    public function getUpdateAutomatically()
    {
        return $this->updateAutomatically;
    }
}

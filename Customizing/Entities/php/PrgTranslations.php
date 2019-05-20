<?php



/**
 * PrgTranslations
 */
class PrgTranslations
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $prgTypeId;

    /**
     * @var string|null
     */
    private $lang;

    /**
     * @var string|null
     */
    private $member;

    /**
     * @var string|null
     */
    private $value;


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
     * Set prgTypeId.
     *
     * @param int|null $prgTypeId
     *
     * @return PrgTranslations
     */
    public function setPrgTypeId($prgTypeId = null)
    {
        $this->prgTypeId = $prgTypeId;

        return $this;
    }

    /**
     * Get prgTypeId.
     *
     * @return int|null
     */
    public function getPrgTypeId()
    {
        return $this->prgTypeId;
    }

    /**
     * Set lang.
     *
     * @param string|null $lang
     *
     * @return PrgTranslations
     */
    public function setLang($lang = null)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string|null
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set member.
     *
     * @param string|null $member
     *
     * @return PrgTranslations
     */
    public function setMember($member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member.
     *
     * @return string|null
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return PrgTranslations
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}

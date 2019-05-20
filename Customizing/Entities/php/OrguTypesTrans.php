<?php



/**
 * OrguTypesTrans
 */
class OrguTypesTrans
{
    /**
     * @var int
     */
    private $orguTypeId = '0';

    /**
     * @var string
     */
    private $lang = '';

    /**
     * @var string
     */
    private $member = '';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set orguTypeId.
     *
     * @param int $orguTypeId
     *
     * @return OrguTypesTrans
     */
    public function setOrguTypeId($orguTypeId)
    {
        $this->orguTypeId = $orguTypeId;

        return $this;
    }

    /**
     * Get orguTypeId.
     *
     * @return int
     */
    public function getOrguTypeId()
    {
        return $this->orguTypeId;
    }

    /**
     * Set lang.
     *
     * @param string $lang
     *
     * @return OrguTypesTrans
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set member.
     *
     * @param string $member
     *
     * @return OrguTypesTrans
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member.
     *
     * @return string
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
     * @return OrguTypesTrans
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

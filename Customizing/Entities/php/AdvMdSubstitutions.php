<?php



/**
 * AdvMdSubstitutions
 */
class AdvMdSubstitutions
{
    /**
     * @var string
     */
    private $objType = ' ';

    /**
     * @var string|null
     */
    private $substitution;

    /**
     * @var bool
     */
    private $hideDescription = '0';

    /**
     * @var bool
     */
    private $hideFieldNames = '0';


    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set substitution.
     *
     * @param string|null $substitution
     *
     * @return AdvMdSubstitutions
     */
    public function setSubstitution($substitution = null)
    {
        $this->substitution = $substitution;

        return $this;
    }

    /**
     * Get substitution.
     *
     * @return string|null
     */
    public function getSubstitution()
    {
        return $this->substitution;
    }

    /**
     * Set hideDescription.
     *
     * @param bool $hideDescription
     *
     * @return AdvMdSubstitutions
     */
    public function setHideDescription($hideDescription)
    {
        $this->hideDescription = $hideDescription;

        return $this;
    }

    /**
     * Get hideDescription.
     *
     * @return bool
     */
    public function getHideDescription()
    {
        return $this->hideDescription;
    }

    /**
     * Set hideFieldNames.
     *
     * @param bool $hideFieldNames
     *
     * @return AdvMdSubstitutions
     */
    public function setHideFieldNames($hideFieldNames)
    {
        $this->hideFieldNames = $hideFieldNames;

        return $this;
    }

    /**
     * Get hideFieldNames.
     *
     * @return bool
     */
    public function getHideFieldNames()
    {
        return $this->hideFieldNames;
    }
}

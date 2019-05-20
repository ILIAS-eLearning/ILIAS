<?php



/**
 * SvyPhraseCat
 */
class SvyPhraseCat
{
    /**
     * @var int
     */
    private $phraseCategoryId = '0';

    /**
     * @var int
     */
    private $phraseFi = '0';

    /**
     * @var int
     */
    private $categoryFi = '0';

    /**
     * @var int
     */
    private $sequence = '0';

    /**
     * @var int
     */
    private $other = '0';

    /**
     * @var int|null
     */
    private $scale;


    /**
     * Get phraseCategoryId.
     *
     * @return int
     */
    public function getPhraseCategoryId()
    {
        return $this->phraseCategoryId;
    }

    /**
     * Set phraseFi.
     *
     * @param int $phraseFi
     *
     * @return SvyPhraseCat
     */
    public function setPhraseFi($phraseFi)
    {
        $this->phraseFi = $phraseFi;

        return $this;
    }

    /**
     * Get phraseFi.
     *
     * @return int
     */
    public function getPhraseFi()
    {
        return $this->phraseFi;
    }

    /**
     * Set categoryFi.
     *
     * @param int $categoryFi
     *
     * @return SvyPhraseCat
     */
    public function setCategoryFi($categoryFi)
    {
        $this->categoryFi = $categoryFi;

        return $this;
    }

    /**
     * Get categoryFi.
     *
     * @return int
     */
    public function getCategoryFi()
    {
        return $this->categoryFi;
    }

    /**
     * Set sequence.
     *
     * @param int $sequence
     *
     * @return SvyPhraseCat
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence.
     *
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set other.
     *
     * @param int $other
     *
     * @return SvyPhraseCat
     */
    public function setOther($other)
    {
        $this->other = $other;

        return $this;
    }

    /**
     * Get other.
     *
     * @return int
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * Set scale.
     *
     * @param int|null $scale
     *
     * @return SvyPhraseCat
     */
    public function setScale($scale = null)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * Get scale.
     *
     * @return int|null
     */
    public function getScale()
    {
        return $this->scale;
    }
}

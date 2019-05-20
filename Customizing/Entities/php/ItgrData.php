<?php



/**
 * ItgrData
 */
class ItgrData
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool
     */
    private $hideTitle = '0';

    /**
     * @var bool|null
     */
    private $behaviour = '0';


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
     * Set hideTitle.
     *
     * @param bool $hideTitle
     *
     * @return ItgrData
     */
    public function setHideTitle($hideTitle)
    {
        $this->hideTitle = $hideTitle;

        return $this;
    }

    /**
     * Get hideTitle.
     *
     * @return bool
     */
    public function getHideTitle()
    {
        return $this->hideTitle;
    }

    /**
     * Set behaviour.
     *
     * @param bool|null $behaviour
     *
     * @return ItgrData
     */
    public function setBehaviour($behaviour = null)
    {
        $this->behaviour = $behaviour;

        return $this;
    }

    /**
     * Get behaviour.
     *
     * @return bool|null
     */
    public function getBehaviour()
    {
        return $this->behaviour;
    }
}

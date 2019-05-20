<?php



/**
 * SahsSc13Sco
 */
class SahsSc13Sco
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool
     */
    private $hideObjPage = '0';


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
     * Set hideObjPage.
     *
     * @param bool $hideObjPage
     *
     * @return SahsSc13Sco
     */
    public function setHideObjPage($hideObjPage)
    {
        $this->hideObjPage = $hideObjPage;

        return $this;
    }

    /**
     * Get hideObjPage.
     *
     * @return bool
     */
    public function getHideObjPage()
    {
        return $this->hideObjPage;
    }
}

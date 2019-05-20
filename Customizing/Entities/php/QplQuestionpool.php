<?php



/**
 * QplQuestionpool
 */
class QplQuestionpool
{
    /**
     * @var int
     */
    private $idQuestionpool = '0';

    /**
     * @var int
     */
    private $objFi = '0';

    /**
     * @var string|null
     */
    private $isonline = '0';

    /**
     * @var int
     */
    private $questioncount = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var bool
     */
    private $showTaxonomies = '0';

    /**
     * @var int|null
     */
    private $navTaxonomy;

    /**
     * @var bool|null
     */
    private $skillService;


    /**
     * Get idQuestionpool.
     *
     * @return int
     */
    public function getIdQuestionpool()
    {
        return $this->idQuestionpool;
    }

    /**
     * Set objFi.
     *
     * @param int $objFi
     *
     * @return QplQuestionpool
     */
    public function setObjFi($objFi)
    {
        $this->objFi = $objFi;

        return $this;
    }

    /**
     * Get objFi.
     *
     * @return int
     */
    public function getObjFi()
    {
        return $this->objFi;
    }

    /**
     * Set isonline.
     *
     * @param string|null $isonline
     *
     * @return QplQuestionpool
     */
    public function setIsonline($isonline = null)
    {
        $this->isonline = $isonline;

        return $this;
    }

    /**
     * Get isonline.
     *
     * @return string|null
     */
    public function getIsonline()
    {
        return $this->isonline;
    }

    /**
     * Set questioncount.
     *
     * @param int $questioncount
     *
     * @return QplQuestionpool
     */
    public function setQuestioncount($questioncount)
    {
        $this->questioncount = $questioncount;

        return $this;
    }

    /**
     * Get questioncount.
     *
     * @return int
     */
    public function getQuestioncount()
    {
        return $this->questioncount;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return QplQuestionpool
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set showTaxonomies.
     *
     * @param bool $showTaxonomies
     *
     * @return QplQuestionpool
     */
    public function setShowTaxonomies($showTaxonomies)
    {
        $this->showTaxonomies = $showTaxonomies;

        return $this;
    }

    /**
     * Get showTaxonomies.
     *
     * @return bool
     */
    public function getShowTaxonomies()
    {
        return $this->showTaxonomies;
    }

    /**
     * Set navTaxonomy.
     *
     * @param int|null $navTaxonomy
     *
     * @return QplQuestionpool
     */
    public function setNavTaxonomy($navTaxonomy = null)
    {
        $this->navTaxonomy = $navTaxonomy;

        return $this;
    }

    /**
     * Get navTaxonomy.
     *
     * @return int|null
     */
    public function getNavTaxonomy()
    {
        return $this->navTaxonomy;
    }

    /**
     * Set skillService.
     *
     * @param bool|null $skillService
     *
     * @return QplQuestionpool
     */
    public function setSkillService($skillService = null)
    {
        $this->skillService = $skillService;

        return $this;
    }

    /**
     * Get skillService.
     *
     * @return bool|null
     */
    public function getSkillService()
    {
        return $this->skillService;
    }
}

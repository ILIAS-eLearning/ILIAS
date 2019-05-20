<?php



/**
 * PageLayout
 */
class PageLayout
{
    /**
     * @var int
     */
    private $layoutId = '0';

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var bool|null
     */
    private $active = '0';

    /**
     * @var int|null
     */
    private $styleId = '0';

    /**
     * @var bool|null
     */
    private $specialPage = '0';

    /**
     * @var bool|null
     */
    private $modScorm = '1';

    /**
     * @var bool|null
     */
    private $modPortfolio;


    /**
     * Get layoutId.
     *
     * @return int
     */
    public function getLayoutId()
    {
        return $this->layoutId;
    }

    /**
     * Set content.
     *
     * @param string|null $content
     *
     * @return PageLayout
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return PageLayout
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return PageLayout
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set active.
     *
     * @param bool|null $active
     *
     * @return PageLayout
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set styleId.
     *
     * @param int|null $styleId
     *
     * @return PageLayout
     */
    public function setStyleId($styleId = null)
    {
        $this->styleId = $styleId;

        return $this;
    }

    /**
     * Get styleId.
     *
     * @return int|null
     */
    public function getStyleId()
    {
        return $this->styleId;
    }

    /**
     * Set specialPage.
     *
     * @param bool|null $specialPage
     *
     * @return PageLayout
     */
    public function setSpecialPage($specialPage = null)
    {
        $this->specialPage = $specialPage;

        return $this;
    }

    /**
     * Get specialPage.
     *
     * @return bool|null
     */
    public function getSpecialPage()
    {
        return $this->specialPage;
    }

    /**
     * Set modScorm.
     *
     * @param bool|null $modScorm
     *
     * @return PageLayout
     */
    public function setModScorm($modScorm = null)
    {
        $this->modScorm = $modScorm;

        return $this;
    }

    /**
     * Get modScorm.
     *
     * @return bool|null
     */
    public function getModScorm()
    {
        return $this->modScorm;
    }

    /**
     * Set modPortfolio.
     *
     * @param bool|null $modPortfolio
     *
     * @return PageLayout
     */
    public function setModPortfolio($modPortfolio = null)
    {
        $this->modPortfolio = $modPortfolio;

        return $this;
    }

    /**
     * Get modPortfolio.
     *
     * @return bool|null
     */
    public function getModPortfolio()
    {
        return $this->modPortfolio;
    }
}

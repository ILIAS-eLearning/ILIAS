<?php



/**
 * Glossary
 */
class Glossary
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $isOnline = 'n';

    /**
     * @var string|null
     */
    private $virtual = 'none';

    /**
     * @var string|null
     */
    private $publicHtmlFile;

    /**
     * @var string|null
     */
    private $publicXmlFile;

    /**
     * @var string|null
     */
    private $gloMenuActive = 'y';

    /**
     * @var string|null
     */
    private $downloadsActive = 'n';

    /**
     * @var string
     */
    private $presMode = 'table';

    /**
     * @var int
     */
    private $snippetLength = '200';

    /**
     * @var bool
     */
    private $showTax = '0';


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
     * Set isOnline.
     *
     * @param string|null $isOnline
     *
     * @return Glossary
     */
    public function setIsOnline($isOnline = null)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return string|null
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set virtual.
     *
     * @param string|null $virtual
     *
     * @return Glossary
     */
    public function setVirtual($virtual = null)
    {
        $this->virtual = $virtual;

        return $this;
    }

    /**
     * Get virtual.
     *
     * @return string|null
     */
    public function getVirtual()
    {
        return $this->virtual;
    }

    /**
     * Set publicHtmlFile.
     *
     * @param string|null $publicHtmlFile
     *
     * @return Glossary
     */
    public function setPublicHtmlFile($publicHtmlFile = null)
    {
        $this->publicHtmlFile = $publicHtmlFile;

        return $this;
    }

    /**
     * Get publicHtmlFile.
     *
     * @return string|null
     */
    public function getPublicHtmlFile()
    {
        return $this->publicHtmlFile;
    }

    /**
     * Set publicXmlFile.
     *
     * @param string|null $publicXmlFile
     *
     * @return Glossary
     */
    public function setPublicXmlFile($publicXmlFile = null)
    {
        $this->publicXmlFile = $publicXmlFile;

        return $this;
    }

    /**
     * Get publicXmlFile.
     *
     * @return string|null
     */
    public function getPublicXmlFile()
    {
        return $this->publicXmlFile;
    }

    /**
     * Set gloMenuActive.
     *
     * @param string|null $gloMenuActive
     *
     * @return Glossary
     */
    public function setGloMenuActive($gloMenuActive = null)
    {
        $this->gloMenuActive = $gloMenuActive;

        return $this;
    }

    /**
     * Get gloMenuActive.
     *
     * @return string|null
     */
    public function getGloMenuActive()
    {
        return $this->gloMenuActive;
    }

    /**
     * Set downloadsActive.
     *
     * @param string|null $downloadsActive
     *
     * @return Glossary
     */
    public function setDownloadsActive($downloadsActive = null)
    {
        $this->downloadsActive = $downloadsActive;

        return $this;
    }

    /**
     * Get downloadsActive.
     *
     * @return string|null
     */
    public function getDownloadsActive()
    {
        return $this->downloadsActive;
    }

    /**
     * Set presMode.
     *
     * @param string $presMode
     *
     * @return Glossary
     */
    public function setPresMode($presMode)
    {
        $this->presMode = $presMode;

        return $this;
    }

    /**
     * Get presMode.
     *
     * @return string
     */
    public function getPresMode()
    {
        return $this->presMode;
    }

    /**
     * Set snippetLength.
     *
     * @param int $snippetLength
     *
     * @return Glossary
     */
    public function setSnippetLength($snippetLength)
    {
        $this->snippetLength = $snippetLength;

        return $this;
    }

    /**
     * Get snippetLength.
     *
     * @return int
     */
    public function getSnippetLength()
    {
        return $this->snippetLength;
    }

    /**
     * Set showTax.
     *
     * @param bool $showTax
     *
     * @return Glossary
     */
    public function setShowTax($showTax)
    {
        $this->showTax = $showTax;

        return $this;
    }

    /**
     * Get showTax.
     *
     * @return bool
     */
    public function getShowTax()
    {
        return $this->showTax;
    }
}

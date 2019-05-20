<?php



/**
 * IlBiblSettings
 */
class IlBiblSettings
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string
     */
    private $name = '-';

    /**
     * @var string
     */
    private $url = '-';

    /**
     * @var string|null
     */
    private $img;

    /**
     * @var bool|null
     */
    private $showInList = '0';


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
     * Set name.
     *
     * @param string $name
     *
     * @return IlBiblSettings
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return IlBiblSettings
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set img.
     *
     * @param string|null $img
     *
     * @return IlBiblSettings
     */
    public function setImg($img = null)
    {
        $this->img = $img;

        return $this;
    }

    /**
     * Get img.
     *
     * @return string|null
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * Set showInList.
     *
     * @param bool|null $showInList
     *
     * @return IlBiblSettings
     */
    public function setShowInList($showInList = null)
    {
        $this->showInList = $showInList;

        return $this;
    }

    /**
     * Get showInList.
     *
     * @return bool|null
     */
    public function getShowInList()
    {
        return $this->showInList;
    }
}

<?php



/**
 * OpenidProvider
 */
class OpenidProvider
{
    /**
     * @var int
     */
    private $providerId = '0';

    /**
     * @var bool|null
     */
    private $enabled;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var int|null
     */
    private $image;


    /**
     * Get providerId.
     *
     * @return int
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * Set enabled.
     *
     * @param bool|null $enabled
     *
     * @return OpenidProvider
     */
    public function setEnabled($enabled = null)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled.
     *
     * @return bool|null
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return OpenidProvider
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url.
     *
     * @param string|null $url
     *
     * @return OpenidProvider
     */
    public function setUrl($url = null)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set image.
     *
     * @param int|null $image
     *
     * @return OpenidProvider
     */
    public function setImage($image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return int|null
     */
    public function getImage()
    {
        return $this->image;
    }
}

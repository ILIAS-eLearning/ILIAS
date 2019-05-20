<?php



/**
 * LsoSettings
 */
class LsoSettings
{
    /**
     * @var int
     */
    private $objId;

    /**
     * @var string|null
     */
    private $abstract;

    /**
     * @var string|null
     */
    private $extro;

    /**
     * @var string|null
     */
    private $abstractImage;

    /**
     * @var string|null
     */
    private $extroImage;

    /**
     * @var bool
     */
    private $gallery = '0';


    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set abstract.
     *
     * @param string|null $abstract
     *
     * @return LsoSettings
     */
    public function setAbstract($abstract = null)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Get abstract.
     *
     * @return string|null
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set extro.
     *
     * @param string|null $extro
     *
     * @return LsoSettings
     */
    public function setExtro($extro = null)
    {
        $this->extro = $extro;

        return $this;
    }

    /**
     * Get extro.
     *
     * @return string|null
     */
    public function getExtro()
    {
        return $this->extro;
    }

    /**
     * Set abstractImage.
     *
     * @param string|null $abstractImage
     *
     * @return LsoSettings
     */
    public function setAbstractImage($abstractImage = null)
    {
        $this->abstractImage = $abstractImage;

        return $this;
    }

    /**
     * Get abstractImage.
     *
     * @return string|null
     */
    public function getAbstractImage()
    {
        return $this->abstractImage;
    }

    /**
     * Set extroImage.
     *
     * @param string|null $extroImage
     *
     * @return LsoSettings
     */
    public function setExtroImage($extroImage = null)
    {
        $this->extroImage = $extroImage;

        return $this;
    }

    /**
     * Get extroImage.
     *
     * @return string|null
     */
    public function getExtroImage()
    {
        return $this->extroImage;
    }

    /**
     * Set gallery.
     *
     * @param bool $gallery
     *
     * @return LsoSettings
     */
    public function setGallery($gallery)
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * Get gallery.
     *
     * @return bool
     */
    public function getGallery()
    {
        return $this->gallery;
    }
}

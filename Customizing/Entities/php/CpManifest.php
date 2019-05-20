<?php



/**
 * CpManifest
 */
class CpManifest
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $base;

    /**
     * @var string|null
     */
    private $defaultorganization;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $uri;

    /**
     * @var string|null
     */
    private $version;


    /**
     * Get cpNodeId.
     *
     * @return int
     */
    public function getCpNodeId()
    {
        return $this->cpNodeId;
    }

    /**
     * Set base.
     *
     * @param string|null $base
     *
     * @return CpManifest
     */
    public function setBase($base = null)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Get base.
     *
     * @return string|null
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Set defaultorganization.
     *
     * @param string|null $defaultorganization
     *
     * @return CpManifest
     */
    public function setDefaultorganization($defaultorganization = null)
    {
        $this->defaultorganization = $defaultorganization;

        return $this;
    }

    /**
     * Get defaultorganization.
     *
     * @return string|null
     */
    public function getDefaultorganization()
    {
        return $this->defaultorganization;
    }

    /**
     * Set id.
     *
     * @param string|null $id
     *
     * @return CpManifest
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return CpManifest
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
     * Set uri.
     *
     * @param string|null $uri
     *
     * @return CpManifest
     */
    public function setUri($uri = null)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri.
     *
     * @return string|null
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set version.
     *
     * @param string|null $version
     *
     * @return CpManifest
     */
    public function setVersion($version = null)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }
}

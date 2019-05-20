<?php



/**
 * EcsPartSettings
 */
class EcsPartSettings
{
    /**
     * @var int
     */
    private $sid = '0';

    /**
     * @var int
     */
    private $mid = '0';

    /**
     * @var bool
     */
    private $export = '0';

    /**
     * @var bool
     */
    private $import = '0';

    /**
     * @var bool|null
     */
    private $importType;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $cname;

    /**
     * @var bool|null
     */
    private $token = '1';

    /**
     * @var string|null
     */
    private $exportTypes;

    /**
     * @var string|null
     */
    private $importTypes;

    /**
     * @var bool
     */
    private $dtoken = '1';


    /**
     * Set sid.
     *
     * @param int $sid
     *
     * @return EcsPartSettings
     */
    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get sid.
     *
     * @return int
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set mid.
     *
     * @param int $mid
     *
     * @return EcsPartSettings
     */
    public function setMid($mid)
    {
        $this->mid = $mid;

        return $this;
    }

    /**
     * Get mid.
     *
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Set export.
     *
     * @param bool $export
     *
     * @return EcsPartSettings
     */
    public function setExport($export)
    {
        $this->export = $export;

        return $this;
    }

    /**
     * Get export.
     *
     * @return bool
     */
    public function getExport()
    {
        return $this->export;
    }

    /**
     * Set import.
     *
     * @param bool $import
     *
     * @return EcsPartSettings
     */
    public function setImport($import)
    {
        $this->import = $import;

        return $this;
    }

    /**
     * Get import.
     *
     * @return bool
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * Set importType.
     *
     * @param bool|null $importType
     *
     * @return EcsPartSettings
     */
    public function setImportType($importType = null)
    {
        $this->importType = $importType;

        return $this;
    }

    /**
     * Get importType.
     *
     * @return bool|null
     */
    public function getImportType()
    {
        return $this->importType;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return EcsPartSettings
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
     * Set cname.
     *
     * @param string|null $cname
     *
     * @return EcsPartSettings
     */
    public function setCname($cname = null)
    {
        $this->cname = $cname;

        return $this;
    }

    /**
     * Get cname.
     *
     * @return string|null
     */
    public function getCname()
    {
        return $this->cname;
    }

    /**
     * Set token.
     *
     * @param bool|null $token
     *
     * @return EcsPartSettings
     */
    public function setToken($token = null)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token.
     *
     * @return bool|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set exportTypes.
     *
     * @param string|null $exportTypes
     *
     * @return EcsPartSettings
     */
    public function setExportTypes($exportTypes = null)
    {
        $this->exportTypes = $exportTypes;

        return $this;
    }

    /**
     * Get exportTypes.
     *
     * @return string|null
     */
    public function getExportTypes()
    {
        return $this->exportTypes;
    }

    /**
     * Set importTypes.
     *
     * @param string|null $importTypes
     *
     * @return EcsPartSettings
     */
    public function setImportTypes($importTypes = null)
    {
        $this->importTypes = $importTypes;

        return $this;
    }

    /**
     * Get importTypes.
     *
     * @return string|null
     */
    public function getImportTypes()
    {
        return $this->importTypes;
    }

    /**
     * Set dtoken.
     *
     * @param bool $dtoken
     *
     * @return EcsPartSettings
     */
    public function setDtoken($dtoken)
    {
        $this->dtoken = $dtoken;

        return $this;
    }

    /**
     * Get dtoken.
     *
     * @return bool
     */
    public function getDtoken()
    {
        return $this->dtoken;
    }
}

<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectCustomIconFactory
 */
class ilObjectCustomIconFactory
{
    /**
     * @var \ILIAS\Filesystem\Filesystem
     */
    protected $webDirectory;

    /**
     * @var \ILIAS\FileUpload\FileUpload
     */
    protected $uploadService;

    /**
     * @var \ilObjectDataCache
     */
    protected $objectCache;


    /**
     * ilObjectCustomIconFactory constructor.
     * @param \ILIAS\Filesystem\Filesystem $webDirectory
     * @param \ILIAS\FileUpload\FileUpload $uploadService
     */
    public function __construct(
        \ILIAS\Filesystem\Filesystem $webDirectory,
        \ILIAS\FileUpload\FileUpload $uploadService,
        \ilObjectDataCache $objectCache
    ) {
        $this->webDirectory  = $webDirectory;
        $this->uploadService = $uploadService;
        $this->objectCache   = $objectCache;
    }

    /**
     * @var string $type
     * @return \ilCustomIconObjectConfiguration
     */
    public function getConfigurationByType(string $type) : \ilCustomIconObjectConfiguration
    {
        switch ($type) {
            case 'grp':
            case 'root':
            case 'cat':
            case 'fold':
            case 'crs':
            case 'prg':
                require_once 'Services/Object/Icon/classes/class.ilContainerCustomIconConfiguration.php';
                $configuration = new \ilContainerCustomIconConfiguration();
                break;

            default:
                require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconConfiguration.php';
                $configuration = new \ilObjectCustomIconConfiguration();
                break;
        }

        return $configuration;
    }

    /**
     * @param int $objId The obj_id of the ILIAS object.
     * @param string $objType An optional type of the ILIAS object. If not passed, the type will be determined automatically.
     * @return \ilObjectCustomIcon
     */
    public function getByObjId(int $objId, string $objType = '') : \ilObjectCustomIcon
    {
        if (0 === strlen($objType)) {
            $objType = (string) $this->objectCache->lookupType($objId);
        }

        require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconImpl.php';
        return new \ilObjectCustomIconImpl(
            $this->webDirectory,
            $this->uploadService,
            $this->getConfigurationByType($objType),
            $objId
        );
    }

    /**
     * Get custom icon presenter
     *
     * @param int $objId
     * @param string $objType
     *
     * @return \ilObjectCustomIconPresenter
     */
    public function getPresenterByObjId(int $objId, string $objType) : \ilObjectCustomIconPresenter
    {
        if (0 === strlen($objType)) {
            $objType = $this->objectCache->lookupType($objId);
        }

        $presenter = null;
        switch ($objType) {
            case 'catr':
            case 'grpr':
            case 'crsr':
                $presenter = new \ilObjectReferenceCustomIconPresenter($objId, $this);
                $presenter->init();
                break;

            default:
                $presenter = new \ilObjectCustomIconPresenterImpl($this->getByObjId((int) $objId, (string) $objType));
                break;

        }
        return $presenter;
    }
}

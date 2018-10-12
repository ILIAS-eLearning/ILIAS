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
	 * @var \ilDBInterface
	 */
	protected $dbInterface;
	
	/**
	 * ilObjectCustomIconFactory constructor.
	 * @param \ILIAS\Filesystem\Filesystem $webDirectory
	 * @param \ILIAS\FileUpload\FileUpload $uploadService
	 */
	public function __construct(
		\ILIAS\Filesystem\Filesystem $webDirectory,
		\ILIAS\FileUpload\FileUpload $uploadService,
		\ilObjectDataCache $objectCache,
		\ilDBInterface $db
	)
	{
		$this->webDirectory  = $webDirectory;
		$this->uploadService = $uploadService;
		$this->objectCache   = $objectCache;
		$this->dbInterface   = $db;

	}

	/**
	 * @var string $type
	 * @return \ilCustomIconObjectConfiguration
	 */
	public function getConfigurationByType($type)
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
	 * @param string $objId The obj_id of the ILIAS object.
	 * @param string $objType An optional type of the ILIAS object. If not passed, the type will be determined automatically.
	 * @return \ilObjectCustomIconImpl
	 */
	public function getByObjId($objId, $objType = '')
	{
		if (0 === strlen($objType)) {
			$objType = $this->objectCache->lookupType($objId);
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
	 * @param $objId
	 * @param $objType
	 *
	 * @return \ilObjectCustomIconPresenter
	 */
	public function getPresenterByObjId($objId, $objType)
	{
		if (0 === strlen($objType)) {
			$objType = $this->objectCache->lookupType($objId);
		}

		$presenter = null;
		switch($objType)
		{
			case 'catr':
			case 'grpr':
			case 'crsr':
				$target_obj_id = \ilObjectReferenceCustomIconPresenter::lookupTargetId($objId, $this->dbInterface);
				$presenter = new \ilObjectReferenceCustomIconPresenter($this->getByObjId($target_obj_id));
				break;

			default:
				$presenter = new \ilObjectCustomIconPresenterImpl($this->getByObjId($objId));
				break;

		}
		return $presenter;
	}
}
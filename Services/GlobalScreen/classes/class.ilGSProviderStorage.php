<?php

use ILIAS\GlobalScreen\Provider\DynamicProvider;
use ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider;

/**
 * Class ilGSProviderStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGSProviderStorage extends ActiveRecord {

	/**
	 * @param string $class_name
	 * @param string $purpose
	 */
	public static function registerIdentifications(string $class_name, string $purpose) {
		if (!class_exists($class_name)) {
			throw new LogicException("Cannot store unknown provider {$class_name}");
		}

		$gsp = ilGSProviderStorage::find($class_name);
		if ($gsp === null) {
			$gsp = new ilGSProviderStorage();
			$gsp->setProviderClass($class_name);
			$gsp->create();
		}
		$gsp->setPurpose($purpose);
		$gsp->setDynamic(in_array(DynamicProvider::class, class_implements($class_name)));
		$gsp->update();

		$instance = $gsp->getInstance();

		foreach ($instance->getAllIdentifications() as $identification) {
			$gsi = ilGSIdentificationStorage::find($identification->serialize());
			if ($gsi === null) {
				$gsi = new ilGSIdentificationStorage();
				$gsi->setIdentification($identification->serialize());
				$gsi->create();
			}
			$gsi->setProviderClass($gsp->getProviderClass());
			$gsi->update();
		}
	}


	/**
	 * @var string
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $provider_class;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $purpose = '';
	/**
	 * @var bool
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $dynamic = false;
	/**
	 * @var ILIAS\GlobalScreen\Provider\Provider
	 */
	protected $instance;
	/**
	 * @var string
	 */
	protected $connector_container_name = "il_gs_providers";


	/**
	 * CachingActiveRecord constructor.
	 *
	 * @param int              $primary_key
	 * @param arConnector|NULL $connector
	 */
	public function __construct($primary_key = 0, arConnector $connector = null) {
		$arConnector = $connector;
		if (is_null($arConnector)) {
			$arConnector = new ilGSStorageCache(new arConnectorDB());
		}

		parent::__construct($primary_key, $arConnector);
	}


	/**
	 * @inheritDoc
	 */
	final public function getConnectorContainerName() {
		return $this->connector_container_name;
	}


	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}


	/**
	 * @param string $id
	 *
	 * @return ilGSProviderStorage
	 */
	public function setId(string $id): ilGSProviderStorage {
		$this->id = $id;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getProviderClass(): string {
		return $this->provider_class;
	}


	/**
	 * @param string $provider_class
	 *
	 * @return ilGSProviderStorage
	 */
	public function setProviderClass(string $provider_class): ilGSProviderStorage {
		$this->provider_class = $provider_class;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getPurpose(): string {
		return $this->purpose;
	}


	/**
	 * @param string $purpose
	 *
	 * @return ilGSProviderStorage
	 */
	public function setPurpose(string $purpose): ilGSProviderStorage {
		$this->purpose = $purpose;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isDynamic(): bool {
		return $this->dynamic;
	}


	/**
	 * @param bool $dynamic
	 *
	 * @return ilGSProviderStorage
	 */
	public function setDynamic(bool $dynamic): ilGSProviderStorage {
		$this->dynamic = $dynamic;

		return $this;
	}


	/**
	 * @return \ILIAS\GlobalScreen\Provider\Provider
	 */
	public function getInstance(): ILIAS\GlobalScreen\Provider\Provider {
		global $DIC;
		if (!$this->instance instanceof ILIAS\GlobalScreen\Provider\Provider) {
			$class_name = $this->provider_class;
			$this->instance = new $class_name($DIC);
		}

		return $this->instance;
	}
}

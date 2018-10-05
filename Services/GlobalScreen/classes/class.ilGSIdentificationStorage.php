<?php

/**
 * Class ilGSProviderStorage
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGSIdentificationStorage extends ActiveRecord {

	/**
	 * @var string
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     64
	 */
	protected $identification;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     256
	 */
	protected $provider_class = '';
	/**
	 * @var bool
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 */
	protected $active = true;
	/**
	 * @var string
	 */
	protected $connector_container_name = "il_gs_identifications";


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


	final public function create() {
		if (empty($this->identification)) {
			throw new LogicException("Cannot store without identification");
		}
		parent::create();
	}


	/**
	 * @return string
	 */
	public function getIdentification(): string {
		return $this->identification;
	}


	/**
	 * @param string $identification
	 *
	 * @return ilGSIdentificationStorage
	 */
	public function setIdentification(string $identification): ilGSIdentificationStorage {
		$this->identification = $identification;

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
	 * @return ilGSIdentificationStorage
	 */
	public function setProviderClass(string $provider_class): ilGSIdentificationStorage {
		$this->provider_class = $provider_class;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isActive(): bool {
		return $this->active;
	}


	/**
	 * @param bool $active
	 *
	 * @return ilGSIdentificationStorage
	 */
	public function setActive(bool $active): ilGSIdentificationStorage {
		$this->active = $active;

		return $this;
	}
}

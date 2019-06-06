<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Container filter service factory.
 *
 * This is an Services/Container internal subservice currently not accessible via DIC API.
 * Do not use this outside of Services/Container.
 *
 * Main entry point.
 *
 * @author @leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterService
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilContainerFilterAdvMDAdapter
	 */
	protected $adv_adapter;

	/**
	 * Constructor
	 */
	public function __construct(ilLanguage $lng = null, ilContainerFilterAdvMDAdapter $adv_adapter = null,
								ilContainerFilterFieldData $container_field_data = null)
	{
		global $DIC;

		$this->lng = (is_null($lng))
			? $DIC->language()
			: $lng;

		$this->adv_adapter = (is_null($adv_adapter))
			? new ilContainerFilterAdvMDAdapter()
			: $adv_adapter;

		$this->field_data = (is_null($container_field_data))
			? new ilContainerFilterFieldData()
			: $container_field_data;
	}

	/**
	 * Utilities
	 * @return ilContainerFilterUtil
	 */
	public function util(): ilContainerFilterUtil
	{
		return new ilContainerFilterUtil($this, $this->adv_adapter, $this->lng);
	}

	/**
	 * @return ilContainerFilterAdvMDAdapter
	 */
	public function advancedMetadata(): ilContainerFilterAdvMDAdapter
	{
		return $this->adv_adapter;
	}

	/**
	 * @return ilContainerFilterFieldData
	 */
	public function data(): ilContainerFilterFieldData
	{
		return $this->field_data;
	}


	/**
	 * Field
	 *
	 * @param int $record_set_id
	 * @param int $field_id
	 * @return ilContainerFilterField
	 */
	public function field(int $record_set_id, int $field_id): ilContainerFilterField
	{
		return new ilContainerFilterField($record_set_id, $field_id);
	}

	/**
	 * Set
	 *
	 * @param array $fields
	 * @return ilContainerFilterSet
	 */
	public function set(array $fields): ilContainerFilterSet
	{
		return new ilContainerFilterSet($fields);
	}

	/**
	 * Get standard set
	 * @return ilContainerFilterSet
	 */
	public function standardSet(): ilContainerFilterSet
	{
		return new ilContainerFilterSet([
			$this->field(0, ilContainerFilterField::STD_FIELD_TITLE),
			$this->field(0, ilContainerFilterField::STD_FIELD_DESCRIPTION),
			$this->field(0, ilContainerFilterField::STD_FIELD_TITLE_DESCRIPTION),
			$this->field(0, ilContainerFilterField::STD_FIELD_KEYWORD),
			$this->field(0, ilContainerFilterField::STD_FIELD_AUTHOR),
			$this->field(0, ilContainerFilterField::STD_FIELD_COPYRIGHT),
			$this->field(0, ilContainerFilterField::STD_FIELD_TUTORIAL_SUPPORT),
			$this->field(0, ilContainerFilterField::STD_FIELD_OBJECT_TYPE)
			]
		);
	}

	/**
	 * User filter
	 *
	 * @param array $data
	 * @return ilContainerUserFilter
	 */
	public function userFilter($data)
	{
		return new ilContainerUserFilter($data);
	}


}
<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Filter field set
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterSet
{
	/**
	 * @var ilContainerFilterField[]
	 */
	protected $filters;

	/**
	 * @var array
	 */
	protected $ids = [];

	/**
	 * Constructor
	 * @param ilContainerFilterField[] $filters
	 */
	public function __construct(array $filters)
	{
		$this->filters = $filters;

		$this->ids = array_map(function($f) {
			/** @var ilContainerFilterField $f */
			return $f->getRecordSetId()."_".$f->getFieldId();
		}, $filters);

	}
	
	/**
	 * Get filters
	 *
	 * @return ilContainerFilterField[]
	 */
	public function getFields(): array
	{
		return $this->filters;
	}

	/**
	 * Has filter field
	 * @param int $record_set_id
	 * @param int $field_id
	 * @return bool
	 */
	public function has(int $record_set_id, int $field_id): bool
	{
		return in_array($record_set_id."_".$field_id, $this->ids);
	}

	

}
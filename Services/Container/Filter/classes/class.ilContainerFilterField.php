<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class ilContainerFilterField
{
	const STD_FIELD_TITLE = 1;
	const STD_FIELD_DESCRIPTION = 2;
	const STD_FIELD_TITLE_DESCRIPTION = 3;
	const STD_FIELD_KEYWORD = 4;
	const STD_FIELD_AUTHOR = 5;
	const STD_FIELD_COPYRIGHT = 6;
	const STD_FIELD_TUTORIAL_SUPPORT = 7;
	const STD_FIELD_OBJECT_TYPE = 8;

	/**
	 * @var int
	 */
	protected $record_set_id = 0;

	/**
	 * @var int
	 */
	protected $field_id = 0;

	/**
	 * Constructor
	 * @param int $record_set_id
	 * @param int $field_id
	 */
	public function __construct(int $record_set_id, int $field_id) {
		$this->record_set_id = $record_set_id;
		$this->field_id = $field_id;
	}

	/**
	 * @return int
	 */
	public function getFieldId()
	{
		return $this->field_id;
	}

	/**
	 * @return int
	 */
	public function getRecordSetId()
	{
		return $this->record_set_id;
	}
}
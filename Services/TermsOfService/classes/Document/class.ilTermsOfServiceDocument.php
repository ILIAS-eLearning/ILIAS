<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocument extends ActiveRecord
{
	const TABLE_NAME = 'tos_documents';

	/**
	 * @var string
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           4
	 * @db_is_primary       true
	 * @con_sequence        true
	 */
	protected $id;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $creation_ts = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $modification_ts = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $owner_usr_id = 0;

	/**
	 * @var int
	 * @con_has_field   true
	 * @con_fieldtype   integer
	 * @con_length      4
	 */
	protected $sorting = 0;

	/**
	 * @var string
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           255
	 */
	protected $title = '';

	/**
	 * @var string
	 * @db_has_field        true
	 * @db_fieldtype        clob
	 */
	protected $text = '';
	
	/**
	 * @var string
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           255
	 */
	protected $last_modified_usr_id = '';

	/**
	 * @inheritdoc
	 */
	static function returnDbTableName()
	{
		return self::TABLE_NAME;
	}

	/**
	 * @inheritdoc
	 */
	public function create()
	{
		$this->setCreationTs(time());

		parent::create();
	}

	/**
	 * @inheritdoc
	 */
	public function update()
	{
		$this->setModificationTs(time());

		parent::update();
	}
}
<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPageImporter
 */
class ilContentPageImporter extends \ilXmlImporter implements \ilContentPageObjectConstants
{
	/**
	 * @var ilContentPageDataSet
	 */
	protected $ds;

	/**
	 * 
	 */
	public function init()
	{
		$this->ds = new \ilContentPageDataSet();
		$this->ds->setDSPrefix('ds');
		$this->ds->setImportDirectory($this->getImportDirectory());
	}

	/**
	 * @inheritdoc
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		$parser = new \ilDataSetImportParser($a_entity, $this->getSchemaVersion(), $a_xml, $this->ds, $a_mapping);
	}

	/**
	 * @inheritdoc
	 */
	public function finalProcessing($a_mapping)
	{
		parent::finalProcessing($a_mapping);

		$copaMap = $a_mapping->getMappingsOfEntity('Services/COPage', 'pg');
		foreach ($copaMap as $oldCopaId => $newCopaId) {
			$newCopaId = substr($newCopaId, strlen(self::OBJ_TYPE) + 1);

			\ilContentPagePage::_writeParentId(self::OBJ_TYPE, $newCopaId, $newCopaId);
		}
	}
}
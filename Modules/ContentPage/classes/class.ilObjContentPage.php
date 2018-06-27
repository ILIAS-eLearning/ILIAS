<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjContentPage
 */
class ilObjContentPage extends \ilObject2 implements \ilContentPageObjectConstants
{
	/**
	 * @inheritdoc
	 */
	protected function initType()
	{
		$this->type = self::OBJ_TYPE;
	}

	/**
	 * @inheritdoc
	 */
	protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
		parent::doCloneObject($new_obj, $a_target_id, $a_copy_id);

		if (\ilContentPagePage::_exists($this->getType(), $this->getId())) {
			$originalPageObject = new \ilContentPagePage($this->getId());
			$originalXML = $originalPageObject->getXMLContent();

			$duplicatePageObject = new \ilContentPagePage();
			$duplicatePageObject->setId($new_obj->getId());
			$duplicatePageObject->setParentId($new_obj->getId());
			$duplicatePageObject->setXMLContent($originalXML);
			$duplicatePageObject->createFromXML();
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function doUpdate()
	{
		parent::doUpdate();
	}

	/**
	 * @inheritdoc
	 */
	protected function doDelete()
	{
		parent::doDelete();

		if (\ilContentPagePage::_exists($this->getType(), $this->getId())) {
			$originalPageObject = new \ilContentPagePage($this->getId());
			$originalPageObject->delete();
		}
	}

	/**
	 * @return int[]
	 */
	public function getPageObjIds()
	{
		$pageObjIds = [];

		$sql = "SELECT page_id FROM page_object WHERE parent_id = %s AND parent_type = %s";
		$res = $this->db->queryF(
			$sql,
			['integer', 'text'],
			[$this->getId(), $this->getType()]
		);

		while ($row = $this->db->fetchAssoc($res)) {
			$pageObjIds[] = $row['page_id'];
		}

		return $pageObjIds;
	}
}
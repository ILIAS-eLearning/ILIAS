<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'class.ilDataCollectionRecordField.php';
require_once("./Services/Rating/classes/class.ilRatingGUI.php");
require_once("./Services/Link/classes/class.ilLink.php");
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');

/**
 * Class ilDataCollectionField
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionILIASRefField extends ilDataCollectionRecordField{

	/**
	 * @var int
	 */
	protected $dcl_obj_id;

    /**
     * @var array
     */
    protected $properties = array();

	public function __construct(ilDataCollectionRecord $record, ilDataCollectionField $field){
		parent::__construct($record, $field);
		$dclTable = ilDataCollectionCache::getTableCache($this->getField()->getTableId());
		$this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
        $this->properties = $field->getProperties();
	}


    /**
     * @param array $options
     * @return mixed|string
     */
    public function getHTML(array $options = array()){
        $value = $this->getValue();
        if (!$value) {
            return '';
        }
		$id = ilObject::_lookupObjId($value);
        $title = ilObject::_lookupTitle($id);
        if ($this->properties[ilDataCollectionField::PROPERTYID_DISPLAY_COPY_LINK_ACTION_MENU]) {
            $html = $this->getLinkHTML($title, true);
        } else if ($this->properties[ilDataCollectionField::PROPERTYID_ILIAS_REFERENCE_LINK]) {
            $html = $this->getLinkHTML($title);
        } else {
            $html = $title;
        }
		return $html;
	}


    public function getSingleHTML(array $options=array())
    {
        $value = $this->getValue();
        if (!$value) {
            return '';
        }
        $id = ilObject::_lookupObjId($value);
        $title = ilObject::_lookupTitle($id);
        if ($this->properties[ilDataCollectionField::PROPERTYID_ILIAS_REFERENCE_LINK]) {
            return $this->getLinkHTML($title);
        }
        return $title;
    }


    /**
     * @param $title
     * @param $show_action_menu
     * @return string
     */
    public function getLinkHTML($title, $show_action_menu=false) {
        global $lng;
        $link = ilLink::_getStaticLink($this->getValue());
        if ($show_action_menu) {
            $list = new ilAdvancedSelectionListGUI();
            $list->setId('adv_list_copy_link_' . $this->field->getId() . $this->record->getId());
            $list->setListTitle($title);
            if ($this->properties[ilDataCollectionField::PROPERTYID_ILIAS_REFERENCE_LINK]) {
                $list->addItem($lng->txt('view'), 'view', $link);
            }
            $list->addItem($lng->txt('copy'), 'copy', $this->getActionLink('copy'));
            $list->addItem($lng->txt('link'), 'link', $this->getActionLink('link'));
            return $list->getHTML();
        } else {
            return "<a href=\"$link\">$title</a>";
        }
    }

	public function getExportValue(){
		$value = $this->getValue();
		$link = ilLink::_getStaticLink($value);
		return $link;
	}

	public function getStatus(){
		global $ilDB, $ilUser;
		$usr_id = $ilUser->getId();
		$obj_ref = $this->getValue();
		$obj_id = ilObject2::_lookupObjectId($obj_ref);
		$query = "  SELECT status_changed, status
                    FROM ut_lp_marks
                    WHERE usr_id = ".$usr_id." AND obj_id = ".$obj_id."
";
		$result = $ilDB->query($query);
		return ($result->numRows() == 0)? false:$result->fetchRow(DB_FETCHMODE_OBJECT);
	}


    /**
     * @param string $mode copy|link
     * @return string
     */
    protected function getActionLink($mode)
    {
        global $ilCtrl;
        switch ($mode) {
            case 'copy':
                $ilCtrl->setParameterByClass('ilobjectcopygui', 'item_ref_id', $this->getValue());
                $ilCtrl->setParameterByClass('ilobjrootfoldergui', 'item_ref_id', $this->getValue());
                $ilCtrl->setParameterByClass('ilobjectcopygui', 'source_id', $this->getValue());
                return $ilCtrl->getLinkTargetByClass('ilobjectcopygui', 'initTargetSelection');
            case 'link':
                return $ilCtrl->getLinkTargetByClass(array('ilrepositorygui','ilobjrootfoldergui'), 'link');
            default:
                return '';
        }
    }

}
?>
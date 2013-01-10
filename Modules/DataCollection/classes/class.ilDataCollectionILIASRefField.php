<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'class.ilDataCollectionRecordField.php';
require_once("./Services/Rating/classes/class.ilRatingGUI.php");
require_once("./Services/Link/classes/class.ilLink.php");

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
	 * @var bool
	 */
	protected $rated;

	/**
	 * @var int
	 */
	protected $dcl_obj_id;

	public function __construct(ilDataCollectionRecord $record, ilDataCollectionField $field){
		parent::__construct($record, $field);
		$dclTable = ilDataCollectionCache::getTableCache($this->getField()->getTableId());
		$this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
	}


    /*
      * getHTML
      *
      * @param array $options
      * @return string
      */
	public function getHTML(array $options = array()){
		$value = $this->getValue();
		$link = ilLink::_getStaticLink($value);
		$id = ilObject::_lookupObjId($value);


        if($options['link']['display']) {
            $html = $this->getLinkHTML($options['link']['name']);
        } else {
            $html = ilObject::_lookupTitle($id);
        }

		return $html;
	}

    /*
      * get Link
      *
      * @param  string    $link_name
      */
    public function getLinkHTML($link_name = NULL) {
        $value = $this->getValue();
        $link = ilLink::_getStaticLink($value);
        $id = ilObject::_lookupObjId($value);

        if($link_name) {
            $html = "<a href='".$link."'>".$link_name."</a>";
        } else {
            $html = "<a href='".$link."'>".ilObject::_lookupTitle($id)."</a>";
        }

        return $html;
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
}
?>
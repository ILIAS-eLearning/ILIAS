<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Settings for LO courses
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilObjectTableGUI extends ilTable2GUI
{
	protected $objects = array();
	
	/**
	 * Constructor
	 * @param type $a_parent_obj
	 * @param type $a_parent_cmd
	 * @param type $a_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_id)
	{
		$this->setId('obj_table_'.$a_id);
		parent::__construct($a_parent_obj, $a_parent_cmd, '');
		
		$this->setOrderColumn('title');
		
		$this->setRowTemplate('tpl.object_table_row.html', 'Services/Object');
	}
	
	
	public function setObjects($a_ref_ids)
	{
		$this->objects = $a_ref_ids;
	}
	
	public function getObjects()
	{
		return $this->objects;
	}
	
	public function init()
	{
		$this->addColumn($this->lng->txt('type'), 'type','30px');
		$this->addColumn($this->lng->txt('title'),'title');
	}
	
	public function fillRow($set)
	{
		include_once './Services/Link/classes/class.ilLink.php';
		$this->tpl->setVariable('OBJ_LINK',ilLink::_getLink($set['ref_id'], $set['type']));
		$this->tpl->setVariable('OBJ_LINKED_TITLE',$set['title']);
		$this->tpl->setVariable('TYPE_IMG',ilUtil::getTypeIconPath($set['type'], $set['obj_id']));
		$this->tpl->setVariable('TYPE_STR',$this->lng->txt('obj_'.$set['type']));
	}
	
	/**
	 * Parse objects
	 */
	public function parse()
	{
		$counter = 0;
		$set = array();
		foreach($this->getObjects() as $ref_id)
		{
			$type = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
			if($type == 'rolf')
			{
				continue;
			}
			
			$set[$counter]['ref_id'] = $ref_id;
			$set[$counter]['obj_id'] = ilObject::_lookupObjId($ref_id);
			$set[$counter]['type'] = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
			$set[$counter]['title'] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
			$counter++;
		}
		
		$this->setData($set);
	}
}
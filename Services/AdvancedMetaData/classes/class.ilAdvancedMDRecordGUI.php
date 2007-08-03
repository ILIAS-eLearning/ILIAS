<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesAdvancedMetaData
*/
class ilAdvancedMDRecordGUI
{
	const MODE_EDITOR = 1;
	const MODE_SEARCH = 2; 
	
	protected $lng;
	
	private $mode;
	private $obj_type;
	private $obj_id;
	
	private $form;
	private $values = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int mode either MODE_EDITOR or MODE_SEARCH
	 * @param int obj_type
	 * 
	 */
	public function __construct($a_mode,$a_obj_type = '',$a_obj_id)
	{
		global $lng;
	 	
	 	$this->mode = $a_mode;
	 	$this->obj_type = $a_obj_type;
	 	$this->obj_id = $a_obj_id;
	}
	
	/**
	 * Set property form object
	 *
	 * @access public
	 * @param object ilPropertyFormGUI instance
	 * 
	 */
	public function setPropertyForm(ilPropertyFormGUI $form)
	{
	 	$this->form = $form;
	}
	
	/**
	 * Get HTML
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function parse()
	{
	 	switch($this->mode)
	 	{
	 		case self::MODE_EDITOR:
	 			return $this->parseEditor();
	 			
	 		case self::MODE_SEARCH:
	 			return $this->parseSearch();
	 			
	 		default:
	 			die('Not implemented yet');
	 	}
	}
	
	/**
	 * Load values from post
	 *
	 * @access public
	 * 
	 */
	public function loadFromPost()
	{
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
		foreach($_POST['md'] as $field_id => $value)
		{
			$val = ilAdvancedMDValue::_getInstance($this->obj_id,$field_id);
			$val->setValue(ilUtil::stripSlashes($value));
			$this->values[] = $val;
		}
	}
	
	/**
	 * Save values
	 *
	 * @access public
	 * 
	 */
	public function saveValues()
	{
	 	foreach($this->values as $value)
	 	{
	 		$value->save();
	 	}
	 	return true;
	}
	
	/**
	 * Parse property form in editorr mode
	 *
	 * @access private
	 * 
	 */
	private function parseEditor()
	{
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
	 	foreach(ilAdvancedMDRecord::_getActivatedRecordsByObjectType($this->obj_type) as $record_obj)
	 	{
	 		$section = new ilFormSectionHeaderGUI();
	 		$section->setTitle($record_obj->getTitle());
	 		$section->setInfo($record_obj->getDescription());
	 		$this->form->addItem($section);
	 		
	 		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
	 		foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($record_obj->getRecordId()) as $def)
	 		{
	 			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValue.php');
				$value = ilAdvancedMDValue::_getInstance($this->obj_id,$def->getFieldId());
	 			
	 			switch($def->getFieldType())
	 			{
	 				case ilAdvancedMDFieldDefinition::TYPE_TEXT:
	 					$text = new ilTextInputGUI($def->getTitle(),'md['.$def->getFieldId().']');
	 					$text->setValue($value->getValue());
	 					$text->setSize(40);
	 					$text->setMaxLength(512);
	 					$this->form->addItem($text);
	 					break;
	 					
	 				case ilAdvancedMDFieldDefinition::TYPE_SELECT:
	 					$select = new ilSelectInputGUI($def->getTitle(),'md['.$def->getFieldId().']');
	 					$select->setOptions($def->getFieldValuesForSelect());
	 					$select->setValue($value->getValue());
	 					$this->form->addItem($select);
	 					break;
	 			}
	 		}
	 	}
	}
	
}


?>
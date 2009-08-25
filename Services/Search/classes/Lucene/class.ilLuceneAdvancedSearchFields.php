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


include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchSettings.php';

/** 
* Field definitions of advanced meta data search
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedSearchFields
{
	private static $instance = null;
	private $settings = null;
	
	protected $lng = null;
	
	private static $fields = null;
	private $active_fields = array();
	
	private static $sections = null;
	private $active_sections = array(); 
	
	
	protected function __construct()
	{
		global $lng;
		
		$this->settings = ilLuceneAdvancedSearchSettings::getInstance();
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule('meta');
		
		$this->readFields();
		$this->readSections();
	}
	
	/**
	 * Get singleton instance
	 */
	public static function getInstance()
	{
		if(isset(self::$instance) and self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilLuceneAdvancedSearchFields();
	}
	
	/**
	 * Return an array of all meta data fields
	 */
	public static function getFields()
	{
		global $lng;
		
		$lng->loadLanguageModule('meta');
		
		$fields = array(
			'lom_content'				=> $lng->txt('content'),
			//'lom_type'					=> $lng->txt('type'),
			'lom_language'				=> $lng->txt('language'),
			'lom_keyword'				=> $lng->txt('meta_keyword'),	
			'lom_coverage'				=> $lng->txt('meta_coverage'),	
			'lom_structure'				=> $lng->txt('meta_structure'),	
			'lom_status'				=> $lng->txt('meta_status'),	
			'lom_version'				=> $lng->txt('meta_version'),	
			'lom_contribute'			=> $lng->txt('meta_contribute'),	
			'lom_format'				=> $lng->txt('meta_format'),	
			'lom_operating_system'		=> $lng->txt('meta_operating_system'),	
			'lom_browser'				=> $lng->txt('meta_browser'),	
			'lom_interactivity'			=> $lng->txt('meta_interactivity_type'),	
			'lom_resource'				=> $lng->txt('meta_learning_resource_type'),	
			'lom_level'					=> $lng->txt('meta_interactivity_level'),	
			'lom_density'				=> $lng->txt('meta_semantic_density'),	
			'lom_user_role'				=> $lng->txt('meta_intended_end_user_role'),	
			'lom_context'				=> $lng->txt('meta_context'),	
			'lom_difficulty'			=> $lng->txt('meta_difficulty'),	
			'lom_costs'					=> $lng->txt('meta_cost'),	
			'lom_copyright'				=> $lng->txt('meta_copyright_and_other_restrictions'),	
			'lom_purpose'				=> $lng->txt('meta_purpose'),	
			'lom_taxon'					=> $lng->txt('meta_taxon')
			);
			
		// Append all advanced meta data fields
		include_once './Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php';
		include_once './Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php';
		foreach(ilAdvancedMDRecord::_getRecords() as $record)
		{
			foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($record->getRecordId()) as $def)
			{
				if($def->isSearchable())
				{
					$fields['adv_'.$def->getFieldId()] = $def->getTitle();
				}
			}	
		}
		return $fields;
	}
	
	/**
	 * Get all active fields 
	 */
	public function getActiveFields()
	{
		return $this->active_fields ? $this->active_fields : array();
	}
	
	public function getActiveSections()
	{
		return $this->active_sections ? $this->active_sections : array();
	}
	
	public function getFormElement($a_query,$a_field_name)
	{
		include_once './Services/MetaData/classes/class.ilMDUtilSelect.php';

		$a_post_name = 'query['.$a_field_name.']';

		switch($a_field_name)
		{
			case 'lom_content':
				$text = new ilTextInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$text->setSubmitFormOnEnter(true);
				$text->setValue($a_query['lom_content']);
				$text->setSize(30);
				$text->setMaxLength(255);
				return $text;
			
			// General	
			case 'lom_language':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_language']);
				$select->setOptions(ilMDUtilSelect::_getLanguageSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;
							
			case 'lom_keyword':
				$text = new ilTextInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$text->setSubmitFormOnEnter(true);
				$text->setValue($a_query['lom_keyword']);
				$text->setSize(30);
				$text->setMaxLength(255);
				return $text;

			case 'lom_coverage':
				$text = new ilTextInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$text->setSubmitFormOnEnter(true);
				$text->setValue($a_query['lom_coverage']);
				$text->setSize(30);
				$text->setMaxLength(255);
				return $text;

			case 'lom_structure':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_structure']);
				$select->setOptions(ilMDUtilSelect::_getStructureSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;

			// Lifecycle
			case 'lom_status':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_status']);
				$select->setOptions(ilMDUtilSelect::_getStatusSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;

			case 'lom_version':
				$text = new ilTextInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$text->setSubmitFormOnEnter(true);
				$text->setValue($a_query['lom_version']);
				$text->setSize(30);
				$text->setMaxLength(255);
				return $text;
				
			case 'lom_contribute':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],'query['.'lom_role'.']');
				$select->setValue($a_query['lom_role']);
				$select->setOptions(ilMDUtilSelect::_getRoleSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
					
					$text = new ilTextInputGUI($this->lng->txt('meta_entry'),'query['.'lom_role_entry'.']');
					$text->setValue($a_query['lom_role_entry']);
					$text->setSize(30);
					$text->setMaxLength(255);
				
				$select->addSubItem($text);
				return $select;

			// Technical
			case 'lom_format':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_format']);
				$select->setOptions(ilMDUtilSelect::_getFormatSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;

			case 'lom_operating_system':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_operating_system']);
				$select->setOptions(ilMDUtilSelect::_getOperatingSystemSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;

			case 'lom_browser':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_browser']);
				$select->setOptions(ilMDUtilSelect::_getBrowserSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;
			
			// Education
			case 'lom_interactivity':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_interactivity']);
				$select->setOptions(ilMDUtilSelect::_getInteractivityTypeSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;
			
			case 'lom_resource':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_resource']);
				$select->setOptions(ilMDUtilSelect::_getLearningResourceTypeSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;
			
			case 'lom_level':
				$range = new ilCustomInputGUI($this->active_fields[$a_field_name]);
				$html = $this->getRangeSelect(
					$this->lng->txt('from'),
					ilMDUtilSelect::_getInteractivityLevelSelect(
						$a_query['lom_level_start'],
						'query['.'lom_level_start'.']',
						array(0 => $this->lng->txt('search_any'))),
					$this->lng->txt('until'),
					ilMDUtilSelect::_getInteractivityLevelSelect(
						$a_query['lom_level_end'],
						'query['.'lom_level_end'.']',
						array(0 => $this->lng->txt('search_any')))
					);
				$range->setHTML($html);
				return $range;
						
			case 'lom_density':
				$range = new ilCustomInputGUI($this->active_fields[$a_field_name]);
				$html = $this->getRangeSelect(
					$this->lng->txt('from'),
					ilMDUtilSelect::_getSemanticDensitySelect(
						$a_query['lom_density_start'],
						'query['.'lom_density_start'.']',
						array(0 => $this->lng->txt('search_any'))),
					$this->lng->txt('until'),
					ilMDUtilSelect::_getSemanticDensitySelect(
						$a_query['lom_density_end'],
						'query['.'lom_density_end'.']',
						array(0 => $this->lng->txt('search_any')))
					);
				$range->setHTML($html);
				return $range;

			
			case 'lom_user_role':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_user_role']);
				$select->setOptions(ilMDUtilSelect::_getIntendedEndUserRoleSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;
			
			case 'lom_context':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_context']);
				$select->setOptions(ilMDUtilSelect::_getContextSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;

			case 'lom_difficulty':
				$range = new ilCustomInputGUI($this->active_fields[$a_field_name]);
				$html = $this->getRangeSelect(
					$this->lng->txt('from'),
					ilMDUtilSelect::_getDifficultySelect(
						$a_query['lom_difficulty_start'],
						'query['.'lom_difficulty_start'.']',
						array(0 => $this->lng->txt('search_any'))),
					$this->lng->txt('until'),
					ilMDUtilSelect::_getDifficultySelect(
						$a_query['lom_difficulty_end'],
						'query['.'lom_difficulty_end'.']',
						array(0 => $this->lng->txt('search_any')))
					);
				$range->setHTML($html);
				return $range;

			// Rights				
			case 'lom_costs':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_costs']);
				$select->setOptions(ilMDUtilSelect::_getCostsSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;

			case 'lom_copyright':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_copyright']);
				$select->setOptions(ilMDUtilSelect::_getCopyrightAndOtherRestrictionsSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;
				


			// Classification
			case 'lom_purpose':
				$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$select->setValue($a_query['lom_purpose']);
				$select->setOptions(ilMDUtilSelect::_getPurposeSelect(
					'',
					$a_field_name,
					array(0 => $this->lng->txt('search_any')),
					true));
				return $select;

			case 'lom_taxon':
				$text = new ilTextInputGUI($this->active_fields[$a_field_name],$a_post_name);
				$text->setSubmitFormOnEnter(true);
				$text->setValue($a_query['lom_taxon']);
				$text->setSize(30);
				$text->setMaxLength(255);
				return $text;
				
			default:
				if(substr($a_field_name,0,3) != 'adv')
					break;
					
				// Advanced meta data
				$field_id = substr($a_field_name,4);
				include_once './Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php';
				$field = ilAdvancedMDFieldDefinition::_getInstanceByFieldId($field_id);
				
				switch($field->getFieldType())
				{
					case ilAdvancedMDFieldDefinition::TYPE_TEXT:
						$text = new ilTextInputGUI($this->active_fields[$a_field_name],$a_post_name);
						$text->setSubmitFormOnEnter(true);
						$text->setValue($a_query[$a_field_name]);
						$text->setSize(30);
						$text->setMaxLength(255);
						return $text;
						
					case ilAdvancedMDFieldDefinition::TYPE_SELECT:
						$select = new ilSelectInputGUI($this->active_fields[$a_field_name],$a_post_name);
						$select->setValue($a_query[$a_field_name]);
						$select->setOptions($field->getFieldValuesForSearch());
						return $select;
						
					case ilAdvancedMDFieldDefinition::TYPE_DATE:
					case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
						
	 					$check = new ilCheckboxInputGUI($this->active_fields[$a_field_name],$a_post_name);
	 					$check->setValue(1);
	 					$check->setChecked($a_query[$a_field_name]);
	 				
	 					$time = new ilDateTimeInputGUI($this->lng->txt('from'),$a_field_name.'_start');
						$time->setShowTime($field->getFieldType() != ilAdvancedMDFieldDefinition::TYPE_DATE);
						$time->setDate(new ilDateTime(mktime(8,0,0,date('m'),date('d'),date('Y')),IL_CAL_UNIX));
	 					$check->addSubItem($time);

	 					$time = new ilDateTimeInputGUI($this->lng->txt('until'),$a_field_name.'_end');
						$time->setShowTime($field->getFieldType() != ilAdvancedMDFieldDefinition::TYPE_DATE);
						$time->setDate(new ilDateTime(mktime(16,0,0,date('m'),date('d'),date('Y')),IL_CAL_UNIX));
	 					$check->addSubItem($time);
						return $check;
				}
		}
		return null;
	}
	
	
	/** 
	 * Called from ilLuceneAdvancedQueryParser
	 * Parse a field specific query
	 */
	public function parseFieldQuery($a_field,$a_query)
	{
		switch($a_field)
		{
			case 'lom_content':
				return $a_query;
				
			// General
			case 'lom_language':
				return 'lomLanguage:'.$a_query;
				
			case 'lom_keyword':
				return 'lomKeyword:'.$a_query;
				
			case 'lom_coverage':
				return 'lomCoverage:'.$a_query;
				
			case 'lom_structure':
				return 'lomStructure:'.$a_query;
			
			// Lifecycle	
			case 'lom_status':
				return 'lomStatus:'.$a_query;

			case 'lom_version':
				return 'lomVersion:'.$a_query;
				
			// Begin Contribute
			case 'lom_role':
				return 'lomRole:'.$a_query;
				
			case 'lom_role_entry':
				return 'lomRoleEntity:'.$a_query;
			// End contribute
	
			// Technical
			case 'lom_format':
				return 'lomFormat:'.$a_query;

			case 'lom_operating_system':
				return 'lomOS:'.$a_query;

			case 'lom_browser':
				return 'lomBrowser:'.$a_query;

			// Educational
			case 'lom_interactivity':
				return 'lomInteractivity:'.$a_query;

			case 'lom_resource':
				return 'lomResource:'.$a_query;
				
			case 'lom_level_start':
				$q_string = '';
				include_once './Services/MetaData/classes/class.ilMDUtilSelect.php';
				$options = ilMDUtilSelect::_getInteractivityLevelSelect(0,'lom_level',array(),true);
				for($i = $a_query; $i <= count($options); $i++)
				{
					if(strlen($q_string))
					{
						$q_string .= 'OR ';
					}
					$q_string .= ('lomLevel:"'.$options[$i].'" ');						
				}
				return $q_string;
				
			case 'lom_level_end':
				$q_string = '';
				include_once './Services/MetaData/classes/class.ilMDUtilSelect.php';
				$options = ilMDUtilSelect::_getInteractivityLevelSelect(0,'lom_level',array(),true);
				for($i = 1; $i <= $a_query; $i++)
				{
					if(strlen($q_string))
					{
						$q_string .= 'OR ';
					}
					$q_string .= ('lomLevel:"'.$options[$i].'" ');						
				}
				return $q_string;

			case 'lom_density_start':
				$q_string = '';
				include_once './Services/MetaData/classes/class.ilMDUtilSelect.php';
				$options = ilMDUtilSelect::_getSemanticDensitySelect(0,'lom_density',array(),true);
				for($i = $a_query; $i <= count($options); $i++)
				{
					if(strlen($q_string))
					{
						$q_string .= 'OR ';
					}
					$q_string .= ('lomDensity:"'.$options[$i].'" ');						
				}
				return $q_string;
				
			case 'lom_density_end':
				$q_string = '';
				include_once './Services/MetaData/classes/class.ilMDUtilSelect.php';
				$options = ilMDUtilSelect::_getSemanticDensitySelect(0,'lom_density',array(),true);
				for($i = 1; $i <= $a_query; $i++)
				{
					if(strlen($q_string))
					{
						$q_string .= 'OR ';
					}
					$q_string .= ('lomDensity:"'.$options[$i].'" ');
				}
				return $q_string;

			case 'lom_user_role':
				return 'lomUserRole:'.$a_query;

			case 'lom_context':
				return 'lomContext:'.$a_query;
			
			case 'lom_difficulty_start':
				$q_string = '';
				include_once './Services/MetaData/classes/class.ilMDUtilSelect.php';
				$options = ilMDUtilSelect::_getDifficultySelect(0,'lom_difficulty',array(),true);
				for($i = $a_query; $i <= count($options); $i++)
				{
					if(strlen($q_string))
					{
						$q_string .= 'OR ';
					}
					$q_string .= ('lomDifficulty:"'.$options[$i].'" ');						
				}
				return $q_string;
				
			case 'lom_difficulty_end':
				$q_string = '';
				include_once './Services/MetaData/classes/class.ilMDUtilSelect.php';
				$options = ilMDUtilSelect::_getDifficultySelect(0,'lom_difficulty',array(),true);
				for($i = 1; $i <= $a_query; $i++)
				{
					if(strlen($q_string))
					{
						$q_string .= 'OR ';
					}
					$q_string .= ('lomDifficulty:"'.$options[$i].'" ');
				}
				return $q_string;

			// Rights
			case 'lom_costs':
				return 'lomCosts:'.$a_query;

			case 'lom_copyright':
				return 'lomCopyright:'.$a_query;

			// Classification
			case 'lom_purpose':
				return 'lomPurpose:'.$a_query;

			case 'lom_taxon':
				return 'lomTaxon:'.$a_query;
				
			default:
				if(substr($a_field,0,3) != 'adv')
					break;
					
				// Advanced meta data
				$field_id = substr($a_field,4);
				include_once './Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php';
				$field = ilAdvancedMDFieldDefinition::_getInstanceByFieldId($field_id);
				
				switch($field->getFieldType())
				{
					case ilAdvancedMDFieldDefinition::TYPE_TEXT:
					case ilAdvancedMDFieldDefinition::TYPE_SELECT:
						return 'advancedMetaData_'.$field_id.':'.$a_query;
						
					case ilAdvancedMDFieldDefinition::TYPE_DATE:
						
						$value = $_POST['adv_'.$field_id.'_start'];
						$dt['year'] = (int) $value['date']['y'];
						$dt['mon'] = (int) $value['date']['m'];
						$dt['mday'] = (int) $value['date']['d'];
						$dt['hours'] = (int) 0;
						$dt['minutes'] = (int) 0;
						$dt['seconds'] = (int) 0;
						$date = new ilDate($dt,IL_CAL_FKT_GETDATE);
						$ustart = $date->get(IL_CAL_UNIX);
						
						$value = $_POST['adv_'.$field_id.'_end'];
						$dt['year'] = (int) $value['date']['y'];
						$dt['mon'] = (int) $value['date']['m'];
						$dt['mday'] = (int) $value['date']['d'];
						$dt['hours'] = (int) 0;
						$dt['minutes'] = (int) 0;
						$dt['seconds'] = (int) 0;
						$date = new ilDate($dt,IL_CAL_FKT_GETDATE);
						$uend = $date->get(IL_CAL_UNIX);
						
						return 'advancedMetaData_'.$field_id.':{'.$ustart.' TO '.$uend.'}';
						
					case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
						
						$value = $_POST['adv_'.$field_id.'_start'];
						$dt['year'] = (int) $value['date']['y'];
						$dt['mon'] = (int) $value['date']['m'];
						$dt['mday'] = (int) $value['date']['d'];
						$dt['hours'] = (int) $value['time']['h'];
						$dt['minutes'] = (int) $value['time']['m'];
						$dt['seconds'] = (int) 0;
						$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE);
						$ustart = $date->get(IL_CAL_UNIX);
						
						$value = $_POST['adv_'.$field_id.'_end'];
						$dt['year'] = (int) $value['date']['y'];
						$dt['mon'] = (int) $value['date']['m'];
						$dt['mday'] = (int) $value['date']['d'];
						$dt['hours'] = (int) $value['time']['h'];
						$dt['minutes'] = (int) $value['time']['m'];
						$dt['seconds'] = (int) 0;
						$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE);
						$uend = $date->get(IL_CAL_UNIX);
						
						return 'advancedMetaData_'.$field_id.':{'.$ustart.' TO '.$uend.'}';
						
	
				}
				break;
		}
	}
	
	
	/**
	 * Read active fields
	 */
	protected function readFields()
	{
		foreach(self::getFields() as $name => $translation)
		{
			if($this->settings->isActive($name))
			{
				$this->active_fields[$name] = $translation;
			}
		}
	}
	
	/**
	 * Read active sections
	 */
	protected function readSections()
	{
		foreach($this->getActiveFields() as $field_name => $translation)
		{
			switch($field_name)
			{
				// Default section
				case 'lom_content':
					$this->active_sections['default']['fields'][] = 'lom_content';
					$this->active_sections['default']['name'] = '';
					break;
				case 'lom_type':
					$this->active_sections['default']['fields'][] = 'lom_type';
					$this->active_sections['default']['name'] = '';
					break;
				
				// General	
				case 'lom_language':
					$this->active_sections['general']['fields'][] = 'lom_language';
					$this->active_sections['general']['name'] = $this->lng->txt('meta_general');
					break;
				case 'lom_keyword':
					$this->active_sections['general']['fields'][] = 'lom_keyword';
					$this->active_sections['general']['name'] = $this->lng->txt('meta_general');
					break;
				case 'lom_coverage':
					$this->active_sections['general']['fields'][] = 'lom_coverage';
					$this->active_sections['general']['name'] = $this->lng->txt('meta_general');
					break;
				case 'lom_structure':
					$this->active_sections['general']['fields'][] = 'lom_structure';
					$this->active_sections['general']['name'] = $this->lng->txt('meta_general');
					break;
					
				// Lifecycle
				case 'lom_status':
					$this->active_sections['lifecycle']['fields'][] = 'lom_status';
					$this->active_sections['lifecycle']['name'] = $this->lng->txt('meta_lifecycle');
					break;
				case 'lom_version':
					$this->active_sections['lifecycle']['fields'][] = 'lom_version';
					$this->active_sections['lifecycle']['name'] = $this->lng->txt('meta_lifecycle');
					break;
				case 'lom_contribute':
					$this->active_sections['lifecycle']['fields'][] = 'lom_contribute';
					$this->active_sections['lifecycle']['name'] = $this->lng->txt('meta_lifecycle');
					break;
					
				// Technical
				case 'lom_format':
					$this->active_sections['technical']['fields'][] = 'lom_format';
					$this->active_sections['technical']['name'] = $this->lng->txt('meta_technical');
					break;
				case 'lom_operating_system':
					$this->active_sections['technical']['fields'][] = 'lom_operating_system';
					$this->active_sections['technical']['name'] = $this->lng->txt('meta_technical');
					break;
				case 'lom_browser':
					$this->active_sections['technical']['fields'][] = 'lom_browser';
					$this->active_sections['technical']['name'] = $this->lng->txt('meta_technical');
					break;
					
				// Education
				case 'lom_interactivity':
					$this->active_sections['education']['fields'][] = 'lom_interactivity';
					$this->active_sections['education']['name'] = $this->lng->txt('meta_education');
					break;
				case 'lom_resource':
					$this->active_sections['education']['fields'][] = 'lom_resource';
					$this->active_sections['education']['name'] = $this->lng->txt('meta_education');
					break;
				case 'lom_level':
					$this->active_sections['education']['fields'][] = 'lom_level';
					$this->active_sections['education']['name'] = $this->lng->txt('meta_education');
					break;
				case 'lom_density':
					$this->active_sections['education']['fields'][] = 'lom_density';
					$this->active_sections['education']['name'] = $this->lng->txt('meta_education');
					break;
				case 'lom_user_role':
					$this->active_sections['education']['fields'][] = 'lom_user_role';
					$this->active_sections['education']['name'] = $this->lng->txt('meta_education');
					break;
				case 'lom_context':
					$this->active_sections['education']['fields'][] = 'lom_context';
					$this->active_sections['education']['name'] = $this->lng->txt('meta_education');
					break;
				case 'lom_difficulty':
					$this->active_sections['education']['fields'][] = 'lom_difficulty';
					$this->active_sections['education']['name'] = $this->lng->txt('meta_education');
					break;
					
				// Rights
				case 'lom_costs':
					$this->active_sections['rights']['fields'][] = 'lom_costs';
					$this->active_sections['rights']['name'] = $this->lng->txt('meta_rights');
					break;
				case 'lom_copyright':
					$this->active_sections['rights']['fields'][] = 'lom_copyright';
					$this->active_sections['rights']['name'] = $this->lng->txt('meta_rights');
					break;
				
				// Classification
				case 'lom_purpose':
					$this->active_sections['classification']['fields'][] = 'lom_purpose';
					$this->active_sections['classification']['name'] = $this->lng->txt('meta_classification');
					break;
				case 'lom_taxon':
					$this->active_sections['classification']['fields'][] = 'lom_taxon';
					$this->active_sections['classification']['name'] = $this->lng->txt('meta_classification');
					break;
					
				default:
					if(substr($field_name,0,3) != 'adv')
						break;

					// Advanced meta data
					$field_id = substr($field_name,4);
					include_once './Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php';
					include_once './Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php';
					$field = ilAdvancedMDFieldDefinition::_getInstanceByFieldId($field_id);
					$record_id = $field->getRecordId();
					
					$this->active_sections['adv_record_'.$record_id]['fields'][] = $field_name;
					$this->active_sections['adv_record_'.$record_id]['name'] = ilAdvancedMDRecord::_lookupTitle($record_id);
					break;
			}
		}
	}
	
	/**
	 * get a range selection
	 */
	protected function getRangeSelect($txt_from,$select_from,$txt_until,$select_until)
	{
		$tpl = new ilTemplate('tpl.range_search.html',true,true,'Services/Search');
		$tpl->setVariable('TXT_FROM',$txt_from);
		$tpl->setVariable('FROM',$select_from);
		$tpl->setVariable('TXT_UPTO',$txt_until);
		$tpl->setVariable('UPTO',$select_until);
		return $tpl->get();		
	}

}
?>
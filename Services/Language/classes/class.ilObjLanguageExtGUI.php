<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

define("ILIAS_LANGUAGE_MODULE", "Services/Language");

require_once("classes/class.ilObjectGUI.php");
require_once("Services/Language/classes/class.ilObjLanguageAccess.php");


/**
* Class ilObjLanguageExtGUI
*
* This class is a replacement for ilObjLanguageGUI
* which is currently not used in ILIAS.
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilObjLanguageExtGUI.php $
*
* @ilCtrl_Calls ilObjLanguageExtGUI:
* @ilCtrl_IsCalledBy ilObjLanguageExtGUI: ilPersonalDesktopGUI
*
* @package ilias-core
*/
class ilObjLanguageExtGUI extends ilObjectGUI
{
	/**
	* Current ILIAS module
	*
	* @var		string
	* @access	private
	*/
	var $module = ILIAS_LANGUAGE_MODULE;


	/**
	* Size of input fields
	*
	* @var		integer
	* @access	private
	*/
	var $inputsize = 40;


	/**
	* Constructor
	*
	* Note:
	* The GET param 'obj_id' is the language object id
	* The GET param 'ref_id' is the language folder (if present)
	*
	* @param    mixed       data (ignored)
	* @param    int         id (ignored)
	* @param    boolean     call-by-reference (ignored)
	*/
	function ilObjLanguageExtGUI($a_data, $a_id = 0, $a_call_by_reference = false)
	{
		global $lng, $ilCtrl;

		// language maintenance strings are defined in administration
        $lng->loadLanguageModule("administration");
        $lng->loadLanguageModule("meta");

		//  view mode ('translate' or empty) needed for prepareOutput()
		$ilCtrl->saveParameter($this, "view_mode");

		// type and id of get the bound object
		$this->type = "lng";
		if (! $this->id = $_GET['obj_id'])
		{
			$this->id = ilObjLanguageAccess::_lookupId($lng->getUserLanguage());
		}
		
		// do all generic GUI initialisations
		$this->ilObjectGUI($a_data, $this->id, false, true);
		
		// initialize the array to store GUI session variables
		if (!is_array($_SESSION[get_class($this)]))
		{
			$_SESSION[get_class($this)] = array();
		}
		$this->session =& $_SESSION[get_class($this)];
	}


	/**
	* Assign the extended language object
	*
	* Overwritten from ilObjectGUI to use the extended language object.
	* (Will be deleted when ilObjLanguageExt is merged with ilObjLanguage)
	*/
	function assignObject()
	{
		require_once("Services/Language/classes/class.ilObjLanguageExt.php");
		$this->object =& new ilObjLanguageExt($this->id);
	}


	/**
	* execute command
	*/
	function &executeCommand()
	{
		if (!ilObjLanguageAccess::_checkTranslate())
		{
             $this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}
		
 		$cmd = $this->ctrl->getCmd("view")."Object";
		$this->$cmd();
		exit;
	}

	
	/**
	* Cancel the current action
	*/
	function cancelObject()
	{
		ilUtil::sendInfo($this->lng->txt("action_aborted"), false);
		$this->viewObject();
	}

	/**
	* Show the edit screen
	*/
	function viewObject()
	{
		global $ilUser;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lang_edit_items.html", $this->module);

		// set the language to compare with
        $compare = $this->getPar('compare', $this->lng->getDefaultLanguage());
		if ($compare == $this->object->key)
		{
			$defaults_read = true;
			$this->object->readLanguageFile();
			$compare_content = $this->object->getLangFileContent();
			$compare_comments = $this->object->getLangFileComments();
			$compare_note = " ". $this->lng->txt("language_default_entries");
		}

		// page translation mode:
		// - the table is filtered by a list of modules and topics
		// - all found entries are shown on the same page
		if ($this->_isPageTranslation())
		{
			$offset = 0;
			$limit = 0;

			$modules = $this->getPar("page_modules", array());
			$topics = $this->getPar("page_topics", array());

			if (!isset($compare_content))
			{
				$compare_content = $this->object->_getTranslations(
									$compare, $modules, $topics);
			}

			$translations = $this->object->_getTranslations(
							$this->object->key,
							$modules, $topics);
		}
		// normal view mode:
		// - the table is filtered manually by module, mode and pattern
		// - found entries are paged by maximum list length
		// - the filter selection is shown
		else
		{
			$offset = $this->getPar('offset','0');
			$limit = $ilUser->getPref("hits_per_page");

			$filter_mode = $this->getPar('filter_mode','all');
			$filter_pattern = $this->getPar('filter_pattern','');
			$filter_module = $this->getPar('filter_module','administration');
			$filter_modules = $filter_module ? array($filter_module) : array();

			if (!isset($compare_content))
			{
				$compare_content = $this->object->_getTranslations(
				            		$compare, $filter_modules);
			}

			switch ($filter_mode)
			{
				case "changed":
				    if (!$defaults_read)
				    {
				    	$this->object->readLanguageFile();
				    }
				    
					$translations = $this->object->getChangedTranslations(
					        		$filter_modules, $filter_pattern);
					break;
					            
				case "unchanged":
				    if (!$defaults_read)
				    {
				    	$this->object->readLanguageFile();
				    }

					$translations = $this->object->getUnchangedTranslations(
					            	$filter_modules, $filter_pattern);
					break;
					
				case "commented":
				    if (!$defaults_read)
				    {
				    	$this->object->readLanguageFile();
				    }
                    $translations = $this->object->getCommentedTranslations(
					            	$filter_modules, $filter_pattern);
					break;

				case "equal":
                    $translations = $this->object->getAllTranslations(
					            	$filter_modules, $filter_pattern);

					$translations = array_intersect_assoc($translations, $compare_content);
					break;

				case "different":
                    $translations = $this->object->getAllTranslations(
					            	$filter_modules, $filter_pattern);

					$translations = array_diff_assoc($translations, $compare_content);
					break;

				case "all":
				default:
				
					$translations = $this->object->getAllTranslations(
					            	$filter_modules, $filter_pattern);
			}

			// show the filter section
			$this->tpl->setCurrentBlock("filter");

			// filter by language module
			$options = array();
			$options[""] = $this->lng->txt("language_all_modules");
			$modules = $this->object->_getModules($this->object->key);
			foreach ($modules as $mod)
			{
				$options[$mod] = $mod;
			}
			$this->tpl->setVariable("SELECT_MODULE",
   				ilUtil::formSelect($filter_module, "filter_module", $options, false, true));
   				
			// filter by mode
			$options = array();
			$options["all"] = $this->lng->txt("language_scope_global");
			$options["changed"] = $this->lng->txt("language_scope_local");
			$options["unchanged"] = $this->lng->txt("language_scope_unchanged");
			$options["equal"] = $this->lng->txt("language_scope_equal");
			$options["different"] = $this->lng->txt("language_scope_different");
			$options["commented"] = $this->lng->txt("language_scope_commented");
			$this->tpl->setVariable("SELECT_MODE",
   				ilUtil::formSelect($filter_mode, "filter_mode", $options, false, true));
			
			// filter by pattern
			$this->tpl->setVariable("PATTERN_NAME", "filter_pattern");
			$this->tpl->setVariable("PATTERN_VALUE", ilUtil::prepareFormOutput($filter_pattern));

			// and general filter variables
			$this->tpl->setVariable("FILTER_ACTION", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_FILTER", $this->lng->txt("filter"));
			$this->tpl->setVariable("OFFSET_NAME", "offset");
			$this->tpl->setVariable("OFFSET_VALUE", "0");
			$this->tpl->setVariable("TXT_APPLY_FILTER", $this->lng->txt("apply_filter"));
			$this->tpl->setVariable("CMD_FILTER", "view");
			$this->tpl->parseCurrentBlock();
		}
		
		// show the compare section
		$this->tpl->setCurrentBlock("compare");
		$this->tpl->setVariable("COMPARE_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_COMPARE", $this->lng->txt("language_compare"));
		$this->tpl->setVariable("TXT_CHANGE", $this->lng->txt("change"));
		$options = array();
		$langlist = $this->lng->getInstalledLanguages();
		foreach ($langlist as $lang_key)
		{
          	$options[$lang_key] = $this->lng->txt("meta_l_".$lang_key);
		}
		$this->tpl->setVariable("SELECT_COMPARE",
			ilUtil::formSelect($compare, "compare", $options, false, true,1));
		$this->tpl->setVariable("CMD_COMPARE", "view");
		$this->tpl->parseCurrentBlock();

		// prepare the dataset for the output table
		$sort_by = $this->getPar('sort_by','translation');
		$sort_order = $this->getPar('sort_order','asc');

		$list = array();
		foreach($translations as $name => $translation)
		{
			$keys = explode("#:#", $name);
			$data = array();
			
			$data["module"] = $keys[0];
			$data["topic"] = str_replace('_', ' ', $keys[1]);
			$data["name"] = $name;
			$data["translation"] = $translation;
			$data["default"] = $compare_content[$name];
			$data["comment"] = $compare_comments[$name];

			$list[] = $data;
		}
		$list = ilUtil::sortArray($list, $sort_by, $sort_order);
		if ($limit > 0)
		{
			$list = array_slice($list, $offset, $limit);
		}

		// create and configure the table object
		include_once 'Services/Table/classes/class.ilTableGUI.php';
		$tbl = new ilTableGUI();

		$tbl->disable('title');
 
		$tbl->setHeaderNames(array($this->lng->txt("module"),
								   $this->lng->txt("identifier"),
								   $this->lng->txt("meta_l_".$this->object->key),
								   $this->lng->txt("meta_l_".$compare).$compare_note));

		$tbl->setHeaderVars(array("module",
								  "topic",
								  "translation",
								  "default"),
							$this->ctrl->getParameterArray($this));


		$tbl->setColumnWidth(array( "10%",
								  	"20%",
								  	"40%",
								  	"30%"));

		$tbl->setOrderColumn($sort_by);
		$tbl->setOrderDirection($sort_order);
		$tbl->setLimit($limit);
		$tbl->setOffset($offset);
		$tbl->setMaxCount(count($translations));


		// prepare the table template
		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",'save');
		$tpl->setVariable("BTN_VALUE",$this->lng->txt('save'));
		$tpl->parseCurrentBlock();
 
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS","4");
		$tpl->parseCurrentBlock();

		// render the table rows
        $tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.lang_items_row.html",$this->module);
		foreach ($list as $data)
		{
			if (strlen($data["translation"]) <= $this->inputsize)
			{
				$tpl->setCurrentBlock("input");
				$tpl->setVariable("I_NAME", ilUtil::prepareFormOutput($data["name"]));
				$tpl->setVariable("I_SIZE", $this->inputsize);
				$tpl->setVariable("I_USER_VALUE", ilUtil::prepareFormOutput($data["translation"]));
			}
			else
			{
				$tpl->setCurrentBlock("textarea");
				$tpl->setVariable("T_ROWS", ceil(strlen($data["translation"]) / $this->inputsize));
				$tpl->setVariable("T_SIZE", $this->inputsize);
				$tpl->setVariable("T_NAME", ilUtil::prepareFormOutput($data["name"]));
				$tpl->setVariable("T_USER_VALUE", ilUtil::prepareFormOutput($data["translation"]));
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("row");
			$tpl->setVariable("MODULE", ilUtil::prepareFormOutput($data["module"]));
			$tpl->setVariable("TOPIC", ilUtil::prepareFormOutput($data["topic"]));
			$tpl->setVariable("DEFAULT_VALUE", ilUtil::prepareFormOutput($data["default"]));
			$tpl->setVariable("COMMENT", ilUtil::prepareFormOutput($data["comment"]));
			$tpl->parseCurrentBlock();
		}

		// render and show the table
		$tbl->setTemplate($tpl);
		$tbl->render();
		$this->tpl->setVariable("TRANSLATION_TABLE", $tpl->get());
		$this->tpl->show();
	}
	

	/**
	* Save the changed translations
	*/
	function saveObject()
	{
		// permission check
		if (!ilObjLanguageAccess::_checkTranslate())
		{
             $this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		// prepare the values to be saved
		$save_array = array();
		foreach ($_POST as $key => $value)
		{
			$keys = explode("#:#", ilUtil::stripSlashes($key, false));
			if (count($keys) == 2)
			{
				// avoid line breaks
		  		$value = preg_replace("/(\015\012)|(\015)|(\012)/","<br />",$value);
		  		$value = ilUtil::stripSlashes($value, false);
				$save_array[$key] = $value;
			}
		}
		
		// save the translations
		$this->object->_saveTranslations($this->object->key, $save_array);

		// view the list
		$this->viewObject();
	}
	

	/**
	* Show the screen to import a language file
	*/
	function importObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lang_file_import.html", $this->module);

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("language_import_file"));
		$this->tpl->setVariable("TXT_FILE",$this->lng->txt("file"));
		
		$this->tpl->setVariable("TXT_MODE",$this->lng->txt("language_mode_existing"));
		$this->tpl->setVariable("TXT_MODE_KEEPALL",$this->lng->txt("language_mode_existing_keepall"));
		$this->tpl->setVariable("TXT_MODE_KEEPNEW",$this->lng->txt("language_mode_existing_keepnew"));
		$this->tpl->setVariable("TXT_MODE_REPLACE",$this->lng->txt("language_mode_existing_replace"));
		$this->tpl->setVariable("TXT_MODE_DELETE",$this->lng->txt("language_mode_existing_delete"));

		$this->tpl->setVariable("TXT_UPLOAD",$this->lng->txt("upload"));
		$this->tpl->setVariable("CMD_UPLOAD","upload");
		$this->tpl->show();
	}
	
	
	/**
	* Process an uploaded language file
	*/
	function uploadObject()
	{
		// permission check
		if (!ilObjLanguageAccess::_checkMaintenance())
		{
             $this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		if ($_POST['cmd']['upload'])
		{
			$file = $_FILES['userfile']['tmp_name'].'x';
			
			if (ilUtil::moveUploadedFile($_FILES['userfile']['tmp_name'],
									 	 $_FILES['userfile']['name'],
									 	 $file))
			{
				$this->object->importLanguageFile($file,$_POST['mode_existing']);
				ilUtil::sendInfo(sprintf($this->lng->txt("language_file_imported"), $_FILES['userfile']['name']) , false);
				$this->importObject();
			}
			else
			{
				$this->importObject();
			}
		}
		else
		{
			$this->cancelObject();
		}
	}

	
	/**
	* Show the screen to export a language file
	*/
	function exportObject()
	{
		$scope = $_POST["scope"] ? $_POST["scope"] : "global";
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lang_file_export.html", $this->module);

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("language_export_file"));

		$this->tpl->setVariable("TXT_SCOPE",$this->lng->txt("language_file_scope"));
		$this->tpl->setVariable("TXT_SCOPE_GLOBAL",$this->lng->txt("language_scope_global"));
		$this->tpl->setVariable("TXT_SCOPE_LOCAL",$this->lng->txt("language_scope_local"));
		$this->tpl->setVariable("TXT_SCOPE_UNCHANGED",$this->lng->txt("language_scope_unchanged"));
		$this->tpl->setVariable("TXT_SCOPE_TRANSLATE",$this->lng->txt("language_scope_translate"));

		$this->tpl->setVariable("CHECKED_GLOBAL",$scope == 'global' ? 'checked="checked"' : '');
		$this->tpl->setVariable("CHECKED_LOCAL",$scope == 'local' ? 'checked="checked"' : '');
		$this->tpl->setVariable("CHECKED_UNCHANGED",$scope == 'unchanged' ? 'checked="checked"' : '');
		$this->tpl->setVariable("CHECKED_TRANSLATE",$scope == 'translate' ? 'checked="checked"' : '');

		$this->tpl->setVariable("TXT_DOWNLOAD",$this->lng->txt("download"));
		$this->tpl->setVariable("CMD_DOWNLOAD","download");
		$this->tpl->show();
	}

	
	/**
	* Download a language file
	*/
	function downloadObject()
	{
		global $ilUser;
		
		$this->object->readLanguageFile();
        $tpl = new ilTemplate("tpl.lang_file_header.html",true,true, $this->module);

		$version = "_"
			. str_replace(".", "_", substr(ILIAS_VERSION, 0, strpos(ILIAS_VERSION, " ")))
        	. "-" . date('Y-m-d');
        
		if ($_POST["scope"] == 'global')
		{
			$translations = $this->object->getAllTranslations();
			$filename = 'ilias_' . $this->object->key . $version . '.lang';

			$tpl->setCurrentBlock('global');
			$tpl->setVariable('AUTHOR', $this->object->getLangFileParam('author'));
			$tpl->setVariable('VERSION', $this->object->getLangFileParam('version'));
			$tpl->parseCurrentBlock();
			$tpl->setVariable('SCOPE', $this->lng->txtlng('administration','language_scope_global','en'));
		}
		elseif ($_POST["scope"] == 'local')
		{
			$translations = $this->object->getChangedTranslations();
			$filename = 'ilias_' . $this->object->key . $version . '.lang.local';

			$tpl->setCurrentBlock('local');
			$tpl->setVariable('BASED_ON', $this->object->getLangFileParam('version'));
			$tpl->parseCurrentBlock();
			$tpl->setVariable('SCOPE', $this->lng->txtlng('administration','language_scope_local','en'));
		}
		elseif ($_POST["scope"] == 'unchanged')
		{
			$translations = $this->object->getUnchangedTranslations();
			$filename = 'ilias_' . $this->object->key . $version . '.lang.unchanged';

			$tpl->setCurrentBlock('global');
			$tpl->setVariable('AUTHOR', $this->object->getLangFileParam('author'));
			$tpl->setVariable('VERSION', $this->object->getLangFileParam('version'));
			$tpl->parseCurrentBlock();
			$tpl->setVariable('SCOPE', $this->lng->txtlng('administration','language_scope_unchanged','en'));

			$tpl->parseCurrentBlock();
		}
		elseif ($_POST["scope"] == 'translate')
		{
			$translations = $this->object->getAllTranslations();
			$filename = 'ilias_' . $this->object->key . $version .  '.lang';

			$tpl->setCurrentBlock('global');
			$tpl->setVariable('AUTHOR', $this->object->getLangFileParam('author'));
			$tpl->setVariable('VERSION', $this->object->getLangFileParam('version'));
			$tpl->parseCurrentBlock();
			$tpl->setVariable('SCOPE', $this->lng->txtlng('administration','language_scope_translate','en'));
		}


		$tpl->setVariable('LANGUAGE', $this->lng->txtlng('common','lang_'.$this->object->key,'en'));
		$tpl->setVariable('ILIAS_HTTP_PATH',ILIAS_HTTP_PATH);
		$tpl->setVariable('ILIAS_VERSION',ILIAS_VERSION);
		$tpl->setVariable('CREATE_DATE',date('Y-m-d H:i:s'));
		$tpl->setVariable('USER',$ilUser->getFullname());
		$tpl->setVariable('EMAIL',$ilUser->getEmail());
		
		$data = $tpl->get();
		
		if ($_POST["scope"] == 'translate')
		{
			foreach ($translations as $key => $value)
			{
				$data .= $key . $this->lng->separator . "\n";
			}
		}
		else
		{
			foreach ($translations as $key => $value)
			{
				$data .= $key . $this->lng->separator . $value . "\n";
			}
		}
		ilUtil::deliverData($data, $filename);
	}


	/**
	* Process the language maintenance
	*/
	function maintainObject()
	{
		global $ilUser;
		
		// permission check
		if (!ilObjLanguageAccess::_checkMaintenance())
		{
             $this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		switch ($_POST["maintain"])
		{
			// save the local changes to the local language file
			case "save":
				$lang_file = $this->object->getCustLangPath() . '/ilias_' . $this->object->key . '.lang.local';

				if ((is_file($lang_file) and is_writable($lang_file))
				or (!file_exists($lang_file) and is_writable($this->object->getCustLangPath())))
				{
					$this->object->readLanguageFile();
					$translations = $this->object->getChangedTranslations();

	        		$tpl = new ilTemplate("tpl.lang_file_header.html",true,true, $this->module);
					$tpl->setCurrentBlock('local');
					$tpl->setVariable('BASED_ON', $this->object->getLangFileParam('version'));
					$tpl->parseCurrentBlock();
					$tpl->setVariable('SCOPE', $this->lng->txtlng('administration','language_scope_local','en'));
					$tpl->setVariable('LANGUAGE', $this->lng->txtlng('common','lang_'.$this->object->key,'en'));
					$tpl->setVariable('ILIAS_HTTP_PATH',ILIAS_HTTP_PATH);
					$tpl->setVariable('ILIAS_VERSION',ILIAS_VERSION);
					$tpl->setVariable('CREATE_DATE',date('Y-m-d H:i:s'));
					$tpl->setVariable('USER',$ilUser->getFullname());
					$tpl->setVariable('EMAIL',$ilUser->getEmail());

					@rename($lang_file, $lang_file.".bak");
					$fp = fopen($lang_file, "w");
					fwrite($fp, $tpl->get());
					foreach ($translations as $key => $value)
					{
						fwrite($fp, $key . $this->lng->separator . $value . "\n");
					}
					fclose($fp);
					$this->object->setLocal(true);
					ilUtil::sendInfo($this->lng->txt("language_saved_local") , false);
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("language_error_write_local") , false);
				}
				break;
				
			// load the content of the local language file
			case "load":
				$lang_file = $this->object->getCustLangPath() . '/ilias_' . $this->object->key . '.lang.local';
			    if (is_file($lang_file) and is_readable($lang_file))
			    {
					$this->object->importLanguageFile($lang_file, 'replace');
					$this->object->setLocal(true);
					ilUtil::sendInfo($this->lng->txt("language_loaded_local") , false);
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("language_error_read_local") , false);
				}
				break;

			// revert the database to the default language file
			case "clear":
			    $lang_file = $this->object->getLangPath() . '/ilias_' . $this->object->key . '.lang';
			    if (is_file($lang_file) and is_readable($lang_file))
			    {
					$this->object->importLanguageFile($lang_file, 'delete');
					$this->object->setLocal(false);
					ilUtil::sendInfo($this->lng->txt("language_cleared_local") , false);
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("language_error_clear_local") , false);
				}
				break;
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lang_maintenance.html", $this->module);
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_MAINTENANCE",$this->lng->txt("language_maintenance"));
		$this->tpl->setVariable("TXT_MAINTAIN_LOCAL",$this->lng->txt("language_maintain_local_changes"));
		$this->tpl->setVariable("TXT_SELECT",$this->lng->txt("please_select"));
		$this->tpl->setVariable("TXT_SAVE",$this->lng->txt("language_save_local_changes"));
		$this->tpl->setVariable("TXT_LOAD",$this->lng->txt("language_load_local_changes"));
		$this->tpl->setVariable("TXT_CLEAR",$this->lng->txt("language_clear_local_changes"));
		$this->tpl->setVariable("TXT_NOTE_SAVE",$this->lng->txt("language_note_save_local"));
		$this->tpl->setVariable("TXT_MAINTAIN",$this->lng->txt("language_process_maintenance"));
		$this->tpl->setVariable("VAR_MAINTAIN", "maintain");
		$this->tpl->setVariable("CMD_MAINTAIN", "maintain");
		$this->tpl->show();
	}

	/**
	* Set the language settings
	*/
	function settingsObject()
	{
		global $ilSetting;

		// permission check
		if (!ilObjLanguageAccess::_checkMaintenance())
		{
             $this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		$translate_key = "lang_translate_". $this->object->key;

		// save and get the page translation setting
		switch ($_POST["translation"])
		{
			case "enable":
				$ilSetting->set($translate_key, true);
				break;
			case "disable":
				$ilSetting->set($translate_key, false);
		}
		$translate = $ilSetting->get($translate_key, false);
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lang_settings.html", $this->module);

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_SETTINGS",$this->lng->txt("language_settings"));
		$this->tpl->setVariable("TXT_TRANSLATION",$this->lng->txt("language_translation_mode"));
		$this->tpl->setVariable("TXT_TRANSLATION_ENABLED",$this->lng->txt("language_translation_enabled"));
		$this->tpl->setVariable("TXT_TRANSLATION_DISABLED",$this->lng->txt("language_translation_disabled"));
		$this->tpl->setVariable("CHECKED_ENABLE", $translate ? 'checked="checked"': '');
		$this->tpl->setVariable("CHECKED_DISABLE", $translate ? '' : 'checked="checked"');
		$this->tpl->setVariable("TXT_NOTE_TRANSLATION",$this->lng->txt("language_note_translation"));
		$this->tpl->setVariable("TXT_CHANGE_SETTINGS",$this->lng->txt("language_change_settings"));
		$this->tpl->setVariable("CMD_SETTINGS", "settings");
		$this->tpl->show();
	}

	/**
	* Print out statistics about the language
	*/
	function statisticsObject()
	{
		$this->object->readLanguageFile();
		$modules = $this->object->_getModules($this->object->key);
		
		$data = array();
		$total = array("",0,0,0);
		foreach($modules as $module)
		{
			$row = array();
			$row[0] = $module;
			$row[1] = count($this->object->getAllTranslations(array($module)));
			$row[2] = count($this->object->getChangedTranslations(array($module)));
			$row[3] = count($this->object->getUnchangedTranslations(array($module)));
			$total[1] += $row[1];
			$total[2] += $row[2];
			$total[3] += $row[3];
			$data[] = $row;
		}
		$total[0] = "<b>".$this->lng->txt("language_all_modules")."</b>";
		$total[1] = "<b>".$total[1]."</b>";
		$total[2] = "<b>".$total[2]."</b>";
		$total[3] = "<b>".$total[3]."</b>";
		$data[] = $total;

		// prepare the templates for output
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.lang_statistics.html", $this->module);
		$this->tpl->addBlockFile("TABLE_STATISTICS", "table_statistics", "tpl.table.html");
		$this->tpl->addBlockFile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		// create and configure the table object
		include_once 'Services/Table/classes/class.ilTableGUI.php';
		$tbl = new ilTableGUI();
		$tbl->disable('title');
		$tbl->disable('sort');
		$tbl->disable('numinfo');
		
		$tbl->setHeaderNames(array($this->lng->txt("module"),
								   $this->lng->txt("language_scope_global"),
								   $this->lng->txt("language_scope_local"),
								   $this->lng->txt("language_scope_unchanged")));
		$tbl->setColumnWidth(array( "25%", "25%", "25%", "25%"));
		$tbl->setLimit(count($data));
		$tbl->setData($data);
		
		// show the table
		$tbl->render();
		$this->tpl->show();
	}
	

	/**
	* Read a param that is either coming from GET, POST
	* or is taken from the session variables of this GUI.
	* A request value is automatically saved in the session variables.
	* Slashes are stripped from request values.
	*
	* @param    string      name of the GET or POST or variable
	* @param    mixed       default value
	*/
	function getPar($a_request_name, $a_default_value)
	{
		// get the parameter value
		if (isset($_GET[$a_request_name]))
		{
			$param = $_GET[$a_request_name];
			$from_request = true;
		}
		elseif (isset($_POST[$a_request_name]))
		{
			$param = $_POST[$a_request_name];
			$from_request = true;
		}
		elseif (isset($this->session[$a_request_name]))
		{
			$param = $this->session[$a_request_name];
			$from_request = false;
		}
		else
		{
			$param = $a_default_value;
			$from_request = false;
		}
		
		// strip slashes from request parameters
		if ($from_request)
		{
			if (is_array($param))
			{
				foreach ($param as $key => $value)
				{
					$param[$key] = ilUtil::stripSlashes($value);
				}
			}
			else
			{
				$param = ilUtil::stripSlashes($param);
			}
		}
		
		// make the parameter available to further requests
		$this->session[$a_request_name] = $param;

		return $param;
	}

	/**
	* Get tabs for admin mode
	* @param	object	tabs gui object
	*/
	function getAdminTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$tabs_gui->addTarget("edit",
			$this->ctrl->getLinkTarget($this, "view"),
			array("","view","cancel","save"));

		$tabs_gui->addTarget("export",
			$this->ctrl->getLinkTarget($this, "export"),
			array("export","download"));

		if (ilObjLanguageAccess::_checkMaintenance())
		{
			$tabs_gui->addTarget("import",
				$this->ctrl->getLinkTarget($this, "import"),
				array("import","upload"));

			$tabs_gui->addTarget("language_maintain",
				$this->ctrl->getLinkTarget($this, "maintain"),
				array("maintain"));

			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "settings"),
				array("settings"));
		}
		
		$tabs_gui->addTarget("language_statistics",
			$this->ctrl->getLinkTarget($this, "statistics"),
			array("statistics"));
	}


	/**
	* Prepare the standard template for output
	* (Overwritten from ilObjectGUI)
	*/
	function prepareOutput()
	{
		if ($this->_isPageTranslation())
		{
			// show the pure translation page without menu, tabs etc.
			$this->tpl->addBlockFile("CONTENT","content","tpl.adm_translate.html",$this->module);
			$this->tpl->setHeaderPageTitle($this->lng->txt("translation"));
			$this->tpl->setTitle($this->lng->txt("translation"). " "
									.$this->lng->txt("meta_l_".$this->object->key));
			$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lng_b.gif"),
									 $this->lng->txt("obj_" . $this->object->getType()));
		}
		else
		{
			// show the full page framework
			parent::prepareOutput();
		}
	}
	
	
	/**
	* Set the locator for admin mode
	* (called from prepareOutput in parent class)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator, $tpl;

		$ilLocator->addItem($this->lng->txt("administration"),
			$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
			ilFrameTargetInfo::_getFrame("MainContent"));

		$ilLocator->addItem($this->lng->txt("languages"),
			$this->ctrl->getLinkTargetByClass("ilobjlanguagefoldergui", ""));

		$ilLocator->addItem($this->lng->txt("meta_l_". $this->object->getTitle()),
			$this->ctrl->getLinkTarget($this, "view"));
	}


	/**
	* Set the Title and the description
	* (Overwritten from ilObjectGUI, called by prepare output)
	*/
	function setTitleAndDescription()
	{
		$this->tpl->setTitle($this->lng->txt("meta_l_".$this->object->getTitle()));
		// $this->tpl->setDescription($this->object->getLongDescription());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif"), $this->lng->txt("obj_" . $this->object->getType()));
	}


	//
	// STATIC FUNCTIONS
	//

	/**
	* Check if the GUI is in page translation mode
	*
	* The page translation mode is used when the translation
	* of a single page is called by the translation link on a page footer.
	* The page translation screen is shown in a separate window. On this screen
	* only the topics used on the calling page are shown for translation
	* and only a save function for these topics is offered.
	*
	* @access   static
	* @return   bool      page translation (true or false)
	*/
	function _isPageTranslation()
	{
		return ($_GET['view_mode'] == "translate");
	}


	/**
	* Get the HTML code for calling the page translation
	*
	* Returns a hidden form with a link to the translation screen
	* The HTML code can be added to the footer of every ILIAS page
	*
	* @access   static
	*/
	function _getTranslationLink()
	{
		global $ilSetting, $lng;

		// prevent translation link on translation screen
		// check setting of translation mode
		if (ilObjLanguageExtGUI::_isPageTranslation()
		or !$ilSetting->get("lang_translate_".$lng->getLangKey()))
		{
			return "";
		}

		// set the target for translation
		// ref id must be given to prevent params being deleted by ilAdministrtionGUI
		$action = "ilias.php"
			."?ref_id=".ilobjLanguageAccess::_lookupLangFolderRefId()
			."&baseClass=ilAdministrationGUI"
			."&cmdClass=ilobjlanguageextgui"
			."&view_mode=translate";

		$tpl = new ilTemplate("tpl.translation_link.html",true,true, ILIAS_LANGUAGE_MODULE);

		foreach($lng->getUsedModules() as $module => $dummy)
		{
			$tpl->setCurrentBlock("hidden");
			$tpl->setVariable("NAME", "page_modules[]");
			$tpl->setVariable("VALUE", ilUtil::prepareFormOutput($module));
			$tpl->parseCurrentBlock();
		}

		foreach($lng->getUsedTopics() as $topic => $dummy)
		{
			$tpl->setCurrentBlock("hidden");
			$tpl->setVariable("NAME", "page_topics[]");
			$tpl->setVariable("VALUE", ilUtil::prepareFormOutput($topic));
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("ACTION", $action);
		$tpl->setVariable("TXT_TRANSLATE",$lng->txt("translation"));

		return $tpl->get();
	}

	
} // END class.ilObjLanguageExtGUI
?>

<?php
/* Copyright (c) 1998-20014 ILIAS open source, Extended GPL, see docs/LICENSE */

define("ILIAS_LANGUAGE_MODULE", "Services/Language");

require_once("./Services/Object/classes/class.ilObjectGUI.php");
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
* @ingroup ServicesLanguage
*/
class ilObjLanguageExtGUI extends ilObjectGUI
{
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
		global $lng, $ilCtrl, $ilClientIniFile;

		// language maintenance strings are defined in administration
        $lng->loadLanguageModule("administration");
        $lng->loadLanguageModule("meta");

		//  view mode ('translate' or empty) determins available table filters
		$ilCtrl->saveParameter($this, "view_mode");

		// type and id of get the bound object
		$this->type = "lng";
		if (! $this->id = $_GET['obj_id'])
		{
			$this->id = ilObjLanguageAccess::_lookupId($lng->getUserLanguage());
		}
		
		// do all generic GUI initialisations
		$this->ilObjectGUI($a_data, $this->id, false, true);
		
		// initialize the array to store session variables for extended language maintenance
		if (!is_array($_SESSION['lang_ext_maintenance']))
		{
			$_SESSION['lang_ext_maintenance'] = array();
		}
		$this->session =& $_SESSION['lang_ext_maintenance'];


		// read the lang mode
        $this->langmode = $ilClientIniFile->readVariable("system","LANGMODE");
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
     * get the language object id (needed for filter serialization)
     * @return int  language object id
     */
    function getId()
    {
        return $this->id;
    }

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilHelp;
		
		if (!ilObjLanguageAccess::_checkMaintenance())
		{
             $this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
             exit;
		}
		
 		$cmd = $this->ctrl->getCmd("view")."Object";
		$this->$cmd();
		
		$ilHelp->setScreenIdComponent("lng");
	}

	
	/**
	* Cancel the current action
	*/
	function cancelObject()
	{
		$this->viewObject();
	}

    /**
     * Get the table to view language entries
     *
     * @return ilLanguageExtTableGUI
     */
    protected function getViewTable()
    {
        // create and configure the table object
        include_once './Services/Language/classes/class.ilLanguageExtTableGUI.php';
        $table_gui = new ilLanguageExtTableGUI($this, 'view', array(
            'langmode' => $this->langmode,
            'lang_key'=> $this->object->key,
        ));

        return $table_gui;
    }

    /**
	* Show the edit screen
	*/
	function viewObject()
	{
        global $tpl;

        // get the view table
        $table_gui = $this->getViewTable();

        // get the remarks in database
        $comments = $this->object->getAllRemarks();

        // set the language to compare with
        // get the default values if the compare language is the same
        $compare = $table_gui->getFilterItemByPostVar('compare')->getValue();
   		if ($compare == $this->object->key)
		{
			$compare_object = $this->object->getGlobalLanguageFile();
			$compare_content = $compare_object->getAllValues();
			$compare_comments = $compare_object->getAllComments();
		}

        // page translation mode:
		// - the table is filtered by a list of modules and topics
		if (ilObjLanguageAccess::_isPageTranslation())
		{
            // get the selection of modules and topics from request or session
			$modules = ilObjLanguageAccess::_getSavedModules();
			$topics = ilObjLanguageAccess::_getSavedTopics();

            // first call for translation
            if ($_GET['reset_offset'])
            {
                $table_gui->resetOffset();
            }

			if (!isset($compare_content))
			{
				$compare_content = ilObjLanguageExt::_getValues(
									$compare, $modules, $topics);

				$compare_comments = ilObjLanguageExt::_getRemarks($compare);
			}

			$translations = ilObjLanguageExt::_getValues(
							$this->object->key, $modules, $topics);
		}
		// normal view mode:
		// - the table is filtered manually by module, mode and pattern
		else
		{
            $filter_mode = $table_gui->getFilterItemByPostVar('mode')->getValue();
            $filter_pattern = $table_gui->getFilterItemByPostVar('pattern')->getValue();
            $filter_module = $table_gui->getFilterItemByPostVar('module')->getValue();
            $filter_module = $filter_module == 'all' ? '' : $filter_module;
            $filter_modules = $filter_module ? array($filter_module) : array();

			if (!isset($compare_content))
			{
				$compare_content = ilObjLanguageExt::_getValues(
				            		$compare, $filter_modules);

				$compare_comments = ilObjLanguageExt::_getRemarks($compare);
			}

			switch ($filter_mode)
			{
				case "changed":
					$translations = $this->object->getChangedValues(
					        		$filter_modules, $filter_pattern);
					break;

				case "added":   //langmode only
					$translations = $this->object->getAddedValues(
					        		$filter_modules, $filter_pattern);
					break;

				case "unchanged":
					$translations = $this->object->getUnchangedValues(
					            	$filter_modules, $filter_pattern);
					break;
					
				case "commented":
                    $translations = $this->object->getCommentedValues(
					            	$filter_modules, $filter_pattern);
					break;

				case "dbremarks":
                    $translations = $this->object->getAllValues(
					            	$filter_modules, $filter_pattern);

					$translations = array_intersect_key($translations, $remarks);
					break;

				case "equal":
                    $translations = $this->object->getAllValues(
					            	$filter_modules, $filter_pattern);

					$translations = array_intersect_assoc($translations, $compare_content);
					break;

				case "different":
                    $translations = $this->object->getAllValues(
					            	$filter_modules, $filter_pattern);

					$translations = array_diff_assoc($translations, $compare_content);
					break;

				case "conflicts":
				    $former_file = $this->object->getCustLangPath() . '/ilias_' . $this->object->key . '.lang';
					if (!is_readable($former_file))
					{
                        ilUtil::sendFailure(sprintf($this->lng->txt("language_former_file_missing"), $former_file)
										.'<br />'. $this->lng->txt("language_former_file_description") , false);
                        $translations = array();
                        break;
					}
					$global_file_obj = $this->object->getGlobalLanguageFile();
					$former_file_obj = new ilLanguageFile($former_file);
					$former_file_obj->read();
					$global_changes = array_diff_assoc(
											$global_file_obj->getAllValues(),
											$former_file_obj->getAllValues());
					if (!count($global_changes))
					{
                        ilUtil::sendInfo(sprintf($this->lng->txt("language_former_file_equal"), $former_file)
										.'<br />'. $this->lng->txt("language_former_file_description") , false);
                        $translations = array();
                        break;
					}
					$translations = $this->object->getChangedValues(
					        		$filter_modules, $filter_pattern);

					$translations = array_intersect_key($translations, $global_changes);
				    break;

				case "all":
				default:
					$translations = $this->object->getAllValues(
					            	$filter_modules, $filter_pattern);
			}
        }

        // prepare the the data for the table
		$data = array();
		foreach($translations as $name => $translation)
		{
			$keys = explode($this->lng->separator, $name);
			$row = array();

            $row["module"] = $keys[0];
            $row["topic"] = $keys[1];
            $row["name"] = $name;
            $row["translation"] = $translation;
            $row["comment"] = $comments[$name];
            $row["default"] = $compare_content[$name];
            $row["default_comment"] = $compare_comments[$name];

			$data[] = $row;
		}

        // render and show the table
        $table_gui->setData($data);
        $tpl->setContent($table_gui->getHTML());
	}

    /**
     * Apply filter
     */
    function applyFilterObject()
    {
        $table_gui = $this->getViewTable();
        $table_gui->writeFilterToSession();    // writes filter to session
        $table_gui->resetOffset();             // sets record offest to 0 (first page)
        $this->viewObject();
    }

    /**
     * Reset filter
     */
    function resetFilterObject()
    {
        $table_gui = $this->getViewTable();
        $table_gui->resetOffset();                // sets record offest to 0 (first page)
        $table_gui->resetFilter();                // clears filter
        $this->viewObject();
    }

	/**
	* Save the changed translations
	*/
	function saveObject()
	{
		// prepare the values to be saved
		$save_array = array();
		$remarks_array = array();
		foreach ($_POST as $key => $value)
		{
			$keys = explode($this->lng->separator, ilUtil::stripSlashes($key, false));

			if (count($keys) == 2)
			{
				// avoid line breaks
		  		$value = preg_replace("/(\015\012)|(\015)|(\012)/","<br />",$value);
		  		$value = ilUtil::stripSlashes($value, false);
				$save_array[$key] = $value;

				$remarks_array[$key] = $_POST[$key.$this->lng->separator."comment"];
			}
		}
		
		// save the translations
		ilObjLanguageExt::_saveValues($this->object->key, $save_array, $remarks_array);

		// view the list
		$this->viewObject();
	}


	/**
	* Show the screen to import a language file
	*/
	function importObject()
	{
        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_import_file"));
        $form->addCommandButton('upload',$this->lng->txt("upload"));

        $fu = new ilFileInputGUI($this->lng->txt("file"), "userfile");
        $form->addItem($fu);

        $rg = new ilRadioGroupInputGUI($this->lng->txt("language_mode_existing"), "mode_existing");
        $ro = new ilRadioOption($this->lng->txt("language_mode_existing_keepall"), "keepall");
        $ro->setInfo($this->lng->txt("language_mode_existing_keepall_info"));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_mode_existing_keepnew"), "keepnew");
        $ro->setInfo($this->lng->txt("language_mode_existing_keepnew_info"));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_mode_existing_replace"), "replace");
        $ro->setInfo($this->lng->txt("language_mode_existing_replace_info"));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_mode_existing_delete"), "delete");
        $ro->setInfo($this->lng->txt("language_mode_existing_delete_info"));
        $rg->addOption($ro);
        $rg->setValue($this->session["import"]["mode_existing"] ? $this->session["import"]["mode_existing"] : "keepall");
        $form->addItem($rg);

        $this->tpl->setContent($form->getHTML());
	}
	
	
	/**
	* Process an uploaded language file
	*/
	function uploadObject()
	{
        // save form inputs for next display
        $this->session["import"]["mode_existing"] = ilUtil::stripSlashes($_POST['mode_existing']);

		if ($_POST['cmd']['upload'])
		{
			$file = $_FILES['userfile']['tmp_name'].'x';
			
			if (ilUtil::moveUploadedFile($_FILES['userfile']['tmp_name'],
									 	 $_FILES['userfile']['name'],
									 	 $file))
			{
				$this->object->importLanguageFile($file,$_POST['mode_existing']);
				ilUtil::sendSuccess(sprintf($this->lng->txt("language_file_imported"), $_FILES['userfile']['name']) , true);
			}
		}

        $this->ctrl->redirect($this, 'import');
	}

	
	/**
	* Show the screen to export a language file
	*/
	function exportObject()
	{
        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_export_file"));
        $form->setPreventDoubleSubmission(false);
        $form->addCommandButton('download',$this->lng->txt("download"));

        $rg = new ilRadioGroupInputGUI($this->lng->txt("language_file_scope"), "scope");
        $ro = new ilRadioOption($this->lng->txt("language_scope_global"), "global");
        $ro->setInfo($this->lng->txt("language_scope_global_info"));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_scope_local"), "local");
        $ro->setInfo($this->lng->txt("language_scope_local_info"));
        $rg->addOption($ro);
        if ($this->langmode)
        {
            $ro = new ilRadioOption($this->lng->txt("language_scope_added"), "added");
            $ro->setInfo($this->lng->txt("language_scope_added_info"));
            $rg->addOption($ro);
        }
        $ro = new ilRadioOption($this->lng->txt("language_scope_unchanged"), "unchanged");
        $ro->setInfo($this->lng->txt("language_scope_unchanged_info"));
        $rg->addOption($ro);
        if ($this->langmode)
        {
            $ro = new ilRadioOption($this->lng->txt("language_scope_merged"), "merged");
            $ro->setInfo($this->lng->txt("language_scope_merged_info"));
            $rg->addOption($ro);
        }

        $rg->setValue($this->session["export"]["scope"] ? $this->session["export"]["scope"] : "global");
        $form->addItem($rg);

        $this->tpl->setContent($form->getHTML());
	}

	
	/**
	* Download a language file
	*/
	function downloadObject()
	{
        // save the selected scope
        $this->session["export"]["scope"] = ilUtil::stripSlashes($_POST["scope"]);

		$filename = 'ilias_' . $this->object->key . '_'
		. str_replace(".", "_", substr(ILIAS_VERSION, 0, strpos(ILIAS_VERSION, " ")))
    	. "-" . date('Y-m-d')
		. ".lang.".$this->session["export"]["scope"];
		
		$global_file_obj = $this->object->getGlobalLanguageFile();
		$local_file_obj = new ilLanguageFile($filename, $this->object->key, $_POST["scope"]);

		if ($_POST["scope"] == 'global')
		{
            $local_file_obj->setParam("author", $global_file_obj->getParam('author'));
            $local_file_obj->setParam("version", $global_file_obj->getParam('version'));
			$local_file_obj->setAllValues($this->object->getAllValues());
			if ($this->langmode)
			{
				$local_file_obj->setAllComments($this->object->getAllRemarks());
			}
		}
		elseif ($_POST["scope"] == 'local')
		{
           	$local_file_obj->setParam("based_on", $global_file_obj->getParam('version'));
			$local_file_obj->setAllValues($this->object->getChangedValues());
			if ($this->langmode)
			{
				$local_file_obj->setAllComments($this->object->getAllRemarks());
			}
		}
		elseif ($_POST["scope"] == 'added') // langmode only
		{
        	$local_file_obj->setParam("author", $global_file_obj->getParam('author'));
            $local_file_obj->setParam("version", $global_file_obj->getParam('version'));
			$local_file_obj->setAllValues($this->object->getAddedValues());
			$local_file_obj->setAllComments($this->object->getAllRemarks());
		}
		elseif ($_POST["scope"] == 'unchanged')
		{
        	$local_file_obj->setParam("author", $global_file_obj->getParam('author'));
            $local_file_obj->setParam("version", $global_file_obj->getParam('version'));
			$local_file_obj->setAllValues($this->object->getUnchangedValues());
			if ($this->langmode)
			{
				$local_file_obj->setAllComments($this->object->getAllRemarks());
			}
		}
		elseif ($_POST["scope"] == 'merged') // langmode only
		{
        	$local_file_obj->setParam("author", $global_file_obj->getParam('author'));
            $local_file_obj->setParam("version", $global_file_obj->getParam('version'));
			$local_file_obj->setAllValues($this->object->getMergedValues());
			$local_file_obj->setAllComments($this->object->getMergedRemarks());
		}

		ilUtil::deliverData($local_file_obj->build(), $filename);
	}


	/**
	* Process the language maintenance
	*/
	function maintainObject()
	{
        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_maintenance"));
        $form->setPreventDoubleSubmission(false);
        $form->addCommandButton('maintainExecute',$this->lng->txt("language_process_maintenance"));

        $rg = new ilRadioGroupInputGUI($this->lng->txt("language_maintain_local_changes"), "maintain");
        $ro = new ilRadioOption($this->lng->txt("language_save_local_changes"), "save");
        $ro->setInfo(sprintf($this->lng->txt("language_save_local_changes_info"),$this->object->key));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_load_local_changes"), "load");
        $ro->setInfo(sprintf($this->lng->txt("language_load_local_changes_info"),$this->object->key));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_clear_local_changes"), "clear");
        $ro->setInfo(sprintf($this->lng->txt("language_clear_local_changes_info"),$this->object->key));
        $rg->addOption($ro);
        if ($this->langmode)
        {
            $ro = new ilRadioOption($this->lng->txt("language_delete_local_additions"), "delete_added");
            $ro->setInfo(sprintf($this->lng->txt("language_delete_local_additions_info"), $this->object->key));
            $rg->addOption($ro);
            $ro = new ilRadioOption($this->lng->txt("language_merge_local_changes"), "merge");
            $ro->setInfo(sprintf($this->lng->txt("language_merge_local_changes_info"), $this->object->key));
            $rg->addOption($ro);
            $ro = new ilRadioOption($this->lng->txt("language_remove_local_file"), "remove_local_file");
            $ro->setInfo(sprintf($this->lng->txt("language_remove_local_file_info"), $this->object->key));
            $rg->addOption($ro);
        }
        $rg->setValue($this->session["maintain"]);
        $form->addItem($rg);

        $this->tpl->setContent($form->getHTML());
	}


    function maintainExecuteObject()
    {
        if (isset($_POST["maintain"]))
        {
            $this->session["maintain"] = ilUtil::stripSlashes($_POST["maintain"]);
        }

        switch ($_POST["maintain"])
        {
            // save the local changes to the local language file
            case "save":
                $lang_file = $this->object->getCustLangPath() . '/ilias_' . $this->object->key . '.lang.local';

                if ((is_file($lang_file) and is_writable($lang_file))
                    or (!file_exists($lang_file) and is_writable($this->object->getCustLangPath())))
                {
                    // save a copy of the distributed language file
                    $orig_file = $this->object->getLangPath() . '/ilias_' . $this->object->key . '.lang';
                    $copy_file = $this->object->getCustLangPath() . '/ilias_' . $this->object->key . '.lang';
                    @copy($orig_file, $copy_file);

                    // save a backup of the old local language file
                    @rename($lang_file, $lang_file.".bak");

                    // create and write the new local language file
                    $global_file_obj = $this->object->getGlobalLanguageFile();
                    $local_file_obj = new ilLanguageFile($lang_file, $this->object->key, 'local');
                    $local_file_obj->setParam('based_on', $global_file_obj->getParam('version'));
                    $local_file_obj->setAllValues($this->object->getChangedValues());
                    if ($this->langmode)
                    {
                        $local_file_obj->setAllComments($this->object->getAllRemarks());
                    }
                    $local_file_obj->write();

                    $this->object->setLocal(true);
                    ilUtil::sendSuccess($this->lng->txt("language_saved_local") , true);
                }
                else
                {
                    ilUtil::sendFailure($this->lng->txt("language_error_write_local") , true);
                }
                break;

            // load the content of the local language file
            case "load":
                $lang_file = $this->object->getCustLangPath() . '/ilias_' . $this->object->key . '.lang.local';
                if (is_file($lang_file) and is_readable($lang_file))
                {
                    $this->object->importLanguageFile($lang_file, 'replace');
                    $this->object->setLocal(true);
                    ilUtil::sendSuccess($this->lng->txt("language_loaded_local") , true);
                }
                else
                {
                    ilUtil::sendFailure($this->lng->txt("language_error_read_local") , true);
                }
                break;

            // revert the database to the default language file
            case "clear":
                $lang_file = $this->object->getLangPath() . '/ilias_' . $this->object->key . '.lang';
                if (is_file($lang_file) and is_readable($lang_file))
                {
                    $this->object->importLanguageFile($lang_file, 'delete');
                    $this->object->setLocal(false);
                    ilUtil::sendSuccess($this->lng->txt("language_cleared_local") , true);
                }
                else
                {
                    ilUtil::sendFailure($this->lng->txt("language_error_clear_local") , true);
                }
                break;

            // delete local additions in the datavase (langmode only)
            case "delete_added":
                ilObjLanguageExt::_deleteValues($this->object->key, $this->object->getAddedValues());
                break;

            // merge local changes back to the global language file (langmode only)
            case "merge":

                $orig_file = $this->object->getLangPath() . '/ilias_' . $this->object->key . '.lang';
                $copy_file = $this->object->getCustLangPath() . '/ilias_' . $this->object->key . '.lang';

                if (is_file($orig_file) and is_writable($orig_file))
                {
                    // save a copy of the global language file
                    @copy($orig_file, $copy_file);

                    // modify and write the new global file
                    $global_file_obj = $this->object->getGlobalLanguageFile();
                    $global_file_obj->setAllValues($this->object->getMergedValues());
                    $global_file_obj->setAllComments($this->object->getMergedRemarks());
                    $global_file_obj->write();
                    ilUtil::sendSuccess($this->lng->txt("language_merged_global") , true);
                }
                else
                {
                    ilUtil::sendFailure($this->lng->txt("language_error_write_global") , true);
                }
                break;

            // remove the local language file (langmode only)
            case "remove_local_file":
                $lang_file = $this->object->getCustLangPath() . '/ilias_' . $this->object->key . '.lang.local';

                if (!is_file($lang_file))
                {
                    $this->object->setLocal(false);
                    ilUtil::sendFailure($this->lng->txt("language_error_local_missed") , true);
                }
                elseif (@unlink($lang_file))
                {
                    $this->object->setLocal(false);
                    ilUtil::sendSuccess($this->lng->txt("language_local_file_deleted") , true);
                }
                else
                {
                    ilUtil::sendFailure($this->lng->txt("language_error_delete_local") , true);
                }
                break;
        }

        $this->ctrl->redirect($this,"maintain");
    }

	/**
	* Set the language settings
	*/
	function settingsObject()
	{
		global $ilSetting;

		$translate_key = "lang_translate_". $this->object->key;

		// save and get the page translation setting
        if (!empty($_POST))
        {
            $ilSetting->set($translate_key, (bool) $_POST["translation"]);
            ilUtil::sendSuccess($this->lng->txt("settings_saved"));
        }
		$translate = $ilSetting->get($translate_key, false);

        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_settings"));
        $form->setPreventDoubleSubmission(false);
        $form->addCommandButton('settings',$this->lng->txt("language_change_settings"));

        $ci = new ilCheckboxInputGUI($this->lng->txt("language_translation_enabled"), "translation");
        $ci->setChecked((bool) $translate);
        $ci->setInfo($this->lng->txt("language_note_translation"));
        $form->addItem($ci);

        $this->tpl->setContent($form->getHTML());
	}

	/**
	* Print out statistics about the language
	*/
	function statisticsObject()
	{
		$modules = ilObjLanguageExt::_getModules($this->object->key);
		
		$data = array();
		$total = array("module"=>'',"all"=>0,"changed"=>0, "unchanged"=>0);
		foreach($modules as $module)
		{
			$row = array();
			$row['module'] = $module;
			$row['all'] = count($this->object->getAllValues(array($module)));
			$row['changed'] = count($this->object->getChangedValues(array($module)));
			$row['unchanged'] = $row['all']-$row['changed'];
			$total['all'] += $row['all'];
			$total['changed'] += $row['changed'];
			$total['unchanged'] += $row['unchanged'];
			$data[] = $row;
		}
		$total['module'] = "<b>".$this->lng->txt("language_all_modules")."</b>";
		$total['all'] = "<b>".$total['all']."</b>";
		$total['changed'] = "<b>".$total['changed']."</b>";
		$total['unchanged'] = "<b>".$total['unchanged']."</b>";
		$data[] = $total;

		// create and configure the table object
		include_once 'Services/Table/classes/class.ilTable2GUI.php';
		$table_gui = new ilTable2GUI($this, "statistics");
        $table_gui->setRowTemplate("tpl.lang_statistics_row.html","Services/Language");
        $table_gui->setEnableTitle(false);
        $table_gui->setEnableNumInfo(false);
        $table_gui->setLimit(count($data));
        $table_gui->setExportFormats(array(ilTable2GUI::EXPORT_EXCEL));

        $table_gui->addColumn(ucfirst($this->lng->txt("module")), "", "25%");
        $table_gui->addColumn($this->lng->txt("language_scope_global"), "", "25%");
        $table_gui->addColumn($this->lng->txt("language_scope_local"), "", "25%");
        $table_gui->addColumn($this->lng->txt("language_scope_unchanged"), "", "25%");

        $table_gui->setData($data);

        $this->tpl->setContent($table_gui->getHTML());
	}


	/**
	 * Get tabs for admin mode
     *(Overwritten from ilObjectGUI, called by prepareOutput)
     *
	 * @param	object	tabs gui object
	 */
	function getAdminTabs(&$tabs_gui)
	{
        if (!ilObjLanguageAccess::_isPageTranslation())
        {
            $tabs_gui->addTarget("edit",
                $this->ctrl->getLinkTarget($this, "view"),
                array("","view","cancel","save"));

            $tabs_gui->addTarget("export",
                $this->ctrl->getLinkTarget($this, "export"),
                array("export","download"));

            $tabs_gui->addTarget("import",
                $this->ctrl->getLinkTarget($this, "import"),
                array("import","upload"));

            $tabs_gui->addTarget("language_maintain",
                $this->ctrl->getLinkTarget($this, "maintain"),
                array("maintain"));

            $tabs_gui->addTarget("settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                array("settings"));

            $tabs_gui->addTarget("language_statistics",
                $this->ctrl->getLinkTarget($this, "statistics"),
                array("statistics"));
        }
	}


	/**
	 * Set the locator for admin mode
	 *(Overwritten from ilObjectGUI, called by prepareOutput)
	 */
	function addAdminLocatorItems()
	{
		global $ilLocator;

        if (!ilObjLanguageAccess::_isPageTranslation())
        {
            parent::addAdminLocatorItems(true); // #13881

            $ilLocator->addItem($this->lng->txt("languages"),
                $this->ctrl->getLinkTargetByClass("ilobjlanguagefoldergui", ""));

            $ilLocator->addItem($this->lng->txt("meta_l_". $this->object->getTitle()),
                $this->ctrl->getLinkTarget($this, "view"));
        }
	}


	/**
	 * Set the Title and the description
	 * (Overwritten from ilObjectGUI, called by prepareOutput)
	 */
	function setTitleAndDescription()
	{
       if (ilObjLanguageAccess::_isPageTranslation())
       {
           $this->tpl->setHeaderPageTitle($this->lng->txt("translation"));
           $this->tpl->setTitle($this->lng->txt("translation"). " ".$this->lng->txt("meta_l_".$this->object->key));
       }
       else
       {
           $this->tpl->setTitle($this->lng->txt("meta_l_".$this->object->key));
       }
       $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lngf.svg"), $this->lng->txt("obj_" . $this->object->getType()));
    }
	
} // END class.ilObjLanguageExtGUI
?>

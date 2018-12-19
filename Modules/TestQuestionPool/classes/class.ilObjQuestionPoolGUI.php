<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Object/classes/class.ilObjectGUI.php';
require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
require_once './Modules/TestQuestionPool/exceptions/class.ilTestQuestionPoolException.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';
require_once './Modules/Test/classes/class.ilObjAssessmentFolder.php';
require_once './Modules/Test/classes/class.ilObjTest.php';

/**
 * Class ilObjQuestionPoolGUI
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * 
 * @version		$Id$
 *
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilAssQuestionPageGUI, ilQuestionBrowserTableGUI, ilToolbarGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assOrderingQuestionGUI, assImagemapQuestionGUI, assJavaAppletGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assNumericGUI, assTextSubsetGUI, assSingleChoiceGUI, ilPropertyFormGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assTextQuestionGUI, ilObjectMetaDataGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilQuestionPoolExportGUI, ilInfoScreenGUI, ilObjTaxonomyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilAssQuestionHintsGUI, ilAssQuestionFeedbackEditingGUI, ilLocalUnitConfigurationGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilObjQuestionPoolSettingsGeneralGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilAssQuestionPreviewGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: assKprimChoiceGUI, assLongMenuGUI
 * @ilCtrl_Calls ilObjQuestionPoolGUI: ilQuestionPoolSkillAdministrationGUI
 *
 * @ingroup ModulesTestQuestionPool
 * 
 */
class ilObjQuestionPoolGUI extends ilObjectGUI
{
	/**
	 * @var ilObjQuestionPool
	 */
	public $object;
	
	/**
	* Constructor
	* @access public
	*/
	public function __construct()
	{
		global $DIC;
		$lng = $DIC['lng'];
		$ilCtrl = $DIC['ilCtrl'];
		$rbacsystem = $DIC['rbacsystem'];
		$lng->loadLanguageModule("assessment");
		$this->type = "qpl";
		$this->ctrl =& $ilCtrl;
		
		$this->ctrl->saveParameter($this, array(
			"ref_id", "test_ref_id", "calling_test", "test_express_mode", "q_id", 'tax_node', 'calling_consumer', 'consumer_context'
		));
		$this->ctrl->saveParameter($this, "calling_consumer");
		$this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'calling_consumer');
		$this->ctrl->saveParameterByClass('ilAssQuestionPageGUI', 'consumer_context');
		$this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'calling_consumer');
		$this->ctrl->saveParameterByClass('ilobjquestionpoolgui', 'consumer_context');

		parent::__construct("",$_GET["ref_id"], true, false);
	}

	/**
	 * execute command
	 *
	 * @global ilLocatorGUI $ilLocator
	 * @global ilAccessHandler $ilAccess
	 * @global ilNavigationHistory $ilNavigationHistory
	 * @global ilTemplate $tpl
	 * @global ilCtrl $ilCtrl
	 * @global ilTabsGUI $ilTabs
	 * @global ilLanguage $lng
	 * @global ILIAS $ilias 
	 */
	function executeCommand()
	{
		global $DIC;
		$ilUser = $DIC['ilUser'];
		$ilLocator = $DIC['ilLocator'];
		$ilAccess = $DIC['ilAccess'];
		$ilNavigationHistory = $DIC['ilNavigationHistory'];
		$tpl = $DIC['tpl'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilErr = $DIC['ilErr'];
		$ilTabs = $DIC['ilTabs'];
		$lng = $DIC['lng'];
		$ilDB = $DIC['ilDB'];
		$ilPluginAdmin = $DIC['ilPluginAdmin'];
		$ilias = $DIC['ilias'];
		
		$writeAccess = $ilAccess->checkAccess("write", "", $_GET["ref_id"]);
		
		if ((!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) && (!$ilAccess->checkAccess("visible", "", $_GET["ref_id"])))
		{
			global $DIC;
			$ilias = $DIC['ilias'];
			$ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
		}
		
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			if('qpl' == $this->object->getType())
			{
				$ilNavigationHistory->addItem($_GET["ref_id"],
					"ilias.php?baseClass=ilObjQuestionPoolGUI&cmd=questions&ref_id=".$_GET["ref_id"], "qpl");
			}
		}
		
		$cmd = $this->ctrl->getCmd("questions");
		$next_class = $this->ctrl->getNextClass($this);
		
		if( in_array($next_class, array('', 'ilobjquestionpoolgui')) && $cmd == 'questions' )
		{
			$_GET['q_id'] = '';
		}
		
		$this->prepareOutput();
		
		$this->ctrl->setReturn($this, "questions");
		
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "ta.css", "Modules/Test"), "screen");
		
		if ($_GET["q_id"] < 1)
		{
			$q_type = ($_POST["sel_question_types"] != "")
				? $_POST["sel_question_types"]
				: $_GET["sel_question_types"];
		}
		if ($cmd != "createQuestion" && $cmd != "createQuestionForTest"
			&& $next_class != "ilassquestionpagegui")
		{
			if (($_GET["test_ref_id"] != "") or ($_GET["calling_test"]))
			{
				$ref_id = $_GET["test_ref_id"];
				if (!$ref_id)
				{
					$ref_id = $_GET["calling_test"];
				}
			}
		}
		switch($next_class)
		{
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ilobjectmetadatagui':
				if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->WARNING);
				}				
				include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
				$md_gui = new ilObjectMetaDataGUI($this->object);	
				$this->ctrl->forwardCommand($md_gui);
				break;
			
			case 'ilassquestionpreviewgui':

				$this->ctrl->saveParameter($this, "q_id");

				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionPreviewGUI.php';
				$gui = new ilAssQuestionPreviewGUI($this->ctrl, $this->tabs_gui, $this->tpl, $this->lng, $ilDB, $ilUser);
				
				$gui->initQuestion((int)$_GET['q_id'], $this->object->getId());
				$gui->initPreviewSettings($this->object->getRefId());
				$gui->initPreviewSession($ilUser->getId(), (int)$_GET['q_id']);
				$gui->initHintTracking();
				$gui->initStyleSheets();

				global $DIC;
				$ilHelp = $DIC['ilHelp'];
				$ilHelp->setScreenIdComponent("qpl");

				$this->ctrl->forwardCommand($gui);
				break;
				
			case "ilassquestionpagegui":
				include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
				$this->tpl->setCurrentBlock("ContentStyle");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
				$this->tpl->parseCurrentBlock();
		
				// syntax style
				$this->tpl->setCurrentBlock("SyntaxStyle");
				$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
				$this->tpl->parseCurrentBlock();
				
				include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
				$q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
				$q_gui->setRenderPurpose(assQuestionGUI::RENDER_PURPOSE_PREVIEW);
				$q_gui->setQuestionTabs();
				$q_gui->outAdditionalOutput();
				$q_gui->object->setObjId($this->object->getId());

				$q_gui->setTargetGuiClass(null);
				$q_gui->setQuestionActionCmd(null);
				
				if( $this->object->getType() == 'qpl' )
				{
					$q_gui->addHeaderAction();
				}
				
				$question = $q_gui->object;
				$this->ctrl->saveParameter($this, "q_id");
				include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
				$this->lng->loadLanguageModule("content");
				$this->ctrl->setReturnByClass("ilAssQuestionPageGUI", "view");
				$this->ctrl->setReturn($this, "questions");
				$page_gui = new ilAssQuestionPageGUI($_GET["q_id"]);
				$page_gui->obj->addUpdateListener(
					$question,
					'updateTimestamp'
				);
				$page_gui->setEditPreview(true);
				$page_gui->setEnabledTabs(false);
				if (strlen($this->ctrl->getCmd()) == 0 && !isset($_POST["editImagemapForward_x"])) // workaround for page edit imagemaps, keep in mind
				{
					$this->ctrl->setCmdClass(get_class($page_gui));
					$this->ctrl->setCmd("preview");
				}
				$page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(TRUE)));
				$page_gui->setTemplateTargetVar("ADM_CONTENT");
				$page_gui->setOutputMode("edit");
				$page_gui->setHeader($question->getTitle());
				$page_gui->setPresentationTitle($question->getTitle());
				$ret = $this->ctrl->forwardCommand($page_gui);
				$tpl->setContent($ret);
				break;
				
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$ret = $this->ctrl->forwardCommand($perm_gui);
				break;
				
			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('qpl');
				$this->ctrl->forwardCommand($cp);
				break;
				
			case "ilquestionpoolexportgui":
				require_once 'Modules/TestQuestionPool/classes/class.ilQuestionPoolExportGUI.php';
				$exp_gui = new ilQuestionPoolExportGUI($this);
				$exp_gui->addFormat('xml', $this->lng->txt('qpl_export_xml'));
				$exp_gui->addFormat('xls', $this->lng->txt('qpl_export_excel'), $this, 'createExportExcel');
				$ret = $this->ctrl->forwardCommand($exp_gui);
				break;
			
			case "ilinfoscreengui":
				$this->infoScreenForward();
				break;
			
			case 'ilassquestionhintsgui':
	
				// set return target
				$this->ctrl->setReturn($this, "questions");

				// set context tabs
				require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
				$questionGUI = assQuestionGUI::_getQuestionGUI($q_type, $_GET['q_id']);
				$questionGUI->object->setObjId($this->object->getId());
				$questionGUI->setQuestionTabs();
				global $DIC;
				$ilHelp = $DIC['ilHelp'];
				$ilHelp->setScreenIdComponent("qpl");
				
				if( $this->object->getType() == 'qpl' && $writeAccess )
				{
					$questionGUI->addHeaderAction();
				}

				// forward to ilAssQuestionHintsGUI
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsGUI.php';
				$gui = new ilAssQuestionHintsGUI($questionGUI);
				$ilCtrl->forwardCommand($gui);
				
				break;
			
			case 'illocalunitconfigurationgui':
				if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->WARNING);
				}

				require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
				$questionGUI = assQuestionGUI::_getQuestionGUI($q_type, $_GET['q_id']);
				$questionGUI->object->setObjId($this->object->getId());
				$questionGUI->setQuestionTabs();

				$this->ctrl->setReturn($this, 'questions');

				require_once 'Modules/TestQuestionPool/classes/class.ilLocalUnitConfigurationGUI.php';
				require_once 'Modules/TestQuestionPool/classes/class.ilUnitConfigurationRepository.php';
				$gui = new ilLocalUnitConfigurationGUI(
					new ilUnitConfigurationRepository((int)$_GET['q_id'])
				);
				$ilCtrl->forwardCommand($gui);
				break;
			
			case 'ilassquestionfeedbackeditinggui':
	
				// set return target
				$this->ctrl->setReturn($this, "questions");

				// set context tabs
				require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
				$questionGUI = assQuestionGUI::_getQuestionGUI($q_type, $_GET['q_id']);
				$questionGUI->object->setObjId($this->object->getId());
				$questionGUI->setQuestionTabs();
				global $DIC;
				$ilHelp = $DIC['ilHelp'];
				$ilHelp->setScreenIdComponent("qpl");
				
				if( $this->object->getType() == 'qpl' && $writeAccess )
				{
					$questionGUI->addHeaderAction();
				}
				
				// forward to ilAssQuestionFeedbackGUI
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackEditingGUI.php';
				$gui = new ilAssQuestionFeedbackEditingGUI($questionGUI, $ilCtrl, $ilAccess, $tpl, $ilTabs, $lng);
				$ilCtrl->forwardCommand($gui);
				
				break;
				
			case 'ilobjquestionpoolsettingsgeneralgui':
				require_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPoolSettingsGeneralGUI.php';
				$gui = new ilObjQuestionPoolSettingsGeneralGUI($ilCtrl, $ilAccess, $lng, $tpl, $ilTabs, $this);
				$this->ctrl->forwardCommand($gui);
				break;

			case "ilobjtaxonomygui":

				require_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPoolTaxonomyEditingCommandForwarder.php';
				$forwarder = new ilObjQuestionPoolTaxonomyEditingCommandForwarder(
						$this->object, $ilDB, $ilPluginAdmin, $ilCtrl, $ilTabs, $lng
				);
				
				$forwarder->forward();
				
				break;
			
			case 'ilquestionpoolskilladministrationgui':

				require_once 'Modules/TestQuestionPool/classes/class.ilQuestionPoolSkillAdministrationGUI.php';
				$gui = new ilQuestionPoolSkillAdministrationGUI(
					$ilias, $ilCtrl, $ilAccess, $ilTabs, $tpl, $lng, $ilDB, $ilPluginAdmin, $this->object, $this->ref_id
				);
				
				$this->ctrl->forwardCommand($gui);
				
				break;
				
			case 'ilquestionbrowsertablegui':
				$this->ctrl->forwardCommand($this->buildQuestionBrowserTableGUI($taxIds = array())); // no tax ids required
				break;
			
			case "ilobjquestionpoolgui":
			case "":
				
				if( $cmd == 'questions' )
				{
					$this->ctrl->setParameter($this, 'q_id', '');
				}
				
				$cmd.= "Object";
				$ret = $this->$cmd();
				break;
				
			default:
				$this->ctrl->setReturn($this, "questions");
				include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
				$q_gui = assQuestionGUI::_getQuestionGUI($q_type, $_GET["q_id"]);
				$q_gui->setEditContext(assQuestionGUI::EDIT_CONTEXT_AUTHORING);
				$q_gui->object->setObjId($this->object->getId());
				if($this->object->getType() == 'qpl')
				{
					$q_gui->setTaxonomyIds($this->object->getTaxonomyIds());
					$this->object->addQuestionChangeListeners($q_gui->object);
					if( $writeAccess )
					{
						$q_gui->addHeaderAction();
					}
				}
				$q_gui->setQuestionTabs();
				global $DIC;
				$ilHelp = $DIC['ilHelp'];
				$ilHelp->setScreenIdComponent("qpl");
				$ret = $this->ctrl->forwardCommand($q_gui);
				break;
		}

		if ( !(strtolower($_GET["baseClass"]) == "iladministrationgui" 
				|| strtolower($_GET['baseClass']) == 'ilrepositorygui') 
			&& $this->getCreationMode() != true)
		{
			$this->tpl->show();
		}
	}
	
	/**
	 * Gateway for exports initiated from workspace, as there is a generic
	 * forward to {objTypeMainGUI}::export()
	 */
	protected function exportObject()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		$DIC->ctrl()->redirectByClass('ilQuestionPoolExportGUI');
	}
	
	/**
	* download file
	*/
	function downloadFileObject()
	{
		$file = explode("_", $_GET["file_id"]);
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj = new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}
	
	/**
	* show fullscreen view
	*/
	function fullscreenObject()
	{
		include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPageGUI.php");
		$page_gui = new ilAssQuestionPageGUI($_GET["pg_id"]);
		$page_gui->showMediaFullscreen();
	}


	/**
	* set question list filter
	*/
	function filterObject()
	{
		$this->questionsObject();
	}

	/**
	* resets filter
	*/
	function resetFilterObject()
	{
		$_POST["filter_text"] = "";
		$_POST["sel_filter_type"] = "";
		$this->questionsObject();
	}

	/**
	* download source code paragraph
	*/
	function download_paragraphObject()
	{
		include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionPage.php");
		$pg_obj = new ilAssQuestionPage($_GET["pg_id"]);
		$pg_obj->send_paragraph ($_GET["par_id"], $_GET["downloadtitle"]);
		exit;
	}

	/**
	* imports question(s) into the questionpool
	*/
	function uploadQplObject($questions_only = false)
	{
		$this->ctrl->setParameter($this, 'new_type', $_REQUEST['new_type']);
		if ($_FILES["xmldoc"]["error"] > UPLOAD_ERR_OK)
		{
			ilUtil::sendFailure($this->lng->txt("error_upload"), true);
			if(!$questions_only)
			{
				$this->ctrl->redirect($this, 'create');
			}
			return false;
		}
		// create import directory
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$basedir = ilObjQuestionPool::_createImportDirectory();
		
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		// copy uploaded file to import directory
		$file = pathinfo($_FILES["xmldoc"]["name"]);
		$full_path = $basedir."/".$_FILES["xmldoc"]["name"];
		$DIC['ilLog']->write(__METHOD__.": full path " . $full_path);
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], $_FILES["xmldoc"]["name"], $full_path);
		$DIC['ilLog']->write(__METHOD__.": full path " . $full_path);
		if (strcmp($_FILES["xmldoc"]["type"], "text/xml") == 0)
		{
			$qti_file = $full_path;
			ilObjTest::_setImportDirectory($basedir);
		}
		else
		{
			// unzip file
			ilUtil::unzip($full_path);
	
			// determine filenames of xml files
			$subdir = basename($file["basename"],".".$file["extension"]);
			ilObjQuestionPool::_setImportDirectory($basedir);
			$xml_file = ilObjQuestionPool::_getImportDirectory().'/'.$subdir.'/'.$subdir.".xml";
			$qti_file = ilObjQuestionPool::_getImportDirectory().'/'.$subdir.'/'. str_replace("qpl", "qti", $subdir).".xml";
		}

		// start verification of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_VERIFY_QTI, 0, "");
		$result = $qtiParser->startParsing();
		$founditems =& $qtiParser->getFoundItems();
		if (count($founditems) == 0)
		{
			// nothing found

			// delete import directory
			ilUtil::delDir($basedir);

			ilUtil::sendFailure($this->lng->txt("qpl_import_no_items"), true);
			if(!$questions_only)
			{
				$this->ctrl->redirect($this, 'create');
			}
			return false;
		}
		
		$complete = 0;
		$incomplete = 0;
		foreach ($founditems as $item)
		{
			if (strlen($item["type"]))
			{
				$complete++;
			}
			else
			{
				$incomplete++;
			}
		}
		
		if ($complete == 0)
		{
			// delete import directory
			ilUtil::delDir($basedir);

			ilUtil::sendFailure($this->lng->txt("qpl_import_non_ilias_files"), true);
			if(!$questions_only)
			{
				$this->ctrl->redirect($this, 'create');
			}
			return false;
		}
		
		$_SESSION["qpl_import_xml_file"] = $xml_file;
		$_SESSION["qpl_import_qti_file"] = $qti_file;
		$_SESSION["qpl_import_subdir"] = $subdir;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.qpl_import_verification.html", "Modules/TestQuestionPool");

		require_once 'Modules/TestQuestionPool/classes/tables/class.ilQuestionPoolImportVerificationTableGUI.php';
		$table = new ilQuestionPoolImportVerificationTableGUI($this, 'uploadQplObject');
		$rows  = array();

		foreach($founditems as $item)
		{
			$row = array(
				'title' => $item['title'],
				'ident' => $item['ident'],
			);

			include_once "./Services/QTI/classes/class.ilQTIItem.php";
			switch ($item["type"])
			{
				case CLOZE_TEST_IDENTIFIER:
					$type = $this->lng->txt("assClozeTest");
					break;
				case IMAGEMAP_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assImagemapQuestion");
					break;
				case JAVAAPPLET_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assJavaApplet");
					break;
				case MATCHING_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assMatchingQuestion");
					break;
				case MULTIPLE_CHOICE_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assMultipleChoice");
					break;
				case KPRIM_CHOICE_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assKprimChoice");
					break;
				case LONG_MENU_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assLongMenu");
					break;
				case SINGLE_CHOICE_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assSingleChoice");
					break;
				case ORDERING_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assOrderingQuestion");
					break;
				case TEXT_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assTextQuestion");
					break;
				case NUMERIC_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assNumeric");
					break;
				case TEXTSUBSET_QUESTION_IDENTIFIER:
					$type = $this->lng->txt("assTextSubset");
					break;
				default:
					$type = $this->lng->txt($item["type"]);
					break;
			}
			
			if (strcmp($type, "-" . $item["type"] . "-") == 0)
			{
				global $DIC;
				$ilPluginAdmin = $DIC['ilPluginAdmin'];
				$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_MODULE, "TestQuestionPool", "qst");
				foreach ($pl_names as $pl_name)
				{
					$pl = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", $pl_name);
					if (strcmp($pl->getQuestionType(), $item["type"]) == 0)
					{
						$type = $pl->getQuestionTypeTranslation();
					}
				}
			}

			$row['type'] = $type;

			$rows[] = $row;
		}
		$table->setData($rows);

		$this->tpl->setCurrentBlock("import_qpl");
		if (is_file($xml_file))
		{
			// read file into a string
			$fh = @fopen($xml_file, "r") or die("");
			$xml = @fread($fh, filesize($xml_file));
			@fclose($fh);
			if (preg_match("/<ContentObject.*?MetaData.*?General.*?Title[^>]*?>([^<]*?)</", $xml, $matches))
			{
				$this->tpl->setVariable("VALUE_NEW_QUESTIONPOOL", $matches[1]);
			}
		}
		$this->tpl->setVariable("TEXT_CREATE_NEW_QUESTIONPOOL", $this->lng->txt("qpl_import_create_new_qpl"));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FOUND_QUESTIONS_INTRODUCTION", $this->lng->txt("qpl_import_verify_found_questions"));
		if ($questions_only)
		{
			$this->tpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_questions_into_qpl"));
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		}
		else
		{
			$this->tpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_qpl"));
			
			$this->ctrl->setParameter($this, "new_type", $this->type);
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		}

		$value_questions_only = 0;
		if ($questions_only) $value_questions_only = 1;
		$this->tpl->setVariable("VALUE_QUESTIONS_ONLY", $value_questions_only);
		$this->tpl->setVariable("VERIFICATION_TABLE", $table->getHtml());
		$this->tpl->setVariable("VERIFICATION_FORM_NAME", $table->getFormName());

		$this->tpl->parseCurrentBlock();
		
		return true;
	}
	
	/**
	* imports question(s) into the questionpool (after verification)
	*/
	function importVerifiedFileObject()
	{
		if ($_POST["questions_only"] == 1)
		{
			$newObj =& $this->object;
		}
		else
		{
			include_once("./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php");
			// create new questionpool object
			$newObj = new ilObjQuestionPool(0, true);
			// set type of questionpool object
			$newObj->setType($_GET["new_type"]);
			// set title of questionpool object to "dummy"
			$newObj->setTitle("dummy");
			// set description of questionpool object
			$newObj->setDescription("questionpool import");
			// create the questionpool class in the ILIAS database (object_data table)
			$newObj->create(true);
			// create a reference for the questionpool object in the ILIAS database (object_reference table)
			$newObj->createReference();
			// put the questionpool object in the administration tree
			$newObj->putInTree($_GET["ref_id"]);
			// get default permissions and set the permissions for the questionpool object
			$newObj->setPermissions($_GET["ref_id"]);			
		}

		if (is_file($_SESSION["qpl_import_dir"].'/'.$_SESSION["qpl_import_subdir"]."/manifest.xml"))
		{
			$_SESSION["qpl_import_idents"] = $_POST["ident"];
			
			$fileName = $_SESSION["qpl_import_subdir"].'.zip';
			$fullPath = $_SESSION["qpl_import_dir"].'/'.$fileName;
			
			include_once("./Services/Export/classes/class.ilImport.php");
			$imp = new ilImport((int) $_GET["ref_id"]);
			$map = $imp->getMapping();
			$map->addMapping("Modules/TestQuestionPool", "qpl", "new_id", $newObj->getId());
			$imp->importObject($newObj, $fullPath, $fileName, "qpl", "Modules/TestQuestionPool", true);
		}
		else
		{
			// start parsing of QTI files
			include_once "./Services/QTI/classes/class.ilQTIParser.php";
			$qtiParser = new ilQTIParser($_SESSION["qpl_import_qti_file"], IL_MO_PARSE_QTI, $newObj->getId(), $_POST["ident"]);
			$result = $qtiParser->startParsing();

			// import page data
			if (strlen($_SESSION["qpl_import_xml_file"]))
			{
				include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
				$contParser = new ilContObjParser($newObj, $_SESSION["qpl_import_xml_file"], $_SESSION["qpl_import_subdir"]);
				$contParser->setQuestionMapping($qtiParser->getImportMapping());
				$contParser->startParsing();

				// #20494
				$newObj->fromXML($_SESSION["qpl_import_xml_file"]);
			}

			// set another question pool name (if possible)
			if( isset($_POST["qpl_new"]) && strlen($_POST["qpl_new"]) )
			{
				$newObj->setTitle($_POST["qpl_new"]);
			}

			$newObj->update();
			$newObj->saveToDb();
		}
		
		// delete import directory
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::delDir(dirname(ilObjQuestionPool::_getImportDirectory()));

		if ($_POST["questions_only"] == 1)
		{
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt("object_imported"),true);
			ilUtil::redirect("ilias.php?ref_id=".$newObj->getRefId().
				"&baseClass=ilObjQuestionPoolGUI");
		}
	}
	
	function cancelImportObject()
	{
		if ($_POST["questions_only"] == 1)
		{
			$this->ctrl->redirect($this, "questions");
		}
		else
		{
			$this->ctrl->redirect($this, "cancel");
		}
	}
	
	/**
	* imports question(s) into the questionpool
	*/
	function uploadObject()
	{
		$upload_valid = true;
		$form = $this->getImportQuestionsForm();
		if($form->checkInput())
		{
			if(!$this->uploadQplObject(true))
			{
				$form->setValuesByPost();
				$this->importQuestionsObject($form);
			}
		}
		else
		{
			$form->setValuesByPost();
			$this->importQuestionsObject($form);
		}
	}
	
	/**
	* display the import form to import questions into the questionpool
	*/
	public function importQuestionsObject(ilPropertyFormGUI $form = null)
	{
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->getImportQuestionsForm();
		}
		
		$this->tpl->setContent($form->getHtml());
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	protected function getImportQuestionsForm()
	{
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('import_question'));
		$form->setFormAction($this->ctrl->getFormAction($this, 'upload'));
		
		$file = new ilFileInputGUI($this->lng->txt('select_file'), 'xmldoc');
		$file->setRequired(true);
		$form->addItem($file);

		$form->addCommandButton('upload', $this->lng->txt('upload'));
		$form->addCommandButton('questions', $this->lng->txt('cancel'));
		
		return $form;
	}

	/**
	* create new question
	*/
	function &createQuestionObject()
	{
		if( ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled() )
		{
			$addContEditMode = $_POST['add_quest_cont_edit_mode'];
		}
		else
		{
			$addContEditMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT;
		}

		include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
		$q_gui =& assQuestionGUI::_getQuestionGUI($_POST["sel_question_types"]);
		$this->object->addQuestionChangeListeners($q_gui->object);
		$q_gui->object->setObjId($this->object->getId());
		$q_gui->object->setAdditionalContentEditingMode($addContEditMode);
		$q_gui->object->createNewQuestion();
		$this->ctrl->setParameterByClass(get_class($q_gui), "q_id", $q_gui->object->getId());
		$this->ctrl->setParameterByClass(get_class($q_gui), "sel_question_types", $_POST["sel_question_types"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
	}

	/**
	* create new question
	*/
	function &createQuestionForTestObject()
	{
	    if( !$_REQUEST['q_id'] )
		{
			require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
			if( ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled() )
			{
				$addContEditMode = $_REQUEST['add_quest_cont_edit_mode'];
			}
			else
			{
				$addContEditMode = assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT;
			}
			
			include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
			$q_gui =& assQuestionGUI::_getQuestionGUI($_GET["sel_question_types"]);
			$q_gui->object->setObjId($this->object->getId());
			$q_gui->object->setAdditionalContentEditingMode($addContEditMode);
			$q_gui->object->createNewQuestion();
			
			$class = get_class($q_gui);
			$qId = $q_gui->object->getId();
	    }
	    else
		{
			$class = $_GET["sel_question_types"] . 'gui';
			$qId = $_REQUEST['q_id'];
	    }
		
		$this->ctrl->setParameterByClass($class, "q_id", $qId);
		$this->ctrl->setParameterByClass($class, "sel_question_types", $_REQUEST["sel_question_types"]);
		$this->ctrl->setParameterByClass($class, "prev_qid", $_REQUEST["prev_qid"]);
		
		$this->ctrl->redirectByClass($class, "editQuestion");
	}

	/**
	* save object
	* @access	public
	*/
	function afterSave(ilObject $a_new_object)
	{
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		ilUtil::redirect("ilias.php?ref_id=".$a_new_object->getRefId().
			"&baseClass=ilObjQuestionPoolGUI");
	}

	function questionObject()
	{
//echo "<br>ilObjQuestionPoolGUI->questionObject()";
		$type = $_GET["sel_question_types"];
		$this->editQuestionForm($type);
	}

	/**
	* delete questions confirmation screen
	*/
	function deleteQuestionsObject()
	{
		global $DIC;
		$rbacsystem = $DIC['rbacsystem'];

		$questionIdsToDelete = isset($_POST['q_id']) ? (array)$_POST['q_id'] : array();
		if(0 === count($questionIdsToDelete) && isset($_GET['q_id']))
		{
			$questionIdsToDelete = array($_GET['q_id']);
		}

		$questionIdsToDelete = array_filter(array_map('intval', $questionIdsToDelete));
		if(0 === count($questionIdsToDelete))
		{
			ilUtil::sendInfo($this->lng->txt("qpl_delete_select_none"), true);
			$this->ctrl->redirect($this, "questions");
		}
		
		ilUtil::sendQuestion($this->lng->txt("qpl_confirm_delete_questions"));
		$deleteable_questions =& $this->object->getDeleteableQuestionDetails($questionIdsToDelete);
		include_once "./Modules/TestQuestionPool/classes/tables/class.ilQuestionBrowserTableGUI.php";
		$table_gui = new ilQuestionBrowserTableGUI($this, 'questions', (($rbacsystem->checkAccess('write', $_GET['ref_id']) ? true : false)), true);
		$table_gui->setEditable($rbacsystem->checkAccess('write', $_GET['ref_id']));
		$table_gui->setData($deleteable_questions);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}


	/**
	* delete questions
	*/
	function confirmDeleteQuestionsObject()
	{
		// delete questions after confirmation
		foreach ($_POST["q_id"] as $key => $value)
		{
			$this->object->deleteQuestion($value);
			$this->object->cleanupClipboard($value);
		}
		if (count($_POST["q_id"])) ilUtil::sendSuccess($this->lng->txt("qpl_questions_deleted"), true);

		$this->ctrl->setParameter($this, 'q_id', '');
		
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Cancel question deletion
	*/
	function cancelDeleteQuestionsObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* export question
	*/
	function exportQuestionObject()
	{
		// export button was pressed
		if (count($_POST["q_id"]) > 0)
		{
			include_once("./Modules/TestQuestionPool/classes/class.ilQuestionpoolExport.php");
			$qpl_exp = new ilQuestionpoolExport($this->object, "xml", $_POST["q_id"]);
			$export_file = $qpl_exp->buildExportFile();
			$filename = $export_file;
			$filename = preg_replace("/.*\//", "", $filename);
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::deliverFile($export_file, $filename);
			exit();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_export_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	function filterQuestionBrowserObject()
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		$enableComments = $DIC->rbac()->system()->checkAccess('write', $_GET['ref_id']);
		
		require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
		$taxIds = ilObjTaxonomy::getUsageOfObject($this->object->getId());
		
		include_once "./Modules/TestQuestionPool/classes/tables/class.ilQuestionBrowserTableGUI.php";
		$table_gui = new ilQuestionBrowserTableGUI($this, 'questions', false, false, $taxIds, $enableComments);
		$table_gui->resetOffset();
		$table_gui->writeFilterToSession();
		$this->questionsObject();
	}

	function resetQuestionBrowserObject()
	{
		require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
		$taxIds = ilObjTaxonomy::getUsageOfObject($this->object->getId());

		include_once "./Modules/TestQuestionPool/classes/tables/class.ilQuestionBrowserTableGUI.php";
		$table_gui = new ilQuestionBrowserTableGUI($this, 'questions', false, false, $taxIds);
		$table_gui->resetOffset();
		$table_gui->resetFilter();
		$this->questionsObject();
	}
	
	protected function renoveImportFailsObject()
	{
		require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportFails.php';
		$qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->object->getId());
		$qsaImportFails->deleteRegisteredImportFails();
		
		$this->ctrl->redirect($this, 'infoScreen');
	}
	
	/**
	* list questions of question pool
	*/
	function questionsObject()
	{
		global $DIC;
		$rbacsystem = $DIC['rbacsystem'];
		$ilUser = $DIC['ilUser'];
		$ilCtrl = $DIC['ilCtrl'];
		$ilDB = $DIC['ilDB'];
		$lng = $DIC['lng'];
		$ilPluginAdmin = $DIC['ilPluginAdmin'];

		if(get_class($this->object) == "ilObjTest")
		{
			if ($_GET["calling_test"] > 0)
			{
				$ref_id = $_GET["calling_test"];
				$q_id = $_GET["q_id"];

				if ($_REQUEST['test_express_mode']) {
				    if ($q_id)
					ilUtil::redirect("ilias.php?ref_id=".$ref_id."&q_id=".$q_id."&test_express_mode=1&cmd=showPage&cmdClass=iltestexpresspageobjectgui&baseClass=ilObjTestGUI");
				    else
					ilUtil::redirect("ilias.php?ref_id=".$ref_id."&test_express_mode=1&cmd=showPage&cmdClass=iltestexpresspageobjectgui&baseClass=ilObjTestGUI");
				}
				else
				    ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&ref_id=".$ref_id."&cmd=questions");

			}
		}
		else if(isset($_GET['calling_consumer']) && (int)$_GET['calling_consumer'])
		{
			$ref_id = (int)$_GET['calling_consumer'];
			$consumer = ilObjectFactory::getInstanceByRefId($ref_id);
			if($consumer instanceof ilQuestionEditingFormConsumer)
			{
				ilUtil::redirect($consumer->getQuestionEditingFormBackTarget($_GET['consumer_context']));
			}
			require_once 'Services/Link/classes/class.ilLink.php';
			ilUtil::redirect(ilLink::_getLink($ref_id));
		}

		$this->object->purgeQuestions();
		// reset test_id SESSION variable
		$_SESSION["test_id"] = "";
		
		require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportFails.php';
		$qsaImportFails = new ilAssQuestionSkillAssignmentImportFails($this->object->getId());
		if( $qsaImportFails->failedImportsRegistered() )
		{
			require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
			$button = ilLinkButton::getInstance();
			$button->setUrl($this->ctrl->getLinkTarget($this, 'renoveImportFails'));
			$button->setCaption('ass_skl_import_fails_remove_btn');
			
			ilUtil::sendFailure($qsaImportFails->getFailedImportsMessage($this->lng).'<br />'.$button->render());
		}
		
		require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';
		$taxIds = ilObjTaxonomy::getUsageOfObject($this->object->getId());

		$table_gui = $this->buildQuestionBrowserTableGUI($taxIds);
		$table_gui->setPreventDoubleSubmission(false);

		if( $rbacsystem->checkAccess('write', $_GET['ref_id']) )
		{
			$toolbar = new ilToolbarGUI();
			
			require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
			$btn = ilLinkButton::getInstance();
			$btn->setCaption('ass_create_question');
			$btn->setUrl($this->ctrl->getLinkTarget($this, 'createQuestionForm'));
			$btn->setPrimary(true);
			$toolbar->addButtonInstance($btn);


			$btnImport = ilLinkButton::getInstance();
			$btnImport->setCaption('import');
			$btnImport->setUrl($this->ctrl->getLinkTarget($this, 'importQuestions'));
			$toolbar->addButtonInstance($btnImport);

			if(array_key_exists("qpl_clipboard", $_SESSION) && count($_SESSION['qpl_clipboard']))
			{
				$btnPaste = ilLinkButton::getInstance();
				$btnPaste->setCaption('paste');
				$btnPaste->setUrl($this->ctrl->getLinkTarget($this, 'paste'));
				$toolbar->addButtonInstance($btnPaste);
			}

			$this->tpl->setContent(
					$this->ctrl->getHTML($toolbar) . $this->ctrl->getHTML($table_gui)
			);
		}
		else
		{
			$this->tpl->setContent( $this->ctrl->getHTML($table_gui) );
		}
		
        if( $this->object->getShowTaxonomies() )
        {
            $this->lng->loadLanguageModule('tax');
			
            require_once 'Services/Taxonomy/classes/class.ilTaxonomyExplorerGUI.php';

            foreach($taxIds as $taxId)
            {
				if( $taxId != $this->object->getNavTaxonomyId() )
				{
					continue;
				}
				
				$taxExp = new ilTaxonomyExplorerGUI(
						$this, 'showNavTaxonomy', $taxId, 'ilobjquestionpoolgui', 'questions'
				);
				
				if( !$taxExp->handleCommand() )
				{
					$this->tpl->setLeftContent($taxExp->getHTML()."&nbsp;");
				}
				
				break;
            }
        }
	}
	
	private function createQuestionFormObject()
	{
		$form = $this->buildCreateQuestionForm();
		
		$this->tpl->setContent( $this->ctrl->getHTML($form) );
	}
	
	private function buildCreateQuestionForm()
	{
		global $DIC;
		$ilUser = $DIC['ilUser'];
		
		// form
		
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('ass_create_question'));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		// question type
		
		$options = array();
		foreach( $this->object->getQuestionTypes(false, true, false) as $translation => $data )
		{
			$options[$data['type_tag']] = $translation;
		}
		
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($this->lng->txt('question_type'), 'sel_question_types');
        $si->setOptions($options);
		//$si->setValue($ilUser->getPref("tst_lastquestiontype"));
		
		$form->addItem($si);
		
		// content editing mode
		
		if( ilObjAssessmentFolder::isAdditionalQuestionContentEditingModePageObjectEnabled() )
		{
			$ri = new ilRadioGroupInputGUI($this->lng->txt("tst_add_quest_cont_edit_mode"), "add_quest_cont_edit_mode");
			
			$ri->addOption(new ilRadioOption(
				$this->lng->txt('tst_add_quest_cont_edit_mode_default'),
					assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT
			));

			$ri->addOption(new ilRadioOption(
					$this->lng->txt('tst_add_quest_cont_edit_mode_page_object'),
					assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_PAGE_OBJECT
			));
			
			$ri->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);

			$form->addItem($ri, true);
		}
		else
		{
			$hi = new ilHiddenInputGUI("question_content_editing_type");
			$hi->setValue(assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT);
			$form->addItem($hi, true);
		}
		
		// commands
		
		$form->addCommandButton('createQuestion', $this->lng->txt('create'));
		$form->addCommandButton('questions', $this->lng->txt('cancel'));
		
		return $form;
	}

	/**
	 * Creates a print view for a question pool
	 */
	public function printObject()
	{
		/**
		 * @var $ilToolbar ilToolbarGUI
		 */
		global $DIC;
		$ilToolbar = $DIC['ilToolbar'];

		$ilToolbar->setFormAction($this->ctrl->getFormAction($this, 'print'));
		require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
		$mode = new ilSelectInputGUI($this->lng->txt('output_mode'), 'output');
		$mode->setOptions(array(
			'overview'           => $this->lng->txt('overview'),
			'detailed'           => $this->lng->txt('detailed_output_solutions'),
			'detailed_printview' => $this->lng->txt('detailed_output_printview')
		));
		$mode->setValue(ilUtil::stripSlashes($_POST['output']));
		
		$ilToolbar->setFormName('printviewOptions');
		$ilToolbar->addInputItem($mode, true);
		$ilToolbar->addFormButton($this->lng->txt('submit'), 'print');

		include_once "./Modules/TestQuestionPool/classes/tables/class.ilQuestionPoolPrintViewTableGUI.php";
		$table_gui = new ilQuestionPoolPrintViewTableGUI($this, 'print', $_POST['output']);
		$data = $this->object->getPrintviewQuestions();
		$totalPoints = 0;
		foreach($data as $d)
		{
			$totalPoints += $d['points'];
		}
		$table_gui->setTotalPoints($totalPoints);
		$table_gui->initColumns();
		$table_gui->setData($data);
		$this->tpl->setContent($table_gui->getHTML());
	}

	function updateObject()
	{
//		$this->update = $this->object->updateMetaData();
		$this->update = $this->object->update();
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
	}

	/**
	* paste questios from the clipboard into the question pool
	*/
	function pasteObject()
	{
		if (array_key_exists("qpl_clipboard", $_SESSION))
		{
			if($this->object->pasteFromClipboard())
			{
				ilUtil::sendSuccess($this->lng->txt("qpl_paste_success"), true);
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("qpl_paste_error"), true);
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_paste_no_objects"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* copy one or more question objects to the clipboard
	*/
	public function copyObject()
	{
		if (count($_POST["q_id"]) > 0)
		{
			foreach ($_POST["q_id"] as $key => $value)
			{
				$this->object->copyToClipboard($value);
			}
			ilUtil::sendInfo($this->lng->txt("qpl_copy_insert_clipboard"), true);
		}
		else if(isset($_GET['q_id']) && $_GET['q_id'] > 0)
		{
			$this->object->copyToClipboard((int)$_GET['q_id']);
			ilUtil::sendInfo($this->lng->txt("qpl_copy_insert_clipboard"), true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_copy_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* mark one or more question objects for moving
	*/
	function moveObject()
	{
		if (count($_POST["q_id"]) > 0)
		{
			foreach ($_POST["q_id"] as $key => $value)
			{
				$this->object->moveToClipboard($value);
			}
			ilUtil::sendInfo($this->lng->txt("qpl_move_insert_clipboard"), true);
		}
		else if(isset($_GET['q_id']) && $_GET['q_id'] > 0)
		{
			$this->object->moveToClipboard((int)$_GET['q_id']);
			ilUtil::sendInfo($this->lng->txt("qpl_copy_insert_clipboard"), true);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_move_select_none"), true);
		}
		$this->ctrl->redirect($this, "questions");
	}

	function createExportExcel()
	{
		global $DIC;
		$rbacsystem = $DIC['rbacsystem'];
		if ($rbacsystem->checkAccess("write", $_GET['ref_id']))
		{
			include_once("./Modules/TestQuestionPool/classes/class.ilQuestionpoolExport.php");
			$question_ids =& $this->object->getAllQuestionIds();
			$qpl_exp = new ilQuestionpoolExport($this->object, 'xls', $question_ids);
			$qpl_exp->buildExportFile();
			$this->ctrl->redirectByClass("ilquestionpoolexportgui", "");
		}
	}

	/**
	* edit question
	*/
	function &editQuestionForTestObject()
	{
		include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
		$q_gui =& assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
		$this->ctrl->redirectByClass(get_class($q_gui), "editQuestion");
	}

	protected function initImportForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("import_qpl"));

		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($this->lng->txt("import_file"), "xmldoc");
		$fi->setSuffixes(array("zip"));
		$fi->setRequired(true);
		$form->addItem($fi);

		$form->addCommandButton("importFile", $this->lng->txt("import"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));

		return $form;
	}
	
	/**
	* form for new questionpool object import
	*/
	protected function importFileObject($parent_id = null, $a_catch_errors = true)
	{
		$form = $this->initImportForm($_REQUEST["new_type"]);
		if($form->checkInput())
		{
			$this->uploadQplObject();
		}

		// display form to correct errors
		$this->tpl->setContent($form->getHTML());
	}

	function addLocatorItems()
	{
		global $DIC;
		$ilLocator = $DIC['ilLocator'];
		switch ($this->ctrl->getCmd())
		{
			case "create":
			case "importFile":
			case "cancel":
				break;
			default:
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
				break;
		}
		if ($_GET["q_id"] > 0)
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
			$q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
			if($q_gui->object instanceof assQuestion)
			{
				$q_gui->object->setObjId($this->object->getId());
				$title = $q_gui->object->getTitle();
				if(!$title)
				{
					$title = $this->lng->txt('new').': '.assQuestion::_getQuestionTypeName($q_gui->object->getQuestionType());
				}
				$ilLocator->addItem($title, $this->ctrl->getLinkTargetByClass(get_class($q_gui), "editQuestion"));
			}
			else
			{
				// Workaround for context issues: If no object was found, redirect without q_id parameter
				$this->ctrl->setParameter($this, 'q_id', '');
				$this->ctrl->redirect($this);
			}
		}
	}
	
	/**
	* called by prepare output
	*/
	function setTitleAndDescription()
	{
		parent::setTitleAndDescription();
		if ($_GET["q_id"] > 0)
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
			$q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
			if($q_gui->object instanceof assQuestion)
			{
				$q_gui->object->setObjId($this->object->getId());
				$title = $q_gui->object->getTitle();
				if(!$title)
				{
					$title = $this->lng->txt('new').': '.assQuestion::_getQuestionTypeName($q_gui->object->getQuestionType());
				}
				$this->tpl->setTitle($title);
				$this->tpl->setDescription($q_gui->object->getComment());
				$this->tpl->setTitleIcon(ilObject2::_getIcon("", "big", $this->object->getType()));
			}
			else
			{
				// Workaround for context issues: If no object was found, redirect without q_id parameter
				$this->ctrl->setParameter($this, 'q_id', '');
				$this->ctrl->redirect($this);
			}
		}
		else
		{
			$this->tpl->setTitle($this->object->getTitle());
			$this->tpl->setDescription($this->object->getLongDescription());
			$this->tpl->setTitleIcon(ilObject2::_getIcon("", "big", $this->object->getType()));
		}
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs()
	{
		global $DIC;
		$ilAccess = $DIC['ilAccess'];
		$ilHelp = $DIC['ilHelp'];

		$currentUserHasWriteAccess = $ilAccess->checkAccess("write", "", $this->object->getRefId());

		$ilHelp->setScreenIdComponent("qpl");
		
		$next_class = strtolower($this->ctrl->getNextClass());
		switch ($next_class)
		{
			case "":
			case "ilpermissiongui":
			case "ilobjectmetadatagui":
			case "ilquestionpoolexportgui":
			case "ilquestionpoolskilladministrationgui":
				break;
			
			case 'ilobjtaxonomygui':
			case 'ilobjquestionpoolsettingsgeneralgui':
				
				if( $currentUserHasWriteAccess )
				{
					$this->addSettingsSubTabs($this->tabs_gui);
				}
				
				break;
			
			default:
				return;
				break;
		}
		// questions
		$force_active = false;
		$commands = $_POST["cmd"];
		if (is_array($commands))
		{
			foreach ($commands as $key => $value)
			{
				if (preg_match("/^delete_.*/", $key, $matches) || 
					preg_match("/^addSelectGap_.*/", $key, $matches) ||
					preg_match("/^addTextGap_.*/", $key, $matches) ||
					preg_match("/^deleteImage_.*/", $key, $matches) ||
					preg_match("/^upload_.*/", $key, $matches) ||
					preg_match("/^addSuggestedSolution_.*/", $key, $matches)
					)
				{
					$force_active = true;
				}
			}
		}
		if (array_key_exists("imagemap_x", $_POST))
		{
			$force_active = true;
		}
		if (!$force_active)
		{
			$force_active = ((strtolower($this->ctrl->getCmdClass()) == strtolower(get_class($this)) || strlen($this->ctrl->getCmdClass()) == 0) &&
				$this->ctrl->getCmd() == "")
				? true
				: false;
		}
		$this->tabs_gui->addTarget("assQuestions",
			 $this->ctrl->getLinkTarget($this, "questions"),
			 array("questions", "filter", "resetFilter", "createQuestion", 
			 	"importQuestions", "deleteQuestions", "filterQuestionBrowser",
				"view", "preview", "editQuestion", "exec_pg",
				"addItem", "upload", "save", "cancel", "addSuggestedSolution",
				"cancelExplorer", "linkChilds", "removeSuggestedSolution",
				"add", "addYesNo", "addTrueFalse", "createGaps", "saveEdit",
				"setMediaMode", "uploadingImage", "uploadingImagemap", "addArea",
				"deletearea", "saveShape", "back", "addPair", "uploadingJavaapplet",
				"addParameter", "assessment", "addGIT", "addST", "addPG", "delete",
				"toggleGraphicalAnswers", "deleteAnswer", "deleteImage", "removeJavaapplet"),
			 "", "", $force_active);

		if ($ilAccess->checkAccess("read", "", $this->ref_id) || $ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->tabs_gui->addTarget("info_short",
				 $this->ctrl->getLinkTarget($this, "infoScreen"),
				array("infoScreen", "showSummary"));		
		}
		
		if ($ilAccess->checkAccess("write", "", $_GET['ref_id']))
		{
			// properties
			$this->tabs_gui->addTarget(
					'settings', $this->ctrl->getLinkTargetByClass('ilObjQuestionPoolSettingsGeneralGUI'),
					array(), array('ilObjQuestionPoolSettingsGeneralGUI', 'ilObjTaxonomyGUI')
			);

			// skill service
			if( $this->isSkillsTabRequired() )
			{
				require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentsGUI.php';

				$link = $this->ctrl->getLinkTargetByClass(
					array('ilQuestionPoolSkillAdministrationGUI', 'ilAssQuestionSkillAssignmentsGUI'),
					ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
				);

				$this->tabs_gui->addTarget('qpl_tab_competences', $link, array(), array());
			}
		}

		// print view
		$this->tabs_gui->addTarget("print_view",
			 $this->ctrl->getLinkTarget($this,'print'),
			 array("print"),
			 "", "");

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			// meta data
			include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
			$mdgui = new ilObjectMetaDataGUI($this->object);					
			$mdtab = $mdgui->getTab();
			if($mdtab)
			{			
				$this->tabs_gui->addTarget("meta_data",
					$mdtab,
					"", "ilmdeditorgui");
			}

//			$this->tabs_gui->addTarget("export",
//				 $this->ctrl->getLinkTarget($this,'export'),
//				 array("export", "createExportFile", "confirmDeleteExportFile", "downloadExportFile"),
//				 "", "");
		}

		if( $currentUserHasWriteAccess )
		{
			$this->tabs_gui->addTarget("export",
				$this->ctrl->getLinkTargetByClass("ilquestionpoolexportgui", ""),
				"", "ilquestionpoolexportgui");
		}

		if ($ilAccess->checkAccess("edit_permission", "", $this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
			$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	private function isSkillsTabRequired()
	{
		if( !($this->object instanceof ilObjQuestionPool) )
		{
			return false;
		}
		
		if( !$this->object->isSkillServiceEnabled() )
		{
			return false;
		}
		
		if( !ilObjQuestionPool::isSkillManagementGloballyActivated() )
		{
			return false;
		}
		
		return true;
	}
	
	private function addSettingsSubTabs(ilTabsGUI $tabs)
	{
		$tabs->addSubTabTarget(
				'qpl_settings_subtab_general',
				$this->ctrl->getLinkTargetByClass('ilObjQuestionPoolSettingsGeneralGUI'),
				'', 'ilObjQuestionPoolSettingsGeneralGUI'
		);
		
		$tabs->addSubTabTarget(
				'qpl_settings_subtab_taxonomies',
				$this->ctrl->getLinkTargetByClass('ilObjTaxonomyGUI', 'editAOTaxonomySettings'),
				'', 'ilObjTaxonomyGUI'
		);
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreenForward();
	}
	
	/**
	* show information screen
	*/
	function infoScreenForward()
	{
		global $DIC;
		$ilErr = $DIC['ilErr'];
		$ilAccess = $DIC['ilAccess'];
		
		if(!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();

		// standard meta data
		$info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
		
		$this->ctrl->forwardCommand($info);
	}

	/**
	* Redirect script to call a test with the question pool reference id
	* 
	* Redirect script to call a test with the question pool reference id
	*
	* @param integer $a_target The reference id of the question pool
	* @access	public
	*/
	public static function _goto($a_target)
	{
		global $DIC;
		$ilAccess = $DIC['ilAccess'];
		$ilErr = $DIC['ilErr'];
		$lng = $DIC['lng'];

		if ($ilAccess->checkAccess("write", "", $a_target) || $ilAccess->checkAccess('read', '', $a_target))
		{
			$_GET['cmdClass']  = 'ilObjQuestionPoolGUI';
			$_GET['cmd']       = 'questions';
			$_GET['baseClass'] = 'ilRepositoryGUI';
			$_GET["ref_id"]    = $a_target;
			include_once("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}
	
	/**
	 * @param array $taxIds
	 * @global ilRbacSystem  $rbacsystem
	 * @global ilDBInterface $ilDB
	 * @global ilLanguage $lng
	 * @global ilPluginAdmin $ilPluginAdmin
	 * @return ilQuestionBrowserTableGUI
	 */
	private function buildQuestionBrowserTableGUI($taxIds)
	{
		global $DIC;
		$rbacsystem = $DIC['rbacsystem'];
		$ilDB = $DIC['ilDB'];
		$lng = $DIC['lng'];
		$ilPluginAdmin = $DIC['ilPluginAdmin'];
		
		$writeAccess = (bool)$rbacsystem->checkAccess('write', $_GET['ref_id']);
		$enableCommenting = $writeAccess;

		include_once "./Modules/TestQuestionPool/classes/tables/class.ilQuestionBrowserTableGUI.php";
		$table_gui = new ilQuestionBrowserTableGUI($this, 'questions', $writeAccess, false, $taxIds, $enableCommenting);
		$table_gui->setEditable($writeAccess);

		require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
		$questionList = new ilAssQuestionList($ilDB, $lng, $ilPluginAdmin);
		$questionList->setParentObjId($this->object->getId());
		
		foreach ($table_gui->getFilterItems() as $item)
		{
			if( substr($item->getPostVar(), 0, strlen('tax_')) == 'tax_' )
			{
				$v = $item->getValue();
				
				if( is_array($v) && count($v) && !(int)$v[0] )
				{
					continue;
				}
				
				$taxId = substr($item->getPostVar(), strlen('tax_'));
				
				$questionList->addTaxonomyFilter(
					$taxId, $item->getValue(), $this->object->getId(), $this->object->getType()
				);
			}
			elseif( $item->getValue() !== false )
			{
				$questionList->addFieldFilter($item->getPostVar(), $item->getValue());
			}
		}
		
		if( $this->object->isNavTaxonomyActive() && (int)$_GET['tax_node'] )
		{
			$questionList->addTaxonomyFilter(
				$this->object->getNavTaxonomyId(), array((int)$_GET['tax_node']),
				$this->object->getId(), $this->object->getType()
			);
		}

		$questionList->load();
		$data = $questionList->getQuestionDataArray();
		
		$table_gui->setQuestionData($data);
		
		return $table_gui;
	}
	
	

} // END class.ilObjQuestionPoolGUI
?>

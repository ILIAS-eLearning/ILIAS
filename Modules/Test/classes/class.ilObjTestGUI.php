<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjTestGUI
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
*
* @ilCtrl_Calls ilObjTestGUI: ilObjCourseGUI, ilMDEditorGUI, ilTestOutputGUI
* @ilCtrl_Calls ilObjTestGUI: ilTestEvaluationGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjTestGUI: ilInfoScreenGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjTestGUI: ilCertificateGUI
* @ilCtrl_Calls ilObjTestGUI: ilTestScoringGUI, ilShopPurchaseGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjTestGUI: ilRepositorySearchGUI
* @ilCtrl_Calls ilObjTestGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI
* @ilCtrl_Calls ilObjTestGUI: assOrderingQuestionGUI, assImagemapQuestionGUI, assJavaAppletGUI
* @ilCtrl_Calls ilObjTestGUI: assNumericGUI, assErrorTextGUI
* @ilCtrl_Calls ilObjTestGUI: assTextSubsetGUI, assOrderingHorizontalGUI
* @ilCtrl_Calls ilObjTestGUI: assSingleChoiceGUI, assFileUploadGUI
* @ilCtrl_Calls ilObjTestGUI: assTextQuestionGUI, assFlashQuestionGUI
* @ilCtrl_Calls ilObjTestGUI: ilTestExpressPageObjectGUI, ilPageEditorGUI, ilPageObjectGUI
* @ilCtrl_Calls ilObjTestGUI: ilObjQuestionPoolGUI
* @ilCtrl_Calls ilObjTestGUI: ilEditClipboardGUI
*
* @extends ilObjectGUI
* @ingroup ModulesTest
*/

include_once "./classes/class.ilObjectGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./Modules/Test/classes/class.ilObjAssessmentFolderGUI.php";
include_once 'Modules/Test/classes/class.ilTestExpressPage.php';

class ilObjTestGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjTestGUI()
	{
		global $lng, $ilCtrl;
		$lng->loadLanguageModule("assessment");
		$this->type = "tst";
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, "ref_id");
		$this->ilObjectGUI("",$_GET["ref_id"], true, false);
		// Added parameter if called from crs_objectives
		if((int) $_GET['crs_show_result'])
		{
			$this->ctrl->saveParameter($this,'crs_show_result',(int) $_GET['crs_show_result']);
		}
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		global $ilAccess, $ilNavigationHistory,$ilCtrl;

		if ((!$ilAccess->checkAccess("read", "", $_GET["ref_id"])) && (!$ilAccess->checkAccess("visible", "", $_GET["ref_id"])))
		{
			global $ilias;
			$ilias->raiseError($this->lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
		}

		$cmd = $this->ctrl->getCmd("properties");
		
		$cmdsDisabledDueToOfflineStatus = array(
			'resume', 'start', 'outUserResultsOverview', 'outUserListOfAnswerPasses'		
		);
		
		if( !$this->getCreationMode() && !$this->object->isOnline() && in_array($cmd, $cmdsDisabledDueToOfflineStatus) )
		{
			$cmd = 'infoScreen';
		}

		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setReturn($this, "properties");

		if (method_exists($this->object, "getTestStyleLocation")) $this->tpl->addCss($this->object->getTestStyleLocation("output"), "screen");
		
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilObjTestGUI&cmd=infoScreen&ref_id=".$_GET["ref_id"], "tst");
		}
		
		if(!$this->getCreationMode())
		{
			if(IS_PAYMENT_ENABLED)
			{
				include_once 'Services/Payment/classes/class.ilPaymentObject.php';
				if(ilPaymentObject::_requiresPurchaseToAccess($this->object->getRefId(), $type = (isset($_GET['purchasetype']) ? $_GET['purchasetype'] : NULL) ))
				{
					$this->setLocator();
					$this->tpl->getStandardTemplate();

					include_once 'Services/Payment/classes/class.ilShopPurchaseGUI.php';
					$pp = new ilShopPurchaseGUI((int)$_GET['ref_id']);
					$ret = $this->ctrl->forwardCommand($pp);
					$this->tpl->show();
					exit();			
				}
			}
		}

                // elba hack for storing question id for inserting new question after
                if ($_REQUEST['prev_qid']) {
                    global $___prev_question_id;
                    $___prev_question_id = $_REQUEST['prev_qid'];
                    $this->ctrl->setParameter($this, 'prev_qid', $_REQUEST['prev_qid']);
                }



		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->prepareOutput();
				$this->infoScreen();	// forwards command
				break;
			case 'ilmdeditorgui':
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$this->prepareOutput();
				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;
			case "iltestoutputgui":
				include_once "./Modules/Test/classes/class.ilTestOutputGUI.php";

				if (!$this->object->getKioskMode()) $this->prepareOutput();
				$output_gui =& new ilTestOutputGUI($this->object);
				$this->ctrl->forwardCommand($output_gui);
				break;

			case "iltestevaluationgui":
				include_once "./Modules/Test/classes/class.ilTestEvaluationGUI.php";
				$this->prepareOutput();
				$evaluation_gui =& new ilTestEvaluationGUI($this->object);
				$this->ctrl->forwardCommand($evaluation_gui);
				break;
				
			case "iltestservicegui":
				include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";
				$this->prepareOutput();
				$serviceGUI =& new ilTestServiceGUI($this->object);
				$this->ctrl->forwardCommand($serviceGUI);
				break;

			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$this->prepareOutput();
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				$this->prepareOutput();
				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,$this->object->getRefId());
				$this->ctrl->forwardCommand($new_gui);

				break;

			case "ilcertificategui":
				include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
				$this->prepareOutput();
				include_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
				$output_gui = new ilCertificateGUI(new ilTestCertificateAdapter($this->object));
				$this->ctrl->forwardCommand($output_gui);
				break;

			case "iltestscoringgui":
				include_once "./Modules/Test/classes/class.ilTestScoringGUI.php";
				$this->prepareOutput();
				$output_gui = new ilTestScoringGUI($this->object);
				$this->ctrl->forwardCommand($output_gui);
				break;
				
			case 'ilobjectcopygui':
				$this->prepareOutput();
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('tst');
				$this->ctrl->forwardCommand($cp);
				break;
				
			case 'ilrepositorysearchgui':
				$this->prepareOutput();
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,
					'addParticipantsObject',
					array(
						)
					);

				// Set tabs
				$this->ctrl->setReturn($this,'participants');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				$this->tabs_gui->setTabActive('participants');
				break;

                        case 'ilpageeditorgui':
                        case 'iltestexpresspageobjectgui':
                            $qid = $_REQUEST['q_id'];

							// :FIXME: does not work
							// $this->ctrl->saveParameterByClass(array('iltestexpresspageobjectgui', 'assorderingquestiongui', 'ilpageeditorgui', 'ilpcquestion', 'ilpcquestiongui'), 'test_express_mode');

							if (!$qid) {
                                $questions = $this->object->getQuestionTitlesAndIndexes();
                                if (!is_array($questions))
                                    $questions = array();

                                $keys = array_keys($questions);
                                $qid = $keys[0];

                                $_REQUEST['q_id'] = $qid;
                                $_GET['q_id'] = $qid;
                                $_POST['q_id'] = $qid;
                            }



                            $this->prepareOutput();
			    if ($cmd != 'addQuestion')
				$this->showQuestionsPerPageObject($qid);
                            if (!$qid) {
                                include_once("./Modules/Test/classes/class.ilTestExpressPageObjectGUI.php");
				$pageObject = new ilTestExpressPageObjectGUI ("qpl", 0);
				$pageObject->test_object = $this->object;
                                $ret =& $this->ctrl->forwardCommand($pageObject);
                                break;
                            }
                            include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
                            $this->tpl->setCurrentBlock("ContentStyle");
                            $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
                                    ilObjStyleSheet::getContentStylePath(0));
                            $this->tpl->parseCurrentBlock();

                            // syntax style
                            $this->tpl->setCurrentBlock("SyntaxStyle");
                            $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
                                    ilObjStyleSheet::getSyntaxStylePath());
                            $this->tpl->parseCurrentBlock();
                            include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";

                            $q_gui =& assQuestionGUI::_getQuestionGUI("", $qid);
                            #$q_gui->setQuestionTabs();
                            $q_gui->outAdditionalOutput();
                            $q_gui->object->setObjId($this->object->getId());
                            $question =& $q_gui->object;
                            $this->ctrl->saveParameter($this, "q_id");

                            #$this->lng->loadLanguageModule("content");
                            $this->ctrl->setReturnByClass("ilTestExpressPageObjectGUI", "view");
                            $this->ctrl->setReturn($this, "questions");

                            //$page =& new ilPageObject("qpl", $_GET["q_id"]);
                            include_once("./Services/COPage/classes/class.ilPageObject.php");
                            include_once("./Modules/Test/classes/class.ilTestExpressPageObjectGUI.php");

                            $page_gui =& new ilTestExpressPageObjectGUI ("qpl", $qid);
                            $page_gui->test_object = $this->object;
                            $page_gui->setEditPreview(true);
                            $page_gui->setEnabledTabs(false);
                            $page_gui->setEnabledInternalLinks(false);
                            if (strlen($this->ctrl->getCmd()) == 0)
                            {
                                    $this->ctrl->setCmdClass(get_class($page_gui));
                                    $this->ctrl->setCmd("preview");
                            }

                            $page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(TRUE)));
                            $page_gui->setTemplateTargetVar("ADM_CONTENT");

                            $page_gui->setOutputMode($this->object->evalTotalPersons() == 0 ? "edit" : 'preview');

                            $page_gui->setHeader($question->getTitle());
                            $page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
                            $page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "fullscreen"));
                            $page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this));
                            $page_gui->setPresentationTitle($question->getTitle());
                            $ret =& $this->ctrl->forwardCommand($page_gui);

			    global $ilTabs;
			    $ilTabs->activateTab('assQuestions');

			    $this->tpl->setContent($ret);
                            break;

                        case 'ilpageobjectgui':
                            include_once ("./Services/COPage/classes/class.ilPageObjectGUI.php");
                                //echo $_REQUEST['prev_qid'];
                                if ($_REQUEST['prev_qid']) {
                                    $this->ctrl->setParameter($this, 'prev_qid', $_REQUEST['prev_qid']);
                                }

                                $this->prepareOutput();
                                //global $___test_express_mode;
                                //$___test_express_mode = true;
                                $_GET['calling_test'] = $this->object->getRefId();
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$this->tpl->setCurrentBlock("ContentStyle");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
				$this->tpl->parseCurrentBlock();

				// syntax style
				$this->tpl->setCurrentBlock("SyntaxStyle");
				$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
					ilObjStyleSheet::getSyntaxStylePath());
				$this->tpl->parseCurrentBlock();
				include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
				$q_gui =& assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
				$q_gui->setQuestionTabs();
				$q_gui->outAdditionalOutput();
				$q_gui->object->setObjId($this->object->getId());
				$question =& $q_gui->object;
				$this->ctrl->saveParameter($this, "q_id");
				include_once("./Services/COPage/classes/class.ilPageObject.php");
				include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
				$this->lng->loadLanguageModule("content");
				$this->ctrl->setReturnByClass("ilPageObjectGUI", "view");
				$this->ctrl->setReturn($this, "questions");
				//$page =& new ilPageObject("qpl", $_GET["q_id"]);
				$page_gui =& new ilPageObjectGUI("qpl", $_GET["q_id"]);
				$page_gui->setEditPreview(true);
				$page_gui->setEnabledTabs(false);
				$page_gui->setEnabledInternalLinks(false);
				if (strlen($this->ctrl->getCmd()) == 0)
				{
					$this->ctrl->setCmdClass(get_class($page_gui));
					$this->ctrl->setCmd("preview");
				}
				//$page_gui->setQuestionXML($question->toXML(false, false, true));
				$page_gui->setQuestionHTML(array($q_gui->object->getId() => $q_gui->getPreview(TRUE)));
				$page_gui->setTemplateTargetVar("ADM_CONTENT");
				$page_gui->setOutputMode($this->object->evalTotalPersons() == 0 ? "edit" : 'preview');
				$page_gui->setHeader($question->getTitle());
				$page_gui->setFileDownloadLink($this->ctrl->getLinkTarget($this, "downloadFile"));
				$page_gui->setFullscreenLink($this->ctrl->getLinkTarget($this, "fullscreen"));
				$page_gui->setSourcecodeDownloadScript($this->ctrl->getLinkTarget($this));
				$page_gui->setPresentationTitle($question->getTitle());
				$ret =& $this->ctrl->forwardCommand($page_gui);
				$this->tpl->setContent($ret);

                                break;
                        case '':
			case 'ilobjtestgui':

				$this->prepareOutput();
				if (preg_match("/deleteqpl_\d+/", $cmd))
				{
					$cmd = "randomQuestions";
				}
				if ((strcmp($cmd, "properties") == 0) && ($_GET["browse"]))
				{
					$this->questionBrowser();
					return;
				}
				if ((strcmp($cmd, "properties") == 0) && ($_GET["up"] || $_GET["down"]))
				{
					$this->questionsObject();
					return;
				}
				$cmd.= "Object";
				$ret =& $this->$cmd();
				break;
                         default:
                                // elba hack for storing question id for inserting new question after
                                if ($_REQUEST['prev_qid']) {
                                    global $___prev_question_id;
                                    $___prev_question_id = $_REQUEST['prev_qid'];
                                    $this->ctrl->setParameterByClass('ilpageobjectgui', 'prev_qid', $_REQUEST['prev_qid']);
                                    $this->ctrl->setParameterByClass($_GET['sel_question_types'] . 'gui', 'prev_qid', $_REQUEST['prev_qid']);
                                }
                                $this->create_question_mode = true;
                                $this->prepareOutput();

                                $this->ctrl->setReturn($this, "questions");
                                include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
                                $q_gui =& assQuestionGUI::_getQuestionGUI($_GET['sel_question_types'], $_GET["q_id"]);
                                $q_gui->object->setObjId($this->object->getId());
                                if (!$_GET['sel_question_types'])
                                    $qType = assQuestion::getQuestionTypeFromDb($_GET['q_id']);
                                else {
                                    $qType = $_GET['sel_question_types'];
                                }
                                $this->ctrl->setParameterByClass($qType . "GUI", 'prev_qid', $_REQUEST['prev_qid']);
                                $this->ctrl->setParameterByClass($qType . "GUI", 'test_ref_id', $_REQUEST['ref_id']);
                                $this->ctrl->setParameterByClass($qType . "GUI", 'q_id', $_REQUEST['q_id']);
				if ($_REQUEST['test_express_mode'])
				    $this->ctrl->setParameterByClass($qType . "GUI", 'test_express_mode', 1);
						
                                #global $___test_express_mode;
                                #$___test_express_mode = true;
                                if (!$q_gui->isSaveCommand())
                                    $_GET['calling_test'] = $this->object->getRefId();

                                $q_gui->setQuestionTabs();
                                #unset($___test_express_mode);
                                $ret =& $this->ctrl->forwardCommand($q_gui);
                                break;
		}
		if (strtolower($_GET["baseClass"]) != "iladministrationgui" &&
			$this->getCreationMode() != true)
		{
			$this->tpl->show();
		}
	}

	function runObject()
	{
		$this->ctrl->redirect($this, "infoScreen");
	}
	
	function outEvaluationObject()
	{
		$this->ctrl->redirectByClass("iltestevaluationgui", "outEvaluation");
	}

	/**
	* form for new test object import
	*/
	function importFileObject()
	{
		$form = $this->initImportForm($_REQUEST["new_type"]);
		if($form->checkInput())
		{
			$this->ctrl->setParameter($this, "new_type", $this->type);
			$this->uploadTstObject();
		}

		// display form to correct errors
		$form->setValuesByPost();
		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	* save object
	* @access	public
	*/
	function afterSave(ilObject $a_new_object)
	{
		if ($_POST["defaults"] > 0) 
		{
			$a_new_object->applyDefaults($_POST["defaults"]);
		}

		if($_POST['template'])
		{
			$template_id = (int)$_POST['template'];

			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template_id, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());

			$template_settings = $template->getSettings();
			if($template_settings)
			{
				$this->applyTemplate($template_settings, $a_new_object);
			}

			$a_new_object->setTemplate($template_id);
			$a_new_object->saveToDb();
		}

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);
		ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&ref_id=".
			$a_new_object->getRefId()."&cmd=properties");
	}

	function backToRepositoryObject()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$path = $this->tree->getPathFull($this->object->getRefID());
		ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
	}
	
	function backToCourseObject()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::redirect($this->getReturnLocation("cancel","./repository.php?ref_id=".(int) $_GET['crs_show_result']));
	}
	
	/*
	* list all export files
	*/
	function exportObject()
	{
		global $tree;
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);
		$data = array();
		if(count($export_files) > 0)
		{
			foreach($export_files as $exp_file)
			{
				$file_arr = explode("__", $exp_file);
				$date = new ilDateTime($file_arr[0], IL_CAL_UNIX);
				array_push($data, array(
					'file' => $exp_file,
					'size' => filesize($export_dir."/".$exp_file),
					'date' => $date->get(IL_CAL_DATETIME)
				));
			}
		}

		include_once "./Modules/Test/classes/tables/class.ilTestExportTableGUI.php";
		$table_gui = new ilTestExportTableGUI($this, 'export');
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}
	
	/**
	* create test export file
	*/
	function createTestExportObject()
	{
		global $ilAccess;
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			include_once("./Modules/Test/classes/class.ilTestExport.php");
			$test_exp = new ilTestExport($this->object, 'xml');
			$test_exp->buildExportFile();
		}
		else
		{
			ilUtil::sendInfo("cannot_export_test", TRUE);
		}
		$this->ctrl->redirect($this, "export");
	}
	
	/**
	* create results export file
	*/
	function createTestResultsExportObject()
	{
		global $ilAccess;
		
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			include_once("./Modules/Test/classes/class.ilTestExport.php");
			$test_exp = new ilTestExport($this->object, 'results');
			$test_exp->buildExportFile();
		}
		else
		{
			ilUtil::sendInfo("cannot_export_test", TRUE);
		}
		$this->ctrl->redirect($this, "export");
	}
	
	
	/**
	* download export file
	*/
	function downloadExportFileObject()
	{
		if(!isset($_POST["file"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		if (count($_POST["file"]) > 1)
		{
			ilUtil::sendInfo($this->lng->txt("select_max_one_item"), true);
			$this->ctrl->redirect($this, "export");
		}


		$export_dir = $this->object->getExportDirectory();
		ilUtil::deliverFile($export_dir."/".$_POST["file"][0],
			$_POST["file"][0]);
	}

	/**
	* confirmation screen for export file deletion
	*/
	function confirmDeleteExportFileObject()
	{
		if (!isset($_POST["file"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "export");
		}

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

		$export_dir = $this->object->getExportDirectory();
		$export_files = $this->object->getExportFiles($export_dir);
		$data = array();
		if (count($_POST["file"]) > 0)
		{
			foreach ($_POST["file"] as $exp_file)
			{
				$file_arr = explode("__", $exp_file);
				$date = new ilDateTime($file_arr[0], IL_CAL_UNIX);
				array_push($data, array(
					'file' => $exp_file,
					'size' => filesize($export_dir."/".$exp_file),
					'date' => $date->get(IL_CAL_DATETIME)
				));
			}
		}

		include_once "./Modules/Test/classes/tables/class.ilTestExportTableGUI.php";
		$table_gui = new ilTestExportTableGUI($this, 'export', true);
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject($in_rep = false)
	{
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
	}

	/**
	* cancel deletion of export files
	*/
	function cancelDeleteExportFileObject()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
		$this->ctrl->redirect($this, "export");
	}


	/**
	* delete export files
	*/
	function deleteExportFileObject()
	{
		$export_dir = $this->object->getExportDirectory();
		foreach ($_POST["file"] as $file)
		{
			$exp_file = $export_dir."/".$file;
			$exp_dir = $export_dir."/".substr($file, 0, strlen($file) - 4);
			if (@is_file($exp_file))
			{
				unlink($exp_file);
			}
			if (@is_dir($exp_dir))
			{
				ilUtil::delDir($exp_dir);
			}
		}
		ilUtil::sendSuccess($this->lng->txt('msg_deleted_export_files'), true);
		$this->ctrl->redirect($this, "export");
	}

	/**
	* imports test and question(s)
	*/
	function uploadTstObject()
	{
		if ($_FILES["xmldoc"]["error"] > UPLOAD_ERR_OK)
		{
			ilUtil::sendFailure($this->lng->txt("error_upload"));
			$this->createObject();
			return;
		}
		include_once("./Modules/Test/classes/class.ilObjTest.php");
		// create import directory
		ilObjTest::_createImportDirectory();

		// copy uploaded file to import directory
		$file = pathinfo($_FILES["xmldoc"]["name"]);
		$full_path = ilObjTest::_getImportDirectory()."/".$_FILES["xmldoc"]["name"];
		ilUtil::moveUploadedFile($_FILES["xmldoc"]["tmp_name"], $_FILES["xmldoc"]["name"], $full_path);

		// unzip file
		ilUtil::unzip($full_path);

		// determine filenames of xml files
		$subdir = basename($file["basename"],".".$file["extension"]);
		$xml_file = ilObjTest::_getImportDirectory()."/".$subdir."/".$subdir.".xml";
		$qti_file = ilObjTest::_getImportDirectory()."/".$subdir."/". str_replace("test", "qti", $subdir).".xml";
		$results_file = ilObjTest::_getImportDirectory()."/".$subdir."/". str_replace("test", "results", $subdir).".xml";

		// start verification of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($qti_file, IL_MO_VERIFY_QTI, 0, "");
		$result = $qtiParser->startParsing();
		$founditems =& $qtiParser->getFoundItems();
		
		if (count($founditems) == 0)
		{
			// nothing found

			// delete import directory
			ilUtil::delDir(ilObjTest::_getImportDirectory());

			ilUtil::sendInfo($this->lng->txt("tst_import_no_items"));
			$this->createObject();
			return;
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
			ilUtil::delDir(ilObjTest::_getImportDirectory());

			ilUtil::sendInfo($this->lng->txt("qpl_import_non_ilias_files"));
			$this->createObject();
			return;
		}
		
		$_SESSION["tst_import_results_file"] = $results_file;
		$_SESSION["tst_import_xml_file"] = $xml_file;
		$_SESSION["tst_import_qti_file"] = $qti_file;
		$_SESSION["tst_import_subdir"] = $subdir;
		// display of found questions
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tst_import_verification.html");
		$row_class = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($founditems as $item)
		{
			$this->tpl->setCurrentBlock("verification_row");
			$this->tpl->setVariable("ROW_CLASS", $row_class[$counter++ % 2]);
			$this->tpl->setVariable("QUESTION_TITLE", $item["title"]);
			$this->tpl->setVariable("QUESTION_IDENT", $item["ident"]);
			include_once "./Services/QTI/classes/class.ilQTIItem.php";
			switch ($item["type"])
			{
				case "MULTIPLE CHOICE QUESTION":
				case QT_MULTIPLE_CHOICE_MR:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("qt_multiple_choice"));
					break;
				case "SINGLE CHOICE QUESTION":
				case QT_MULTIPLE_CHOICE_SR:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assSingleChoice"));
					break;
				case "NUMERIC QUESTION":
				case QT_NUMERIC:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assNumeric"));
					break;
				case "TEXTSUBSET QUESTION":
				case QT_TEXTSUBSET:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assTextSubset"));
					break;
				case "CLOZE QUESTION":
				case QT_CLOZE:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assClozeTest"));
					break;
				case "IMAGE MAP QUESTION":
				case QT_IMAGEMAP:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assImagemapQuestion"));
					break;
				case "JAVA APPLET QUESTION":
				case QT_JAVAAPPLET:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assJavaApplet"));
					break;
				case "MATCHING QUESTION":
				case QT_MATCHING:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assMatchingQuestion"));
					break;
				case "ORDERING QUESTION":
				case QT_ORDERING:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assOrderingQuestion"));
					break;
				case "TEXT QUESTION":
				case QT_TEXT:
					$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("assTextQuestion"));
					break;
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("question_title"));
		$this->tpl->setVariable("FOUND_QUESTIONS_INTRODUCTION", $this->lng->txt("tst_import_verify_found_questions"));
		$this->tpl->setVariable("VERIFICATION_HEADING", $this->lng->txt("import_tst"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("QUESTIONPOOL_ID", $_POST["qpl"]);
		$this->tpl->setVariable("VALUE_IMPORT", $this->lng->txt("import"));
		$this->tpl->setVariable("VALUE_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* imports question(s) into the questionpool (after verification)
	*/
	function importVerifiedFileObject()
	{
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		// create new questionpool object
		$newObj = new ilObjTest(0, true);
		// set type of questionpool object
		$newObj->setType($_GET["new_type"]);
		// set title of questionpool object to "dummy"
		$newObj->setTitle("dummy");
		// set description of questionpool object
		$newObj->setDescription("test import");
		// create the questionpool class in the ILIAS database (object_data table)
		$newObj->create(true);
		// create a reference for the questionpool object in the ILIAS database (object_reference table)
		$newObj->createReference();
		// put the questionpool object in the administration tree
		$newObj->putInTree($_GET["ref_id"]);
		// get default permissions and set the permissions for the questionpool object
		$newObj->setPermissions($_GET["ref_id"]);
		// notify the questionpool object and all its parent objects that a "new" object was created
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());
		// empty mark schema
		$newObj->mark_schema->flush();

		// start parsing of QTI files
		include_once "./Services/QTI/classes/class.ilQTIParser.php";
		$qtiParser = new ilQTIParser($_SESSION["tst_import_qti_file"], IL_MO_PARSE_QTI, $_POST["qpl_id"], $_POST["ident"]);
		$qtiParser->setTestObject($newObj);
		$result = $qtiParser->startParsing();
		$newObj->saveToDb();

		// import page data
		include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser($newObj, $_SESSION["tst_import_xml_file"], $_SESSION["tst_import_subdir"]);
		$contParser->setQuestionMapping($qtiParser->getImportMapping());
		$contParser->startParsing();

		// import test results
		if (@file_exists($_SESSION["tst_import_results_file"]))
		{
			include_once ("./Modules/Test/classes/class.ilTestResultsImportParser.php");
			$results = new ilTestResultsImportParser($_SESSION["tst_import_results_file"], $newObj);
			$results->startParsing();
		}

		// delete import directory
		ilUtil::delDir(ilObjTest::_getImportDirectory());
		ilUtil::sendSuccess($this->lng->txt("object_imported"),true);
		ilUtil::redirect("ilias.php?ref_id=".$newObj->getRefId().
				"&baseClass=ilObjTestGUI");
	}
	
	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject($redirect = true)
	{
		$this->uploadTstObject();
	}

	/**
	* Displays a save confirmation dialog for test properties
	*
	* Displays a save confirmation dialog for test properties when
	* already defined questions or question pools get lost after saving
	*
	* @param int $direction Direction of the change (0 = from random test to standard, anything else = from standard to random test)
	* @access	private
	*/
	function confirmChangeProperties($direction = 0)
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_properties_save_confirmation.html", "Modules/Test");
		$information = "";
		switch ($direction)
		{
			case 0:
				$information = $this->lng->txt("change_properties_from_random_to_standard");
				break;
			default:
				$information = $this->lng->txt("change_properties_from_standard_to_random");
				break;
		}
		foreach ($_POST as $key => $value)
		{
			if (strcmp($key, "cmd") != 0)
			{
				if (is_array($value))
				{
					foreach ($value as $k => $v)
					{
						$this->tpl->setCurrentBlock("hidden_variable");
						$this->tpl->setVariable("HIDDEN_KEY", $key . "[" . $k . "]");
						$this->tpl->setVariable("HIDDEN_VALUE", $v);
						$this->tpl->parseCurrentBlock();
					}
				}
				else
				{
					$this->tpl->setCurrentBlock("hidden_variable");
					$this->tpl->setVariable("HIDDEN_KEY", $key);
					$this->tpl->setVariable("HIDDEN_VALUE", $value);
					$this->tpl->parseCurrentBlock();
				}
			}
		}
		$this->tpl->setCurrentBlock("hidden_variable");
		$this->tpl->setVariable("HIDDEN_KEY", "tst_properties_confirmation");
		$this->tpl->setVariable("HIDDEN_VALUE", "1");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_CONFIRMATION", $this->lng->txt("confirmation"));
		$this->tpl->setVariable("TXT_INFORMATION", $information);
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Save the form input of the scoring form
	*
	* @access	public
	*/
	function saveScoringObject()
	{
		$hasErrors = $this->scoringObject(true);
		if (!$hasErrors)
		{
			$total = $this->object->evalTotalPersons();
			// Check the values the user entered in the form
			if (!$total)
			{
				$this->object->setCountSystem($_POST["count_system"]);
				$this->object->setMCScoring($_POST["mc_scoring"]);
				$this->object->setScoreCutting($_POST["score_cutting"]);
				$this->object->setPassScoring($_POST["pass_scoring"]);
			}

                        /*
			$this->object->setAnswerFeedback((is_array($_POST['instant_feedback']) && in_array('instant_feedback_answer', $_POST['instant_feedback'])) ? 1 : 0);
			$this->object->setAnswerFeedbackPoints((is_array($_POST['instant_feedback']) && in_array('instant_feedback_points', $_POST['instant_feedback'])) ? 1 : 0);
			$this->object->setInstantFeedbackSolution((is_array($_POST['instant_feedback']) && in_array('instant_feedback_solution', $_POST['instant_feedback'])) ? 1 : 0);
                        */

                        /**
                         * I introduced a single setter for instant_feedback options
                         * @author jposselt at databay . de
                         */
                        $this->object->setScoringFeedbackOptionsByArray($_POST['instant_feedback']);


			$this->object->setScoreReporting($_POST["results_access"]);
			if ($this->object->getScoreReporting() == REPORT_AFTER_DATE)
			{
				$this->object->setReportingDate(sprintf("%04d%02d%02d%02d%02d%02d",
					$_POST["reporting_date"]['date']["y"],
					$_POST["reporting_date"]['date']["m"],
					$_POST["reporting_date"]['date']["d"],
					$_POST["reporting_date"]['time']["h"],
					$_POST["reporting_date"]['time']["m"],
					$_POST["reporting_date"]['time']["s"]
				));
			}
			else
			{
				$this->object->setReportingDate('');
			}

			$this->object->setShowPassDetails((is_array($_POST['results_presentation']) && in_array('pass_details', $_POST['results_presentation'])) ? 1 : 0);
			$this->object->setShowSolutionDetails((is_array($_POST['results_presentation']) && in_array('solution_details', $_POST['results_presentation'])) ? 1 : 0);
			$this->object->setShowSolutionPrintview((is_array($_POST['results_presentation']) && in_array('solution_printview', $_POST['results_presentation'])) ? 1 : 0);
			$this->object->setShowSolutionFeedback((is_array($_POST['results_presentation']) && in_array('solution_feedback', $_POST['results_presentation'])) ? 1 : 0);
			$this->object->setShowSolutionAnswersOnly((is_array($_POST['results_presentation']) && in_array('solution_answers_only', $_POST['results_presentation'])) ? 1 : 0);
			$this->object->setShowSolutionSignature((is_array($_POST['results_presentation']) && in_array('solution_signature', $_POST['results_presentation'])) ? 1 : 0);
			$this->object->setShowSolutionSuggested((is_array($_POST['results_presentation']) && in_array('solution_suggested', $_POST['results_presentation'])) ? 1 : 0);

			$this->object->setExportSettingsSingleChoiceShort((is_array($_POST['export_settings']) && in_array('exp_sc_short', $_POST['export_settings'])) ? 1 : 0);

			$this->object->saveToDb(true);
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), TRUE);
			$this->ctrl->redirect($this, "scoring");
		}
	}
	
	/**
	* Display and fill the scoring settings form of the test
	*
	* @access	public
	*/
	function scoringObject($checkonly = FALSE)
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}


		// using template?
		$template_settings = null;
		$template = $this->object->getTemplate();

		if($template)
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
                        include_once "Modules/Test/classes/class.ilObjAssessmentFolderGUI.php";

			$template = new ilSettingsTemplate($template, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());

			$template_settings = $template->getSettings();
		}

		$save = (strcmp($this->ctrl->getCmd(), "saveScoring") == 0) ? TRUE : FALSE;
		$total = $this->object->evalTotalPersons();
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("test_properties_scoring");

		// scoring properties
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("scoring"));
		$form->addItem($header);
		
		// scoring system
		$count_system = new ilRadioGroupInputGUI($this->lng->txt("tst_text_count_system"), "count_system");
		$count_system->addOption(new ilRadioOption($this->lng->txt("tst_count_partial_solutions"), 0, ''));
		$count_system->addOption(new ilRadioOption($this->lng->txt("tst_count_correct_solutions"), 1, ''));
		$count_system->setValue($this->object->getCountSystem());
		$count_system->setInfo($this->lng->txt("tst_count_system_description"));
		if ($total)
		{
			$count_system->setDisabled(true);
		}
		$form->addItem($count_system);

		// mc questions
		$mc_scoring = new ilRadioGroupInputGUI($this->lng->txt("tst_score_mcmr_questions"), "mc_scoring");
		$mc_scoring->addOption(new ilRadioOption($this->lng->txt("tst_score_mcmr_zero_points_when_unanswered"), 0, ''));
		$mc_scoring->addOption(new ilRadioOption($this->lng->txt("tst_score_mcmr_use_scoring_system"), 1, ''));
		$mc_scoring->setValue($this->object->getMCScoring());
		$mc_scoring->setInfo($this->lng->txt("tst_score_mcmr_questions_description"));
		if ($total)
		{
			$mc_scoring->setDisabled(true);
		}
		$form->addItem($mc_scoring);
		
		// score cutting
		$score_cutting = new ilRadioGroupInputGUI($this->lng->txt("tst_score_cutting"), "score_cutting");
		$score_cutting->addOption(new ilRadioOption($this->lng->txt("tst_score_cut_question"), 0, ''));
		$score_cutting->addOption(new ilRadioOption($this->lng->txt("tst_score_cut_test"), 1, ''));
		$score_cutting->setValue($this->object->getScoreCutting());
		$score_cutting->setInfo($this->lng->txt("tst_score_cutting_description"));
		if ($total)
		{
			$score_cutting->setDisabled(true);
		}
		$form->addItem($score_cutting);
		
		// pass scoring
		$pass_scoring = new ilRadioGroupInputGUI($this->lng->txt("tst_pass_scoring"), "pass_scoring");
		$pass_scoring->addOption(new ilRadioOption($this->lng->txt("tst_pass_last_pass"), 0, ''));
		$pass_scoring->addOption(new ilRadioOption($this->lng->txt("tst_pass_best_pass"), 1, ''));
		$pass_scoring->setValue($this->object->getPassScoring());
		$pass_scoring->setInfo($this->lng->txt("tst_pass_scoring_description"));
		if ($total)
		{
			$pass_scoring->setDisabled(true);
		}
		$form->addItem($pass_scoring);

		// instant feedback
		$instant_feedback = new ilCheckboxGroupInputGUI($this->lng->txt("tst_instant_feedback"), "instant_feedback");
		$instant_feedback->addOption(new ilCheckboxOption($this->lng->txt("tst_instant_feedback_answer_specific"), 'instant_feedback_answer', ''));
		$instant_feedback->addOption(new ilCheckboxOption($this->lng->txt("tst_instant_feedback_results"), 'instant_feedback_points', ''));
		$instant_feedback->addOption(new ilCheckboxOption($this->lng->txt("tst_instant_feedback_solution"), 'instant_feedback_solution', ''));
		$values = array();
		if ($this->object->getAnswerFeedback()) array_push($values, 'instant_feedback_answer');
		if ($this->object->getAnswerFeedbackPoints()) array_push($values, 'instant_feedback_points');
		if ($this->object->getInstantFeedbackSolution()) array_push($values, 'instant_feedback_solution');
		$instant_feedback->setValue($values);
		$instant_feedback->setInfo($this->lng->txt("tst_instant_feedback_description"));
		$form->addItem($instant_feedback);

		// access to test results
		$results_access = new ilRadioGroupInputGUI($this->lng->txt("tst_results_access"), "results_access");
		$results_access->addOption(new ilRadioOption($this->lng->txt("tst_results_access_finished"), 1, ''));
		$results_access->addOption(new ilRadioOption($this->lng->txt("tst_results_access_always"), 2, ''));
		$results_access->addOption(new ilRadioOption($this->lng->txt("tst_results_access_never"), 4, ''));
		$results_access->addOption(new ilRadioOption($this->lng->txt("tst_results_access_date"), 3, ''));
		$results_access->setValue($this->object->getScoreReporting());
		$results_access->setInfo($this->lng->txt("tst_results_access_description"));

		// access date
		$reporting_date = new ilDateTimeInputGUI('', 'reporting_date');
		$reporting_date->setShowDate(true);
		$reporting_date->setShowTime(true);
		if (strlen($this->object->getReportingDate()))
		{
			$reporting_date->setDate(new ilDateTime($this->object->getReportingDate(), IL_CAL_TIMESTAMP));
		}
		else
		{
			$reporting_date->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$results_access->addSubItem($reporting_date);
		$form->addItem($results_access);

		// results presentation
		$results_presentation = new ilCheckboxGroupInputGUI($this->lng->txt("tst_results_presentation"), "results_presentation");
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_pass_details"), 'pass_details', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_details"), 'solution_details', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_printview"), 'solution_printview', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_feedback"), 'solution_feedback', ''));
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_answers_only"), 'solution_answers_only', ''));
		$signatureOption = new ilCheckboxOption($this->lng->txt("tst_show_solution_signature"), 'solution_signature', '');
		$results_presentation->addOption($signatureOption);
		$results_presentation->addOption(new ilCheckboxOption($this->lng->txt("tst_show_solution_suggested"), 'solution_suggested', ''));
		$values = array();
		if ($this->object->getShowPassDetails()) array_push($values, 'pass_details');
		if ($this->object->getShowSolutionDetails()) array_push($values, 'solution_details');
		if ($this->object->getShowSolutionPrintview()) array_push($values, 'solution_printview');
		if ($this->object->getShowSolutionFeedback()) array_push($values, 'solution_feedback');
		if ($this->object->getShowSolutionAnswersOnly()) array_push($values, 'solution_answers_only');
		if ($this->object->getShowSolutionSignature()) array_push($values, 'solution_signature');
		if ($this->object->getShowSolutionSuggested()) array_push($values, 'solution_suggested');
		$results_presentation->setValue($values);
		$results_presentation->setInfo($this->lng->txt("tst_results_presentation_description"));
		if ($this->object->getAnonymity())
		{
			$signatureOption->setDisabled(true);
		}
		$form->addItem($results_presentation);

		// export settings
		$export_settings = new ilCheckboxGroupInputGUI($this->lng->txt("tst_export_settings"), "export_settings");
		$export_settings->addOption(new ilCheckboxOption($this->lng->txt("tst_exp_sc_short"), 'exp_sc_short', ''));
		$values = array();
		if ($this->object->getExportSettingsSingleChoiceShort()) array_push($values, 'exp_sc_short');
		$export_settings->setValue($values);
		$form->addItem($export_settings);
		
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form->addCommandButton("saveScoring", $this->lng->txt("save"));

		// remove items when using template
		if($template_settings)
		{
			foreach($template_settings as $id => $item)
			{
				if($item["hide"])
				{
					$form->removeItemByPostVar($id);
				}
			}
		}

                $errors = false;

		if ($save)
		{
			$errors = !$form->checkInput();
			$form->setValuesByPost();
			if ($errors) $checkonly = false;
		}
		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}
	
	/**
	* Display and fill the properties form of the test
	*
	* @access	public
	*/
	function propertiesObject($checkonly = FALSE)
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		// using template?
		$template_settings = null;
		$template = $this->object->getTemplate();

		if($template)
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());

			$template_settings = $template->getSettings();
			$hide_rte_switch = $template_settings["rte_switch"]["hide"];
		}

		$save = (strcmp($this->ctrl->getCmd(), "saveProperties") == 0) ? TRUE : FALSE;
		$total = $this->object->evalTotalPersons();
		$this->tpl->addJavascript("./Services/JavaScript/js/Basic.js");

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("test_properties");

                if(!$template || $template && $this->formShowGeneralSection($template_settings)) {
                    // general properties
                    $header = new ilFormSectionHeaderGUI();
                    $header->setTitle($this->lng->txt("tst_general_properties"));
                    $form->addItem($header);
                }

		// title & description (meta data)

		// online
		$online = new ilCheckboxInputGUI($this->lng->txt("online"), "online");
		$online->setValue(1);
		$online->setChecked($this->object->isOnline());
		$form->addItem($online);

		include_once 'Services/MetaData/classes/class.ilMD.php';
		$md_obj = new ilMD($this->object->getId(), 0, "tst");
		$md_section = $md_obj->getGeneral();

		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setRequired(true);
		$title->setValue($md_section->getTitle());
		$form->addItem($title);

		$ids = $md_section->getDescriptionIds();
		if($ids)
		{
			$desc_obj = $md_section->getDescription(array_pop($ids));

			$desc = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
			$desc->setCols(50);
			$desc->setRows(4);
			$desc->setValue($desc_obj->getDescription());
			$form->addItem($desc);
		}

		// anonymity
		/*
		 * old behaviour
		 */
		/*
		$anonymity = new ilCheckboxInputGUI($this->lng->txt("tst_anonymity"), "anonymity");
		$anonymity->setValue(1);
		if ($total) $anonymity->setDisabled(true);
		$anonymity->setChecked($this->object->getAnonymity());
		$anonymity->setInfo($this->lng->txt("tst_anonymity_description"));
		$form->addItem($anonymity);
		*/

		$anonymity = new ilRadioGroupInputGUI($this->lng->txt('tst_anonymity'), 'anonymity');
		if ($total) $anonymity->setDisabled(true);
		$rb = new ilRadioOption($this->lng->txt('tst_anonymity_no_anonymization'), 0);
		$anonymity->addOption($rb);
		$rb = new ilRadioOption($this->lng->txt('tst_anonymity_anonymous_test'), 1);
		$anonymity->addOption($rb);
		$anonymity->setValue((int)$this->object->getAnonymity());
		$form->addItem($anonymity);

		// random selection of questions
		$random = new ilCheckboxInputGUI($this->lng->txt("tst_random_selection"), "random_test");
		$random->setValue(1);
		if ($total) $random->setDisabled(true);
		$random->setChecked($this->object->isRandomTest());

		$info = $this->lng->txt("tst_random_test_description");
		if ($this->object->hasQuestionsWithoutQuestionpool()) {
		    $info .= $this->lng->txt("tst_random_test_description_warning");
		}
		$random->setInfo($info);
		$form->addItem($random);

		
/*
                $options = array(
                    'table' => $this->lng->txt("test_enable_view_table"),
                    'express' => $this->lng->txt("test_enable_view_express"),
                    'both' => $this->lng->txt("test_enable_view_both"),
                );
		$enable_views = new ilSelectInputGUI($this->lng->txt("test_enable_views"), "enabled_view_mode");
		$enable_views->setOptions($options);
                $enable_views->setValue($this->object->getEnabledViewMode());
		$form->addItem($enable_views);

                // enable usage of question pool in express mode
		$express_qpool = new ilCheckboxInputGUI($this->lng->txt("tst_express_allow_question_pool"), "express_allow_question_pool");
		$express_qpool->setValue(1);
		//if ($total) $random->setDisabled(true);
		$express_qpool->setChecked($this->object->isExpressModeQuestionPoolAllowed());
		$express_qpool->setInfo($this->lng->txt("tst_express_allow_question_pool_description"));
		$form->addItem($express_qpool);
*/
		// pool usage
		$pool_usage = new ilCheckboxInputGUI($this->lng->txt("test_question_pool_usage"), "use_pool");
		$pool_usage->setValue(1);
		$pool_usage->setChecked($this->object->getPoolUsage());
		$form->addItem($pool_usage);

                if(!$template || $template && $this->formShowBeginningEndingInformation($template_settings)) {
                    // general properties
                    $header = new ilFormSectionHeaderGUI();
                    $header->setTitle($this->lng->txt("tst_beginning_ending_information"));
                    $form->addItem($header);
                }

		// introduction
		$intro = new ilTextAreaInputGUI($this->lng->txt("tst_introduction"), "introduction");
		$intro->setValue($this->object->prepareTextareaOutput($this->object->getIntroduction()));
		$intro->setRows(10);
		$intro->setCols(80);
		$intro->setUseRte(TRUE);
		$intro->addPlugin("latex");
		$intro->addButton("latex");
		$intro->setRTESupport($this->object->getId(), "tst", "assessment");
		$intro->setRteTagSet('full');
		$intro->setInfo($this->lng->txt('intro_desc'));
		// showinfo
		$showinfo = new ilCheckboxInputGUI('', "showinfo");
		$showinfo->setValue(1);
		$showinfo->setChecked($this->object->getShowInfo());
		$showinfo->setOptionTitle($this->lng->txt("showinfo"));
		$showinfo->setInfo($this->lng->txt("showinfo_desc"));
		$intro->addSubItem($showinfo);
		$form->addItem($intro);

		// final statement
		$finalstatement = new ilTextAreaInputGUI($this->lng->txt("final_statement"), "finalstatement");
		$finalstatement->setValue($this->object->prepareTextareaOutput($this->object->getFinalStatement()));
		$finalstatement->setRows(10);
		$finalstatement->setCols(80);
		$finalstatement->setUseRte(TRUE);
		$finalstatement->addPlugin("latex");
		$finalstatement->addButton("latex");
		$finalstatement->setRTESupport($this->object->getId(), "tst", "assessment");
		$finalstatement->setRteTagSet('full');
		// show final statement
		$showfinal = new ilCheckboxInputGUI('', "showfinalstatement");
		$showfinal->setValue(1);
		$showfinal->setChecked($this->object->getShowFinalStatement());
		$showfinal->setOptionTitle($this->lng->txt("final_statement_show"));
		$showfinal->setInfo($this->lng->txt("final_statement_show_desc"));
		$finalstatement->addSubItem($showfinal);
		$form->addItem($finalstatement);

		if(!$template || $template && $this->formShowSessionSection($template_settings)) {
                    // session properties
                    $sessionheader = new ilFormSectionHeaderGUI();
                    $sessionheader->setTitle($this->lng->txt("tst_session_settings"));
                    $form->addItem($sessionheader);
                }

		// max. number of passes
		$nr_of_tries = new ilTextInputGUI($this->lng->txt("tst_nr_of_tries"), "nr_of_tries");
		$nr_of_tries->setSize(3);
		$nr_of_tries->setValue($this->object->getNrOfTries());
		$nr_of_tries->setRequired(true);
		$nr_of_tries->setSuffix($this->lng->txt("0_unlimited"));
		if ($total) $nr_of_tries->setDisabled(true);
		$form->addItem($nr_of_tries);

		// enable max. processing time
		$processing = new ilCheckboxInputGUI($this->lng->txt("tst_processing_time"), "chb_processing_time");
		$processing->setValue(1);
		//$processing->setOptionTitle($this->lng->txt("enabled"));

                if ($template_settings && $template_settings['chb_processing_time'] && $template_settings['chb_processing_time']['value'])
                    $processing->setChecked(true);
                else
                    $processing->setChecked($this->object->getEnableProcessingTime());

		// max. processing time
		$processingtime = new ilDurationInputGUI('', 'processing_time');
		$ptime = $this->object->getProcessingTimeAsArray();
		$processingtime->setHours($ptime['hh']);
		$processingtime->setMinutes($ptime['mm']);
		$processingtime->setSeconds($ptime['ss']);
		$processingtime->setShowMonths(false);
		$processingtime->setShowDays(false);
		$processingtime->setShowHours(true);
		$processingtime->setShowMinutes(true);
		$processingtime->setShowSeconds(true);
		$processingtime->setInfo($this->lng->txt("tst_processing_time_desc"));
		$processing->addSubItem($processingtime);

		// reset max. processing time
		$resetprocessing = new ilCheckboxInputGUI('', "chb_reset_processing_time");
		$resetprocessing->setValue(1);
		$resetprocessing->setOptionTitle($this->lng->txt("tst_reset_processing_time"));
		$resetprocessing->setChecked($this->object->getResetProcessingTime());
		$resetprocessing->setInfo($this->lng->txt("tst_reset_processing_time_desc"));
		$processing->addSubItem($resetprocessing);
		$form->addItem($processing);

		// enable starting time
		$enablestartingtime = new ilCheckboxInputGUI($this->lng->txt("tst_starting_time"), "chb_starting_time");
		$enablestartingtime->setValue(1);
		//$enablestartingtime->setOptionTitle($this->lng->txt("enabled"));

                if ($template_settings && $template_settings['chb_starting_time'] && $template_settings['chb_starting_time']['value'])
                    $enablestartingtime->setChecked(true);
                else
                    $enablestartingtime->setChecked(strlen($this->object->getStartingTime()));
		// starting time
		$startingtime = new ilDateTimeInputGUI('', 'starting_time');
		$startingtime->setShowDate(true);
		$startingtime->setShowTime(true);
		if (strlen($this->object->getStartingTime()))
		{
			$startingtime->setDate(new ilDateTime($this->object->getStartingTime(), IL_CAL_TIMESTAMP));
		}
		else
		{
			$startingtime->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$enablestartingtime->addSubItem($startingtime);
		if ($total) $enablestartingtime->setDisabled(true);
		if ($total) $startingtime->setDisabled(true);
		$form->addItem($enablestartingtime);

		// enable ending time
		$enableendingtime = new ilCheckboxInputGUI($this->lng->txt("tst_ending_time"), "chb_ending_time");
		$enableendingtime->setValue(1);
		//$enableendingtime->setOptionTitle($this->lng->txt("enabled"));
                if ($template_settings && $template_settings['chb_ending_time'] && $template_settings['chb_ending_time']['value'])
                    $enableendingtime->setChecked(true);
                else
                    $enableendingtime->setChecked(strlen($this->object->getEndingTime()));
		// ending time
		$endingtime = new ilDateTimeInputGUI('', 'ending_time');
		$endingtime->setShowDate(true);
		$endingtime->setShowTime(true);
		if (strlen($this->object->getEndingTime()))
		{
			$endingtime->setDate(new ilDateTime($this->object->getEndingTime(), IL_CAL_TIMESTAMP));
		}
		else
		{
			$endingtime->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		}
		$enableendingtime->addSubItem($endingtime);
		$form->addItem($enableendingtime);

		// test password
		$password = new ilTextInputGUI($this->lng->txt("tst_password"), "password");
		$password->setSize(20);
		$password->setValue($this->object->getPassword());
		$password->setInfo($this->lng->txt("tst_password_details"));
		$form->addItem($password);

		if(!$template || $template && $this->formShowPresentationSection($template_settings)) {
                    // sequence properties
                    $seqheader = new ilFormSectionHeaderGUI();
                    $seqheader->setTitle($this->lng->txt("tst_presentation_properties"));
                    $form->addItem($seqheader);
                }

		// use previous answers
		$prevanswers = new ilCheckboxInputGUI($this->lng->txt("tst_use_previous_answers"), "chb_use_previous_answers");
		$prevanswers->setValue(1);
		$prevanswers->setChecked($this->object->getUsePreviousAnswers());
		$prevanswers->setInfo($this->lng->txt("tst_use_previous_answers_description"));
		$form->addItem($prevanswers);

		// force js
		$forcejs = new ilCheckboxInputGUI($this->lng->txt("forcejs_short"), "forcejs");
		$forcejs->setValue(1);
		$forcejs->setChecked($this->object->getForceJS());
		$forcejs->setOptionTitle($this->lng->txt("forcejs"));
		$forcejs->setInfo($this->lng->txt("forcejs_desc"));
		$form->addItem($forcejs);

		// question title output
		$title_output = new ilRadioGroupInputGUI($this->lng->txt("tst_title_output"), "title_output");
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_full"), 0, ''));
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_hide_points"), 1, ''));
		$title_output->addOption(new ilRadioOption($this->lng->txt("tst_title_output_no_title"), 2, ''));
		$title_output->setValue($this->object->getTitleOutput());
		$title_output->setInfo($this->lng->txt("tst_title_output_description"));
		$form->addItem($title_output);

                if(!$template || $template && $this->formShowSequenceSection($template_settings)) {
                    // sequence properties
                    $seqheader = new ilFormSectionHeaderGUI();
                    $seqheader->setTitle($this->lng->txt("tst_sequence_properties"));
                    $form->addItem($seqheader);
                }

		// postpone questions
		$postpone = new ilCheckboxInputGUI($this->lng->txt("tst_postpone"), "chb_postpone");
		$postpone->setValue(1);
		$postpone->setChecked($this->object->getSequenceSettings());
		$postpone->setInfo($this->lng->txt("tst_postpone_description"));
		$form->addItem($postpone);
		
		// shuffle questions
		$shuffle = new ilCheckboxInputGUI($this->lng->txt("tst_shuffle_questions"), "chb_shuffle_questions");
		$shuffle->setValue(1);
		$shuffle->setChecked($this->object->getShuffleQuestions());
		$shuffle->setInfo($this->lng->txt("tst_shuffle_questions_description"));
		$form->addItem($shuffle);

		// show list of questions
		$list_of_questions = new ilCheckboxInputGUI($this->lng->txt("tst_show_summary"), "list_of_questions");
		//$list_of_questions->setOptionTitle($this->lng->txt("tst_show_summary"));
		$list_of_questions->setValue(1);
		$list_of_questions->setChecked($this->object->getListOfQuestions());
		$list_of_questions->setInfo($this->lng->txt("tst_show_summary_description"));

		$list_of_questions_options = new ilCheckboxGroupInputGUI('', "list_of_questions_options");
		$list_of_questions_options->addOption(new ilCheckboxOption($this->lng->txt("tst_list_of_questions_start"), 'chb_list_of_questions_start', ''));
		$list_of_questions_options->addOption(new ilCheckboxOption($this->lng->txt("tst_list_of_questions_end"), 'chb_list_of_questions_end', ''));
		$list_of_questions_options->addOption(new ilCheckboxOption($this->lng->txt("tst_list_of_questions_with_description"), 'chb_list_of_questions_with_description', ''));
		$values = array();
		if ($this->object->getListOfQuestionsStart()) array_push($values, 'chb_list_of_questions_start');
		if ($this->object->getListOfQuestionsEnd()) array_push($values, 'chb_list_of_questions_end');
		if ($this->object->getListOfQuestionsDescription()) array_push($values, 'chb_list_of_questions_with_description');
		$list_of_questions_options->setValue($values);

		$list_of_questions->addSubItem($list_of_questions_options);
		$form->addItem($list_of_questions);

		// show question marking
		$marking = new ilCheckboxInputGUI($this->lng->txt("question_marking"), "chb_show_marker");
		$marking->setValue(1);
		$marking->setChecked($this->object->getShowMarker());
		$marking->setInfo($this->lng->txt("question_marking_description"));
		$form->addItem($marking);

		// show suspend test
		$cancel = new ilCheckboxInputGUI($this->lng->txt("tst_show_cancel"), "chb_show_cancel");
		$cancel->setValue(1);
		$cancel->setChecked($this->object->getShowCancel());
		$cancel->setInfo($this->lng->txt("tst_show_cancel_description"));
		$form->addItem($cancel);

                if(!$template || $template && $this->formShowNotificationSection($template_settings)) {
                    // notifications
                    $notifications = new ilFormSectionHeaderGUI();
                    $notifications->setTitle($this->lng->txt("tst_mail_notification"));
                    $form->addItem($notifications);
                }

		// mail notification
		$mailnotification = new ilRadioGroupInputGUI($this->lng->txt("tst_finish_notification"), "mailnotification");
		$mailnotification->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_no"), 0, ''));
		$mailnotification->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_simple"), 1, ''));
		$mailnotification->addOption(new ilRadioOption($this->lng->txt("tst_finish_notification_advanced"), 2, ''));
		$mailnotification->setValue($this->object->getMailNotification());
		$form->addItem($mailnotification);

		$mailnottype = new ilCheckboxInputGUI('', "mailnottype");
		$mailnottype->setValue(1);
		$mailnottype->setOptionTitle($this->lng->txt("mailnottype"));
		$mailnottype->setChecked($this->object->getMailNotificationType());
		$form->addItem($mailnottype);


                if(!$template || $template && $this->formShowKioskSection($template_settings)) {
                    // kiosk mode properties
                    $kioskheader = new ilFormSectionHeaderGUI();
                    $kioskheader->setTitle($this->lng->txt("kiosk"));
                    $form->addItem($kioskheader);
                }

		// kiosk mode
		$kiosk = new ilCheckboxInputGUI($this->lng->txt("kiosk"), "kiosk");
		$kiosk->setValue(1);
		$kiosk->setChecked($this->object->getKioskMode());
		$kiosk->setInfo($this->lng->txt("kiosk_description"));

		// kiosk mode options
		$kiosktitle = new ilCheckboxGroupInputGUI($this->lng->txt("kiosk_options"), "kiosk_options");
		$kiosktitle->addOption(new ilCheckboxOption($this->lng->txt("kiosk_show_title"), 'kiosk_title', ''));
		$kiosktitle->addOption(new ilCheckboxOption($this->lng->txt("kiosk_show_participant"), 'kiosk_participant', ''));
		$values = array();
		if ($this->object->getShowKioskModeTitle()) array_push($values, 'kiosk_title');
		if ($this->object->getShowKioskModeParticipant()) array_push($values, 'kiosk_participant');
		$kiosktitle->setValue($values);
		$kiosktitle->setInfo($this->lng->txt("kiosk_options_desc"));
		$kiosk->addSubItem($kiosktitle);

		$form->addItem($kiosk);

                if(!$template || $template && $this->formShowParticipantSection($template_settings)) {
                    // participants properties
                    $restrictions = new ilFormSectionHeaderGUI();
                    $restrictions->setTitle($this->lng->txt("tst_max_allowed_users"));
                    $form->addItem($restrictions);
                }

		$fixedparticipants = new ilCheckboxInputGUI($this->lng->txt('participants_invitation'), "fixedparticipants");
		$fixedparticipants->setValue(1);
		$fixedparticipants->setChecked($this->object->getFixedParticipants());
		$fixedparticipants->setOptionTitle($this->lng->txt("tst_allow_fixed_participants"));
		$fixedparticipants->setInfo($this->lng->txt("participants_invitation_description"));
		$invited_users = $this->object->getInvitedUsers();
		if ($total && (count($invited_users) == 0))
		{
			$fixedparticipants->setDisabled(true);
		}
		$form->addItem($fixedparticipants);


		// simultaneous users
		$simul = new ilTextInputGUI($this->lng->txt("tst_allowed_users"), "allowedUsers");
		$simul->setSize(3);
		$simul->setValue(($this->object->getAllowedUsers()) ? $this->object->getAllowedUsers() : '');
		$form->addItem($simul);

		// idle time
		$idle = new ilTextInputGUI($this->lng->txt("tst_allowed_users_time_gap"), "allowedUsersTimeGap");
		$idle->setSize(4);
		$idle->setSuffix($this->lng->txt("seconds"));
		$idle->setValue(($this->object->getAllowedUsersTimeGap()) ? $this->object->getAllowedUsersTimeGap() : '');
		$form->addItem($idle);

		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) $form->addCommandButton("saveProperties", $this->lng->txt("save"));

		// remove items when using template
		if($template_settings)
		{
			foreach($template_settings as $id => $item)
			{
				if($item["hide"])
				{
					$form->removeItemByPostVar($id);
				}
			}
		}

		$errors = false;
		
		if ($save)
		{
			$errors = !$form->checkInput();
			$form->setValuesByPost();
			if( $online->getChecked() && !$this->object->isComplete() )
			{
				$online->setAlert($this->lng->txt("cannot_switch_to_online_no_questions_andor_no_mark_steps"));
				ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
				$errors = true;
			}
			if ($errors) $checkonly = false;
		}

		if (!$checkonly)
		{
			// using template?
			$message = "";
			if($template)
			{
				global $tpl;

				$link = $this->ctrl->getLinkTarget($this, "confirmResetTemplate");
				$link = "<a href=\"".$link."\">".$this->lng->txt("test_using_template_link")."</a>";
				$message = "<div style=\"margin-top:10px\">".
					$tpl->getMessageHTML(sprintf($this->lng->txt("test_using_template"), $template->getTitle(), $link), "info").
					"</div>";
			}
	
			$this->tpl->setVariable("ADM_CONTENT", $form->getHTML().$message);
		}

		return $errors;
	}
	
	/**
	* Save the form input of the properties form
	*
	* @access	public
	*/
	function savePropertiesObject()
	{
		if (!array_key_exists("tst_properties_confirmation", $_POST))
		{
			$hasErrors = $this->propertiesObject(true);
		}
		else
		{
			$hasErrors = false;
		}
		if (!$hasErrors)
		{
                        $template_settings = null;
			$template = $this->object->getTemplate();
			if($template)
			{
				include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
				$template = new ilSettingsTemplate($template, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());

				$template_settings = $template->getSettings();
			}



			include_once 'Services/MetaData/classes/class.ilMD.php';
			$md_obj =& new ilMD($this->object->getId(), 0, "svy");
			$md_section = $md_obj->getGeneral();

			// title
			$md_section->setTitle(ilUtil::stripSlashes($_POST['title']));
			$md_section->update();

			// Description
			$md_desc_ids = $md_section->getDescriptionIds();
			if($md_desc_ids)
			{
				$md_desc = $md_section->getDescription(array_pop($md_desc_ids));
				$md_desc->setDescription(ilUtil::stripSlashes($_POST['description']));
				$md_desc->update();
			}
                        else {
                            $md_desc = $md_section->addDescription();
			    $md_desc->setDescription(ilUtil::stripSlashes($_POST['description']));
			    $md_desc->save();
                        }

			$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
			$this->object->setDescription(ilUtil::stripSlashes($_POST['description']));
			$this->object->update();

			$total = $this->object->evalTotalPersons();
			$randomtest_switch = false;
			// Check the values the user entered in the form
			if (!$total)
			{
				if (!array_key_exists("tst_properties_confirmation", $_POST))
				{
					if (($this->object->isRandomTest()) && (count($this->object->getRandomQuestionpools()) > 0))
					{
						if (!$_POST["random_test"])
						{
							// user tries to change from a random test with existing random question pools to a non random test
							$this->confirmChangeProperties(0);
							return;
						}
					}
					if ((!$this->object->isRandomTest()) && (count($this->object->questions) > 0))
					{
						if ($_POST["random_test"])
						{
							// user tries to change from a non random test with existing questions to a random test
							$this->confirmChangeProperties(1);
							return;
						}
					}
				}

				if (!strlen($_POST["random_test"]))
				{
					$random_test = 0;
				}
				else
				{
					$random_test = $_POST["random_test"];
				}
			}
			else
			{
				$random_test = $this->object->isRandomTest();
			}
			if ($random_test != $this->object->isRandomTest())
			{
				$randomtest_switch = true;
			}

			// buffer online status sent by form in local variable and store
			// it to model after the following if block, because the new status
			// gets reset when random test setting is switched
			$online = $_POST["online"];
			
			if (!$total)
			{
				if( $randomtest_switch && $this->object->isOnline() && $online )
				{
					// reset online status that is stored to model later on
					// due to fact that the random test setting has been changed
					$online = false;

					$info = $this->lng->txt(
						"tst_set_offline_due_to_switched_random_test_setting"
					);

					ilUtil::sendInfo($info, true);
				}

				$this->object->setAnonymity($_POST["anonymity"]);
				$this->object->setRandomTest($random_test);
				$this->object->setNrOfTries($_POST["nr_of_tries"]);
				if ($_POST['chb_starting_time'])
				{
					$this->object->setStartingTime(ilFormat::dateDB2timestamp($_POST['starting_time']['date'] . ' ' . $_POST['starting_time']['time']));
				}
				else
				{
					$this->object->setStartingTime('');
				}
			}

			// store effective online status to model
			$this->object->setOnline($online);

			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$this->object->setIntroduction($_POST["introduction"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
			$this->object->setShowInfo(($_POST["showinfo"]) ? 1 : 0);
			$this->object->setFinalStatement($_POST["finalstatement"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
			$this->object->setShowFinalStatement(($_POST["showfinalstatement"]) ? 1 : 0);
			$this->object->setSequenceSettings(($_POST["chb_postpone"]) ? 1 : 0);
			$this->object->setShuffleQuestions(($_POST["chb_shuffle_questions"]) ? 1 : 0);
			$this->object->setListOfQuestions($_POST["list_of_questions"]);
			if (is_array($_POST["list_of_questions_options"]))
			{
				$this->object->setListOfQuestionsStart((in_array('chb_list_of_questions_start', $_POST["list_of_questions_options"])) ? 1 : 0);
				$this->object->setListOfQuestionsEnd((in_array('chb_list_of_questions_end', $_POST["list_of_questions_options"])) ? 1 : 0);
				$this->object->setListOfQuestionsDescription((in_array('chb_list_of_questions_with_description', $_POST["list_of_questions_options"])) ? 1 : 0);
			}
			else
			{
				$this->object->setListOfQuestionsStart(0);
				$this->object->setListOfQuestionsEnd(0);
				$this->object->setListOfQuestionsDescription(0);
			}
			$this->object->setMailNotification($_POST["mailnotification"]);
			$this->object->setMailNotificationType($_POST["mailnottype"]);
			$this->object->setShowMarker(($_POST["chb_show_marker"]) ? 1 : 0);
			$this->object->setShowCancel(($_POST["chb_show_cancel"]) ? 1 : 0);
			$this->object->setKioskMode(($_POST["kiosk"]) ? 1 : 0);
			$this->object->setShowKioskModeTitle((is_array($_POST["kiosk_options"]) && in_array('kiosk_title', $_POST["kiosk_options"])) ? 1 : 0);
			$this->object->setShowKioskModeParticipant((is_array($_POST["kiosk_options"]) && in_array('kiosk_participant', $_POST["kiosk_options"])) ? 1 : 0);
			$this->object->setEnableProcessingTime(($_POST["chb_processing_time"]) ? 1 : 0);
			if ($this->object->getEnableProcessingTime())
			{
				$this->object->setProcessingTime(sprintf("%02d:%02d:%02d",
					$_POST["processing_time"]["hh"],
					$_POST["processing_time"]["mm"],
					$_POST["processing_time"]["ss"]
				));
			}
			else
			{
				$this->object->setProcessingTime('');
			}
			$this->object->setResetProcessingTime(($_POST["chb_reset_processing_time"]) ? 1 : 0);
			if ($_POST['chb_ending_time'])
			{
				$this->object->setEndingTime(ilFormat::dateDB2timestamp($_POST['ending_time']['date'] . ' ' . $_POST['ending_time']['time']));
			}
			else
			{
				$this->object->setEndingTime('');
			}
			$this->object->setUsePreviousAnswers(($_POST["chb_use_previous_answers"]) ? 1 : 0);
			$this->object->setForceJS(($_POST["forcejs"]) ? 1 : 0);
			$this->object->setTitleOutput($_POST["title_output"]);
			$this->object->setPassword($_POST["password"]);
			$this->object->setAllowedUsers($_POST["allowedUsers"]);
			$this->object->setAllowedUsersTimeGap($_POST["allowedUsersTimeGap"]);

			if ($this->object->isRandomTest())
			{
				$this->object->setUsePreviousAnswers(0);
			}

			$invited_users = $this->object->getInvitedUsers();
			if (!($total && (count($invited_users) == 0)))
			{
				$fixed_participants = 0;
				if (array_key_exists("fixedparticipants", $_POST))
				{
					if ($_POST["fixedparticipants"])
					{
						$fixed_participants = 1;
					}
				}
				$this->object->setFixedParticipants($fixed_participants);
				if (!$fixed_participants)
				{
					$invited_users = $this->object->getInvitedUsers();
					foreach ($invited_users as $user_object)
					{
						$this->object->disinviteUser($user_object["usr_id"]);
					}
				}
			}

            //$this->object->setExpressModeQuestionPoolAllowed($_POST['express_allow_question_pool']);
            $this->object->setEnabledViewMode($_POST['enabled_view_mode']);
            $this->object->setPoolUsage($_POST['use_pool']);

			$this->object->saveToDb(true);

			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			if ($randomtest_switch)
			{
				if ($this->object->isRandomTest())
				{
					$this->object->removeNonRandomTestData();
				}
				else
				{
					$this->object->removeRandomTestData();
				}
			}

			$this->ctrl->redirect($this, 'properties');
		}
	}
	
	/**
	* download file
	*/
	function downloadFileObject()
	{
		$file = explode("_", $_GET["file_id"]);
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}
	
	/**
	* show fullscreen view
	*/
	function fullscreenObject()
	{
		include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
		//$page =& new ilPageObject("qpl", $_GET["pg_id"]);
		$page_gui =& new ilPageObjectGUI("qpl", $_GET["pg_id"]);
		$page_gui->showMediaFullscreen();
		
	}

	/**
	* download source code paragraph
	*/
	function download_paragraphObject()
	{
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		$pg_obj =& new ilPageObject("qpl", $_GET["pg_id"]);
		$pg_obj->send_paragraph ($_GET["par_id"], $_GET["downloadtitle"]);
		exit;
	}

	/**
	* Sets the filter for the question browser 
	*
	* Sets the filter for the question browser 
	*
	* @access	public
	*/
	function filterObject()
	{
		$this->questionBrowser();
	}

	/**
	* Resets the filter for the question browser 
	*
	* Resets the filter for the question browser 
	*
	* @access	public
	*/
	function resetFilterObject()
	{
		$this->questionBrowser();
	}

	/**
	* Called when the back button in the question browser was pressed 
	*
	* Called when the back button in the question browser was pressed 
	*
	* @access	public
	*/
	function backObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Creates a new questionpool and returns the reference id
	*
	* Creates a new questionpool and returns the reference id
	*
	* @return integer Reference id of the newly created questionpool
	* @access	public
	*/
	function createQuestionPool($name = "dummy", $description = "")
	{
		global $tree;
		$parent_ref = $tree->getParentId($this->object->getRefId());
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$qpl = new ilObjQuestionPool();
		$qpl->setType("qpl");
		$qpl->setTitle($name);
		$qpl->setDescription($description);
		$qpl->create();
		$qpl->createReference();
		$qpl->putInTree($parent_ref);
		$qpl->setPermissions($parent_ref);
		$qpl->setOnline(1); // must be online to be available
		$qpl->saveToDb();
		return $qpl->getRefId();
	}

	/**
	* Creates a form for random selection of questions
	*/
	public function randomselectObject()
	{
		global $ilUser;
		$this->getQuestionsSubTabs();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_select.html", "Modules/Test");
		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, FALSE, FALSE, TRUE);
		$this->tpl->setCurrentBlock("option");
		$this->tpl->setVariable("VALUE_OPTION", "0");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("all_available_question_pools"));
		$this->tpl->parseCurrentBlock();
		foreach ($questionpools as $key => $value)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_OPTION", $key);
			$this->tpl->setVariable("TEXT_OPTION", $value["title"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "sel_question_types");
		$this->tpl->setVariable("HIDDEN_VALUE", $_POST["sel_question_types"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_random_select_questionpool"));
		$this->tpl->setVariable("TXT_NR_OF_QUESTIONS", $this->lng->txt("tst_random_nr_of_questions"));
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Cancels the form for random selection of questions
	*
	* Cancels the form for random selection of questions
	*
	* @access	public
	*/
	function cancelRandomSelectObject()
	{
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Offers a random selection for insertion in the test
	*
	* Offers a random selection for insertion in the test
	*
	* @access	public
	*/
	function createRandomSelectionObject()
	{
		$this->getQuestionsSubTabs();
		$question_array = $this->object->randomSelectQuestions($_POST["nr_of_questions"], $_POST["sel_qpl"]);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_question_offer.html", "Modules/Test");
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$questionpools =& $this->object->getAvailableQuestionpools(true);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		foreach ($question_array as $question_id)
		{
			$dataset = $this->object->getQuestionDataset($question_id);
			$this->tpl->setCurrentBlock("QTab");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->setVariable("QUESTION_TITLE", $dataset->title);
			$this->tpl->setVariable("QUESTION_COMMENT", $dataset->description);
			$this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($dataset->type_tag));
			$this->tpl->setVariable("QUESTION_AUTHOR", $dataset->author);
			$this->tpl->setVariable("QUESTION_POOL", $questionpools[$dataset->obj_fi]["title"]);
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if (count($question_array) == 0)
		{
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_NO_QUESTIONS_AVAILABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		}
			else
		{
			$this->tpl->setCurrentBlock("Selectionbuttons");
			$this->tpl->setVariable("BTN_YES", $this->lng->txt("random_accept_sample"));
			$this->tpl->setVariable("BTN_NO", $this->lng->txt("random_another_sample"));
			$this->tpl->parseCurrentBlock();
		}
		$chosen_questions = join($question_array, ",");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("QUESTION_POOL", $this->lng->txt("qpl"));
		$this->tpl->setVariable("VALUE_CHOSEN_QUESTIONS", $chosen_questions);
		$this->tpl->setVariable("VALUE_QUESTIONPOOL_SELECTION", $_POST["sel_qpl"]);
		$this->tpl->setVariable("VALUE_NR_OF_QUESTIONS", $_POST["nr_of_questions"]);
		$this->tpl->setVariable("TEXT_QUESTION_OFFER", $this->lng->txt("tst_question_offer"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Inserts a random selection into the test
	*
	* Inserts a random selection into the test
	*
	* @access	public
	*/
	function insertRandomSelectionObject()
	{
		$selected_array = split(",", $_POST["chosen_questions"]);
		if (!count($selected_array))
		{
			ilUtil::sendInfo($this->lng->txt("tst_insert_missing_question"));
		}
		else
		{
			$total = $this->object->evalTotalPersons();
			if ($total)
			{
				// the test was executed previously
				ilUtil::sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("tst_insert_questions"));
			}
			foreach ($selected_array as $key => $value) 
			{
				$this->object->insertQuestion($value);
			}
			$this->object->saveCompleteStatus();
			ilUtil::sendSuccess($this->lng->txt("tst_questions_inserted"), true);
			$this->ctrl->redirect($this, "questions");
			return;
		}
	}
	
	function addQuestionpoolObject()
	{
		$this->randomQuestionsObject();
	}
	
	/**
	* Evaluates a posted random question form and saves the form data
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writeRandomQuestionInput($always = false)
	{
		$hasErrors = (!$always) ? $this->randomQuestionsObject(true) : false;
		if (!$hasErrors)
		{
			global $ilUser;
			$ilUser->setPref("tst_question_selection_mode_equal", ($_POST['chbQuestionSelectionMode']) ? 1 : 0);
			$ilUser->writePref("tst_question_selection_mode_equal", ($_POST['chbQuestionSelectionMode']) ? 1 : 0);
			$this->object->setRandomQuestionCount($_POST['total_questions']);
			if (is_array($_POST['source']['qpl']) && count(array_unique($_POST['source']['qpl'])) == count($_POST['source']['qpl']))
			{
				$data = array();
				include_once "./Modules/Test/classes/class.ilRandomTestData.php";
				foreach ($_POST['source']['qpl'] as $idx => $qpl)
				{
					array_push($data, new ilRandomTestData($_POST['source']['count'][$idx], $qpl));
				}
				$this->object->setRandomQuestionpoolData($data);
			}
			return 0;
		}
		return 1;
	}

	function saveRandomQuestionsObject()
	{
		if ($this->writeRandomQuestionInput() == 0)
		{
			$this->object->saveRandomQuestionCount($this->object->getRandomQuestionCount());
			$this->object->saveRandomQuestionpools();
			$this->object->saveCompleteStatus();
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
			$this->ctrl->redirect($this, 'randomQuestions');
		}
	}
		
	function addsourceObject()
	{
		$this->writeRandomQuestionInput(true);
		$position = key($_POST['cmd']['addsource']);
		$this->object->addRandomQuestionpoolData(0, 0, $position+1);
		$this->randomQuestionsObject();
	}
	
	function removesourceObject()
	{
		$this->writeRandomQuestionInput(true);
		$position = key($_POST['cmd']['removesource']);
		$this->object->removeRandomQuestionpoolData($position);
		$this->randomQuestionsObject();
	}

	function randomQuestionsObject()
	{
		global $ilUser;

		$total = $this->object->evalTotalPersons();
		$save = (strcmp($this->ctrl->getCmd(), "saveRandomQuestions") == 0) ? TRUE : FALSE;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'randomQuestions'));
		$form->setTitle($this->lng->txt('random_selection'));
		$form->setDescription($this->lng->txt('tst_select_random_questions'));
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("randomSelectionForm");

		// question selection
		$selection_mode = ($save) ? $_POST['chbQuestionSelectionMode'] : $ilUser->getPref("tst_question_selection_mode_equal");
		$question_selection = new ilCheckboxInputGUI($this->lng->txt("tst_question_selection"), "chbQuestionSelectionMode");
		$question_selection->setValue(1);
		$question_selection->setChecked($selection_mode);
		$question_selection->setOptionTitle($this->lng->txt('tst_question_selection_equal'));
		$question_selection->setInfo($this->lng->txt('tst_question_selection_description'));
		$question_selection->setRequired(false);
		$form->addItem($question_selection);
		
		// total amount of questions
		$total_questions = new ilNumberInputGUI($this->lng->txt('tst_total_questions'), 'total_questions');
		$total_questions->setValue($this->object->getRandomQuestionCount());
		$total_questions->setSize(3);
		$total_questions->setInfo($this->lng->txt('tst_total_questions_description'));
		$total_questions->setRequired(false);
		$form->addItem($total_questions);

		if ($total == 0)
		{
			$found_qpls = $this->object->getRandomQuestionpoolData();
			include_once "./Modules/Test/classes/class.ilRandomTestData.php";
			if (count($found_qpls) == 0)
			{
				array_push($found_qpls, new ilRandomTestData());
			}
			$available_qpl =& $this->object->getAvailableQuestionpools(TRUE, $selection_mode, FALSE, TRUE, TRUE);
			include_once './Modules/Test/classes/class.ilRandomTestInputGUI.php';
			$source = new ilRandomTestInputGUI($this->lng->txt('tst_random_questionpools'), 'source');
			$source->setUseEqualPointsOnly($selection_mode);
			$source->setRandomQuestionPools($available_qpl);
			$source->setUseQuestionCount((array_key_exists('total_questions', $_POST)) ? ($_POST['total_questions'] < 1) : ($this->object->getRandomQuestionCount() < 1));
			$source->setValues($found_qpls);
			$form->addItem($source);
		}
		else
		{
			$qpls = $this->object->getUsedRandomQuestionpools();
			include_once './Modules/Test/classes/class.ilRandomTestROInputGUI.php';
			$source = new ilRandomTestROInputGUI($this->lng->txt('tst_random_questionpools'), 'source');
			$source->setValues($qpls);
			$form->addItem($source);
		}

		if ($total == 0) $form->addCommandButton("saveRandomQuestions", $this->lng->txt("save"));
	
		$errors = false;
	
		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			if (!$errors)
			{
				// check total amount of questions
				if ($_POST['total_questions'] > 0)
				{
					$totalcount = 0;
					foreach ($_POST['source']['qpl'] as $idx => $qpl)
					{
						$totalcount += $available_qpl[$qpl]['count'];
					}
					if ($_POST['total_questions'] > $totalcount)
					{
						$total_questions->setAlert($this->lng->txt('msg_total_questions_too_high'));
						$errors = true;
					}
				}
			}
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		return $errors;
	}
	
	function saveQuestionSelectionModeObject()
	{
		global $ilUser;
		if ($_POST["chbQuestionSelectionMode"])
		{
			$ilUser->setPref("tst_question_selection_mode_equal", 1);
			$ilUser->writePref("tst_question_selection_mode_equal", 1);
		}
		else
		{
			$ilUser->setPref("tst_question_selection_mode_equal", 0);
			$ilUser->writePref("tst_question_selection_mode_equal", 0);
		}
		$this->randomQuestionsObject();
	}

	function browseForQuestionsObject()
	{
		$this->questionBrowser();
	}
	
	/**
	* Called when a new question should be created from a test after confirmation
	*
	* Called when a new question should be created from a test after confirmation
	*
	* @access	public
	*/
	function executeCreateQuestionObject()
	{
		$qpl_ref_id = $_REQUEST["sel_qpl"];

		$qpl_mode = $_REQUEST['usage'];

		if (!$qpl_mode || ($qpl_mode == 2 && strcmp($_REQUEST["txt_qpl"], "") == 0) || ($qpl_mode == 3 && strcmp($qpl_ref_id, "") == 0))
		//if ((strcmp($_REQUEST["txt_qpl"], "") == 0) && (strcmp($qpl_ref_id, "") == 0))
		{
			ilUtil::sendInfo($this->lng->txt("questionpool_not_entered"));
			$this->createQuestionObject();
			return;
		}
		else
		{
			$_SESSION["test_id"] = $this->object->getRefId();
			if ($qpl_mode == 2 && strcmp($_REQUEST["txt_qpl"], "") != 0)
			{
				// create a new question pool and return the reference id
				$qpl_ref_id = $this->createQuestionPool($_REQUEST["txt_qpl"]);
			}
			else if ($qpl_mode == 1)
			{
			    $qpl_ref_id = $_GET["ref_id"];
			}

			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPoolGUI.php";

			$baselink = "ilias.php?baseClass=ilObjQuestionPoolGUI&ref_id=" . $qpl_ref_id . "&cmd=createQuestionForTest&test_ref_id=".$_GET["ref_id"]."&calling_test=".$_GET["ref_id"]."&sel_question_types=" . $_REQUEST["sel_question_types"];

			if ($_REQUEST['q_id']) {
			    $baselink .= '&q_id=' . $_REQUEST['q_id'];
			}
			if ($_REQUEST['prev_qid']) {
			    $baselink .= '&prev_qid=' . $_REQUEST['prev_qid'];
			}
			if ($_REQUEST['test_express_mode']) {
			    $baselink .= '&test_express_mode=1';
			}
#var_dump($_REQUEST['prev_qid']);
			ilUtil::redirect($baselink);
			
			exit();
		}
	}

	/**
	* Called when the creation of a new question is cancelled
	*
	* Called when the creation of a new question is cancelled
	*
	* @access	public
	*/
	function cancelCreateQuestionObject()
	{
		$this->ctrl->redirect($this, "questions");
	}

	/**
	* Called when a new question should be created from a test
	*
	* Called when a new question should be created from a test
	*
	* @access	public
	*/
	function createQuestionObject()
	{
		global $ilUser;
		$this->getQuestionsSubTabs();
		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_qpl_select.html", "Modules/Test");
		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, FALSE, FALSE, TRUE, FALSE, "write");
		if ($this->object->getPoolUsage()) {
		    global $lng, $ilCtrl, $tpl;

		    include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

		    $form = new ilPropertyFormGUI();
		    $form->setFormAction($ilCtrl->getFormAction($this, "executeCreateQuestion"));
		    $form->setTitle($lng->txt("test_add_new_question"));
		    include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';


		    $hidden = new ilHiddenInputGUI('sel_question_types');
		    $hidden->setValue($_REQUEST["sel_question_types"]);
		    $form->addItem($hidden);

		    // use pool
		    $usage = new ilRadioGroupInputGUI($this->lng->txt("assessment_pool_selection"), "usage");
		    $usage->setRequired(true);
		    $no_pool = new ilRadioOption($this->lng->txt("assessment_no_pool"), 1);
		    $usage->addOption($no_pool);
		    $existing_pool = new ilRadioOption($this->lng->txt("assessment_existing_pool"), 3);
		    $usage->addOption($existing_pool);
		    $new_pool = new ilRadioOption($this->lng->txt("assessment_new_pool"), 2);
		    $usage->addOption($new_pool);
		    $form->addItem($usage);

		    $usage->setValue(1);

		    $questionpools = ilObjQuestionPool::_getAvailableQuestionpools(FALSE, FALSE, TRUE, FALSE, FALSE, "write");
		    $pools_data = array();
		    foreach($questionpools as $key => $p) {
			$pools_data[$key] = $p['title'];
		    }
		    $pools = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "sel_qpl");
		    $pools->setOptions($pools_data);
		    $existing_pool->addSubItem($pools);


		    $name = new ilTextInputGUI($this->lng->txt("cat_create_qpl"), "txt_qpl");
		    $name->setSize(50);
		    $name->setMaxLength(50);
		    $new_pool->addSubItem($name);

		    $form->addCommandButton("executeCreateQuestion", $lng->txt("submit"));
		    $form->addCommandButton("cancelCreateQuestion", $lng->txt("cancel"));

		    return $this->tpl->setVariable('ADM_CONTENT', $form->getHTML());

		}
		else {
		    global $ilCtrl;

		    $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'sel_question_types', $_REQUEST["sel_question_types"]);
		    $link = $ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'handleToolbarCommand','',false,false);
		    ilUtil::redirect($link);
		}
	}

	/**
	* Remove questions from the test after confirmation
	*
	* Remove questions from the test after confirmation
	*
	* @access	public
	*/
	function confirmRemoveQuestionsObject()
	{
		ilUtil::sendSuccess($this->lng->txt("tst_questions_removed"));
		$checked_questions = array();

		foreach ($_POST as $key => $value) {
			if (preg_match("/id_(\d+)/", $key, $matches)) {
				array_push($checked_questions, $matches[1]);
			}
		}

		$questions = $this->object->getQuestionTitlesAndIndexes();
		$first = null;
		
		foreach ($checked_questions as $key => $value) {
			if (!$first)
			    $first = $value;
			$this->object->removeQuestion($value);
		}
		$this->object->saveCompleteStatus();
		
		if ($_REQUEST['test_express_mode']) {

		    //$questions = $this->object->getQuestionTitlesAndIndexes();
		    $prev = null;
		    foreach($questions as $key => $value) {
			if ($prev === null) {
			    $prev = $key;
			    continue;
			}
			if ($key == $first) {
			    $return_to = $prev;
			}
			$prev = $key;
		    }

		    if (count($questions) == count($checked_questions)) {
			$this->ctrl->redirect($this, "showPage");
		    }
		    else if (!$return_to)
			    $return_to = $questions[0];

		    $this->ctrl->setParameter($this, 'q_id', $return_to);
		    $this->ctrl->redirect($this, "showPage");
		}
		else {
		    $this->ctrl->redirect($this, "questions");
		}
	}
	
	/**
	* Cancels the removal of questions from the test
	*
	* Cancels the removal of questions from the test
	*
	* @access	public
	*/
	function cancelRemoveQuestionsObject()
	{
	    	if ($_REQUEST['test_express_mode']) {
		    $this->ctrl->setParameter($this, 'q_id', $_REQUEST['q_id']);
		    $this->ctrl->redirect($this, "showPage");
		}
		else {
		    $this->ctrl->redirect($this, "questions");
		}
	}
	
	/**
	* Displays a form to confirm the removal of questions from the test
	*
	* Displays a form to confirm the removal of questions from the test
	*
	* @access	public
	*/
	function removeQuestionsForm($checked_questions)
	{
		ilUtil::sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_remove_questions.html", "Modules/Test");
		$removablequestions =& $this->object->getTestQuestions();
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		if (count($removablequestions))
		{
			foreach ($removablequestions as $data)
			{
				if (in_array($data["question_id"], $checked_questions))
				{
					$this->tpl->setCurrentBlock("row");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->setVariable("TXT_TITLE", $data["title"]);
					$this->tpl->setVariable("TXT_DESCRIPTION", $data["description"]);
					$this->tpl->setVariable("TXT_TYPE", assQuestion::_getQuestionTypeName($data["type_tag"]));
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
			}
		}
		foreach ($checked_questions as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "1");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));

		$this->ctrl->saveParameter($this, 'test_express_mode');
		$this->ctrl->saveParameter($this, 'q_id');
		
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Called when a selection of questions should be removed from the test
	*
	* Called when a selection of questions should be removed from the test
	*
	* @access	public
	*/
	function removeQuestionsObject()
	{
		$this->getQuestionsSubTabs();
		$checked_questions = $_REQUEST["q_id"];
		if (!is_array($checked_questions) && $checked_questions) {
		    $checked_questions = array($checked_questions);
		}
		if (count($checked_questions) > 0) 
		{
			$total = $this->object->evalTotalPersons();
			if ($total) 
			{
				// the test was executed previously
				ilUtil::sendInfo(sprintf($this->lng->txt("tst_remove_questions_and_results"), $total));
			} 
			else 
			{
			    if (count($checked_questions) == 1)
				ilUtil::sendQuestion($this->lng->txt("tst_remove_question"));
			    else
				ilUtil::sendQuestion($this->lng->txt("tst_remove_questions"));
			}
			$this->removeQuestionsForm($checked_questions);
			return;
		} 
		elseif (count($checked_questions) == 0) 
		{
			ilUtil::sendInfo($this->lng->txt("tst_no_question_selected_for_removal"), true);
			$this->ctrl->redirect($this, "questions");
		}
	}
	
	/**
	* Marks selected questions for moving
	*/
	function moveQuestionsObject()
	{
		$_SESSION['tst_qst_move_' . $this->object->getTestId()] = $_POST['q_id'];
		ilUtil::sendSuccess($this->lng->txt("msg_selected_for_move"), true);
		$this->ctrl->redirect($this, 'questions');
	}
	
	/**
	* Insert checked questions before the actual selection
	*/
	public function insertQuestionsBeforeObject()
	{
		// get all questions to move
		$move_questions = $_SESSION['tst_qst_move_' . $this->object->getTestId()];

		if (count($_POST['q_id']) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("no_target_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		if (count($_POST['q_id']) > 1)
		{
			ilUtil::sendFailure($this->lng->txt("too_many_targets_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		$insert_mode = 0;
		$this->object->moveQuestions($_SESSION['tst_qst_move_' . $this->object->getTestId()], $_POST['q_id'][0], $insert_mode);
		ilUtil::sendSuccess($this->lng->txt("msg_questions_moved"), true);
		unset($_SESSION['tst_qst_move_' . $this->object->getTestId()]);
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Insert checked questions after the actual selection
	*/
	public function insertQuestionsAfterObject()
	{
		// get all questions to move
		$move_questions = $_SESSION['tst_qst_move_' . $this->object->getTestId()];
		if (count($_POST['q_id']) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("no_target_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		if (count($_POST['q_id']) > 1)
		{
			ilUtil::sendFailure($this->lng->txt("too_many_targets_selected_for_move"), true);
			$this->ctrl->redirect($this, 'questions');
		}
		$insert_mode = 1;
		$this->object->moveQuestions($_SESSION['tst_qst_move_' . $this->object->getTestId()], $_POST['q_id'][0], $insert_mode);
		ilUtil::sendSuccess($this->lng->txt("msg_questions_moved"), true);
		unset($_SESSION['tst_qst_move_' . $this->object->getTestId()]);
		$this->ctrl->redirect($this, "questions");
	}
	
	/**
	* Insert questions from the questionbrowser into the test 
	*
	* @access	public
	*/
	function insertQuestionsObject()
	{
		$selected_array = (is_array($_POST['q_id'])) ? $_POST['q_id'] : array();
		if (!count($selected_array))
		{
			ilUtil::sendInfo($this->lng->txt("tst_insert_missing_question"), true);
			$this->ctrl->redirect($this, "browseForQuestions");
		}
		else
		{
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			$manscoring = FALSE;
			foreach ($selected_array as $key => $value) 
			{
				$this->object->insertQuestion($value);
				if (!$manscoring)
				{
					$manscoring = $manscoring | assQuestion::_needsManualScoring($value);
				}
			}
			$this->object->saveCompleteStatus();
			if ($manscoring)
			{
				ilUtil::sendInfo($this->lng->txt("manscoring_hint"), TRUE);
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt("tst_questions_inserted"), TRUE);
			}
			$this->ctrl->redirect($this, "questions");
			return;
		}
	}

	public function filterAvailableQuestionsObject()
	{
		include_once "./Modules/Test/classes/tables/class.ilTestQuestionBrowserTableGUI.php";
		$table_gui = new ilTestQuestionBrowserTableGUI($this, 'browseForQuestions');
		$table_gui->writeFilterToSession();
		$this->ctrl->redirect($this, "browseForQuestions");
	}
	
	public function resetfilterAvailableQuestionsObject()
	{
		include_once "./Modules/Test/classes/tables/class.ilTestQuestionBrowserTableGUI.php";
		$table_gui = new ilTestQuestionBrowserTableGUI($this, 'browseForQuestions');
		$table_gui->resetFilter();
		$this->ctrl->redirect($this, "browseForQuestions");
	}
	
	/**
	* Creates a form to select questions from questionpools to insert the questions into the test 
	*
	* @access	public
	*/
	function questionBrowser()
	{
		global $ilAccess;

		$this->ctrl->setParameterByClass(get_class($this), "browse", "1");

		include_once "./Modules/Test/classes/tables/class.ilTestQuestionBrowserTableGUI.php";
		$table_gui = new ilTestQuestionBrowserTableGUI($this, 'browseForQuestions', (($ilAccess->checkAccess("write", "", $this->ref_id) ? true : false)));
		$arrFilter = array();
		foreach ($table_gui->getFilterItems() as $item)
		{
			if ($item->getValue() !== false)
			{
				$arrFilter[$item->getPostVar()] = $item->getValue();
			}
		}
		$data = $this->object->getAvailableQuestions($arrFilter, 1);
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	function questionsObject()
	{
		global $ilAccess, $ilTabs;

		$ilTabs->activateTab('assQuestions');

		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

                #if (!in_array($this->object->getEnabledViewMode(), array('both', 'table'))) {
                #    return $this->showQuestionsPerPageObject();
                #}
                
		if ($_GET['browse'])
		{
			return $this->questionbrowser();
		}

		$this->getQuestionsSubTabs();
		if ($this->object->isRandomTest())
		{
			$this->randomQuestionsObject();
			return;
		}
		
		if ($_GET["eqid"] && $_GET["eqpl"])
		{
			ilUtil::redirect("ilias.php?baseClass=ilObjQuestionPoolGUI&ref_id=" . $_GET["eqpl"] . "&cmd=editQuestionForTest&calling_test=".$_GET["ref_id"]."&q_id=" . $_GET["eqid"]);
		}
		
		if ($_GET["up"] > 0)
		{
			$this->object->questionMoveUp($_GET["up"]);
		}
		if ($_GET["down"] > 0)
		{
			$this->object->questionMoveDown($_GET["down"]);
		}

		if ($_GET["add"])
		{
			$selected_array = array();
			array_push($selected_array, $_GET["add"]);
			$total = $this->object->evalTotalPersons();
			if ($total)
			{
				// the test was executed previously
				ilUtil::sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("tst_insert_questions"));
			}
			$this->insertQuestions($selected_array);
			return;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", "Modules/Test");

		$total = $this->object->evalTotalPersons();
		if (($ilAccess->checkAccess("write", "", $this->ref_id) and ($total == 0)))
		{
			global $ilToolbar;

			$ilToolbar->addButton($this->lng->txt("tst_browse_for_questions"), $this->ctrl->getLinkTarget($this, 'browseForQuestions'));

			$ilToolbar->addSeparator();

			$qtypes = array();
			include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
			foreach (ilObjQuestionPool::_getQuestionTypes() as $trans => $data)
			{
				$qtypes[$data['type_tag']] = $trans;
			}
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			$types = new ilSelectInputGUI($this->lng->txt("create_new"), "sel_question_types");
			$types->setOptions($qtypes);
			$ilToolbar->addInputItem($types, $this->lng->txt("create_new"));
			$ilToolbar->addFormButton($this->lng->txt("create"), "createQuestion");

			$ilToolbar->addSeparator();

			$ilToolbar->addButton($this->lng->txt("random_selection"), $this->ctrl->getLinkTarget($this, "randomselect"));
		}

		$this->tpl->setCurrentBlock("adm_content");
		include_once "./Modules/Test/classes/tables/class.ilTestQuestionsTableGUI.php";
		$checked_move = is_array($_SESSION['tst_qst_move_' . $this->object->getTestId()]) && (count($_SESSION['tst_qst_move_' . $this->object->getTestId()]));
		$table_gui = new ilTestQuestionsTableGUI($this, 'questions', (($ilAccess->checkAccess("write", "", $this->ref_id) ? true : false)), $checked_move, $total);
		$data = $this->object->getTestQuestions();
		$table_gui->setData($data);
		$this->tpl->setVariable('QUESTIONBROWSER', $table_gui->getHTML());	
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	function takenObject() {
	}
	
	/**
	* Add a new mark step to the tests marks
	*
	* Add a new mark step to the tests marks
	*
	* @access	public
	*/
	function addMarkStepObject()
	{
		$this->saveMarkSchemaFormData();
		$this->object->mark_schema->addMarkStep();
		$this->marksObject();
	}

	/**
	* Save the mark schema POST data when the form was submitted
	*
	* Save the mark schema POST data when the form was submitted
	*
	* @access	public
	*/
	function saveMarkSchemaFormData()
	{
		$this->object->mark_schema->flush();
		foreach ($_POST as $key => $value) {
			if (preg_match("/mark_short_(\d+)/", $key, $matches)) 
			{
				$this->object->mark_schema->addMarkStep(ilUtil::stripSlashes($_POST["mark_short_$matches[1]"]), ilUtil::stripSlashes($_POST["mark_official_$matches[1]"]), ilUtil::stripSlashes($_POST["mark_percentage_$matches[1]"]), ilUtil::stripSlashes($_POST["passed_$matches[1]"]));
			}
		}
		$this->object->ects_grades["A"] = $_POST["ects_grade_a"];
		$this->object->ects_grades["B"] = $_POST["ects_grade_b"];
		$this->object->ects_grades["C"] = $_POST["ects_grade_c"];
		$this->object->ects_grades["D"] = $_POST["ects_grade_d"];
		$this->object->ects_grades["E"] = $_POST["ects_grade_e"];
		if ($_POST["chbUseFX"])
		{
			$this->object->ects_fx = $_POST["percentFX"];
		}
		else
		{
			$this->object->ects_fx = "";
		}
		$this->object->ects_output = $_POST["chbECTS"];
	}
	
	/**
	* Add a simple mark schema to the tests marks
	*
	* Add a simple mark schema to the tests marks
	*
	* @access	public
	*/
	function addSimpleMarkSchemaObject()
	{
		$this->object->mark_schema->createSimpleSchema($this->lng->txt("failed_short"), $this->lng->txt("failed_official"), 0, 0, $this->lng->txt("passed_short"), $this->lng->txt("passed_official"), 50, 1);
		$this->marksObject();
	}
	
	/**
	* Delete selected mark steps
	*
	* Delete selected mark steps
	*
	* @access	public
	*/
	function deleteMarkStepsObject()
	{
		$this->saveMarkSchemaFormData();
		$delete_mark_steps = array();
		foreach ($_POST as $key => $value) {
			if (preg_match("/cb_(\d+)/", $key, $matches)) {
				array_push($delete_mark_steps, $matches[1]);
			}
		}
		if (count($delete_mark_steps)) {
			$this->object->mark_schema->deleteMarkSteps($delete_mark_steps);
		} else {
			ilUtil::sendInfo($this->lng->txt("tst_delete_missing_mark"));
		}
		$this->marksObject();
	}

	/**
	* Cancel the mark schema form and return to the properties form
	*
	* Cancel the mark schema form and return to the properties form
	*
	* @access	public
	*/
	function cancelMarksObject()
	{
		$this->ctrl->redirect($this, "marks");
	}
	
	/**
	* Save the mark schema
	*
	* Save the mark schema
	*
	* @access	public
	*/
	function saveMarksObject()
	{
		$this->saveMarkSchemaFormData();
		
		$mark_check = $this->object->checkMarks();
		if ($mark_check !== true)
		{
			ilUtil::sendInfo($this->lng->txt($mark_check));
		}
		elseif ($_POST["chbECTS"] && ((strcmp($_POST["ects_grade_a"], "") == 0) or (strcmp($_POST["ects_grade_b"], "") == 0) or (strcmp($_POST["ects_grade_c"], "") == 0) or (strcmp($_POST["ects_grade_d"], "") == 0) or (strcmp($_POST["ects_grade_e"], "") == 0)))
		{
			ilUtil::sendInfo($this->lng->txt("ects_fill_out_all_values"), true);
		}
		elseif (($_POST["ects_grade_a"] > 100) or ($_POST["ects_grade_a"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_a"), true);
		}
		elseif (($_POST["ects_grade_b"] > 100) or ($_POST["ects_grade_b"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_b"), true);
		}
		elseif (($_POST["ects_grade_c"] > 100) or ($_POST["ects_grade_c"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_c"), true);
		}
		elseif (($_POST["ects_grade_d"] > 100) or ($_POST["ects_grade_d"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_d"), true);
		}
		elseif (($_POST["ects_grade_e"] > 100) or ($_POST["ects_grade_e"] < 0))
		{
			ilUtil::sendInfo($this->lng->txt("ects_range_error_e"), true);
		}
		else 
		{
			$this->object->mark_schema->saveToDb($this->object->getTestId());
			$this->object->saveCompleteStatus();
			if ($this->object->getReportingDate())
			{
				$fxpercent = "";
				if ($_POST["chbUseFX"])
				{
					$fxpercent = ilUtil::stripSlashes($_POST["percentFX"]);
				}
				$this->object->saveECTSStatus($_POST["chbECTS"], $fxpercent, ilUtil::stripSlashes($this->object->ects_grades["A"]), ilUtil::stripSlashes($this->object->ects_grades["B"]), ilUtil::stripSlashes($this->object->ects_grades["C"]), ilUtil::stripSlashes($this->object->ects_grades["D"]), ilUtil::stripSlashes($this->object->ects_grades["E"]));
			}
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		}
		$this->marksObject();
	}
	
	function marksObject() 
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		if (!$this->object->canEditMarks())
		{
			ilUtil::sendInfo($this->lng->txt("cannot_edit_marks"));
		}
		
		$this->object->mark_schema->sort();
	
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_marks.html", "Modules/Test");
		$marks = $this->object->mark_schema->mark_steps;
		$rows = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($marks as $key => $value) {
			$this->tpl->setCurrentBlock("markrow");
			$this->tpl->setVariable("MARK_SHORT", $value->getShortName());
			$this->tpl->setVariable("MARK_OFFICIAL", $value->getOfficialName());
			$this->tpl->setVariable("MARK_PERCENTAGE", sprintf("%.2f", $value->getMinimumLevel()));
			$this->tpl->setVariable("MARK_PASSED", strtolower($this->lng->txt("tst_mark_passed")));
			$this->tpl->setVariable("MARK_ID", "$key");
			$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
			if ($value->getPassed()) {
				$this->tpl->setVariable("MARK_PASSED_CHECKED", " checked=\"checked\"");
			}
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if (count($marks) == 0) 
		{
			$this->tpl->setCurrentBlock("Emptyrow");
			$this->tpl->setVariable("EMPTY_ROW", $this->lng->txt("tst_no_marks_defined"));
			$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		} 
		else 
		{
			if ($ilAccess->checkAccess("write", "", $this->ref_id) && $this->object->canEditMarks()) 
			{
				$this->tpl->setCurrentBlock("selectall");
				$counter++;
				$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
				$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("Footer");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\"/>");
				$this->tpl->setVariable("BUTTON_EDIT", $this->lng->txt("edit"));
				$this->tpl->setVariable("BUTTON_DELETE", $this->lng->txt("delete"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($this->object->getReportingDate())
		{
			$this->tpl->setCurrentBlock("ects");
			if ($this->object->ects_output)
			{
				$this->tpl->setVariable("CHECKED_ECTS", " checked=\"checked\"");
			}
			$this->tpl->setVariable("TEXT_OUTPUT_ECTS_GRADES", $this->lng->txt("ects_output_of_ects_grades"));
			$this->tpl->setVariable("TEXT_ALLOW_ECTS_GRADES", $this->lng->txt("ects_allow_ects_grades"));
			$this->tpl->setVariable("TEXT_USE_FX", $this->lng->txt("ects_use_fx_grade"));
			if (preg_match("/\d+/", $this->object->ects_fx))
			{
				$this->tpl->setVariable("CHECKED_FX", " checked=\"checked\"");
				$this->tpl->setVariable("VALUE_PERCENT_FX", sprintf("value=\"%s\" ", $this->object->ects_fx));
			}
			$this->tpl->setVariable("TEXT_PERCENT", $this->lng->txt("ects_use_fx_grade_part2"));
			$this->tpl->setVariable("ECTS_GRADE", $this->lng->txt("ects_grade"));
			$this->tpl->setVariable("PERCENTILE", $this->lng->txt("percentile"));
			$this->tpl->setVariable("ECTS_GRADE_A", "A - " . $this->lng->txt("ects_grade_a_short"));
			$this->tpl->setVariable("VALUE_GRADE_A", $this->object->ects_grades["A"]);
			$this->tpl->setVariable("ECTS_GRADE_B", "B - " . $this->lng->txt("ects_grade_b_short"));
			$this->tpl->setVariable("VALUE_GRADE_B", $this->object->ects_grades["B"]);
			$this->tpl->setVariable("ECTS_GRADE_C", "C - " . $this->lng->txt("ects_grade_c_short"));
			$this->tpl->setVariable("VALUE_GRADE_C", $this->object->ects_grades["C"]);
			$this->tpl->setVariable("ECTS_GRADE_D", "D - " . $this->lng->txt("ects_grade_d_short"));
			$this->tpl->setVariable("VALUE_GRADE_D", $this->object->ects_grades["D"]);
			$this->tpl->setVariable("ECTS_GRADE_E", "E - " . $this->lng->txt("ects_grade_e_short"));
			$this->tpl->setVariable("VALUE_GRADE_E", $this->object->ects_grades["E"]);
			
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("ACTION_MARKS", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_SHORT", $this->lng->txt("tst_mark_short_form"));
		$this->tpl->setVariable("HEADER_OFFICIAL", $this->lng->txt("tst_mark_official_form"));
		$this->tpl->setVariable("HEADER_PERCENTAGE", $this->lng->txt("tst_mark_minimum_level"));
		$this->tpl->setVariable("HEADER_PASSED", $this->lng->txt("tst_mark_passed"));
		if ($ilAccess->checkAccess("write", "", $this->ref_id) && $this->object->canEditMarks()) 
		{
			$this->tpl->setVariable("BUTTON_NEW", $this->lng->txt("tst_mark_create_new_mark_step"));
			$this->tpl->setVariable("BUTTON_NEW_SIMPLE", $this->lng->txt("tst_mark_create_simple_mark_schema"));
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Deletes all user data for the test object
	*
	* Deletes all user data for the test object
	*
	* @access	public
	*/
	function confirmDeleteAllUserResultsObject()
	{
		$this->object->removeAllTestEditings();

		// Update lp status
		include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
		ilLPStatusWrapper::_refreshStatus($this->object->getId());

		ilUtil::sendSuccess($this->lng->txt("tst_all_user_data_deleted"), true);
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Deletes the selected user data for the test object
	*
	* Deletes the selected user data for the test object
	*
	* @access	public
	*/
	function confirmDeleteSelectedUserDataObject()
	{
		$active_ids = array();
		foreach ($_POST["chbUser"] as $active_id)
		{
			if ($this->object->getFixedParticipants())
			{
				array_push($active_ids, $this->object->getActiveIdOfUser($active_id));
			}
			else
			{
				array_push($active_ids, $active_id);
			}
		}
		$this->object->removeSelectedTestResults($active_ids);

		// Update lp status
		include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
		ilLPStatusWrapper::_refreshStatus($this->object->getId());

		ilUtil::sendSuccess($this->lng->txt("tst_selected_user_data_deleted"), true);
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Cancels the deletion of all user data for the test object
	*
	* Cancels the deletion of all user data for the test object
	*
	* @access	public
	*/
	function cancelDeleteSelectedUserDataObject()
	{
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Asks for a confirmation to delete all user data of the test object
	*
	* Asks for a confirmation to delete all user data of the test object
	*
	* @access	public
	*/
	function deleteAllUserDataObject()
	{
		ilUtil::sendQuestion($this->lng->txt("confirm_delete_all_user_data"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_maintenance.html", "Modules/Test");

		$this->tpl->setCurrentBlock("confirm_delete");
		$this->tpl->setVariable("BTN_CONFIRM_DELETE_ALL", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_DELETE_ALL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Asks for a confirmation to delete all user data of the test object
	*/
	public function deleteAllUserResultsObject()
	{
		ilUtil::sendQuestion($this->lng->txt("delete_all_user_data_confirmation"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.confirm_deletion.html", "Modules/Test");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "participants"));

		// cancel/confirm button
		$buttons = array( "confirmDeleteAllUserResults"  => $this->lng->txt("proceed"),
			"participants"  => $this->lng->txt("cancel"));
		foreach ($buttons as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	* Asks for a confirmation to delete selected user data of the test object
	*
	* Asks for a confirmation to delete selected user data of the test object
	*
	* @access	public
	*/
	function deleteSingleUserResultsObject()
	{
		if (count($_POST["chbUser"]) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), TRUE);
			$this->ctrl->redirect($this, "participants");
		}
		ilUtil::sendQuestion($this->lng->txt("confirm_delete_single_user_data"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_maintenance.html", "Modules/Test");

		foreach ($_POST["chbUser"] as $key => $value)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("USER_ID", $value);
			$this->tpl->parseCurrentBlock();
		}
		
		include_once './Services/User/classes/class.ilObjUser.php';
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($_POST["chbUser"] as $key => $active_id)
		{
			if ($this->object->getFixedParticipants())
			{
				$user_id = $active_id;
			}
			else
			{
				$user_id = $this->object->_getUserIdFromActiveId($active_id);
			}
			$user = ilObjUser::_lookupName($user_id);
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("USER_ICON", ilUtil::getImagePath("icon_usr.gif"));
			$this->tpl->setVariable("USER_ALT", $this->lng->txt("usr"));
			$this->tpl->setVariable("USER_TITLE", $this->lng->txt("usr"));
			if ($this->object->getAnonymity())
			{
				$this->tpl->setVariable("TXT_FIRSTNAME", "");
				$this->tpl->setVariable("TXT_LASTNAME", $this->lng->txt("unknown"));
				$this->tpl->setVariable("TXT_LOGIN", "");
			}
			else
			{
				$this->tpl->setVariable("TXT_FIRSTNAME", $user["firstname"]);
				if (strlen($user["lastname"]))
				{
					$this->tpl->setVariable("TXT_LASTNAME", $user["lastname"]);
				}
				else
				{
					$this->tpl->setVariable("TXT_LASTNAME", $this->lng->txt("deleted_user"));
				}
				$this->tpl->setVariable("TXT_LOGIN", ilObjUser::_lookupLogin($user_id));
			}
			$this->tpl->setVariable("ROW_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		$this->tpl->setCurrentBlock("selectedusers");
		$this->tpl->setVariable("HEADER_TXT_FIRSTNAME", $this->lng->txt("firstname"));
		$this->tpl->setVariable("HEADER_TXT_LASTNAME", $this->lng->txt("lastname"));
		$this->tpl->setVariable("HEADER_TXT_LOGIN", $this->lng->txt("login"));
		$this->tpl->setVariable("BTN_CONFIRM_DELETE_SELECTED", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_DELETE_SELECTED", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Creates the change history for a test
	*
	* Creates the change history for a test
	*
	* @access	public
	*/
	function historyObject()
	{
		include_once "./Modules/Test/classes/tables/class.ilTestHistoryTableGUI.php";
		$table_gui = new ilTestHistoryTableGUI($this, 'history');
		$table_gui->setTestObject($this->object);
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		$log =& ilObjAssessmentFolder::_getLog(0, time(), $this->object->getId(), TRUE);
		$table_gui->setData($log);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	/**
	* form for new content object creation
	*/
	protected function initCreateForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt($a_new_type."_new"));

		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);

		// defaults
		include_once("./Modules/Test/classes/class.ilObjTest.php");
		$tst = new ilObjTest();
		$defaults = $tst->getAvailableDefaults();
		if (count($defaults))
		{
			$options = array(0 => $this->lng->txt("tst_defaults_dont_use"));
			foreach ($defaults as $row)
			{
				$options[$row["test_defaults_id"]] = $row["name"];
			}

			$def = new ilSelectInputGUI($this->lng->txt("defaults"), "defaults");
			$def->setOptions($options);
			$form->addItem($def);
		}


		// using template?
		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		$templates = ilSettingsTemplate::getAllSettingsTemplates("tst");
		if($templates)
		{
			$this->tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery.js");
			// $this->tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery-ui-min.js");

			$options = array(""=>$this->lng->txt("none"));
			$js_data = array();
			foreach($templates as $item)
			{
				$options[$item["id"]] = $item["title"];

				$desc = str_replace("\n", "", nl2br(trim($item["description"])));
				$desc = str_replace("\r", "", $desc);

				$js_data[] = "jsInfo[".$item["id"]."] = \"".$desc."\"";
			}

			$tmpl = new ilSelectInputGUI($this->lng->txt("tst_settings_template"), "template");
			$tmpl->setOptions($options);
			$tmpl->addCustomAttribute("onChange=\"showInfo(this.value);\"");
			$form->addItem($tmpl);

			$js_data = implode("\n", $js_data);

$preview = <<<EOT
			<script>
			var jsInfo = {};
			$js_data
			function showInfo(id) {
				if(jsInfo[id] != undefined && jsInfo[id].length)
				{
					jQuery("#jsInfo").html(jsInfo[id]).css("display", "");
				}
				else
				{
					jQuery("#jsInfo").html("").css("display", "hidden");
				}
			}
			</script>
			<div id="jsInfo" style="display:none; margin: 5px;" class="small">xxx</div></td>
EOT;

			$tmpl->setInfo($preview);
		}

		$form->addCommandButton("save", $this->lng->txt($a_new_type."_add"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));

		return $form;
	}

	function initImportForm($a_new_type)
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setTarget("_top");
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt("import_tst"));

		// file
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($this->lng->txt("import_file"), "xmldoc");
		$fi->setSuffixes(array("zip"));
		$fi->setRequired(true);
		$form->addItem($fi);

		// question pool
		include_once("./Modules/Test/classes/class.ilObjTest.php");
		$tst = new ilObjTest();
		$questionpools = $tst->getAvailableQuestionpools(TRUE, FALSE, TRUE, TRUE);
		if (count($questionpools))
		{
			$options = array("-1" => $this->lng->txt("dont_use_questionpool"));
			foreach ($questionpools as $key => $value)
			{
				$options[$key] = $value["title"];
			}

			$pool = new ilSelectInputGUI($this->lng->txt("select_questionpool"), "qpl");
			$pool->setOptions($options);
			$form->addItem($pool);
		}

		$form->addCommandButton("importFile", $this->lng->txt("import"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));

		return $form;
	}

 /**
	* Evaluates the actions on the participants page
	*
	* @access	public
	*/
	function participantsActionObject()
	{
		$command = $_POST["command"];
		if (strlen($command))
		{
			$method = $command . "Object";
			if (method_exists($this, $method))
			{
				$this->$method();
				return;
			}
		}
		$this->ctrl->redirect($this, "participants");
	}

 /**
	* Creates the output of the test participants
	*
	* @access	public
	*/
	function participantsObject()
	{
		global $ilAccess, $ilToolbar, $lng;
		
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		if ($this->object->getFixedParticipants())
		{
			// search button
			include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
			ilRepositorySearchGUI::fillAutoCompleteToolbar(
				$this,
				$tb,
				array(
					'auto_complete_name'	=> $lng->txt('user'),
					'submit_name'			=> $lng->txt('add')
				)
			);

			// search button
			$ilToolbar->addButton($this->lng->txt("tst_search_users"),
				$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));


			$participants =& $this->object->getInvitedUsers();
			$rows = array();
			foreach ($participants as $data)
			{
				$maxpass = $this->object->_getMaxPass($data["active_id"]);
				if (!is_null($maxpass))
				{
					$maxpass += 1;
				}
				$access = "";
				if (strlen($data["active_id"]))
				{
					$last_access = $this->object->_getLastAccess($data["active_id"]);
					$access = ilDatePresentation::formatDate(new ilDateTime($last_access,IL_CAL_DATETIME));					
				}
				$this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);
				include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
				$fullname = ilObjTestAccess::_getParticipantData($data['active_id']);
				array_push($rows, array(
					'usr_id' => $data["usr_id"],
					'active_id' => $data['active_id'],
					'login' => $data["login"],
					'clientip' => $data["clientip"],
					'firstname' => $data["firstname"],
					'lastname' => $data["lastname"],
					'name' => $fullname,
					'started' => ($data["active_id"] > 0) ? 1 : 0,
					'finished' => ($data["test_finished"] == 1) ? 1 : 0,
					'access' => $access,
					'maxpass' => $maxpass,
					'result' => $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outParticipantsResultsOverview')
				));
			}
			include_once "./Modules/Test/classes/tables/class.ilTestFixedParticipantsTableGUI.php";
			$table_gui = new ilTestFixedParticipantsTableGUI($this, 'participants', $this->object->getAnonymity(), count($rows));
			$table_gui->setData($rows);
			$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
		}
		else
		{
			$participants =& $this->object->getTestParticipants();
			$rows = array();
			foreach ($participants as $data)
			{
				$maxpass = $this->object->_getMaxPass($data["active_id"]);
				if (!is_null($maxpass))
				{
					$maxpass += 1;
				}
				$access = "";
				if (strlen($data["active_id"]))
				{
					$last_access = $this->object->_getLastAccess($data["active_id"]);
					$access = ilDatePresentation::formatDate(new ilDateTime($last_access,IL_CAL_DATETIME));
				}
				$this->ctrl->setParameterByClass('iltestevaluationgui', 'active_id', $data['active_id']);
				include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
				$fullname = ilObjTestAccess::_getParticipantData($data['active_id']);
				array_push($rows, array(
					'usr_id' => $data["active_id"],
					'active_id' => $data['active_id'],
					'login' => $data["login"],
					'name' => $fullname,
					'firstname' => $data["firstname"],
					'lastname' => $data["lastname"],
					'started' => ($data["active_id"] > 0) ? 1 : 0,
					'finished' => ($data["test_finished"] == 1) ? 1 : 0,
					'access' => $access,
					'maxpass' => $maxpass,
					'result' => $this->ctrl->getLinkTargetByClass('iltestevaluationgui', 'outParticipantsResultsOverview')
				));
			}
			include_once "./Modules/Test/classes/tables/class.ilTestParticipantsTableGUI.php";
			$table_gui = new ilTestParticipantsTableGUI($this, 'participants', $this->object->getAnonymity(), count($rows));
			$table_gui->setData($rows);
			$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
		}
	}

 /**
	* Shows the pass overview and the answers of one ore more users for the scored pass
	*
	* @access	public
	*/
	function showDetailedResultsObject()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = TRUE, $show_answers = TRUE, $show_reached_points = TRUE);
	}

 /**
	* Shows the answers of one ore more users for the scored pass
	*
	* @access	public
	*/
	function showUserAnswersObject()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = FALSE, $show_answers = TRUE);
	}

 /**
	* Shows the pass overview of the scored pass for one ore more users
	*
	* @access	public
	*/
	function showPassOverviewObject()
	{
		if (count($_POST))
		{
			$_SESSION["show_user_results"] = $_POST["chbUser"];
		}
		$this->showUserResults($show_pass_details = TRUE, $show_answers = FALSE);
	}
	
 /**
	* Shows the pass overview of the scored pass for one ore more users
	*
	* @access	public
	*/
	function showUserResults($show_pass_details, $show_answers, $show_reached_points = FALSE)
	{
		$template = new ilTemplate("tpl.il_as_tst_participants_result_output.html", TRUE, TRUE, "Modules/Test");
		
		if (count($_SESSION["show_user_results"]) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), TRUE);
			$this->ctrl->redirect($this, "participants");
		}

		include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";
		$serviceGUI =& new ilTestServiceGUI($this->object);
		$count = 0;
		foreach ($_SESSION["show_user_results"] as $key => $active_id)
		{
			$count++;
			$results = "";
			if ($this->object->getFixedParticipants())
			{
				$active_id = $this->object->getActiveIdOfUser($active_id);
			}
			if ($active_id > 0)
			{
				$results = $serviceGUI->getResultsOfUserOutput($active_id, $this->object->_getResultPass($active_id), $show_pass_details, $show_answers, FALSE, $show_reached_points);
			}
			if ($count < count($_SESSION["show_user_results"]))
			{
				$template->touchBlock("break");
			}
			$template->setCurrentBlock("user_result");
			$template->setVariable("USER_RESULT", $results);
			$template->parseCurrentBlock();
		}
		$template->setVariable("BACK_TEXT", $this->lng->txt("back"));
		$template->setVariable("BACK_URL", $this->ctrl->getLinkTargetByClass("ilobjtestgui", "participants"));
		$template->setVariable("PRINT_TEXT", $this->lng->txt("print"));
		$template->setVariable("PRINT_URL", "javascript:window.print();");
		
		$this->tpl->setVariable("ADM_CONTENT", $template->get());
		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		if ($this->object->getShowSolutionAnswersOnly())
		{
			$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
		}
	}

	function removeParticipantObject()
	{
		if (is_array($_POST["chbUser"])) 
		{
			foreach ($_POST["chbUser"] as $user_id)
			{
				$this->object->disinviteUser($user_id);
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), true);
		}
		$this->ctrl->redirect($this, "participants");
	}
	
	function saveClientIPObject()
	{
		if (is_array($_POST["chbUser"])) 
		{
			foreach ($_POST["chbUser"] as $user_id)
			{
				$this->object->setClientIP($user_id, $_POST["clientip_".$user_id]);
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("select_one_user"), true);
		}
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Print tab to create a print of all questions with points and solutions
	*
	* Print tab to create a print of all questions with points and solutions
	*
	* @access	public
	*/
	function printobject() 
	{
		global $ilAccess, $ilias;
		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}
		$this->getQuestionsSubTabs();
		$template = new ilTemplate("tpl.il_as_tst_print_test_confirm.html", TRUE, TRUE, "Modules/Test");

		include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
		if(ilRPCServerSettings::getInstance()->isEnabled())
		{
			$this->ctrl->setParameter($this, "pdf", "1");
			$template->setCurrentBlock("pdf_export");
			$template->setVariable("PDF_URL", $this->ctrl->getLinkTarget($this, "print"));
			$this->ctrl->setParameter($this, "pdf", "");
			$template->setVariable("PDF_TEXT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_ALT", $this->lng->txt("pdf_export"));
			$template->setVariable("PDF_IMG_URL", ilUtil::getHtmlPath(ilUtil::getImagePath("application-pdf.png")));
			$template->parseCurrentBlock();
		}

		$this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
		
		global $ilUser;		
		$print_date = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
		$max_points= 0;
		$counter = 1;
					
		foreach ($this->object->questions as $question) 
		{		
			$template->setCurrentBlock("question");
			$question_gui = $this->object->createQuestionGUI("", $question);
			$template->setVariable("COUNTER_QUESTION", $counter.".");
			$template->setVariable("QUESTION_TITLE", ilUtil::prepareFormOutput($question_gui->object->getTitle()));
			if ($question_gui->object->getMaximumPoints() == 1)
			{
				$template->setVariable("QUESTION_POINTS", $question_gui->object->getMaximumPoints() . " " . $this->lng->txt("point"));
			}
			else
			{
				$template->setVariable("QUESTION_POINTS", $question_gui->object->getMaximumPoints() . " " . $this->lng->txt("points"));
			}
			$result_output = $question_gui->getSolutionOutput("", NULL, FALSE, TRUE, FALSE, $this->object->getShowSolutionFeedback());
			if (strlen($result_output) == 0) $result_output = $question_gui->getPreview(FALSE);
			$template->setVariable("SOLUTION_OUTPUT", $result_output);
			$template->parseCurrentBlock("question");
			$counter ++;
			$max_points += $question_gui->object->getMaximumPoints();
		}

		$template->setCurrentBlock("navigation_buttons");
		$template->setVariable("BUTTON_PRINT", $this->lng->txt("print"));
		$template->parseCurrentBlock();
		
		$template->setVariable("TITLE", ilUtil::prepareFormOutput($this->object->getTitle()));
		$template->setVariable("PRINT_TEST", ilUtil::prepareFormOutput($this->lng->txt("tst_print")));
		$template->setVariable("TXT_PRINT_DATE", ilUtil::prepareFormOutput($this->lng->txt("date")));
		$template->setVariable("VALUE_PRINT_DATE", ilUtil::prepareFormOutput(strftime("%c",$print_date)));
		$template->setVariable("TXT_MAXIMUM_POINTS", ilUtil::prepareFormOutput($this->lng->txt("tst_maximum_points")));
		$template->setVariable("VALUE_MAXIMUM_POINTS", ilUtil::prepareFormOutput($max_points));
		
		if (array_key_exists("pdf", $_GET) && ($_GET["pdf"] == 1))
		{
			$this->object->deliverPDFfromHTML($template->get(), $this->object->getTitle());
		}
		else
		{
			$this->tpl->setVariable("PRINT_CONTENT", $template->get());
		}
	}
	
	function addParticipantsObject($a_user_ids = array())
	{
		$countusers = 0;
		// add users 
		if (is_array($a_user_ids))
		{
			$i = 0;
			foreach ($a_user_ids as $user_id)
			{
				$client_ip = $_POST["client_ip"][$i];
				$this->object->inviteUser($user_id, $client_ip);
				$countusers++;
				$i++;
			}
		}
		$message = "";
		if ($countusers)
		{
			$message = $this->lng->txt("tst_invited_selected_users");
		}
		if (strlen($message))
		{
			ilUtil::sendInfo($message, TRUE);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_invited_nobody"), TRUE);
			return false;
		}
		
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	* Displays the settings page for test defaults
	*
	* @access public
	*/
	function defaultsObject()
	{
		global $ilUser;
		global $ilAccess;

		if (!$ilAccess->checkAccess("write", "", $this->ref_id)) 
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_defaults.html", "Modules/Test");
		
		$maxentries = $ilUser->getPref("hits_per_page");
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}

		$offset = $_GET["offset"] ? $_GET["offset"] : 0;
		$sortby = $_GET["sort_by"] ? $_GET["sort_by"] : "name";
		$sortorder = $_GET["sort_order"] ? $_GET["sort_order"] : "asc";
		
		$defaults =& $this->object->getAvailableDefaults($sortby, $sortorder);
		if (count($defaults) > 0)
		{
			$tablerows = array();
			foreach ($defaults as $row)
			{
				array_push($tablerows, array("checkbox" => "<input type=\"checkbox\" name=\"chb_defaults[]\" value=\"" . $row["test_defaults_id"] . "\"/>", "name" => $row["name"]));
			}
			$headervars = array("", "name");

			include_once "./Services/Table/classes/class.ilTableGUI.php";
			$tbl = new ilTableGUI(0, FALSE);
			$tbl->setTitle($this->lng->txt("tst_defaults_available"));
			$header_names = array(
				"",
				$this->lng->txt("title")
			);
			$tbl->setHeaderNames($header_names);

			$tbl->disable("sort");
			$tbl->disable("auto_sort");
			$tbl->enable("title");
			$tbl->enable("action");
			$tbl->enable("select_all");
			$tbl->setLimit($maxentries);
			$tbl->setOffset($offset);
			$tbl->setData($tablerows);
			$tbl->setMaxCount(count($tablerows));
			$tbl->setOrderDirection($sortorder);
			$tbl->setSelectAllCheckbox("chb_defaults");
			$tbl->setFormName("formDefaults");
			$tbl->addActionButton("deleteDefaults", $this->lng->txt("delete"));
			$tbl->addActionButton("applyDefaults", $this->lng->txt("apply"));

			$header_params = $this->ctrl->getParameterArray($this, "defaults");
			$tbl->setHeaderVars($headervars, $header_params);

			// footer
			$tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
			// render table
			$tableoutput = $tbl->render();
			$this->tpl->setVariable("TEST_DEFAULTS_TABLE", $tableoutput);
		}
		else
		{
			$this->tpl->setVariable("TEST_DEFAULTS_TABLE", $this->lng->txt("tst_defaults_not_defined"));
		}
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "addDefaults"));
		$this->tpl->setVariable("BUTTON_ADD", $this->lng->txt("add"));
		$this->tpl->setVariable("TEXT_DEFAULTS_OF_TEST", $this->lng->txt("tst_defaults_defaults_of_test"));
	}
	
	/**
	* Deletes selected test defaults
	*/
	function deleteDefaultsObject()
	{
		if (count($_POST["chb_defaults"]))
		{
			foreach ($_POST["chb_defaults"] as $test_default_id)
			{
				$this->object->deleteDefaults($test_default_id);
			}
		}
		$this->defaultsObject();
	}
	
	/**
	* Applies the selected test defaults
	*/
	function applyDefaultsObject()
	{
		if (count($_POST["chb_defaults"]) == 1)
		{
			foreach ($_POST["chb_defaults"] as $test_default_id)
			{
				$result = $this->object->applyDefaults($test_default_id);
				if (!$result)
				{
					ilUtil::sendInfo($this->lng->txt("tst_defaults_apply_not_possible"));
				}
				else
				{
					ilUtil::sendSuccess($this->lng->txt("tst_defaults_applied"));
				}
			}
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_defaults_apply_select_one"));
		}
		$this->defaultsObject();
	}
	
	/**
	* Adds the defaults of this test to the defaults
	*/
	function addDefaultsObject()
	{
		if (strlen($_POST["name"]) > 0)
		{
			$this->object->addDefaults($_POST['name']);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("tst_defaults_enter_name"));
		}
		$this->defaultsObject();
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
		$this->infoScreen();
	}
	
	function redirectToInfoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen($_SESSION["lock"]);
	}
	
	/**
	* show information screen
	*/
	function infoScreen($session_lock = "")
	{
		global $ilAccess;
		global $ilUser;

		// Disabled
		if ($_GET['crs_show_result'])
		{
			$this->object->hideCorrectAnsweredQuestions();
		}
		else
		{
			if ($this->object->getTestSequence()->hasHiddenQuestions())
			{
				$this->object->getTestSequence()->clearHiddenQuestions();
				$this->object->getTestSequence()->saveToDb();
			}
		}
		
		if ($_GET['createRandomSolutions'])
		{
			$this->object->createRandomSolutions($_GET['createRandomSolutions']);
		}

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$seq = $this->object->getTestSession()->getLastSequence();

		include_once "./Modules/Test/classes/class.ilTestOutputGUI.php";
		$output_gui =& new ilTestOutputGUI($this->object);
		$this->ctrl->setParameter($output_gui, "sequence", $seq);
		$info->setFormAction($this->ctrl->getFormAction($output_gui));
		if (strlen($session_lock))
		{
			$info->addHiddenElement("lock", $session_lock);
		}
		else
		{
			$info->addHiddenElement("lock", md5($_COOKIE['PHPSESSID'] . time()));
		}
		$online_access = false;
		if ($this->object->getFixedParticipants())
		{
			include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
			$online_access_result = ilObjTestAccess::_lookupOnlineTestAccess($this->object->getId(), $ilUser->getId());
			if ($online_access_result === true)
			{
				$online_access = true;
			}
			else
			{
				ilUtil::sendInfo($online_access_result);
			}
		}
		if( $this->object->isOnline() && $this->object->isComplete() )
		{
			if ((!$this->object->getFixedParticipants() || $online_access) && $ilAccess->checkAccess("read", "", $this->ref_id))
			{
				$executable = $this->object->isExecutable($ilUser->getId(), $allowPassIncrease = TRUE);
				if ($executable["executable"])
				{
					if ($this->object->getTestSession()->getActiveId() > 0)
					{
						// resume test
						$resume_text = $this->lng->txt("tst_resume_test");
						if (($seq < 1) || ($seq == $this->object->getTestSequence()->getFirstSequence()))
						{
							$resume_text = $this->object->getStartTestLabel($this->object->getTestSession()->getActiveId());
						}
						// Disabled
						#if(!$_GET['crs_show_result'] or $this->object->getTestSequence()->getFirstSequence())
						{
							//$info->addFormButton("resume", $resume_text);
							$big_button[] = array('resume', $resume_text);
						}
					}
					else
					{
						// start new test
						//$info->addFormButton("start", $this->object->getStartTestLabel($this->object->getTestSession()->getActiveId()));
						$big_button[] = array("start", $this->object->getStartTestLabel($this->object->getTestSession()->getActiveId()));
					}
				}
				else
				{
					ilUtil::sendInfo($executable["errormessage"]);
				}
				if ($this->object->getTestSession()->getActiveId() > 0)
				{
					// test results button
					if ($this->object->canShowTestResults($ilUser->getId())) 
					{
						//$info->addFormButton("outUserResultsOverview", $this->lng->txt("tst_show_results"));
						$big_button[] = array("outUserResultsOverview", $this->lng->txt("tst_show_results"));
					}
				}
			}
			if ($this->object->getTestSession()->getActiveId() > 0)
			{
				if ($this->object->canShowSolutionPrintview($ilUser->getId()))
				{
					//$info->addFormButton("outUserListOfAnswerPasses", $this->lng->txt("tst_list_of_answers_show"));
					$big_button[] = array("outUserListOfAnswerPasses", $this->lng->txt("tst_list_of_answers_show"));
				}
			}
		}

		if( !$this->object->isOnline() )
 		{
			$message = $this->lng->txt("test_is_offline");

			if($ilAccess->checkAccess("write", "", $this->ref_id))
			{
				$message .= "<br /><a href=\"".$this->ctrl->getLinkTarget($this, "properties")."\">".
					$this->lng->txt("test_edit_settings")."</a>";
			}

			ilUtil::sendInfo($message);
		}
		
		if ($this->object->getShowInfo())
		{
			$info->enablePrivateNotes();
		}
		/*
		if (strlen($this->object->getIntroduction()))
		{
			$info->addSection($this->lng->txt("tst_introduction"));
			$info->addProperty("", $this->object->prepareTextareaOutput($this->object->getIntroduction()));
		}
		 * */
		if($big_button)
		{
		    $out = '<div class="il_ButtonGroup" style="margin:25px; text-align:center; font-size:25px;">';
		    foreach($big_button as $button) {
			$out .= '<input type="submit" class="submit" name="cmd['.$button[0].']" value="'.
				$button[1].'" style="padding:10px;" />';
		    }
		    $out .= '</div>';
		    $big_button = $out;
		}
		
		if (strlen($this->object->getIntroduction()))
		{
			$introduction = $this->object->getIntroduction();
			$info->addSection($this->lng->txt("tst_introduction"));
			$info->addProperty("", $this->object->prepareTextareaOutput($this->object->getIntroduction()).
					$big_button."<br />".$info->getHiddenToggleButton());
		}
		else
		{
			$info->addSection("");
			$info->addProperty("", $big_button.$info->getHiddenToggleButton());
		}

		$info->hideFurtherSections(false);

		$info->addSection($this->lng->txt("tst_general_properties"));
		if ($this->object->getShowInfo())
		{
			$info->addProperty($this->lng->txt("author"), $this->object->getAuthor());
			$info->addProperty($this->lng->txt("title"), $this->object->getTitle());
		}
		if( $this->object->isOnline() && $this->object->isComplete() )
		{
			if ((!$this->object->getFixedParticipants() || $online_access) && $ilAccess->checkAccess("read", "", $this->ref_id))
			{
				if ($this->object->getShowInfo() || !$this->object->getForceJS())
				{
					// use javascript
					$checked_javascript = false;
					if ($this->object->getJavaScriptOutput())
					{
						$checked_javascript = true;
					}
					if ($this->object->getForceJS())
					{
						$info->addProperty($this->lng->txt("tst_test_output"), $this->lng->txt("tst_use_javascript"));
					}
					else
					{
						$info->addPropertyCheckbox($this->lng->txt("tst_test_output"), "chb_javascript", 1, $this->lng->txt("tst_use_javascript"), $checked_javascript);
					}
				}
				// hide previous results
				if( !$this->object->isRandomTest() )
				{
					if ($this->object->getNrOfTries() != 1)
					{
						if ($this->object->getUsePreviousAnswers() == 0)
						{
							if ($this->object->getShowInfo())
							{
								$info->addProperty($this->lng->txt("tst_use_previous_answers"), $this->lng->txt("tst_dont_use_previous_answers"));
							}
						}
						else
						{
							$use_previous_answers = FALSE;
							if ($ilUser->prefs["tst_use_previous_answers"])
							{
								$checked_previous_answers = TRUE;
							}
							$info->addPropertyCheckbox($this->lng->txt("tst_use_previous_answers"), "chb_use_previous_answers", 1, $this->lng->txt("tst_use_previous_answers_user"), $checked_previous_answers);
						}
					}
				}
				if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
				{
					$info->addPropertyTextinput($this->lng->txt("enter_anonymous_code"), "anonymous_id", "", 8, "setAnonymousId", $this->lng->txt("submit"));
				}
			}
		}
		                                 
		if ($this->object->getShowInfo())
		{
			$info->addSection($this->lng->txt("tst_sequence_properties"));
			$info->addProperty($this->lng->txt("tst_sequence"), $this->lng->txt(($this->object->getSequenceSettings() == TEST_FIXED_SEQUENCE)? "tst_sequence_fixed":"tst_sequence_postpone"));
		
			$info->addSection($this->lng->txt("tst_heading_scoring"));
			$info->addProperty($this->lng->txt("tst_text_count_system"), $this->lng->txt(($this->object->getCountSystem() == COUNT_PARTIAL_SOLUTIONS)? "tst_count_partial_solutions":"tst_count_correct_solutions"));
			$info->addProperty($this->lng->txt("tst_score_mcmr_questions"), $this->lng->txt(($this->object->getMCScoring() == SCORE_ZERO_POINTS_WHEN_UNANSWERED)? "tst_score_mcmr_zero_points_when_unanswered":"tst_score_mcmr_use_scoring_system"));
			if ($this->object->isRandomTest())
			{
				$info->addProperty($this->lng->txt("tst_pass_scoring"), $this->lng->txt(($this->object->getPassScoring() == SCORE_BEST_PASS)? "tst_pass_best_pass":"tst_pass_last_pass"));
			}

			$info->addSection($this->lng->txt("tst_score_reporting"));
			$score_reporting_text = "";
			switch ($this->object->getScoreReporting())
			{
				case REPORT_AFTER_TEST:
					$score_reporting_text = $this->lng->txt("tst_report_after_test");
					break;
				case REPORT_ALWAYS:
					$score_reporting_text = $this->lng->txt("tst_report_after_first_question");
					break;
				case REPORT_AFTER_DATE:
					$score_reporting_text = $this->lng->txt("tst_report_after_date");
					break;
				case 4:
					$score_reporting_text = $this->lng->txt("tst_report_after_never");
					break;
			}
			$info->addProperty($this->lng->txt("tst_score_reporting"), $score_reporting_text); 
			$reporting_date = $this->object->getReportingDate();
			if ($reporting_date)
			{
				#preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $reporting_date, $matches);
				#$txt_reporting_date = date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]));
				#$info->addProperty($this->lng->txt("tst_score_reporting_date"), $txt_reporting_date);
				$info->addProperty($this->lng->txt('tst_score_reporting_date'),
					ilDatePresentation::formatDate(new ilDateTime($reporting_date,IL_CAL_TIMESTAMP)));
			}
	
			$info->addSection($this->lng->txt("tst_session_settings"));
			$info->addProperty($this->lng->txt("tst_nr_of_tries"), ($this->object->getNrOfTries() == 0)?$this->lng->txt("unlimited"):$this->object->getNrOfTries());
			if ($this->object->getNrOfTries() != 1)
			{
				$info->addProperty($this->lng->txt("tst_nr_of_tries_of_user"), ($this->object->getTestSession()->getPass() == false)?$this->lng->txt("tst_no_tries"):$this->object->getTestSession()->getPass());
			}

			if ($this->object->getEnableProcessingTime())
			{
				$info->addProperty($this->lng->txt("tst_processing_time"), $this->object->getProcessingTime());
			}
			if (strlen($this->object->getAllowedUsers()) && ($this->object->getAllowedUsersTimeGap()))
			{
				$info->addProperty($this->lng->txt("tst_allowed_users"), $this->object->getAllowedUsers());
			}
		
			$starting_time = $this->object->getStartingTime();
			if ($starting_time)
			{
				$info->addProperty($this->lng->txt("tst_starting_time"),
					ilDatePresentation::formatDate(new ilDateTime($starting_time,IL_CAL_TIMESTAMP)));
			}
			$ending_time = $this->object->getEndingTime();
			if ($ending_time)
			{
				$info->addProperty($this->lng->txt("tst_ending_time"),
					ilDatePresentation::formatDate(new ilDateTime($ending_time,IL_CAL_TIMESTAMP)));
			}
			$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
			// forward the command

			if($_GET['crs_show_result'] and !$this->object->getTestSequence()->getFirstSequence())
			{
				#ilUtil::sendInfo($this->lng->txt('crs_all_questions_answered_successfully'));
			}			
		}
		
		$this->ctrl->forwardCommand($info);
	}

	function addLocatorItems()
	{
		global $ilLocator;
		switch ($this->ctrl->getCmd())
		{
			case "run":
			case "infoScreen":
			case "redirectToInfoScreen":
			case "start":
			case "resume":
			case "previous":
			case "next":
			case "summary":
			case "finishTest":
			case "outCorrectSolution":
			case "passDetails":
			case "showAnswersOfUser":
			case "outUserResultsOverview":
			case "backFromSummary":
			case "show_answers":
			case "setsolved":
			case "resetsolved":
			case "outTestSummary":
			case "outQuestionSummary":
			case "gotoQuestion":
			case "selectImagemapRegion":
			case "confirmSubmitAnswers":
			case "finalSubmission":
			case "postpone":
			case "redirectQuestion":
			case "outUserPassDetails":
			case "checkPassword":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
				break;
			case "eval_stat":
			case "evalAllUsers":
			case "evalUserDetail":
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "eval_stat"), "", $_GET["ref_id"]);
				break;
			case "create":
			case "save":
			case "cancel":
			case "importFile":
			case "cloneAll":
			case "importVerifiedFile":
			case "cancelImport":
				break;
		default:
				$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
				break;
		}
	}
	
	function getBrowseForQuestionsTab(&$tabs_gui)
	{
		global $ilAccess;
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// edit page
			$tabs_gui->setBackTarget($this->lng->txt("backtocallingtest"), $this->ctrl->getLinkTarget($this, "questions"));
			$tabs_gui->addTarget("tst_browse_for_questions",
				$this->ctrl->getLinkTarget($this, "browseForQuestions"),
				array("browseForQuestions", "filter", "resetFilter", "resetTextFilter", "insertQuestions"),
				"", "", TRUE
			);
		}
	}
	
	function getRandomQuestionsTab(&$tabs_gui)
	{
		global $ilAccess;
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// edit page
			$tabs_gui->setBackTarget($this->lng->txt("backtocallingtest"), $this->ctrl->getLinkTarget($this, "questions"));
			$tabs_gui->addTarget("random_selection",
				$this->ctrl->getLinkTarget($this, "randomQuestions"),
				array("randomQuestions"),
				"", ""
			);
		}
	}

	function statisticsObject()
	{
	}

	/**
	* Shows the certificate editor
	*/
	function certificateObject()
	{
		include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
		include_once "./Modules/Test/classes/class.ilTestCertificateAdapter.php";
		$output_gui = new ilCertificateGUI(new ilTestCertificateAdapter($this->object));
		$output_gui->certificateEditor();
	}

	function getQuestionsSubTabs()
	{
		global $ilTabs, $ilCtrl;
		$ilTabs->activateTab('assQuestions');
		$a_cmd = $ilCtrl->getCmd();

		if (!$this->object->isRandomTest())
		{
                #if (in_array($this->object->getEnabledViewMode(), array('both', 'express'))) {
                    $questions_per_page = ($a_cmd == 'questions_per_page' || ($a_cmd == 'removeQuestions' && $_REQUEST['test_express_mode'])) ? true : false;

                    $this->tabs_gui->addSubTabTarget(
                            "questions_per_page_view",
                            $this->ctrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'showPage'),
                            "", "", "", $questions_per_page);
                #}
		}
		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		$template = new ilSettingsTemplate($this->object->getTemplate(), ilObjAssessmentFolderGUI::getSettingsTemplateConfig());

                if (!in_array('questions', $template->getHiddenTabs())) {
                    // questions subtab
                    $ilTabs->addSubTabTarget("edit_test_questions",
                             $this->ctrl->getLinkTarget($this,'questions'),
                             array("questions", "browseForQuestions", "questionBrowser", "createQuestion",
                             "randomselect", "filter", "resetFilter", "insertQuestions",
                             "back", "createRandomSelection", "cancelRandomSelect",
                             "insertRandomSelection", "removeQuestions", "moveQuestions",
                             "insertQuestionsBefore", "insertQuestionsAfter", "confirmRemoveQuestions",
                             "cancelRemoveQuestions", "executeCreateQuestion", "cancelCreateQuestion",
                             "addQuestionpool", "saveRandomQuestions", "saveQuestionSelectionMode"),
                             "");

                    if (in_array($a_cmd, array('questions', 'createQuestion')) || ($a_cmd == 'removeQuestions' && !$_REQUEST['test_express_mode']))
                            $this->tabs_gui->activateSubTab('edit_test_questions');
		}
                #}

		// print view subtab
		if (!$this->object->isRandomTest())
		{
			$ilTabs->addSubTabTarget("print_view",
				 $this->ctrl->getLinkTarget($this,'print'),
				 "print", "", "", $this->ctrl->getCmd() == 'print');
		}
			
	}
	
	function getStatisticsSubTabs()
	{
		global $ilTabs;
		
		// user results subtab
		$ilTabs->addSubTabTarget("eval_all_users",
			 $this->ctrl->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
			 array("outEvaluation", "detailedEvaluation", "exportEvaluation", "evalUserDetail", "passDetails",
			 	"outStatisticsResultsOverview", "statisticsPassDetails")
			 , "");
	
		// aggregated results subtab
		$ilTabs->addSubTabTarget("tst_results_aggregated",
			$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "eval_a"),
			array("eval_a"),
			"", "");
	
		// question export
		$ilTabs->addSubTabTarget("tst_single_results",
			$this->ctrl->getLinkTargetByClass("iltestevaluationgui", "singleResults"),
			array("singleResults"),
			"", "");
	}
	
	function getSettingsSubTabs($hiddenTabs = array())
	{
		global $ilTabs, $ilias;
		
		// general subtab
		$force_active = ($this->ctrl->getCmd() == "")
			? true
			: false;
		$ilTabs->addSubTabTarget("general",
			 $this->ctrl->getLinkTarget($this,'properties'),
			 array("properties", "saveProperties", "cancelProperties"),
			 array("", "ilobjtestgui", "ilcertificategui"),
			 "", $force_active);
                
		if (!in_array('mark_schema', $hiddenTabs)) {
                    // mark schema subtab
                    $ilTabs->addSubTabTarget(
                            "mark_schema",
                            $this->ctrl->getLinkTarget($this,'marks'),
                            array("marks", "addMarkStep", "deleteMarkSteps", "addSimpleMarkSchema",
                                    "saveMarks", "cancelMarks"),
                            array("", "ilobjtestgui", "ilcertificategui")
                    );
                }

		// scoring subtab
		$ilTabs->addSubTabTarget(
			"scoring",
			$this->ctrl->getLinkTarget($this,'scoring'),
			array("scoring"),
			array("", "ilobjtestgui", "ilcertificategui")
		);
	
		include_once './Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';
		if(!in_array('certificate', $hiddenTabs) && ilRPCServerSettings::getInstance()->isEnabled())
		{
			// certificate subtab
			$ilTabs->addSubTabTarget(
				"certificate",
				$this->ctrl->getLinkTarget($this,'certificate'),
				array("certificate", "certificateEditor", "certificateRemoveBackground", "certificateSave",
					"certificatePreview", "certificateDelete", "certificateUpload", "certificateImport"),
				array("", "ilobjtestgui", "ilcertificategui")
			);
		}

                if (!in_array('defaults', $hiddenTabs)) {
                    // defaults subtab
                    $ilTabs->addSubTabTarget(
                            "defaults",
                            $this->ctrl->getLinkTarget($this, "defaults"),
                            array("defaults", "deleteDefaults", "addDefaults", "applyDefaults"),
                            array("", "ilobjtestgui", "ilcertificategui")
                    );
                }
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess,$ilUser;

                if (preg_match('/^ass(.*?)gui$/i', $this->ctrl->getNextClass($this))) {
                    return;
                }
                else if ($this->ctrl->getNextClass($this) == 'ilpageobjectgui') {
                    return;
                }

		$hidden_tabs = array();
		
		$template = $this->object->getTemplate();
		if($template)
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template = new ilSettingsTemplate($template, ilObjAssessmentFolderGUI::getSettingsTemplateConfig());

			$hidden_tabs = $template->getHiddenTabs();
		}

		switch ($this->ctrl->getCmd())
		{
			case "resume":
			case "previous":
			case "next":
			case "summary":
			case "directfeedback":
			case "finishTest":
			case "outCorrectSolution":
			case "passDetails":
			case "showAnswersOfUser":
			case "outUserResultsOverview":
			case "backFromSummary":
			case "show_answers":
			case "setsolved":
			case "resetsolved":
			case "confirmFinish":
			case "outTestSummary":
			case "outQuestionSummary":
			case "gotoQuestion":
			case "selectImagemapRegion":
			case "confirmSubmitAnswers":
			case "finalSubmission":
			case "postpone":
			case "redirectQuestion":
			case "outUserPassDetails":
			case "checkPassword":
			case "exportCertificate":
			case "finishListOfAnswers":
			case "backConfirmFinish":
			case "showFinalStatement":
				return;
				break;
			case "browseForQuestions":
			case "filter":
			case "resetFilter":
			case "resetTextFilter":
			case "insertQuestions":
				return $this->getBrowseForQuestionsTab($tabs_gui);
				break;
			case "scoring":
			case "properties":
			case "marks":
			case "saveMarks":
			case "cancelMarks":
			case "addMarkStep":
			case "deleteMarkSteps":
			case "addSimpleMarkSchema":
			case "certificate":
			case "certificateservice":
			case "certificateImport":
			case "certificateUpload":
			case "certificateEditor":
			case "certificateDelete":
			case "certificateSave":
			case "defaults":
			case "deleteDefaults":
			case "addDefaults":
			case "applyDefaults":
			case "inviteParticipants":
			case "searchParticipants":
			case "":
				if (($ilAccess->checkAccess("write", "", $this->ref_id)) && ((strcmp($this->ctrl->getCmdClass(), "ilobjtestgui") == 0) || (strcmp($this->ctrl->getCmdClass(), "ilcertificategui") == 0) || (strlen($this->ctrl->getCmdClass()) == 0)))
				{
					$this->getSettingsSubTabs($hidden_tabs);
				}
				break;
			case "export":
			case "print":
				break;
			case "statistics":
			case "eval_a":
			case "detailedEvaluation":
			case "outEvaluation":
			case "singleResults":
			case "exportEvaluation":
			case "evalUserDetail":
			case "passDetails":
			case "outStatisticsResultsOverview":
			case "statisticsPassDetails":
				$this->getStatisticsSubTabs();
				break;
		}

		if (strcmp(strtolower(get_class($this->object)), "ilobjtest") == 0)
		{
			// questions tab
			if ($ilAccess->checkAccess("write", "", $this->ref_id) && !in_array('assQuestions', $hidden_tabs))
			{
				$force_active = ($_GET["up"] != "" || $_GET["down"] != "")
					? true
					: false;
				if (!$force_active)
				{
					if ($_GET["browse"] == 1) $force_active = true;
					if (preg_match("/deleteqpl_\d+/", $this->ctrl->getCmd()))
					{
						$force_active = true;
					}
				}

				if ($this->object->isRandomTest()) {
				    $target = $this->ctrl->getLinkTarget($this,'questions');
				}
				else {
				    $target = $this->ctrl->getLinkTargetByClass('iltestexpresspageobjectgui','showPage');
				}

				$tabs_gui->addTarget("assQuestions",
					 //$this->ctrl->getLinkTarget($this,'questions'),
					 $target,
					 array("questions", "browseForQuestions", "questionBrowser", "createQuestion", 
					 "randomselect", "filter", "resetFilter", "insertQuestions",
					 "back", "createRandomSelection", "cancelRandomSelect",
					 "insertRandomSelection", "removeQuestions", "moveQuestions",
					 "insertQuestionsBefore", "insertQuestionsAfter", "confirmRemoveQuestions",
					 "cancelRemoveQuestions", "executeCreateQuestion", "cancelCreateQuestion",
					 "addQuestionpool", "saveRandomQuestions", "saveQuestionSelectionMode", "print",
					"addsource", "removesource", "randomQuestions"), 
					 "", "", $force_active);
			}

			// info tab
			if ($ilAccess->checkAccess("visible", "", $this->ref_id) && !in_array('info_short', $hidden_tabs))
			{
				$tabs_gui->addTarget("info_short",
					 $this->ctrl->getLinkTarget($this,'infoScreen'),
					 array("infoScreen", "outIntroductionPage", "showSummary", 
					 "setAnonymousId", "outUserListOfAnswerPasses", "redirectToInfoScreen"));
			}
			
			// settings tab
			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
                            if (!in_array('settings', $hidden_tabs)) {
				$tabs_gui->addTarget("settings",
					$this->ctrl->getLinkTarget($this,'properties'),
						array("properties", "saveProperties", "cancelProperties",
							"marks", "addMarkStep", "deleteMarkSteps", "addSimpleMarkSchema",
							"saveMarks", "cancelMarks", 
							"certificate", "certificateEditor", "certificateRemoveBackground",
							"certificateSave", "certificatePreview", "certificateDelete", "certificateUpload",
							"certificateImport", "scoring", "defaults", "addDefaults", "deleteDefaults", "applyDefaults",
							"inviteParticipants", "saveFixedParticipantsStatus", "searchParticipants", "addParticipants", 
							""
					),
					 array("", "ilobjtestgui", "ilcertificategui")
				);
                            }

                            if (!in_array('participants', $hidden_tabs)) {
				// participants
				$tabs_gui->addTarget("participants",
					 $this->ctrl->getLinkTarget($this,'participants'),
					 array("participants", "saveClientIP",
					 "removeParticipant", 
					 "showParticipantAnswersForAuthor",
					 "deleteAllUserResults",
					 "cancelDeleteAllUserData", "deleteSingleUserResults",
					 "outParticipantsResultsOverview", "outParticipantsPassDetails",
					 "showPassOverview", "showUserAnswers", "participantsAction",
					"showDetailedResults"), 
					 "");
                            }
			}

			include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
			if(ilLearningProgressAccess::checkAccess($this->object->getRefId()) && !in_array('learning_progress', $hidden_tabs))
			{
				$tabs_gui->addTarget('learning_progress',
									 $this->ctrl->getLinkTargetByClass(array('illearningprogressgui'),''),
									 '',
									 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
			}

			if ($ilAccess->checkAccess("write", "", $this->ref_id)  && !in_array('manscoring', $hidden_tabs))
			{
				include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
				$scoring = ilObjAssessmentFolder::_getManualScoring();
				if (count($scoring))
				{
					// scoring tab
					$tabs_gui->addTarget("manscoring",
						 $this->ctrl->getLinkTargetByClass("iltestscoringgui", "manscoring"),
						 array("manscoring", "scoringfilter", "scoringfilterreset", "setPointsManual", "setFeedbackManual", "setManscoringDone"),
						 "");
				}
			}

			if ((($ilAccess->checkAccess("tst_statistics", "", $this->ref_id)) || ($ilAccess->checkAccess("write", "", $this->ref_id)))  && !in_array('statistics', $hidden_tabs))
			{
				// statistics tab
				$tabs_gui->addTarget("statistics",
					 $this->ctrl->getLinkTargetByClass("iltestevaluationgui", "outEvaluation"),
					 array("statistics", "outEvaluation", "exportEvaluation", "detailedEvaluation", "eval_a", "evalUserDetail",
					 	"passDetails", "outStatisticsResultsOverview", "statisticsPassDetails", "singleResults")
					 , "");
			}

			if ($ilAccess->checkAccess("write", "", $this->ref_id))
			{
                             if (!in_array('history', $hidden_tabs)) {

				// history
				$tabs_gui->addTarget("history",
					 $this->ctrl->getLinkTarget($this,'history'),
					 "history", "");
                             }

                             if (!in_array('meta_data', $hidden_tabs)) {
				// meta data
				$tabs_gui->addTarget("meta_data",
					 $this->ctrl->getLinkTargetByClass('ilmdeditorgui','listSection'),
					 "", "ilmdeditorgui");
                             }

                             if (!in_array('export', $hidden_tabs)) {
				// export tab
				$tabs_gui->addTarget("export",
					 $this->ctrl->getLinkTarget($this,'export'),
					 array("export", "createExportFile", "confirmDeleteExportFile",
					 "downloadExportFile", "deleteExportFile", "cancelDeleteExportFile"),
					 "");
                             }
			}
			
			if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id)&& !in_array('permissions', $hidden_tabs))
			{
				$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
			}
		}
	}
	
	/**
	* Redirect script to call a test with the test reference id
	* 
	* Redirect script to call a test with the test reference id
	*
	* @param integer $a_target The reference id of the test
	* @access	public
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			//include_once "./Services/Utilities/classes/class.ilUtil.php";
			$_GET["baseClass"] = "ilObjTestGUI";
			$_GET["cmd"] = "infoScreen";
			$_GET["ref_id"] = $a_target;
			include_once("ilias.php");
			exit;
			//ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=infoScreen&ref_id=$a_target");
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
	}





	/**
	 * Questions per page
	 *
	 * @param
	 * @return
	 */
	function showQuestionsPerPageObject($qid = 0)
	{
            if ($this->create_question_mode)
                    return;

		global $ilToolbar, $ilCtrl, $lng;

                $this->getQuestionsSubTabs();

                $ilCtrl->saveParameter($this, 'q_mode');

                $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'test_express_mode', 1);
		$ilCtrl->setParameter($this, 'test_express_mode', 1);
                $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', $_REQUEST['q_id']);
		$ilCtrl->setParameter($this, 'q_id', $_REQUEST['q_id']);
                $ilToolbar->setFormAction($ilCtrl->getFormActionByClass('iltestexpresspageobjectgui', 'edit'));

                if ($this->object->evalTotalPersons() == 0) {
		    /*
		    include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
		    $pool = new ilObjQuestionPool();
		    $questionTypes = $pool->getQuestionTypes();$options = array();
		    foreach($questionTypes as $label => $data) {
			$options[$data['question_type_id']] = $label;
		    }

                    include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
                    $si = new ilSelectInputGUI($lng->txt("test_add_new_question"), "qtype");
                    $si->setOptions($options);
                    $ilToolbar->addInputItem($si, true);
		    /*
                    // use pool
                    if ($this->object->isExpressModeQuestionPoolAllowed()) {
                        include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
                        $cb = new ilCheckboxInputGUI($lng->txt("test_use_pool"), "use_pool");
                        $ilToolbar->addInputItem($cb, true);
                    }
		    */
                    $ilToolbar->addFormButton($lng->txt("test_add_new_question"), "addQuestion");
                }

                $questions = $this->object->getQuestionTitlesAndIndexes();

		// desc
                $options = array();
                foreach($questions as $id => $label) {
		    $options[$id] = $label;
                }

                $optionKeys = array_keys($options);

                if (!$options) {
                    $options[] = $lng->txt('none');
                }
                //else if (count($options) > 1) {
//                    $addSeparator = false;
//                    if ($optionKeys[0] != $qid) {
//                        //$ilToolbar->addFormButton($lng->txt("test_prev_question"), "prevQuestion");
//                        $ilToolbar->addLink($lng->txt("test_prev_question"), $ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'prevQuestion'));
//                        $addSeparator = true;
//                    }
//		    else {
//			$ilToolbar->addSpacer(45);
//		    }
//
//                    if ($optionKeys[count($optionKeys)-1] != $qid) {
//                        //$ilToolbar->addFormButton($lng->txt("test_next_question"), "nextQuestion");
//                        $ilToolbar->addLink($lng->txt("test_next_question"), $ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'nextQuestion'));
//			$addSeparator = true;
//                    }
//		    else {
//			$ilToolbar->addSpacer(45);
//		    }
//
//                    //if ($addSeparator) {
//                        $ilToolbar->addSeparator();
//                    //}

		if (count($questions)) {
		    $ilToolbar->addSeparator();

		    $ilToolbar->addLink($lng->txt("test_prev_question"), $ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'prevQuestion'), !(count($options) > 1 && $optionKeys[0] != $qid));
		    $ilToolbar->addLink($lng->txt("test_next_question"), $ilCtrl->getLinkTargetByClass('iltestexpresspageobjectgui', 'nextQuestion'), !(count($options) > 1 && $optionKeys[count($optionKeys)-1] != $qid));
		}

                if (count($questions) > 1) {

		    $ilToolbar->addSeparator();

                    include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
                    $si = new ilSelectInputGUI($lng->txt("test_jump_to"), "q_id");
                    $si->addCustomAttribute("onChange=\"forms['ilToolbar'].submit();\"");
                    $si->setOptions($options);

                    if ($qid) {
                        $si->setValue($qid);
                    }

                    $ilToolbar->addInputItem($si, true);
                }

		$total = $this->object->evalTotalPersons();

                /*if (count($options)) {
                    include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
                    $si = new ilSelectInputGUI($lng->txt("test_jump_to"), "q_id");
                    $si->addCustomAttribute("onChange=\"forms['ilToolbar'].submit();\"");
                    $si->setOptions($options);

                    if ($qid) {
                        $si->setValue($qid);
                    }

                    $ilToolbar->addInputItem($si, true);
                }*/

		if (count($questions) && !$total) {
		    $ilCtrl->setParameter($this, 'q_id', $_REQUEST['q_id']);
		    $ilToolbar->addSeparator();
		    $ilToolbar->addButton($lng->txt("test_delete_page"), $ilCtrl->getLinkTarget($this, "removeQuestions"));
		}

		if (count($questions) > 1 && !$total) {
		    $ilToolbar->addSeparator();
		    $ilToolbar->addButton($lng->txt("test_move_page"), $ilCtrl->getLinkTarget($this, "movePageForm"));
		}

		//$ilToolbar->addFormButton($lng->txt("go"), "showPage");

	}

	public function copyQuestionsToPoolObject($returnResult = false) {
            //var_dump($_REQUEST);
            include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
            $qpool = new ilObjQuestionPool($_REQUEST['sel_qpl'], true);
            $qpool->setOnline(ilObjQuestionPool::_lookupOnline($_REQUEST['sel_qpl'], true));

            $newIds = array();
            foreach($_REQUEST['q_id'] as $q_id) {
                $newId = $qpool->copyQuestion($q_id, $qpool->getId());
                $newIds[$q_id] = $newId;
            }

            $result = new stdClass();
            $result->ids = $newIds;
            $result->qpool = $qpool;

            if ($returnResult)
                return $result;
            else
                $this->backObject();
        }

        public function copyAndLinkQuestionsToPoolObject() {
            $result = $this->copyQuestionsToPoolObject(true);
            
            foreach($result->ids as $oldId => $newId) {
                $questionInstance = assQuestion::_instanciateQuestion($oldId);
                $questionInstance->setNewOriginalId($newId);
                $questionInstance->setObjId($result->qpool->getId());
                $questionInstance->saveToDb();

            }
            
            $this->backObject();
        }

        private function getQuestionpoolCreationForm() {
            global $lng;
            include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
            $form = new ilPropertyFormGUI();

            $title = new ilTextInputGUI($lng->txt('title'), 'title');
            $title->setRequired(true);
            $form->addItem($title);

            $description = new ilTextAreaInputGUI($lng->txt('description'), 'description');
            $form->addItem($description);

            $form->addCommandButton('createQuestionPoolAndCopy', $lng->txt('create'));

            foreach($_REQUEST['q_id'] as $id) {
                $hidden = new ilHiddenInputGUI('q_id[]');
                $hidden->setValue($id);
                $form->addItem($hidden);

            }

            return $form;
        }

        public function copyToQuestionpoolObject() {
            $this->createQuestionpoolTargetObject('copyQuestionsToPool');
        }

        public function copyAndLinkToQuestionpoolObject() {
            $this->createQuestionpoolTargetObject('copyAndLinkQuestionsToPool');
        }

        public function createQuestionPoolAndCopyObject() {
            $form = $this->getQuestionpoolCreationForm();

            $ref_id = $this->createQuestionPool($_REQUEST['title'], $_REQUEST['description']);
            $_REQUEST['sel_qpl'] = $ref_id;

            if ($_REQUEST['link']) {
                $this->copyAndLinkQuestionsToPoolObject();
            }
            else {
                $this->copyQuestionsToPoolObject();
            }
        }

	/**
	* Called when a new question should be created from a test
*
	* @access	public
	*/
	function createQuestionpoolTargetObject($cmd)
	{
		global $ilUser, $ilTabs;
		$this->getQuestionsSubTabs();
		$ilTabs->activateSubTab('edit_test_questions');
                
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_qpl_select_copy.html", "Modules/Test");
		$questionpools =& $this->object->getAvailableQuestionpools(FALSE, FALSE, FALSE, TRUE, FALSE, "write");
		if (count($questionpools) == 0)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_QPL", "");
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			foreach ($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("option");
				$this->tpl->setVariable("VALUE_OPTION", $key);
				$this->tpl->setVariable("TEXT_OPTION", $value["title"]);
				$this->tpl->parseCurrentBlock();
			}
		}
                foreach($_REQUEST['q_id'] as $id) {
                    $this->tpl->setCurrentBlock("hidden");
                    $this->tpl->setVariable("HIDDEN_NAME", "q_id[]");
                    $this->tpl->setVariable("HIDDEN_VALUE", $id);
                    $this->tpl->parseCurrentBlock();
                    $this->tpl->setCurrentBlock("adm_content");
                }
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));

		if (count($questionpools) == 0)
		{
			$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_enter_questionpool"));
		}
		else
		{
			$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_select_questionpool"));
		}

                $this->tpl->setVariable("CMD_SUBMIT", $cmd);
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));

                $createForm = $this->getQuestionpoolCreationForm();
                switch($cmd) {
                    case 'copyAndLinkQuestionsToPool':
                        $hidden = new ilHiddenInputGUI('link');
                        $hidden->setValue(1);
                        $createForm->addItem($hidden);
                        break;
                    case 'copyQuestionsToPool':
                        break;
                }
                $createForm->setFormAction($this->ctrl->getFormAction($this));
                #$this->tpl->setVariable('CREATE_QPOOL_FORM', $createForm->getHTML());

		$this->tpl->parseCurrentBlock();
	}

        private function applyTemplate($templateData, $object) {
            // map formFieldName => setterName
            $simpleSetters = array(
                'anonymity' => 'setAnonymity',
                'random_test' => 'setRandomTest',
                'test_enabled_views' => 'setEnabledViewMode',
                //'express_allow_question_pool' => 'setExpressModeQuestionPoolAllowed',
                'introduction' => 'setIntroduction',
                'showinfo' => 'setShowInfo',
                'finalstatement' => 'setFinalStatement',
                'showfinalstatement' => 'setShowFinalStatement',
                'chb_shuffle_questions' => 'setShuffleQuestions',
                'list_of_questions' => 'setListOfQuestionsSettings',
                'chb_show_marker' => 'setShowMarker',
                'chb_show_cancel' => 'setShowCancel',
                'kiosk' => 'setKiosk',
                'nr_of_tries' => 'setNrOfTries',
                'chb_processing_time' => 'setEnableProcessingTime',
                'chb_use_previous_answers' => 'setUsePreviousAnswers',
                'forcejs' => 'setForceJS',
                'title_output' => 'setTitleOutput',
                'password' => 'setPassword',
                'fixedparticipants' => 'setFixedParticipants',
                'allowedUsers' => 'setAllowedUsers',
                'allowedUsersTimeGap' => 'setAllowedUsersTimeGap',
                'mailnotification' => 'setMailNotification',
                'mailnottype' => 'setMailNotificationType',
                //'' => '',
                'count_system' => 'setCountSystem',
                'mc_scoring' => 'setMCScoring',
                'score_cutting' => 'setScoreCutting',
                'pass_scoring' => 'setScoreReporting',

                'instant_feedback' => 'setScoringFeedbackOptionsByArray',

                'results_presentation' => 'setResultsPresentationOptionsByArray',
                'export_settings' => 'setExportSettings',
            );

	    if (!$templateData['results_presentation']['value']) {
		$templateData['results_presentation']['value'] = array();
	    }

            foreach($simpleSetters as $field => $setter) {
                if($templateData[$field]) {
                    $object->$setter($templateData[$field]['value']);
                }
            }
        }

        private function formShowGeneralSection($templateData) {
	    // alway show because of title and description
	    return true;
        }

	private function formShowBeginningEndingInformation($templateData) {
	    // show always because of statement text areas
	    return true;
	}

        private function formShowPresentationSection($templateData) {
	    // show always because of "previous answer" setting
	    return true;
        }

        private function formShowSequenceSection($templateData) {
	    // show always because of "list of question" and "shuffle"
            return true;
        }

        private function formShowKioskSection($templateData) {
            $fields = array(
                'kiosk',
                );
            return $this->formsectionHasVisibleFields($templateData, $fields);
        }

        private function formShowSessionSection($templateData) {
            // show always because of "nr_of_tries", "chb_processing_time", "chb_starting_time", "chb_ending_time"
            return true;
        }

        private function formShowParticipantSection($templateData) {
            $fields = array(
                'fixedparticipants',
                'allowedUsers',
                'allowedUsersTimeGap',
                );
            return $this->formsectionHasVisibleFields($templateData, $fields);
        }

        private function formShowNotificationSection($templateData) {
            $fields = array(
                'mailnotification',
                'mailnottype',
                );
            return $this->formsectionHasVisibleFields($templateData, $fields);
        }

        private function formsectionHasVisibleFields($templateData, $fields) {
            foreach($fields as $fld) {
                if (isset($templateData[$fld])) {
                    if(!$templateData[$fld]['hide'])
                        return true;
                }
                else {
                    return true;
                }
            }
            return false;
        }

	/**
	 * Enable all settings - Confirmation
	 */
	function confirmResetTemplateObject()
	{
		ilUtil::sendQuestion($this->lng->txt("test_confirm_template_reset"));
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_tst_tst_confirm_resettemplate.html", "Modules/Test");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BTN_CONFIRM_REMOVE", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL_REMOVE", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "resetTemplateObject"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * Enable all settings - remove template
	 */
	function resetTemplateObject()
	{
		$this->object->setTemplate(null);
		$this->object->saveToDB();

		ilUtil::sendSuccess($this->lng->txt("test_template_reset"), true);
		$this->ctrl->redirect($this, "properties");
	}

	public function saveOrderObject() {
	    global $ilAccess;
	    if (!$ilAccess->checkAccess("write", "", $this->ref_id))
	    {
		    // allow only write access
		    ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
		    $this->ctrl->redirect($this, "infoScreen");
	    }

	    global $ilCtrl;
	    $this->object->setQuestionOrder($_REQUEST['order']);

	    $ilCtrl->redirect($this, 'questions');
	}

	/**
	 * Move current page
	 */
	protected function movePageFormObject()
	{
		global $lng, $ilCtrl, $tpl;

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "movePage"));
		$form->setTitle($lng->txt("test_move_page"));

		$old_pos = new ilHiddenInputGUI("q_id");
		$old_pos->setValue($_REQUEST['q_id']);
		$form->addItem($old_pos);

		$questions = $this->object->getQuestionTitlesAndIndexes();
		if (!is_array($questions))
		    $questions = array();

		foreach($questions as $k => $q) {
		    if ($k == $_REQUEST['q_id']) {
			unset($questions[$k]);
			continue;
		    }
		    $questions[$k] = $lng->txt('behind') . ' '. $q;
		}
		#$questions['0'] = $lng->txt('first');

		$options = array(
		    0 => $lng->txt('first')
		);
		foreach($questions as $k => $q) {
		    $options[$k] = $q;
		}

		$pos = new ilSelectInputGUI($lng->txt("position"), "position_after");
		$pos->setOptions($options);
		$form->addItem($pos);

		$form->addCommandButton("movePage", $lng->txt("submit"));
		$form->addCommandButton("showPage", $lng->txt("cancel"));

		return $tpl->setContent($form->getHTML());
	}

	public function movePageObject() {
	    global $ilAccess;
	    if (!$ilAccess->checkAccess("write", "", $this->ref_id))
	    {
		    // allow only write access
		    ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
		    $this->ctrl->redirect($this, "infoScreen");
	    }
	    
	    $this->object->moveQuestionAfter($_REQUEST['q_id'], $_REQUEST['position_after']);
	    $this->showPageObject();
	}

	public function showPageObject() {
	    global $ilCtrl;

	    $ilCtrl->setParameterByClass('iltestexpresspageobjectgui', 'q_id', $_REQUEST['q_id']);
	    $ilCtrl->redirectByClass('iltestexpresspageobjectgui', 'showPage');
	}

	public function copyQuestionObject() {
	    global $ilAccess;
	    if (!$ilAccess->checkAccess("write", "", $this->ref_id))
	    {
		    // allow only write access
		    ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
		    $this->ctrl->redirect($this, "infoScreen");
	    }

	    if ($_REQUEST['q_id'] && !is_array($_REQUEST['q_id']))
		$ids = array($_REQUEST['q_id']);
	    else if ($_REQUEST['q_id'])
		$ids = $_REQUEST['q_id'];
	    else
	    {
		ilUtil::sendFailure( $this->lng->txt('copy_no_questions_selected'), true );
		$this->ctrl->redirect($this, 'questions');
	    }

	    $copy_count = 0;

	    $questionTitles = $this->object->getQuestionTitles();

	    foreach($ids as $id)
	    {
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$question = assQuestion::_instanciateQuestionGUI($id);
		if ($question)
		{
		    $title = $question->object->getTitle();
		    $i = 2;
		    while(  in_array( $title . ' (' . $i . ')', $questionTitles ))
			    $i++;

		    $title .= ' (' . $i . ')';

		    $questionTitles[] = $title;

		    $new_id = $question->object->duplicate(false, $title);

		    $clone = assQuestion::_instanciateQuestionGUI($new_id);
		    $clone->object->setObjId($this->object->getId());
		    $clone->object->saveToDb();

		    $this->object->insertQuestion($new_id, true);

		    $copy_count++;
		}
	    }

	    ilUtil::sendSuccess($this->lng->txt('copy_questions_success'), true);

	    $this->ctrl->redirect($this, 'questions');
	}
} // END class.ilObjTestGUI
?>

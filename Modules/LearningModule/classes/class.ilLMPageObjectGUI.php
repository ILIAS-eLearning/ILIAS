<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\UI\Component\Input\Container\Form;

/**
 * User Interface for Learning Module Page Objects Editing
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilLMPageObjectGUI: ilLMPageGUI, ilAssGenFeedbackPageGUI
 */
class ilLMPageObjectGUI extends ilLMObjectGUI
{
    protected ilPropertyFormGUI $form;
    protected ilTabsGUI $tabs;
    protected ilSetting $settings;
    protected \ILIAS\Style\Content\DomainService $content_style_domain;

    public function __construct(
        ilObjLearningModule $a_content_obj
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->settings = $DIC->settings();
        $this->lng = $DIC->language();
        parent::__construct($a_content_obj);
        $cs = $DIC->contentStyle();
        $this->content_style_domain = $cs->domain();
    }

    /**
     * Set content object dependent page object (co page)
     */
    public function setLMPageObject(ilLMPageObject $a_pg_obj) : void
    {
        $this->obj = $a_pg_obj;
        $this->obj->setLMId($this->content_object->getId());
    }

    public function executeCommand() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        switch ($next_class) {
            case "illmpagegui":

                $lm_set = new ilSetting("lm");

                $this->ctrl->setReturn($this, "edit");
                if (!ilPageObject::_exists("lm", $this->obj->getId(), $this->requested_transl) &&
                    ilPageObject::_exists("lm", $this->obj->getId(), "-")) {
                    if ($this->requested_totransl == "") {
                        $this->requested_totransl = $this->requested_transl;
                        $ilCtrl->setCmd("switchToLanguage");
                    }
                    $ilCtrl->setCmdClass("illmpagegui");
                    $page_gui = new ilLMPageGUI($this->obj->getId(), 0, false, "-");
                } else {
                    $page_gui = new ilLMPageGUI($this->obj->getId());
                }
                $page_gui->setEditPreview(true);
                $page_gui->activateMetaDataEditor(
                    $this->content_object,
                    $this->obj->getType(),
                    $this->obj->getId(),
                    $this->obj,
                    "MDUpdateListener"
                );
                if ($ilSetting->get("block_activated_news")) {
                    $page_gui->setEnabledNews(
                        true,
                        $this->obj->content_object->getId(),
                        $this->obj->content_object->getType()
                    );
                }

                $page_gui->setViewPageLink(
                    ILIAS_HTTP_PATH . "/goto.php?target=pg_" . $this->obj->getId() .
                    "_" . $this->requested_ref_id
                );

                $page_gui->setStyleId($this->content_style_domain
                    ->styleForRefId($this->content_object->getRefId())
                    ->getEffectiveStyleId());
                $page_gui->setTemplateTargetVar("ADM_CONTENT");
                $page_gui->getPageObject()->buildDom();
                $int_links = $page_gui->getPageObject()->getInternalLinks();
                $link_xml = $this->getLinkXML($int_links);
                $page_gui->setLinkXml($link_xml);

                $page_gui->enableChangeComments($this->content_object->isActiveHistoryUserComments());
                $page_gui->setFileDownloadLink("ilias.php?cmd=downloadFile&ref_id=" . $this->requested_ref_id . "&baseClass=ilLMPresentationGUI");
                $page_gui->setFullscreenLink("ilias.php?cmd=fullscreen&ref_id=" . $this->requested_ref_id . "&baseClass=ilLMPresentationGUI");
                $page_gui->setLinkParams("ref_id=" . $this->content_object->getRefId());
                $page_gui->setSourcecodeDownloadScript("ilias.php?ref_id=" . $this->requested_ref_id . "&baseClass=ilLMPresentationGUI");
                $page_gui->setPresentationTitle(
                    ilLMPageObject::_getPresentationTitle(
                        $this->obj->getId(),
                        $this->content_object->getPageHeader(),
                        $this->content_object->isActiveNumbering()
                    )
                );
                //$page_gui->setLocator($contObjLocator);
                $page_gui->setHeader($this->lng->txt("page") . ": " . $this->obj->getTitle());
                $page_gui->setActivationListener($this, "activatePage");
                
                $up_gui = "ilobjlearningmodulegui";
                $ilCtrl->setParameterByClass($up_gui, "active_node", $this->obj->getId());

                $tpl->setTitleIcon(ilUtil::getImagePath("icon_pg.svg"));
                $tpl->setTitle($this->lng->txt("page") . ": " . $this->obj->getTitle());
                if ($this->content_object->getLayoutPerPage()) {
                    $page_gui->setTabHook($this, "addPageTabs");
                }
                $ret = $this->ctrl->forwardCommand($page_gui);
                if ($ret != "") {               // in 6.0 this overwrites already set content with an empty string sometimes
                    $tpl->setContent($ret);
                }
                break;

            default:
                $this->$cmd();
                break;
        }
    }


    /**
     * display content of page (edit view)
     */
    public function edit() : void
    {
        $this->ctrl->setCmdClass("ilLMPageGUI");
        $this->ctrl->setCmd("edit");
        $this->executeCommand();
    }

    /**
     * display content of page (edit view)
     */
    public function preview() : void
    {
        $this->ctrl->setCmdClass("ilLMPageGUI");
        $this->ctrl->setCmd("preview");
        $this->executeCommand();
    }


    /**
     * cancel
     */
    public function cancel() : void
    {
        if ($this->requested_obj_id != 0) {
            ilUtil::redirect($this->ctrl->getLinkTargetByClass(
                "ilStructureObjectGUI",
                "view",
                "",
                true
            ));
        }
        $up_gui = "ilobjlearningmodulegui";
        $this->ctrl->redirectByClass($up_gui, "pages");
    }

    /**
     * get link targets
     */
    public function getLinkXML(array $a_int_links) : string
    {
        $link_info = "<IntLinkInfos>";
        foreach ($a_int_links as $int_link) {
            $target = $int_link["Target"];
            $ltarget = "";
            $href = "";
            $lcontent = "";
            if (substr($target, 0, 4) == "il__") {
                $target_arr = explode("_", $target);
                $target_id = $target_arr[count($target_arr) - 1];
                $type = $int_link["Type"];
                $targetframe = ($int_link["TargetFrame"] != "")
                    ? $int_link["TargetFrame"]
                    : "None";
                    
                // anchor
                $anc = $anc_add = "";
                if ($int_link["Anchor"] != "") {
                    $anc = $int_link["Anchor"];
                    $anc_add = "_" . rawurlencode($int_link["Anchor"]);
                }

                switch ($type) {
                    case "PageObject":
                    case "StructureObject":
                        $lm_id = ilLMObject::_lookupContObjID($target_id);
                        $cont_obj = $this->content_object;
                        if ($lm_id == $cont_obj->getId()) {
                            $ltarget = "";
                            if ($type == "PageObject") {
                                $this->ctrl->setParameter($this, "obj_id", $target_id);
                                $href = $this->ctrl->getLinkTargetByClass(
                                    get_class($this),
                                    "edit",
                                    "",
                                    false,
                                    true
                                );
                            } else {
                                $this->ctrl->setParameterByClass("ilstructureobjectgui", "obj_id", $target_id);
                                $href = $this->ctrl->getLinkTargetByClass(
                                    "ilstructureobjectgui",
                                    "view",
                                    "",
                                    false,
                                    true
                                );
                            }
                            $href = str_replace("&", "&amp;", $href);
                            $this->ctrl->setParameter($this, "obj_id", $this->requested_obj_id);
                        } else {
                            if ($type == "PageObject") {
                                $href = "goto.php?target=pg_" . $target_id . $anc_add;
                            } else {
                                $href = "goto.php?target=st_" . $target_id;
                            }
                            $ltarget = "ilContObj" . $lm_id;
                        }
                        if ($lm_id == "") {
                            $href = "";
                        }
                        break;

                    case "GlossaryItem":
                        $ltarget = $nframe = "_blank";
                        $href = "ilias.php?cmdClass=illmpresentationgui&amp;baseClass=ilLMPresentationGUI&amp;" .
                            "obj_type=$type&amp;cmd=glossary&amp;ref_id=" . $this->requested_ref_id .
                            "&amp;obj_id=" . $target_id . "&amp;frame=$nframe";
                        break;

                    case "MediaObject":
                        $ltarget = $nframe = "_blank";
                        $href = "ilias.php?cmdClass=illmpresentationgui&amp;baseClass=ilLMPresentationGUI&amp;obj_type=$type&amp;cmd=media&amp;ref_id=" . $this->requested_ref_id .
                            "&amp;mob_id=" . $target_id . "&amp;frame=$nframe";
                        break;
                        
                    case "RepositoryItem":
                        $obj_type = ilObject::_lookupType($target_id, true);
                        $obj_id = ilObject::_lookupObjId($target_id);
                        $href = "./goto.php?target=" . $obj_type . "_" . $target_id;
                        $t_frame = ilFrameTargetInfo::_getFrame("MainContent", $obj_type);
                        $ltarget = $t_frame;
                        break;

                    case "File":
                        $this->ctrl->setParameter($this, "file_id", "il__file_" . $target_id);
                        $href = $this->ctrl->getLinkTarget(
                            $this,
                            "downloadFile",
                            "",
                            false,
                            true
                        );
                        $this->ctrl->setParameter($this, "file_id", null);
                        break;

                    case "User":
                        $obj_type = ilObject::_lookupType($target_id);
                        if ($obj_type == "usr") {
                            $back = $this->ctrl->getLinkTarget(
                                $this,
                                "edit",
                                "",
                                false,
                                true
                            );
                            //var_dump($back); exit;
                            $this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user_id", $target_id);
                            $this->ctrl->setParameterByClass(
                                "ilpublicuserprofilegui",
                                "back_url",
                                rawurlencode($back)
                            );
                            $href = "";
                            if (ilUserUtil::hasPublicProfile($target_id)) {
                                $href = $this->ctrl->getLinkTargetByClass(
                                    "ilpublicuserprofilegui",
                                    "getHTML",
                                    "",
                                    false,
                                    true
                                );
                            }
                            $this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user_id", null);
                            $lcontent = ilUserUtil::getNamePresentation($target_id, false, false);
                        }
                        break;

                }

                if ($href != "") {
                    $anc_par = 'Anchor="' . $anc . '"';
                    $link_info .= "<IntLinkInfo Target=\"$target\" Type=\"$type\" " .
                        "TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" LinkContent=\"$lcontent\" $anc_par/>";
                }
            }
        }
        $link_info .= "</IntLinkInfos>";
        return $link_info;
    }

    /**
     * update history
     */
    public function updateHistory() : void
    {
        ilHistory::_createEntry(
            $this->obj->getId(),
            "update",
            [],
            $this->content_object->getType() . ":pg",
            "",
            true
        );
    }

    /**
     * redirect script
     */
    public static function _goto(string $a_target) : void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ctrl = $DIC->ctrl();
        $ref_id = 0;
        $anchor = "";

        $first = strpos($a_target, "_");
        $second = strpos($a_target, "_", $first + 1);
        if ($first > 0) {
            $page_id = substr($a_target, 0, $first);
            if ($second > 0) {
                $ref_id = substr($a_target, $first + 1, $second - ($first + 1));
                $anchor = substr($a_target, $second + 1);
            } else {
                $ref_id = substr($a_target, $first + 1);
            }
        } else {
            $page_id = $a_target;
        }

        // determine learning object
        $lm_id = ilLMObject::_lookupContObjID($page_id);

        // get all references
        $ref_ids = ilObject::_getAllReferences($lm_id);

        // always try passed ref id first
        if (in_array($ref_id, $ref_ids)) {
            $ref_ids = array_merge(array($ref_id), $ref_ids);
        }

        // check read permissions
        foreach ($ref_ids as $ref_id) {
            // check read permissions
            if ($ilAccess->checkAccess("read", "", $ref_id)) {
                $ctrl->setParameterByClass("ilLMPresentationGUI", "obj_id", $page_id);
                $ctrl->setParameterByClass("ilLMPresentationGUI", "ref_id", $ref_id);
                $ctrl->setParameterByClass("ilLMPresentationGUI", "anchor", $anchor);
                $ctrl->redirectByClass("ilLMPresentationGUI", "");
            }
        }

        if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            if ($lm_id > 0) {
                $main_tpl->setOnScreenMessage('failure', sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle($lm_id)
                ), true);
            } else {
                $lng->loadLanguageModule("content");
                $main_tpl->setOnScreenMessage('failure', $lng->txt("page_does_not_exist"), true);
            }
            ilObjectGUI::_gotoRepositoryRoot();
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
    }

    /**
     * Edit layout of page
     */
    public function editLayout() : void
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        
        $page_gui = new ilLMPageGUI($this->obj->getId());
        $page_gui->setEditPreview(true);
        $page_gui->activateMetaDataEditor(
            $this->content_object,
            $this->obj->getType(),
            $this->obj->getId(),
            $this->obj,
            "MDUpdateListener"
        );
        $page_gui->setActivationListener($this, "activatePage");
        $page_gui->setTabHook($this, "addPageTabs");
        $lm_set = new ilSetting("lm");
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_pg.svg"));
        $tpl->setTitle($this->lng->txt("page") . ": " . $this->obj->getTitle());
        $ilCtrl->getHTML($page_gui);
        $ilTabs->setTabActive("cont_layout");
        $this->initEditLayoutForm();
        $tpl->setContent($this->form->getHTML());
    }
    
    public function initEditLayoutForm() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $im_tag = "";
    
        $this->form = new ilPropertyFormGUI();
    
        // default layout
        $layout = new ilRadioGroupInputGUI($lng->txt("cont_layout"), "layout");
        
        if (is_file($im = ilUtil::getImagePath("layout_" . $this->content_object->getLayout() . ".png"))) {
            $im_tag = ilUtil::img($im, $this->content_object->getLayout());
        }
        $layout->addOption(new ilRadioOption("<table><tr><td>" . $im_tag . "</td><td><b>" .
            $lng->txt("cont_lm_default_layout") .
            "</b>: " . $lng->txt("cont_layout_" . $this->content_object->getLayout()) .
            "</td></tr></table>", ""));
        
        foreach (ilObjContentObject::getAvailableLayouts() as $l) {
            $im_tag = "";
            if (is_file($im = ilUtil::getImagePath("layout_" . $l . ".png"))) {
                $im_tag = ilUtil::img($im, $l);
            }
            $layout->addOption(new ilRadioOption("<table><tr><td>" . $im_tag . "</td><td><b>" .
                $lng->txt("cont_layout_" . $l) . "</b>: " . $lng->txt("cont_layout_" . $l . "_desc") .
                "</td></tr></table>", $l));
        }
        
        $layout->setValue($this->obj->getLayout());
        $this->form->addItem($layout);

        $this->form->addCommandButton("saveLayout", $lng->txt("save"));
                    
        $this->form->setTitle($lng->txt("cont_page_layout"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }
    
    public function saveLayout() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $this->initEditLayoutForm();
        if ($this->form->checkInput()) {
            ilLMObject::writeLayout($this->obj->getId(), $this->form->getInput("layout"));
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editLayout");
        }
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }
    
    public function addPageTabs() : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        
        $ilTabs->addTarget(
            "cont_layout",
            $ilCtrl->getLinkTarget($this, 'editLayout'),
            "editLayout"
        );
    }

    /**
     * download file of file lists
     */
    public function downloadFile() : void
    {
        $pg_obj = $this->obj->getPageObject();
        $pg_obj->buildDom();
        $int_links = $pg_obj->getInternalLinks();
        foreach ($int_links as $il) {
            if ($il["Target"] == str_replace(
                "_file_",
                "_dfile_",
                $this->request->getFileId()
            )) {
                $file = explode("_", $this->request->getFileId());
                $file_id = (int) $file[count($file) - 1];
                $fileObj = new ilObjFile($file_id, false);
                $fileObj->sendFile();
                exit;
            }
        }
    }

    public function create() : void
    {
        $ui = $this->ui;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $tabs = $this->tabs;
        $tabs->setBackTarget($lng->txt("back"), $ctrl->getLinkTarget($this, "cancel"));

        $ctrl->setParameter($this, "new_type", "pg");
        $form = $this->initNewPageForm();

        $this->tpl->setContent($ui->renderer()->render($form) . self::getLayoutCssFix());
    }

    public function initNewPageForm() : Form\Standard
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $fields["title"] = $f->input()->field()->text($lng->txt("title"), "");

        $fields["description"] = $f->input()->field()->textarea($lng->txt("description"));

        $ts = ilPageLayoutGUI::getTemplateSelection(ilPageLayout::MODULE_LM);
        if (!is_null($ts)) {
            $fields["layout_id"] = $ts;
        }

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("cont_insert_pagelayout"));

        $form_action = $ctrl->getLinkTarget($this, "save");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    /**
     * Save page
     */
    public function save() : void
    {
        global $DIC;

        $request = $DIC->http()->request();
        $lng = $this->lng;

        $form = $this->initNewPageForm();
        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData()["sec"];

            $layout_id = (int) $data["layout_id"];

            $this->obj = new ilLMPageObject($this->content_object);
            $this->obj->setType("pg");
            $this->obj->setTitle(ilUtil::stripSlashes($data["title"]));
            $this->obj->setDescription(ilUtil::stripSlashes($data["description"]));
            $this->obj->setLMId($this->content_object->getId());
            if ($layout_id > 0) {
                $this->obj->create(false, false, $layout_id);
            } else {
                $this->obj->create();
            }
            $this->tpl->setOnScreenMessage('success', $lng->txt("cont_page_created"), true);
        }
        $this->cancel();
    }

    /**
     * Get layout css fix
     * (workaround for broken radio options)
     */
    public static function getLayoutCssFix() : string
    {
        return "
		<style>
		.form-control.il-input-radiooption > label {
			vertical-align: middle;
		}
		.form-control.il-input-radiooption > .help-block {
			padding-left: 2rem;
		}
		</style>
		";
    }
}

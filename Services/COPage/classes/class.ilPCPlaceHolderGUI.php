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

/**
 * User Interface for Place Holder Management
 *
 * @author Hendrik Holtmann <holtmann@me.com>
 * @ilCtrl_Calls ilPCPlaceHolderGUI: ilPCMediaObjectGUI
 */
class ilPCPlaceHolderGUI extends ilPageContentGUI
{
    public const TYPE_TEXT = "Text";
    public const TYPE_QUESTION = "Question";
    public const TYPE_MEDIA = "Media";
    public const TYPE_VERIFICATION = "Verification";
    protected ilPropertyFormGUI $form_gui;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->pg_obj = $a_pg_obj;
        $this->content_obj = $a_content_obj;
        $this->hier_id = $a_hier_id;
        $this->pc_id = $a_pc_id;

        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand(): void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);
        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case 'ilpcmediaobjectgui':  //special handling
                $media_gui = new ilPCMediaObjectGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $ret = $this->ctrl->forwardCommand($media_gui);
                break;

            default:
                $this->$cmd();
                break;
        }
    }

    protected function insert(): void
    {
        $this->propertyGUI("create", self::TYPE_TEXT, "100px", "insert");
    }

    protected function create(): void
    {
        $plach_height = $this->request->getString("plach_height");
        if ($plach_height == "" ||
            !preg_match("/[0-9]+/", $plach_height)) {
            $this->insert();
            return;
        }

        $this->content_obj = new ilPCPlaceHolder($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->content_obj->setHeight($plach_height . "px");
        $this->content_obj->setContentClass(
            $this->request->getString("plach_type")
        );
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }

    public function edit(): void
    {
        if ($this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $this->edit_object();
        } else {
            $this->forward_edit();
        }
    }

    public function setStyleId(int $a_styleid): void
    {
        $this->styleid = $a_styleid;
    }

    public function getStyleId(): int
    {
        return $this->styleid;
    }

    protected function edit_object(): void
    {
        $this->propertyGUI(
            "saveProperties",
            $this->content_obj->getContentClass(),
            $this->content_obj->getHeight(),
            "save"
        );
    }

    protected function forward_edit(): void
    {
        switch ($this->content_obj->getContentClass()) {
            case self::TYPE_MEDIA:
                $this->ctrl->setCmdClass("ilpcmediaobjectgui");
                $this->ctrl->setCmd("insert");
                $media_gui = new ilPCMediaObjectGUI($this->pg_obj, null, "");
                $this->ctrl->forwardCommand($media_gui);
                break;

            case self::TYPE_TEXT:
                $this->textCOSelectionGUI();
                break;

            case self::TYPE_QUESTION:
                $this->ctrl->setCmdClass("ilpcquestiongui");
                $this->ctrl->setCmd("insert");
                $question_gui = new ilPCQuestionGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $question_gui->setSelfAssessmentMode(true);
                $this->ctrl->forwardCommand($question_gui);
                break;

            case self::TYPE_VERIFICATION:
                $this->ctrl->setCmdClass("ilpcverificationgui");
                $this->ctrl->setCmd("insert");
                /** @var ilPCVerification $ver */
                $ver = $this->content_obj;
                $cert_gui = new ilPCVerificationGUI($this->pg_obj, $ver, $this->hier_id, $this->pc_id);
                $this->ctrl->forwardCommand($cert_gui);
                break;

            default:
                break;
        }
    }


    /**
     * save placeholder properties in db and return to page edit screen
     */
    protected function saveProperties(): void
    {
        $plach_height = $this->request->getString("plach_height");
        if ($plach_height == "" ||
            !preg_match("/[0-9]+/", $plach_height)) {
            $this->edit_object();
            return;
        }

        $this->content_obj->setContentClass($this->request->getString("plach_type"));
        $this->content_obj->setHeight($plach_height . "px");

        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }

    /**
     * Property Form
     */
    protected function propertyGUI(
        string $a_action,
        string $a_type,
        string $a_height,
        string $a_mode
    ): void {
        $lng = $this->lng;

        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this));
        $this->form_gui->setTitle($lng->txt("cont_ed_plachprop"));

        $ttype_input = new ilRadioGroupInputGUI($lng->txt("type"), "plach_type");
        $type_captions = $this->getTypeCaptions();
        foreach ($this->getAvailableTypes($a_type) as $type) {
            $ttype_input->addOption(new ilRadioOption($type_captions[$type], $type));
        }
        $ttype_input->setRequired(true);
        $this->form_gui->addItem($ttype_input);

        $theight_input = new ilTextInputGUI($lng->txt("height"), "plach_height");
        $theight_input->setSize(4);
        $theight_input->setMaxLength(3);
        $theight_input->setTitle($lng->txt("height") . " (px)");
        $theight_input->setRequired(true);
        $this->form_gui->addItem($theight_input);

        $theight_input->setValue(preg_replace("/px/", "", $a_height));
        $ttype_input->setValue($a_type);

        $this->form_gui->addCommandButton($a_action, $lng->txt($a_mode));
        $this->form_gui->addCommandButton("cancelCreate", $lng->txt("cancel"));
        $this->tpl->setContent($this->form_gui->getHTML());
    }

    /**
     * Text Item Selection
     */
    protected function textCOSelectionGUI(): void
    {
        $lng = $this->lng;

        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this));
        $this->form_gui->setTitle($lng->txt("cont_ed_select_pctext"));

        // Select Question Type
        $ttype_input = new ilRadioGroupInputGUI($lng->txt("cont_ed_textitem"), "pctext_type");
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_par"), 0));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_dtable"), 1));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_atable"), 2));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_list"), 3));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_flist"), 4));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_tabs"), 5));
        $this->form_gui->addItem($ttype_input);

        $this->form_gui->addCommandButton("insertPCText", $lng->txt("insert"));
        $this->form_gui->addCommandButton("cancelCreate", $lng->txt("cancel"));
        $this->tpl->setContent($this->form_gui->getHTML());
    }

    /**
     * Forwards Text Item Selection to GUI classes
     */
    protected function insertPCText(): void
    {
        switch ($this->request->getString("pctext_type")) {
            case 0:  //Paragraph / Text

                $ret_class = strtolower(get_class($this->getPage()) . "gui");
                $this->ctrl->setParameterByClass($ret_class, "pl_hier_id", $this->hier_id);
                $this->ctrl->setParameterByClass($ret_class, "pl_pc_id", $this->pc_id);
                $this->ctrl->redirectByClass(
                    $ret_class,
                    "insertJSAtPlaceholder"
                );

                $this->ctrl->setCmdClass("ilpcparagraphgui");
                $this->ctrl->setCmd("insert");
                $paragraph_gui = new ilPCParagraphGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $paragraph_gui->setStyleId($this->getStyleId());
                $paragraph_gui->setPageConfig($this->getPageConfig());
                $this->ctrl->forwardCommand($paragraph_gui);
                break;

            case 1:  //DataTable
                $this->ctrl->setCmdClass("ilpcdatatablegui");
                $this->ctrl->setCmd("insert");
                $dtable_gui = new ilPCDataTableGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $this->ctrl->forwardCommand($dtable_gui);
                break;

            case 2:  //Advanced Table
                $this->ctrl->setCmdClass("ilpctablegui");
                $this->ctrl->setCmd("insert");
                $atable_gui = new ilPCTableGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $this->ctrl->forwardCommand($atable_gui);
                break;

            case 3:  //Advanced List
                $this->ctrl->setCmdClass("ilpclistgui");
                $this->ctrl->setCmd("insert");
                $list_gui = new ilPCListGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $this->ctrl->forwardCommand($list_gui);
                break;

            case 4:  //File List
                $this->ctrl->setCmdClass("ilpcfilelistgui");
                $this->ctrl->setCmd("insert");
                $file_list_gui = new ilPCFileListGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $file_list_gui->setStyleId($this->getStyleId());
                $this->ctrl->forwardCommand($file_list_gui);
                break;

            case 5:  //Tabs
                $this->ctrl->setCmdClass("ilpctabsgui");
                $this->ctrl->setCmd("insert");
                $tabs_gui = new ilPCTabsGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $tabs_gui->setStyleId($this->getStyleId());
                $this->ctrl->forwardCommand($tabs_gui);
                break;

            default:
                break;
        }
    }

    public function cancel(): void
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    protected function getAvailableTypes(
        string $a_selected_type = ""
    ): array {
        // custom config?
        if (method_exists($this->getPageConfig(), "getAvailablePlaceholderTypes")) {
            $types = $this->getPageConfig()->getAvailablePlaceholderTypes();
        } else {
            $types = array(self::TYPE_TEXT, self::TYPE_MEDIA, self::TYPE_QUESTION);
        }

        $validator = new ilCertificateActiveValidator();
        if (true === $validator->validate()) {
            // we remove type verification if certificates are deactivated and this
            // is not the currently selected value
            if (($key = array_search(self::TYPE_VERIFICATION, $types)) !== false &&
                self::TYPE_VERIFICATION != $a_selected_type) {
                unset($types[$key]);
            }
        }
        return $types;
    }

    protected function getTypeCaptions(): array
    {
        $lng = $this->lng;

        return array(
                self::TYPE_TEXT => $lng->txt("cont_ed_plachtext"),
                self::TYPE_MEDIA => $lng->txt("cont_ed_plachmedia"),
                self::TYPE_QUESTION => $lng->txt("cont_ed_plachquestion"),
                self::TYPE_VERIFICATION => $lng->txt("cont_ed_plachverification")
            );
    }
}

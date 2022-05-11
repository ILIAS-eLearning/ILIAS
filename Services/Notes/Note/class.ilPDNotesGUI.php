<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\Notes\NotesManager;
use ILIAS\Notes\StandardGUIRequest;
use ILIAS\Notes\Note;

/**
 * Private Notes on PD
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPDNotesGUI: ilNoteGUI
 */
class ilPDNotesGUI
{
    protected int $note_type;
    protected string $search_text = "";
    protected ?\ILIAS\Notes\FilterAdapterGUI $filter = null;
    protected \ILIAS\Notes\InternalGUIService $gui;
    protected \ILIAS\DI\UIServices $ui;
    protected NotesManager $notes_manager;

    protected ?int $current_rel_obj = null;
    protected StandardGUIRequest $request;

    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected ilSetting $settings;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    public ilGlobalTemplateInterface $tpl;
    public ilLanguage $lng;
    protected ?array $related_objects = null;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->help = $DIC["ilHelp"];
        $this->settings = $DIC->settings();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        $ilHelp = $DIC["ilHelp"];
        $this->ui = $DIC->ui();

        $this->request = $DIC->notes()
            ->internal()
            ->gui()
            ->standardRequest();

        $ilHelp->setScreenIdComponent("note");

        $lng->loadLanguageModule("notes");
        
        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $this->notes_manager = $DIC->notes()->internal()->domain()->notes();
        $this->gui = $DIC->notes()->internal()->gui();

        // link from ilPDNotesBlockGUI
        $rel_obj = $this->request->getRelatedObjId();
        $this->note_type = ($this->request->getNoteType() === Note::PRIVATE || $ilCtrl->getCmd() === "getNotesHTML")
            ? Note::PRIVATE
            : Note::PUBLIC;
        $this->ctrl->setParameter($this, "note_type", $this->note_type);
        if ($rel_obj > 0) {
            $ilUser->writePref("pd_notes_rel_obj" . $this->note_type, (string) $rel_obj);
        }
        // edit link
        elseif ($this->request->getNoteId() > 0) {
            $note = $this->notes_manager->getById($this->request->getNoteId());
            $context = $note->getContext();
            $ilUser->writePref("pd_notes_rel_obj" . $this->note_type, $context->getObjId());
        }
        $this->readFilter();
        $ajax_url = $this->ctrl->getLinkTargetByClass(
            ["ildashboardgui", "ilpdnotesgui", "ilnotegui"],
            "",
            "",
            true,
            false
        );
        $this->gui->initJavascript($ajax_url);
    }

    protected function readFilter() : void
    {
        $data = $this->getFilter()->getData();
        if (!isset($data["object"]) || $data["object"] === "") {
            $this->current_rel_obj = null;
        } else {
            $this->current_rel_obj = (int) $data["object"];
        }
        $this->search_text = $data["text"] ?? "";
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case "ilnotegui":
                $this->displayHeader();
                $this->view();		// forwardCommand is invoked in view() method
                break;
                
            default:
                $cmd = $this->ctrl->getCmd("view");
                $this->displayHeader();
                $this->$cmd();
                break;
        }
        $this->tpl->printToStdout(true);
    }

    public function displayHeader() : void
    {
        $ilSetting = $this->settings;

        $t = $this->lng->txt("notes");
        if (!$ilSetting->get("disable_notes") && !$ilSetting->get("disable_comments")) {
            $t = $this->lng->txt("notes_and_comments");
        }
        if ($ilSetting->get("disable_notes")) {
            $t = $this->lng->txt("notes_comments");
        }

        if ($this->note_type === Note::PRIVATE) {
            $t = $this->lng->txt("private_notes");
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_nots.svg"));
        } else {
            $t = $this->lng->txt("notes_public_comments");
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_coms.svg"));
        }

        $this->tpl->setTitle($t);
    }

    protected function getRelatedObjects() : array
    {
        if (is_null($this->related_objects)) {
            $this->related_objects = $this->notes_manager->getRelatedObjectsOfUser(
                $this->note_type
            );
        }
        return $this->related_objects;
    }

    public function view() : void
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilAccess = $this->access;
        $ilToolbar = $this->toolbar;

        $rel_objs = $this->getRelatedObjects();
        $this->setToolbar();

        if ($this->note_type === Note::PRIVATE) {
            $rel_objs = array_merge(
                [0],
                $rel_objs
            );
        }

        // #9410
        if (count($rel_objs) === 0 && $this->note_type === Note::PUBLIC) {
            $lng->loadLanguageModule("notes");
            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_no_search_result"));
            return;
        }
        if ($this->current_rel_obj === null) {
            $notes_gui = new ilNoteGUI(
                $rel_objs,
                0,
                "",
                true,
                0,
                false,
                $this->search_text
            );
        } elseif ($this->current_rel_obj > 0) {
            $notes_gui = new ilNoteGUI(
                $this->current_rel_obj,
                0,
                \ilObject::_lookupType($this->current_rel_obj),
                true,
                0,
                false,
                $this->search_text
            );
        } else {
            $notes_gui = new ilNoteGUI(
                0,
                $ilUser->getId(),
                "pd",
                false,
                0,
                false,
                $this->search_text
            );
        }
        //$notes_gui->setHideNewForm(true);
        
        if ($this->note_type === Note::PRIVATE) {
            $notes_gui->enablePrivateNotes(true);
            $notes_gui->enablePublicNotes(false);
        } else {
            $notes_gui->enablePrivateNotes(false);
            $notes_gui->enablePublicNotes(true);
            // #13707
            if ($this->current_rel_obj > 0 &&
                $ilSetting->get("comments_del_tutor", '1')) {
                foreach (\ilObject::_getAllReferences($this->current_rel_obj) as $ref_id) {
                    if ($ilAccess->checkAccess("write", "", $ref_id)) {
                        $notes_gui->enablePublicNotesDeletion(true);
                        break;
                    }
                }
            }
        }
        $notes_gui->enableTargets(true);

        $next_class = $this->ctrl->getNextClass($this);

        if ($next_class === "ilnotegui") {
            $html = $this->ctrl->forwardCommand($notes_gui);
        } elseif ($this->note_type === Note::PRIVATE) {
            $html = $notes_gui->getNotesHTML();
        } else {
            $html = $notes_gui->getCommentsHTML();
        }

        $filter_html = $this->getFilter()->render();

        $this->tpl->setContent($filter_html . $html);
    }
    
    public function changeRelatedObject() : void
    {
        $ilUser = $this->user;

        $ilUser->writePref(
            "pd_notes_rel_obj" . $this->note_type,
            (string) $this->request->getRelatedObjId()
        );
        $this->ctrl->redirect($this);
    }

    public function showPrivateNotes() : void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->redirectByClass(ilNoteGUI::class, "getNotesHTML");
    }
    
    public function showPublicComments() : void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        
        if ($ilSetting->get("disable_comments")) {
            $ilCtrl->redirect($this, "showPrivateNotes");
        }
        
        $ilCtrl->redirectByClass(ilNoteGUI::class, "getCommentsHTML");
    }

    protected function setSortation() : void
    {
        $this->notes_manager->setSortAscending($this->gui->standardRequest()->getSortation() === "asc");
        $this->view();
    }

    protected function getFilter() : \ILIAS\Notes\FilterAdapterGUI
    {
        $gui = $this->gui;
        $lng = $this->lng;
        if (is_null($this->filter)) {
            $options = [];

            if ($this->note_type === Note::PRIVATE) {
                $options[-1] = $this->lng->txt("note_without_object");
            }

            foreach ($this->getRelatedObjects() as $k) {
                $options[$k] = ilObject::_lookupTitle($k);
            }
            $this->filter = $gui->filter(
                "notes_filter_" . $this->note_type,
                self::class,
                "view",
                false,
                false
            )
                ->text("text", $lng->txt("notes_text"))
                ->select("object", $lng->txt("notes_origin"), $options);
        }
        return $this->filter;
    }

    protected function setToolbar() : void
    {
        $ctrl = $this->ctrl;

        // sortation
        $c = $this->lng->txt("create_date") . ", ";
        $options = [
            'desc' => $c . $this->lng->txt("sorting_desc"),
            'asc' => $c . $this->lng->txt("sorting_asc")
        ];
        $select_option = ($this->notes_manager->getSortAscending())
            ? 'asc'
            : 'desc';
        $s = $this->ui->factory()->viewControl()->sortation($options)
               ->withTargetURL($ctrl->getLinkTarget($this, "setSortation"), 'sortation')
               ->withLabel($options[$select_option]);
        $this->toolbar->addComponent($s);

        // print selection
        $pv = $this->gui->print();
        $modal_elements = $pv->getModalElements(
            $ctrl->getLinkTarget($this, "printSelection")
        );
        $this->toolbar->addComponent($modal_elements->button);
        $this->toolbar->addComponent($modal_elements->modal);

        // export html
        $b = $this->ui->factory()->button()->standard(
            $this->lng->txt("notes_html_export"),
            $ctrl->getLinkTargetByClass("ilNoteGUI", "exportNotesHTML")
        );
        $this->toolbar->addComponent($b);
    }

    /**
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    public function printSelection() : void
    {
        $pv = $this->gui->print();
        $pv->sendForm();
    }
}

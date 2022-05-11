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
 * Notes GUI class. An instance of this class handles all notes
 * (and their lists) of an object.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNoteGUI
{
    /**
     * @var Note[]
     */
    protected ?array $notes = null;
    protected \ILIAS\Notes\InternalGUIService $gui;
    protected string $search_text;
    protected \ILIAS\Notes\AccessManager $notes_access;
    protected \ILIAS\Notes\InternalDataService $data;
    protected ilWorkspaceAccessHandler $wsp_access_handler;
    protected ilWorkspaceTree $wsp_tree;
    protected bool $public_enabled;
    protected StandardGUIRequest $request;
    protected NotesManager $manager;
    protected bool $targets_enabled = false;
    protected bool $export_html = false;
    protected bool $print = false;
    protected bool $comments_settings = false;
    protected string $obj_type;
    protected bool $private_enabled;
    protected bool $edit_note_form;
    protected bool $add_note_form;
    protected bool $ajax;
    protected bool $inc_sub;
    protected int $obj_id;
    /**
     * @var int|int[]
     */
    protected $rep_obj_id;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilSetting $settings;
    protected ilObjectDefinition $obj_definition;
    protected ilTree $tree;
    protected ilAccessHandler $access;
    public bool $public_deletion_enabled = false;
    public bool $repository_mode = false;
    public bool $old = false;
    protected string $default_command = "getListHTML";
    protected array $observer = [];
    protected \ILIAS\DI\UIServices $ui;
    protected int $news_id = 0;
    protected bool $hide_new_form = false;
    protected bool $only_latest = false; // Show only latest note/comment
    protected string $widget_header = "";
    protected bool $no_actions = false; //  Do not show edit/delete actions
    protected bool $enable_sorting = true;
    protected bool $user_img_export_html = false;
    protected ilLogger $log;
    protected ilTemplate $form_tpl;
    protected int $requested_note_type = 0;
    protected int $requested_note_id = 0;
    protected string $requested_note_mess = "";
    protected int $requested_news_id = 0;
    protected bool $delete_note = false;
    protected string $note_mess = "";
    protected array $item_list_gui = [];
    protected bool $use_obj_title_header = true;

    /**
     * @param int|int[]    $a_rep_obj_id object id of repository object (0 for personal desktop)
     * @param int    $a_obj_id sub-object id (0 for repository items, user id for personal desktop)
     * @param string $a_obj_type "pd" for personal desktop
     * @param bool   $a_include_subobjects include all subobjects of rep object (e.g. pages)
     * @param int    $a_news_id
     * @param bool   $ajax
     * @throws ilCtrlException
     */
    public function __construct(
        $a_rep_obj_id = 0,
        int $a_obj_id = 0,
        string $a_obj_type = "",
        bool $a_include_subobjects = false,
        int $a_news_id = 0,
        bool $ajax = true,
        string $search_text = ""
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->ui = $DIC->ui();
        $ilCtrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->log = ilLoggerFactory::getLogger('note');
        $this->search_text = $search_text;

        $ns = $DIC->notes()->internal();
        $this->manager = $ns
            ->domain()
            ->notes();
        $this->request = $ns
            ->gui()
            ->standardRequest();
        $this->data = $ns->data();
        $this->gui = $ns->gui();
        $this->notes_access = $ns->domain()->noteAccess();

        $this->lng->loadLanguageModule("notes");
        
        $ilCtrl->saveParameter($this, "notes_only");

        $this->rep_obj_id = $a_rep_obj_id;
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_obj_type;
        $this->inc_sub = $a_include_subobjects;
        $this->news_id = $a_news_id;
        
        // auto-detect object type
        if (!is_array($a_rep_obj_id) && !$this->obj_type && $a_rep_obj_id) {
            $this->obj_type = ilObject::_lookupType($a_rep_obj_id);
        }

        $this->ajax = $ajax;

        $this->ctrl = $ilCtrl;

        $this->add_note_form = false;
        $this->edit_note_form = false;
        $this->private_enabled = false;

        if (!is_array($this->rep_obj_id)) {
            if ($this->manager->commentsActive($this->rep_obj_id)) {
                $this->public_enabled = true;
            } else {
                $this->public_enabled = false;
            }
        }
        $this->targets_enabled = false;
        $this->export_html = false;
        $this->print = false;
        $this->comments_settings = false;

        // default: notes for repository objects
        $this->setRepositoryMode(true);

        $this->ctrl->saveParameter($this, "note_type");
        $this->requested_note_type = $this->request->getNoteType();
        $this->requested_note_id = $this->request->getNoteId();
        $this->requested_note_mess = $this->request->getNoteMess();
        $this->requested_news_id = $this->request->getNewsId();
    }

    public function setUseObjectTitleHeader(bool $a_val) : void
    {
        $this->use_obj_title_header = $a_val;
    }

    public function getUseObjectTitleHeader() : bool
    {
        return $this->use_obj_title_header;
    }

    public function setDefaultCommand(string $a_val) : void
    {
        $this->default_command = $a_val;
    }
    
    public function setHideNewForm(bool $a_val) : void
    {
        $this->hide_new_form = $a_val;
    }

    public function getDefaultCommand() : string
    {
        return $this->default_command;
    }
    
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd($this->getDefaultCommand());
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }
    
    public function enablePrivateNotes(bool $a_enable = true) : void
    {
        $this->private_enabled = $a_enable;
    }
    
    public function enablePublicNotes(bool $a_enable = true) : void
    {
        $this->public_enabled = $a_enable;
    }

    public function enableCommentsSettings(bool $a_enable = true) : void
    {
        $this->comments_settings = $a_enable;
    }
    
    public function enablePublicNotesDeletion(bool $a_enable = true) : void
    {
        $this->public_deletion_enabled = $a_enable;
    }

    // enable target objects
    public function enableTargets(bool $a_enable = true) : void
    {
        $this->targets_enabled = $a_enable;
    }

    public function setRepositoryMode(bool $a_value) : void
    {
        $this->repository_mode = $a_value;
    }

    public function getNotesHTML() : string
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter($this, "notes_type", Note::PRIVATE);
        $this->requested_note_type = Note::PRIVATE;
        return $this->getListHTML($a_init_form = true);
    }
    
    public function getCommentsHTML() : string
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter($this, "notes_type", Note::PUBLIC);
        $this->requested_note_type = Note::PUBLIC;
        return $this->getListHTML($a_init_form = true);
    }
    
    public function getListHTML(bool $a_init_form = true) : string
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;

        if ($this->requested_note_type === Note::PRIVATE && $ilUser->getId() !== ANONYMOUS_USER_ID) {
            $content = $this->getNoteListHTML(Note::PRIVATE, $a_init_form);
        }
        
        // #15948 - public enabled vs. comments_settings
        if ($this->requested_note_type === Note::PUBLIC) {
            $active = true;
            if (!is_array($this->rep_obj_id)) {
                $active = $this->manager->commentsActive($this->rep_obj_id);
            }

            if ($active) {
                $content = $this->getNoteListHTML(Note::PUBLIC, $a_init_form);
            }

            // Comments Settings
            if (!is_array($this->rep_obj_id) && $this->comments_settings && $ilUser->getId() !== ANONYMOUS_USER_ID) {
                $active = $this->manager->commentsActive($this->rep_obj_id);
                if ($active) {
                    if ($this->news_id === 0) {
                        $content .= $this->renderComponents([$this->getShyButton(
                            "comments_settings",
                            $lng->txt("notes_deactivate_comments"),
                            "deactivateComments",
                            ""
                        )
                        ]);
                    }
                } else {
                    $content .= $this->renderComponents([$this->getShyButton(
                        "comments_settings",
                        $lng->txt("notes_activate_comments"),
                        "activateComments",
                        ""
                    )
                    ]);
                    /*
                    if ($this->ajax && !$comments_col) {
                        $ntpl->setVariable(
                            "COMMENTS_MESS",
                            ilUtil::getSystemMessageHTML($lng->txt("comments_feature_currently_not_activated_for_object"), "info")
                        );
                    }*/
                }
            }
        }

        return $this->renderContent($content);
    }

    protected function renderComponents(array $components) : string
    {
        if ($this->ctrl->isAsynch()) {
            return $this->ui->renderer()->renderAsync($components);
        }
        return $this->ui->renderer()->render($components);
    }

    public function activateComments() : void
    {
        $ilCtrl = $this->ctrl;
        
        if ($this->comments_settings) {
            $this->manager->activateComments($this->rep_obj_id, true);
        }
        
        $ilCtrl->redirectByClass("ilnotegui", "getCommentsHTML", "", $this->ajax);
    }

    public function deactivateComments() : void
    {
        $ilCtrl = $this->ctrl;
        
        if ($this->comments_settings) {
            $this->manager->activateComments($this->rep_obj_id, false);
        }

        $ilCtrl->redirectByClass("ilnotegui", "getCommentsHTML", "", $this->ajax);
    }

    /**
     * @return Note[]
     */
    protected function getNotes(int $a_type) : array
    {
        if ($this->notes === null) {
            $ilUser = $this->user;
            $filter = null;
            if ($this->export_html || $this->print) {
                if ($this->requested_note_id > 0) {
                    $filter = $this->requested_note_id;
                } else {
                    $filter = $this->request->getNoteText();
                }
            }

            $ascending = $this->manager->getSortAscending();
            if ($this->only_latest) {
                $order = false;
            }
            $author_id = ($a_type === Note::PRIVATE)
                ? $ilUser->getId()
                : 0;

            if (!is_array($this->rep_obj_id)) {
                $notes = $this->manager->getNotesForContext(
                    $this->data->context(
                        $this->rep_obj_id,
                        $this->obj_id,
                        $this->obj_type,
                        $this->news_id,
                        $this->repository_mode
                    ),
                    $a_type,
                    $this->inc_sub,
                    $author_id,
                    $ascending,
                    "",
                    $this->search_text
                );
            } else {
                $notes = $this->manager->getNotesForRepositoryObjIds(
                    $this->rep_obj_id,
                    $a_type,
                    $this->inc_sub,
                    $author_id,
                    $ascending,
                    "",
                    $this->search_text
                );
            }
            $this->notes = $notes;
        }
        return $this->notes;
    }

    public function getNoteListHTML(
        int $a_type = Note::PRIVATE,
        bool $a_init_form = true
    ) : string {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $f = $this->ui->factory();
        $ilUser = $this->user;

        $mtype = "";

        $suffix = ($a_type === Note::PRIVATE)
            ? "private"
            : "public";

        $notes = $this->getNotes($a_type);

        $tpl = new ilTemplate("tpl.notes_list.html", true, true, "Services/Notes");

        // show counter if notes are hidden
        $cnt_str = (count($notes) > 0)
            ? " (" . count($notes) . ")"
            : "";

        // origin header
        $origin_header = $this->getOriginHeader();
        if ($origin_header != "") {
            $tpl->setCurrentBlock("title");
            $tpl->setVariable("TITLE", $origin_header);
            $tpl->parseCurrentBlock();
        }

        if ($a_type === Note::PRIVATE) {
            $tpl->setVariable("TXT_NOTES", $lng->txt("private_notes") . $cnt_str);
            $ilCtrl->setParameterByClass("ilnotegui", "note_type", Note::PRIVATE);
        } else {
            $tpl->setVariable("TXT_NOTES", $lng->txt("notes_public_comments") . $cnt_str);
            $ilCtrl->setParameterByClass("ilnotegui", "note_type", Note::PUBLIC);
        }
        $anch = "";

        // show add new note text area
        if (!$this->edit_note_form && !is_array($this->rep_obj_id) &&
            !$this->hide_new_form && $ilUser->getId() !== ANONYMOUS_USER_ID) {
            $tpl->setCurrentBlock("edit_note_form");
            $b_caption = ($this->requested_note_type === Note::PRIVATE)
                ? $this->lng->txt("note_add_note")
                : $this->lng->txt("note_add_comment");
            $b = $this->ui->factory()->button()->standard(
                $b_caption,
                "#"
            );
            $tpl->setVariable("EDIT_STYLE", "display:none;");
            $tpl->setVariable(
                "EDIT_FORM_ACTION",
                $ilCtrl->getFormActionByClass("ilnotegui", "addNote", "", true)
            );
            $tpl->setVariable(
                "TXT_CANCEL",
                $this->lng->txt("cancel")
            );
            $tpl->setVariable(
                "EDIT_FORM_BUTTON",
                $this->renderComponents([$b])
            );
            $tpl->setVariable(
                "EDIT_FORM",
                $this->getNoteForm("create", $a_type)->render()
            );
            $tpl->parseCurrentBlock();
        }
        
        // list all notes
        $reldates = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $notes_given = false;

        // edit form
        if ($this->edit_note_form && $a_type === $this->requested_note_type) {
            $note = $this->manager->getById($this->requested_note_id);
            $ilCtrl->setParameterByClass("ilnotegui", "note_id", $this->requested_note_id);
            $tpl->setVariable(
                "EDIT_FORM_ACTION",
                $ilCtrl->getFormActionByClass("ilnotegui", "updateNote", "", true)
            );
            $tpl->setVariable(
                "CANCEL_FORM_ACTION",
                $ilCtrl->getFormActionByClass("ilnotegui", "cancelUpdateNote", "", true)
            );
            $tpl->setVariable(
                "TXT_CANCEL",
                $this->lng->txt("cancel")
            );
            $tpl->setVariable(
                "EDIT_FORM",
                $this->getNoteForm("edit", $a_type, $note)->render()
            );
            $tpl->parseCurrentBlock();
        }

        $items = [];
        $item_groups = [];
        $text_placeholders = [];
        $texts = [];
        $last_obj_id = null;
        foreach ($notes as $note) {
            if ($this->only_latest && $notes_given) {
                continue;
            }

            $current_obj_id = $note->getContext()->getObjId();
            if ($last_obj_id !== null && $current_obj_id !== $last_obj_id) {
                $it_group_title = $this->getItemGroupTitle($last_obj_id);
                $item_groups[] = $f->item()->group($it_group_title, $items);
                $items = [];
            }
            $last_obj_id = $current_obj_id;

            $items[] = $this->getItemForNote($note);
            $notes_given = true;

            $text_placeholders[] = $this->getNoteTextPlaceholder($note);
            $texts[] = $this->getNoteText($note);
        }

        $it_group_title = $this->getItemGroupTitle((int) $last_obj_id);
        $item_groups[] = $f->item()->group($it_group_title, $items);

        if ($notes_given) {
            $panel = $f->panel()->listing()->standard("", $item_groups);
            $html = $this->renderComponents([$panel]);
            $html = str_replace($text_placeholders, $texts, $html);
            $tpl->setVariable("NOTES_LIST", $html);
        } elseif (!is_array($this->rep_obj_id)) {
            $it_group_title = $this->getItemGroupTitle($this->rep_obj_id);
            $item_groups = [$f->item()->group($it_group_title, [])];
            $panel = $f->panel()->listing()->standard("", $item_groups);
            if ($this->search_text === "") {
                $mess_txt = ($this->requested_note_type === Note::PRIVATE)
                    ? $lng->txt("notes_no_notes")
                    : $lng->txt("notes_no_comments");
            } else {
                $mess_txt = ($this->requested_note_type === Note::PRIVATE)
                    ? $lng->txt("notes_no_notes_found")
                    : $lng->txt("notes_no_comments_found");
            }
            $mess = $f->messageBox()->info($mess_txt);
            $html = $this->renderComponents([$panel, $mess]);
            $tpl->setVariable("NOTES_LIST", $html);
        } elseif ($this->search_text !== "") {
            $mess_txt = ($this->requested_note_type === Note::PRIVATE)
                ? $lng->txt("notes_no_notes_found")
                : $lng->txt("notes_no_comments_found");
            $mess = $f->messageBox()->info($mess_txt);
            $tpl->setVariable("NOTES_LIST", $this->renderComponents([$mess]));
        }

        ilDatePresentation::setUseRelativeDates($reldates);

        // message
        $mtxt = "";
        switch ($this->requested_note_mess !== "" ? $this->requested_note_mess : $this->note_mess) {
            case "mod":
                $mtype = "success";
                $mtxt = $lng->txt("msg_obj_modified");
                break;
                
            case "ntsdel":
                $mtype = "success";
                $mtxt = ($a_type === Note::PRIVATE)
                    ? $lng->txt("notes_notes_deleted")
                    : $lng->txt("notes_comments_deleted");
                break;

            case "ntdel":
                $mtype = "success";
                $mtxt = ($a_type === Note::PRIVATE)
                    ? $lng->txt("notes_note_deleted")
                    : $lng->txt("notes_comment_deleted");
                break;
                
            case "frmfld":
                $mtype = "failure";
                $mtxt = $lng->txt("form_input_not_valid");
                break;

            case "qdel":
                $mtype = "question";
                $mtxt = $lng->txt("info_delete_sure");
                break;
                
            case "noc":
                $mtype = "failure";
                $mtxt = $lng->txt("no_checkbox");
                break;
        }
        if ($mtxt !== "") {
            $tpl->setVariable("MESS", ilUtil::getSystemMessageHTML($mtxt, $mtype));
        } else {
            $tpl->setVariable("MESS", "");
        }

        if ($this->widget_header !== "") {
            $tpl->setVariable("WIDGET_HEADER", $this->widget_header);
        }

        return $tpl->get();
    }

    protected function getItemGroupTitle(int $obj_id = 0) : string
    {
        if (!is_array($this->rep_obj_id) && !$this->getUseObjectTitleHeader()) {
            $it_group_title = ($this->requested_note_type === Note::PRIVATE)
                ? $this->lng->txt("notes")
                : $this->lng->txt("notes_comments");
        } else {
            $it_group_title = ($obj_id)
                ? ilObject::_lookupTitle($obj_id)
                : $this->lng->txt("note_without_object");
        }
        return $it_group_title;
    }

    /**
     * @throws ilCtrlException
     * @throws ilDateTimeException
     * @throws ilWACException
     */
    protected function getItemForNote(
        Note $note,
        bool $actions = true
    ) : \ILIAS\UI\Component\Item\Item {
        $f = $this->ui->factory();
        $ctrl = $this->ctrl;


        $dd_buttons = [];

        // edit note stuff for all private notes
        if ($actions && $this->notes_access->canEdit($note)) {
            if (!$this->export_html && !$this->print
                && !$this->edit_note_form && !$this->add_note_form && !$this->no_actions) {
                $ctrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
                $dd_buttons[] = $this->getShyButton(
                    "edit_note",
                    $this->lng->txt("edit"),
                    "editNoteForm",
                    "note_" . $note->getId(),
                    $note->getId()
                );
            }
        }

        // delete note stuff for all private notes
        if ($actions && !$this->export_html && !$this->print
            && !$this->no_actions
            && $this->notes_access->canDelete($note)) {
            $ctrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
            $dd_buttons[] = $this->getShyButton(
                "delete_note",
                $this->lng->txt("delete"),
                "deleteNote",
                "note_" . $note->getId(),
                $note->getId()
            );
        }


        $creation_date = ilDatePresentation::formatDate(new ilDate($note->getCreationDate(), IL_CAL_DATETIME));

        $properties = [];

        // origin
        if ($this->targets_enabled) {
            $target = $this->getTarget($note);
            if ($target["title"] !== "") {
                if ($target["link"] === "") {
                    $properties[$this->lng->txt("notes_origin")] = $target["title"];
                } else {
                    $properties[$this->lng->txt("notes_origin")] = $f
                        ->button()
                        ->shy(
                            $target["title"],
                            $target["link"]
                        );
                }
            }
        }

        // output author account and creation date
        $img_path = "";
        $img_alt = "";
        $avatar = null;
        if ($note->getType() === Note::PUBLIC) {
            $avatar = ilObjUser::_getAvatar($note->getAuthor());
            $title = ilUserUtil::getNamePresentation($note->getAuthor(), false, false);
            $properties[$this->lng->txt("create_date")] = $creation_date;
        } else {
            $title = $creation_date;
        }

        // last edited
        if ($note->getUpdateDate() !== null) {
            $properties[$this->lng->txt("last_edited_on")] = ilDatePresentation::formatDate(
                new ilDate(
                    $note->getUpdateDate(),
                    IL_CAL_DATETIME
                )
            );
        }

        $item = $f->item()->standard($title)
            ->withDescription($this->getNoteTextPlaceholder($note))
            ->withProperties($properties);
        if (!is_null($avatar)) {
            $item = $item->withLeadAvatar($avatar);
        }
        if (count($dd_buttons) > 0) {
            $item = $item->withActions(
                $f->dropdown()->standard($dd_buttons)
            );
        }
        return $item;
    }

    protected function getNoteTextPlaceholder(Note $note) : string
    {
        return "##note-text-" . $note->getId() . "##";
    }

    protected function getNoteText(Note $note) : string
    {
        return (trim($note->getText()) !== "")
            ? nl2br(htmlentities($note->getText()))
            : $this->lng->txt("note_content_removed");
    }

    /**
     * Get sub object title if available with callback
     */
    protected function getSubObjectTitle(
        int $parent_obj_id,
        int $sub_obj_id
    ) : string {
        $objDefinition = $this->obj_definition;
        $parent_type = ilObject::_lookupType($parent_obj_id);
        if ($parent_type === "") {
            return "";
        }
        $parent_class = "ilObj" . $objDefinition->getClassName($parent_type) . "GUI";
        if (method_exists($parent_class, "lookupSubObjectTitle")) {
            return call_user_func_array(array($parent_class, "lookupSubObjectTitle"), array($parent_obj_id, $sub_obj_id));
        }
        return "";
    }
    
    protected function getNoteForm(
        string $mode,
        int $type,
        Note $note = null
    ) : \ILIAS\Notes\FormAdapterGUI {
        global $DIC;

        $label = ($type === Note::PUBLIC)
            ? $this->lng->txt("comment")
            : $this->lng->txt("note");
        $cmd = ($mode === "create")
            ? "addNote"
            : "updateNote";

        $value = ($note)
            ? $note->getText()
            : "";
        if ($cmd === "updateNote") {
            $this->ctrl->setParameter($this, "note_id", $this->requested_note_id);
        }
        $action = $this->ctrl->getFormAction($this, $cmd, "");
        $form = $this->gui->form(self::class, $action)
            ->textarea("note", $label, "", $value);
        return $form;
    }

    /**
     * show related objects as links
     */
    public function getTarget(
        Note $note
    ) : array {
        $tree = $this->tree;
        $ilAccess = $this->access;
        $objDefinition = $this->obj_definition;
        $ilUser = $this->user;

        $title = "";
        $link = "";

        $a_note_id = $note->getId();
        $context = $note->getContext();
        $a_obj_type = $context->getType();
        $a_obj_id = $context->getSubObjId();

        if ($context->getObjId() > 0) {

            // get first visible reference
            $vis_ref_id = 0;
            $ref_ids = ilObject::_getAllReferences($context->getObjId());
            foreach ($ref_ids as $ref_id) {
                if ($vis_ref_id > 0) {
                    break;
                }
                if ($ilAccess->checkAccess("visible", "", $ref_id)) {
                    $vis_ref_id = $ref_id;
                }
            }

            // if we got the reference id
            if ($vis_ref_id > 0) {
                $type = ilObject::_lookupType($vis_ref_id, true);
                $title = ilObject::_lookupTitle($context->getObjId());

                if ($type === "poll") {
                    $link = ilLink::_getLink($vis_ref_id, "poll");
                } elseif ($a_obj_type !== "pg") {
                    if (!isset($this->item_list_gui[$type])) {
                        $class = $objDefinition->getClassName($type);
                        $full_class = "ilObj" . $class . "ListGUI";
                        $this->item_list_gui[$type] = new $full_class();
                    }

                    // for references, get original title
                    // (link will lead to orignal, which basically is wrong though)
                    if ($a_obj_type === "crsr" || $a_obj_type === "catr" || $a_obj_type === "grpr") {
                        $tgt_obj_id = ilContainerReference::_lookupTargetId($context->getObjId());
                        $title = ilObject::_lookupTitle($tgt_obj_id);
                    }
                    $this->item_list_gui[$type]->initItem($vis_ref_id, $context->getObjId(), $title, $a_obj_type);
                    $link = $this->item_list_gui[$type]->getCommandLink("infoScreen");
                    $link = $this->item_list_gui[$type]->appendRepositoryFrameParameter($link) . "#note_" . $a_note_id;
                } else {
                    $title = ilObject::_lookupTitle($context->getObjId());
                    $link = "goto.php?target=pg_" . $a_obj_id . "_" . $vis_ref_id;
                }
            } else {  // personal workspace
                // we only need 1 instance
                if (!$this->wsp_tree) {
                    $this->wsp_tree = new ilWorkspaceTree($ilUser->getId());
                    $this->wsp_access_handler = new ilWorkspaceAccessHandler($this->wsp_tree);
                }
                $node_id = $this->wsp_tree->lookupNodeId($context->getObjId());
                if ($this->wsp_access_handler->checkAccess("visible", "", $node_id)) {
                    $path = $this->wsp_tree->getPathFull($node_id);
                    if ($path) {
                        $item = array_pop($path);
                        $title = $item["title"];
                        $link = ilWorkspaceAccessHandler::getGotoLink($node_id, $context->getObjId());
                    }
                    // shared resource
                    else {
                        $owner = ilObject::_lookupOwner($context->getObjId());
                        $title = ilObject::_lookupTitle($context->getObjId()) .
                            " (" . ilObject::_lookupOwnerName($owner) . ")";
                        $link = "ilias.php?baseClass=ilDashboardGUI&cmd=jumpToWorkspace&dsh=" .
                            $owner;
                    }
                }
            }
        }
        return [
            "title" => $title,
            "link" => $link,
        ];
    }

    /**
     * get notes list including add note area
     */
    public function addNoteForm(
        bool $a_init_form = true
    ) : string {
        $this->add_note_form = true;
        return $this->getListHTML($a_init_form);
    }
    
    /**
     * cancel add note
     */
    public function cancelAddNote() : string
    {
        return $this->getListHTML();
    }
    
    /**
     * cancel edit note
     */
    public function cancelUpdateNote() : string
    {
        return $this->getListHTML();
    }
    
    /**
     * add note
     */
    public function addNote() : void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        $data = $this->getNoteForm("create", $this->requested_note_type)->getData();
        $text = $data["note"] ?? "";

        //if ($this->form->checkInput())
        if ($text !== "" && !is_array($this->rep_obj_id)) {
            $context = $this->data->context(
                $this->rep_obj_id,
                $this->obj_id,
                $this->obj_type,
                $this->news_id,
                $this->repository_mode
            );
            $note = $this->data->note(
                0,
                $context,
                $text,
                $ilUser->getId(),
                $this->requested_note_type
            );
            $this->manager->createNote(
                $note,
                $this->observer
            );

            $ilCtrl->setParameter($this, "note_mess", "mod");
        }
        $ilCtrl->redirect($this, "getListHTML", "", $this->ctrl->isAsynch());
    }

    public function updateNote() : void
    {
        $ilCtrl = $this->ctrl;

        $note = $this->manager->getById($this->requested_note_id);
        $data = $this->getNoteForm("edit", $this->requested_note_type)->getData();
        $text = $data["note"] ?? "";

        if ($this->notes_access->canEdit($note)) {
            $this->manager->updateNoteText(
                $this->requested_note_id,
                $text,
                $this->observer
            );
            $ilCtrl->setParameter($this, "note_mess", "mod");
        }
        $ilCtrl->redirect($this, "getListHTML", "", $this->ctrl->isAsynch());
    }
    
    /**
     * get notes list including add note area
     */
    public function editNoteForm(
        bool $a_init_form = true
    ) : string {
        $this->edit_note_form = true;
        
        return $this->getListHTML($a_init_form);
    }

    /**
     * Render content into notes wrapper
     */
    public function renderContent(string $content) : string
    {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $ntpl = new ilTemplate(
            "tpl.notes_and_comments.html",
            true,
            true,
            "Services/Notes"
        );

        if (!$ctrl->isAsynch()) {
            $ntpl->setVariable("OUTER_ID", " id='notes_embedded_outer' ");
        }

        $ntpl->setVariable("CONTENT", $content);

        if ($ctrl->isAsynch() && !$this->request->isFilterCommand()) {
            echo $ntpl->get();
            exit;
        }

        return $ntpl->get();
    }

    protected function deleteNote() : string
    {
        $reldates = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $f = $this->ui->factory();
        $ctrl = $this->ctrl;
        $ctrl->setParameter($this, "note_id", $this->requested_note_id);
        $note = $this->manager->getById($this->requested_note_id);

        $text = ($this->requested_note_type === Note::PRIVATE)
            ? $this->lng->txt("notes_delete_note")
            : $this->lng->txt("notes_delete_comment");

        $mess = $f->messageBox()->confirmation($text);
        $item = $this->getItemForNote($note, false);

        $b1 = $this->getButton(
            "",
            $this->lng->txt("cancel"),
            "cancelDelete"
        );
        $b2 = $this->getButton(
            "",
            $this->lng->txt("delete"),
            "confirmDelete"
        );

        $it_group_title = $this->getItemGroupTitle($note->getContext()->getObjId());
        $item_groups = [$f->item()->group($it_group_title, [$item])];
        $panel = $f->panel()->listing()->standard("", $item_groups);

        $html = $this->renderComponents([$mess, $panel, $b2, $b1]);
        $html = str_replace($this->getNoteTextPlaceholder($note), $this->getNoteText($note), $html);

        return $this->renderContent($html);
    }

    public function cancelDelete() : string
    {
        return $this->getListHTML();
    }
    
    public function confirmDelete() : void
    {
        $ilCtrl = $this->ctrl;

        $cnt = 0;
        $ids = [$this->request->getNoteId()];
        foreach ($ids as $id) {
            $note = $this->manager->getById($id);
            if ($this->notes_access->canDelete($note, $this->user->getId(), $this->public_deletion_enabled)) {
                $this->manager->deleteNote($note, $this->user->getId(), $this->public_deletion_enabled);
                $cnt++;
            }
        }
        if ($cnt > 1) {
            $ilCtrl->setParameter($this, "note_mess", "ntsdel");
        } else {
            $ilCtrl->setParameter($this, "note_mess", "ntdel");
        }
        $ilCtrl->redirect($this, "getListHTML", "", $this->ajax);
    }

    /**
     * export selected notes to html
     */
    public function exportNotesHTML() : void
    {
        $tpl = new ilGlobalTemplate("tpl.main.html", true, true);

        $this->export_html = true;
        //$tpl->setVariable("CONTENT", $this->getListHTML());
        //ilUtil::deliverData($tpl->get(), "notes.html");

        $authors = array_unique(array_map(function (Note $note) {
            return $note->getAuthor();
        }, $this->getNotes($this->requested_note_type)));
        $export = new \ILIAS\Notes\Export\NotesHtmlExport(
            $this->requested_note_type,
            $this->user->getId(),
            $authors
        );
        $export->exportHTML($this->getListHTML());
    }
    
    /**
     * Get list notes js call
     */
    public static function getListNotesJSCall(
        string $a_hash,
        string $a_update_code = null
    ) : string {
        if ($a_update_code === null) {
            $a_update_code = "null";
        } else {
            $a_update_code = "'" . $a_update_code . "'";
        }
        
        return "ilNotes.listNotes(event, '" . $a_hash . "', " . $a_update_code . ");";
    }
    
    /**
     * Get list comments js call
     */
    public static function getListCommentsJSCall(
        string $a_hash,
        string $a_update_code = null
    ) : string {
        if ($a_update_code === null) {
            $a_update_code = "null";
        } else {
            $a_update_code = "'" . $a_update_code . "'";
        }
        
        return "ilNotes.listComments(event, '" . $a_hash . "', " . $a_update_code . ");";
    }

    /**
     * @throws ilCtrlException
     */
    public function getShyButton(
        string $a_var,
        string $a_txt,
        string $a_cmd,
        string $a_anchor = "",
        int $note_id = 0
    ) : \ILIAS\UI\Component\Button\Shy {
        $ctrl = $this->ctrl;
        $f = $this->ui->factory();

        if ($this->ajax) {
            $button = $f->button()->shy(
                $a_txt,
                "#"
            )->withOnLoadCode(function ($id) use ($ctrl, $a_cmd, $note_id) {
                $ctrl->setParameterByClass("ilnotegui", "note_id", $note_id);
                return
                    "$('#$id').on('click', () => { ilNotes.cmdAjaxLink(event, '" .
                    $ctrl->getLinkTargetByClass("ilnotegui", $a_cmd, "", true) .
                    "');});";
            });
        } else {
            $button = $f->button()->shy(
                $a_txt,
                $ctrl->getLinkTargetByClass("ilnotegui", $a_cmd, $a_anchor)
            );
        }
        return $button;
    }

    /**
     * @throws ilCtrlException
     */
    public function getButton(
        string $a_var,
        string $a_txt,
        string $a_cmd,
        string $a_anchor = ""
    ) : \ILIAS\UI\Component\Button\Standard {
        $ctrl = $this->ctrl;
        $f = $this->ui->factory();

        if ($this->ajax) {
            $button = $f->button()->standard(
                $a_txt,
                "#"
            )->withOnLoadCode(function ($id) use ($ctrl, $a_cmd) {
                return
                    "$('#$id').on('click', () => { ilNotes.cmdAjaxLink(event, '" .
                    $ctrl->getLinkTargetByClass("ilnotegui", $a_cmd, "", true) .
                    "');});";
            });
        } else {
            $button = $f->button()->standard(
                $a_txt,
                $ctrl->getLinkTargetByClass("ilnotegui", $a_cmd, $a_anchor)
            );
        }
        return $button;
    }

    /**
     * Add observer
     */
    public function addObserver(
        callable $a_callback
    ) : void {
        $this->observer[] = $a_callback;
    }

    protected function listSortAsc() : string
    {
        $this->manager->setSortAscending(true);
        return $this->getListHTML();
    }

    protected function listSortDesc() : string
    {
        $this->manager->setSortAscending(false);
        return $this->getListHTML();
    }

    /**
     * Get HTML
     */
    public function getHTML() : string
    {
        $this->gui->initJavascript();
        return $this->getCommentsWidget();
    }

    protected function getCommentsWidget() : string
    {
        $f = $this->ui->factory();
        $r = $this->ui->renderer();

        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $ctrl->setParameter($this, "news_id", $this->news_id);
        $hash = ilCommonActionDispatcherGUI::buildAjaxHash(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            null,
            ilObject::_lookupType($this->rep_obj_id),
            $this->rep_obj_id,
            $this->obj_type,
            $this->obj_id,
            $this->news_id
        );

        $context = $this->data->context(
            $this->rep_obj_id,
            $this->obj_id,
            $this->obj_type,
            $this->news_id
        );

        $cnt[$this->obj_id][Note::PUBLIC] = $this->manager->getNrOfNotesForContext($context, Note::PUBLIC);
        $cnt[$this->obj_id][Note::PRIVATE] = $this->manager->getNrOfNotesForContext($context, Note::PRIVATE);
        $cnt = $cnt[$this->rep_obj_id][Note::PUBLIC] ?? 0;

        $tpl = new ilTemplate("tpl.note_widget_header.html", true, true, "Services/Notes");
        $widget_el_id = "notew_" . str_replace(";", "_", $hash);
        $ctrl->setParameter($this, "hash", $hash);
        $update_url = $ctrl->getLinkTarget($this, "updateWidget", "", true, false);
        $comps = array();
        if ($cnt > 0) {
            $c = $f->counter()->status((int) $cnt);
            $comps[] = $f->symbol()->glyph()->comment()->withCounter($c)->withAdditionalOnLoadCode(function ($id) use ($hash, $update_url, $widget_el_id) {
                return "$(\"#$id\").click(function(event) { " . self::getListCommentsJSCall($hash, "ilNotes.updateWidget(\"" . $widget_el_id . "\",\"" . $update_url . "\");") . "});";
            });
            $comps[] = $f->divider()->vertical();
            $tpl->setVariable("GLYPH", $r->render($comps));
            $tpl->setVariable("TXT_LATEST", $lng->txt("notes_latest_comment"));
        }

        $b = $f->button()->shy($lng->txt("notes_add_edit_comment"), "#")->withAdditionalOnLoadCode(function ($id) use ($hash, $update_url, $widget_el_id) {
            return "$(\"#$id\").click(function(event) { " . self::getListCommentsJSCall($hash, "ilNotes.updateWidget(\"" . $widget_el_id . "\",\"" . $update_url . "\");") . "});";
        });
        if ($ctrl->isAsynch()) {
            $tpl->setVariable("SHY_BUTTON", $r->renderAsync($b));
        } else {
            $tpl->setVariable("SHY_BUTTON", $r->render($b));
        }

        $this->widget_header = $tpl->get();

        $this->hide_new_form = true;
        $this->only_latest = true;
        $this->no_actions = true;
        $html = "<div id='" . $widget_el_id . "'>" . $this->getNoteListHTML(Note::PUBLIC) . "</div>";
        $ctrl->setParameter($this, "news_id", $this->requested_news_id);
        return $html;
    }

    public function setExportMode() : void
    {
        $this->hide_new_form = true;
        $this->no_actions = true;
        $this->enable_sorting = false;
        $this->user_img_export_html = true;
    }

    protected function updateWidget() : void
    {
        echo $this->getCommentsWidget();
        exit;
    }

    protected function getOriginHeader() : string
    {
        if (!is_array($this->rep_obj_id) && !$this->only_latest && $this->ctrl->isAsynch()) {
            switch ($this->obj_type) {
                case "grpr":
                case "catr":
                case "crsr":
                    $title = ilContainerReference::_lookupTitle($this->rep_obj_id);
                    break;

                default:
                    $title = ilObject::_lookupTitle($this->rep_obj_id);
                    break;
            }

            $img = ilUtil::img(ilObject::_getIcon($this->rep_obj_id, "tiny"));

            // add sub-object if given
            if ($this->obj_id) {
                $sub_title = $this->getSubObjectTitle($this->rep_obj_id, $this->obj_id);
                if ($sub_title) {
                    $title .= " - " . $sub_title;
                }
            }

            return $img . " " . $title;
        }
        return "";
    }
}

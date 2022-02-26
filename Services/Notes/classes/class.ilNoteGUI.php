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

/**
 * Notes GUI class. An instance of this class handles all notes
 * (and their lists) of an object.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNoteGUI
{
    protected ilWorkspaceAccessHandler $wsp_access_handler;
    protected ilWorkspaceTree $wsp_tree;
    protected array $comment_img;
    protected array $note_img;
    protected bool $public_enabled;
    protected string $only;
    protected StandardGUIRequest $request;
    protected NotesManager $manager;
    protected bool $enable_hiding = false;
    protected bool $targets_enabled = false;
    protected bool $multi_selection = false;
    protected bool $export_html = false;
    protected bool $print = false;
    protected bool $comments_settings = false;
    protected string $obj_type;
    protected bool $private_enabled;
    protected bool $edit_note_form;
    protected bool $add_note_form;
    protected bool $anchor_jump;
    protected bool $ajax;
    protected bool $inc_sub;
    protected int $obj_id;
    protected int $rep_obj_id;
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
    protected string $default_command = "getNotesHTML";
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

    /**
     * @param int    $a_rep_obj_id object id of repository object (0 for personal desktop)
     * @param int    $a_obj_id sub-object id (0 for repository items, user id for personal desktop)
     * @param string $a_obj_type "pd" for personal desktop
     * @param bool   $a_include_subobjects include all subobjects of rep object (e.g. pages)
     * @param int    $a_news_id
     * @param bool   $ajax
     * @throws ilCtrlException
     */
    public function __construct(
        int $a_rep_obj_id = 0,
        int $a_obj_id = 0,
        string $a_obj_type = "",
        bool $a_include_subobjects = false,
        int $a_news_id = 0,
        bool $ajax = true
    ) {
        global $DIC;

        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->ui = $DIC->ui();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->log = ilLoggerFactory::getLogger('note');

        $this->manager = $DIC->notes()
            ->internal()
            ->domain()
            ->notes();
        $this->request = $DIC->notes()
            ->internal()
            ->gui()
            ->standardRequest();

        $lng->loadLanguageModule("notes");
        
        $ilCtrl->saveParameter($this, "notes_only");

        $this->rep_obj_id = $a_rep_obj_id;
        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_obj_type;
        $this->inc_sub = $a_include_subobjects;
        $this->news_id = $a_news_id;
        
        // auto-detect object type
        if (!$this->obj_type && $a_rep_obj_id) {
            $this->obj_type = ilObject::_lookupType($a_rep_obj_id);
        }

        $this->ajax = $ajax;

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        
        $this->anchor_jump = true;
        $this->add_note_form = false;
        $this->edit_note_form = false;
        $this->private_enabled = false;

        if (ilNote::commentsActivated($this->rep_obj_id, $this->obj_id, $this->obj_type, $this->news_id)) {
            $this->public_enabled = true;
        } else {
            $this->public_enabled = false;
        }
        $this->enable_hiding = false;
        $this->targets_enabled = false;
        $this->multi_selection = false;
        $this->export_html = false;
        $this->print = false;
        $this->comments_settings = false;
        
        $this->note_img = array(
            IL_NOTE_UNLABELED => array(
                "img" => ilUtil::getImagePath("note_unlabeled.svg"),
                "alt" => $lng->txt("note")),
            IL_NOTE_IMPORTANT => array(
                "img" => ilUtil::getImagePath("note_unlabeled.svg"),
                "alt" => $lng->txt("note") . ", " . $lng->txt("important")),
            IL_NOTE_QUESTION => array(
                "img" => ilUtil::getImagePath("note_unlabeled.svg"),
                "alt" => $lng->txt("note") . ", " . $lng->txt("question")),
            IL_NOTE_PRO => array(
                "img" => ilUtil::getImagePath("note_unlabeled.svg"),
                "alt" => $lng->txt("note") . ", " . $lng->txt("pro")),
            IL_NOTE_CONTRA => array(
                "img" => ilUtil::getImagePath("note_unlabeled.svg"),
                "alt" => $lng->txt("note") . ", " . $lng->txt("contra"))
            );
            
        $this->comment_img = array(
            IL_NOTE_UNLABELED => array(
                "img" => ilUtil::getImagePath("comment_unlabeled.svg"),
                "alt" => $lng->txt("notes_comment")),
            IL_NOTE_IMPORTANT => array(
                "img" => ilUtil::getImagePath("comment_unlabeled.svg"),
                "alt" => $lng->txt("notes_comment") . ", " . $lng->txt("important")),
            IL_NOTE_QUESTION => array(
                "img" => ilUtil::getImagePath("comment_unlabeled.svg"),
                "alt" => $lng->txt("notes_comment") . ", " . $lng->txt("question")),
            IL_NOTE_PRO => array(
                "img" => ilUtil::getImagePath("comment_unlabeled.svg"),
                "alt" => $lng->txt("notes_comment") . ", " . $lng->txt("pro")),
            IL_NOTE_CONTRA => array(
                "img" => ilUtil::getImagePath("comment_unlabeled.svg"),
                "alt" => $lng->txt("notes_comment") . ", " . $lng->txt("contra"))
            );
        
        // default: notes for repository objects
        $this->setRepositoryMode(true);

        $this->only = $this->request->getOnly();
        $this->requested_note_type = $this->request->getNoteType();
        $this->requested_note_id = $this->request->getNoteId();
        $this->requested_note_mess = $this->request->getNoteMess();
        $this->requested_news_id = $this->request->getNewsId();
    }
    
    public function setDefaultCommand(string $a_val) : void
    {
        $this->default_command = $a_val;
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

    public function enableHiding(bool $a_enable = true) : void
    {
        $this->enable_hiding = $a_enable;
    }
    
    // enable target objects
    public function enableTargets(bool $a_enable = true) : void
    {
        $this->targets_enabled = $a_enable;
    }

    // enable multi selection (checkboxes and commands)
    public function enableMultiSelection(bool $a_enable = true) : void
    {
        $this->multi_selection = $a_enable;
    }

    public function enableAnchorJump(bool $a_enable = true) : void
    {
        $this->anchor_jump = $a_enable;
    }
    
    public function setRepositoryMode(bool $a_value) : void
    {
        $this->repository_mode = $a_value;
    }

    public function getOnlyNotesHTML() : string
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter($this, "notes_only", "notes");
        $this->only = "notes";
        return $this->getNotesHTML($a_init_form = true);
    }
    
    public function getOnlyCommentsHTML() : string
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->setParameter($this, "notes_only", "comments");
        $this->only = "comments";
        return $this->getNotesHTML($a_init_form = true);
    }
    
    public function getNotesHTML(bool $a_init_form = true) : string
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        
        $lng->loadLanguageModule("notes");

        $ntpl = new ilTemplate(
            "tpl.notes_and_comments.html",
            true,
            true,
            "Services/Notes"
        );

        // check, whether column is hidden due to processing in other column
        $hide_comments = ($this->only == "notes");
        $hide_notes = ($this->only == "comments");
        switch ($ilCtrl->getCmd()) {
            case "addNoteForm":
            case "editNoteForm":
            case "addNote":
            case "updateNote":
                if ($this->requested_note_type == ilNote::PRIVATE) {
                    $hide_comments = true;
                }
                if ($this->requested_note_type == ilNote::PUBLIC) {
                    $hide_notes = true;
                }
                break;
        }


        // temp workaround: only show comments (if both have been activated)
        if ($this->private_enabled && $this->public_enabled && $this->only != "notes") {
            $this->private_enabled = false;
        }

        if (!$ilCtrl->isAsynch()) {
            $ntpl->setVariable("OUTER_ID", " id='notes_embedded_outer' ");
        }

        $nodes_col = false;
        if ($this->private_enabled && ($ilUser->getId() != ANONYMOUS_USER_ID)
            && !$hide_notes) {
            $ntpl->setCurrentBlock("notes_col");
            $ntpl->setVariable("NOTES", $this->getNoteListHTML(ilNote::PRIVATE, $a_init_form));
            $ntpl->parseCurrentBlock();
            $nodes_col = true;
        }
        
        // #15948 - public enabled vs. comments_settings
        $comments_col = false;
        if ($this->public_enabled && (!$this->delete_note || $this->public_deletion_enabled || $ilSetting->get("comments_del_user", 0))
            && !$hide_comments /* && $ilUser->getId() != ANONYMOUS_USER_ID */) {
            $ntpl->setVariable("COMMENTS", $this->getNoteListHTML(ilNote::PUBLIC, $a_init_form));
            $comments_col = true;
        }
        
        // Comments Settings
        if ($this->comments_settings && !$hide_comments && !$this->delete_note
            && !$this->edit_note_form && !$this->add_note_form && $ilUser->getId() != ANONYMOUS_USER_ID) {
            //$active = $notes_settings->get("activate_".$id);
            $active = ilNote::commentsActivated($this->rep_obj_id, $this->obj_id, $this->obj_type);

            if ($active) {
                if ($this->news_id == 0) {
                    $this->renderLink(
                        $ntpl,
                        "comments_settings",
                        $lng->txt("notes_deactivate_comments"),
                        "deactivateComments",
                        "notes_top"
                    );
                }
                $ntpl->setCurrentBlock("comments_settings2");
            } else {
                $this->renderLink(
                    $ntpl,
                    "comments_settings",
                    $lng->txt("notes_activate_comments"),
                    "activateComments",
                    "notes_top"
                );
                $ntpl->setCurrentBlock("comments_settings2");

                if ($this->ajax && !$comments_col) {
                    $ntpl->setVariable(
                        "COMMENTS_MESS",
                        ilUtil::getSystemMessageHTML($lng->txt("comments_feature_currently_not_activated_for_object"), "info")
                    );
                }
            }
            $ntpl->parseCurrentBlock();
            
            if (!$comments_col) {
                $ntpl->setVariable("COMMENTS", "");
            }
            
            $comments_col = true;
        }
        
        if ($comments_col) {
            $ntpl->setCurrentBlock("comments_col");
            if ($nodes_col) {
                //				$ntpl->touchBlock("comments_style");
            }
            $ntpl->parseCurrentBlock();
        }

        if ($ilCtrl->isAsynch()) {
            echo $ntpl->get();
            exit;
        }
        
        return $ntpl->get();
    }
    
    public function activateComments() : void
    {
        $ilCtrl = $this->ctrl;
        
        if ($this->comments_settings) {
            ilNote::activateComments($this->rep_obj_id, $this->obj_id, $this->obj_type, true);
        }
        
        $ilCtrl->redirectByClass("ilnotegui", "showNotes", "", $this->ajax);
    }

    public function deactivateComments() : void
    {
        $ilCtrl = $this->ctrl;
        
        if ($this->comments_settings) {
            ilNote::activateComments($this->rep_obj_id, $this->obj_id, $this->obj_type, false);
        }
        
        $ilCtrl->redirectByClass("ilnotegui", "showNotes", "", $this->ajax);
    }

    public function getNoteListHTML(
        int $a_type = ilNote::PRIVATE,
        bool $a_init_form = true
    ) : string {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $mtype = "";

        $suffix = ($a_type == ilNote::PRIVATE)
            ? "private"
            : "public";
        
        $user_setting_notes_public_all = "y";
        $user_setting_notes_by_type = "y";

        $filter = null;
        if ($this->delete_note || $this->export_html || $this->print) {
            if ($this->requested_note_id > 0) {
                $filter = $this->requested_note_id;
            } else {
                $filter = $this->request->getNoteText();
            }
        }

        $order = $this->manager->getSortAscending();
        if ($this->only_latest) {
            $order = false;
        }


        $notes = ilNote::_getNotesOfObject(
            $this->rep_obj_id,
            $this->obj_id,
            $this->obj_type,
            $a_type,
            $this->inc_sub,
            (string) $filter,
            $user_setting_notes_public_all,
            $this->repository_mode,
            $order,
            $this->news_id
        );

        $tpl = new ilTemplate("tpl.notes_list.html", true, true, "Services/Notes");

        if ($this->ajax) {
            $tpl->setCurrentBlock("close_img");
            $tpl->setVariable("CLOSE_IMG", ilGlyphGUI::get(ilGlyphGUI::CLOSE));
            $tpl->parseCurrentBlock();
        }
        
        // show counter if notes are hidden
        $cnt_str = (count($notes) > 0)
            ? " (" . count($notes) . ")"
            : "";

        // title
        if ($ilCtrl->isAsynch() && !$this->only_latest) {
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

            $tpl->setCurrentBlock("title");
            $tpl->setVariable("TITLE", $img . " " . $title);
            $tpl->parseCurrentBlock();
        }

        if ($this->delete_note) {
            $cnt_str = "";
        }
        if ($a_type == ilNote::PRIVATE) {
            $tpl->setVariable("TXT_NOTES", $lng->txt("private_notes") . $cnt_str);
            $ilCtrl->setParameterByClass("ilnotegui", "note_type", ilNote::PRIVATE);
        } else {
            $tpl->setVariable("TXT_NOTES", $lng->txt("notes_public_comments") . $cnt_str);
            $ilCtrl->setParameterByClass("ilnotegui", "note_type", ilNote::PUBLIC);
        }
        $anch = $this->anchor_jump
            ? "notes_top"
            : "";
        if (!$this->only_latest && !$this->hide_new_form) {
            $tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this, "getNotesHTML", $anch));
            if ($this->ajax) {
                $os = "onsubmit = \"ilNotes.cmdAjaxForm(event, '" .
                    $ilCtrl->getFormActionByClass("ilnotegui", "", "", true) .
                    "'); return false;\"";
                $tpl->setVariable("ON_SUBMIT_FORM", $os);
                /*if ($a_type == ilNote::PRIVATE) {
                    $tpl->setVariable("FORM_ID", "id='ilNoteFormAjax'");
                } else {
                    $tpl->setVariable("FORM_ID", "id='ilCommentFormAjax'");
                }*/
            }
        }

        
        if ($this->export_html || $this->print) {
            $tpl->touchBlock("print_style");
        }
        
        // show show/hide button for note list
        if (count($notes) > 0 && $this->enable_hiding && !$this->delete_note
            && !$this->export_html && !$this->print && !$this->edit_note_form
            && !$this->add_note_form) {
            // never individually hide for anonymous users
            if (($ilUser->getId() != ANONYMOUS_USER_ID)) {
                if ($a_type == ilNote::PUBLIC) {
                    $txt = $lng->txt("notes_hide_comments");
                } else {
                    $txt = $lng->txt("hide_" . $suffix . "_notes");
                }
                $this->renderLink($tpl, "hide_notes", $txt, "hideNotes", "notes_top");

                // show all public notes / my notes only switch
                if ($a_type == ilNote::PUBLIC) {
                    $this->renderLink(
                        $tpl,
                        "my_pub_notes",
                        $lng->txt("notes_my_comments"),
                        "showMyPublicNotes",
                        "notes_top"
                    );
                }
            }
        }
        
        // show add new note text area
        if (!$this->edit_note_form && $user_setting_notes_by_type != "n" &&
            !$this->delete_note && $ilUser->getId() != ANONYMOUS_USER_ID && !$this->hide_new_form) {
            if ($a_init_form) {
                $this->initNoteForm("create", $a_type);
            }

            $tpl->setCurrentBlock("edit_note_form");
            //			$tpl->setVariable("EDIT_FORM", $this->form->getHTML());
            $tpl->setVariable("EDIT_FORM", $this->form_tpl->get());
            $tpl->parseCurrentBlock();

            $tpl->parseCurrentBlock();
            $tpl->setCurrentBlock("note_row");
            $tpl->parseCurrentBlock();
        }
        
        // list all notes
        if ($user_setting_notes_by_type != "n" || !$this->enable_hiding) {
            $reldates = ilDatePresentation::useRelativeDates();
            ilDatePresentation::setUseRelativeDates(false);
            
            if (sizeof($notes) && !$this->only_latest && $this->enable_sorting) {
                if ($this->manager->getSortAscending()) {
                    $sort_txt = $lng->txt("notes_sort_desc");
                    $sort_cmd = "listSortDesc";
                } else {
                    $sort_txt = $lng->txt("notes_sort_asc");
                    $sort_cmd = "listSortAsc";
                }
                $this->renderLink($tpl, "sort_list", $sort_txt, $sort_cmd, $anch);
            }
            
            $notes_given = false;
            foreach ($notes as $note) {
                if ($this->only_latest && $notes_given) {
                    continue;
                }


                if ($this->edit_note_form && ($note->getId() == $this->requested_note_id)
                    && $a_type == $this->requested_note_type) {
                    if ($a_init_form) {
                        $this->initNoteForm("edit", $a_type, $note);
                    }
                    $tpl->setCurrentBlock("edit_note_form");
                    //					$tpl->setVariable("EDIT_FORM", $this->form->getHTML());
                    $tpl->setVariable("EDIT_FORM", $this->form_tpl->get());
                } else {
                    $cnt_col = 2;
                    
                    // delete note stuff for all private notes
                    if ($this->checkDeletion($note)
                        && !$this->delete_note
                        && !$this->export_html && !$this->print
                        && !$this->edit_note_form && !$this->add_note_form && !$this->no_actions) {
                        $ilCtrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
                        $this->renderLink(
                            $tpl,
                            "delete_note",
                            $lng->txt("delete"),
                            "deleteNote",
                            "note_" . $note->getId()
                        );
                    }
                    
                    // checkboxes in multiselection mode
                    if ($this->multi_selection && !$this->delete_note) {
                        $tpl->setVariable("CHECKBOX_CLASS", "ilNotesCheckboxes");
                        $tpl->setCurrentBlock("checkbox_col");
                        $tpl->setVariable("CHK_NOTE", "note[]");
                        $tpl->setVariable("CHK_NOTE_ID", $note->getId());
                        $tpl->parseCurrentBlock();
                        $cnt_col = 1;
                    }
                    
                    // edit note stuff for all private notes
                    if ($this->checkEdit($note)) {
                        if (!$this->delete_note && !$this->export_html && !$this->print
                            && !$this->edit_note_form && !$this->add_note_form && !$this->no_actions) {
                            $ilCtrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
                            $this->renderLink(
                                $tpl,
                                "edit_note",
                                $lng->txt("edit"),
                                "editNoteForm",
                                "note_edit"
                            );
                        }
                    }
                    
                    $tpl->setVariable("CNT_COL", $cnt_col);
                    
                    // output author account
                    if ($a_type == ilNote::PUBLIC && ilObject::_exists($note->getAuthor())) {
                        //$tpl->setCurrentBlock("author");
                        //$tpl->setVariable("VAL_AUTHOR", ilObjUser::_lookupLogin($note->getAuthor()));
                        //$tpl->parseCurrentBlock();
                        $tpl->setCurrentBlock("user_img");
                        $tpl->setVariable(
                            "USR_IMG",
                            ilObjUser::_getPersonalPicturePath($note->getAuthor(), "xsmall", false, false, $this->user_img_export_html)
                        );
                        $tpl->setVariable("USR_ALT", $lng->txt("user_image") . ": " .
                            ilObjUser::_lookupLogin($note->getAuthor()));
                        $tpl->parseCurrentBlock();
                        $tpl->setVariable(
                            "TXT_USR",
                            ilUserUtil::getNamePresentation($note->getAuthor(), false, false) . " - "
                        );
                    }
                    
                    // last edited
                    if ($note->getUpdateDate() != null) {
                        $tpl->setVariable("TXT_LAST_EDIT", $lng->txt("last_edited_on"));
                        $tpl->setVariable(
                            "DATE_LAST_EDIT",
                            ilDatePresentation::formatDate(new ilDate($note->getUpdateDate(), IL_CAL_DATETIME))
                        );
                    } else {
                        $tpl->setVariable(
                            "VAL_DATE",
                            ilDatePresentation::formatDate(new ilDate($note->getCreationDate(), IL_CAL_DATETIME))
                        );
                    }
                    
                    // hidden note ids for deletion
                    if ($this->delete_note) {
                        $tpl->setCurrentBlock("delete_ids");
                        $tpl->setVariable("HID_NOTE", "note[]");
                        $tpl->setVariable("HID_NOTE_ID", $note->getId());
                        $tpl->parseCurrentBlock();
                    }
                    $target = $note->getObject();
                    

                    $tpl->setCurrentBlock("note");
                    $text = (trim($note->getText()) != "")
                        ? nl2br($note->getText())
                        : "<p class='subtitle'>" . $lng->txt("note_content_removed") . "</p>";
                    $tpl->setVariable("NOTE_TEXT", $text);
                    $tpl->setVariable("VAL_SUBJECT", $note->getSubject());
                    $tpl->setVariable("NOTE_ID", $note->getId());

                    // target objects
                    $tpl->setVariable(
                        "TARGET_OBJECTS",
                        $this->renderTargets($note)
                    );
                }
                $tpl->parseCurrentBlock();
                $tpl->setCurrentBlock("note_row");
                $tpl->parseCurrentBlock();
                $notes_given = true;
            }
            
            if (!$notes_given) {
                $tpl->setCurrentBlock("no_notes");
                if ($a_type == ilNote::PUBLIC && !$this->only_latest) {
                    $tpl->setVariable("NO_NOTES", $lng->txt("notes_no_comments"));
                }
                $tpl->parseCurrentBlock();
            }
            
            ilDatePresentation::setUseRelativeDates($reldates);
            
            // multiple items commands
            if ($this->multi_selection && !$this->delete_note && !$this->edit_note_form
                && count($notes) > 0) {
                if ($a_type == ilNote::PRIVATE) {
                    $tpl->setCurrentBlock("delete_cmd");
                    $tpl->setVariable("TXT_DELETE_NOTES", $this->lng->txt("delete"));
                    $tpl->parseCurrentBlock();
                }
                
                $tpl->setCurrentBlock("multiple_commands");
                $tpl->setVariable("TXT_SELECT_ALL", $this->lng->txt("select_all"));
                $tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
                $tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
                $tpl->setVariable("TXT_PRINT_NOTES", $this->lng->txt("print"));
                $tpl->setVariable("TXT_EXPORT_NOTES", $this->lng->txt("exp_html"));
                $tpl->parseCurrentBlock();
            }

            // delete / cancel row
            if ($this->delete_note) {
                $tpl->setCurrentBlock("delete_cancel");
                $tpl->setVariable("TXT_DEL_NOTES", $this->lng->txt("delete"));
                $tpl->setVariable("TXT_CANCEL_DEL_NOTES", $this->lng->txt("cancel"));
                $tpl->parseCurrentBlock();
            }
            
            // print
            if ($this->print) {
                $tpl->touchBlock("print_js");
                $tpl->setCurrentBlock("print_back");
                $tpl->setVariable("LINK_BACK", $this->ctrl->getLinkTarget($this, "showNotes"));
                $tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
                $tpl->parseCurrentBlock();
            }
        }
        
        // message
        $mtxt = "";
        switch ($this->requested_note_mess != "" ? $this->requested_note_mess : $this->note_mess) {
            case "mod":
                $mtype = "success";
                $mtxt = $lng->txt("msg_obj_modified");
                break;
                
            case "ntsdel":
                $mtype = "success";
                $mtxt = ($a_type == ilNote::PRIVATE)
                    ? $lng->txt("notes_notes_deleted")
                    : $lng->txt("notes_comments_deleted");
                break;

            case "ntdel":
                $mtype = "success";
                $mtxt = ($a_type == ilNote::PRIVATE)
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
        if ($mtxt != "") {
            $tpl->setVariable("MESS", ilUtil::getSystemMessageHTML($mtxt, $mtype));
        } else {
            $tpl->setVariable("MESS", "");
        }

        if ($this->widget_header != "") {
            $tpl->setVariable("WIDGET_HEADER", $this->widget_header);
        }

        
        if ($this->delete_note && count($notes) == 0) {
            return "";
        } else {
            return $tpl->get();
        }
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
        if ($parent_type == "") {
            return "";
        }
        $parent_class = "ilObj" . $objDefinition->getClassName($parent_type) . "GUI";
        if (method_exists($parent_class, "lookupSubObjectTitle")) {
            return call_user_func_array(array($parent_class, "lookupSubObjectTitle"), array($parent_obj_id, $sub_obj_id));
        }
        return "";
    }
    
    /**
     * Check whether deletion is allowed
     */
    public function checkDeletion(
        ilNote $a_note
    ) : bool {
        $ilUser = $this->user;
        $ilSetting = $this->settings;
        
        if ($ilUser->getId() == ANONYMOUS_USER_ID) {
            return false;
        }
                
        $is_author = ($a_note->getAuthor() == $ilUser->getId());
        
        if ($a_note->getType() == ilNote::PRIVATE && $is_author) {
            return true;
        }
        
        if ($a_note->getType() == ilNote::PUBLIC && $this->public_deletion_enabled) {
            return true;
        }
        
        if ($a_note->getType() == ilNote::PUBLIC && $is_author && $ilSetting->get("comments_del_user", 0)) {
            return true;
        }
        
        return false;
    }
    
    public function checkEdit(
        ilNote $a_note
    ) : bool {
        $ilUser = $this->user;

        if ($a_note->getAuthor() == $ilUser->getId()
            && ($ilUser->getId() != ANONYMOUS_USER_ID)) {
            return true;
        }
        return false;
    }

    public function initNoteForm(
        string $a_mode,
        int $a_type,
        ilNote $a_note = null
    ) : void {
        $lng = $this->lng;

        $this->form_tpl = new ilTemplate("tpl.notes_edit.html", true, true, "Services/Notes");
        $this->form_tpl->setVariable("LABEL", ($a_type == ilNote::PUBLIC)
            ? $lng->txt("comment")
            : $lng->txt("note"));
        
        if ($a_note) {
            $this->form_tpl->setVariable("VAL_NOTE", ilLegacyFormElementsUtil::prepareFormOutput($a_note->getText()));
            $this->form_tpl->setVariable("NOTE_ID", $a_note->getId());
        }

        if ($a_mode === "create") {
            $this->form_tpl->setVariable("TXT_CMD", ($a_type === ilNote::PUBLIC)
                ? $lng->txt("note_add_comment")
                : $lng->txt("note_add_note"));
            $this->form_tpl->setVariable("CMD", "addNote");
        } else {
            $this->form_tpl->setVariable("TXT_CMD", ($a_type === ilNote::PUBLIC)
                ? $lng->txt("note_update_comment")
                : $lng->txt("note_update_note"));
            $this->form_tpl->setVariable("CMD", "updateNote");
        }
    }

    /**
     * Note display for dashboard
     */
    public function getPDNoteHTML(
        int $note_id
    ) : string {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $tpl = new ilTemplate("tpl.pd_note.html", true, true, "Services/Notes");
        $note = new ilNote($note_id);
        $target = $note->getObject();
        
        if ($note->getAuthor() != $ilUser->getId()) {
            return "";
        }
        
        $tpl->setCurrentBlock("edit_note");
        $ilCtrl->setParameterByClass("ilnotegui", "rel_obj", $target["rep_obj_id"]);
        $ilCtrl->setParameterByClass("ilnotegui", "note_id", $note_id);
        $ilCtrl->setParameterByClass("ilnotegui", "note_type", $note->getType());
        $tpl->setVariable(
            "LINK_EDIT_NOTE",
            $ilCtrl->getLinkTargetByClass(
                array("ildashboardgui", "ilpdnotesgui", "ilnotegui"),
                "editNoteForm"
            )
        );
        $tpl->setVariable("TXT_EDIT_NOTE", $lng->txt("edit"));
        $tpl->parseCurrentBlock();
        $ilCtrl->clearParametersByClass("ilnotegui");

        // last edited
        if ($note->getUpdateDate() != null) {
            $tpl->setVariable("TXT_LAST_EDIT", $lng->txt("last_edited_on"));
            $tpl->setVariable(
                "DATE_LAST_EDIT",
                ilDatePresentation::formatDate(new ilDate($note->getUpdateDate(), IL_CAL_DATETIME))
            );
        } else {
            //$tpl->setVariable("TXT_CREATED", $lng->txt("create_date"));
            $tpl->setVariable(
                "VAL_DATE",
                ilDatePresentation::formatDate(new ilDate($note->getCreationDate(), IL_CAL_DATETIME))
            );
        }
        
        $tpl->setVariable("VAL_SUBJECT", $note->getSubject());
        $text = (trim($note->getText()) != "")
            ? nl2br($note->getText())
            : "<p class='subtitle'>" . $lng->txt("note_content_removed") . "</p>";
        $tpl->setVariable("NOTE_TEXT", $text);
        $tpl->setVariable("TARGET_OBJECTS", $this->renderTargets($note));
        return $tpl->get();
    }
    
    /**
     * show related objects as links
     */
    public function renderTargets(
        ilNote $a_note
    ) : string {
        $tree = $this->tree;
        $ilAccess = $this->access;
        $objDefinition = $this->obj_definition;
        $ilUser = $this->user;

        if (!$this->targets_enabled) {
            return "";
        }

        $a_note_id = $a_note->getId();
        $target = $a_note->getObject();
        $a_obj_type = $target["obj_type"];
        $a_obj_id = $target["obj_id"];

        $target_tpl = new ilTemplate("tpl.note_target_object.html", true, true, "Services/Notes");

        if ($target["rep_obj_id"] > 0) {
            // get all visible references of target object

            // repository
            $ref_ids = ilObject::_getAllReferences($target["rep_obj_id"]);
            if ($ref_ids) {
                $vis_ref_ids = array();
                foreach ($ref_ids as $ref_id) {
                    if ($ilAccess->checkAccess("visible", "", $ref_id)) {
                        $vis_ref_ids[] = $ref_id;
                    }
                }

                // output links to targets
                if (count($vis_ref_ids) > 0) {
                    foreach ($vis_ref_ids as $vis_ref_id) {
                        $type = ilObject::_lookupType($vis_ref_id, true);
                        $title = ilObject::_lookupTitle($target["rep_obj_id"]);

                        $sub_link = $sub_title = "";
                        if ($type == "sahs") {		// bad hack, needs general procedure
                            $link = "goto.php?target=sahs_" . $vis_ref_id;
                            if ($a_obj_type == "sco" || $a_obj_type == "seqc" || $a_obj_type == "chap" || $a_obj_type == "pg") {
                                $sub_link = "goto.php?target=sahs_" . $vis_ref_id . "_" . $a_obj_id;
                                $sub_title = ilSCORM2004Node::_lookupTitle($a_obj_id);
                            }
                        } elseif ($type == "poll") {
                            $link = ilLink::_getLink($vis_ref_id, "poll");
                        } elseif ($a_obj_type != "pg") {
                            if (!isset($this->item_list_gui[$type])) {
                                $class = $objDefinition->getClassName($type);
                                $full_class = "ilObj" . $class . "ListGUI";
                                $this->item_list_gui[$type] = new $full_class();
                            }

                            // for references, get original title
                            // (link will lead to orignal, which basically is wrong though)
                            if ($a_obj_type == "crsr" || $a_obj_type == "catr" || $a_obj_type == "grpr") {
                                $tgt_obj_id = ilContainerReference::_lookupTargetId($target["rep_obj_id"]);
                                $title = ilObject::_lookupTitle($tgt_obj_id);
                            }
                            $this->item_list_gui[$type]->initItem($vis_ref_id, $target["rep_obj_id"], $title, $a_obj_type);
                            $link = $this->item_list_gui[$type]->getCommandLink("infoScreen");

                            // workaround, because # anchor can't be passed through frameset
                            $link = ilUtil::appendUrlParameterString($link, "anchor=note_" . $a_note_id);

                            $link = $this->item_list_gui[$type]->appendRepositoryFrameParameter($link) . "#note_" . $a_note_id;
                        } else {
                            $title = ilObject::_lookupTitle($target["rep_obj_id"]);
                            $link = "goto.php?target=pg_" . $a_obj_id . "_" . $vis_ref_id;
                        }

                        $par_id = $tree->getParentId($vis_ref_id);

                        // sub object link
                        if ($sub_link != "") {
                            if ($this->export_html || $this->print) {
                                $target_tpl->setCurrentBlock("exp_target_sub_object");
                            } else {
                                $target_tpl->setCurrentBlock("target_sub_object");
                                $target_tpl->setVariable("LINK_SUB_TARGET", $sub_link);
                            }
                            $target_tpl->setVariable("TXT_SUB_TARGET", $sub_title);
                            $target_tpl->parseCurrentBlock();
                        }

                        // container and object link
                        if ($this->export_html || $this->print) {
                            $target_tpl->setCurrentBlock("exp_target_object");
                        } else {
                            $target_tpl->setCurrentBlock("target_object");
                            $target_tpl->setVariable("LINK_TARGET", $link);
                        }
                        $target_tpl->setVariable(
                            "TXT_CONTAINER",
                            ilObject::_lookupTitle(
                                ilObject::_lookupObjId($par_id)
                            )
                        );
                        $target_tpl->setVariable("TXT_TARGET", $title);

                        $target_tpl->parseCurrentBlock();
                    }
                    $target_tpl->touchBlock("target_objects");
                }
            }
            // personal workspace
            else {
                // we only need 1 instance
                if (!$this->wsp_tree) {
                    $this->wsp_tree = new ilWorkspaceTree($ilUser->getId());
                    $this->wsp_access_handler = new ilWorkspaceAccessHandler($this->wsp_tree);
                }
                $node_id = $this->wsp_tree->lookupNodeId($target["rep_obj_id"]);
                if ($this->wsp_access_handler->checkAccess("visible", "", $node_id)) {
                    $path = $this->wsp_tree->getPathFull($node_id);
                    if ($path) {
                        $item = array_pop($path);
                        $parent = array_pop($path);

                        if (!$parent["title"]) {
                            $parent["title"] = $this->lng->txt("personal_resources");
                        }

                        // sub-objects
                        $additional = null;
                        if ($a_obj_id) {
                            $sub_title = $this->getSubObjectTitle($target["rep_obj_id"], $a_obj_id);
                            if ($sub_title) {
                                $item["title"] .= " (" . $sub_title . ")";
                                $additional = "_" . $a_obj_id;
                            }
                        }

                        $link = ilWorkspaceAccessHandler::getGotoLink($node_id, $target["rep_obj_id"], $additional);
                    }
                    // shared resource
                    else {
                        $owner = ilObject::_lookupOwner($target["rep_obj_id"]);
                        $parent["title"] = $this->lng->txt("wsp_tab_shared") .
                            " (" . ilObject::_lookupOwnerName($owner) . ")";
                        $item["title"] = ilObject::_lookupTitle($target["rep_obj_id"]);
                        $link = "ilias.php?baseClass=ilDashboardGUI&cmd=jumpToWorkspace&dsh=" .
                            $owner;
                    }

                    // container and object link
                    if ($this->export_html || $this->print) {
                        $target_tpl->setCurrentBlock("exp_target_object");
                    } else {
                        $target_tpl->setCurrentBlock("target_object");
                        $target_tpl->setVariable("LINK_TARGET", $link);
                    }


                    // :TODO: no images in template ?

                    $target_tpl->setVariable("TXT_CONTAINER", $parent["title"]);

                    $target_tpl->setVariable("TXT_TARGET", $item["title"]);

                    $target_tpl->parseCurrentBlock();
                }
            }
        }
        return $target_tpl->get();
    }

    /**
     * get notes list including add note area
     */
    public function addNoteForm(
        bool $a_init_form = true
    ) : string {
        $ilUser = $this->user;
        
        $suffix = ($this->requested_note_type == ilNote::PRIVATE)
            ? "private"
            : "public";
        $ilUser->setPref("notes_" . $suffix, "y");

        $this->add_note_form = true;
        return $this->getNotesHTML($a_init_form);
    }
    
    /**
     * cancel add note
     */
    public function cancelAddNote() : string
    {
        return $this->getNotesHTML();
    }
    
    /**
     * cancel edit note
     */
    public function cancelUpdateNote() : string
    {
        return $this->getNotesHTML();
    }
    
    /**
     * add note
     */
    public function addNote() : void
    {
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;
        $this->initNoteForm("create", $this->requested_note_type);

        //if ($this->form->checkInput())
        if ($this->request->getNoteText() != "") {
            $note = new ilNote();
            $note->setObject($this->obj_type, $this->rep_obj_id, $this->obj_id, $this->news_id);
            $note->setInRepository($this->repository_mode);
            $note->setType($this->requested_note_type);
            $note->setAuthor($ilUser->getId());
            $note->setText($this->request->getNoteText());
            $note->create();
            $this->notifyObserver("new", $note);
            $ilCtrl->setParameter($this, "note_mess", "mod");
        }
        $ilCtrl->redirect($this, "showNotes", "notes_top", $this->ajax);
    }

    public function updateNote() : void
    {
        $ilCtrl = $this->ctrl;

        $note = new ilNote($this->requested_note_id);
        $this->initNoteForm(
            "edit",
            $note->getType(),
            $note
        );

        $note->setText($this->request->getNoteText());
        $note->setSubject($this->request->getNoteSubject());
        $note->setLabel($this->request->getNoteLabel());
        if ($this->checkEdit($note)) {
            $note->update();
                
            $this->notifyObserver("update", $note);
                
            $ilCtrl->setParameter($this, "note_mess", "mod");
        }
        $ilCtrl->redirect($this, "showNotes", "notes_top", $this->ajax);
    }
    
    /**
     * get notes list including add note area
     */
    public function editNoteForm(
        bool $a_init_form = true
    ) : string {
        $this->edit_note_form = true;
        
        return $this->getNotesHTML($a_init_form);
    }

    /**
     * delete note confirmation
     */
    public function deleteNote() : string
    {
        $this->delete_note = true;
        $this->note_mess = "qdel";
        return $this->getNotesHTML();
    }
    
    /**
     * delete notes confirmation
     */
    public function deleteNotes() : string
    {
        if (count($this->request->getNoteIds()) == 0) {
            $this->note_mess = "noc";
        } else {
            $this->delete_note = true;
            $this->note_mess = "qdel";
        }

        return $this->getNotesHTML();
    }

    public function cancelDelete() : string
    {
        return $this->getNotesHTML();
    }
    
    public function confirmDelete() : void
    {
        $ilCtrl = $this->ctrl;

        $cnt = 0;
        $ids = $this->request->getNoteIds();
        foreach ($ids as $id) {
            $note = new ilNote($id);
            if ($this->checkDeletion($note)) {
                $note->delete();
                $cnt++;
            }
        }
        if ($cnt > 1) {
            $ilCtrl->setParameter($this, "note_mess", "ntsdel");
        } else {
            $ilCtrl->setParameter($this, "note_mess", "ntdel");
        }
        $ilCtrl->redirect($this, "showNotes", "notes_top", $this->ajax);
    }

    /**
     * export selected notes to html
     */
    public function exportNotesHTML() : void
    {
        $tpl = new ilGlobalTemplate("tpl.main.html", true, true);

        $this->export_html = true;
        $this->multi_selection = false;
        $tpl->setVariable("CONTENT", $this->getNotesHTML());
        ilUtil::deliverData($tpl->get(), "notes.html");
    }
    
    /**
     * notes print view screen
     */
    public function printNotes() : void
    {
        $tpl = new ilTemplate("tpl.main.html", true, true);

        $this->print = true;
        $this->multi_selection = false;
        $tpl->setVariable("CONTENT", $this->getNotesHTML());
        echo $tpl->get();
        exit;
    }

    public function showNotes() : string
    {
        $ilUser = $this->user;

        $suffix = ($this->requested_note_type == ilNote::PRIVATE)
            ? "private"
            : "public";
        $ilUser->writePref("notes_" . $suffix, "y");

        return $this->getNotesHTML();
    }
    
    public function hideNotes() : string
    {
        $ilUser = $this->user;

        $suffix = ($this->requested_note_type == ilNote::PRIVATE)
            ? "private"
            : "public";
        $ilUser->writePref("notes_" . $suffix, "n");

        return $this->getNotesHTML();
    }

    /**
     * show all public notes to user
     */
    public function showAllPublicNotes() : string
    {
        $ilUser = $this->user;
        
        $ilUser->writePref("notes_pub_all", "y");
        
        return $this->getNotesHTML();
    }

    /**
     * show only public notes of user
     */
    public function showMyPublicNotes() : string
    {
        $ilUser = $this->user;
        
        $ilUser->writePref("notes_pub_all", "n");
        
        return $this->getNotesHTML();
    }
    
    public static function initJavascript(
        string $a_ajax_url,
        int $a_type = ilNote::PRIVATE,
        ilGlobalTemplateInterface $a_main_tpl = null
    ) : void {
        global $DIC;

        if ($a_main_tpl != null) {
            $tpl = $a_main_tpl;
        } else {
            $tpl = $DIC["tpl"];
        }
        $lng = $DIC->language();

        $lng->loadLanguageModule("notes");

        ilModalGUI::initJS($tpl);

        $lng->toJS(array("private_notes", "notes_public_comments"), $tpl);

        iljQueryUtil::initjQuery($tpl);
        $tpl->addJavaScript("./Services/Notes/js/ilNotes.js");

        $tpl->addOnLoadCode("ilNotes.setAjaxUrl('" . $a_ajax_url . "');");
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

    public function renderLink(
        ilTemplate $a_tpl,
        string $a_var,
        string $a_txt,
        string $a_cmd,
        string $a_anchor = ""
    ) : void {
        $ilCtrl = $this->ctrl;
        
        $low_var = strtolower($a_var);
        $up_var = strtoupper($a_var);

        if ($this->ajax) {
            $a_tpl->setVariable("LINK_" . $up_var, "#");
            $oc = "onclick = \"ilNotes.cmdAjaxLink(event, '" .
                $ilCtrl->getLinkTargetByClass("ilnotegui", $a_cmd, "", true) .
                "');\"";
            $a_tpl->setVariable("ON_CLICK_" . $up_var, $oc);
        } else {
            $a_tpl->setVariable(
                "LINK_" . $up_var,
                $ilCtrl->getLinkTargetByClass("ilnotegui", $a_cmd, $a_anchor)
            );
        }
        
        $a_tpl->setCurrentBlock($low_var);
        $a_tpl->setVariable("TXT_" . $up_var, $a_txt);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Add observer
     */
    public function addObserver(
        callable $a_callback
    ) : void {
        $this->observer[] = $a_callback;
    }
    
    /**
     * Notify observers on update/create
     */
    protected function notifyObserver(
        string $a_action,
        ilNote $a_note
    ) : void {
        $this->log->debug("Notifying Observers (" . count($this->observer) . ").");
        if (count($this->observer) > 0) {
            foreach ($this->observer as $item) {
                $param = $a_note->getObject();
                //TODO refactor this, check what is this news_id from getObject
                unset($param['news_id']);
                $param["action"] = $a_action;
                $param["note_id"] = $a_note->getId();
                call_user_func_array($item, $param);
            }
        }
    }

    protected function listSortAsc() : string
    {
        $this->manager->setSortAscending(true);
        return $this->getNotesHTML();
    }

    protected function listSortDesc() : string
    {
        $this->manager->setSortAscending(false);
        return $this->getNotesHTML();
    }

    /**
     * Get HTML
     */
    public function getHTML() : string
    {
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

        $cnt = ilNote::_countNotesAndComments($this->rep_obj_id, $this->obj_id, $this->obj_type, $this->news_id);
        $cnt = $cnt[$this->rep_obj_id][ilNote::PUBLIC] ?? 0;

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
        $html = "<div id='" . $widget_el_id . "'>" . $this->getNoteListHTML(ilNote::PUBLIC) . "</div>";
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
}

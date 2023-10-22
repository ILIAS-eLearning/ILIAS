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

declare(strict_types=1);

use ILIAS\Notes\Note;

/**
 * Message GUI
 */
class ilMessageGUI extends ilNoteGUI
{
    protected int $note_type = Note::MESSAGE;
    protected bool $anonymised = false;
    protected string $counterpart_name = "";

    public function __construct(
        int $recipient,
        $rep_obj_id = 0,
        int $obj_id = 0,
        string $obj_type = ""
    ) {
        parent::__construct(
            $rep_obj_id,
            $obj_id,
            $obj_type
        );
        $this->enablePrivateNotes(false);
        $this->enablePublicNotes(false);
        $this->recipient = $recipient;
    }

    public function getNotesHTML(): string
    {
        throw new ilException("Call to getNotesHTML is deprecated");
    }

    public function getCommentsHTML(): string
    {
        throw new ilException("Call to getCommentsHTML is deprecated");
    }

    protected function getNoEntriesText(bool $search): string
    {
        if (!$search) {
            $mess_txt = $this->lng->txt("notes_no_messages");
        } else {
            $mess_txt = $this->lng->txt("notes_no_messages_found");
        }
        return $mess_txt;
    }

    protected function getItemGroupTitle(int $obj_id = 0): string
    {
        if (!$this->show_header) {
            return "";
        }
        return $this->lng->txt("notes_messages");
    }

    protected function getItemTitle(Note $note): string
    {
        if ($this->anonymised) {
            if ($note->getAuthor() === $this->user->getId()) {
                return $this->lng->txt("notes_message_author_you");
            } else {
                if ($this->counterpart_name !== "") {
                    return $this->counterpart_name;
                }
                return $this->lng->txt("notes_message_counterpart");
            }
        }
        $avatar = ilObjUser::_getAvatar($note->getAuthor());
        return ilUserUtil::getNamePresentation($note->getAuthor(), false, false);
    }

    protected function addItemProperties(Note $note, array &$properties): void
    {
        $creation_date = ilDatePresentation::formatDate(new ilDate($note->getCreationDate(), IL_CAL_DATETIME));
        $properties[$this->lng->txt("create_date")] = $creation_date;
    }

    protected function getFormLabelKey(): string
    {
        return "message";
    }

    protected function getDeleteText(): string
    {
        return $this->lng->txt("notes_delete_message");
    }

    public function getHTML(): string
    {
        throw new ilException("Not implemented.");
    }

    public function getListHTML(bool $a_init_form = true): string
    {
        return parent::getListHTML($a_init_form);
    }


    protected function getListTitle(): string
    {
        return $this->lng->txt("notes_messages");
    }

    protected function getAddText(): string
    {
        return $this->lng->txt("note_add_message");
    }

    protected function getDeletedMultipleText(): string
    {
        return $this->lng->txt("notes_messages_deleted");
    }

    protected function getDeletedSingleText(): string
    {
        return $this->lng->txt("notes_message_deleted");
    }

    protected function getLatestItemText(): string
    {
        return $this->lng->txt("notes_latest_message");
    }

    protected function getAddEditItemText(): string
    {
        return $this->lng->txt("notes_add_edit_message");
    }

    public function setAnonymised(bool $anonymised, string $counterpart_name)
    {
        $this->anonymised = $anonymised;
        $this->counterpart_name = $counterpart_name;
    }

}

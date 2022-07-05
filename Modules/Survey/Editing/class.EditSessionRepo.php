<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Editing;

/**
 * Stores access codes of anonymous session
 * @author Alexander Killing <killing@leifos.de>
 */
class EditSessionRepo
{
    protected const KEY_BASE = "svy_edit_";
    protected const KEY_CONSTRAINT_STRUCTURE = self::KEY_BASE . "constraints";
    protected const KEY_CONSTRAINT_ELEMENTS = self::KEY_BASE . "elements";
    protected const KEY_EXT_TEXT = self::KEY_BASE . "ext_text";

    public function __construct()
    {
    }

    public function setConstraintStructure(?array $structure) : void
    {
        \ilSession::set(self::KEY_CONSTRAINT_STRUCTURE, $structure);
    }

    public function getConstraintStructure() : ?array
    {
        return \ilSession::get(self::KEY_CONSTRAINT_STRUCTURE);
    }

    public function clearConstraintStructure() : void
    {
        \ilSession::clear(self::KEY_CONSTRAINT_STRUCTURE);
    }

    public function setConstraintElements(?array $elements) : void
    {
        \ilSession::set(self::KEY_CONSTRAINT_ELEMENTS, $elements);
    }

    public function getConstraintElements() : ?array
    {
        return \ilSession::get(self::KEY_CONSTRAINT_ELEMENTS);
    }

    public function clearConstraintElements() : void
    {
        \ilSession::clear(self::KEY_CONSTRAINT_ELEMENTS);
    }

    public function setMoveSurveyQuestions(int $survey_id, array $question_ids) : void
    {
        \ilSession::set(self::KEY_BASE . "move_svy_id", $survey_id);
        \ilSession::set(self::KEY_BASE . "move_svy_qids", $question_ids);
    }

    public function clearMoveSurveyQuestions() : void
    {
        \ilSession::clear(self::KEY_BASE . "move_svy_id");
        \ilSession::clear(self::KEY_BASE . "move_svy_qids");
    }

    public function getMoveSurveyQuestions() : array
    {
        if (\ilSession::has(self::KEY_BASE . "move_svy_qids")) {
            return \ilSession::get(self::KEY_BASE . "move_svy_qids");
        }
        return [];
    }

    public function getMoveSurveyId() : int
    {
        if (\ilSession::has(self::KEY_BASE . "move_svy_id")) {
            return \ilSession::get(self::KEY_BASE . "move_svy_id");
        }
        return 0;
    }

    protected function getClipKey(int $ref_id) : string
    {
        return self::KEY_BASE . "q_clip_" . $ref_id;
    }

    public function setQuestionClipboard(
        int $ref_id,
        int $page,
        string $mode,
        array $question_ids
    ) : void {
        \ilSession::set(
            $this->getClipKey($ref_id),
            [
                "source_page" => $page,
                "mode" => $mode,
                "question_ids" => $question_ids
            ]
        );
    }

    public function clearQuestionClipboard($ref_id) : void
    {
        \ilSession::clear($this->getClipKey($ref_id));
    }

    public function getQuestionClipboardSourcePage(int $ref_id) : ?int
    {
        if (\ilSession::has($this->getClipKey($ref_id))) {
            $data = \ilSession::get($this->getClipKey($ref_id));
            return $data["source_page"];
        }
        return null;
    }

    public function getQuestionClipboardMode(int $ref_id) : string
    {
        if (\ilSession::has($this->getClipKey($ref_id))) {
            $data = \ilSession::get($this->getClipKey($ref_id));
            return $data["mode"];
        }
        return "";
    }

    public function getQuestionClipboardQuestions(int $ref_id) : array
    {
        if (\ilSession::has($this->getClipKey($ref_id))) {
            $data = \ilSession::get($this->getClipKey($ref_id));
            return $data["question_ids"];
        }
        return [];
    }

    public function isQuestionClipboardEmpty(int $ref_id) : bool
    {
        if (\ilSession::has($this->getClipKey($ref_id))) {
            return false;
        }
        return true;
    }

    public function setPoolChoice(int $id) : void
    {
        \ilSession::set(self::KEY_BASE . "pool_choice", $id);
    }

    public function getPoolChoice() : int
    {
        if (\ilSession::has(self::KEY_BASE . "pool_choice")) {
            return \ilSession::get(self::KEY_BASE . "pool_choice");
        }
        return 0;
    }

    public function setExternalText(string $text) : void
    {
        \ilSession::set(self::KEY_EXT_TEXT, $text);
    }

    public function getExternalText() : string
    {
        return (string) \ilSession::get(self::KEY_EXT_TEXT);
    }
}

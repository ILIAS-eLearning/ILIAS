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

namespace ILIAS\SurveyQuestionPool\Editing;

/**
 * Stores session data in import process
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class EditSessionRepository
{
    public const KEY_BASE = "svy_edit_";
    public const KEY_QCLIP = self::KEY_BASE . "qclip";
    public const KEY_SLTYPE = self::KEY_BASE . "search_link_type";
    public const KEY_NLTYPE = self::KEY_BASE . "new_link_type";
    public const KEY_PHRASE_DATA = self::KEY_BASE . "phr_data";

    public function __construct()
    {
    }

    public function addQuestionToClipboard(int $qid, string $action) : void
    {
        $entries = [];
        if (\ilSession::has(self::KEY_QCLIP)) {
            $entries = \ilSession::get(self::KEY_QCLIP);
        }
        $entries[$qid] = [
            "question_id" => $qid,
            "action" => $action
        ];
        \ilSession::set(self::KEY_QCLIP, $entries);
    }

    public function getQuestionsFromClipboard() : array
    {
        $entries = [];
        if (\ilSession::has(self::KEY_QCLIP)) {
            $entries = \ilSession::get(self::KEY_QCLIP);
        }
        return $entries;
    }

    public function clearClipboardQuestions() : void
    {
        if (\ilSession::has(self::KEY_QCLIP)) {
            \ilSession::clear(self::KEY_QCLIP);
        }
    }

    public function setSearchLinkType(string $type) : void
    {
        \ilSession::set(self::KEY_SLTYPE, $type);
    }

    public function getSearchLinkType() : string
    {
        return (string) \ilSession::get(self::KEY_SLTYPE);
    }

    public function clearSearchLinkType() : void
    {
        if (\ilSession::has(self::KEY_SLTYPE)) {
            \ilSession::clear(self::KEY_SLTYPE);
        }
    }

    public function setNewLinkType(string $type) : void
    {
        \ilSession::set(self::KEY_NLTYPE, $type);
    }

    public function getNewLinkType() : string
    {
        return (string) \ilSession::get(self::KEY_NLTYPE);
    }

    public function clearNewLinkType() : void
    {
        if (\ilSession::has(self::KEY_NLTYPE)) {
            \ilSession::clear(self::KEY_NLTYPE);
        }
    }

    public function setPhraseData(array $data) : void
    {
        \ilSession::set(self::KEY_PHRASE_DATA, $data);
    }

    public function getPhraseData() : array
    {
        return (\ilSession::get(self::KEY_PHRASE_DATA) ?? []);
    }
}

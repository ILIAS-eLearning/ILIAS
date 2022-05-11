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

namespace ILIAS\Notes;

use ILIAS\Repository\BaseGUIRequest;

class StandardGUIRequest
{
    use BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getRelatedObjId() : int
    {
        return $this->int("rel_obj");
    }

    public function getNoteType() : int
    {
        return $this->int("note_type");
    }

    public function getNoteId() : int
    {
        return $this->int("note_id");
    }

    public function getNoteIds() : array
    {
        return $this->intArray("note");
    }

    public function getNoteMess() : string
    {
        return $this->str("note_mess");
    }

    public function getNoteText() : string
    {
        return $this->str("note");
    }

    public function getNoteSubject() : string
    {
        return $this->str("sub_note");
    }

    public function getNoteLabel() : string
    {
        return $this->str("note_label");
    }

    public function getOnly() : string
    {
        return $this->str("notes_only");
    }

    public function getNewsId() : int
    {
        return $this->int("news_id");
    }

    public function getSortation() : string
    {
        return $this->str("sortation");
    }

    public function isFilterCommand() : bool
    {
        return ($this->str("cmdFilter") !== "");
    }
}

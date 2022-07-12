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

namespace ILIAS\Glossary\Presentation;

use ILIAS\Repository\BaseGUIRequest;

class PresentationGUIRequest
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

    public function getMobId() : int
    {
        return $this->int("mob_id");
    }

    public function getExportType() : string
    {
        return $this->str("type");
    }

    public function getFileId() : string
    {
        return $this->str("file_id");
    }

    public function getSearchString() : string
    {
        return $this->str("srcstring");
    }

    public function getDefinitionPageId() : int
    {
        return $this->int("pg_id");
    }

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function getTermId() : int
    {
        return $this->int("term_id");
    }

    public function getDefinitionId() : int
    {
        return $this->int("def");
    }

    public function getTaxNode() : int
    {
        return $this->int("tax_node");
    }

    public function getLetter() : string
    {
        return $this->str("letter");
    }
}

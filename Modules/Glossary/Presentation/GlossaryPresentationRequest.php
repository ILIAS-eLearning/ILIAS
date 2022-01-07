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

namespace ILIAS\Glossary\Presentation;

/**
 * Glossary presentation request
 *
 * @author killing@leifos.de
 */
class GlossaryPresentationRequest
{
    protected int $requested_ref_id;
    protected int $requested_term_id;
    protected int $requested_tax_node;
    protected string $requested_letter;
    protected int $requested_def_pg_id;
    protected int $requested_mob_id;
    protected string $requested_file_id;
    protected string $requested_export_type;

    public function __construct(array $query_params)
    {
        $this->requested_ref_id = (int) $query_params["ref_id"];
        $this->requested_term_id = (int) $query_params["term_id"];
        $this->requested_tax_node = (int) $query_params["tax_node"];
        $this->requested_letter = (string) $query_params["letter"];
        $this->requested_def_pg_id = (int) $query_params["pg_id"];
        $this->requested_search_string = (string) $query_params["srcstring"];
        $this->requested_file_id = (string) $query_params["file_id"];
        $this->requested_mob_id = (int) $query_params["mob_id"];
        $this->requested_export_type = (string) $query_params["type"];
    }

    public function getRequestedMobId() : int
    {
        return $this->requested_mob_id;
    }

    public function getRequestedExportType() : string
    {
        return $this->requested_export_type;
    }

    public function getRequestedFileId() : string
    {
        return $this->requested_file_id;
    }

    public function getRequestedSearchString() : string
    {
        return $this->requested_search_string;
    }

    public function getRequestedDefinitionPageId() : int
    {
        return $this->requested_def_pg_id;
    }

    public function getRequestedRefId() : int
    {
        return $this->requested_ref_id;
    }

    public function getRequestedTermId() : int
    {
        return $this->requested_term_id;
    }

    public function getRequestedTaxNode() : int
    {
        return $this->requested_tax_node;
    }

    public function getRequestedLetter() : string
    {
        return $this->requested_letter;
    }
}

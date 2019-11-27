<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Glossary\Presentation;

/**
 * Glossary presentation request
 *
 * @author killing@leifos.de
 */
class GlossaryPresentationRequest
{
    /**
     * @var int
     */
    protected $requested_ref_id;

    /**
     * @var int
     */
    protected $requested_term_id;

    /**
     * @var int
     */
    protected $requested_tax_node;

    /**
     * @var string
     */
    protected $requested_letter;

    /**
     * @var int
     */
    protected $requested_def_pg_id;

    /**
     * @var int
     */
    protected $requested_mob_id;

    /**
     * @var string
     */
    protected $requested_file_id;

    /**
     * @var string
     */
    protected $requested_export_type;

    /**
     * Constructor
     */
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

    /**
     * @return int
     */
    public function getRequestedMobId(): int
    {
        return $this->requested_mob_id;
    }

    /**
     * @return string
     */
    public function getRequestedExportType(): string
    {
        return $this->requested_export_type;
    }

    /**
     * @return string
     */
    public function getRequestedFileId(): string
    {
        return $this->requested_file_id;
    }

    /**
     * @return string
     */
    public function getRequestedSearchString(): string
    {
        return $this->requested_search_string;
    }

    /**
     * @return int
     */
    public function getRequestedDefinitionPageId(): int
    {
        return $this->requested_def_pg_id;
    }

    /**
     * @return int
     */
    public function getRequestedRefId(): int
    {
        return $this->requested_ref_id;
    }

    /**
     * @return int
     */
    public function getRequestedTermId(): int
    {
        return $this->requested_term_id;
    }

    /**
     * @return int
     */
    public function getRequestedTaxNode(): int
    {
        return $this->requested_tax_node;
    }

    /**
     * @return string
     */
    public function getRequestedLetter(): string
    {
        return $this->requested_letter;
    }
}
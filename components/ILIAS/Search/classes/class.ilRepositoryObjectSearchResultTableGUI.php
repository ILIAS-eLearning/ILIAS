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

abstract class ilRepositoryObjectSearchResultTableGUI extends ilTable2GUI
{
    private ilSearchSettings $settings;
    protected int $ref_id;
    private string $search_term;

    private ?ilRepositoryObjectDetailSearchResult $results = null;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, int $a_ref_id)
    {
        $this->settings = ilSearchSettings::getInstance();
        $this->ref_id = $a_ref_id;
        $this->setId('rep_obj_search_res_' . $this->ref_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function setSearchTerm(string $a_term): void
    {
        $this->search_term = $a_term;
    }

    public function getSearchTerm(): string
    {
        return $this->search_term;
    }

    public function getSettings(): ilSearchSettings
    {
        return $this->settings;
    }

    public function setResults(ilRepositoryObjectDetailSearchResult $a_result): void
    {
        $this->results = $a_result;
    }

    public function getResults(): ilRepositoryObjectDetailSearchResult
    {
        return $this->results;
    }

    public function init(): void
    {
        $this->initColumns();
        $this->initRowTemplate();

        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->setLimit(0);

        $this->setTitle(
            $this->lng->txt('search_results') . ' "' . str_replace(['"'], '', ilLegacyFormElementsUtil::prepareFormOutput($this->getSearchTerm())) . '"'
        );
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->lng->txt("title"), "", "100%");
    }

    protected function initRowTemplate(): void
    {
        $this->setRowTemplate('tpl.repository_object_search_result_row.html', 'components/ILIAS/Search');
    }


    abstract public function parse();
}

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

namespace ILIAS\Search\GUI;

use ILIAS\Data\URI;
use ILIAS\Search\Presentation\Result\Sortation;
use ilCtrlInterface;
use ILIAS\Data\Factory as DataFactory;
use ilSearchGUI;

class ActionsImpl implements Actions
{
    protected const string SEARCH_CMD = 'search';
    protected const string REMOTE_SEARCH_CMD = 'remoteSearch';
    protected const string SHOW_SAVED_RESULTS_CMD = 'showSavedResults';
    protected const string APPLY_FILTER_CMD = 'applyFilter';
    protected const string SWITCH_RESULT_PAGE_CMD = 'switchResultPage';
    protected const string SORT_RESULT_PAGE_CMD = 'sortResultPage';
    protected const string AUTO_COMPLETE_CMD = 'autoComplete';

    public function __construct(
        protected ilCtrlInterface $ctrl,
        protected DataFactory $data_factory
    ) {
    }

    public function search(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            ilSearchGUI::class,
            self::SEARCH_CMD
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function remoteSearch(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            ilSearchGUI::class,
            self::REMOTE_SEARCH_CMD
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function showSavedResults(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            ilSearchGUI::class,
            self::SHOW_SAVED_RESULTS_CMD
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function applyFilter(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            ilSearchGUI::class,
            self::APPLY_FILTER_CMD
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function switchResultPage(Sortation $sortation): URI
    {
        $this->ctrl->setParameterByClass(ilSearchGUI::class, Param::SORTATION->value, $sortation->value);
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            ilSearchGUI::class,
            self::SWITCH_RESULT_PAGE_CMD
        );
        $this->ctrl->clearParameterByClass(ilSearchGUI::class, Param::SORTATION->value);
        return $this->ctrlToURI($ctrl_target);
    }

    public function sortResultPage(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            ilSearchGUI::class,
            self::SORT_RESULT_PAGE_CMD
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function autoComplete(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            ilSearchGUI::class,
            self::AUTO_COMPLETE_CMD,
            null,
            true
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function isValidCommand(string $cmd): bool
    {
        return in_array($cmd, [
            self::SEARCH_CMD,
            self::REMOTE_SEARCH_CMD,
            self::SHOW_SAVED_RESULTS_CMD,
            self::APPLY_FILTER_CMD,
            self::SWITCH_RESULT_PAGE_CMD,
            self::SORT_RESULT_PAGE_CMD,
            self::AUTO_COMPLETE_CMD
        ]);
    }

    protected function ctrlToURI(string $ctrl_target): URI
    {
        return $this->data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' . $ctrl_target
        );
    }
}

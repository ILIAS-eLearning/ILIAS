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

namespace ILIAS\Search\GUI\Global\User;

use ILIAS\Data\URI;
use ilCtrlInterface;
use ILIAS\Data\Factory as DataFactory;
use ilLuceneUserSearchGUI;
use ilSearchControllerGUI;
use ILIAS\Search\GUI\Global\Param;

class ActionsImpl implements Actions
{
    protected const string SEARCH_CMD = 'search';
    protected const string REMOTE_SEARCH_CMD = 'remoteSearch';
    protected const string SHOW_SAVED_RESULTS_CMD = 'showSavedResults';
    protected const string SWITCH_RESULT_PAGE_CMD = 'switchResultPage';

    public function __construct(
        protected ilCtrlInterface $ctrl,
        protected DataFactory $data_factory
    ) {
    }

    public function search(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            [ilSearchControllerGUI::class, ilLuceneUserSearchGUI::class],
            self::SEARCH_CMD
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function remoteSearch(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            [ilSearchControllerGUI::class, ilLuceneUserSearchGUI::class],
            self::REMOTE_SEARCH_CMD
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function showSavedResults(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            [ilSearchControllerGUI::class, ilLuceneUserSearchGUI::class],
            self::SHOW_SAVED_RESULTS_CMD
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function switchResultPage(): URI
    {
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            [ilSearchControllerGUI::class, ilLuceneUserSearchGUI::class],
            self::SWITCH_RESULT_PAGE_CMD
        );
        return $this->ctrlToURI($ctrl_target);
    }

    public function isValidCommand(string $cmd): bool
    {
        return in_array($cmd, [
            self::SEARCH_CMD,
            self::REMOTE_SEARCH_CMD,
            self::SHOW_SAVED_RESULTS_CMD,
            self::SWITCH_RESULT_PAGE_CMD
        ]);
    }

    protected function ctrlToURI(string $ctrl_target): URI
    {
        return $this->data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' . $ctrl_target
        );
    }
}

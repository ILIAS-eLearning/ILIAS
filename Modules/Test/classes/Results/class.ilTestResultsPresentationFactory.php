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

/**
 * @package Modules/Test
 * Results for one user and pass in a Presentation Table
 */
class ilTestResultsPresentationFactory
{
    public function __construct(
        protected ILIAS\UI\Factory $ui_factory,
        protected ILIAS\UI\Renderer $ui_renderer,
        protected ILIAS\Refinery\Factory $refinery,
        protected ILIAS\Data\Factory $data_factory,
        protected ILIAS\HTTP\Services $http,
        protected ilLanguage $lng
    ) {
    }

    public function getPassResultsPresentationTable(
        ilTestPassResult $pass_results,
        string $title = ''
    ): ilTestPassResultsTable {
        return  new ilTestPassResultsTable(
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->http,
            $this->data_factory,
            $this->lng,
            $pass_results,
            $title
        );
    }
}

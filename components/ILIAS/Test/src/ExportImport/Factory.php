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

namespace ILIAS\Test\ExportImport;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\Test\Logging\TestLogger;

class Factory
{
    public function __construct(
        private readonly \ilObjTest $test_obj,
        private readonly \ilLanguage $lng,
        private readonly TestLogger $logger,
        private readonly \ilTree $tree,
        private readonly \ilComponentRepository $component_repository,
        private readonly GeneralQuestionPropertiesRepository $questionrepository
    ) {
    }

    public function getExporter($mode = 'xml'): ExportFixedQuestionSet|ExportRandomQuestionSet
    {
        if ($this->test_obj->isFixedTest()) {
            return new ExportFixedQuestionSet($this->test_obj, $mode);
        }
        return new ExportRandomQuestionSet(
            $this->test_obj,
            $this->lng,
            $this->logger,
            $this->tree,
            $this->component_repository,
            $this->questionrepository,
            $mode
        );
    }
}

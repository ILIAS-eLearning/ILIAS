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

namespace ILIAS\Test\Logging;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\Data\ReferenceId;
use ILIAS\UI\Component\Link\Factory as LinkFactory;
use ILIAS\UI\Renderer as UIRenderer;

trait ColumnsHelperFunctionsTrait
{
    private function buildQuestionTitleColumnContent(
        GeneralQuestionPropertiesRepository $properties_repository,
        \ilLanguage $lng,
        StaticURLServices $static_url,
        LinkFactory $link_factory,
        UIRenderer $ui_renderer,
        ?int $question_id,
        int $test_ref_id
    ): string {
        if ($question_id === null) {
            return '';
        }
        $question_title = $properties_repository->getForQuestionId($question_id)?->getTitle();

        if ($question_title === null) {
            return "{$lng->txt('deleted')} ({$lng->txt('id')}: {$question_id})";
        }

        return $ui_renderer->render(
            $link_factory->standard(
                $properties_repository->getForQuestionId($question_id)?->getTitle(),
                $static_url->builder()->build(
                    'tst',
                    new ReferenceId($test_ref_id),
                    ['qst', $question_id]
                )->__toString()
            )
        );
    }

    private function buildQuestionTitleCSVContent(
        GeneralQuestionPropertiesRepository $properties_repository,
        \ilLanguage $lng,
        ?int $question_id
    ): string {
        if ($question_id === null) {
            return '';
        }
        $question_title = $properties_repository->getForQuestionId($question_id)?->getTitle();

        if ($question_title === null) {
            return "{$lng->txt('deleted')} ({$lng->txt('id')}: {$question_id})";
        }

        return $question_title;
    }

    private function buildTestTitleColumnContent(
        \ilLanguage $lng,
        StaticURLServices $static_url,
        LinkFactory $link_factory,
        UIRenderer $ui_renderer,
        int $test_ref_id
    ): string {
        $test_obj_id = \ilObject::_lookupObjId($test_ref_id);
        if ($test_obj_id === 0) {
            return "{$lng->txt('deleted')} ({$lng->txt('id')}: {$test_ref_id})";
        }

        if (\ilObject::_isInTrash($test_ref_id)) {
            return "{$lng->txt('in_trash')} ({$lng->txt('title')}: "
                . \ilObject::_lookupTitle($test_obj_id) . ", {$lng->txt('id')}: {$test_ref_id})";
        }

        return $ui_renderer->render(
            $link_factory->standard(
                \ilObject::_lookupTitle($test_obj_id),
                $static_url->builder()->build('tst', new ReferenceId($this->test_ref_id))->__toString()
            )
        );
    }

    private function buildTestTitleCSVContent(
        \ilLanguage $lng,
        int $test_ref_id
    ): string {
        $test_obj_id = \ilObject::_lookupObjId($test_ref_id);
        if ($test_obj_id === 0) {
            return "{$lng->txt('deleted')} ({$lng->txt('id')}: {$test_ref_id})";
        }

        return \ilObject::_lookupTitle($test_obj_id);
    }
}

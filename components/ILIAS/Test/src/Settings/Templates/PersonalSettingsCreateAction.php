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

namespace ILIAS\Test\Settings\Templates;

use ILIAS\Test\Scoring\Marks\MarksRepository;
use ILIAS\Test\Settings\MainSettings\MainSettingsRepository;
use ILIAS\Test\Settings\ScoreReporting\ScoreSettingsRepository;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Language\Language;
use Psr\Http\Message\ServerRequestInterface;

class PersonalSettingsCreateAction
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly Language $lng,
        private readonly \ilObjUser $user,
        private readonly PersonalSettingsRepository $repository,
        private readonly MainSettingsRepository $main_settings_repository,
        private readonly ScoreSettingsRepository $score_settings_repository,
        private readonly MarksRepository $marks_repository,
    ) {
    }

    public function buildModal(string $url): RoundTrip
    {
        $input_factory = $this->ui_factory->input();

        $inputs = [
            'name' => $input_factory->field()->text($this->lng->txt('title'))
                ->withLabel($this->lng->txt('title'))
                ->withRequired(true),
            'author' => $input_factory->field()->text($this->lng->txt('author'))
                ->withRequired(true)
                ->withValue($this->user->getFullname()),
            'description' => $input_factory->field()->textarea($this->lng->txt('description')),
        ];

        $explanation = $this->ui_factory->messageBox()->info(
            $this->lng->txt('personal_settings_explanation')
        );

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('personal_settings_create'),
            [$explanation],
            $inputs,
            $url
        )->withSubmitLabel($this->lng->txt('personal_settings_save'));
    }

    public function perform(int $test_id, ServerRequestInterface $request): void
    {
        $container = $this->buildModal('')->withRequest($request);
        $data = $container->getData();

        // 1. Resolve error messages on validation failure
        if ($data === null) {
            $inputs = $container->getInputs();

            if ($inputs['name']->getValue() === '') {
                throw new \InvalidArgumentException('personal_settings_required_title');
            }

            if ($inputs['author']->getValue() === '') {
                throw new \InvalidArgumentException('personal_settings_required_author');
            }
        }

        // 2. Create a new template
        $template = $this->repository->create(
            $data['name'],
            $data['description'] ?? '',
            $data['author']
        );

        // 3. Clone settings from test for the new template
        $this->main_settings_repository->store(
            $this->main_settings_repository->getFor($test_id)->withId($template->getSettingsId())
        );
        $this->score_settings_repository->store(
            $this->score_settings_repository->getFor($test_id)->withId($template->getSettingsId())
        );

        // 4. Clone the mark schema of the test and create references for the template
        $mark_schema = $this->marks_repository->getMarkSchemaFor($test_id);
        $mark_ids = $this->marks_repository->storeMarkSchema($mark_schema->withTestId(-1));
        $this->repository->associateMarkSteps($template->getId(), $mark_ids);
    }
}

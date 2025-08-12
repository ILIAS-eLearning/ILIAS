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
    ) {
    }

    public function buildInput(string $url): RoundTrip
    {
        $input_factory = $this->ui_factory->input();

        $inputs = [
            'name' => $input_factory->field()->text($this->lng->txt('title'))
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
        $data = $this->buildInput('')->withRequest($request)->getData();

        $name = $data['name'] ?? '';
        if ($name === '') {
            throw new \InvalidArgumentException('personal_settings_required_title');
        }

        $this->repository->createTemplateFor(
            $test_id,
            $name,
            $data['description'] ?? '',
            $data['author'] ?? ''
        );
    }
}

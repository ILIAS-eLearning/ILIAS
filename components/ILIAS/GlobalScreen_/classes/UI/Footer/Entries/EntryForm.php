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

namespace ILIAS\GlobalScreen\UI\Footer\Entries;

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use Psr\Http\Message\RequestInterface;
use ILIAS\GlobalScreen_\UI\Translator;
use ILIAS\GlobalScreen\UI\Footer\Groups\Group;

class EntryForm
{
    private readonly Factory $ui_factory;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private ?Standard $form = null;

    public function __construct(
        private readonly EntriesRepository $repository,
        private readonly Translator $translator,
        private readonly Group $group,
        private ?Entry $entry = null,
    ) {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->refinery = $DIC->refinery();
        $this->entry ??= $this->repository->blank();
    }

    protected function getInputs(): array
    {
        $ff = $this->ui_factory->input()->field();

        $inputs = [
            'title' => $ff
                ->text($this->translator->translate('title', 'entry'))
                ->withValue($this->entry->getTitle())
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(function (string $value): string {
                        $this->entry = $this->entry->withTitle($value);
                        return $value;
                    })
                )
                ->withRequired(true)
                ->withMaxLength(255),
            'active' => $ff
                ->checkbox(
                    $this->translator->translate('active', 'entry'),
                    $this->translator->translate('active_info', 'entry')
                )
                ->withValue($this->entry->isActive())
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(function (bool $value): bool {
                        $this->entry = $this->entry->withActive($value);
                        return $value;
                    })
                ),
            'action' => $ff
                ->url(
                    $this->translator->translate('action', 'entry'),
                    $this->translator->translate('action_info', 'entry')
                )
                ->withRequired(true)
                ->withValue($this->entry->getAction())
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(function (string $value): string {
                        $this->entry = $this->entry->withAction($value);
                        return $value;
                    })
                ),
            'external' => $ff
                ->checkbox($this->translator->translate('external', 'entry'))
                ->withValue($this->entry->isExternal())
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(function (bool $value): bool {
                        $this->entry = $this->entry->withExternal($value);
                        return $value;
                    })
                ),
        ];

        return [
            $ff->section($inputs, $this->translator->translate($this->entry->getId() === '' ? 'add' : 'edit', 'entry'))
        ];
    }

    public function get(string $target): Standard
    {
        return $this->form ?? $this->ui_factory
            ->input()
            ->container()
            ->form()
            ->standard($target, $this->getInputs())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(fn(array $value): ?Entry => $this->entry)
            );
    }

    public function store(RequestInterface $request, string $target): bool
    {
        $this->form = $this->get($target)->withRequest($request);
        $data = $this->form->getData();
        if ($data !== null) {
            $this->entry = $this->entry->withParent($this->group->getId());
            $this->repository->store($this->entry);
            return true;
        }
        return false;
    }

}

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

namespace ILIAS\GlobalScreen\UI\Footer\Groups;

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use Psr\Http\Message\RequestInterface;
use ILIAS\GlobalScreen_\UI\Translator;

class GroupForm
{
    private readonly Factory $ui_factory;
    private readonly \ILIAS\Refinery\Factory $refinery;

    public function __construct(
        private readonly GroupsRepository $repository,
        private readonly Translator $translator,
        private ?Group $group = null,
    ) {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->refinery = $DIC->refinery();
        $this->group ??= $this->repository->blank();
    }

    protected function getInputs(): array
    {
        $ff = $this->ui_factory->input()->field();

        $inputs = [
            'title' => $ff
                ->text($this->translator->translate('title', 'group'))
                ->withValue($this->group->getTitle())
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(function (string $value): string {
                        $this->group = $this->group->withTitle($value);
                        return $value;
                    })
                )
                ->withRequired(true)
                ->withMaxLength(255),
            'active' => $ff
                ->checkbox($this->translator->translate('active', 'group'))
                ->withValue($this->group->isActive())
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(function (bool $value): bool {
                        $this->group = $this->group->withActive($value);
                        return $value;
                    })
                )
            ,
        ];

        return [
            $ff->section($inputs, $this->translator->translate($this->group->getId() === '' ? 'add' : 'edit', 'group'))
        ];
    }

    public function get(string $target): Standard
    {
        return $this->ui_factory
            ->input()
            ->container()
            ->form()
            ->standard($target, $this->getInputs())
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(fn(array $value): ?Group => $this->group)
            );
    }

    public function store(RequestInterface $request, string $target): bool
    {
        $form = $this->get($target)->withRequest($request);
        $data = $form->getData();
        if ($data !== null) {
            $this->repository->store($this->group);
            return true;
        }
        return false;
    }

}

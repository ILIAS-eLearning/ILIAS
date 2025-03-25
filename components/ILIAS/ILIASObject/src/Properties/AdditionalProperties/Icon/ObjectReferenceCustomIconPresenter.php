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

namespace ILIAS\ILIASObject\Properties\AdditionalProperties\Icon;

class ObjectReferenceCustomIconPresenter implements Presenter
{
    private ?ilObjectCustomIcon $icon = null;

    public function __construct(
        private readonly int $obj_id,
        private readonly Factory $factory
    ) {
    }

    public function init(): void
    {
        $this->icon = $this->factory->getByObjId($this->lookupTargetId());
    }

    public function exists(): bool
    {
        return $this->icon->exists();
    }

    public function getFullPath(): string
    {
        return $this->icon->getFullPath();
    }

    protected function lookupTargetId(): int
    {
        return \ilContainerReference::_lookupTargetId($this->obj_id);
    }
}

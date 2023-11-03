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

namespace ILIAS\Container\Classification;

class ClassificationManager
{
    protected ClassificationSessionRepository $repo;
    protected int $base_ref_id;

    public function __construct(
        ClassificationSessionRepository $repo,
        int $base_ref_id
    ) {
        $this->repo = $repo;
        $this->base_ref_id = $base_ref_id;
    }

    public function clearSelection(): void
    {
        $this->repo->unsetAll();
    }

    public function clearSelectionOfProvider(string $provider): void
    {
        $this->repo->unsetValueForProvider($provider);
    }

    public function isEmptySelection(): bool
    {
        return $this->repo->isEmpty();
    }

    public function getSelectionOfProvider(string $provider): array
    {
        return $this->repo->getValueForProvider($provider);
    }

    public function setSelectionOfProvider(string $provider, array $value): void
    {
        $this->repo->setValueForProvider($provider, $value);
    }

}

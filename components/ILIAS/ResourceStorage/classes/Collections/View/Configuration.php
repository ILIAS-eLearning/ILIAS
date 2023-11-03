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

namespace ILIAS\Services\ResourceStorage\Collections\View;

use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class Configuration
{
    public function __construct(
        private ResourceCollection $collection,
        private ResourceStakeholder $stakeholder,
        private string $title,
        private Mode $mode = Mode::DATA_TABLE,
        private int $items_per_page = 100,
        private bool $user_can_upload = false,
        private bool $user_can_administrate = false,
    ) {
    }

    public function getCollection(): ResourceCollection
    {
        return $this->collection;
    }

    public function getStakeholder(): ResourceStakeholder
    {
        return $this->stakeholder;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getItemsPerPage(): int
    {
        return $this->items_per_page;
    }

    public function getMode(): Mode
    {
        return $this->mode;
    }

    public function canUserUpload(): bool
    {
        return $this->user_can_upload;
    }

    public function canUserAdministrate(): bool
    {
        return $this->user_can_administrate;
    }
}

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

namespace ILIAS\Services\ResourceStorage\Resources\Listing;

use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ViewDefinition
{
    public const MODE_AS_TABLE = 1;
    public const MODE_AS_ITEMS = 2;
    public const MODE_AS_DECK = 3;
    private int $mode = self::MODE_AS_TABLE;

    public function __construct(
        private string $embedding_gui,
        private string $embedding_cmd,
        private string $title,
        private int $items_per_page = 50,
        private ?\ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder $stakeholder = null,
        private bool $enable_upload = false
    ) {
        if ($this->enable_upload && !$this->stakeholder instanceof \ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder) {
            throw new \InvalidArgumentException('If upload is enabled, a stakeholder must be provided');
        }
    }

    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }

    public function getMode(): int
    {
        return $this->mode;
    }


    public function getEmbeddingGui(): string
    {
        return $this->embedding_gui;
    }

    public function getEmbeddingCmd(): string
    {
        return $this->embedding_cmd;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getItemsPerPage(): int
    {
        return $this->items_per_page;
    }

    public function getStakeholder(): ?ResourceStakeholder
    {
        return $this->stakeholder;
    }

    public function isEnableUpload(): bool
    {
        return $this->enable_upload;
    }
}

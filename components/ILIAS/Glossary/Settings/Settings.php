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

namespace ILIAS\Glossary\Settings;

class Settings
{
    public function __construct(
        protected int $id,
        protected bool $online,
        protected string $virtual,
        protected bool $glo_menu_active,
        protected string $pres_mode,
        protected int $show_tax,
        protected int $snippet_length,
        protected bool $flash_active,
        protected string $flash_mode
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOnline(): bool
    {
        return $this->online;
    }

    public function getVirtualMode(): string
    {
        return $this->virtual;
    }

    public function getActiveGlossaryMenu(): bool
    {
        return $this->glo_menu_active;
    }


    public function getPresentationMode(): string
    {
        return $this->pres_mode;
    }

    public function getShowTaxonomy(): int
    {
        return $this->show_tax;
    }

    public function getSnippetLength(): int
    {
        return $this->snippet_length;
    }

    public function getActiveFlashcards(): bool
    {
        return $this->flash_active;
    }

    public function getFlashcardsMode(): string
    {
        return $this->flash_mode;
    }
}

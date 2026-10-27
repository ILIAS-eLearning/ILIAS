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

namespace ILIAS\UI\Implementation\Component\Prompt\State;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Prompt as I;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Data\URI;

class State implements I\State\State
{
    use ComponentHelper;

    protected ?string $title = null;

    /**
     * @param I\IsPromptContent[] $secondary_content
     */
    public function __construct(
        protected Instruction $instruction,
        protected ?URI $redirect,
        protected ?I\IsPromptContent $primary_content,
        protected array $secondary_content = [],
    ) {
    }

    public function withTitle(string $title): static
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getInstruction(): Instruction
    {
        return $this->instruction;
    }

    public function getRedirect(): ?URI
    {
        return $this->redirect;
    }

    public function getPrimaryContent(): ?I\IsPromptContent
    {
        return $this->primary_content;
    }

    /** @return I\IsPromptContent[] */
    public function getSecondaryContent(): array
    {
        return $this->secondary_content;
    }
}

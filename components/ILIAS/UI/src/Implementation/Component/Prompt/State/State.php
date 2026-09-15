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

/**
 *
 */
class State implements I\State\State
{
    use ComponentHelper;

    public const string CMD_CLOSE = 'close';
    public const string CMD_REDIRECT = 'redirect';

    protected array $buttons = [];
    protected string $cmd = 'show';
    protected array $params = [];
    protected string $title = '';
    protected array $secondary_content = [];

    public function __construct(
        protected ?I\IsPromptContent $primary_content
    ) {
    }

    public function getTitle(): string
    {
        return $this->title ? $this->title : $this->primary_content->getPromptTitle();
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withPrimaryContent(I\IsPromptContent $content): self
    {
        $clone = clone $this;
        $clone->primary_content = $content;
        return $clone;
    }

    public function withSecondaryContent(I\IsPromptContent ...$content): self
    {
        $clone = clone $this;
        $clone->secondary_content = $content;
        return $clone;
    }

    /**
     * @return Component[]
     */
    public function getPrimaryContent(): ?I\IsPromptContent
    {
        return $this->primary_content;
    }

    /**
     * @return I\IsPromptContent[]
     */
    public function getSecondaryContent(): array
    {
        return $this->secondary_content;
    }

    /**
     * @return Button\Button[]
     */
    public function getButtons(): array
    {
        return $this->primary_content->getPromptButtons();
    }

    public function withCloseModal(bool $flag): self
    {
        return $this->withCommand($flag ? self::CMD_CLOSE : '');
    }

    public function withRedirect(URI $redirect): self
    {
        $clone = $this->withCommand(self::CMD_REDIRECT);
        $clone->params = [
            self::CMD_REDIRECT => $redirect->__toString()
        ];
        return $clone;
    }

    protected function withCommand(string $cmd)
    {
        $clone = clone $this;
        $clone->cmd = $cmd;
        return $clone;
    }

    public function getCommand(): string
    {
        return $this->cmd;
    }

    public function getParameters(): array
    {
        return $this->params;
    }
}

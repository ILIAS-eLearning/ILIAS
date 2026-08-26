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

    public const CMD_CLOSE = 'close';
    public const CMD_REDIRECT = 'redirect';

    protected array $buttons = [];
    protected string $cmd = 'show';
    protected array $params = [];
    protected string $title = '';

    public function __construct(
        protected ?I\IsPromptContent $content
    ) {
    }

    public function getTitle(): string
    {
        return $this->title ? $this->title : $this->content->getPromptTitle();
    }

    public function withTitle(string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withContent(I\IsPromptContent $content): self
    {
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }

    /**
     * @return Component[]
     */
    public function getContent(): ?I\IsPromptContent
    {
        return $this->content;
    }

    /**
     * @return Button[]
     */
    public function getButtons(): array
    {
        return $this->content->getPromptButtons();
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

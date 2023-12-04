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

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button;
use ILIAS\UI\Component\Modal as M;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 *
 */
class DialogResponse implements M\DialogResponse
{
    use ComponentHelper;

    private const CMD_CLOSE = 'close';

    protected M\DialogContent $content;
    protected array $buttons = [];
    protected string $cmd = 'show';

    public function __construct(
        //protected Button\Factory $button_factory,
        M\DialogContent $content
    ) {
        $this->content = $content;
    }

    public function getTitle(): ?string
    {
        return $this->content->getDialogTitle();
    }

    public function withContent(M\DialogContent $content): self
    {
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }

    /**
     * @return Component[]
     */
    public function getContent(): M\DialogContent
    {
        return $this->content;
    }

    /**
     * @return Button[]
     */
    public function getButtons(): array
    {
        return $this->content->getDialogButtons();
    }


    public function withCloseModal(bool $flag): self
    {
        return $this->withCommand($flag ? self::CMD_CLOSE : '');
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

    /*public function getCloseButton(string $label = 'Cancel'): Button\Standard
    {
        return $this->button_factory->standard($label, '')
            ->withOnLoadCode(
                fn($id) => "$('#$id').on('click', (e)=> {
                    let dialogId = e.target.closest('dialog').parentNode.id;
                    il.UI.modal.dialog.get(dialogId).close();
                });"
            );
    }*/
}

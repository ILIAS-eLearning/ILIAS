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

use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\GUI\I18n\Translator;
use ILIAS\GlobalScreen\GUI\Access\Access;
use ILIAS\GlobalScreen\GUI\Flow\Flow;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\GlobalScreen\GUI\Hasher;
use ILIAS\GlobalScreen\GUI\I18n\SupportsTranslationGUI;
use ILIAS\GlobalScreen\GUI\Flow\Command;

abstract class ilMMBaseGUI implements SupportsTranslationGUI
{
    use Hasher;

    /**
     * @var string
     */
    public const CMD_DEFAULT = 'index';
    /**
     * @var string
     */
    public const CMD_ADD = 'add';
    /**
     * @var string
     */
    public const CMD_CREATE = 'create';
    /**
     * @var string
     */
    public const CMD_EDIT = 'edit';
    /**
     * @var string
     */
    public const CMD_DELETE = 'delete';
    /**
     * @var string
     */
    public const CMD_CONFIRM_DELETE = 'confirmDelete';
    /**
     * @var string
     */
    public const CMD_UPDATE = 'update';
    /**
     * @var string
     */
    public const CMD_ACTIVATE = 'activate';
    /**
     * @var string
     */
    public const CMD_DEACTIVATE = 'deactivate';
    /**
     * @var string
     */
    public const CMD_SAVE_ORDER = 'saveOrder';

    protected ilMMItemRepository $repository;
    protected UIServices $ui;
    protected Translator $lng;
    protected Access $access;
    protected Flow $flow;
    protected ilToolbarGUI $toolbar;

    public function __construct(protected Pons $pons)
    {
        $this->ui = $this->pons->out()->ui();
        $this->lng = $this->pons->i18n();
        $this->repository = new ilMMItemRepository();
        $this->access = $this->pons->access();
        $this->flow = $this->pons->flow();
        $this->toolbar = $this->pons->out()->toolbar();
    }

    protected function keepTokens(): void
    {
        $this->pons->in()->keepTokens($this);
    }

    abstract public function getCurrentItem(): ilMMItemFacadeInterface;

    /**
     * @return ilMMItemFacadeInterface[]
     */
    abstract public function getMutlipleItems(): array;

    abstract public function getTokensToKeep(): array;

    public function executeCommand(): bool
    {
        match ($cmd = $this->pons->flow()->getCommand(self::CMD_DEFAULT)) {
            self::CMD_DEFAULT => $this->index(),
            self::CMD_ADD => $this->add(),
            self::CMD_EDIT => $this->edit(),
            self::CMD_CREATE => $this->create(),
            self::CMD_UPDATE => $this->update(),
            self::CMD_ACTIVATE => $this->activate(),
            self::CMD_DEACTIVATE => $this->deactivate(),
            self::CMD_CONFIRM_DELETE => $this->confirmDelete(),
            self::CMD_DELETE => $this->delete(),
            self::CMD_SAVE_ORDER => $this->saveOrder(),
            default => $this->pons->out()->outString('Command not found:' . $cmd)
        };
        return true;
    }

    #[Command('write')]
    private function add(): void
    {
        $this->form(false);
    }

    #[Command('write')]
    private function edit(): void
    {
        $this->form(false);
    }

    #[Command('write')]
    private function create(): void
    {
        $this->form(true);
    }

    #[Command('write')]
    private function update(): void
    {
        $this->form(true);
    }

    #[Command('write')]
    protected function delete(): void
    {
        foreach ($this->getMutlipleItems() as $item) {
            if ($item->isDeletable()) {
                $this->repository->deleteItem($item);
            }
        }
        $this->pons->out()->success($this->lng->txt("msg_topitem_deleted"), true);
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

    #[Command('write')]
    protected function confirmDelete(): void
    {
        $this->keepTokens();
        $items = [];
        foreach ($this->getMutlipleItems() as $item) {
            $items[] = $this->ui->factory()->modal()->interruptiveItem()->standard(
                $this->hash($item->identification()->serialize()),
                $item->getDefaultTitle()
            );
        }

        $this->pons->out()->outAsyncAsConfirmation(
            $this->lng->txt('delete'),
            $this->lng->txt('confirm_delete'),
            $this->lng->txt('delete'),
            $this->flow->getHereAsURI(self::CMD_DELETE),
            ...$items
        );
    }

    #[Command('write')]
    protected function activate(): void
    {
        $this->toggle(true);
    }

    #[Command('write')]
    protected function deactivate(): void
    {
        $this->toggle(false);
    }

    #[Command('write')]
    protected function toggle(bool $activation): void
    {
        $not_changed = [];
        $changed = [];

        foreach ($this->getMutlipleItems() as $item) {
            if (!$item->canBeDeactivated()) {
                $not_changed[] = $item->getDefaultTitle();
                continue;
            }
            $item->setActiveStatus($activation);
            $item->update();
            $changed[] = $item->getDefaultTitle();
        }
        if ($changed !== []) {
            $this->pons->out()->success(
                $this->lng->t('msg_success'),
                true
            );
        }

        if ($not_changed !== []) {
            $this->pons->out()->error(
                $this->lng->t('msg_not_changed', null, [implode(', ', $not_changed)]),
                true
            );
        }
        $this->flow->redirect(self::CMD_DEFAULT);
    }

    #[Command('write')]
    protected function saveOrder(): void
    {
        foreach ($this->pons->in()->request()->getParsedBody() as $hashed_id => $position) {
            $item = $this->repository->getItemFacadeForIdentificationString($this->unhash($hashed_id));
            $item->setPosition((int) $position);
            $this->repository->updateItem($item);
        }
        $this->pons->out()->success($this->pons->i18n()->translate('order_saved'));
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

}

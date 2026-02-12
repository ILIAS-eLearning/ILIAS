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

use ILIAS\GlobalScreen\GUI\Flow\Command;
use ILIAS\GlobalScreen\GUI\Input\TokenContainer;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\GlobalScreen\GUI\Input\Input;

/**
 * @ilCtrl_IsCalledBy ilMMTopItemGUI: ilObjMainMenuGUI
 * @ilCtrl_Calls      ilMMTopItemGUI: ILIAS\GlobalScreen\GUI\I18n\MultiLanguageGUI
 *
 * @author            Fabian Schmid <fabian@sr.solutions>
 */
class ilMMTopItemGUI extends ilMMBaseGUI
{
    public const CMD_RESTORE = 'restore';
    public const CMD_SELECT_PARENT = 'selectParent';
    public const CMD_MOVE = 'move';
    public const string CMD_FLUSH = 'flush';

    private TokenContainer $top_token;

    public function __construct(Pons $pons)
    {
        parent::__construct($pons);
        $this->top_token = $this->pons->in()->buildToken('top', 'id');
    }

    #[\Override]
    public function executeCommand(): bool
    {
        if ($this->pons->handle('top_items')) {
            return true;
        }

        $command = $this->flow->getCommand(self::CMD_DEFAULT);
        match ($command) {
            self::CMD_SELECT_PARENT => $this->selectParent(),
            self::CMD_MOVE => $this->move(),
            self::CMD_RESTORE => $this->restore(),
            default => parent::executeCommand()
        };
        return true;
    }

    public function getMutlipleItems(): array
    {
        $ids = $this->pons->in()->getAllFromRequest($this->top_token->token());
        if (($ids[0] ?? null) === Input::ALL_OBJECTS) {
            return array_map(
                fn(array $data): \ilMMItemFacadeInterface => $this->repository->getItemFacadeForIdentificationString($data['identification']),
                $this->repository->getTopItems()
            );
        }
        return $this->repository->getItemFacadesForIdentificationStrings($ids);
    }

    public function getCurrentItem(): ilMMItemFacadeInterface
    {
        return $this->repository->getItemFacadeForIdentificationString(
            $this->pons->in()->getFirstFromRequest($this->top_token->token())
        );
    }

    public function getTokensToKeep(): array
    {
        return [
            $this->top_token->token(),
        ];
    }

    protected function buildForm(): ilMMTopItemFormGUI
    {
        $item = $this->repository->getItemFacadeForIdentificationString(
            $this->pons->in()->getFirstFromRequest($this->top_token->token())
        );
        return new ilMMTopItemFormGUI(
            $this->flow->ctrl(),
            $this->ui->factory(),
            $this->ui->renderer(),
            $this->lng,
            $this->pons->in()->request(),
            $item,
            $this->repository
        );
    }

    #[Command('write')]
    protected function form(bool $save = false): void
    {
        if (!$save) {
            $this->pons->in()->keep($this->top_token->token());
        }

        $form = $this->buildForm();

        if ($save) {
            if ($form->save()) {
                $this->pons->out()->success($this->lng->t('item_updated'), true);
                $this->pons->flow()->redirect(self::CMD_DEFAULT);
            } else {
                $this->pons->in()->keep($this->top_token->token());
                $this->pons->out()->out($form->get());
                return;
            }
        }

        $this->pons->out()->outAsyncAsModal(
            $this->pons->i18n()->txt('edit'),
            $this->flow->getLinkTarget($this, self::CMD_UPDATE),
            $form->get()
        );
    }

    #[Command('read')]
    protected function index(): void
    {
        $write_access = $this->access->hasUserPermissionTo('write');
        if ($write_access) {
            // ADD NEW
            $form = $this->buildForm();
            $add_modal = $this->ui->factory()->modal()->roundtrip(
                $this->lng->t('topitem_add'),
                null,
                $form->get()->getInputs(),
                $this->flow->getLinkTarget($this, self::CMD_CREATE)
            );

            $btn_add = $this->ui->factory()->button()->primary(
                $this->lng->t('topitem_add'),
                $add_modal->getShowSignal()
            );
            $this->toolbar->addComponent($add_modal);
            $this->toolbar->addComponent($btn_add);

            // RESTORE
            $restoration_modal = $this->ui->factory()->modal()->interruptive(
                $this->lng->t(self::CMD_RESTORE),
                $this->lng->t('msg_restore_confirm'),
                $this->flow->getLinkTarget($this, self::CMD_RESTORE)
            )->withActionButtonLabel($this->lng->t('restore'));
            $btn_restore = $this->ui->factory()->button()->standard(
                $this->lng->t(self::CMD_RESTORE),
                ''
            )->withOnClick(
                $restoration_modal->getShowSignal()
            );
            $this->toolbar->addComponent($btn_restore);
            $this->toolbar->addComponent($restoration_modal);

            // REMOVE LOST ITEMS
            if ($this->repository->hasLostItems() && method_exists($this, self::CMD_FLUSH)) {
                $btn_flush = $this->ui->factory()->button()->standard(
                    $this->lng->txt(self::CMD_FLUSH),
                    $this->flow->getLinkTarget($this, self::CMD_FLUSH)
                );
                $this->toolbar->addComponent($btn_flush);
            }
        }

        $table = new ilMMTopItemTableComponent(
            $this->pons,
            $this->top_token,
            $this->repository,
            $write_access
        );

        $this->pons->out()->out(...$table->get());
    }

    #[Command('write')]
    private function selectParent(): string
    {
        $this->keepTokens();

        $this->pons->out()->outAsyncAsModal(
            $this->lng->txt('select_parent'),
            $this->flow->getLinkTarget($this, self::CMD_MOVE),
            $this->getMoveForm()
        );
    }

    #[Command('write')]
    private function move(): void
    {
        $data = $this->getMoveForm()
                     ->withRequest($this->pons->in()->request())
                     ->getData();

        foreach ($this->getMutlipleItems() as $item) {
            if (isset($data[0]) && $item->isInterchangeable()) {
                $f = $this->repository->getItemFacadeForIdentificationString($data[0]);
                $item->setParent($data[0]);
                $this->repository->updateItem($item);
                $this->pons->out()->success($this->lng->txt('msg_moved'), true);
            } else {
                $this->pons->out()->error($this->lng->txt('msg_not_moved'), true);
            }

            $this->pons->flow()->redirect(self::CMD_DEFAULT);
        }
    }

    private function getMoveForm(): Standard
    {
        $f = $this->ui->factory();
        $parent = $f->input()
                    ->field()
                    ->select(
                        $this->lng->txt('select_parent'),
                        $this->repository->getPossibleParentsForFormAndTable()
                    )
                    ->withRequired(true);

        return $f->input()
                 ->container()
                 ->form()
                 ->standard(
                     $this->flow->getLinkTarget($this, self::CMD_MOVE),
                     [$parent]
                 );
    }

    #[Command('write')]
    private function restore(): void
    {
        ilMMItemStorage::flushDB();
        ilMMCustomItemStorage::flushDB();
        ilMMItemTranslationStorage::flushDB();
        ilMMTypeActionStorage::flushDB();

        $this->pons->out()->success($this->lng->txt('msg_restored'), true);
        $this->flow->redirect(self::CMD_DEFAULT);
    }
}

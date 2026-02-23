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

use ILIAS\GlobalScreen\GUI\Input\TokenContainer;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\GlobalScreen\GUI\Input\Input;

/**
 * @ilCtrl_IsCalledBy ilMMSubItemGUI: ilMMTopItemGUI
 * @ilCtrl_Calls      ilMMSubItemGUI: ILIAS\GlobalScreen\GUI\I18n\MultiLanguageGUI
 *
 * @author            Fabian Schmid <fabian@sr.solutions>
 */
class ilMMSubItemGUI extends ilMMBaseGUI
{
    public const CMD_CONFIRM_MOVE = 'confirmMove';
    public const CMD_MOVE = 'MOVE';

    private TokenContainer $sub_token;
    private TokenContainer $top_token;
    private ?ilMMItemFacadeInterface $parent_item = null;

    public function __construct(Pons $pons)
    {
        parent::__construct($pons);
        $this->sub_token = $this->pons->in()->buildToken('sub', 'id');
        $this->top_token = $this->pons->in()->buildToken('top', 'id');
        $identification = $this->pons->in()->getFirstFromRequest($this->top_token->token());
        if ($identification === 'lost_items' || $identification === null) {
            $this->parent_item = null;
        } else {
            $this->parent_item = $this->repository->getItemFacadeForIdentificationString(
                $identification
            );
        }
    }

    #[\Override]
    public function executeCommand(): bool
    {
        $this->pons->in()->keep($this->top_token->token());
        if ($this->pons->handle('sub_items')) {
            return true;
        }

        match ($this->pons->flow()->getCommand(self::CMD_DEFAULT)) {
            self::CMD_CONFIRM_MOVE => $this->confirmMove(),
            self::CMD_MOVE => $this->move(),
            default => parent::executeCommand()
        };
        return true;
    }

    public function getCurrentItem(): ilMMItemFacadeInterface
    {
        return $this->repository->getItemFacadeForIdentificationString(
            $this->pons->in()->getFirstFromRequest($this->sub_token->token())
        );
    }

    public function getMutlipleItems(): array
    {
        $ids = $this->pons->in()->getAllFromRequest($this->sub_token->token());
        if (($ids[0] ?? null) === Input::ALL_OBJECTS) {
            return array_map(
                fn(array $data): \ilMMItemFacadeInterface => $this->repository->getItemFacadeForIdentificationString(
                    $data['identification']
                ),
                $this->repository->getSubItemsForTable($this->parent_item)
            );
        }
        return $this->repository->getItemFacadesForIdentificationStrings($ids);
    }

    public function getTokensToKeep(): array
    {
        return [
            $this->top_token->token(),
            $this->sub_token->token(),
        ];
    }

    protected function buildForm(): ilMMSubitemFormGUI
    {
        $item = $this->repository->getItemFacadeForIdentificationString(
            $this->pons->in()->getFirstFromRequest($this->sub_token->token())
        );
        return new ilMMSubitemFormGUI(
            $this->flow->ctrl(),
            $this->ui->factory(),
            $this->ui->renderer(),
            $this->lng,
            $this->pons->in()->request(),
            $item,
            $this->repository,
            $this->parent_item
        );
    }

    protected function form(bool $save = false): void
    {
        if (!$save) {
            $this->pons->in()->keep($this->sub_token->token());
        }

        $form = $this->buildForm();

        if ($save) {
            if ($form->save()) {
                $this->pons->out()->success($this->lng->t('item_updated'), true);
                $this->pons->flow()->redirect(self::CMD_DEFAULT);
            } else {
                $this->pons->in()->keep($this->sub_token->token());
                $this->pons->out()->out($form->get());
                return;
            }
        }

        $this->pons->out()->outAsyncAsModal(
            $this->lng->txt('subitem'),
            $this->flow->getLinkTarget($this, self::CMD_UPDATE),
            $form->get()
        );
    }

    protected function index(): void
    {
        $write_access = $this->access->hasUserPermissionTo('write');
        if ($write_access && $this->parent_item !== null) {
            // ADD NEW
            $form = $this->buildForm();
            $add_modal = $this->ui->factory()->modal()->roundtrip(
                $this->lng->t('subitem_add'),
                null,
                $form->get()->getInputs(),
                $this->flow->getLinkTarget($this, self::CMD_CREATE)
            );

            $btn_add = $this->ui->factory()->button()->primary(
                $this->lng->t('subitem_add'),
                $add_modal->getShowSignal()
            );
            $this->toolbar->addComponent($add_modal);
            $this->toolbar->addComponent($btn_add);
        }

        $table = new ilMMSubItemTableComponent(
            $this->pons,
            $this->sub_token,
            $this->repository,
            $this->parent_item,
            $write_access
        );

        $this->pons->out()->out(...$table->get());
    }

    private function confirmMove(): void
    {
        $this->keepTokens();

        $items = [];
        foreach ($this->getMutlipleItems() as $item) {
            $items[] = $this->ui->factory()->modal()->interruptiveItem()->standard(
                $this->hash($item->getId()),
                $item->getDefaultTitle(),
            );
        }

        $this->pons->out()->outAsyncAsConfirmation(
            $this->lng->t('move'),
            $this->lng->t('confirm_move'),
            $this->lng->t('move'),
            $this->flow->getLinkTarget($this, self::CMD_MOVE),
            ...$items
        );
    }

    private function move(): void
    {
        $mutliple_items = $this->getMutlipleItems();
        if (empty($mutliple_items)) {
            $this->pons->out()->error($this->lng->t('msg_not_moved'), true);
            $this->pons->flow()->redirect(self::CMD_DEFAULT);
            return;
        }
        foreach ($mutliple_items as $item) {
            $item->setParent('');
            $this->repository->updateItem($item);
        }

        $this->pons->out()->success($this->lng->t('msg_moved'), true);
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }
}

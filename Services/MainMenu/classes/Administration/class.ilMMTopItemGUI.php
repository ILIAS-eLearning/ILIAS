<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

/**
 * Class ilMMTopItemGUI
 * @ilCtrl_IsCalledBy ilMMTopItemGUI: ilObjMainMenuGUI
 * @ilCtrl_Calls      ilMMTopItemGUI: ilMMItemTranslationGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMTopItemGUI extends ilMMAbstractItemGUI
{
    use Hasher;

    const CMD_VIEW_TOP_ITEMS = 'subtab_topitems';
    const CMD_ADD = 'topitem_add';
    const CMD_RESTORE = 'restore';
    const CMD_CREATE = 'topitem_create';
    const CMD_EDIT = 'topitem_edit';
    const CMD_DELETE = 'delete';
    const CMD_CONFIRM_DELETE = 'topitem_confirm_delete';
    const CMD_TRANSLATE = 'topitem_translate';
    const CMD_UPDATE = 'topitem_update';
    const CMD_SAVE_TABLE = 'save_table';
    const CMD_CANCEL = 'cancel';
    const CMD_RENDER_INTERRUPTIVE = 'render_interruptive_modal';
    const CMD_CONFIRM_RESTORE = 'confirmRestore';
    const CMD_UPLOAD = 'upload';
    const CMD_SELECT_PARENT = 'selectParent';
    const CMD_MOVE = 'move';
    const CMD_FLUSH = 'flush';

    private function dispatchCommand($cmd)
    {
        global $DIC;
        switch ($cmd) {
            case self::CMD_VIEW_TOP_ITEMS:
                $this->access->checkAccessAndThrowException("visible,read");
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, $cmd);

                return $this->index($DIC);
            case self::CMD_ADD:
                $this->access->checkAccessAndThrowException('write');
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true, self::class);

                return $this->add($DIC);
            case self::CMD_CREATE:
                $this->access->checkAccessAndThrowException('write');
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true, self::class);

                return $this->create($DIC);
            case self::CMD_EDIT:
                $this->access->checkAccessAndThrowException('write');
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true, self::class);

                return $this->edit($DIC);
            case self::CMD_UPDATE:
                $this->access->checkAccessAndThrowException('write');
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true, self::class);

                return $this->update($DIC);
            case self::CMD_SAVE_TABLE:
                $this->access->checkAccessAndThrowException('write');
                $this->saveTable();

                break;
            case self::CMD_CONFIRM_DELETE:
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true, self::class);
                $this->access->checkAccessAndThrowException('write');

                return $this->confirmDelete();
            case self::CMD_DELETE:
                $this->access->checkAccessAndThrowException('write');
                $this->delete();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            case self::CMD_CONFIRM_RESTORE:
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true, self::class);
                $this->access->checkAccessAndThrowException('write');
                return $this->confirmRestore();
            case self::CMD_RESTORE:
                $this->access->checkAccessAndThrowException('write');

                $this->restore();
                break;
            case self::CMD_SELECT_PARENT:
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true, self::class);
                $this->access->checkAccessAndThrowException('write');
                return $this->selectParent();
            case self::CMD_FLUSH:
                $this->access->checkAccessAndThrowException('write');
                $this->flush();
                break;
            case self::CMD_UPLOAD:
                $this->access->checkAccessAndThrowException('write');
                return $this->selectParent();
            case self::CMD_MOVE:
                $this->access->checkAccessAndThrowException('write');
                $this->move();
                break;
        }

        return "";
    }

    private function saveTable() : void
    {
        global $DIC;
        $r = $DIC->http()->request()->getParsedBody();
        foreach ($r[self::IDENTIFIER] as $identification_string => $data) {
            $item = $this->repository->getItemFacadeForIdentificationString($this->unhash($identification_string));
            $item->setPosition((int) $data['position']);
            $item->setActiveStatus((bool) $data['active']);
            $this->repository->updateItem($item);
        }
        $this->cancel();
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass();

        if ($next_class === '') {
            $cmd = $this->determineCommand(self::CMD_VIEW_TOP_ITEMS, self::CMD_DELETE);
            $this->tpl->setContent($this->dispatchCommand($cmd));

            return;
        }

        switch ($next_class) {
            case strtolower(ilMMItemTranslationGUI::class):
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_TOP_ITEMS, true);
                $g = new ilMMItemTranslationGUI($this->getMMItemFromRequest(), $this->repository);
                $this->ctrl->forwardCommand($g);
                break;
            default:
                break;
        }
    }

    /**
     * @return string
     */
    private function index() : string
    {

        if ($this->access->hasUserPermissionTo('write')) {
            // ADD NEW
            $b = ilLinkButton::getInstance();
            $b->setCaption($this->lng->txt(self::CMD_ADD), false);
            $b->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD));
            $this->toolbar->addButtonInstance($b);

            // RESTORE
            $b = ilLinkButton::getInstance();
            $b->setCaption($this->lng->txt(self::CMD_RESTORE), false);
            $b->setUrl($this->ctrl->getLinkTarget($this, self::CMD_CONFIRM_RESTORE));
            $this->toolbar->addButtonInstance($b);

            // REMOVE LOST ITEMS
            if ($this->repository->hasLostItems()) {
                $b = ilLinkButton::getInstance();
                $b->setUrl($this->ctrl->getLinkTarget($this, self::CMD_FLUSH));
                $b->setCaption($this->lng->txt(self::CMD_FLUSH), false);
                $this->toolbar->addButtonInstance($b);
            }
        }

        // TABLE
        $table = new ilMMTopItemTableGUI($this, new ilMMItemRepository(), $this->access);
        $table->setShowRowsSelector(false);

        return $table->getHTML();
    }

    private function cancel()
    {
        $this->ctrl->redirectByClass(self::class, self::CMD_VIEW_TOP_ITEMS);
    }

    private function doubleCancel()
    {
        $this->ctrl->redirectByClass(self::class, self::CMD_CANCEL);
    }

    /**
     * @param $DIC
     * @return string
     * @throws Throwable
     */
    private function add(\ILIAS\DI\Container $DIC) : string
    {
        $f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $DIC->http(), $this->repository->getItemFacade(), $this->repository);

        return $f->getHTML();
    }

    /**
     * @param \ILIAS\DI\Container $DIC
     * @return string
     * @throws Throwable
     */
    private function create(\ILIAS\DI\Container $DIC) : string
    {
        $f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $DIC->http(), $this->repository->getItemFacade(), $this->repository);
        if ($f->save()) {
            $this->cancel();
        }

        return $f->getHTML();
    }

    /**
     * @param $DIC
     * @return string
     * @throws Throwable
     */
    private function edit(\ILIAS\DI\Container $DIC) : string
    {
        $f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $DIC->http(), $this->getMMItemFromRequest(), $this->repository);

        return $f->getHTML();
    }

    /**
     * @param \ILIAS\DI\Container $DIC
     * @return string
     * @throws Throwable
     */
    private function update(\ILIAS\DI\Container $DIC) : string
    {
        $item = $this->getMMItemFromRequest();
        if ($item->isEditable()) {
            $f = new ilMMTopItemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $DIC->http(), $item, $this->repository);
            if ($f->save()) {
                $this->cancel();
            }

            return $f->getHTML();
        }

        return "";
    }

    private function delete() : void
    {
        $item = $this->getMMItemFromRequest();
        if ($item->isDeletable()) {
            $this->repository->deleteItem($item);
        }
        ilUtil::sendSuccess($this->lng->txt("msg_topitem_deleted"), true);
        $this->cancel();
    }

    /**
     * @return string
     * @throws Throwable
     */
    private function confirmDelete() : string
    {
        $this->ctrl->saveParameterByClass(self::class, self::IDENTIFIER);
        $i = $this->getMMItemFromRequest();
        $c = new ilConfirmationGUI();
        $c->addItem(self::IDENTIFIER, $this->hash($i->getId()), $i->getDefaultTitle());
        $c->setFormAction($this->ctrl->getFormActionByClass(self::class));
        $c->setConfirm($this->lng->txt(self::CMD_DELETE), self::CMD_DELETE);
        $c->setCancel($this->lng->txt(self::CMD_CANCEL), self::CMD_CANCEL);
        $c->setHeaderText($this->lng->txt(self::CMD_CONFIRM_DELETE));

        return $c->getHTML();
    }

    private function confirmRestore() : string
    {
        $c = new ilConfirmationGUI();
        $c->setFormAction($this->ctrl->getFormActionByClass(self::class));
        $c->setConfirm($this->lng->txt(self::CMD_DELETE), self::CMD_RESTORE);
        $c->setCancel($this->lng->txt(self::CMD_CANCEL), self::CMD_CANCEL);
        $c->setHeaderText($this->lng->txt('msg_restore_confirm'));

        return $c->getHTML();
    }

    private function flush() : void
    {
        $this->repository->flushLostItems();
        ilUtil::sendSuccess($this->lng->txt("msg_subitem_flushed"), true);
        $this->cancel();
    }

    private function restore() : void
    {
        ilMMItemStorage::flushDB();
        ilMMCustomItemStorage::flushDB();
        ilMMItemTranslationStorage::flushDB();
        ilMMTypeActionStorage::flushDB();

        ilUtil::sendSuccess($this->lng->txt('msg_restored'), true);

        $this->cancel();
    }

    private function selectParent() : string
    {
        $form = $this->getMoveForm();

        return $this->ui->renderer()->render($form);
    }

    private function move() : void
    {
        $form = $this->getMoveForm();
        $form = $form->withRequest($this->http->request());

        $item = $this->getMMItemFromRequest();

        $data = $form->getData();
        if ($item->isInterchangeable() && isset($data[0])) {
            $f = $this->repository->getItemFacadeForIdentificationString($data[0]);
            $item->setParent($data[0]);
            $this->repository->updateItem($item);
            ilUtil::sendSuccess($this->lng->txt('msg_moved'), true);
        } else {
            ilUtil::sendFailure($this->lng->txt('msg_not_moved'), true);
        }

        $this->cancel();
    }

    /**
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    private function getMoveForm() : \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $this->ctrl->saveParameter($this, self::IDENTIFIER);
        $f = $this->ui->factory();

        $parent = $f->input()->field()->select($this->lng->txt('select_parent'), $this->repository->getPossibleParentsForFormAndTable())->withRequired(true);

        return $f->input()->container()->form()->standard($this->ctrl->getFormAction($this, self::CMD_MOVE), [$parent]);
    }
}

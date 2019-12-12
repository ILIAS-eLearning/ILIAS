<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

/**
 * Class ilMMTopItemGUI
 *
 * @ilCtrl_IsCalledBy ilMMSubItemGUI: ilObjMainMenuGUI
 * @ilCtrl_Calls      ilMMSubItemGUI: ilMMItemTranslationGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMSubItemGUI extends ilMMAbstractItemGUI
{
    use Hasher;
    const CMD_VIEW_SUB_ITEMS = 'subtab_subitems';
    const CMD_ADD = 'subitem_add';
    const CMD_CREATE = 'subitem_create';
    const CMD_DELETE = 'subitem_delete';
    const CMD_CONFIRM_DELETE = 'subitem_confirm_delete';
    const CMD_EDIT = 'subitem_edit';
    const CMD_TRANSLATE = 'subitem_translate';
    const CMD_UPDATE = 'subitem_update';
    const CMD_SAVE_TABLE = 'save_table';
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_RESET_FILTER = 'resetFilter';
    const CMD_RENDER_INTERRUPTIVE = 'render_interruptive_modal';
    const CMD_CANCEL = 'cancel';


    private function dispatchCommand($cmd)
    {
        global $DIC;
        switch ($cmd) {
            case self::CMD_ADD:
                $this->access->checkAccessAndThrowException('write');
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true, self::class);

                return $this->add($DIC);
            case self::CMD_CREATE:
                $this->access->checkAccessAndThrowException('write');
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true, self::class);

                return $this->create($DIC);
            case self::CMD_EDIT:
                $this->access->checkAccessAndThrowException('write');
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true, self::class);

                return $this->edit($DIC);
            case self::CMD_UPDATE:
                $this->access->checkAccessAndThrowException('write');
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, ilMMSubItemGUI::CMD_VIEW_SUB_ITEMS, true, self::class);

                return $this->update($DIC);
            case self::CMD_APPLY_FILTER:
                $this->access->checkAccessAndThrowException('visible,read');
                $this->applyFilter();
                break;
            case self::CMD_RESET_FILTER:
                $this->access->checkAccessAndThrowException('visible,read');
                $this->resetFilter();
                break;
            case self::CMD_VIEW_SUB_ITEMS:
                $this->access->checkAccessAndThrowException('visible,read');
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, $cmd);

                return $this->index();
            case self::CMD_SAVE_TABLE:
                $this->access->checkAccessAndThrowException('write');
                $this->saveTable();
                break;
            case self::CMD_CONFIRM_DELETE:
                $this->access->checkAccessAndThrowException('write');

                return $this->confirmDelete();
                break;
            case self::CMD_DELETE:
                $this->access->checkAccessAndThrowException('write');
                $this->delete();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            case self::CMD_RENDER_INTERRUPTIVE:
                $this->access->checkAccessAndThrowException('write');
                $this->renderInterruptiveModal();
                break;
        }

        return "";
    }


    private function saveTable()
    {
        global $DIC;
        $r = $DIC->http()->request()->getParsedBody();
        foreach ($r[self::IDENTIFIER] as $identification_string => $data) {
            $item = $this->repository->getItemFacadeForIdentificationString($this->unhash($identification_string));
            $position = (int) $data['position'];
            $item->setPosition($position);
            $item->setActiveStatus((bool) $data['active']);
            $item->setParent($this->unhash((string) $data['parent']));
            $this->repository->updateItem($item);
        }
        $this->cancel();
    }


    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();

        if ($next_class == '') {
            $cmd = $this->determineCommand(self::CMD_VIEW_SUB_ITEMS, self::CMD_DELETE);
            $this->tpl->setContent($this->dispatchCommand($cmd));

            return;
        }

        switch ($next_class) {
            case strtolower(ilMMItemTranslationGUI::class):
                $this->tab_handling->initTabs(ilObjMainMenuGUI::TAB_MAIN, self::CMD_VIEW_SUB_ITEMS, true, $this->ctrl->getCallHistory()[2]['class'] ? $this->ctrl->getCallHistory()[2]['class'] : "");
                $g = new ilMMItemTranslationGUI($this->getMMItemFromRequest(), $this->repository);
                $this->ctrl->forwardCommand($g);
                break;
            default:
                break;
        }
    }


    /**
     * @param $DIC
     *
     * @return string
     * @throws Throwable
     */
    private function add($DIC) : string
    {
        $f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->repository->getItemFacade(), $this->repository);

        return $f->getHTML();
    }


    /**
     * @param $DIC
     *
     * @return string
     * @throws Throwable
     */
    private function create($DIC)
    {
        $f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->repository->getItemFacade(), $this->repository);
        if ($f->save()) {
            $this->cancel();
        }

        return $f->getHTML();
    }


    /**
     * @param $DIC
     *
     * @return string
     * @throws Throwable
     */
    private function edit($DIC) : string
    {
        $f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->getMMItemFromRequest(), $this->repository);

        return $f->getHTML();
    }


    /**
     * @param $DIC
     *
     * @return string
     * @throws Throwable
     */
    private function update($DIC)
    {
        $f = new ilMMSubitemFormGUI($DIC->ctrl(), $DIC->ui()->factory(), $DIC->ui()->renderer(), $this->lng, $this->getMMItemFromRequest(), $this->repository);
        if ($f->save()) {
            $this->cancel();
        }

        return $f->getHTML();
    }


    private function applyFilter()
    {
        $table = new ilMMSubItemTableGUI($this, $this->repository, $this->access);
        $table->writeFilterToSession();

        $this->cancel();
    }


    private function resetFilter()
    {
        $table = new ilMMSubItemTableGUI($this, $this->repository, $this->access);
        $table->resetFilter();
        $table->resetOffset();

        $this->cancel();
    }


    /**
     * @return string
     */
    private function index() : string
    {
        // ADD NEW
        if ($this->access->hasUserPermissionTo('write')) {
            $b = ilLinkButton::getInstance();
            $b->setUrl($this->ctrl->getLinkTarget($this, ilMMSubItemGUI::CMD_ADD));
            $b->setCaption($this->lng->txt(ilMMSubItemGUI::CMD_ADD), false);

            $this->toolbar->addButtonInstance($b);
        }

        // TABLE
        $table = new ilMMSubItemTableGUI($this, $this->repository, $this->access);
        $table->setShowRowsSelector(false);

        return $table->getHTML();
    }


    private function delete()
    {
        $item = $this->getMMItemFromRequest();
        if ($item->isCustom()) {
            $this->repository->deleteItem($item);
        }

        ilUtil::sendSuccess($this->lng->txt("msg_subitem_deleted"), true);
        $this->cancel();
    }


    private function cancel()
    {
        $this->ctrl->redirectByClass(self::class, self::CMD_VIEW_SUB_ITEMS);
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
}

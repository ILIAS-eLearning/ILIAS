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
 ********************************************************************
 */

namespace ILIAS\Modules\OrgUnit\ARHelper;

/**
 * Class BaseForm
 * @package ILIAS\Modules\OrgUnit\CtrlHelper
 */
abstract class BaseForm extends \ilPropertyFormGUI
{
    protected BaseCommands $parent_gui;
    protected \ILIAS\DI\Container $DIC;
    protected \ActiveRecord $object;
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;

    /**
     * @throws \ilCtrlException
     */
    public function __construct(BaseCommands $parent_gui, \ActiveRecord $object)
    {
        global $DIC;

        $this->parent_gui = $parent_gui;
        $this->object = $object;
        $this->lng = $DIC->language();

        $this->ctrl = $DIC->ctrl();
        $this->ctrl->saveParameter($parent_gui, 'arid');
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->initFormElements();
        $this->initButtons();
        $this->setTarget('_top');
        parent::__construct();
    }

    abstract protected function initFormElements() : void;

    abstract public function fillForm() : void;

    abstract protected function fillObject() : bool;

    public function saveObject() : bool
    {
        if ($this->fillObject() === false) {
            return false;
        }
        if ($this->object->getId()) {
            $this->object->update();
        } else {
            $this->object->create();
        }

        return $this->object->getId();
    }

    private function initButtons() : void
    {
        if (!$this->object->getId()) {
            $this->setTitle($this->txt('create'));
            $this->addCommandButton(BaseCommands::CMD_CREATE, $this->txt(BaseCommands::CMD_CREATE));
            $this->addCommandButton(BaseCommands::CMD_CANCEL, $this->txt(BaseCommands::CMD_CANCEL));
        } else {
            $this->setTitle($this->txt('update'));
            $this->addCommandButton(BaseCommands::CMD_UPDATE, $this->txt(BaseCommands::CMD_UPDATE));
            $this->addCommandButton(BaseCommands::CMD_CANCEL, $this->txt(BaseCommands::CMD_CANCEL));
        }
    }


    private function txt(string $key) : string
    {
        return $this->lng->txt($key);
    }

    private function infoTxt(string $key): string
    {
        return $this->lng->txt($key . '_info');
    }

}

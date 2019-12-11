<?php

namespace ILIAS\Modules\OrgUnit\ARHelper;

/**
 * Class BaseForm
 *
 * @package ILIAS\Modules\OrgUnit\CtrlHelper
 */
abstract class BaseForm extends \ilPropertyFormGUI
{

    /**
     * @var \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands
     */
    protected $parent_gui;
    /**
     * @var \ILIAS\DI\Container
     */
    protected $DIC;
    /**
     * @var \ActiveRecord
     */
    protected $object;


    /**
     * BaseForm constructor.
     *
     * @param \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands $parent_gui
     * @param \ActiveRecord                                $object
     */
    public function __construct(BaseCommands $parent_gui, \ActiveRecord $object)
    {
        $this->parent_gui = $parent_gui;
        $this->object = $object;
        $this->dic()->ctrl()->saveParameter($parent_gui, 'arid');
        $this->setFormAction($this->dic()->ctrl()->getFormAction($this->parent_gui));
        $this->initFormElements();
        $this->initButtons();
        $this->setTarget('_top');
        parent::__construct();
    }


    abstract protected function initFormElements();


    abstract public function fillForm();


    abstract protected function fillObject();


    /**
     * @return int ID of the object
     */
    public function saveObject()
    {
        if (!$this->fillObject()) {
            return false;
        }
        if ($this->object->getId()) {
            $this->object->update();
        } else {
            $this->object->create();
        }

        return $this->object->getId();
    }


    protected function initButtons()
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


    /**
     * @param $key
     *
     * @return string
     */
    protected function txt($key)
    {
        return $this->parent_gui->txt($key);
    }


    /**
     * @param $key
     *
     * @return string
     */
    protected function infoTxt($key)
    {
        return $this->parent_gui->txt($key . '_info');
    }


    /**
     * @return \ILIAS\DI\Container
     */
    protected function dic()
    {
        return $GLOBALS["DIC"];
    }
}

<?php declare(strict_types=1);
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author jposselt@databay.de
 */
abstract class ilChatroomObjectGUI extends ilObjectGUI
{
    protected \ILIAS\HTTP\Services $httpServices;
    protected ilTree $repositoryTree;

    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
    {
        /** @var $DIC \ILIAS\DI\Container */
        global $DIC;

        $this->httpServices = $DIC->http();
        $this->repositoryTree = $DIC->repositoryTree();

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
    }


    /**
     * @param string $gui
     * @param string $method
     * @return bool A boolean flag whether or not the request could be dispatched
     */
    protected function dispatchCall(string $gui, string $method) : bool
    {
        $definition = $this->getObjectDefinition();
        if ($definition->hasGUI($gui)) {
            $definition->loadGUI($gui);
            $guiHandler = $definition->buildGUI($gui, $this);
            $guiHandler->execute($method);
            return true;
        }

        return false;
    }

    abstract protected function getObjectDefinition() : ilChatroomObjectDefinition;

    abstract public function getConnector() : ilChatroomServerConnector;

    /**
     * Calls $this->prepareOutput() method.
     */
    public function switchToVisibleMode() : void
    {
        $this->prepareOutput();
    }

    public function getAdminTabs()
    {
        if (
            isset($this->httpServices->request()->getQueryParams()['admin_mode']) &&
            $this->httpServices->request()->getQueryParams()['admin_mode'] === 'repository'
        ) {
            $this->ctrl->setParameterByClass(ilAdministrationGUI::class, 'admin_mode', 'settings');
            $this->tabs_gui->setBackTarget(
                $this->lng->txt('administration'),
                $this->ctrl->getLinkTargetByClass(ilAdministrationGUI::class, 'frameset')
            );
            $this->ctrl->setParameterByClass(ilAdministrationGUI::class, 'admin_mode', 'repository');
        }

        if ($this->repositoryTree->getSavedNodeData($this->object->getRefId())) {
            $this->tabs_gui->addTarget('trash', $this->ctrl->getLinkTarget($this, 'trash'), 'trash', get_class($this));
        }
    }
}

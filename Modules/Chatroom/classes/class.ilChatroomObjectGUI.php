<?php

declare(strict_types=1);

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

use ILIAS\HTTP\GlobalHttpState;

/**
 * @author jposselt@databay.de
 */
abstract class ilChatroomObjectGUI extends ilObjectGUI
{
    protected GlobalHttpState $http;

    public function __construct($data, ?int $id = 0, bool $call_by_reference = true, bool $prepare_output = true)
    {
        /** @var $DIC \ILIAS\DI\Container */
        global $DIC;

        $this->http = $DIC->http();

        parent::__construct($data, $id, $call_by_reference, $prepare_output);
    }


    /**
     * @param string $gui
     * @param string $method
     * @return bool A boolean flag whether or not the request could be dispatched
     */
    protected function dispatchCall(string $gui, string $method): bool
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

    abstract protected function getObjectDefinition(): ilChatroomObjectDefinition;

    abstract public function getConnector(): ilChatroomServerConnector;

    /**
     * Calls $this->prepareOutput() method.
     */
    public function switchToVisibleMode(): void
    {
        $this->prepareOutput();
    }

    public function getAdminTabs(): void
    {
        if (
            $this->http->wrapper()->query()->has('admin_mode') &&
            $this->http->wrapper()->query()->retrieve(
                'admin_mode',
                $this->refinery->kindlyTo()->string()
            ) === 'repository'
        ) {
            $this->ctrl->setParameterByClass(ilAdministrationGUI::class, 'admin_mode', 'settings');
            $this->tabs_gui->setBackTarget(
                $this->lng->txt('administration'),
                $this->ctrl->getLinkTargetByClass(ilAdministrationGUI::class, 'frameset')
            );
            $this->ctrl->setParameterByClass(ilAdministrationGUI::class, 'admin_mode', 'repository');
        }

        if ($this->tree->getSavedNodeData($this->object->getRefId())) {
            $this->tabs_gui->addTarget('trash', $this->ctrl->getLinkTarget($this, 'trash'), 'trash', get_class($this));
        }
    }
}

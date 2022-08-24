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

/**
 * ListGUI implementation for Example object plugin. This one
 * handles the presentation in container items (categories, courses, ...)
 * together with the ...Access class.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilObjectPluginListGUI extends ilObjectListGUI
{
    protected ilComponentFactory $component_factory;
    protected ?ilObjectPlugin $plugin;

    public function __construct(int $a_context = self::CONTEXT_REPOSITORY)
    {
        global $DIC;

        $this->component_factory = $DIC["component.factory"];

        parent::__construct($a_context);

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
    }

    final public function init(): void
    {
        $this->initListActions();
        $this->initType();
        $this->plugin = $this->getPlugin();
        $this->gui_class_name = $this->getGuiClass();
        $this->commands = $this->initCommands();
    }

    abstract public function getGuiClass(): string;
    abstract public function initCommands(): array;

    public function setType(string $a_val): void
    {
        $this->type = $a_val;
    }

    /**
     * @return ilObjectPlugin|null
     */
    protected function getPlugin(): ?ilObjectPlugin
    {
        if (!$this->plugin) {
            $this->plugin = $this->component_factory->getPlugin($this->getType());
        }
        return $this->plugin;
    }

    public function getType(): string
    {
        return $this->type;
    }

    abstract public function initType();

    public function txt(string $a_str): string
    {
        return $this->plugin->txt($a_str);
    }

    public function getCommandFrame(string $cmd): string
    {
        return ilFrameTargetInfo::_getFrame("MainContent");
    }

    public function getProperties(): array
    {
        return [];
    }

    public function getCommandLink(string $cmd): string
    {

        // separate method for this line
        $cmd_link = "ilias.php?baseClass=ilObjPluginDispatchGUI&amp;" .
            "cmd=forward&amp;ref_id=" . $this->ref_id . "&amp;forwardCmd=" . $cmd;

        return $cmd_link;
    }

    protected function initListActions(): void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
    }
}

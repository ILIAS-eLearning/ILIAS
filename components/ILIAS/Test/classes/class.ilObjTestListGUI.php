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

use ILIAS\Test\Access\ParticipantAccess;
use ILIAS\Test\TestDIC;

/**
 * Class ilObjTestListGUI
 *
 * @author		Helmut Schottmueller <helmut.schottmueller@mac.com>
 * @author		Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 * @extends ilObjectListGUI
 * @ingroup components\ILIASTest
 */

class ilObjTestListGUI extends ilObjectListGUI
{
    protected $command_link_params = [];
    private ilTestAccess $test_access;

    /**
    * initialisation
    */
    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "tst";
        $this->gui_class_name = "ilobjtestgui";

        // general commands array
        $this->commands = ilObjTestAccess::_getCommands();
    }

    public function initItem(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title = '',
        string $description = ''
    ): void {
        try {
            if (TestDIC::dic()['settings.main.repository']->getForObjFi($obj_id)
                ->getAdditionalSettings()->getHideInfoTab()) {
                $this->enableInfoScreen(false);
            }
        } catch (Exception $e) {

        }
        $this->test_access = new ilTestAccess($ref_id);
        parent::initItem($ref_id, $obj_id, $type, $title, $description);
    }

    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties(): array
    {
        $props = parent::getProperties();

        $participant_access = $this->test_access->isParticipantAllowed(
            $this->obj_id,
            $this->user->getId()
        );

        if ($participant_access === ParticipantAccess::ALLOWED) {
            return $props;
        }

        $props[] = ['alert' => true, 'property' => $this->lng->txt('status'),
            'value' => $participant_access->getAccessForbiddenMessage($this->lng)];

        return $props;
    }

    public function getCommandLink(string $cmd): string
    {
        $cmd = explode('::', $cmd);

        if (count($cmd) === 2) {
            $cmd_link = $this->ctrl->getLinkTargetByClass([ilRepositoryGUI::class, ilObjTestGUI::class, $cmd[0]], $cmd[1]);
        } else {
            $cmd_link = $this->ctrl->getLinkTargetByClass('ilObjTestGUI', $cmd[0]);
        }

        $params = array_merge(['ref_id' => $this->ref_id], $this->command_link_params);

        foreach ($params as $param => $value) {
            $cmd_link = ilUtil::appendUrlParameterString($cmd_link, "$param=$value", true);
        }

        return $cmd_link;
    }

    public function getCommands(): array
    {
        $commands = parent::getCommands();
        if ($this->access->checkAccess('read', '', $this->ref_id)) {
            $this->insertCommand($this->getCommandLink('testScreen'), $this->lng->txt('tst_start_test'));
        }
        return $commands;
    }

    /**
     * overwritten from base class for course objectives
     *
     * @access public
     * @param
     * @return
     */
    public function createDefaultCommand(array $command): array
    {
        return $command;
    }


    /**
     * add command link parameters
     *
     * @access public
     * @param array (param => value)
     */
    public function addCommandLinkParameter($a_param)
    {
        $this->command_link_params = $a_param;
    }

    // begin-patch lok
    protected function modifyTitleLink(string $default_link): string
    {
        if (!ilLOSettings::isObjectiveTest($this->ref_id)) {
            return parent::modifyTitleLink($default_link);
        }

        $path = $this->tree->getPathFull($this->ref_id);

        while ($parent = array_pop($path)) {
            if ($parent['type'] === 'crs') {
                $parent_crs_ref_id = $parent['ref_id'];
                break;
            }
        }

        $this->ctrl->setParameterByClass("ilrepositorygui", 'ref_id', $parent_crs_ref_id);
        $this->ctrl->setParameterByClass("ilrepositorygui", 'tid', $this->ref_id);
        $cmd_link = $this->ctrl->getLinkTargetByClass("ilrepositorygui", 'redirectLocToTest');
        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
        $this->ctrl->clearParametersByClass('ilrepositorygui');

        return parent::modifyTitleLink($cmd_link);
    }

    // end-patch lok
} // END class.ilObjTestListGUI

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
 */

declare(strict_types=1);

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ModulesRemoteTest
*/
class ilObjRemoteTestListGUI extends ilRemoteObjectBaseListGUI
{
    /**
     * Constructor
     *
     * @access public
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * init
     *
     * @access public
     */
    public function init(): void
    {
        $this->copy_enabled = false;
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = 'rtst';
        $this->gui_class_name = 'ilobjremotetestgui';

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }

        // general commands array
        $this->commands = ilObjRemoteTestAccess::_getCommands();
    }


    /**
     * get properties (offline)
     *
     * @access public
     * @param
     *
     */
    public function getProperties(): array
    {
        if ($org = $this->_lookupOrganization(ilObjRemoteTest::DB_TABLE_NAME, $this->obj_id)) {
            $this->addCustomProperty($this->lng->txt('organization'), $org, false, true);
        }
        if (!ilObjRemoteTest::_lookupOnline($this->obj_id)) {
            $this->addCustomProperty($this->lng->txt("status"), $this->lng->txt("offline"), true, true);
        }

        return array();
    }

    /**
     * get command frame
     *
     * @access public
     * @param
     * @return
     */
    public function getCommandFrame(string $cmd): string
    {
        switch ($cmd) {
            case 'show':
                if (ilECSExportManager::_isRemote(
                    ilECSImport::lookupServerId($this->obj_id),
                    ilECSImport::_lookupEContentId($this->obj_id)
                )) {
                    return '_blank';
                }

                // no break
            default:
                return parent::getCommandFrame($cmd);
        }
    }
} // END class.ilObjRemoteTestListGUI

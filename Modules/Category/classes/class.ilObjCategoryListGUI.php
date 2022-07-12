<?php declare(strict_types=1);

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

use ILIAS\Category\StandardGUIRequest;

/**
 * Class ilObjCategoryListGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjCategoryListGUI extends ilObjectListGUI
{
    protected StandardGUIRequest $cat_request;

    /**
     * Constructor
     */
    public function __construct(int $a_context = self::CONTEXT_REPOSITORY)
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        parent::__construct($a_context);
        $this->ctrl = $DIC->ctrl();

        $this->cat_request = $DIC
            ->category()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    /**
    * initialisation
    */
    public function init() : void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;

        $this->type = "cat";
        $this->gui_class_name = "ilobjcategorygui";

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }

        // general commands array
        $this->commands = ilObjCategoryAccess::_getCommands();
    }

    public function getInfoScreenStatus() : bool
    {
        if (ilContainer::_lookupContainerSetting(
            $this->obj_id,
            ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
            '1'
        )) {
            return $this->info_screen_enabled;
        }

        return false;
    }

    public function getCommandLink(string $cmd) : string
    {
        $ilCtrl = $this->ctrl;

        $cmd_link = "";

        // BEGIN WebDAV
        switch ($cmd) {
            case 'mount_webfolder':
                if (ilDAVActivationChecker::_isActive()) {
                    global $DIC;
                    $uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
                    return $uri_builder->getUriToMountInstructionModalByRef($this->ref_id);
                }
                break;
            default:
                // separate method for this line
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $ilCtrl->setParameterByClass(
                    "ilrepositorygui",
                    "ref_id",
                    $this->cat_request->getRefId()
                );
                break;
        }
        // END WebDAV

        return $cmd_link;
    }

    public function checkInfoPageOnAsynchronousRendering() : bool
    {
        return true;
    }
}

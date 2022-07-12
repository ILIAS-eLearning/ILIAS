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
 
class ilObjLearningSequenceListGUI extends ilObjectListGUI
{
    public function __construct()
    {
        parent::__construct();

        $dic = $this->getDIC();
        $obj_type = ilObjLearningSequence::OBJ_TYPE;
        $this->lng = $dic->language();
        $this->lng->loadLanguageModule($obj_type);
    }

    protected function getDIC() : ILIAS\DI\Container
    {
        global $DIC;
        return $DIC;
    }

    public function init() : void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->info_screen_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = true;
        $this->gui_class_name = "ilobjlearningsequencegui";
        $this->type = ilObjLearningSequence::OBJ_TYPE;
        $this->commands = ilObjLearningSequenceAccess::_getCommands();
    }

    public function getProperties() : array
    {
        $props = parent::getProperties();

        if (ilObjLearningSequenceAccess::isOffline($this->ref_id)) {
            $props[] = [
                "alert" => true,
                "property" => $this->lng->txt("status"),
                "value" => $this->lng->txt("offline")
            ];
        }

        return $props;
    }
}

<?php

declare(strict_types=1);

/**
 * Class ilObjLearningSequenceListGUI
 */
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

    public function init()
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

    public function getProperties()
    {
        $props = parent::getProperties();

        if (ilObjLearningSequenceAccess::isOffline($this->ref_id)) {
            $props[] = array("alert" => true, "property" => $this->lng->txt("status"),
                "value" => $this->lng->txt("offline"));
        }

        return $props;
    }
}

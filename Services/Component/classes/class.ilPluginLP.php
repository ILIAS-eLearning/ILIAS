<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * Plugin to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ServicesComponent
 */
class ilPluginLP extends ilObjectLP
{
    protected $status; // [mixed]

    public const INACTIVE_PLUGIN = -1;

    protected function __construct($a_obj_id)
    {
        parent::__construct($a_obj_id);

        $this->initPlugin();
    }

    protected function initPlugin(): void
    {
        // active plugin?
        if (ilRepositoryObjectPluginSlot::isTypePluginWithLP(ilObject::_lookupType($this->obj_id))) {
            $obj = ilObjectFactory::getInstanceByObjId($this->obj_id, false); // #12640
            if ($obj instanceof ilLPStatusPluginInterface) {
                $this->status = $obj;
            }
        }
        // inactive plugin?
        elseif (ilRepositoryObjectPluginSlot::isTypePluginWithLP(ilObject::_lookupType($this->obj_id), false)) {
            $this->status = self::INACTIVE_PLUGIN;
        }
    }

    public function getPluginInstance()
    {
        return $this->status;
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_UNDEFINED;
    }

    public function getValidModes(): array
    {
        return array(
            ilLPObjSettings::LP_MODE_UNDEFINED,
            ilLPObjSettings::LP_MODE_PLUGIN
        );
    }

    public function getCurrentMode(): int
    {
        if ($this->status !== null) {
            return ilLPObjSettings::LP_MODE_PLUGIN;
        }
        return ilLPObjSettings::LP_MODE_UNDEFINED;
    }

    protected static function isLPMember(array &$res, int $usr_id, array $obj_ids): bool
    {
        global $DIC;
        $objDefinition = $DIC['objDefinition'];

        $type = $obj_ids;
        $type = array_shift($type);
        $type = ilObject::_lookupType($type);

        $location = $objDefinition->getLocation($type);
        $class_name = "ilObj" . $objDefinition->getClassName($type);
        include_once $location . "/class." . $class_name . ".php";

        // forward to plugin object
        if (method_exists($class_name, "isLPMember")) {
            return $class_name::isLPMember($res, $usr_id, $obj_ids);
        }

        return false;
    }
}

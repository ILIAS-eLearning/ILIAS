<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Save container block property
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilContainerBlockPropertiesStorage:
 */
class ilContainerBlockPropertiesStorage
{
    protected ilCtrl $ctrl;
    protected ilObjUser $user;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
    }

    public function executeCommand() : void
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd();
        if (in_array($cmd, array("store"))) {
            $this->$cmd();
        }
    }
    
    public function store() : void
    {
        $ilUser = $this->user;

        switch ($_GET["act"]) {

            case "expand":
                self::storeProperty($_GET["cont_block_id"], (int) $ilUser->getId(), "opened", "1");
                break;

            case "collapse":
                self::storeProperty($_GET["cont_block_id"], (int) $ilUser->getId(), "opened", "0");
                break;
        }
    }
    
    public static function storeProperty(
        string $a_block_id,
        int $a_user_id,
        string $a_property,
        string $a_value
    ) : void {
        $_SESSION["cont_block"][$a_block_id][$a_user_id][$a_property] = $a_value;
        ilLoggerFactory::getLogger("cont")
            ->debug("block id: " . $a_block_id . ", user id: " . $a_user_id . ", property: " . $a_property . ", val: " . print_r($a_value, true));
    }
    
    public static function getProperty(
        string $a_block_id,
        int $a_user_id,
        string $a_property
    ) : string {
        $val = false;
        if (isset($_SESSION["cont_block"][$a_block_id][$a_user_id][$a_property])) {
            $val = $_SESSION["cont_block"][$a_block_id][$a_user_id][$a_property];
        }
        ilLoggerFactory::getLogger("cont")
            ->debug("block id: " . $a_block_id . ", user id: " . $a_user_id . ", property: " . $a_property . ", val: " . print_r($val, true));
        return $val;
    }
}

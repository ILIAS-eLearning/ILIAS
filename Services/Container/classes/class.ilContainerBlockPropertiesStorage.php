<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Save container block property
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup ServicesContainer
 * @ilCtrl_Calls ilContainerBlockPropertiesStorage:
 */
class ilContainerBlockPropertiesStorage
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
    }

    /**
    * execute command
    */
    public function &executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd();
        if (in_array($cmd, array("store"))) {
            $this->$cmd();
        }
    }
    
    /**
     * Store property
     */
    public function store()
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
    
    /**
     * Store property in session or db
     *
     * @param string $a_block_id
     * @param int $a_user_id
     * @param string $a_property
     * @param string $a_value
     */
    public static function storeProperty($a_block_id, $a_user_id, $a_property, $a_value)
    {
        $_SESSION["cont_block"][$a_block_id][$a_user_id][$a_property] = $a_value;
        ilLoggerFactory::getLogger("cont")
            ->debug("block id: " . $a_block_id . ", user id: " . $a_user_id . ", property: " . $a_property . ", val: " . print_r($a_value, true));
    }
    
    /**
     * Get property in session or db
     *
     * @param string $a_block_id
     * @param int $a_user_id
     * @param string $a_property
     * @return bool|string
     */
    public static function getProperty($a_block_id, $a_user_id, $a_property)
    {
        $val = false;
        if (isset($_SESSION["cont_block"][$a_block_id][$a_user_id][$a_property])) {
            $val = $_SESSION["cont_block"][$a_block_id][$a_user_id][$a_property];
        }
        ilLoggerFactory::getLogger("cont")
            ->debug("block id: " . $a_block_id . ", user id: " . $a_user_id . ", property: " . $a_property . ", val: " . print_r($val, true));
        return $val;
    }
}

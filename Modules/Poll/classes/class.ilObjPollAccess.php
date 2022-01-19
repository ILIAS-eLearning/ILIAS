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
 ********************************************************************
 */

/**
 * Class ilObjPollAccess
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjPollAccess extends ilObjectAccess implements ilWACCheckingClass
{
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacsystem;
    protected ilAccessHandler $access;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
    }

    /**
    * @inheritdoc
    */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") : bool
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }

        if (
            $a_cmd == 'preview' &&
            $a_permission == 'read'
        ) {
            return false;
        }
        
        return true;
    }

    public static function _isActivated(int $a_ref_id) : bool
    {
        $item = ilObjectActivation::getItem($a_ref_id);
        switch ($item['timing_type']) {
            case ilObjectActivation::TIMINGS_ACTIVATION:
                if (time() < $item['timing_start'] or
                   time() > $item['timing_end']) {
                    return false;
                }
                // fallthrough
                
                // no break
            default:
                return true;
        }
    }
    
    /**
     * @inheritdoc
     */
    public static function _getCommands() : array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "preview", "lang_var" => "show", "default" => true),
            array("permission" => "write", "cmd" => "render", "lang_var" => "edit")
        );
        
        return $commands;
    }
    
    /**
    * @inheritdoc
    */
    public static function _checkGoto($a_target) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);
        
        if ($t_arr[0] != "poll" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }


    /**
     * @inheritdoc
     */
    public function canBeDelivered(ilWACPath $ilWACPath) : bool
    {
        $ilAccess = $this->access;
        preg_match("/\\/poll_([\\d]*)\\//uism", $ilWACPath->getPath(), $results);

        foreach (ilObject2::_getAllReferences($results[1]) as $ref_id) {
            if ($ilAccess->checkAccess('read', '', (int) $ref_id)) {
                return true;
            }
        }

        return false;
    }
}

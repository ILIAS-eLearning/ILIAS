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
 
/**
 * Class ilObjLinkResourceAccess
 * @author        Alex Killing <alex.killing@gmx.de>
 * @ingroup       ModulesWebResource
 */
class ilObjLinkResourceAccess extends ilObjectAccess
{
    public static array $item = [];
    public static array $single_link = [];

    /**
     * @inheritDoc
     */
    public static function _getCommands() : array
    {
        return array(
            array("permission" => "read",
                  "cmd" => "",
                  "lang_var" => "show",
                  "default" => true
            ),
            array("permission" => "read",
                  "cmd" => "exportHTML",
                  "lang_var" => "export_html"
            ),
            array("permission" => "write",
                  "cmd" => "editLinks",
                  "lang_var" => "edit_content"
            ),
            array("permission" => "write",
                  "cmd" => "settings",
                  "lang_var" => "settings"
            )
        );
    }

    public static function _checkGoto(string $target) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);
        $type = $t_arr[0] ?? '';
        $ref_id = (int) ($t_arr[1] ?? 0);

        if ($type !== 'webr' || $ref_id <= 0) {
            return false;
        }
        return $ilAccess->checkAccess('read', '', $ref_id) || $ilAccess->checkAccess('visible', '', $ref_id);
    }

    /**
     * @inheritDoc
     */
    public function _checkAccess(
        string $cmd,
        string $permission,
        int $ref_id,
        int $obj_id,
        ?int $user_id = null
    ) : bool {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        // Set offline if no valid link exists
        if ($permission == 'read') {
            if (!self::_getFirstLink(
                $obj_id
            ) && !$rbacsystem->checkAccessOfUser(
                $user_id,
                'write',
                $ref_id
            )) {
                return false;
            }
        }
        return parent::_checkAccess(
            $cmd,
            $permission,
            $ref_id,
            $obj_id,
            $user_id
        );
    }

    /**
     * Get first link item
     * Check before with _isSingular() if there is more or less than one
     */
    public static function _getFirstLink(int $a_webr_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (isset(self::$item[$a_webr_id])) {
            return self::$item[$a_webr_id];
        }
        $query = "SELECT * FROM webr_items " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer') . ' ' .
            "AND active = " . $ilDB->quote(1, 'integer') . ' ';
        $res = $ilDB->query($query);
        $item = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['title'] = (string) $row->title;
            $item['description'] = (string) $row->description;
            $item['target'] = (string) $row->target;
            $item['active'] = (bool) $row->active;
            $item['disable_check'] = (bool) $row->disable_check;
            $item['create_date'] = (int) $row->create_date;
            $item['last_update'] = (int) $row->last_update;
            $item['last_check'] = (int) $row->last_check;
            $item['valid'] = (bool) $row->valid;
            $item['link_id'] = (int) $row->link_id;
            self::$item[(int) $row->webr_id] = $item;
        }
        return $item;
    }

    public static function _preloadData(array $obj_ids, array $ref_ids) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $res = $ilDB->query(
            "SELECT * FROM webr_items WHERE " .
            $ilDB->in("webr_id", $obj_ids, false, "integer") .
            " AND active = " . $ilDB->quote(1, 'integer')
        );
        foreach ($obj_ids as $id) {
            self::$item[$id] = array();
        }
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $item['title'] = (string) $row->title;
            $item['description'] = (string) $row->description;
            $item['target'] = (string) $row->target;
            $item['active'] = (bool) $row->active;
            $item['disable_check'] = (bool) $row->disable_check;
            $item['create_date'] = (int) $row->create_date;
            $item['last_update'] = (int) $row->last_update;
            $item['last_check'] = (int) $row->last_check;
            $item['valid'] = (bool) $row->valid;
            $item['link_id'] = (int) $row->link_id;
            self::$item[(int) $row->webr_id] = $item;
        }
    }

    /**
     * Check whether there is only one active link in the web resource.
     * In this case this link is shown in a new browser window
     */
    public static function _checkDirectLink($a_obj_id)
    {
        if (isset(self::$single_link[$a_obj_id])) {
            return self::$single_link[$a_obj_id];
        }
        return self::$single_link[$a_obj_id] = ilLinkResourceItems::_isSingular(
            $a_obj_id
        );
    }
}

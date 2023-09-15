<?php

declare(strict_types=1);

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
 */
class ilObjLinkResourceAccess extends ilObjectAccess
{
    /**
     * @var ilWebLinkItem[]
     */
    public static array $item = [];
    public static array $single_link = [];

    public static function _getCommands(): array
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

    public static function _checkGoto(string $target): bool
    {
        global $DIC;

        $ilAccess = $DIC->access();

        $t_arr = explode("_", $target);
        $type = $t_arr[0] ?? '';
        $ref_id = (int) ($t_arr[1] ?? 0);

        if ($type !== 'webr' || $ref_id <= 0) {
            return false;
        }
        return $ilAccess->checkAccess('read', '', $ref_id) ||
            $ilAccess->checkAccess('visible', '', $ref_id);
    }

    public function _checkAccess(
        string $cmd,
        string $permission,
        int $ref_id,
        int $obj_id,
        ?int $user_id = null
    ): bool {
        global $DIC;
        $rbacsystem = $DIC->rbac()->system();
        $web_link_repo = new ilWebLinkDatabaseRepository($obj_id);

        // Set offline if no valid link exists
        if ($permission == 'read') {
            if (!$web_link_repo->getAllItemsAsContainer(true)
                                     ->getFirstItem() &&
                !$rbacsystem->checkAccessOfUser(
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
    public static function _getFirstLink(int $a_webr_id): ilWebLinkItem
    {
        if (isset(self::$item[$a_webr_id])) {
            return self::$item[$a_webr_id];
        }

        $web_link_repo = new ilWebLinkDatabaseRepository($a_webr_id);

        $current_item = $web_link_repo->getAllItemsAsContainer(true)
                                      ->getFirstItem();

        self::$item[$current_item->getWebrId()] = $current_item;

        return $current_item;
    }

    public static function _preloadData(array $obj_ids, array $ref_ids): void
    {
        foreach ($obj_ids as $id) {
            $web_link_repo = new ilWebLinkDatabaseRepository($id);
            $first_item = $web_link_repo->getAllItemsAsContainer(true)
                                        ->getFirstItem();
            self::$item[$id] = $first_item;
        }
    }

    /**
     * Check whether there is only one active link in the web resource.
     * In this case this link is shown in a new browser window
     */
    public static function _checkDirectLink($a_obj_id): bool
    {
        if (isset(self::$single_link[$a_obj_id])) {
            return self::$single_link[$a_obj_id];
        }

        $web_link_repo = new ilWebLinkDatabaseRepository($a_obj_id);

        return self::$single_link[$a_obj_id] = $web_link_repo->doesOnlyOneItemExist(true);
    }
}

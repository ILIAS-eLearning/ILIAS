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

final class ilObjTalkTemplateAdministration extends ilContainer
{
    public const TABLE_NAME = 'etal_data';

    private static int $root_ref_id = -1;
    protected static int $root_id = -1;


    /**
     * @param int  $a_id
     * @param bool $a_call_by_reference
     */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "tala";
        parent::__construct($a_id, $a_call_by_reference);
    }


    public function read(): void
    {
        parent::read();
    }


    public function create(): int
    {
        return parent::create();
    }


    public function update(): bool
    {
        return parent::update();
    }


    /**
     * @return int
     */
    public static function getRootRefId(): int
    {
        self::loadRootOrgRefIdAndId();

        return self::$root_ref_id;
    }


    /**
     * @return int
     */
    public static function getRootObjId(): int
    {
        self::loadRootOrgRefIdAndId();

        return self::$root_id;
    }


    private static function loadRootOrgRefIdAndId(): void
    {
        if (self::$root_ref_id === -1 || self::$root_id === -1) {
            global $DIC;
            $ilDB = $DIC['ilDB'];
            $q = "SELECT o.obj_id, r.ref_id FROM object_data o
			INNER JOIN object_reference r ON r.obj_id = o.obj_id
			WHERE title = '__TalkTemplateAdministration'
			LIMIT 1";
            $set = $ilDB->query($q);
            $res = $ilDB->fetchAssoc($set);
            self::$root_id = (int) $res["obj_id"];
            self::$root_ref_id = (int) $res["ref_id"];
        }
    }

    public function getTitle(): string
    {
        if (parent::getTitle() !== "__TalkTemplateAdministration") {
            return parent::getTitle();
        } else {
            return $this->lng->txt("objs_tala");
        }
    }

    /**
     * @param int         $a_id
     * @param bool        $a_reference
     * @param string|null $type
     * @return bool
     */
    public static function _exists(int $a_id, bool $a_reference = false, ?string $type = null): bool
    {
        return parent::_exists($a_id, $a_reference, "tala");
    }

    /**
     * delete orgunit, childs and all related data
     *
     * @return    boolean    true if all object data were removed; false if only a references were
     *                       removed
     */
    public function delete(): bool
    {
        return parent::delete();
    }
}

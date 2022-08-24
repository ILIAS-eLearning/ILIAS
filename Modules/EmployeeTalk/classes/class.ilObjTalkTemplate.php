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

final class ilObjTalkTemplate extends ilContainer
{
    public const TYPE = 'talt';

    private static int $root_ref_id = -1;
    private static int $root_id = -1;


    /**
     * @param int  $id
     * @param bool $a_call_by_reference
     */
    public function __construct(int $id = 0, bool $a_call_by_reference = true)
    {
        $this->setType(self::TYPE);
        parent::__construct($id, $a_call_by_reference);
    }


    public function read(): void
    {
        parent::read();
    }


    public function create(): int
    {
        $this->setOfflineStatus(true);
        parent::create();
        $this->_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::CUSTOM_METADATA, '1');
        //$this->_writeContainerSetting($this->getId(), ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS, true);
        return $this->getId();
    }


    public function update(): bool
    {
        return parent::update();
    }


    /**
     * @return int
     */
    public static function getRootOrgRefId(): int
    {
        self::loadRootOrgRefIdAndId();

        return self::$root_ref_id;
    }


    /**
     * @return int
     */
    public static function getRootOrgId(): int
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
			WHERE title = " . $ilDB->quote('__TalkTemplateAdministration', 'text') . "";
            $set = $ilDB->query($q);
            $res = $ilDB->fetchAssoc($set);
            self::$root_id = (int) $res["obj_id"];
            self::$root_ref_id = (int) $res["ref_id"];
        }
    }

    /**
     * @param int         $id
     * @param bool        $reference
     * @param string|null $type
     * @return bool
     */
    public static function _exists(int $id, bool $reference = false, ?string $type = null): bool
    {
        return parent::_exists($id, $reference, self::TYPE);
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

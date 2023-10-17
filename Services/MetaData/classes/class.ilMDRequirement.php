<?php

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

declare(strict_types=1);

/**
 * Meta Data class (element requirement)
 * @package ilias-core
 * @version $Id$
 */
class ilMDRequirement extends ilMDBase
{
    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    private const OS_TRANSLATION = [
        'pc-dos' => 'PC-DOS',
        'ms-windows' => 'MS-Windows',
        'macos' => 'MacOS',
        'unix' => 'Unix',
        'multi-os' => 'Multi-OS',
        'none' => 'None'
    ];

    private const BROWSER_TRANSLATION = [
        'any' => 'Any',
        'netscape communicator' => 'NetscapeCommunicator',
        'ms-internet explorer' => 'MS-InternetExplorer',
        'opera' => 'Opera',
        'amaya' => 'Amaya'
    ];

    private int $or_composite_id = 0;
    private string $operating_system_name = '';
    private string $operating_system_minimum_version = '';
    private string $operating_system_maximum_version = '';
    private string $browser_name = '';
    private string $browser_minimum_version = '';
    private string $browser_maximum_version = '';

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    private int $or_id_browser = 0;
    private int $or_id_os = 0;

    // SET/GET
    public function setOrCompositeId(int $a_or_composite_id): void
    {
        $this->or_composite_id = $a_or_composite_id;
    }

    public function getOrCompositeId(): int
    {
        return $this->or_composite_id;
    }

    public function setOperatingSystemName(string $a_val): bool
    {
        switch ($a_val) {
            case 'PC-DOS':
            case 'MS-Windows':
            case 'MacOS':
            case 'Unix':
            case 'Multi-OS':
            case 'None':
                $this->operating_system_name = $a_val;
                return true;

            default:
                return false;
        }
    }

    public function getOperatingSystemName(): string
    {
        return $this->operating_system_name;
    }

    public function setOperatingSystemMinimumVersion(string $a_val): void
    {
        $this->operating_system_minimum_version = $a_val;
    }

    public function getOperatingSystemMinimumVersion(): string
    {
        return $this->operating_system_minimum_version;
    }

    public function setOperatingSystemMaximumVersion(string $a_val): void
    {
        $this->operating_system_maximum_version = $a_val;
    }

    public function getOperatingSystemMaximumVersion(): string
    {
        return $this->operating_system_maximum_version;
    }

    public function setBrowserName(string $a_val): bool
    {
        switch ($a_val) {
            case 'Any':
            case 'NetscapeCommunicator':
            case 'MS-InternetExplorer':
            case 'Opera':
            case 'Amaya':
            case 'Mozilla':
                $this->browser_name = $a_val;
                return true;

            default:
                return false;
        }
    }

    public function getBrowserName(): string
    {
        return $this->browser_name;
    }

    public function setBrowserMinimumVersion(string $a_val): void
    {
        $this->browser_minimum_version = $a_val;
    }

    public function getBrowserMinimumVersion(): string
    {
        return $this->browser_minimum_version;
    }

    public function setBrowserMaximumVersion(string $a_val): void
    {
        $this->browser_maximum_version = $a_val;
    }

    public function getBrowserMaximumVersion(): string
    {
        return $this->browser_maximum_version;
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_requirement_id'] = array('integer', $next_id = $this->db->nextId('il_meta_requirement'));

        if ($this->db->insert('il_meta_requirement', $fields)) {
            $this->setMetaId($next_id);
            $this->createOrUpdateOrs();
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        if (!$this->getMetaId()) {
            return false;
        }

        $this->createOrUpdateOrs();

        return (bool) $this->db->update(
            'il_meta_requirement',
            $this->__getFields(),
            array("meta_requirement_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_requirement " .
                "WHERE meta_requirement_id = " . $this->db->quote($this->getMetaId(), 'integer');
            $res = $this->db->manipulate($query);

            $this->deleteAllOrs();
            return true;
        }
        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields(): array
    {
        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'parent_type' => array('text', $this->getParentType()),
            'parent_id' => array('integer', $this->getParentId()),
            //'operating_system_name' => array('text', $this->getOperatingSystemName()),
            //'os_min_version' => array('text', $this->getOperatingSystemMinimumVersion()),
            //'os_max_version' => array('text', $this->getOperatingSystemMaximumVersion()),
            //'browser_name' => array('text', $this->getBrowserName()),
            //'browser_minimum_version' => array('text', $this->getBrowserMinimumVersion()),
            //'browser_maximum_version' => array('text', $this->getBrowserMaximumVersion()),
            'or_composite_id' => array('integer', $this->getOrCompositeId())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_requirement " .
                "WHERE meta_requirement_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type ?? '');
                $this->setParentId((int) $row->parent_id);
                $this->setParentType($row->parent_type);
                //$this->setOperatingSystemName($row->operating_system_name ?? '');
                //$this->setOperatingSystemMinimumVersion($row->os_min_version ?? '');
                //$this->setOperatingSystemMaximumVersion($row->os_max_version ?? '');
                //$this->setBrowserName($row->browser_name ?? '');
                //$this->setBrowserMinimumVersion($row->browser_minimum_version ?? '');
                //$this->setBrowserMaximumVersion($row->browser_maximum_version ?? '');
                $this->setOrCompositeId((int) $row->or_composite_id);
            }

            $this->readFirstOrs();
        }
        return true;
    }

    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(ilXmlWriter $writer): void
    {
        $writer->xmlStartTag('Requirement');
        $writer->xmlStartTag('Type');

        if ($this->getOperatingSystemName() !== '') {
            $writer->xmlElement('OperatingSystem', array(
                'Name' => $this->getOperatingSystemName() ?: 'None',
                'MinimumVersion' => $this->getOperatingSystemMinimumVersion(),
                'MaximumVersion' => $this->getOperatingSystemMaximumVersion()
            ));
        }
        if ($this->getBrowserName() !== '') {
            $writer->xmlElement('Browser', array(
                'Name' => $this->getBrowserName() ?: 'Any',
                'MinimumVersion' => $this->getBrowserMinimumVersion(),
                'MaximumVersion' => $this->getBrowserMaximumVersion()
            ));
        }
        $writer->xmlEndTag('Type');
        $writer->xmlEndTag('Requirement');
    }

    // STATIC

    /**
     * @return int[]
     */
    public static function _getIds(
        int $a_rbac_id,
        int $a_obj_id,
        int $a_parent_id,
        string $a_parent_type,
        int $a_or_composite_id = 0
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_requirement_id FROM il_meta_requirement " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND parent_id = " . $ilDB->quote($a_parent_id, 'integer') . " " .
            "AND parent_type = " . $ilDB->quote($a_parent_type, 'text') . " " .
            "AND or_composite_id = " . $ilDB->quote($a_or_composite_id, 'integer');

        $res = $ilDB->query($query);
        $ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ids[] = (int) $row->meta_requirement_id;
        }
        return $ids;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function createOrUpdateOrs(): void
    {
        $os_name = (string) array_search(
            $this->getOperatingSystemName(),
            self::OS_TRANSLATION
        );
        $browser_name = (string) array_search(
            $this->getBrowserName(),
            self::BROWSER_TRANSLATION
        );

        $this->or_id_os = $this->createOrUpdateOr(
            $this->getOrIdOS(),
            'operating system',
            $os_name,
            $this->getOperatingSystemMinimumVersion(),
            $this->getOperatingSystemMaximumVersion()
        );

        $this->or_id_browser = $this->createOrUpdateOr(
            $this->getOrIdBrowser(),
            'browser',
            $browser_name,
            $this->getBrowserMinimumVersion(),
            $this->getBrowserMaximumVersion()
        );
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function createOrUpdateOr(
        int $id,
        string $type,
        string $name,
        string $min_version,
        string $max_version
    ): int {
        if ($name === '' && $min_version === '' && $max_version === '') {
            return 0;
        }

        if (!$id) {
            $this->db->insert(
                'il_meta_or_composite',
                [
                    'meta_or_composite_id' => ['integer', $next_id = $this->db->nextId('il_meta_or_composite')],
                    'rbac_id' => ['integer', $this->getRBACId()],
                    'obj_id' => ['integer', $this->getObjId()],
                    'obj_type' => ['text', $this->getObjType()],
                    'parent_type' => ['text', 'meta_requirement'],
                    'parent_id' => ['integer', $this->getMetaId()],
                    'type' => ['text', $type],
                    'name' => ['text', $name],
                    'min_version' => ['text', $min_version],
                    'max_version' => ['text', $max_version]
                ]
            );
            return $next_id;
        }

        $this->db->update(
            'il_meta_or_composite',
            [
                'type' => ['text', $type],
                'name' => ['text', $name],
                'min_version' => ['text', $min_version],
                'max_version' => ['text', $max_version]
            ],
            ['meta_or_composite_id' => ['integer', $id]]
        );
        return $id;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function deleteAllOrs(): void
    {
        $query = "DELETE FROM il_meta_or_composite WHERE parent_type = 'meta_requirement'
                AND parent_id = " . $this->db->quote($this->getMetaId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readFirstOrs(): void
    {
        $query = "SELECT * FROM il_meta_or_composite WHERE meta_or_composite_id = " .
                $this->db->quote($this->getOrIdOS(), 'integer') .
                " OR meta_or_composite_id = " . $this->db->quote($this->getOrIdBrowser(), 'integer');

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            switch ($row['type']) {
                case 'operating system':
                    if (key_exists($row['name'] ?? '', self::OS_TRANSLATION)) {
                        $row['name'] = self::OS_TRANSLATION[$row['name'] ?? ''];
                    }
                    $this->setOperatingSystemName($row['name'] ?? '');
                    $this->setOperatingSystemMinimumVersion($row['min_version'] ?? '');
                    $this->setOperatingSystemMaximumVersion($row['max_version'] ?? '');
                    break;

                case 'browser':
                    if (key_exists($row['name'] ?? '', self::BROWSER_TRANSLATION)) {
                        $row['name'] = self::BROWSER_TRANSLATION[$row['name'] ?? ''];
                    }
                    $this->setBrowserName($row['name'] ?? '');
                    $this->setBrowserMinimumVersion($row['min_version'] ?? '');
                    $this->setBrowserMaximumVersion($row['max_version'] ?? '');
                    break;
            }
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readOrIds(int $parent_id): void
    {
        $query = "SELECT meta_or_composite_id, type FROM il_meta_or_composite WHERE 
                  parent_id = " . $this->db->quote($parent_id, 'integer') .
                 " ORDER BY meta_or_composite_id";

        $res = $this->db->query($query);
        $browser_id = 0;
        $os_id = 0;
        while ($row = $this->db->fetchAssoc($res)) {
            if (!$browser_id && $row['type'] === 'browser') {
                $browser_id = (int) $row['meta_or_composite_id'];
            }
            if (!$os_id && $row['type'] === 'operating system') {
                $os_id = (int) $row['meta_or_composite_id'];
            }
            if ($browser_id && $os_id) {
                break;
            }
        }

        $this->or_id_browser = $browser_id;
        $this->or_id_os = $os_id;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function getOrIdOS(): int
    {
        return $this->or_id_os ?? 0;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function getOrIdBrowser(): int
    {
        return $this->or_id_browser ?? 0;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    public function setMetaId(int $a_meta_id, bool $a_read_data = true): void
    {
        $this->readOrIds($a_meta_id);
        parent::setMetaId($a_meta_id, $a_read_data);
    }
}

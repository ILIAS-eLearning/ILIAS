<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Didactical template settings
 * @author   Stefan Meyer <meyer@leifos.com>
 * @defgroup ServicesDidacticTemplate
 */
class ilDidacticTemplateSettings
{
    private static ?ilDidacticTemplateSettings $instance = null;
    private static array $instances = [];

    /** @var ilDidacticTemplateSetting[] */
    private array $templates = [];
    private string $obj_type = '';

    private ilDBInterface $db;

    private function __construct(string $a_obj_type = '')
    {
        global $DIC;

        $this->obj_type = $a_obj_type;
        $this->db = $DIC->database();
        $this->read();
    }

    public static function getInstance() : ilDidacticTemplateSettings
    {
        if (self::$instance) {
            return self::$instance;
        }

        return self::$instance = new ilDidacticTemplateSettings();
    }

    public static function getInstanceByObjectType(string $a_obj_type) : ilDidacticTemplateSettings
    {
        return self::$instances[$a_obj_type] ?? (self::$instances[$a_obj_type] = new ilDidacticTemplateSettings($a_obj_type));
    }

    /**
     * @return string[]
     * @throws ilDatabaseException
     */
    public static function lookupAssignedObjectTypes() : array
    {
        global $DIC;

        $db = $DIC->database();
        $query = 'select distinct (obj_type) from didactic_tpl_sa ' .
            'group by obj_type';
        $res = $db->query($query);
        $types = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $types[] = $row->obj_type;
        }

        return $types;
    }

    /**
     * @return ilDidacticTemplateSetting[]
     */
    public function getTemplates() : array
    {
        return $this->templates;
    }

    public function getObjectType() : string
    {
        return $this->obj_type;
    }

    /**
     * Read disabled templates
     */
    public function readInactive() : bool
    {
        $query = 'SELECT dtpl.id FROM didactic_tpl_settings dtpl ';

        if ($this->getObjectType()) {
            $query .= 'JOIN didactic_tpl_sa tplsa ON dtpl.id = tplsa.id ';
        }
        $query .= 'WHERE enabled = ' . $this->db->quote(0, 'integer') . ' ';

        if ($this->getObjectType()) {
            $query .= 'AND obj_type = ' . $this->db->quote($this->getObjectType(), 'text');
        }

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->templates[$row->id] = new ilDidacticTemplateSetting((int) $row->id);
        }

        return true;
    }

    /**
     * Read active didactic templates
     * @return bool
     */
    protected function read() : bool
    {
        $query = 'SELECT dtpl.id FROM didactic_tpl_settings dtpl ';
        if ($this->getObjectType()) {
            $query .= 'JOIN didactic_tpl_sa tplsa ON dtpl.id = tplsa.id ';
        }
        $query .= 'WHERE enabled = ' . $this->db->quote(1, 'integer') . ' ';

        if ($this->getObjectType()) {
            $query .= 'AND obj_type = ' . $this->db->quote($this->getObjectType(), 'text');
        }

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->templates[$row->id] = new ilDidacticTemplateSetting((int) $row->id);
        }

        return true;
    }
}

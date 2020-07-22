<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Object settings regarding position permissions
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilOrgUnitObjectTypePositionSetting
{
    const DEFAULT_OFF = 0;
    const DEFAULT_ON = 1;
    /**
     * @var ilDBInterface
     */
    protected $db;
    /**
     * @var string
     */
    private $type = '';
    /**
     * @var bool
     */
    private $active = false;
    /**
     * @var bool
     */
    private $changeable = false;
    /**
     * @var int
     */
    private $default = self::DEFAULT_OFF;


    /**
     * Constructor
     *
     * @param string $a_obj_type
     */
    public function __construct($a_obj_type)
    {
        $this->db = $GLOBALS['DIC']->database();
        $this->type = $a_obj_type;
        $this->read();
    }


    /**
     * set active for object type
     */
    public function setActive($a_active)
    {
        $this->active = $a_active;
    }


    /**
     * @param int $a_default
     */
    public function setActivationDefault($a_default)
    {
        $this->default = $a_default;
    }


    /**
     * @param bool $a_status
     */
    public function setChangeableForObject($a_status)
    {
        $this->changeable = $a_status;
    }


    /**
     * Check if active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }


    /**
     * Get activation default
     *
     * @return int
     */
    public function getActivationDefault()
    {
        return $this->default;
    }


    /**
     * return bool
     */
    public function isChangeableForObject()
    {
        return $this->changeable;
    }


    /**
     * Update type entry
     */
    public function update()
    {
        $this->db->replace('orgu_obj_type_settings', [
                'obj_type' => [ 'text', $this->type ],
            ], [
                'active' => [ 'integer', (int) $this->isActive() ],
                'activation_default' => [ 'integer', (int) $this->getActivationDefault() ],
                'changeable' => [ 'integer', (int) $this->isChangeableForObject() ],
            ]);
    }


    /**
     * Read from db
     */
    protected function read()
    {
        $query = 'SELECT * FROM orgu_obj_type_settings ' . 'WHERE obj_type = '
                 . $this->db->quote($this->type, 'text');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->entry_exists = true;
            $this->setActive((bool) $row->active);
            $this->setActivationDefault((int) $row->activation_default);
            $this->setChangeableForObject((bool) $row->changeable);
        }
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}

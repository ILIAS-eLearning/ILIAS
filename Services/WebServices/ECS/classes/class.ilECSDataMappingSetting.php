<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSDataMappingSetting
{
    const MAPPING_EXPORT = 1;
    const MAPPING_IMPORT_CRS = 2;
    const MAPPING_IMPORT_RCRS = 3;

    private ilDBInterface $db;

    private int $server_id = 0;
    private int $mapping_type = 0;
    private $ecs_field = 0;
    private $advmd_id = 0;


    /**
     * constructor
     * @access public
     *
     */
    public function __construct($a_server_id = 0, $mapping_type = 0, $ecs_field = '')
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->setServerId($a_server_id);
        $this->setMappingType($mapping_type);
        $this->setECSField($ecs_field);
    }

    /**
     * set server id
     * @param int $a_server_id
     */
    public function setServerId($a_server_id)
    {
        $this->server_id = $a_server_id;
    }

    /**
     * Get server id
     */
    public function getServerId()
    {
        return $this->server_id;
    }

    /**
     *
     * @param string $ecs_field
     */
    public function setECSField($ecs_field)
    {
        $this->ecs_field = $ecs_field;
    }

    /**
     * Get ecs field
     */
    public function getECSField()
    {
        return $this->ecs_field;
    }

    /**
     * Set mapping type
     * @param int $mapping_type
     */
    public function setMappingType($mapping_type)
    {
        $this->mapping_type = $mapping_type;
    }

    /**
     * Get mapping type
     */
    public function getMappingType()
    {
        return $this->mapping_type;
    }


    /**
     *
     * @return int
     */
    public function getAdvMDId()
    {
        return $this->advmd_id;
    }

    public function setAdvMDId($a_id)
    {
        $this->advmd_id = $a_id;
    }

    /**
     * Save mappings
     *
     * @access public
     */
    public function save()
    {
        $query = 'SELECT * FROM ecs_data_mapping ' .
            'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            'AND mapping_type = ' . $this->db->quote($this->getMappingType(), 'integer') . ' ' .
            'AND ecs_field = ' . $this->db->quote($this->getECSField(), 'text');
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $this->update();
        } else {
            $this->create();
        }
    }

    /**
     * Update setting
     * @$this->db ilDB $ilDB
     */
    protected function update()
    {
        $query = 'UPDATE ecs_data_mapping ' .
            'SET advmd_id = ' . $this->db->quote($this->getAdvMDId(), 'integer') . ' ' .
            'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            'AND mapping_type = ' . $this->db->quote($this->getMappingType(), 'integer') . ' ' .
            'AND ecs_field = ' . $this->db->quote($this->getECSField(), 'text');
        $this->db->manipulate($query);
    }

    protected function create()
    {
        $query = 'INSERT INTO ecs_data_mapping (sid,mapping_type,ecs_field,advmd_id) ' .
            'VALUES(' .
            $this->db->quote($this->getServerId(), 'integer') . ', ' .
            $this->db->quote($this->getMappingType(), 'integer') . ', ' .
            $this->db->quote($this->getECSField(), 'text') . ', ' .
            $this->db->quote($this->getAdvMDId(), 'integer') . ' ) ';
        $this->db->manipulate($query);
        return true;
    }


    /**
     * Read settings
     *
     * @access private
     *
     */
    private function read()
    {
        if ($this->getServerId() and $this->getMappingType() and $this->getECSField()) {
            $query = 'SELECT * FROM ecs_data_mapping ' .
                'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
                'AND mapping_type = ' . $this->db->quote($this->getMappingType(), 'integer') . ' ' .
                'AND ecs_field = ' . $this->db->quote($this->getECSField(), 'text');
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setAdvMDId($row->advmd_id);
            }
        }
    }
}

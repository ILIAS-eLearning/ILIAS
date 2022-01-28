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

class ilRemoteObjectBaseListGUI extends ilObjectListGUI
{
    private ilDBInterface $db;
    /**
     * Constructor
     *
     * @access public
     *
     */
    public function __construct()
    {
        global $DIC;
        parent::__construct();

        $this->db = $DIC->database();
    }

    /**
     * lookup organization
     *
     * @param int $a_obj_id
     * @return string
     */
    public function _lookupOrganization($table, $a_obj_id)
    {
        $query = "SELECT organization FROM " . $this->db->quoteIdentifier($table) .
        " WHERE obj_id = " . $this->db->quote($a_obj_id, 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->organization;
        }
        return '';
    }
}

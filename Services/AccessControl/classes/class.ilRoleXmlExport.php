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
 * Xml export of roles and role templates
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesAccessControl
 */
class ilRoleXmlExport extends ilXmlWriter
{
    public const MODE_DTPL = 1;

    private array $roles = array();
    private array $operations = array();
    private int $mode = 0;

    protected ilRbacReview $rbacreview;

    public function __construct()
    {
        global $DIC;

        $this->rbacreview = $DIC->rbac()->review();

        parent::__construct();
        $this->initRbacOperations();
    }

    /**
     * Set roles
     * Format is: array(role_id => array(role_folder_id))
     */
    public function setRoles(array $a_roles) : void
    {
        $this->roles = $a_roles;
    }

    public function getRoles() : array
    {
        return $this->roles;
    }

    public function addRole(int $a_role_id, int $a_rolf_id) : void
    {
        $this->roles[$a_role_id][] = $a_rolf_id;
    }

    public function setMode(int $a_mode) : void
    {
        $this->mode = $a_mode;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    /**
     * Write xml header
     */
    public function writeHeader() : void
    {
        $this->xmlSetDtdDef("<!DOCTYPE Roles PUBLIC \"-//ILIAS//DTD ILIAS Roles//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_role_definition_4_2.dtd\">");
        $this->xmlSetGenCmt("Role Definition");
        $this->xmlHeader();
    }

    /**
     * Write xml presentation of chosen roles
     */
    public function write() : void
    {
        if ($this->getMode() != self::MODE_DTPL) {
            $this->xmlStartTag('roles');
        }

        foreach ($this->getRoles() as $role_id => $role_folder_ids) {
            foreach ((array) $role_folder_ids as $rolf) {
                $this->writeRole($role_id, $rolf);
            }
        }

        if ($this->getMode() != self::MODE_DTPL) {
            $this->xmlEndTag('roles');
        }
    }

    /**
     * Write xml presentation of one role
     */
    private function writeRole(int $a_role_id, int $a_rolf) : void
    {
        $attributes = array(
            'type' => ilObject::_lookupType($a_role_id),
            'id' => 'il_' . IL_INST_ID . '_' . ilObject::_lookupType($a_role_id) . '_' . $a_role_id,
            'protected' => ($GLOBALS['DIC']['rbacreview']->isProtected($a_rolf, $a_role_id) ? 1 : 0)
        );

        $this->xmlStartTag('role', $attributes);

        $this->xmlElement('title', array(), ilObject::_lookupTitle($a_role_id));
        $this->xmlElement('description', array(), ilObject::_lookupDescription($a_role_id));

        $this->xmlStartTag('operations');
        foreach ($this->rbacreview->getAllOperationsOfRole($a_role_id, $a_rolf) as $obj_group => $operations) {
            foreach ($operations as $ops_id) {
                $this->xmlElement('operation', array('group' => $obj_group), trim($this->operations[$ops_id]));
            }
        }
        $this->xmlEndTag('operations');
        $this->xmlEndTag('role');
    }

    /**
     * Cache rbac operations
     */
    private function initRbacOperations() : void
    {
        foreach ($this->rbacreview->getOperations() as $operation) {
            $this->operations[$operation['ops_id']] = $operation['operation'];
        }
    }
}

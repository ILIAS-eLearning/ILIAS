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
 * @defgroup ServicesCopyWizard Services/CopyWizard
 * @author   Stefan Meyer <meyer@leifos.com>
 * @ingroup  ServicesCopyWizard
 */
class ilCopyWizardOptions
{
    private static array $instances = [];

    public const COPY_WIZARD_UNDEFINED = 0;
    public const COPY_WIZARD_OMIT = 1;
    public const COPY_WIZARD_COPY = 2;
    public const COPY_WIZARD_LINK = 3;
    public const COPY_WIZARD_LINK_TO_TARGET = 4;

    protected const OWNER_KEY = -3;
    protected const DISABLE_SOAP = -4;
    protected const ROOT_NODE = -5;
    protected const DISABLE_TREE_COPY = -6;

    protected ilDBInterface $db;
    protected ilTree $tree;

    private int $copy_id;
    private array $options = [];
    private array $tmp_tree = [];

    /**
     * Private Constructor (Singleton class)
     */
    private function __construct(int $a_copy_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->copy_id = $a_copy_id;

        if ($this->copy_id) {
            $this->read();
        }
    }

    public static function _getInstance(int $a_copy_id) : ilCopyWizardOptions
    {
        if (isset(self::$instances[$a_copy_id])) {
            return self::$instances[$a_copy_id];
        }
        return self::$instances[$a_copy_id] = new ilCopyWizardOptions($a_copy_id);
    }

    public function getRequiredSteps() : int
    {
        $steps = 0;
        if (array_key_exists(0, $this->options) && is_array($this->options[0])) {
            $steps += count($this->options[0]);
        }
        if (array_key_exists(-1, $this->options) && is_array($this->options[-1])) {
            $steps += count($this->options[-1]);
        }
        return $steps;
    }

    public static function _isFinished(int $a_copy_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM copy_wizard_options " .
            "WHERE copy_id  = " . $ilDB->quote($a_copy_id, 'integer') . " ";
        $res = $ilDB->query($query);
        return !$res->numRows();
    }

    /**
     * Allocate a copy for further entries
     * @todo this is not thread safe
     */
    public static function _allocateCopyId() : int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT MAX(copy_id) latest FROM copy_wizard_options ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        $ilDB->insert("copy_wizard_options", array(
            "copy_id" => array("integer", ((int) $row->latest) + 1),
            "source_id" => array("integer", 0)
        ));
        return ((int) $row->latest) + 1;
    }

    /**
     * Save owner for copy. It will be checked against this user id in all soap calls
     */
    public function saveOwner(int $a_user_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilDB->insert("copy_wizard_options", array(
            "copy_id" => array("integer", $this->getCopyId()),
            "source_id" => array("integer", self::OWNER_KEY),
            "options" => array('clob', serialize(array($a_user_id)))
        ));
    }

    /**
     * Save root node id
     */
    public function saveRoot(int $a_root) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $ilDB->insert("copy_wizard_options", array(
            "copy_id" => array("integer", $this->getCopyId()),
            "source_id" => array("integer", self::ROOT_NODE),
            "options" => array('clob', serialize(array($a_root)))
        ));
    }

    /**
     * Is root node
     * @access public
     * @param int ref_id of copy
     */
    public function isRootNode(int $a_root) : bool
    {
        return in_array($a_root, $this->getOptions(self::ROOT_NODE));
    }

    public function getRootNode() : ?int
    {
        $options = $this->getOptions(self::ROOT_NODE);
        if (!is_array($options) || !array_key_exists(0, $options)) {
            return null;
        }
        return (int) $options[0];
    }

    /**
     * Disable soap calls. Recursive call of ilClone and ilCloneDependencies
     */
    public function disableSOAP() : void
    {
        $this->options[self::DISABLE_SOAP] = 1;

        $this->db->insert("copy_wizard_options", array(
            "copy_id" => array("integer", $this->getCopyId()),
            "source_id" => array("integer", self::DISABLE_SOAP),
            "options" => array('clob', serialize(array(1)))
        ));
    }

    /**
     * Disable copying of tree.
     * Used for workspace copies
     */
    public function disableTreeCopy() : void
    {
        $this->options[self::DISABLE_TREE_COPY] = 1;

        $this->db->insert("copy_wizard_options", array(
            "copy_id" => array("integer", $this->getCopyId()),
            "source_id" => array("integer", self::DISABLE_TREE_COPY),
            "options" => array('clob', serialize(array(1)))
        ));
    }

    /**
     * Check if tree copy is enabled
     */
    public function isTreeCopyDisabled() : bool
    {
        if (isset($this->options[self::DISABLE_TREE_COPY]) && $this->options[self::DISABLE_TREE_COPY]) {
            return true;
        }
        return false;
    }

    /**
     * Check if SOAP calls are disabled
     */
    public function isSOAPEnabled() : bool
    {
        if (isset($this->options[self::DISABLE_SOAP]) and $this->options[self::DISABLE_SOAP]) {
            return false;
        }
        return true;
    }

    /**
     * check owner
     */
    public function checkOwner(int $a_user_id) : bool
    {
        return in_array($a_user_id, $this->getOptions(self::OWNER_KEY));
    }

    public function getCopyId() : int
    {
        return $this->copy_id;
    }

    public function initContainer(int $a_source_id, int $a_target_id) : void
    {
        $mapping_source = $this->tree->getParentId($a_source_id);
        $this->addEntry($a_source_id, array('type' => ilCopyWizardOptions::COPY_WIZARD_COPY));
        $this->appendMapping($mapping_source, $a_target_id);
    }

    /**
     * Save tree
     * Stores two copies of the tree structure:
     * id 0 is used for recursive call of cloneObject()
     * id -1 is used for recursive call of cloneDependencies()
     */
    public function storeTree(int $a_source_id) : void
    {
        $this->readTree($a_source_id);
        $a_tree_structure = $this->tmp_tree;

        $this->db->update("copy_wizard_options", array(
            "options" => array('clob', serialize($a_tree_structure))
        ), array(
            "copy_id" => array('integer', $this->getCopyId()),
            "source_id" => array('integer',
                                 0
            )
        ));

        $this->db->insert('copy_wizard_options', array(
            'copy_id' => array('integer', $this->getCopyId()),
            'source_id' => array('integer', -1),
            'options' => array('clob', serialize($a_tree_structure))
        ));
    }

    /**
     * Get first node of stored tree
     */
    private function fetchFirstNodeById($a_id) : ?array
    {
        $tree = $this->getOptions($a_id);
        if (isset($tree[0]) and is_array($tree[0])) {
            return $tree[0];
        }
        return null;
    }

    /**
     * Fetch first node for cloneObject
     */
    public function fetchFirstNode() : ?array
    {
        return $this->fetchFirstNodeById(0);
    }

    /**
     * Fetch first dependencies node
     */
    public function fetchFirstDependenciesNode() : ?array
    {
        return $this->fetchFirstNodeById(-1);
    }

    /**
     * Drop first node by id
     */
    public function dropFirstNodeById(int $a_id) : bool
    {
        if (!isset($this->options[$a_id]) || !is_array($this->options[$a_id])) {
            return false;
        }
        $this->options[$a_id] = array_slice($this->options[$a_id], 1);

        $this->db->update('copy_wizard_options', array(
            'options' => array('clob', serialize($this->options[$a_id]))
        ), array(
            'copy_id' => array('integer', $this->getCopyId()),
            'source_id' => array('integer', $a_id)
        ));

        $this->read();
        // check for role_folder
        if (($node = $this->fetchFirstNodeById($a_id)) === null) {
            return true;
        }
        if ($node['type'] == 'rolf') {
            $this->dropFirstNodeById($a_id);
        }
        return true;
    }

    /**
     * Drop first node (for cloneObject())
     */
    public function dropFirstNode() : bool
    {
        return $this->dropFirstNodeById(0);
    }

    /**
     * Drop first node (for cloneDependencies())
     */
    public function dropFirstDependenciesNode() : bool
    {
        return $this->dropFirstNodeById(-1);
    }

    /**
     * Get entry by source
     * @access public
     * @param int source ref_id
     */
    public function getOptions(int $a_source_id) : array
    {
        if (isset($this->options[$a_source_id]) and is_array($this->options[$a_source_id])) {
            return $this->options[$a_source_id];
        }
        return [];
    }

    /**
     * Add new entry
     */
    public function addEntry(int $a_source_id, array $a_options) : void
    {
        $query = "DELETE FROM copy_wizard_options " .
            "WHERE copy_id = " . $this->db->quote($this->copy_id, 'integer') . " " .
            "AND source_id = " . $this->db->quote($a_source_id, 'integer');
        $res = $this->db->manipulate($query);
        $this->db->insert('copy_wizard_options', array(
            'copy_id' => array('integer', $this->copy_id),
            'source_id' => array('integer', $a_source_id),
            'options' => array('clob', serialize($a_options))
        ));
    }

    /**
     * Add mapping of source -> target
     * @param int | string $a_source_id
     * @param mixed        $a_target_id
     * @return void
     */
    public function appendMapping($a_source_id, $a_target_id) : void
    {
        $query = "SELECT * FROM copy_wizard_options " .
            "WHERE copy_id = " . $this->db->quote($this->copy_id, 'integer') . " " .
            "AND source_id = -2 ";
        $res = $this->db->query($query);
        $mappings = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $mappings = unserialize((string) $row->options);
        }
        $mappings[$a_source_id] = $a_target_id;

        $query = "DELETE FROM copy_wizard_options " .
            "WHERE copy_id = " . $this->db->quote($this->getCopyId(), 'integer') . " " .
            "AND source_id = -2 ";
        $res = $this->db->manipulate($query);

        $this->db->insert('copy_wizard_options', array(
            'copy_id' => array('integer', $this->getCopyId()),
            'source_id' => array('integer', -2),
            'options' => array('clob', serialize($mappings))
        ));
    }

    public function getMappings() : array
    {
        if (isset($this->options[-2]) and is_array($this->options[-2])) {
            return $this->options[-2];
        }
        return [];
    }

    public function deleteAll() : void
    {
        if (isset(self::$instances[$this->copy_id])) {
            unset(self::$instances[$this->copy_id]);
        }
        $query = "DELETE FROM copy_wizard_options " .
            "WHERE copy_id = " . $this->db->quote($this->copy_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    public function read() : void
    {
        $query = "SELECT * FROM copy_wizard_options " .
            "WHERE copy_id = " . $this->db->quote($this->copy_id, 'integer');
        $res = $this->db->query($query);

        $this->options = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->options[(int) $row->source_id] = unserialize((string) $row->options);
        }
    }

    /**
     * Purge ommitted node recursively
     * @access private
     * @param array current node
     */
    private function readTree(int $a_source_id) : void
    {
        $this->tmp_tree[] = $this->tree->getNodeData($a_source_id);
        foreach ($this->tree->getChilds($a_source_id) as $sub_nodes) {
            $sub_node_ref_id = (int) $sub_nodes['child'];
            // check ommited, linked ...
            $options = $this->options[$sub_node_ref_id] ?? [];
            $type = (int) ($options['type'] ?? 0);
            if ($type === self::COPY_WIZARD_COPY or
                $type === self::COPY_WIZARD_LINK) {
                $this->readTree($sub_node_ref_id);
            }
        }
    }
}

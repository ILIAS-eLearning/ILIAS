<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
require_once('./Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRules.php');

/**
 * Shibboleth role assignment rule
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 * @author  Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version $Id$
 *
 *
 * @ingroup AuthShibboleth
 */
class ilShibbolethRoleAssignmentRule {

	const ERR_MISSING_NAME = 'shib_missing_attr_name';
	const ERR_MISSING_VALUE = 'shib_missing_attr_value';
	const ERR_MISSING_ROLE = 'shib_missing_role';
	const ERR_MISSING_PLUGIN_ID = 'shib_missing_plugin_id';
	const TABLE_NAME = 'shib_role_assignment';
	/**
	 * @var ilDB
	 */
	protected $db;
	/**
	 * @var int
	 */
	private $rule_id = 0;
	/**
	 * @var int
	 */
	private $role_id = 0;
	/**
	 * @var string
	 */
	private $attribute_name = '';
	/**
	 * @var string
	 */
	private $attribute_value = '';
	/**
	 * @var bool
	 */
	private $plugin_active = false;
	/**
	 * @var bool
	 */
	private $add_on_update = false;
	/**
	 * @var bool
	 */
	private $remove_on_update = false;
	/**
	 * @var int
	 */
	private $plugin_id = 0;


	/**
	 * @param int $a_rule_id
	 */
	public function __construct($a_rule_id = 0) {
		global $ilDB;
		$this->db = $ilDB;
		$this->rule_id = $a_rule_id;
		$this->read();
	}


	/**
	 * @param $a_id
	 */
	public function setRuleId($a_id) {
		$this->rule_id = $a_id;
	}


	/**
	 * @return int
	 */
	public function getRuleId() {
		return $this->rule_id;
	}


	/**
	 * @param $a_id
	 */
	public function setRoleId($a_id) {
		$this->role_id = $a_id;
	}


	/**
	 * @return int
	 */
	public function getRoleId() {
		return $this->role_id;
	}


	/**
	 * @param $a_name
	 */
	public function setName($a_name) {
		$this->attribute_name = $a_name;
	}


	/**
	 * @return string
	 */
	public function getName() {
		return $this->attribute_name;
	}


	/**
	 * @param $a_value
	 */
	public function setValue($a_value) {
		$this->attribute_value = $a_value;
	}


	/**
	 * @return string
	 */
	public function getValue() {
		return $this->attribute_value;
	}


	/**
	 * @param $a_status
	 */
	public function enablePlugin($a_status) {
		$this->plugin_active = $a_status;
	}


	/**
	 * @return bool
	 */
	public function isPluginActive() {
		return (bool)$this->plugin_active;
	}


	/**
	 * @param $a_status
	 */
	public function enableAddOnUpdate($a_status) {
		$this->add_on_update = $a_status;
	}


	/**
	 * @return bool
	 */
	public function isAddOnUpdateEnabled() {
		return (bool)$this->add_on_update;
	}


	/**
	 * @param $a_status
	 */
	public function enableRemoveOnUpdate($a_status) {
		$this->remove_on_update = $a_status;
	}


	/**
	 * @return bool
	 */
	public function isRemoveOnUpdateEnabled() {
		return (bool)$this->remove_on_update;
	}


	/**
	 * @param $a_id
	 */
	public function setPluginId($a_id) {
		$this->plugin_id = $a_id;
	}


	/**
	 * @return int
	 */
	public function getPluginId() {
		return $this->plugin_id;
	}


	/**
	 * @return string
	 */
	public function conditionToString() {
		global $lng;
		if ($this->isPluginActive()) {
			return $lng->txt('shib_plugin_id') . ': ' . $this->getPluginId();
		} else {
			return $this->getName() . '=' . $this->getValue();
		}
	}


	/**
	 * @return string
	 */
	public function validate() {
		if (! $this->getRoleId()) {
			return self::ERR_MISSING_ROLE;
		}
		if (! $this->isPluginActive()) {
			if (! $this->getName()) {
				return self::ERR_MISSING_NAME;
			}
			if (! $this->getValue()) {
				return self::ERR_MISSING_VALUE;
			}
		} else {
			// check plugin id is given
			if (! $this->getPluginId()) {
				return self::ERR_MISSING_PLUGIN_ID;
			}
		}

		return '';
	}


	/**
	 * @return bool
	 */
	public function delete() {
		$query = 'DELETE FROM ' . self::TABLE_NAME . ' ' . 'WHERE rule_id = ' . $this->db->quote($this->getRuleId(), 'integer');
		$this->db->manipulate($query);

		return true;
	}


	/**
	 * @return bool
	 */
	public function add() {
		$next_id = $this->db->nextId(self::TABLE_NAME);
		$query = 'INSERT INTO ' . self::TABLE_NAME . ' (rule_id,role_id,name,value,plugin,plugin_id,add_on_update,remove_on_update ) ' . 'VALUES( '
			. $this->db->quote($next_id, 'integer') . ', ' . $this->db->quote($this->getRoleId(), 'integer') . ', '
			. $this->db->quote($this->getName(), 'text') . ', ' . $this->db->quote($this->getValue(), 'text') . ', '
			. $this->db->quote((int)$this->isPluginActive(), 'integer') . ', ' . $this->db->quote((int)$this->getPluginId(), 'integer') . ', '
			. $this->db->quote((int)$this->isAddOnUpdateEnabled(), 'integer') . ', '
			. $this->db->quote((int)$this->isRemoveOnUpdateEnabled(), 'integer') . ') ';
		$this->db->manipulate($query);
		$this->setRuleId($this->db->getLastInsertId());

		return true;
	}


	/**
	 * @return bool
	 */
	public function update() {
		$query = 'UPDATE ' . self::TABLE_NAME . ' ' . 'SET role_id = ' . $this->db->quote($this->getRoleId(), 'integer') . ', ' . 'name = '
			. $this->db->quote($this->getName(), 'text') . ', ' . 'value = ' . $this->db->quote($this->getValue(), 'text') . ', ' . 'plugin = '
			. $this->db->quote((int)$this->isPluginActive(), 'integer') . ', ' . 'plugin_id = '
			. $this->db->quote((int)$this->getPluginId(), 'integer') . ', ' . 'add_on_update = '
			. $this->db->quote((int)$this->isAddOnUpdateEnabled(), 'integer') . ', ' . 'remove_on_update = '
			. $this->db->quote((int)$this->isRemoveOnUpdateEnabled(), 'integer') . ' '
			. 'WHERE rule_id = ' . $this->db->quote($this->getRuleId(), 'integer');
		$this->db->manipulate($query);

		return true;
	}


	/**
	 * @param $a_data
	 *
	 * @deprecated
	 * @return bool
	 */
	public function matches($a_data) {
		if ($this->isPluginActive()) {
			return ilShibbolethRoleAssignmentRules::callPlugin($this->getPluginId(), $a_data);
		}
		// No value
		if (! isset($a_data[$this->getName()])) {
			return false;
		}
		$values = $a_data[$this->getName()];
		if (is_array($values)) {
			return in_array($this->getValue(), $values);
		} else {
			return $this->wildcardCompare($this->getValue(), $values);
		}
	}


	/**
	 * @param $a_str1
	 * @param $a_str2
	 *
	 * @deprecated
	 * @return bool
	 */
	protected function wildcardCompare($a_str1, $a_str2) {
		$pattern = str_replace('*', '.*?', $a_str1);

		return (bool)preg_match("/" . $pattern . "/us", $a_str2);
	}


	/**
	 * @param array $a_data
	 *
	 * @return bool
	 */
	public  function doesMatch(array $a_data) {
		if ($this->isPluginActive()) {
			return ilShibbolethRoleAssignmentRules::callPlugin($this->getPluginId(), $a_data);
		}
		if (! isset($a_data[$this->getName()])) {
			return false;
		}
		$values = $a_data[$this->getName()];
		if (is_array($values)) {
			return in_array($this->getValue(), $values);
		} else {
			$pattern = str_replace('*', '.*?', $this->getValue());

			return (bool)preg_match("/" . $pattern . "/us", $values);
		}
	}


	/**
	 * @return bool
	 */
	private function read() {
		if (! $this->getRuleId()) {
			return true;
		}
		$query = 'SELECT * FROM ' . self::TABLE_NAME . ' ' . 'WHERE rule_id = ' . $this->db->quote($this->getRuleId(), 'integer');
		$res = $this->db->query($query);
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
			$this->setRoleId($row->role_id);
			$this->setName($row->name);
			$this->setValue($row->value);
			$this->enablePlugin($row->plugin);
			$this->setPluginId($row->plugin_id);
			$this->enableAddOnUpdate($row->add_on_update);
			$this->enableRemoveOnUpdate($row->remove_on_update);
		}
	}
}

?>
<?php
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
 * Shibboleth role assignment rule
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 * @author  Fabian Schmid <fabian.schmid@ilub.unibe.ch>
 * @version $Id$
 *
 *
 * @ingroup AuthShibboleth
 */
class ilShibbolethRoleAssignmentRule
{
    private const ERR_MISSING_NAME = 'shib_missing_attr_name';
    private const ERR_MISSING_VALUE = 'shib_missing_attr_value';
    private const ERR_MISSING_ROLE = 'shib_missing_role';
    private const ERR_MISSING_PLUGIN_ID = 'shib_missing_plugin_id';
    private const TABLE_NAME = 'shib_role_assignment';

    private ilDBInterface $db;
    private int $rule_id;
    private int $role_id = 0;
    private string $attribute_name = '';
    private string $attribute_value = '';
    private bool $plugin_active = false;
    private bool $add_on_update = false;
    private bool $remove_on_update = false;
    private ?string $plugin_id = null;

    public function __construct(int $a_rule_id = 0)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $this->db = $ilDB;
        $this->rule_id = $a_rule_id;
        $this->read();
    }

    public function setRuleId(int $a_id): void
    {
        $this->rule_id = $a_id;
    }

    public function getRuleId(): int
    {
        return $this->rule_id;
    }

    public function setRoleId(int $a_id): void
    {
        $this->role_id = $a_id;
    }

    public function getRoleId(): int
    {
        return $this->role_id;
    }

    public function setName(string $a_name): void
    {
        $this->attribute_name = $a_name;
    }

    public function getName(): string
    {
        return $this->attribute_name;
    }

    public function setValue(string $a_value): void
    {
        $this->attribute_value = $a_value;
    }

    public function getValue(): string
    {
        return $this->attribute_value;
    }

    public function enablePlugin(bool $a_status): void
    {
        $this->plugin_active = $a_status;
    }

    public function isPluginActive(): bool
    {
        return $this->plugin_active;
    }

    public function enableAddOnUpdate(bool $a_status): void
    {
        $this->add_on_update = $a_status;
    }

    public function isAddOnUpdateEnabled(): bool
    {
        return $this->add_on_update;
    }

    public function enableRemoveOnUpdate(bool $a_status): void
    {
        $this->remove_on_update = $a_status;
    }

    public function isRemoveOnUpdateEnabled(): bool
    {
        return $this->remove_on_update;
    }

    public function setPluginId(?string $a_id): void
    {
        $this->plugin_id = $a_id;
    }

    public function getPluginId(): ?string
    {
        return $this->plugin_id;
    }

    public function conditionToString(): ?string
    {
        global $DIC;
        $lng = $DIC['lng'];
        if ($this->isPluginActive()) {
            return $lng->txt('shib_plugin_id') . ': ' . $this->getPluginId();
        }

        return $this->getName() . '=' . $this->getValue();
    }

    public function validate(): string
    {
        if ($this->getRoleId() === 0) {
            return self::ERR_MISSING_ROLE;
        }
        if (!$this->isPluginActive()) {
            if ($this->getName() === '' || $this->getName() === '0') {
                return self::ERR_MISSING_NAME;
            }
            if ($this->getValue() === '' || $this->getValue() === '0') {
                return self::ERR_MISSING_VALUE;
            }
        } elseif ($this->getPluginId() === 0) {
            return self::ERR_MISSING_PLUGIN_ID;
        }

        return '';
    }

    public function delete(): bool
    {
        $query = 'DELETE FROM ' . self::TABLE_NAME . ' ' . 'WHERE rule_id = ' . $this->db->quote(
            $this->getRuleId(),
            'integer'
        );
        $this->db->manipulate($query);

        return true;
    }

    public function add(): bool
    {
        $next_id = $this->db->nextId(self::TABLE_NAME);
        $query = 'INSERT INTO ' . self::TABLE_NAME . ' (rule_id,role_id,name,value,plugin,plugin_id,add_on_update,remove_on_update ) ' . 'VALUES( '
            . $this->db->quote($next_id, 'integer') . ', ' . $this->db->quote($this->getRoleId(), 'integer') . ', '
            . $this->db->quote($this->getName(), 'text') . ', ' . $this->db->quote($this->getValue(), 'text') . ', '
            . $this->db->quote((int) $this->isPluginActive(), 'integer') . ', ' . $this->db->quote(
                $this->getPluginId() ?? 0,
                'integer'
            ) . ', '
            . $this->db->quote((int) $this->isAddOnUpdateEnabled(), 'integer') . ', '
            . $this->db->quote((int) $this->isRemoveOnUpdateEnabled(), 'integer') . ') ';
        $this->db->manipulate($query);
        $this->setRuleId($this->db->getLastInsertId());

        return true;
    }

    public function update(): bool
    {
        $query = 'UPDATE ' . self::TABLE_NAME . ' ' . 'SET role_id = ' . $this->db->quote(
            $this->getRoleId(),
            'integer'
        ) . ', ' . 'name = '
            . $this->db->quote($this->getName(), 'text') . ', ' . 'value = ' . $this->db->quote(
                $this->getValue(),
                'text'
            ) . ', ' . 'plugin = '
            . $this->db->quote((int) $this->isPluginActive(), 'integer') . ', ' . 'plugin_id = '
            . $this->db->quote($this->getPluginId() ?? 0, 'integer') . ', ' . 'add_on_update = '
            . $this->db->quote((int) $this->isAddOnUpdateEnabled(), 'integer') . ', ' . 'remove_on_update = '
            . $this->db->quote((int) $this->isRemoveOnUpdateEnabled(), 'integer') . ' '
            . 'WHERE rule_id = ' . $this->db->quote($this->getRuleId(), 'integer');
        $this->db->manipulate($query);

        return true;
    }

    /**
     * @deprecated
     */
    public function matches(array $a_data): bool
    {
        if ($this->isPluginActive()) {
            return ilShibbolethRoleAssignmentRules::callPlugin($this->getPluginId(), $a_data);
        }
        // No value
        if (!isset($a_data[$this->getName()])) {
            return false;
        }
        $values = $a_data[$this->getName()];
        if (is_array($values)) {
            return in_array($this->getValue(), $values);
        }

        return $this->wildcardCompare($this->getValue(), $values);
    }

    /**
     * @deprecated
     */
    protected function wildcardCompare(string $a_str1, string $a_str2): bool
    {
        $pattern = str_replace('*', '.*?', $a_str1);

        return (bool) preg_match("/" . $pattern . "/us", $a_str2);
    }

    public function doesMatch(array $a_data): bool
    {
        if ($this->isPluginActive()) {
            return ilShibbolethRoleAssignmentRules::callPlugin($this->getPluginId(), $a_data);
        }

        if (!isset($a_data[$this->getName()])) {
            return false;
        }

        $values = $a_data[$this->getName()];
        if (is_array($values)) {
            return in_array($this->getValue(), $values);
        }

        $pattern = str_replace('*', '.*?', $this->getValue());

        return (bool) preg_match('/^' . $pattern . '$/us', $values);
    }

    private function read(): void
    {
        if ($this->getRuleId() === 0) {
            return;
        }

        $query = 'SELECT * FROM ' . self::TABLE_NAME . ' ' . 'WHERE rule_id = ' . $this->db->quote(
            $this->getRuleId(),
            'integer'
        );
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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

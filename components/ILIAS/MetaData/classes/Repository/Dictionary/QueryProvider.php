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

namespace ILIAS\MetaData\Repository\Dictionary;

use ILIAS\MetaData\Vocabularies\Dictionary\LOMDictionaryInitiator as LOMVocabInitiator;

class QueryProvider
{
    protected TagFactory $factory;
    protected \ilDBInterface $db;

    public function __construct(
        TagFactory $factory,
        \ilDBInterface $db
    ) {
        $this->factory = $factory;
        $this->db = $db;
    }

    protected function checkTable(string $table): void
    {
        if (
            is_null($this->table($table)) ||
            is_null($this->IDName($table))
        ) {
            throw new \ilMDRepositoryException('Invalid MD table: ' . $table);
        }
    }

    protected function table(string $table): ?string
    {
        return LOMDictionaryInitiator::TABLES[$table] ?? null;
    }

    protected function IDName(string $table): ?string
    {
        return LOMDictionaryInitiator::ID_NAME[$table] ?? null;
    }

    /**
     * Returns the appropriate database tag for a container element
     * with its own table.
     */
    public function tableContainer(
        string $table,
        bool $is_parent = false
    ): Tag {
        $this->checkTable($table);

        $create =
            'INSERT INTO ' . $this->db->quoteIdentifier($this->table($table)) .
            ' (' . $this->db->quoteIdentifier($this->IDName($table)) .
            ', rbac_id, obj_id, obj_type) VALUES (%s, %s, %s, %s)';
        $read =
            'SELECT ' . $this->db->quoteIdentifier($this->IDName($table)) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName($table));
        $delete =
            'DELETE FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        return $this->factory->tag(
            $create,
            $read,
            '',
            $delete,
            $is_parent,
            $this->table($table),
            ExpectedParameter::MD_ID,
            ExpectedParameter::RESSOURCE_IDS,
        );
    }

    /**
     * Returns the appropriate database tag for a container element
     * with its own table, but which has a parent element.
     */
    public function tableContainerWithParent(
        string $table,
        string $parent_type,
        bool $second_parent = false,
        bool $is_parent = false
    ): Tag {
        $this->checkTable($table);

        $create =
            'INSERT INTO ' . $this->db->quoteIdentifier($this->table($table)) .
            ' (' . $this->db->quoteIdentifier($this->IDName($table)) .
            ', parent_type, parent_id, rbac_id, obj_id, obj_type) VALUES (%s, ' .
            $this->db->quote($parent_type, \ilDBConstants::T_TEXT) . ', ' .
            '%s, %s, %s, %s)';
        $read =
            'SELECT ' . $this->db->quoteIdentifier($this->IDName($table)) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE parent_type = ' . $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName($table));
        $delete =
            'DELETE FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND parent_type = ' . $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        $parent = $second_parent ?
            ExpectedParameter::SECOND_PARENT_MD_ID :
            ExpectedParameter::PARENT_MD_ID;
        return $this->factory->tag(
            $create,
            $read,
            '',
            $delete,
            $is_parent,
            $this->table($table),
            ExpectedParameter::MD_ID,
            $parent,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for a container element
     * without its own table.
     * @param string[] $fields
     */
    public function nonTableContainer(
        string $table,
        array $fields
    ): Tag {
        $this->checkTable($table);
        if (empty($fields)) {
            throw new \ilMDRepositoryException(
                'A container element can not be empty.'
            );
        }
        $read_fields = '(';
        foreach ($fields as $field) {
            $read_fields .= 'CHAR_LENGTH(' . $this->db->quoteIdentifier($field) .
                ') > 0 OR ';
        }
        $read_fields = substr($read_fields, 0, -3) . ') AND ';
        $read =
            'SELECT ' . $this->db->quoteIdentifier($this->IDName($table)) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE ' . $read_fields .
            $this->db->quoteIdentifier($this->IDName($table)) . ' = %s AND' .
            ' rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName($table));
        $delete_fields = '';
        foreach ($fields as $field) {
            $delete_fields .= $this->db->quoteIdentifier($field) . " = '', ";
        }
        $delete_fields = substr($delete_fields, 0, -2) . ' ';
        $delete =
            'UPDATE ' . $this->db->quoteIdentifier($this->table($table)) .
            ' SET ' . $delete_fields .
            'WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        return $this->factory->tag(
            '',
            $read,
            '',
            $delete,
            false,
            $this->table($table),
            ExpectedParameter::MD_ID,
            ExpectedParameter::SUPER_MD_ID,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for a container element
     * without its own table, but with a parent.
     * @param string[] $fields
     */
    public function nonTableContainerWithParent(
        string $table,
        array $fields,
        string $parent_type,
        bool $second_parent = false,
    ): Tag {
        $this->checkTable($table);
        if (empty($fields)) {
            throw new \ilMDRepositoryException(
                'A container element can not be empty.'
            );
        }
        $read_fields = '(';
        foreach ($fields as $field) {
            $read_fields .= 'CHAR_LENGTH(' . $this->db->quoteIdentifier($field) .
                ') > 0 OR ';
        }
        $read_fields = substr($read_fields, 0, -3) . ') AND ';
        $read =
            'SELECT ' . $this->db->quoteIdentifier($this->IDName($table)) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE ' . $read_fields . ' parent_type = ' .
            $this->db->quote($parent_type, \ilDBConstants::T_TEXT) . ' AND ' .
            $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName($table));
        $delete_fields = '';
        foreach ($fields as $field) {
            $delete_fields .= $this->db->quoteIdentifier($field) . " = '', ";
        }
        $delete_fields = substr($delete_fields, 0, -2) . ' ';
        $delete =
            'UPDATE ' . $this->db->quoteIdentifier($this->table($table)) .
            ' SET ' . $delete_fields .
            'WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND parent_type = ' . $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        $parent = $second_parent ?
            ExpectedParameter::SECOND_PARENT_MD_ID :
            ExpectedParameter::PARENT_MD_ID;
        return $this->factory->tag(
            '',
            $read,
            '',
            $delete,
            false,
            $this->table($table),
            ExpectedParameter::MD_ID,
            ExpectedParameter::SUPER_MD_ID,
            $parent,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for the technical: orComposite
     * container element, which is a special case.
     */
    public function orComposite(): Tag
    {
        $read =
            'SELECT ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ((SELECT ' . $this->db->quote(LOMDictionaryInitiator::MD_ID_OS, \ilDBConstants::T_INTEGER) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ', parent_type, parent_id, rbac_id, obj_id, obj_type, ' .
            $this->db->quoteIdentifier($this->IDName('requirement')) .
            ' FROM ' . $this->db->quoteIdentifier($this->table('requirement')) .
            ' WHERE (CHAR_LENGTH(operating_system_name) > 0 OR' .
            ' CHAR_LENGTH(os_min_version) > 0 OR CHAR_LENGTH(os_max_version) > 0)' .
            ') UNION (' .
            'SELECT ' . $this->db->quote(LOMDictionaryInitiator::MD_ID_BROWSER, \ilDBConstants::T_INTEGER) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ', parent_type, parent_id, rbac_id, obj_id, obj_type, ' .
            $this->db->quoteIdentifier($this->IDName('requirement')) .
            ' FROM ' . $this->db->quoteIdentifier($this->table('requirement')) .
            ' WHERE (CHAR_LENGTH(browser_name) > 0 OR' .
            ' CHAR_LENGTH(browser_minimum_version) > 0 OR CHAR_LENGTH(browser_maximum_version) > 0)))' .
            " AS u WHERE u.parent_type = 'meta_technical' AND u." .
            $this->db->quoteIdentifier($this->IDName('requirement')) . ' = %s' .
            ' AND u.parent_id = %s AND u.rbac_id = %s AND u.obj_id = %s AND u.obj_type = %s' .
            ' ORDER BY u.' . $this->db->quoteIdentifier($this->IDName('requirement'));
        $delete =
            'UPDATE ' . $this->db->quoteIdentifier($this->table('requirement')) .
            " SET operating_system_name = '', os_min_version = '', os_max_version = ''" .
            " browser_name = '', browser_minimum_version = '', browser_maximum_version = '' " .
            " WHERE parent_type = 'meta_technical' AND " .
            $this->db->quoteIdentifier($this->IDName('requirement')) . ' = %s' .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        return $this->factory->tag(
            '',
            $read,
            '',
            $delete,
            false,
            $this->table('requirement'),
            ExpectedParameter::SUPER_MD_ID,
            ExpectedParameter::SECOND_PARENT_MD_ID,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for the technical: orComposite:
     * type container element, which is a special case.
     */
    public function orCompositeType(): Tag
    {
        return $this->factory->tag(
            '',
            'SELECT %s AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value),
            '',
            '',
            false,
            $this->table('requirement'),
            ExpectedParameter::SUPER_MD_ID
        );
    }

    /**
     * Returns the appropriate database tag for the technical: orComposite:
     * type: value container element, which is a special case.
     */
    public function orCompositeTypeValue(): Tag
    {
        return $this->factory->tag(
            '',
            "SELECT '%s' AS " . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ', CASE %s WHEN ' . $this->db->quote(LOMDictionaryInitiator::MD_ID_OS, \ilDBConstants::T_INTEGER) . ' THEN ' .
            "'operating system'" .
            ' WHEN ' . $this->db->quote(LOMDictionaryInitiator::MD_ID_BROWSER, \ilDBConstants::T_INTEGER) . ' THEN ' .
            "'browser' END AS " . $this->db->quoteIdentifier(ReturnedParameter::DATA->value),
            '',
            '',
            false,
            $this->table('requirement'),
            ExpectedParameter::SUPER_MD_ID,
            ExpectedParameter::SUPER_MD_ID
        );
    }

    /**
     * Returns the appropriate database tag for the technical: orComposite:
     * name container element, which is a special case.
     */
    public function orCompositeName(): Tag
    {
        $read =
            "SELECT '%s' AS " . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table('requirement')) .
            ' WHERE CASE %s WHEN ' . $this->db->quote(LOMDictionaryInitiator::MD_ID_OS, \ilDBConstants::T_INTEGER) .
            ' THEN CHAR_LENGTH(operating_system_name)' .
            ' WHEN ' . $this->db->quote(LOMDictionaryInitiator::MD_ID_BROWSER, \ilDBConstants::T_INTEGER) .
            ' THEN CHAR_LENGTH(browser_name) END > 0 ' .
            " AND parent_type = 'meta_technical' AND " .
            $this->db->quoteIdentifier($this->IDName('requirement')) . ' = %s' .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName('requirement'));
        $delete =
            'UPDATE ' . $this->db->quoteIdentifier($this->table('requirement')) .
            ' SET operating_system_name = CASE %s WHEN ' .
            $this->db->quote(LOMDictionaryInitiator::MD_ID_OS, \ilDBConstants::T_INTEGER) . " THEN ''" .
            " ELSE '' END, " .
            ' browser_name = CASE %s WHEN ' .
            $this->db->quote(LOMDictionaryInitiator::MD_ID_BROWSER, \ilDBConstants::T_INTEGER) . " THEN ''" .
            " ELSE '' END" .
            " WHERE parent_type = 'meta_technical' AND " .
            $this->db->quoteIdentifier($this->IDName('requirement')) . ' = %s' .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        return $this->factory->tag(
            '',
            $read,
            '',
            $delete,
            false,
            $this->table('requirement'),
            ExpectedParameter::SUPER_MD_ID,
            ExpectedParameter::SUPER_MD_ID,
            ExpectedParameter::PARENT_MD_ID,
            ExpectedParameter::SECOND_PARENT_MD_ID,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for data-carrying sub-elements
     * of technical: orComposite element, which are special cases.
     */
    public function orCompositeData(
        string $field_os,
        string $field_browser
    ): Tag {
        $read =
            "SELECT '%s' AS " . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ', CASE %s WHEN ' . $this->db->quote(LOMDictionaryInitiator::MD_ID_OS, \ilDBConstants::T_INTEGER) .
            ' THEN ' . $this->db->quoteIdentifier($field_os) .
            ' WHEN ' . $this->db->quote(LOMDictionaryInitiator::MD_ID_BROWSER, \ilDBConstants::T_INTEGER) .
            ' THEN  ' . $this->db->quoteIdentifier($field_browser) .
            ' END AS ' . $this->db->quoteIdentifier(ReturnedParameter::DATA->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table('requirement')) .
            " WHERE parent_type = 'meta_technical' AND " .
            $this->db->quoteIdentifier($this->IDName('requirement')) . ' = %s' .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName('requirement'));
        $create_and_update =
            'UPDATE ' . $this->db->quoteIdentifier($this->table('requirement')) .
            ' SET ' . $this->db->quoteIdentifier($field_os) . ' = CASE %s WHEN ' .
            $this->db->quote(LOMDictionaryInitiator::MD_ID_OS, \ilDBConstants::T_INTEGER) .
            " THEN %s ELSE '' END, " .
            $this->db->quoteIdentifier($field_browser) . ' = CASE %s WHEN ' .
            $this->db->quote(LOMDictionaryInitiator::MD_ID_BROWSER, \ilDBConstants::T_INTEGER) .
            " THEN %s ELSE '' END" .
            " WHERE parent_type = 'meta_technical' AND " .
            $this->db->quoteIdentifier($this->IDName('requirement')) . ' = %s' .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';
        $delete =
            'UPDATE ' . $this->db->quoteIdentifier($this->table('requirement')) .
            ' SET ' . $this->db->quoteIdentifier($field_os) . ' = CASE %s WHEN ' .
            $this->db->quote(LOMDictionaryInitiator::MD_ID_OS, \ilDBConstants::T_INTEGER) . " THEN ''" .
            " ELSE '' END, " .
            $this->db->quoteIdentifier($field_browser) . ' = CASE %s WHEN ' .
            $this->db->quote(LOMDictionaryInitiator::MD_ID_BROWSER, \ilDBConstants::T_INTEGER) . " THEN ''" .
            " ELSE '' END" .
            " WHERE parent_type = 'meta_technical' AND " .
            $this->db->quoteIdentifier($this->IDName('requirement')) . ' = %s' .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        return $this->factory->tag(
            $create_and_update,
            $read,
            $create_and_update,
            $delete,
            false,
            $this->table('requirement'),
            ExpectedParameter::SUPER_MD_ID,
            ExpectedParameter::DATA,
            ExpectedParameter::SUPER_MD_ID,
            ExpectedParameter::DATA,
            ExpectedParameter::PARENT_MD_ID,
            ExpectedParameter::SECOND_PARENT_MD_ID,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for a container element
     * without its own table, but with a parent, where the corresponding
     * fields are scattered across two tables.
     * @param string[] $first_fields
     * @param string[] $second_fields
     */
    public function nonTableContainerWithParentAcrossTwoTables(
        string $first_table,
        array $first_fields,
        string $second_table,
        array $second_fields,
        string $parent_type,
        bool $second_parent = false
    ): Tag {
        $this->checkTable($first_table);
        $this->checkTable($second_table);
        if (empty($first_fields) || empty($second_fields)) {
            throw new \ilMDRepositoryException(
                'A container element can not be empty.'
            );
        }
        $shared_fields = [
            'parent_type',
            'parent_id',
            'rbac_id',
            'obj_id',
            'obj_type'
        ];
        $join_select = '';
        foreach ($shared_fields as $field) {
            $join_select .= 't1.' . $field . ' AS ' . $field . '_1,' .
                ' t2.' . $field . ' AS ' . $field . '_2, ';
        }
        foreach ($first_fields as $field) {
            $join_select .= 't1.' . $field . ' AS ' . $field . '_1, ';
        }
        foreach ($second_fields as $field) {
            $join_select .= 't2.' . $field . ' AS ' . $field . '_2, ';
        }
        $join_select = substr($join_select, 0, -2);
        $join_condition = '';
        foreach ($shared_fields as $field) {
            $join_condition .= 't1.' . $field . ' = t2.' . $field . ' AND ';
        }
        $join_condition = substr($join_condition, 0, -4);
        $read_fields_1 = '(';
        foreach ($first_fields as $field) {
            $read_fields_1 .= 'CHAR_LENGTH(' .
                $this->db->quoteIdentifier($field . '_1') .
                ') > 0 OR ';
        }
        $read_fields_1 = substr($read_fields_1, 0, -3) . ') AND ';
        $read_fields_2 = '(';
        foreach ($second_fields as $field) {
            $read_fields_2 .= 'CHAR_LENGTH(' .
                $this->db->quoteIdentifier($field . '_2') .
                ') > 0 OR ';
        }
        $read_fields_2 = substr($read_fields_2, 0, -3) . ') AND ';
        $read =
            'SELECT %s' .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ((SELECT ' . $join_select .
            ' FROM ' . $this->db->quoteIdentifier($this->table($first_table)) .
            ' AS t1 LEFT OUTER JOIN ' . $this->db->quoteIdentifier($this->table($second_table)) .
            ' AS t2 ON ' . $join_condition . ') UNION (' .
            'SELECT ' . $join_select .
            ' FROM ' . $this->db->quoteIdentifier($this->table($first_table)) .
            ' AS t1 RIGHT OUTER JOIN ' . $this->db->quoteIdentifier($this->table($second_table)) .
            ' AS t2 ON ' . $join_condition .
            ' )) AS t WHERE (' . $read_fields_1 . ' t.parent_type_1 = ' .
            $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND t.parent_id_1 = %s' .
            ' AND t.rbac_id_1 = %s AND t.obj_id_1 = %s AND t.obj_type_1 = %s)' .
            ' OR (' . $read_fields_2 . ' t.parent_type_2 = ' .
            $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND t.parent_id_2 = %s' .
            ' AND t.rbac_id_2 = %s AND t.obj_id_2 = %s AND t.obj_type_2 = %s)' .
            ' ORDER BY t.parent_id_1, t.parent_id_2';

        $parent = $second_parent ?
            ExpectedParameter::SECOND_PARENT_MD_ID :
            ExpectedParameter::PARENT_MD_ID;
        return $this->factory->tag(
            '',
            $read,
            '',
            '',
            false,
            '',
            $parent,
            $parent,
            ExpectedParameter::RESSOURCE_IDS,
            $parent,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for a data element
     * without its own table, but where a parent has to be given.
     */
    public function dataWithParent(
        string $table,
        string $field,
        string $parent_type,
        bool $second_parent = false
    ): Tag {
        $this->checkTable($table);

        $create_and_update =
            'UPDATE ' . $this->db->quoteIdentifier($this->table($table)) .
            ' SET ' . $this->db->quoteIdentifier($field) . ' = %s' .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND parent_type = ' . $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';
        $read =
            'SELECT ' . $this->db->quoteIdentifier($field) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::DATA->value) . ', ' .
            $this->db->quoteIdentifier($this->IDName($table)) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) .
            ' = %s AND parent_type = ' .
            $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName($table));
        $delete =
            'UPDATE ' . $this->db->quoteIdentifier($this->table($table)) .
            ' SET ' . $this->db->quoteIdentifier($field) . " = ''" .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND parent_type = ' . $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        $parent = $second_parent ?
            ExpectedParameter::SECOND_PARENT_MD_ID :
            ExpectedParameter::PARENT_MD_ID;
        return $this->factory->tag(
            $create_and_update,
            $read,
            $create_and_update,
            $delete,
            false,
            $this->table($table),
            ExpectedParameter::DATA,
            ExpectedParameter::SUPER_MD_ID,
            $parent,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for a data element
     * with its own table.
     */
    public function tableData(
        string $table,
        string $field
    ): Tag {
        $this->checkTable($table);

        $create =
            'INSERT INTO ' . $this->db->quoteIdentifier($this->table($table)) .
            ' (' . $this->db->quoteIdentifier($field) . ', ' .
            $this->db->quoteIdentifier($this->IDName($table)) .
            ', rbac_id, obj_id, obj_type) VALUES (%s, %s, %s, %s, %s)';
        $read =
            'SELECT ' . $this->db->quoteIdentifier($field) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::DATA->value) . ', ' .
            $this->db->quoteIdentifier($this->IDName($table)) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName($table));
        $update =
            'UPDATE ' . $this->db->quoteIdentifier($this->table($table)) .
            ' SET ' . $this->db->quoteIdentifier($field) . ' = %s' .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND rbac_id = %s AND obj_id = %s AND obj_type = %s';
        $delete =
            'DELETE FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        return $this->factory->tag(
            $create,
            $read,
            $update,
            $delete,
            false,
            $this->table($table),
            ExpectedParameter::DATA,
            ExpectedParameter::MD_ID,
            ExpectedParameter::PARENT_MD_ID,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for a data element
     * with its own table, and which has a parent element.
     */
    public function tableDataWithParent(
        string $table,
        string $field,
        string $parent_type,
        bool $second_parent = false
    ): Tag {
        $this->checkTable($table);

        $create =
            'INSERT INTO ' . $this->db->quoteIdentifier($this->table($table)) .
            ' (' . $this->db->quoteIdentifier($field) . ', ' .
            $this->db->quoteIdentifier($this->IDName($table)) .
            ', parent_type, parent_id, rbac_id, obj_id, obj_type) VALUES (%s, %s, ' .
            $this->db->quote($parent_type, \ilDBConstants::T_TEXT) . ', ' .
            '%s, %s, %s, %s)';
        $read =
            'SELECT ' . $this->db->quoteIdentifier($field) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::DATA->value) . ', ' .
            $this->db->quoteIdentifier($this->IDName($table)) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE parent_type = ' .
            $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName($table));
        $update =
            'UPDATE ' . $this->db->quoteIdentifier($this->table($table)) .
            ' SET ' . $this->db->quoteIdentifier($field) . ' = %s' .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND parent_type = ' . $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';
        $delete =
            'DELETE FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND parent_type = ' . $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
            ' AND parent_id = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        return $this->factory->tag(
            $create,
            $read,
            $update,
            $delete,
            false,
            $this->table($table),
            ExpectedParameter::DATA,
            ExpectedParameter::MD_ID,
            $second_parent ?
            ExpectedParameter::SECOND_PARENT_MD_ID :
            ExpectedParameter::PARENT_MD_ID,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    /**
     * Returns the appropriate database tag for a data element
     * without its own table.
     */
    public function data(
        string $table,
        string $field
    ): Tag {
        $this->checkTable($table);

        $create_and_update =
            'UPDATE ' . $this->db->quoteIdentifier($this->table($table)) .
            ' SET ' . $this->db->quoteIdentifier($field) . ' = %s' .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND rbac_id = %s AND obj_id = %s AND obj_type = %s';
        $read =
            'SELECT ' . $this->db->quoteIdentifier($field) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::DATA->value) . ', ' .
            $this->db->quoteIdentifier($this->IDName($table)) .
            ' AS ' . $this->db->quoteIdentifier(ReturnedParameter::MD_ID->value) .
            ' FROM ' . $this->db->quoteIdentifier($this->table($table)) .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) .
            ' = %s AND rbac_id = %s AND obj_id = %s AND obj_type = %s' .
            ' ORDER BY ' . $this->db->quoteIdentifier($this->IDName($table));
        $delete =
            'UPDATE ' . $this->db->quoteIdentifier($this->table($table)) .
            ' SET ' . $this->db->quoteIdentifier($field) . " = ''" .
            ' WHERE ' . $this->db->quoteIdentifier($this->IDName($table)) . ' = %s' .
            ' AND rbac_id = %s AND obj_id = %s AND obj_type = %s';

        return $this->factory->tag(
            $create_and_update,
            $read,
            $create_and_update,
            $delete,
            false,
            $this->table($table),
            ExpectedParameter::DATA,
            ExpectedParameter::SUPER_MD_ID,
            ExpectedParameter::RESSOURCE_IDS
        );
    }

    public function vocabSource(): Tag
    {
        return $this->factory->tag(
            '',
            "SELECT '" . LOMVocabInitiator::SOURCE .
            "' AS " . ReturnedParameter::DATA->value . ', 0 AS ' . ReturnedParameter::MD_ID->value,
            '',
            '',
            false,
            ''
        );
    }
}

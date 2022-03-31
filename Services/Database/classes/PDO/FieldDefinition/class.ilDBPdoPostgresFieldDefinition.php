<?php declare(strict_types=1);

/**
 * Class ilDBPdoPostgresFieldDefinition
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoPostgresFieldDefinition extends ilDBPdoFieldDefinition
{
    protected array $options = array(
        'default_text_field_length' => 4096,
        'decimal_places' => 2,
    );

    /**
     * @param $field
     */
    public function getTypeDeclaration($field) : string
    {
        switch ($field['type']) {
            case 'text':
                $length = empty($field['length']) ? $this->options['default_text_field_length'] : $field['length'];
                $fixed = false; // FSX we do not want to have fixed lengths
                if ($fixed) {
                    return $length ? 'CHAR(' . $length . ')' : 'CHAR(' . $this->options['default_text_field_length'] . ')';
                }
                return $length ? 'VARCHAR(' . $length . ')' : 'TEXT';
            case 'clob':
                return 'TEXT';
            case 'blob':
                return 'BYTEA';
            case 'integer':
                if (!empty($field['autoincrement'])) {
                    if (!empty($field['length'])) {
                        $length = $field['length'];
                        if ($length > 4) {
                            return 'BIGSERIAL PRIMARY KEY';
                        }
                    }

                    return 'SERIAL PRIMARY KEY';
                }
                if (!empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 2) {
                        return 'SMALLINT';
                    }

                    if ($length === 3 || $length === 4) {
                        return 'INT';
                    }

                    if ($length > 4) {
                        return 'BIGINT';
                    }
                }

                return 'INT';
            case 'boolean':
                return 'BOOLEAN';
            case 'date':
                return 'DATE';
            case 'time':
                return 'TIME without time zone';
            case 'timestamp':
                return 'TIMESTAMP without time zone';
            case 'float':
                return 'FLOAT8';
            case 'decimal':
                $length = empty($field['length']) ? 18 : $field['length'];
                $scale = empty($field['scale']) ? $this->options['decimal_places'] : $field['scale'];

                return 'NUMERIC(' . $length . ',' . $scale . ')';
        }
        return '';
    }

    /**
     * @param $name
     * @param $field
     * @throws \ilDatabaseException
     */
    protected function getIntegerDeclaration($name, $field) : string
    {
        $db = $this->getDBInstance();

        if (!empty($field['autoincrement'])) {
            $name = $db->quoteIdentifier($name, true);

            return $name . ' ' . $this->getTypeDeclaration($field);
        }
        $default = '';
        if (array_key_exists('default', $field)) {
            if ($field['default'] === '') {
                $field['default'] = empty($field['notnull']) ? null : 0;
            }
            $default = ' DEFAULT ' . $this->quote($field['default'], 'integer');
        } elseif (empty($field['notnull'])) {
            $default = ' DEFAULT NULL';
        }

        $notnull = empty($field['notnull']) ? '' : ' NOT NULL';
        $name = $db->quoteIdentifier($name, true);

        return $name . ' ' . $this->getTypeDeclaration($field) . $default . $notnull;
    }

    /**
     * @throws \ilDatabaseException
     */
    protected function mapNativeDatatypeInternal($field) : array
    {
        $db_type = strtolower($field['type']);
        $length = $field['length'];
        $type = array();
        $unsigned = $fixed = null;
        switch ($db_type) {
            case 'smallint':
            case 'int2':
                $type[] = 'integer';
                $unsigned = false;
                $length = 2;
                if ($length == '2') {
                    $type[] = 'boolean';
                    if (preg_match('/^(is|has)/', $field['name'])) {
                        $type = array_reverse($type);
                    }
                }
                break;
            case 'int':
            case 'int4':
            case 'integer':
            case 'serial':
            case 'serial4':
                $type[] = 'integer';
                $unsigned = false;
                $length = 4;
                break;
            case 'bigint':
            case 'int8':
            case 'bigserial':
            case 'serial8':
                $type[] = 'integer';
                $unsigned = false;
                $length = 8;
                break;
            case 'bool':
            case 'boolean':
                $type[] = 'boolean';
                $length = null;
                break;
            case 'text':
            case 'varchar':
                $fixed = false;
            // no break
            case 'unknown':
            case 'char':
            case 'bpchar':
                $type[] = 'text';
                if ($length === '1') {
                    $type[] = 'boolean';
                    if (preg_match('/^(is|has)/', $field['name'])) {
                        $type = array_reverse($type);
                    }
                } elseif (strpos($db_type, 'text') !== false) {
                    $type[] = 'clob';
                }
                if ($fixed !== false) {
                    $fixed = true;
                }
                break;
            case 'date':
                $type[] = 'date';
                $length = null;
                break;
            case 'datetime':
            case 'timestamp':
                $type[] = 'timestamp';
                $length = null;
                break;
            case 'time':
                $type[] = 'time';
                $length = null;
                break;
            case 'float':
            case 'float8':
            case 'double':
            case 'real':
                $type[] = 'float';
                break;
            case 'decimal':
            case 'money':
            case 'numeric':
                $type[] = 'decimal';
                if ($field['scale']) {
                    $length = $length . ',' . $field['scale'];
                }
                break;
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
            case 'blob':
            case 'bytea':
                $type[] = 'blob';
                $length = null;
                break;
            case 'oid':
                $type[] = 'blob';
                $type[] = 'clob';
                $length = null;
                break;
            case 'year':
                $type[] = 'integer';
                $type[] = 'date';
                $length = null;
                break;
            default:
                throw new ilDatabaseException('unknown database attribute type: ' . $db_type);
        }

        if ((int) $length <= 0) {
            $length = null;
        }

        return array($type, $length, $unsigned, $fixed);
    }
}

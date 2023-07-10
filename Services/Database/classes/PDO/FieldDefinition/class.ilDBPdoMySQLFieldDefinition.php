<?php

declare(strict_types=1);

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
 * Class ilDBPdoMySQLFieldDefinition
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoMySQLFieldDefinition extends ilDBPdoFieldDefinition
{
    public function getTypeDeclaration(array $field): string
    {
        $db = $this->getDBInstance();

        switch ($field['type']) {
            case 'text':
                if (empty($field['length']) && array_key_exists('default', $field)) {
                    $field['length'] = $db->varchar_max_length ?? null;
                }
                $length = empty($field['length']) ? false : $field['length'];
                $fixed = empty($field['fixed']) ? false : $field['fixed'];
                if ($fixed) {
                    return $length ? 'CHAR(' . $length . ')' : 'CHAR(255)';
                }
                return $length ? 'VARCHAR(' . $length . ')' : 'TEXT';

            case 'clob':
                if (!empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 255) {
                        return 'TINYTEXT';
                    }

                    if ($length <= 65532) {
                        return 'TEXT';
                    }

                    if ($length <= 16_777_215) {
                        return 'MEDIUMTEXT';
                    }
                }

                return 'LONGTEXT';
            case 'blob':
                if (!empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 255) {
                        return 'TINYBLOB';
                    }

                    if ($length <= 65532) {
                        return 'BLOB';
                    }

                    if ($length <= 16_777_215) {
                        return 'MEDIUMBLOB';
                    }
                }

                return 'LONGBLOB';
            case 'integer':
                if (!empty($field['length'])) {
                    $length = $field['length'];
                    if ($length <= 1) {
                        return 'TINYINT';
                    }

                    if ($length === 2) {
                        return 'SMALLINT';
                    }

                    if ($length === 3) {
                        return 'MEDIUMINT';
                    }

                    if ($length === 4) {
                        return 'INT';
                    }

                    if ($length > 4) {
                        return 'BIGINT';
                    }
                }

                return 'INT';
            case 'boolean':
                return 'TINYINT(1)';
            case 'date':
                return 'DATE';
            case 'time':
                return 'TIME';
            case 'timestamp':
                return 'DATETIME';
            case 'float':
                return 'DOUBLE';
            case 'decimal':
                $length = empty($field['length']) ? 18 : $field['length'];
                $scale = empty($field['scale']) ? $db->options['decimal_places'] : $field['scale'];

                return 'DECIMAL(' . $length . ',' . $scale . ')';
        }

        return '';
    }


    /**
     * @throws \ilDatabaseException
     */
    protected function getIntegerDeclaration(string $name, array $field): string
    {
        $db = $this->getDBInstance();

        $default = $autoinc = '';
        if (!empty($field['autoincrement'])) {
            $autoinc = ' AUTO_INCREMENT PRIMARY KEY';
        } elseif (array_key_exists('default', $field)) {
            if ($field['default'] === '') {
                $field['default'] = empty($field['notnull']) ? null : 0;
            }
            $default = ' DEFAULT ' . $this->quote($field['default'], 'integer');
        } elseif (empty($field['notnull'])) {
            $default = ' DEFAULT NULL';
        }

        $notnull = empty($field['notnull']) ? '' : ' NOT NULL';
        $unsigned = empty($field['unsigned']) ? '' : ' UNSIGNED';
        $name = $db->quoteIdentifier($name, true);

        return $name . ' ' . $this->getTypeDeclaration($field) . $unsigned . $default . $notnull . $autoinc;
    }


    /**
     * @throws \ilDatabaseException
     */
    protected function mapNativeDatatypeInternal(array $field): array
    {
        $db_type = strtolower($field['type']);
        $db_type = strtok($db_type, '(), ');
        if ($db_type === 'national') {
            $db_type = strtok('(), ');
        }
        if (!empty($field['length'])) {
            $length = strtok($field['length'], ', ');
            $decimal = strtok(', ');
        } else {
            $length = strtok('(), ');
            $decimal = strtok('(), ');
        }
        $type = array();
        $unsigned = $fixed = null;
        switch ($db_type) {
            case 'tinyint':
                $type[] = 'integer';
                $type[] = 'boolean';
                if (preg_match('/^(is|has)/', $field['name'])) {
                    $type = array_reverse($type);
                }
                $unsigned = preg_match('/ unsigned/i', $field['type']);
                $length = 1;
                break;
            case 'smallint':
                $type[] = 'integer';
                $unsigned = preg_match('/ unsigned/i', $field['type']);
                $length = 2;
                break;
            case 'mediumint':
                $type[] = 'integer';
                $unsigned = preg_match('/ unsigned/i', $field['type']);
                $length = 3;
                break;
            case 'int':
            case 'integer':
                $type[] = 'integer';
                $unsigned = preg_match('/ unsigned/i', $field['type']);
                $length = 4;
                break;
            case 'bigint':
                $type[] = 'integer';
                $unsigned = preg_match('/ unsigned/i', $field['type']);
                $length = 8;
                break;
            case 'tinytext':
            case 'mediumtext':
            case 'longtext':
            case 'text':
            case 'varchar':
                $fixed = false;
                // no break
            case 'string':
            case 'char':
                $type[] = 'text';
                if ($length == '1') {
                    $type[] = 'boolean';
                    if (preg_match('/^(is|has)/', $field['name'])) {
                        $type = array_reverse($type);
                    }
                } elseif (strpos($db_type, 'text') !== false) {
                    $type[] = 'clob';
                    if ($decimal === 'binary') {
                        $type[] = 'blob';
                    }
                }
                if ($fixed !== false) {
                    $fixed = true;
                }
                break;
            case 'enum':
                $type[] = 'text';
                preg_match_all('/\'.+\'/U', $field['type'], $matches);
                $length = 0;
                $fixed = false;
                if (is_array($matches)) {
                    foreach ($matches[0] as $value) {
                        $length = max($length, strlen($value) - 2);
                    }
                    if ($length == '1' && count($matches[0]) === 2) {
                        $type[] = 'boolean';
                        if (preg_match('/^(is|has)/', $field['name'])) {
                            $type = array_reverse($type);
                        }
                    }
                }
                $type[] = 'integer';
                // no break
            case 'set':
                $fixed = false;
                $type[] = 'text';
                $type[] = 'integer';
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
            case 'double':
            case 'real':
                $type[] = 'float';
                $unsigned = preg_match('/ unsigned/i', $field['type']);
                break;
            case 'unknown':
            case 'decimal':
            case 'numeric':
                $type[] = 'decimal';
                $unsigned = preg_match('/ unsigned/i', $field['type']);
                if ($decimal !== false) {
                    $length = $length . ',' . $decimal;
                }
                break;
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
            case 'blob':
                $type[] = 'blob';
                $length = null;
                break;
            case 'binary':
            case 'varbinary':
                $type[] = 'blob';
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

        return array( $type, $length, $unsigned, $fixed );
    }
}

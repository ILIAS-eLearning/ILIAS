<?php
require_once('class.ilDBPdoFieldDefinition.php');

/**
 * Class ilDBPdoPostgresFieldDefinition
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoPostgresFieldDefinition extends ilDBPdoFieldDefinition {

	/**
	 * @param $type
	 * @param $field_name
	 * @param array $field_info
	 * @return string
	 */
	public function getDeclaration($type, $field_name, array $field_info) {
		$query = $field_name . ' ' . $this->getTypeDeclaration($type, $field_info);

		switch ($type) {
			case self::T_INTEGER:
				$default = $autoinc = '';
				if (!empty($field_info['autoincrement'])) {
					$autoinc = ' AUTO_INCREMENT PRIMARY KEY';
				} elseif (array_key_exists('default', $field_info)) {
					if ($field_info['default'] === '') {
						$field_info['default'] = empty($field_info['notnull']) ? null : 0;
					}
					$default = ' DEFAULT ' . $this->db_instance->quote($field_info['default'], self::T_INTEGER);
				} elseif (empty($field_info['notnull'])) {
					$default = ' DEFAULT NULL';
				}

				$notnull = empty($field_info['notnull']) ? '' : ' NOT NULL';
				$unsigned = empty($field_info['unsigned']) ? '' : ' UNSIGNED';

				$declaration_options = $unsigned . $default . $notnull . $autoinc;

				break;

			case self::T_CLOB:
			case self::T_BLOB:
				$declaration_options = '';
				break;

			default:
				$declaration_options = $this->getDeclarationOptions($field_info);
				break;
		}

		$field_declaration = $query . $declaration_options;

		return $field_declaration;
	}


	/**
	 * @param $type
	 * @param array $field
	 * @return null
	 */
	public function getTypeDeclaration($type, array $field) {
		$db = $this->db_instance;

		switch ($field['type']) {
			case 'text':
				$length = !empty($field['length']) ? $field['length'] : $db->options['default_text_field_length'];
				$fixed = !empty($field['fixed']) ? $field['fixed'] : false;

				return $fixed ? ($length ? 'CHAR(' . $length . ')' : 'CHAR(' . $db->options['default_text_field_length']
				                                                     . ')') : ($length ? 'VARCHAR(' . $length . ')' : 'TEXT');
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
					} elseif ($length == 3 || $length == 4) {
						return 'INT';
					} elseif ($length > 4) {
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
				$length = !empty($field['length']) ? $field['length'] : 18;
				$scale = !empty($field['scale']) ? $field['scale'] : $db->options['decimal_places'];

				return 'NUMERIC(' . $length . ',' . $scale . ')';
		}
	}


	/**
	 * @param $field
	 * @return array
	 * @throws \ilDatabaseException
	 */
	public function mapNativeDatatype($field) {
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
			case 'unknown':
			case 'char':
			case 'bpchar':
				$type[] = 'text';
				if ($length == '1') {
					$type[] = 'boolean';
					if (preg_match('/^(is|has)/', $field['name'])) {
						$type = array_reverse($type);
					}
				} elseif (strstr($db_type, 'text')) {
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

		if ((int)$length <= 0) {
			$length = null;
		}

		return array( $type, $length, $unsigned, $fixed );
	}
}

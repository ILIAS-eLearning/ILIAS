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
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class arConverter
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 * @description         $arConverter = new arConverter('my_msql_table_name', 'arMyRecordClass');
 *                      $arConverter->readStructure();
 *                      $arConverter->downloadClassFile();
 */
class arConverter
{
    public const REGEX = "/([a-z]*)\\(([0-9]*)\\)/u";
    protected static array $field_map = array(
        'varchar' => arField::FIELD_TYPE_TEXT,
        'char' => arField::FIELD_TYPE_TEXT,
        'int' => arField::FIELD_TYPE_INTEGER,
        'tinyint' => arField::FIELD_TYPE_INTEGER,
        'smallint' => arField::FIELD_TYPE_INTEGER,
        'mediumint' => arField::FIELD_TYPE_INTEGER,
        'bigint' => arField::FIELD_TYPE_INTEGER,
    );
    protected static array $length_map = array(
        arField::FIELD_TYPE_TEXT => false,
        arField::FIELD_TYPE_DATE => false,
        arField::FIELD_TYPE_TIME => false,
        arField::FIELD_TYPE_TIMESTAMP => false,
        arField::FIELD_TYPE_CLOB => false,
        arField::FIELD_TYPE_FLOAT => false,
        arField::FIELD_TYPE_INTEGER => array(
            11 => 4,
            4 => 1,
        )
    );
    protected string $table_name = '';
    protected string $class_name = '';
    protected array $structure = array();
    protected array $ids = array();

    public function __construct(string $table_name, string $class_name)
    {
        $this->setClassName($class_name);
        $this->setTableName($table_name);
        $this->readStructure();
    }

    public function readStructure(): void
    {
        $sql = 'DESCRIBE ' . $this->getTableName();
        $res = self::getDB()->query($sql);
        while ($data = self::getDB()->fetchObject($res)) {
            $this->addStructure($data);
        }
    }

    public function downloadClassFile(): void
    {
        $header = "<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class {CLASS_NAME}
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.6
 */
class {CLASS_NAME} extends ActiveRecord {

	/**
	 * @return string
	 * @deprecated
	 */
	static function returnDbTableName() {
		return '{TABLE_NAME}';
	}

	/**
	 * @return string
	 */
	public function getConnectorContainerName() {
		return '{TABLE_NAME}';
	}
";
        $txt = str_replace(
            ['{CLASS_NAME}', '{TABLE_NAME}'],
            [$this->getClassName(), $this->getTableName()],
            $header
        );
        $all_members = '';
        foreach ($this->getStructure() as $str) {
            $member = "/**
	 * @var {DECLARATION}
	 *\n";
            foreach ($this->returnAttributesForField($str) as $name => $value) {
                $member .= '	 * @con_' . $name . ' ' . $value . "\n";
            }

            $member .= "*/
	protected \${FIELD_NAME};

	";

            $member = str_replace(['{FIELD_NAME}', '{DECLARATION}'], [$str->Field, ' '], $member);

            $all_members .= $member;
        }
        $txt = $txt . $all_members . '
}

?>';

        //		echo '<pre>' . print_r(, 1) . '</pre>';

        header('Content-type: application/x-httpd-php');
        header("Content-Disposition: attachment; filename=\"class." . $this->getClassName() . ".php\"");
        echo $txt;
        exit;
    }

    /**
     * @return array<string, string>
     */
    protected function returnAttributesForField(stdClass $field): array
    {
        $attributes = array();
        $attributes[arFieldList::HAS_FIELD] = 'true';
        $attributes[arFieldList::FIELDTYPE] = self::lookupFieldType($field->Type);
        $attributes[arFieldList::LENGTH] = self::lookupFieldLength($field->Type);

        if ($field->Null === 'NO') {
            $attributes[arFieldList::IS_NOTNULL] = 'true';
        }

        if ($field->Key === 'PRI') {
            $attributes[arFieldList::IS_PRIMARY] = 'true';
        }

        return $attributes;
    }

    protected static function lookupFieldType(string $field_name): string
    {
        preg_match(self::REGEX, $field_name, $matches);

        return self::$field_map[$matches[1]];
    }

    /**
     * @return mixed|void
     */
    protected static function lookupFieldLength(string $field_name)
    {
        $field_type = self::lookupFieldType($field_name);

        preg_match(self::REGEX, $field_name, $matches);

        if (self::$length_map[$field_type][$matches[2]]) {
            return self::$length_map[$field_type][$matches[2]];
        }

        return $matches[2];
    }

    public static function getDB(): ilDBInterface
    {
        global $DIC;

        return $DIC['ilDB'];
    }

    public function setTableName(string $table_name): void
    {
        $this->table_name = $table_name;
    }

    public function getTableName(): string
    {
        return $this->table_name;
    }

    /**
     * @param mixed[] $structure
     */
    public function setStructure(array $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * @return mixed[]
     */
    public function getStructure(): array
    {
        return $this->structure;
    }

    public function addStructure(stdClass $structure): void
    {
        if (!in_array($structure->Field, $this->ids)) {
            $this->structure[] = $structure;
            $this->ids[] = $structure->Field;
        }
    }

    public function setClassName(string $class_name): void
    {
        $this->class_name = $class_name;
    }

    public function getClassName(): string
    {
        return $this->class_name;
    }
}

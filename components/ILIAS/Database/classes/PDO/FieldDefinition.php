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

namespace ILIAS\Database;

interface FieldDefinition
{
    public const INDEX_FORMAT = '%s_idx';
    public const SEQUENCE_COLUMNS_NAME = 'sequence';
    public const SEQUENCE_FORMAT = '%s_seq';
    public const T_BLOB = 'blob';
    public const T_CLOB = 'clob';
    public const T_DATE = 'date';
    public const T_DATETIME = 'datetime';
    public const T_FLOAT = 'float';
    public const T_INTEGER = 'integer';
    public const T_TEXT = 'text';
    public const T_TIME = 'time';
    public const T_TIMESTAMP = 'timestamp';

    public function checkTableName(string $table_name): bool;
    public function isReserved(string $table_name): bool;
    public function getAllReserved(): array;
    public function getReservedMysql(): array;
    public function setReservedMysql(array $reserved_mysql): void;
    public function checkColumnName(string $column_name): bool;
    public function checkIndexName(string $a_name): bool;
    public function checkColumnDefinition(array $a_def): bool;
    public function isAllowedAttribute(string $attribute, string $type): bool;
    public function getAvailableTypes(): array;
    public function setAvailableTypes(array $available_types): void;
    public function getAllowedAttributes(): array;
    public function setAllowedAttributes(array $allowed_attributes): void;
    public function getMaxLength(): array;
    public function setMaxLength(array $max_length): void;
    public function getValidTypes(): array;
    public function getDeclaration(string $type, string $name, array $field);
    public function getTypeDeclaration(array $field): string;
    public function compareDefinition(array $current, array $previous): array;
    public function quote($value, ?string $type = null, bool $quote = true, bool $escape_wildcards = false): string;
    public function writeLOBToFile($lob, string $file): bool;
    public function destroyLOB($lob): bool;
    public function matchPattern(array $pattern, $operator = null, $field = null): string;
    public function patternEscapeString(): string;
    public function mapNativeDatatype(array $field);
    public function mapPrepareDatatype(string $type);
}

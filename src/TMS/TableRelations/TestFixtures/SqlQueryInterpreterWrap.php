<?php

namespace ILIAS\TMS\TableRelations\TestFixtures;

use ILIAS\TMS\Filter as Filters;
use ILIAS\TMS\TableRelations as TR;

class SqlQueryInterpreterWrap extends TR\SqlQueryInterpreter {

	public function _interpretField(Filters\Predicates\Field $field) {
		return $this->interpretField($field);
	}

	public function _interpretTable(TR\Tables\AbstractTable $table) {
		return $this->interpretTable($table);
	}
}


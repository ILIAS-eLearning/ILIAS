<?php
namespace ILIAS\RuleEngine\Example\UserOrgUnitToStudyProgramExample;

use ILIAS\RuleEngine\Entity\Entity;
use ILIAS\RuleEngine\Entity\Field;

class ilOrgUnitUserAssignmentEntity extends Entity {

	public function __construct() {
		$this->setTableName('il_orgu_ua');
		$this->addField(new Field('id',Field::FIELD_TYPE_INTEGER));
		$this->addField(new Field('user_id',Field::FIELD_TYPE_INTEGER));
		$this->addField(new Field('position_id',Field::FIELD_TYPE_INTEGER));
		$this->addField(new Field('orgu_id',Field::FIELD_TYPE_INTEGER));
		$this->setPrimaryKey('id');
	}
}
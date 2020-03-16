<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityDocumentCriterionAssignment
 */
class ilAccessibilityDocumentCriterionAssignment extends ActiveRecord implements ilAccessibilityEvaluableCriterion, ilAccessibilityEquatable
{
    const TABLE_NAME = 'acc_criterion_to_doc';

    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           4
     * @db_is_primary       true
     * @con_sequence        true
     */
    protected $id;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $assigned_ts = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $modification_ts = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $doc_id = 0;

    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           255
     * @con_is_notnull      true
     */
    protected $criterion_id = '';

    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           255
     */
    protected $criterion_value = '';

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $owner_usr_id = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $last_modified_usr_id = 0;

    /**
     * @inheritdoc
     */
    public static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @inheritdoc
     */
    public function create()
    {
        $this->setAssignedTs(time());

        parent::create();
    }

    /**
     * @inheritdoc
     */
    public function update()
    {
        $this->setModificationTs(time());

        parent::update();
    }

    /**
     * @param ilAccessibilityCriterionConfig $config
     */
    public function setCriterionValue(ilAccessibilityCriterionConfig $config) : void
    {
        $this->criterion_value = $config->toJson();
    }

    /**
     * @return ilAccessibilityCriterionConfig
     */
    public function getCriterionValue() : ilAccessibilityCriterionConfig
    {
        return new ilAccessibilityCriterionConfig($this->criterion_value);
    }

    /**
     * @return string
     */
    public function getCriterionId() : string
    {
        return $this->criterion_id;
    }

    /**
     * @inheritDoc
     */
    public function equals($other) : bool
    {
        if (!($other instanceof static)) {
            return false;
        }

        $criterionIdCurrent = $this->getCriterionId();
        $criterionIdNew = $other->getCriterionId();

        $valueCurrent = $this->getCriterionValue();
        $valueNew = $other->getCriterionValue();

        $equals = (
            $criterionIdCurrent == $criterionIdNew &&
            $valueCurrent == $valueNew
        );

        return $equals;
    }
}

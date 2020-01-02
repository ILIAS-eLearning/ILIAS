<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentCriterionAssignment
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriterionAssignment extends \ActiveRecord implements \ilTermsOfServiceEvaluableCriterion, \ilTermsOfServiceEquatable
{
    const TABLE_NAME = 'tos_criterion_to_doc';

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
    protected $last_modified_usr_id = '';

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
     * @param \ilTermsOfServiceCriterionConfig $config
     */
    public function setCriterionValue(\ilTermsOfServiceCriterionConfig $config)
    {
        $this->criterion_value = $config->toJson();
    }

    /**
     * @return \ilTermsOfServiceCriterionConfig
     */
    public function getCriterionValue() : \ilTermsOfServiceCriterionConfig
    {
        return new \ilTermsOfServiceCriterionConfig($this->criterion_value);
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

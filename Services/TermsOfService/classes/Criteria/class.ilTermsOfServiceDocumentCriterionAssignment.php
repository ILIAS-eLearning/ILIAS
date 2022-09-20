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
 * Class ilTermsOfServiceDocumentCriterionAssignment
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriterionAssignment extends ActiveRecord implements ilTermsOfServiceEvaluableCriterion, ilTermsOfServiceEquatable
{
    private const TABLE_NAME = 'tos_criterion_to_doc';

    /**
     * @var int
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           4
     * @db_is_primary       true
     * @con_sequence        true
     */
    protected ?int $id = null;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected int $assigned_ts = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected int $modification_ts = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected int $doc_id = 0;

    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           255
     * @con_is_notnull      true
     */
    protected string $criterion_id = '';

    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           255
     */
    protected string $criterion_value;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected int $owner_usr_id = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected int $last_modified_usr_id = 0;

    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function create(): void
    {
        $this->setAssignedTs(time());

        parent::create();
    }

    public function update(): void
    {
        $this->setModificationTs(time());

        parent::update();
    }

    public function setCriterionValue(ilTermsOfServiceCriterionConfig $config): void
    {
        $this->criterion_value = $config->toJson();
    }

    public function getCriterionValue(): ilTermsOfServiceCriterionConfig
    {
        return new ilTermsOfServiceCriterionConfig($this->criterion_value);
    }

    public function getCriterionId(): string
    {
        return $this->criterion_id;
    }

    /**
     * @inheritDoc
     */
    public function equals($other): bool
    {
        if (!($other instanceof static)) {
            return false;
        }

        $criterionIdCurrent = $this->getCriterionId();
        $criterionIdNew = $other->getCriterionId();

        $valueCurrent = $this->getCriterionValue();
        $valueNew = $other->getCriterionValue();

        return $criterionIdCurrent === $criterionIdNew && $valueCurrent == $valueNew;
    }
}

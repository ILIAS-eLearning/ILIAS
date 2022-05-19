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

/**
 * Class ilAccessibilityDocument
 */
class ilAccessibilityDocument extends ActiveRecord implements ilAccessibilitySignableDocument
{
    public const TABLE_NAME = 'acc_documents';

    /**
     * @var int
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           4
     * @db_is_primary       true
     * @con_sequence        true
     */
    protected ?int $id;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected ?int $creation_ts = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected ?int $modification_ts = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected ?int $owner_usr_id = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected ?int $last_modified_usr_id = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected ?int $sorting = 0;

    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           255
     */
    protected ?string $title = '';

    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        clob
     */
    protected ?string $text = '';

    /**
     * @var ilAccessibilityDocumentCriterionAssignment[]
     */
    protected array $criteria = [];

    /**
     * @var ilAccessibilityDocumentCriterionAssignment[]
     */
    protected array $initialPersistedCriteria = [];

    private bool $criteriaFetched = false;

    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }

    public function content() : string
    {
        return $this->text;
    }

    public function title() : string
    {
        return $this->title;
    }

    public function id() : int
    {
        return (int) $this->id;
    }

    public function read() : void
    {
        parent::read();

        $this->fetchAllCriterionAssignments();
    }

    public function buildFromArray(array $array) : \ActiveRecord
    {
        $document = parent::buildFromArray($array);

        $this->fetchAllCriterionAssignments();

        return $document;
    }

    public function create() : void
    {
        $this->setCreationTs(time());

        parent::create();

        foreach ($this->criteria as $criterionAssignment) {
            /** @var $criterionAssignment ilAccessibilityDocumentCriterionAssignment */
            $criterionAssignment->setDocId($this->getId());
            $criterionAssignment->store();
        }

        $this->initialPersistedCriteria = $this->criteria;
    }

    public function update()
    {
        $this->setModificationTs(time());

        foreach ($this->criteria as $criterionAssignment) {
            /** @var $criterionAssignment ilAccessibilityDocumentCriterionAssignment */
            $criterionAssignment->setDocId($this->getId());
            $criterionAssignment->store();
        }

        foreach ($this->initialPersistedCriteria as $criterionAssignment) {
            /** @var $criterionAssignment ilAccessibilityDocumentCriterionAssignment */
            $found = array_filter(
                $this->criteria,
                function (ilAccessibilityDocumentCriterionAssignment $criterionToMatch) use ($criterionAssignment) {
                    return $criterionToMatch->getId() == $criterionAssignment->getId();
                }
            );

            if (0 === count($found)) {
                $criterionAssignment->delete();
            }
        }

        $this->initialPersistedCriteria = $this->criteria;

        parent::update();
    }

    public function delete()
    {
        foreach ($this->initialPersistedCriteria as $criterionAssignment) {
            /** @var $criterionAssignment ilAccessibilityDocumentCriterionAssignment */
            $criterionAssignment->delete();
        }

        $this->initialPersistedCriteria = $this->criteria = [];

        parent::delete();
    }

    public function criteria() : array
    {
        return $this->criteria;
    }

    /**
     * @throws ilAccessibilityDuplicateCriterionAssignmentException
     */
    public function attachCriterion(ilAccessibilityDocumentCriterionAssignment $criterionAssignment) : void
    {
        foreach ($this->criteria as $currentAssignment) {
            /** @var $criterionAssignment ilAccessibilityDocumentCriterionAssignment */
            if ($currentAssignment->equals($criterionAssignment)) {
                throw new ilAccessibilityDuplicateCriterionAssignmentException(sprintf(
                    "Cannot attach duplicate criterion with criterion typeIdent %s and value: %s",
                    $criterionAssignment->getCriterionId(),
                    var_export($criterionAssignment->getCriterionValue(), true)
                ));
            }
        }

        $this->criteria[] = $criterionAssignment;
    }

    /**
     * @throws OutOfBoundsException
     */
    public function detachCriterion(ilAccessibilityDocumentCriterionAssignment $criterionAssignment) : void
    {
        $numCriteriaBeforeRemoval = count($this->criteria);

        $this->criteria = array_filter(
            $this->criteria,
            function (ilAccessibilityDocumentCriterionAssignment $currentAssignment) use ($criterionAssignment) {
                return !$currentAssignment->equals($criterionAssignment);
            }
        );

        $numCriteriaAfterRemoval = count($this->criteria);

        if ($numCriteriaAfterRemoval === $numCriteriaBeforeRemoval) {
            throw new OutOfBoundsException(sprintf(
                "Could not find any criterion with criterion typeIdent %s and value: %s",
                $criterionAssignment->getCriterionId(),
                var_export($criterionAssignment->getCriterionValue(), true)
            ));
        }
    }

    /**
     * Reads all criterion assignments from database
     */
    public function fetchAllCriterionAssignments() : void
    {
        if (!$this->criteriaFetched) {
            $this->criteriaFetched = true;

            $this->initialPersistedCriteria = [];
            $this->criteria = [];

            $criteria = ilAccessibilityDocumentCriterionAssignment::where(['doc_id' => $this->getId()])->get();
            foreach ($criteria as $criterionAssignment) {
                /** @var $criterionAssignment ilAccessibilityDocumentCriterionAssignment */
                $this->criteria[] = $criterionAssignment;
            }

            $this->initialPersistedCriteria = $this->criteria;
        }
    }
}

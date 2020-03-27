<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityDocument
 */
class ilAccessibilityDocument extends ActiveRecord implements ilAccessibilitySignableDocument
{
    const TABLE_NAME = 'acc_documents';

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
    protected $creation_ts = 0;

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
    protected $owner_usr_id = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $last_modified_usr_id = 0;

    /**
     * @var int
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      4
     */
    protected $sorting = 0;

    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           255
     */
    protected $title = '';

    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        clob
     */
    protected $text = '';

    /**
     * @var ilAccessibilityDocumentCriterionAssignment[]
     */
    protected $criteria = [];

    /**
     * @var ilAccessibilityDocumentCriterionAssignment[]
     */
    protected $initialPersistedCriteria = [];

    /**
     * @var bool
     */
    private $criteriaFetched = false;

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
    public function content() : string
    {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function title() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function id() : int
    {
        return (int) $this->id;
    }

    /**
     * @inheritdoc
     */
    public function read()
    {
        parent::read();

        $this->fetchAllCriterionAssignments();
    }

    /**
     * @inheritdoc
     */
    public function buildFromArray(array $array)
    {
        $document = parent::buildFromArray($array);

        $this->fetchAllCriterionAssignments();

        return $document;
    }

    /**
     * @inheritdoc
     */
    public function create()
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function delete()
    {
        foreach ($this->initialPersistedCriteria as $criterionAssignment) {
            /** @var $criterionAssignment ilAccessibilityDocumentCriterionAssignment */
            $criterionAssignment->delete();
        }

        $this->initialPersistedCriteria = $this->criteria = [];

        parent::delete();
    }

    /**
     * @inheritdoc
     */
    public function criteria() : array
    {
        return $this->criteria;
    }

    /**
     * @param ilAccessibilityDocumentCriterionAssignment $criterionAssignment
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
     * @param ilAccessibilityDocumentCriterionAssignment
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

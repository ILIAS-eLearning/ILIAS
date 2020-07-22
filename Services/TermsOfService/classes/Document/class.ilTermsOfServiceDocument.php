<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocument extends ActiveRecord implements \ilTermsOfServiceSignableDocument
{
    const TABLE_NAME = 'tos_documents';

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
    protected $last_modified_usr_id = '';

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
     * @var \ilTermsOfServiceDocumentCriterionAssignment[]
     */
    protected $criteria = [];

    /**
     * @var \ilTermsOfServiceDocumentCriterionAssignment[]
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
        return $this->id;
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
            /** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
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
            /** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
            $criterionAssignment->setDocId($this->getId());
            $criterionAssignment->store();
        }

        foreach ($this->initialPersistedCriteria as $criterionAssignment) {
            /** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
            $found = array_filter($this->criteria, function (\ilTermsOfServiceDocumentCriterionAssignment $criterionToMatch) use ($criterionAssignment) {
                return $criterionToMatch->getId() == $criterionAssignment->getId();
            });

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
            /** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
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
     * @param \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment
     * @throws \ilTermsOfServiceDuplicateCriterionAssignmentException
     */
    public function attachCriterion(\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment)
    {
        foreach ($this->criteria as $currentAssignment) {
            /** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
            if ($currentAssignment->equals($criterionAssignment)) {
                throw new \ilTermsOfServiceDuplicateCriterionAssignmentException(sprintf(
                    "Cannot attach duplicate criterion with criterion typeIdent %s and value: %s",
                    $criterionAssignment->getCriterionId(),
                    var_export($criterionAssignment->getCriterionValue(), 1)
                ));
            }
        }

        $this->criteria[] = $criterionAssignment;
    }

    /**
     * @param \ilTermsOfServiceDocumentCriterionAssignment
     * @throws \OutOfBoundsException
     */
    public function detachCriterion(\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment)
    {
        $numCriteriaBeforeRemoval = count($this->criteria);

        $this->criteria = array_filter($this->criteria, function (\ilTermsOfServiceDocumentCriterionAssignment $currentAssignment) use ($criterionAssignment) {
            return !$currentAssignment->equals($criterionAssignment);
        });

        $numCriteriaAfterRemoval = count($this->criteria);

        if ($numCriteriaAfterRemoval === $numCriteriaBeforeRemoval) {
            throw new \OutOfBoundsException(sprintf(
                "Could not find any criterion with criterion typeIdent %s and value: %s",
                $criterionAssignment->getCriterionId(),
                var_export($criterionAssignment->getCriterionValue(), 1)
            ));
        }
    }

    /**
     * Reads all criterion assignments from database
     */
    public function fetchAllCriterionAssignments()
    {
        if (!$this->criteriaFetched) {
            $this->criteriaFetched = true;

            $this->initialPersistedCriteria = [];
            $this->criteria = [];

            $criteria = \ilTermsOfServiceDocumentCriterionAssignment::where(['doc_id' => $this->getId()])->get();
            foreach ($criteria as $criterionAssignment) {
                /** @var $criterionAssignment \ilTermsOfServiceDocumentCriterionAssignment */
                $this->criteria[] = $criterionAssignment;
            }

            $this->initialPersistedCriteria = $this->criteria;
        }
    }
}

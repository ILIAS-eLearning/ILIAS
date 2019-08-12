<?php
namespace ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection;

use ActiveRecord;
use arConnector;
use ilDateTime;
use ilException;

abstract class AbstractProjectionAr extends ActiveRecord implements ProjectionAr
{


    /**
     * @var int
     *
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     * @con_sequence   true
     */
    protected $id;
    /**
     * @con_has_field true
     * @con_fieldtype timestamp
     */
    protected $created;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_index      true
     * @con_is_notnull true
     */
    protected $question_id;
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     200
     * @con_index      true
     * @con_is_notnull true
     */
    protected $revision_id;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_index      true
     * @con_is_notnull true
     */
    protected $container_obj_id;
    /**
     * @con_has_field        true
     * @con_fieldtype        integer
     * @con_length           1
     * @con_is_notnull       true
     *
     * @var int
     */
    protected $is_current_container_revision  = 1;

    public function __construct($primary_key = 0, arConnector $connector = NULL) {
        global $ilUser;

        $created = new ilDateTime(time(), IL_CAL_UNIX);
        $this->created = $created->get(IL_CAL_DATETIME);

        parent::__construct($primary_key, $connector);
    }


    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId(int $id) : void
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * @param mixed $created
     */
    public function setCreated($created) : void
    {
        $this->created = $created;
    }


    /**
     * @return string
     */
    public function getQuestionId() : string
    {
        return $this->question_id;
    }


    /**
     * @param string $question_id
     */
    public function setQuestionId(string $question_id) : void
    {
        $this->question_id = $question_id;
    }


    /**
     * @return string
     */
    public function getRevisionId() : string
    {
        return $this->revision_id;
    }


    /**
     * @param string $revision_id
     */
    public function setRevisionId(string $revision_id) : void
    {
        $this->revision_id = $revision_id;
    }


    /**
     * @return int
     */
    public function getContainerObjId() : int
    {
        return $this->container_obj_id;
    }


    /**
     * @param int $container_obj_id
     */
    public function setContainerObjId(int $container_obj_id) : void
    {
        $this->container_obj_id = $container_obj_id;
    }


    /**
     * @return int
     */
    public function getIsCurrentContainerRevision() : int
    {
        return $this->is_current_container_revision;
    }


    /**
     * @param int $is_current_container_revision
     */
    public function setIsCurrentContainerRevision(int $is_current_container_revision) : void
    {
        $this->is_current_container_revision = $is_current_container_revision;
    }

    /**
     * @param int $is_current_container_revision
     */
    public function updateIsCurrentContainerRevisionToNo() : void
    {
        $this->is_current_container_revision = 0;
        parent::update();
    }

    //
    // CRUD
    //
    /**
     *
     */
    public function create() {
        parent::create();
    }


    //
    // Not supported CRUD-Options:
    //
    /**
     * @throws ilException
     */
    public function store() {
        throw new ilException("Store is not supported - It's only possible to add new records to this store!");
    }


    /**
     * @throws ilException
     */
    public function update() {
        throw new ilException("Update is not supported - It's only possible to add new records to this store!");
    }

    /**
     * @throws ilException
     */
    public function save() {
        throw new ilException("Save is not supported - It's only possible to add new records to this store!");
    }

}
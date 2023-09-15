<?php
/**
 * Class ilGradebookGradeTotalConfig
 *
 * @author  CPKN <itstaff@cpkn.ca>
 */
class ilGradebookGradeTotalConfig extends ActiveRecord
{

    const TABLE_NAME = 'gradebook_total_grade';
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const EXCEPTIONS = true;
    const TRACE = false;

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     * @db_is_primary   true
     * @db_is_unique    true
     * @db_sequence     true
     * @db_is_notnull   true
     */
    protected $id = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $usr_id = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $revision_id = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    float
     * @db_length       4
     * @db_is_notnull   true
     */
    protected $progress  = 0.00;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    float
     * @db_length       4
     * @db_is_notnull   true
     */
    protected $adjusted_grade  = 0.00;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    float
     * @db_length       4
     * @db_is_notnull   true
     */
    protected $overall_grade  = 0.00;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $status = 0;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $gradebook_id = null;


    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $owner = null;


    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    timestamp
     */
    protected $deleted = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    timestamp
     */
    protected $create_date = null;

    /**
     * @var int
     *
     * @db_has_field    true
     * @db_fieldtype    timestamp
     */
    protected $last_update = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getUsrId()
    {
        return $this->usr_id;
    }

    /**
     * @param int $usr_id
     */
    public function setUsrId($usr_id)
    {
        $this->usr_id = $usr_id;
    }

    /**
     * @return int
     */
    public function getRevisionId()
    {
        return $this->revision_id;
    }

    /**
     * @param int $revision_id
     */
    public function setRevisionId($revision_id)
    {
        $this->revision_id = $revision_id;
    }

    /**
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param int $progress
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;
    }

    /**
     * @return int
     */
    public function getAdjustedGrade()
    {
        return $this->adjusted_grade;
    }

    /**
     * @param int $adjusted_grade
     */
    public function setAdjustedGrade($adjusted_grade)
    {
        $this->adjusted_grade = $adjusted_grade;
    }

    /**
     * @return int
     */
    public function getOverallGrade()
    {
        return $this->overall_grade;
    }

    /**
     * @param int $overall_grade
     */
    public function setOverallGrade($overall_grade)
    {
        $this->overall_grade = $overall_grade;
    }

    /**
     * @return int
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param int $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param int $create_date
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
    }

    /**
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }

    /**
     * @param int $last_update
     */
    public function setLastUpdate($last_update)
    {
        $this->last_update = $last_update;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getGradebookId()
    {
        return $this->gradebook_id;
    }

    /**
     * @param int $gradebook_id
     */
    public function setGradebookId($gradebook_id)
    {
        $this->gradebook_id = $gradebook_id;
    }

    /**
     * returns an instance of gradebook grade total
     * @param $revision_id
     * @param $usr_id
     * @param $gradebook_id
     * @return ActiveRecord|ilGradebookGradeTotalConfig
     */
    public static function firstOrNew($revision_id,$usr_id,$gradebook_id)
    {
        global $ilUser;
        $instance = self::where([
            'revision_id'=>$revision_id,
            'usr_id'=>$usr_id,
            'gradebook_id'=>$gradebook_id
        ])->first();
        if(is_null($instance)){
            $instance = new self();
            $instance->setGradebookId($gradebook_id);
            $instance->setRevisionId($revision_id);
            $instance->setUsrId($usr_id);
            $instance->setRecentlyCreated(TRUE);
            $instance->setOwner($ilUser->getId());
            $instance->setCreateDate(date("Y-m-d H:i:s"));
        }
        return $instance;
    }


}
?>


<?php
/**
 * Class ilGradebookRevisionConfig
 *
 * @author  CPKN <itstaff@cpkn.ca>
 */
class ilGradebookRevisionConfig extends ActiveRecord {

    const TABLE_NAME        = 'gradebook_revisions';
    const DATE_FORMAT       = 'Y-m-d H:i:s';
    const EXCEPTIONS        = true;
    const TRACE             = false;

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    static function returnDbTableName() {
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
    protected $gradebook_id = null;

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
     * @db_fieldtype    integer
     * @db_length       4
     */
    protected $passing_grade = null;

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
     * @db_has_field    FALSE
     */
    protected $containerTypes = ['grp','fold','cat'];

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
    public function getPassingGrade()
    {
        return $this->passing_grade;
    }

    /**
     * @param int $passing_grade
     */
    public function setPassingGrade($passing_grade)
    {
        $this->passing_grade = $passing_grade;
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
     * @param array $attributes
     * @return array
     */
    public function getGradebookObjects($attributes = NULL)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookObjectsConfig.php');
        $objects = ilGradebookObjectsConfig::where([
            'revision_id'=>$this->getRevisionId(),
            'deleted'=>null,
            'gradebook_id'=>$this->gradebook_id
        ]);
        if(!is_null($attributes)){
            return $objects->getArray(NULL,$attributes);
        }else{
            return $objects->getArray();
        }
    }

    /**
     * @param array $attributes
     * @return array
     */
    public function getWeightedGradebookObjects($attributes = NULL)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookObjectsConfig.php');
        $objects = ilGradebookObjectsConfig::where([
            'revision_id'=>$this->getRevisionId(),
            'deleted'=>null,
            'gradebook_id'=>$this->gradebook_id
        ])
            ->where(['object_activated'=>1],'=');
        if(!is_null($attributes)){
            return $objects->getArray(NULL,$attributes);
        }else{
            return $objects->getArray();
        }
    }

    /**
     * @param $usr_id
     * @param int $parent_id
     * @return array
     */
    public function getUsersStructuredGradebook($usr_id,$parent_id = 0)
    {
        $arr = [];
        $objects = $this->getAllChildObjects($parent_id);
        foreach($objects as $k=>$object){
            if($object['object_data_type'] == 'grp'){
                if($this->isMember($object['obj_id'],$usr_id)){
                    $arr[$k]=$object;
                    $arr[$k]['children'] = $this->getUsersStructuredGradebook($usr_id,$object['id']);
                }
            }elseif(in_array($object['object_data_type'],['cat','fold'])) {
                $arr[$k]=$object;
                $arr[$k]['children'] = $this->getUsersStructuredGradebook($usr_id,$object['id']);
            }
            else{
                $arr[$k]=$object;
            }
        }
        return $arr;
    }

    public function isMember($obj_id,$usr_id)
    {
        $participants = ilParticipants::getInstanceByObjId($obj_id);
        return $participants->isMember($usr_id);
    }


    /**
     * @param $depth
     * @return array
     */
    public function getObjectsAtDepth($depth)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookObjectsConfig.php');

        $objects = ilGradebookObjectsConfig::innerjoin('object_data', 'obj_id', 'obj_id',['type'])
        ->where([
            'placement_depth'=>$depth,
            'revision_id'=>$this->getRevisionId(),'deleted'=>null,
            'gradebook_id'=>$this->gradebook_id
        ])->where(['object_activated'=>1],'=');

        return $objects->getArray();

    }



    /**
     * Gets all gradebook objects in a Revision that
     *  A) are weighted
     *  B) are of type grp
     *  C) have weighd objects under them in the tree.
     *  D) The user is a member of.
     * @return array sorted by depth. ( so the lowest depths are calculated first).
     */
    public function getUsersGroupsWithCalculatedGrading($usr_id)
    {
        include_once './Services/Membership/classes/class.ilParticipants.php';
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookObjectsConfig.php');

        //get all objects that are groups and are part of this reivision.
        $objects = ilGradebookObjectsConfig::innerjoin('object_data', 'obj_id', 'obj_id',['type'])
            ->where(['revision_id'=>$this->getRevisionId(),'deleted'=>null,
                'gradebook_id'=>$this->gradebook_id])->where(['type'=>$this->containerTypes],'IN')
            ->where(['object_activated'=>1],'=');

     
        //foreach of the ogroups make sure it has weighted objects under it and
        //the the user is actually a member.
        foreach($objects_arr = $objects->getArray() as $key=>&$object) {
            $children = self::getAllChildObjects($object['id']);
            if($object['object_data_type'] == 'grp'){
                $participants = ilParticipants::getInstanceByObjId($object['obj_id']);
                if(count($children)==0 || !$participants->isMember($usr_id)){
                    unset($objects_arr[$key]);
                }
            }elseif(in_array($object['object_data_type'],['cat','fold'])){
                if(count($children)==0){
                    unset($objects_arr[$key]);
                }
            }
        }
        //sort by the objects placement depth so we always get the lowest depth first.
        //so if higher calculations are based on them they are included.
        usort($objects_arr, function($a, $b) {
            return $a['placement_depth'] < $b['placement_depth']?1:-1;
        });
        return $objects_arr;
    }

    /**
     * @param $parent_object_id
     * @return array
     */
    public function getAllChildObjects($parent_object_id)
    {
        $objects = ilGradebookObjectsConfig::innerjoin('object_data', 'obj_id', 'obj_id',['type','title'])
            ->where(['revision_id'=>$this->getRevisionId(),'deleted'=>null,
                'gradebook_id'=>$this->gradebook_id])
            ->where(['object_activated'=>1],'=')->where(['parent'=>$parent_object_id]);
        return $objects->getArray();
    }

    /**
     * @param $gradebook_id
     * @return int
     */
    public static function getIncrementedRevisionId($gradebook_id)
    {
        $revision = self::where(['gradebook_id'=>$gradebook_id])
            ->where(['deleted'=>null])->orderBy('revision_id','desc')->first();
        return is_object($revision)?(int)$revision->getRevisionId()+1:0;
    }

    /**
     * @param $obj_id
     * @return array
     */
    public function getGradebookObject($obj_id)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookObjectsConfig.php');
        $objects = ilGradebookObjectsConfig::where(['revision_id'=>$this->getRevisionId(),'deleted'=>null,
            'gradebook_id'=>$this->gradebook_id])
            ->where(['object_activated'=>1],'=')->where(['obj_id'=>$obj_id]);
        return array_shift($objects->getArray());
    }

    /**
     * @param $parent_id
     * @return array
     */
    public function getGroupsForParentId($parent_id)
    {
        require_once('./Services/Tracking/classes/gradebook/config/class.ilGradebookObjectsConfig.php');
        $objects = ilGradebookObjectsConfig::innerjoin('object_data', 'obj_id', 'obj_id',['type'])
            ->where(['revision_id'=>$this->getRevisionId(),'deleted'=>null,'type'=>'grp','parent'=>$parent_id,
                'gradebook_id'=>$this->gradebook_id])
            ->where(['object_activated'=>1],'=');
        return $objects->getArray();
    }

}

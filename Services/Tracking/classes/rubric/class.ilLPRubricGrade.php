<?php

/**
 * @author JKN Inc.
 * @copyright 2015
 */

class ilLPRubricGrade
{
    protected $db;
    protected $obj_id;

    private $rubric_id;
    private $rubric_grade_locked;
    private $passing_grade=80;
    private $grade_lock_owner;

    public function __construct($obj_id)
    {
        global $ilDB;

        $this->ilDB=$ilDB;
        $this->obj_id=$obj_id;
    }
    public function getGradeLockOwner()
    {
        return $this->grade_lock_owner;
    }


    public function getPassingGrade()
    {
        return($this->passing_grade);
    }
    public function getRubricGradeLocked()
    {
        return $this->rubric_grade_locked;
    }

    public function getRubricUserGradeData($user_id,$history_id = NULL)
    {
        $data=array();
        if(!is_null($history_id) && $history_id !== 'current'){
            $res=$this->ilDB->query(
                "select d.* from rubric_grade_hist h INNER JOIN rubric_data d on d.deleted = h.create_date
             where
                h.rubric_history_id = ".$this->ilDB->quote($history_id, "integer")." and
                d.rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." and
                d.usr_id=".$this->ilDB->quote($user_id, "integer")
            );
        }else{

        $res=$this->ilDB->query(
            "select
                rubric_criteria_id,criteria_point,criteria_comment
             from rubric_data
             where
                deleted is null and
                rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." and
                usr_id=".$this->ilDB->quote($user_id, "integer")
        );

        }
        while($row=$res->fetchRow(DB_FETCHMODE_OBJECT)){
            array_push($data,array(
                'rubric_criteria_id'=>$row->rubric_criteria_id,
                'criteria_point'=>$row->criteria_point,
                'criteria_comment'=>$row->criteria_comment,
            ));
        }
        return($data);
    }

    public function lockUnlockGrade()
    {
        global $ilUser;
        if(!$this->isGradingLocked()){
            $this->ilDB->manipulateF('INSERT INTO rubric_grade_lock(grade_lock_id,rubric_id,user_id,owner,create_date,last_update)
            VALUES (%s,%s,%s,%s,%s,%s)'
            ,array("integer","integer","integer","integer","timestamp","timestamp"),
                array($this->ilDB->nextId('rubric_grade_lock'),$this->rubric_id,$this->getGradeUserId(),
                    $ilUser->getId(),date("Y-m-d H:i:s"),date("Y-m-d H:i:s"))
            );
        }else{
            $this->ilDB->manipulate("DELETE FROM rubric_grade_lock WHERE rubric_id = ".$this->ilDB->quote($this->rubric_id, "integer")
            ." AND user_id = ".$this->ilDB->quote($this->getGradeUserId(), "integer")
            );
        }
    }

    public function isGradingLocked()
    {
        $user_id = is_null($this->getGradeUserId())?$_GET['user_id']:$this->getGradeUserId();
        $res=$this->ilDB->query(
            "select grade_lock_id,create_date,owner from rubric_grade_lock where rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." AND user_id="
            .$this->ilDB->quote($user_id, "integer")
        );

        $row=$res->fetchRow(DB_FETCHMODE_OBJECT);
        $this->rubric_grade_locked = $row->create_date;
        $this->grade_lock_owner = $row->owner;

        return (is_null($row->grade_lock_id))?false:true;
    }

    private function getRubricCriteriaByGroupId($rubric_group_id)
    {
        $data=array();

        $res=$this->ilDB->query('select rubric_criteria_id,criteria
                                        from rubric_criteria
                                        where
                                            rubric_group_id='.$this->ilDB->quote($rubric_group_id, "integer").' and
                                            deleted is null
                                        order by sort_order
        ');
        while($row=$res->fetchRow(DB_FETCHMODE_OBJECT)){

            array_push($data,array(
                'criteria_id'=>$row->rubric_criteria_id,
                'criteria'=>($row->criteria=='|||incomplete|||')?(''):($row->criteria),
                'behaviors'=>$this->getRubricBehaviorByCriteriaId($row->rubric_criteria_id),
            ));

        }

        return($data);

    }

    private function getRubricWeightsByGroupId($rubric_group_id)
    {
        $data=array();

        $res=$this->ilDB->query(
            'select w.rubric_weight_id,w.rubric_label_id,w.weight_max,w.weight_min
             from rubric_weight w
                inner join rubric_label l on w.rubric_label_id=l.rubric_label_id
             where
                w.rubric_group_id='.$this->ilDB->quote($rubric_group_id, "integer").' and
                w.deleted is null
             order by l.sort_order'
        );

        while($row=$res->fetchRow(DB_FETCHMODE_OBJECT)){
            array_push($data,array(
                'rubric_weight_id'=>$row->rubric_weight_id,
                'rubric_label_id'=>$row->rubric_label_id,
                'weight_max'=>$row->weight_max,
                'weight_min'=>$row->weight_min,
            ));
        }

        return($data);

    }
    private function incrementSequence($table)
    {
        $sequence="";

        //update and get the next sequence
        $this->ilDB->manipulate("update $table set sequence=sequence+1");

        //what is the current sequence
        $set=$this->ilDB->query("select sequence from $table");
        $row=$this->ilDB->fetchAssoc($set);
        $sequence=$row['sequence'];

        if(empty($sequence)){
            $this->ilDB->manipulate("insert into $table (sequence) value (1)");
            $sequence=1;
        }

        return($sequence);
    }


    private function getRubricBehaviorByCriteriaId($rubric_criteria_id)
    {
        $data=array();

        $res=$this->ilDB->query('select b.rubric_behavior_id,b.description
                                        from rubric_behavior b
                                        where
                                            b.rubric_criteria_id='.$this->ilDB->quote($rubric_criteria_id, "integer").' and
                                            b.deleted is null
                                        order by b.sort_order
        ');

        while($row=$res->fetchRow(DB_FETCHMODE_OBJECT)){
            array_push($data,array(
                'behavior_id'=>$row->rubric_behavior_id,
                'description'=>($row->description=='|||incomplete|||')?(''):($row->description),
            ));
        }


        return($data);
    }

    private function getRubricLabels()
    {
        $data=array();

        $res=$this->ilDB->query("select rubric_label_id,label,create_date,last_update from rubric_label where deleted is null and rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." order by sort_order");
        while($row=$res->fetchRow(DB_FETCHMODE_OBJECT)){
            array_push($data,array(
                'rubric_label_id'=>$row->rubric_label_id,
                'label'=>$row->label,
                //'weight'=>$row->weight,
                'create_date'=>$row->create_date,
                'last_update'=>$row->last_update,
            ));
        }

        return($data);
    }


    private function getRubricGroups()
    {
        $data=array();

        $res=$this->ilDB->query(
            'select rubric_group_id,group_name
            from rubric_group
            where
                rubric_id='.$this->ilDB->quote($this->rubric_id, "integer").' and
                deleted is null'
        );

        while($row=$res->fetchRow(DB_FETCHMODE_OBJECT)){

            array_push($data,array(
                'group_id'=>$row->rubric_group_id,
                'group_name'=>($row->group_name=='|||incomplete|||')?(''):($row->group_name),
                'criteria'=>$this->getRubricCriteriaByGroupId($row->rubric_group_id),
                'weights'=>$this->getRubricWeightsByGroupId($row->rubric_group_id),
            ));

        }
        return($data);
    }


    private function getRubricGrader()
    {
        $data = array();
        $res=$this->ilDB->query(
            'select owner from rubric_data where rubric_id='
            .$this->ilDB->quote($this->rubric_id, "integer").
            ' and usr_id ='.$this->ilDB->quote($_SESSION['AccountId'], "integer").
            ' and deleted is null'
        );
        while($row=$res->fetchRow(DB_FETCHMODE_OBJECT)){
            array_push($data,array(
                'grader'=>$row->owner
            ));
        }
        return($data);
    }



    public function grade($rubric_data)
    {
        $grades=$this->getGradePostData($rubric_data);
        $user_id=$this->getGradeUserId();

        // null out grades
        //$this->ilDB->manipulate("update rubric_data set deleted = NOW() where deleted is null and rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." and usr_id=".$this->ilDB->quote($user_id, "integer"));

        $count=0;
        foreach($grades as $criteria_id => $grade){

            // does this grade already exist?
            $set=$this->ilDB->query(
                "select
                        rubric_data_id
                     from rubric_data
                     where
                        deleted IS NULL and
                        rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." and
                        usr_id=".$this->ilDB->quote($user_id, "integer")." and
                        rubric_criteria_id=".$this->ilDB->quote($criteria_id, "integer")

            );
            $row=$this->ilDB->fetchAssoc($set);
            if(!empty($row)){
                //update
                if($grade['point']!=''){
                    $this->ilDB->manipulate(
                        "update rubric_data set
                            rubric_criteria_id=".$this->ilDB->quote($criteria_id, "integer").",
                            criteria_point=".$this->ilDB->quote($grade['point'], "float").",
                            criteria_comment=".$this->ilDB->quote($grade['comment'], "text").",
                            deleted=NULL,
                            last_update=NOW(),
                            owner=".$this->ilDB->quote($_SESSION['AccountId'], "integer")."
                        where rubric_data_id=".$this->ilDB->quote($row['rubric_data_id'], "integer")
                    );
                }else{
                    $this->ilDB->manipulate(
                        "update rubric_data set
                            rubric_criteria_id=".$this->ilDB->quote($criteria_id, "integer").",
                            criteria_point=NULL,
                            criteria_comment=".$this->ilDB->quote($grade['comment'], "text").",
                            deleted=NULL,
                            last_update=NOW(),
                            owner=".$this->ilDB->quote($_SESSION['AccountId'], "integer")."
                        where rubric_data_id=".$this->ilDB->quote($row['rubric_data_id'], "integer")
                    );
                }

            }else{
                //new record, insert
                $new_rubric_data_id=$this->incrementSequence('rubric_data_seq');

                if($grade['point']!=''){
                    $this->ilDB->manipulate(
                        "insert into rubric_data (rubric_data_id,rubric_id,usr_id,rubric_criteria_id,criteria_point,criteria_comment,owner,create_date,last_update) values (
                            ".$this->ilDB->quote($new_rubric_data_id, "integer").",
                            ".$this->ilDB->quote($this->rubric_id, "integer").",
                            ".$this->ilDB->quote($user_id, "integer").",
                            ".$this->ilDB->quote($criteria_id, "integer").",
                            ".$this->ilDB->quote($grade['point'], "float").",
                            ".$this->ilDB->quote($grade['comment'], "text").",
                            ".$this->ilDB->quote($_SESSION['AccountId'], "integer").",
                            NOW(),
                            NOW()

                        )"
                    );

                }else{
                    $this->ilDB->manipulate(
                        "insert into rubric_data (rubric_data_id,rubric_id,usr_id,rubric_criteria_id,criteria_comment,owner,create_date,last_update) values (
                            ".$this->ilDB->quote($new_rubric_data_id, "integer").",
                            ".$this->ilDB->quote($this->rubric_id, "integer").",
                            ".$this->ilDB->quote($user_id, "integer").",
                            ".$this->ilDB->quote($criteria_id, "integer").",
                            ".$this->ilDB->quote($grade['comment'], "text").",
                            ".$this->ilDB->quote($_SESSION['AccountId'], "integer").",
                            NOW(),
                            NOW()

                        )"
                    );
                }
            }
            $count++;
        }
    }

    public function load()
    {
        $data=array();
        $data['groups']=$this->getRubricGroups();
        $data['labels']=$this->getRubricLabels();
        $data['grader'] = $this->getRubricGrader();

        return($data);
    }



    public function objHasRubric()
    {
        $res=$this->ilDB->query(
            "select rubric_id,passing_grade from rubric where obj_id=".$this->ilDB->quote($this->obj_id, "integer")." and deleted is null"
        );
        $row=$res->fetchRow(DB_FETCHMODE_OBJECT);
        if(!empty($row->rubric_id)){
            $this->rubric_id=$row->rubric_id;
            $this->passing_grade=$row->passing_grade;
            return(true);
        }else{
            return(false);
        }

    }

    public function getUserHistory($user_id)
    {
        $history = array();
        $res = $this->ilDB->query("SELECT * FROM (select h.rubric_history_id,d.create_date,d.owner from rubric_grade_hist h INNER JOIN rubric_data d on d.deleted = h.create_date WHERE h.deleted IS NULL AND h.obj_id = ".$this->ilDB->quote($this->obj_id, "integer").
            " AND h.usr_id = ".$this->ilDB->quote($user_id, "integer")." GROUP BY create_date
             UNION ALL
            select
                'current' as rubric_history_id,d.last_update as 'create_date',d.owner
             from rubric_data d
             inner join rubric r on r.rubric_id = d.rubric_id
             where
               d.deleted is null and
               r.obj_id=".$this->ilDB->quote($this->obj_id, "integer")." and
                d.usr_id=".$this->ilDB->quote($user_id, "integer")." GROUP BY create_date) as history ORDER BY create_date DESC"
            );
        while ($record = $this->ilDB->fetchAssoc($res))
        {
            $history[$record['rubric_history_id']] = $record;
        }

        return $history;
    }


    public function isRubricComplete()
    {
        $res=$this->ilDB->query(
            "select rubric_id,passing_grade,locked,owner from rubric where obj_id=".$this->ilDB->quote($this->obj_id, "integer")." and deleted is null and complete = 1"
        );
        $row=$res->fetchRow(DB_FETCHMODE_OBJECT);
        if(!empty($row)) {
            return true;
        }
        else{
            return false;
        }
    }


    private function getGradePostData($rubric_data)
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form=new ilPropertyFormGUI();

        $grades=array();

        foreach($rubric_data['groups'] as $g => $group){

            foreach($group['criteria'] as $c => $criteria){

                $grades[$criteria['criteria_id']]['point']=$form->getInput("Grade${g}_${c}",false);
                $grades[$criteria['criteria_id']]['comment']=$form->getInput("Comment_${g}_${c}",false);
            }

        }
        return($grades);
    }

    private function getGradeUserId()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form=new ilPropertyFormGUI();

        return($form->getInput('user_id',false));
    }

    public function isGradeCompleted()
    {
        $user_id=$this->getGradeUserId();
        $set=$this->ilDB->query("select
                                    rubric_data_id
                                from rubric_data
                                where
                                    deleted is null and
                                    rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." and
                                    usr_id=".$this->ilDB->quote($user_id, "integer")." and
                                    criteria_point is null"
        );

        if($this->ilDB->numRows($set)>0){
            return(false);
        }else{
            return(true);
        }
    }

    /**
     * Prepare For a Regrade of a Rubric
     * @param $obj_id
     * @param $usr_id
     * @return bool
     */
    public static function _prepareForRegrade($obj_id,$usr_id)
    {
        global $ilDB, $ilUser;

        $delete_date = date("Y-m-d H:i:s");
       //try and set deleted on any criteria in rubric_data table where deleted is not null.
        $affected_rows = $ilDB->manipulate("UPDATE rubric_data d INNER JOIN rubric r on d.rubric_id = r.rubric_id SET d.deleted =
                                            ".$ilDB->quote($delete_date,"timestamp")." WHERE d.deleted IS NULL AND d.usr_id = "
                                            .$ilDB->quote($usr_id, "integer")." AND r.obj_id = ".
                                            $ilDB->quote($obj_id, "integer"));
        if($affected_rows > 0){
            //there was a mark prior, we should proceed with preparing things for a regrade.
            include_once 'Services/Tracking/classes/class.ilLPMarks.php';
            include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
            include_once("./Services/Tracking/classes/class.ilLPStatus.php");

            //grab everything from ut_lp_marks for the users obj_id and usr_id, that way we can save it for our own use.
            $marks = new ilLPMarks($obj_id, $usr_id);
            $status = ilLPStatus::_lookupStatus($obj_id,$usr_id);
            $completed = $marks->getCompleted();
            $mark = $marks->getMark();
            $comments = $marks->getComment();

            //Save the UT LP marks for this object. We're using Delete Date for the Create Date so we can inner join to the delete up above so we have a
            //record of all marks.

            $id = $ilDB->nextID('rubric_grade_hist');
            $ilDB->manipulateF("INSERT INTO rubric_grade_hist(rubric_history_id,rubric_id,obj_id,usr_id,status,mark,completed,comments,owner,create_date,last_update) VALUES ".
                " (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                array("integer","integer","integer","integer","integer","float","integer","text","integer","date","date"),
                array($id,self::_lookupRubricId($obj_id),$obj_id,$usr_id,$status,$mark,$completed,$comments,$ilUser->getId(),$delete_date,$delete_date));

            //now that a record is saved delete it from marks, status and exercise.
            $marks->_deleteForUsers($obj_id,array($usr_id));
            ilLPStatus::writeStatus($obj_id,$usr_id,ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);

            //Remove from Ex Assignment
            $ass_id=array_shift(ilExAssignment::getAssignmentDataOfExercise($obj_id));
            $assignment = new ilExAssignment($ass_id['id']);
            $assignment->updateMarkOfUser($ass_id['id'],$usr_id,'');
            $assignment->updateStatusOfUser($ass_id['id'],$usr_id,'notgraded');
            return true;
        } else {
            //there were no marks to begin with OR this was already marked for regrade, so go no further.
            return false;
        }

    }



    public static function _lookupRubricId($obj_id)
    {
        global $ilDB;
        $res=$ilDB->query(
            "select rubric_id from rubric where obj_id=".$ilDB->quote($obj_id, "integer")." and deleted is null"
        );
        $row=$res->fetchRow(DB_FETCHMODE_OBJECT);
        return $row->rubric_id;
    }

    public static function _lookupRubricHistoryLP($rubric_history_id)
    {
        global $ilDB;
        $res=$ilDB->query(
            "select * from rubric_grade_hist where rubric_history_id=".$ilDB->quote($rubric_history_id, "integer")." and deleted is null"
        );
        $row=$res->fetchRow(DB_FETCHMODE_ASSOC);
        return $row;
    }



}
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

    public function getRubricUserGradeData($user_id)
    {
        $data=array();

        $res=$this->ilDB->query(
            "select
                rubric_criteria_id,criteria_point,criteria_comment
             from rubric_data
             where
                deleted is null and
                rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." and
                usr_id=".$this->ilDB->quote($user_id, "integer")
        );
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

        $lock_var = ($this->isGradingLocked())?NULL:date("Y-m-d H:i:s");
        $this->ilDB->manipulate(
            "update rubric set
              grading_locked = ".$this->ilDB->quote($lock_var,"timestamp").
            ",grading_locked_by= ".$this->ilDB->quote($_SESSION['AccountId'], "integer").
            " where obj_id=".$this->ilDB->quote($this->obj_id, "integer")
        );
    }

    public function isGradingLocked()
    {
        $res=$this->ilDB->query(
            "select grading_locked,grading_locked_by from rubric where obj_id=".$this->ilDB->quote($this->obj_id, "integer")." and deleted is null"
        );
        $row=$res->fetchRow(DB_FETCHMODE_OBJECT);
        return (is_null($row->grading_locked))?false:true;
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
        $this->ilDB->manipulate("update rubric_data set deleted = NOW() where rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." and usr_id=".$this->ilDB->quote($user_id, "integer"));

        $count=0;
        foreach($grades as $criteria_id => $grade){

            // does this grade already exist?
            $set=$this->ilDB->query(
                "select
                        rubric_data_id
                     from rubric_data
                     where
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
            "select rubric_id,passing_grade,grading_locked,grading_locked_by from rubric where obj_id=".$this->ilDB->quote($this->obj_id, "integer")." and deleted is null"
        );
        $row=$res->fetchRow(DB_FETCHMODE_OBJECT);
        if(!empty($row->rubric_id)){
            $this->rubric_id=$row->rubric_id;
            $this->passing_grade=$row->passing_grade;
            $this->rubric_grade_locked = $row->grading_locked;

            $this->grade_lock_owner = $row->grading_locked_by;
            return(true);
        }else{
            return(false);
        }

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




}
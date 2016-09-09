<?php

/**
 * @author JKN Inc.
 * @copyright 2015
 */

include_once('./Services/Database/classes/class.ilDB.php');

class ilLPRubricCard
{
    protected $db;
    protected $obj_id;
    
    private $rubric_id;
    private $passing_grade=80;
    private $rubric_locked;
    private $rubric_owner;
    private $rubric_complete;

    const RUBRIC_MODE_GRADER = 50;
    const RUBRIC_MODE_DEVELOPER = 51;


    public function __construct($obj_id)
    {
        global $ilDB;
        
        $this->ilDB=$ilDB;
        $this->obj_id=$obj_id;
    }


    
    public function getPassingGrade()
    {
        return($this->passing_grade);    
    }

    public function getRubricLocked()
    {
        return $this->rubric_locked;
    }
    public function getRubricOwner()
    {
        return $this->rubric_owner;
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

    private function getCardPostData()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form=new ilPropertyFormGUI();
        // gather label values
        $labels=array();        
        for($a=0;$a<7;$a++){
            $tmp_label=$form->getInput("Label$a",false);
            //if(!empty($tmp_label)){
            if(isset($_POST["Label$a"])){
                array_push($labels,array('label'=>$tmp_label));                
            }else{
                break;
            }                                    
        }
        //is the rubric complete?
        $this->rubric_complete=$form->getInput('complete',false);
        // gather passing grade
        $this->passing_grade=$form->getInput('passing_grade',false);
        
        // gather group, critiera, behavior
        $g=1;// set group increment
        $groups=array();
        $tmp_group=$form->getInput("Group_${g}",false);
        
        if($tmp_group==''){
            $tmp_group='|||incomplete|||';
        }
        
        //while(!empty($tmp_group)){
        while(isset($_POST["Group_${g}"])){
            $c=1;// set criteria increment
                       
            $groups[$g]=array('group_name'=>$tmp_group);
            
            // assign group weights
            $groups[$g]['weights']=array();
            for($a=0;$a<count($labels);$a++){
                array_push($groups[$g]['weights'],$form->getInput("Points${g}_${a}",false));
            }
            
            
            $tmp_criteria=$form->getInput("Criteria_${g}_${c}",false);
            if($tmp_criteria==''){
                $tmp_criteria='|||incomplete|||';
            }
            
            do{
                
                // set array for criteria behaviors
                $groups[$g]['criteria'][$c]=array('criteria_name'=>$tmp_criteria);
                
                for($b=1;$b<=count($labels);$b++){
                    $tmp_behavior=$form->getInput("Behavior_${g}_${c}_${b}",false);
                    if($tmp_behavior==''){
                        $tmp_behavior='|||incomplete|||';
                    }
                    $groups[$g]['criteria'][$c]['behavior'][$b]=array('behavior_name'=>$tmp_behavior);
                }
                
                $c++;
                
                $tmp_criteria=$form->getInput("Criteria_${g}_${c}",false);
                if($tmp_criteria==''){
                    $tmp_criteria='|||incomplete|||';
                }
            }while(isset($_POST["Criteria_${g}_${c}"]));    
            //}while(!empty($tmp_criteria));
            
            $g++;
            
            $tmp_group=$form->getInput("Group_${g}",false);
            
            if($tmp_group==''){
                $tmp_group='|||incomplete|||';
            }
        }
        return(array('groups'=>$groups,'labels'=>$labels));        
    }

    public function save()
    {
        $data=$this->getCardPostData();
        if(!empty($data['groups']))
        {
            $this->saveRubricCardTbl();
            $labels=$this->saveRubricLabelTbl($data['labels']);
            $this->saveRubricGroupTbl($data['groups'],$labels);
            $this->saveRubricCriteriaTbl($data['groups']);
            $this->saveRubricBehaviorTbl($data['groups'],$labels);
        }
    }

    public function load()
    {    
        $data=array();
        $data['groups']=$this->getRubricGroups();
        $data['labels']=$this->getRubricLabels();
        
        return($data);
    }
    
    public function objHasRubric()
    {
        $res=$this->ilDB->query(
            "select rubric_id,passing_grade,locked,owner from rubric where obj_id=".$this->ilDB->quote($this->obj_id, "integer")." and deleted is null"
        );
        $row=$res->fetchRow(DB_FETCHMODE_OBJECT);
        if(!empty($row->rubric_id)){
            $this->rubric_id=$row->rubric_id;
            $this->passing_grade=$row->passing_grade;
            $this->rubric_locked = $row->locked;
            $this->rubric_owner = $row->owner;
            return(true);
        }else{
            return(false);
        }
    }


    public function lockUnlock()
    {

        $lock_var = ($this->isLocked())?NULL:date("Y-m-d H:i:s");
        $this->ilDB->manipulate(
            "update rubric set
              locked = ".$this->ilDB->quote($lock_var,"timestamp").
              ",owner = ".$this->ilDB->quote($_SESSION['AccountId'], "integer").
              " where obj_id=".$this->ilDB->quote($this->obj_id, "integer")
        );
    }

    public function isLocked()
    {
        $res=$this->ilDB->query(
            "select locked from rubric where obj_id=".$this->ilDB->quote($this->obj_id, "integer")." and deleted is null"
        );
        $row=$res->fetchRow(DB_FETCHMODE_OBJECT);
        return (is_null($row->locked))?false:true;
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
    
    
    private function saveRubricBehaviorTbl($data,$labels)
    {
        //null out behaviors
        $this->ilDB->manipulate(
            "update rubric_behavior as b 
                inner join rubric_criteria as c on b.rubric_criteria_id=c.rubric_criteria_id
                inner join rubric_group as g on c.rubric_group_id=g.rubric_group_id
             set 
                b.deleted=NOW()
             where
                g.rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")
        );
        
        // insert or update behaviors
        foreach($data as $new_sort_order => $new_group_name){
            
            //new criteria
            foreach($new_group_name['criteria'] as $_new_sort_order => $new_criteria_name){
                
                foreach($new_criteria_name['behavior'] as $__new_sort_order => $behavior_name){
                    //does this new behavior already exist for this criteria?
                    $set=$this->ilDB->query(
                        "select 
                            c.rubric_criteria_id,b.rubric_behavior_id,b.sort_order
                         from rubric_behavior b 
                            inner join rubric_criteria c on b.rubric_criteria_id=c.rubric_criteria_id
                            inner join rubric_group as g on c.rubric_group_id=g.rubric_group_id 
                         where 
                            c.deleted is null and
                            b.description=".$this->ilDB->quote($behavior_name['behavior_name'], "text")." and
                            c.criteria=".$this->ilDB->quote($new_criteria_name['criteria_name'], "text")." and 
                            g.rubric_id=".$this->ilDB->quote($this->rubric_id, "integer")." and
                            b.sort_order=".$this->ilDB->quote($__new_sort_order, "integer")." and
                            c.sort_order=".$this->ilDB->quote($_new_sort_order, "integer")." and
                            g.sort_order=".$this->ilDB->quote($new_sort_order,"integer")
                            
                    );
                    $row=$this->ilDB->fetchAssoc($set);
                    
                    //if this behavior exists, check to see if it is the correct label
                    $is_new_behavior=true;
                    if(count($row)>0){                    
                        $this->ilDB->manipulate("update rubric_behavior set deleted=null where rubric_behavior_id=".$this->ilDB->quote($row['rubric_behavior_id'], "integer"));
                        $is_new_behavior=false;
                    }
                    
                    if($is_new_behavior===true){
                        
                        $new_rubric_behavior_id=$this->incrementSequence('rubric_behavior_seq');
                        
                        // get the group id 
                        $set=$this->ilDB->query(
                            "select 
                                c.rubric_criteria_id 
                            from rubric_criteria c
                                inner join rubric_group g on c.rubric_group_id=g.rubric_group_id
                            where 
                                c.deleted is null and 
                                g.rubric_id= ".$this->ilDB->quote($this->rubric_id, "integer")." and 
                                c.criteria=".$this->ilDB->quote($new_criteria_name['criteria_name'], "text")." and
                                g.sort_order=".$this->ilDB->quote($new_sort_order,"integer")." and
                                c.sort_order=".$this->ilDB->quote($_new_sort_order,"integer")
                        );
                        $row_criteria=$this->ilDB->fetchAssoc($set);
                        
                        $this->ilDB->manipulate(
                            "insert into rubric_behavior (rubric_behavior_id,rubric_criteria_id,description,sort_order,owner,create_date,last_update) values (
                                ".$this->ilDB->quote($new_rubric_behavior_id, "integer").",
                                ".$this->ilDB->quote($row_criteria['rubric_criteria_id'], "integer").",                                
                                ".$this->ilDB->quote($behavior_name['behavior_name'], "text").",
                                ".$this->ilDB->quote($__new_sort_order, "integer").",
                                ".$this->ilDB->quote($_SESSION['AccountId'], "integer").",
                                NOW(),
                                NOW()                                
                            )"
                        );
                    }                    
                    
                }
                
            }
            
        }
    }
    
    private function saveRubricCriteriaTbl($data)
    {
        // null out criteria
        $this->ilDB->manipulate("update rubric_criteria as c inner join rubric_group as g on c.rubric_group_id=g.rubric_group_id set c.deleted = NOW() where g.rubric_id=".$this->ilDB->quote($this->rubric_id, "integer"));
        
        // insert or update the criteria
        foreach($data as $new_sort_order => $new_group_name){
            
            //new criteria
            foreach($new_group_name['criteria'] as $_new_sort_order => $new_criteria_name){
                
                //does this new criteria already exist for this group?
                $set=$this->ilDB->query(
                    "select 
                        g.rubric_group_id,c.rubric_criteria_id 
                     from rubric_group g 
                        inner join rubric_criteria c on g.rubric_group_id=c.rubric_group_id 
                     where 
                        g.deleted is null and
                        c.criteria=".$this->ilDB->quote($new_criteria_name['criteria_name'], "text")." and
                        g.group_name=".$this->ilDB->quote($new_group_name['group_name'], "text")." and
                        c.sort_order=".$this->ilDB->quote($_new_sort_order, "integer")." and
                        g.sort_order=".$this->ilDB->quote($new_sort_order,"integer")." and
			            g.rubric_id=".$this->ilDB->quote($this->rubric_id,"integer")
                );
                $row=$this->ilDB->fetchAssoc($set);
                
                if(count($row)>0){
                    
                    //exists, undelete
                    $this->ilDB->manipulate("update rubric_criteria set deleted=null where rubric_criteria_id=".$this->ilDB->quote($row['rubric_criteria_id'], "integer"));
                    
                }else{
                    //doesn't exist, insert
                    $new_rubric_criteria_id=$this->incrementSequence('rubric_criteria_seq');
                    
                    // get the group id 
                    $set=$this->ilDB->query(
                        "select 
                            rubric_group_id 
                        from rubric_group 
                        where 
                            deleted is null and 
                            rubric_id= ".$this->ilDB->quote($this->rubric_id, "integer")." and 
                            group_name=".$this->ilDB->quote($new_group_name['group_name'], "text")." and
                            sort_order=".$this->ilDB->quote($new_sort_order,"integer")
                    );
                    $row_group=$this->ilDB->fetchAssoc($set);
                    
                    $this->ilDB->manipulate(
                        "insert into rubric_criteria (rubric_criteria_id,rubric_group_id,criteria,sort_order,owner,create_date,last_update) values (
                            ".$this->ilDB->quote($new_rubric_criteria_id, "integer").",
                            ".$this->ilDB->quote($row_group['rubric_group_id'], "integer").",
                            ".$this->ilDB->quote($new_criteria_name['criteria_name'], "text").",
                            ".$this->ilDB->quote($_new_sort_order,"integer").",
                            ".$this->ilDB->quote($_SESSION['AccountId'], "integer").",
                            NOW(),
                            NOW()
                        )"
                    );
                }
                
            }// foreach criteria_data
            
        }// foreach data
    }
    
    private function saveRubricGroupTbl($data,$labels)
    {         
        /**
         *      Add/Update Groups and Weights
         */        
        //get the current active groups
        $current_groups=array();
        $set=$this->ilDB->query("select rubric_group_id,group_name,sort_order from rubric_group where deleted is null and rubric_id=".$this->ilDB->quote($this->rubric_id, "integer"));
        while($row=$this->ilDB->fetchAssoc($set)){
            $current_groups[$row['rubric_group_id']]=array('group_name'=>$row['group_name'],'sort_order'=>$row['sort_order']);
        }
        
        // null out groups
        $this->ilDB->manipulate("update rubric_group set deleted=NOW() where deleted is null and rubric_id=".$this->ilDB->quote($this->rubric_id, "integer"));
        
        
               
        
        foreach($data as $new_sort_order => $new_group_name){
            $is_new_group=true;
            
            // does this group already exist
            $current_sort_order=0;
            foreach($current_groups as $rubric_group_id => $current_group_name){
                
                if($new_group_name['group_name']==$current_group_name['group_name']&&$new_sort_order==$current_group_name['sort_order']){
                    
                    $this->ilDB->manipulate("update rubric_group set deleted=null where rubric_group_id=".$this->ilDB->quote($rubric_group_id, "integer"));
                    
                    $is_new_group=false;
                    
                    $this->saveRubricWeightTbl($labels,$rubric_group_id,$new_group_name['weights']);
                    
                }
                $current_sort_order++;
                
            }
            
            if($is_new_group===true){
                
                $new_rubric_group_id=$this->incrementSequence('rubric_group_seq');
                
                $this->ilDB->manipulate(
                    "insert into rubric_group (rubric_group_id,rubric_id,group_name,sort_order,owner,create_date,last_update) values (
                        ".$this->ilDB->quote($new_rubric_group_id, "integer").",
                        ".$this->ilDB->quote($this->rubric_id, "integer").",
                        ".$this->ilDB->quote($new_group_name['group_name'], "text").",
                        ".$this->ilDB->quote($new_sort_order, "integer").",
                        ".$this->ilDB->quote($_SESSION['AccountId'], "integer").",
                        NOW(),
                        NOW()
                    )"
                );
                
                $this->saveRubricWeightTbl($labels,$new_rubric_group_id,$new_group_name['weights']);
                
            }
            
            
            
        }// foreach data
    }
    
    private function saveRubricWeightTbl($labels,$rubric_group_id,$weights)
    {
        // null out weight for group id
        $this->ilDB->manipulate("update rubric_weight set deleted=NOW() where deleted is null and rubric_group_id=".$this->ilDB->quote($rubric_group_id, "integer"));
        //update the weight min/max for this rubric_group_id
        foreach($labels as $k => $label){
            
            $set=$this->ilDB->query(
                "select 
                    rubric_weight_id 
                 from rubric_weight 
                 where 
                    rubric_group_id =".$this->ilDB->quote($rubric_group_id, "integer")." and 
                    rubric_label_id=".$this->ilDB->quote($label['rubric_label_id'], "integer")
            );
            
            $broken_weight=explode('-',$weights[$k]);
            error_log(count($broken_weight));
            if(count($broken_weight) == 1)
            {
                $broken_weight[1]=$broken_weight[0];
            }
            sort($broken_weight);
            
            if($this->ilDB->numRows($set)>0){
                $row=$this->ilDB->fetchAssoc($set);
                //update
                $this->ilDB->manipulate(
                    "update rubric_weight set 
                        deleted=null,
                        last_update=NOW(),
                        owner=".$this->ilDB->quote($_SESSION['AccountId'], "integer").",
                        weight_min=".$this->ilDB->quote($broken_weight[0], "float").",
                        weight_max=".$this->ilDB->quote($broken_weight[1], "float")."
                    where rubric_weight_id=".$this->ilDB->quote($row['rubric_weight_id'], "integer")
                );                            
            }else{
                //insert                            
                $new_rubric_weight_id=$this->incrementSequence('rubric_weight_seq');
                
                $this->ilDB->manipulate(
                    "insert into rubric_weight (rubric_weight_id,rubric_group_id,rubric_label_id,weight_min,weight_max,owner,create_date,last_update) values (
                        ".$this->ilDB->quote($new_rubric_weight_id, "integer").",
                        ".$this->ilDB->quote($rubric_group_id, "integer").",
                        ".$this->ilDB->quote($label['rubric_label_id'], "text").",
                        ".$this->ilDB->quote($broken_weight[0], "float").",
                        ".$this->ilDB->quote($broken_weight[1], "float").",
                        ".$this->ilDB->quote($_SESSION['AccountId'], "integer").",
                        NOW(),
                        NOW()
                    )"
                );
            }
            
            
        }
        
    }

    private function saveRubricLabelTbl($labels)
    {
        /**
         *      Add/Update Rubric Labels
         */
        $new_labels=array(); 
        //get the current active labels
        $current_labels=array();
        $set=$this->ilDB->query("select rubric_label_id,label,sort_order from rubric_label where deleted is null and rubric_id=".$this->ilDB->quote($this->rubric_id, "integer"));
        while($row=$this->ilDB->fetchAssoc($set)){
            $current_labels[$row['rubric_label_id']]=array('label_name'=>$row['label'],'sort_order'=>$row['sort_order']);
        }
        // null out labels
        $this->ilDB->manipulate("update rubric_label set deleted=NOW() where deleted is null and rubric_id=".$this->ilDB->quote($this->rubric_id, "integer"));
        $sort_order=0;
        foreach($labels as $k => $new_label){
                        
            //does this label already exist?
            $is_new_label=true;
                              
            foreach($current_labels as $rubric_label_id => $compare_label){
                
                if($compare_label['label_name']==$new_label['label']&&$compare_label['sort_order']==$sort_order){
                    
                    //nothing has changed, remove deleted status
                    $this->ilDB->manipulate("update rubric_label set deleted=null, sort_order=".$this->ilDB->quote($sort_order, "integer")." where rubric_label_id=".$this->ilDB->quote($rubric_label_id, "integer"));
                    
                    $is_new_label=false;
                    
                    //$labels[$k]['rubric_label_id']=$rubric_label_id;
                    $new_labels[$k]['rubric_label_id']=$rubric_label_id;
                    
                }
                
            }// foreach current_labels
            
            if($is_new_label===true){
                $new_rubric_label_id=$this->incrementSequence('rubric_label_seq');
                
                // now add it in
                $this->ilDB->manipulate(
                    "insert into rubric_label (rubric_label_id,rubric_id,label,sort_order,owner,create_date,last_update) 
                    values (
                        ".$this->ilDB->quote($new_rubric_label_id, "integer").",
                        ".$this->ilDB->quote($this->rubric_id, "integer").",
                        ".$this->ilDB->quote($new_label['label'], "text").",                        
                        ".$this->ilDB->quote($sort_order, "integer").",
                        ".$this->ilDB->quote($_SESSION['AccountId'], "integer").",
                        NOW(),
                        NOW()
                    )"
                );
                //$labels[$k]['rubric_label_id']=$new_rubric_label_id;
                $new_labels[$k]['rubric_label_id']=$new_rubric_label_id;
            }
            
            $sort_order++;
            
        }// foreach label
        //return($labels);
        return($new_labels);
    }
    
    private function saveRubricCardTbl()
    {
        /**
         *      Add/Update Rubric Card
         */
        //is there a rubric already for this?
        $set=$this->ilDB->query("select rubric_id from rubric where obj_id=".$this->ilDB->quote($this->obj_id, "integer")." and deleted is null");
        $row = $this->ilDB->fetchAssoc($set);
        $this->rubric_id=$row['rubric_id'];
        $complete = $this->rubric_complete === 'true' ? 1 : 0;
        if(empty($this->rubric_id)){            
            $this->rubric_id=$this->incrementSequence('rubric_seq');
        }
        // insert or update the rubric        
        $this->ilDB->manipulate(
            "insert into rubric (rubric_id,obj_id,passing_grade,owner,create_date,last_update,complete) values (
                ".$this->ilDB->quote($this->rubric_id, "integer").",                
                ".$this->ilDB->quote($this->obj_id, "integer").",
                ".$this->ilDB->quote($this->passing_grade, "integer").",
                ".$this->ilDB->quote($_SESSION['AccountId'], "integer").",                
                NOW(),
                NOW(),".$complete."
            ) on duplicate key update 
                last_update=NOW(),
                passing_grade=".$this->ilDB->quote($this->passing_grade, "integer").",
                owner=".$this->ilDB->quote($_SESSION['AccountId'], "integer").",
                complete=".$complete
        );
    }

    public function _lookupRubricMode()
    {
        error_log(var_export($this->obj_id,true));
        $set=$this->ilDB->query("select 1 from rubric_data d INNER JOIN rubric r on r.rubric_id = d.rubric_id AND r.obj_id =".$this->ilDB->quote($this->obj_id, "integer")." and d.deleted is null LIMIT 1");
        $row = $this->ilDB->fetchAssoc($set);
        return(is_null($row)?self::RUBRIC_MODE_DEVELOPER:self::RUBRIC_MODE_GRADER);
    }






}

?>

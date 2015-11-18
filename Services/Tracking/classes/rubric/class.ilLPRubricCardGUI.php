<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
 * name table
 *
 * @author Adam MacDonald <adam.macdonald@cpkn.ca>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilLPRubricCardGUI extends ilLPTableBaseGUI
{
    protected $lng;
    protected $tpl;
    protected $rubric_data;
    protected $user_data;
    protected $passing_grade;
    private $student_view=false;

	/**
	 * Constructor
	 */	
    public function __construct()
	{	   
	   global $tpl,$lng;
       $this->lng=$lng;
       $this->tpl=$tpl;       
	}
    
    public function setPassingGrade($passing_grade)
    {
        $this->passing_grade=$passing_grade;        
    }
    
    public function setRubricData($rubric_data)
    {
        $this->rubric_data=$rubric_data;        
    }
    
    public function setUserData($user_data)
    {
        $this->user_data=$user_data;
    }    
    
    private function getRubricGradeFormHeader($user_full_name)
    {   
        // configure the header for content windows
        $rubric_heading_tpl=new ilTemplate('tpl.lp_rubricgrade_heading.html',true,true,'Services/Tracking');
        
        $rubric_heading_tpl->setVariable('RUBRIC_HEADER',$this->lng->txt('trac_rubric'));
        $rubric_heading_tpl->setVariable('USER_FULL_NAME',$user_full_name);
        
        return($rubric_heading_tpl);  
    }
    
    private function getRubricCardFormHeader()
    {   
        // configure the header for content windows
        $rubric_heading_tpl=new ilTemplate('tpl.lp_rubricform_heading.html',true,true,'Services/Tracking');
        
        $rubric_heading_tpl->setVariable('RUBRIC_HEADER',$this->lng->txt('trac_rubric'));
        
        return($rubric_heading_tpl);  
    }
    
    private function getRubricGradeFormCommandRow($form_action,$user_id)
    {
        //configure the command row
        $rubric_commandrow_tpl=new ilTemplate('tpl.lp_rubricgrade_commandrow.html',true,true,'Services/Tracking');        
        $rubric_commandrow_tpl->setVariable('RUBRIC_SAVE',$this->lng->txt('save'));        
        $rubric_commandrow_tpl->setVariable('FORM_ACTION',$form_action);
        $rubric_commandrow_tpl->setVariable('USER_ID',$user_id);        
        
        return($rubric_commandrow_tpl);
    }
    
    private function getRubricCardFormCommandRow($form_action)
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        
        //configure the command row
        $rubric_commandrow_tpl=new ilTemplate('tpl.lp_rubricform_commandrow.html',true,true,'Services/Tracking');        
        $select_prop=new ilSelectInputGUI('Title','selected_cmdrubric');
        $options=array(                      
            'behavior_2'=>$this->lng->txt('rubric_option_behavior_2'),
            'behavior_3'=>$this->lng->txt('rubric_option_behavior_3'),
            'behavior_4'=>$this->lng->txt('rubric_option_behavior_4'),
            'behavior_5'=>$this->lng->txt('rubric_option_behavior_5'),
            'behavior_6'=>$this->lng->txt('rubric_option_behavior_6'),
            'add_group'=>$this->lng->txt('rubric_option_add_group'),
            'del_group'=>$this->lng->txt('rubric_option_del_group'),
            'add_criteria'=>$this->lng->txt('rubric_option_add_criteria'),
            'del_criteria'=>$this->lng->txt('rubric_option_del_criteria'),            
        );
        $select_prop->setOptions($options);        
        $rubric_commandrow_tpl->setVariable('RURBRIC_COMMANDROW_SELECT',$select_prop->render());
        $rubric_commandrow_tpl->setVariable('RUBRIC_SAVE',$this->lng->txt('save'));
        $rubric_commandrow_tpl->setVariable('RUBRIC_CANCEL',$this->lng->txt('cancel'));
        $rubric_commandrow_tpl->setVariable('RUBRIC_EXECUTE',$this->lng->txt('execute'));
        $rubric_commandrow_tpl->setVariable('FORM_ACTION',$form_action);
        $rubric_commandrow_tpl->setVariable('PASSING_GRADE_VALUE',"$this->passing_grade");
        
        return($rubric_commandrow_tpl);
    }
    
    private function getRubricGradeForm()
    {
        $filename=$this->buildGradeTemplate();
        
        //configure the rubric form
        $language_variables=array(
            'TOTAL'=>'rubric_total',
            'LABEL'=>'rubric_label',
            'POINT'=>'rubric_point',
            'GROUP'=>'rubric_group',
            'COMMENT'=>'rubric_comment',
            'GROUP_NAME'=>'rubric_group_name',
            'CRITERIA'=>'rubric_criteria',
            'CRITERIA_NAME'=>'rubric_criteria_name',
            'BEHAVIOR'=>'rubric_behavior',
            'BEHAVIOR_NAME'=>'rubric_behavior_name',
            'EXCELLENT'=>'rubric_label_excellent',
            'GOOD'=>'rubric_label_good',
            'ACCEPTABLE'=>'rubric_label_acceptable',
            'FAIR'=>'rubric_label_fair',
            'POOR'=>'rubric_label_poor',
            'BAD'=>'rubric_label_bad',
            'OVERALL_POINT'=>'rubric_overall_point',
            'NO_POINT'=>'rubric_no_point',
            'GROUP_POINT'=>'rubric_point_range_group',
            'RUBRIC_SAVE'=>'rubric_card_save',
            'OUT_OF'=>'rubric_out_of',            
            'GRAND_TOTAL'=>'rubric_grand_total',
        );        
         
        $rubric_form_tpl=new ilTemplate($filename,true,true,'Services/Tracking');
        
        //load language files        
        foreach($language_variables as $lang_key => $lang_label){
            $rubric_form_tpl->setVariable($lang_key,$this->lng->txt($lang_label));
        }
        
        // remove temporary template file
        unlink($filename);
               
        //return template
        return($rubric_form_tpl);
    }
    
    private function getRubricCardForm()
    {
        //configure the rubric form
        $language_variables=array(
            'TOTAL'=>'rubric_total',
            'LABEL'=>'rubric_label',
            'POINT'=>'rubric_point',
            'GROUP'=>'rubric_group',
            'GROUP_NAME'=>'rubric_group_name',
            'CRITERIA'=>'rubric_criteria',
            'CRITERIA_NAME'=>'rubric_criteria_name',
            'BEHAVIOR'=>'rubric_behavior',
            'BEHAVIOR_NAME'=>'rubric_behavior_name',
            'EXCELLENT'=>'rubric_label_excellent',
            'GOOD'=>'rubric_label_good',
            'ACCEPTABLE'=>'rubric_label_acceptable',
            'FAIR'=>'rubric_label_fair',
            'POOR'=>'rubric_label_poor',
            'BAD'=>'rubric_label_bad',
            'OVERALL_POINT'=>'rubric_overall_point',
            'NO_POINT'=>'rubric_no_point',
        );
        
        $rubric_form_tpl=new ilTemplate('tpl.lp_rubricform.html',true,true,'Services/Tracking');
        
        //load language files        
        foreach($language_variables as $lang_key => $lang_label){
            $rubric_form_tpl->setVariable($lang_key,$this->lng->txt($lang_label));
        }
        
        return($rubric_form_tpl);
    }
    
    private function loadRubricCardForm()
    {
        $filename=$this->buildCompleteTemplate();
        
        //configure the rubric form
        $language_variables=array(
            'TOTAL'=>'rubric_total',
            'LABEL'=>'rubric_label',
            'POINT'=>'rubric_point',
            'GROUP'=>'rubric_group',
            'GROUP_NAME'=>'rubric_group_name',
            'CRITERIA'=>'rubric_criteria',
            'CRITERIA_NAME'=>'rubric_criteria_name',
            'BEHAVIOR'=>'rubric_behavior',
            'BEHAVIOR_NAME'=>'rubric_behavior_name',
            'EXCELLENT'=>'rubric_label_excellent',
            'GOOD'=>'rubric_label_good',
            'ACCEPTABLE'=>'rubric_label_acceptable',
            'FAIR'=>'rubric_label_fair',
            'POOR'=>'rubric_label_poor',
            'BAD'=>'rubric_label_bad',
            'OVERALL_POINT'=>'rubric_overall_point',
            'NO_POINT'=>'rubric_no_point',
            'GROUP_POINT'=>'rubric_point_range_group',
            'RUBRIC_SAVE'=>'rubric_card_save',
        );        
         
        $rubric_form_tpl=new ilTemplate($filename,true,true,'Services/Tracking');
        
        //load language files        
        foreach($language_variables as $lang_key => $lang_label){
            $rubric_form_tpl->setVariable($lang_key,$this->lng->txt($lang_label));
        }
        
        // remove temporary template file
        unlink($filename);
               
        //return template
        return($rubric_form_tpl);
    }
    
    public function getStudentViewHTML($user_full_name)
    {       
        $this->student_view=true;
        
        $rubric_heading_tpl=$this->getRubricGradeFormHeader($user_full_name);
        $rubric_grade_tpl=$this->getRubricGradeForm();
        return($rubric_heading_tpl->get().$rubric_grade_tpl->get());        
    }
        
    public function getRubricGrade($form_action,$user_full_name,$user_id)
    {
        $rubric_heading_tpl=$this->getRubricGradeFormHeader($user_full_name);
        $rubric_commandrow_tpl=$this->getRubricGradeFormCommandRow($form_action,$user_id);
        
        $rubric_grade_tpl=$this->getRubricGradeForm();
        
        
        // append all templates into ilTemplate
        $this->tpl->setContent(
            $rubric_heading_tpl->get().            
            $rubric_commandrow_tpl->get().
            $rubric_grade_tpl->get()            
        );
        
        // add in our javascript file
        $this->tpl->addJavaScript('./Services/Tracking/js/ilRubricCard.js');
    }
    
    
    public function getRubricCard($form_action)
    {        
        // get the required templates
        $rubric_heading_tpl=$this->getRubricCardFormHeader();        
        $rubric_commandrow_tpl=$this->getRubricCardFormCommandRow($form_action);
        
        if(!empty($this->rubric_data)){
            $rubric_form_tpl=$this->loadRubricCardForm();            
        }else{
            $rubric_form_tpl=$this->getRubricCardForm();
        }
        
        // append all templates into ilTemplate
        $this->tpl->setContent(
            $rubric_heading_tpl->get().            
            $rubric_commandrow_tpl->get().
            $rubric_form_tpl->get()            
        ); 
        
        // add in our javascript file
        $this->tpl->addJavaScript('./Services/Tracking/js/ilRubricCard.js');
    }
    
    private function buildGradeBehavior($behavior,$group_increment,$criteria_increment,$behavior_increment)
    {
        $tmp_behavior_name='Behavior_'.$group_increment.'_'.$criteria_increment.'_'.$behavior_increment;
        $tmp_radio_name="Criteria_${group_increment}_${criteria_increment}";
        
        $checked='';
        $class='';
        foreach($this->user_data as $k => $user_data){
            if($user_data['rubric_behavior_id']==$behavior['behavior_id']){
                $checked='checked="checked"';
                $class='class="success"';
                break;
            }
        }
        
        if($this->student_view){
            $tmp_write.="<td scope=\"rowgroup\" $class>                            
                            ${behavior['description']}
                        </td>";
            
        }else{
            $tmp_write="<td scope=\"rowgroup\">
                            <div class=\"radio\">
                                <label>
                                    <input type=\"radio\" name=\"$tmp_radio_name\" value=\"${behavior['behavior_id']}\" onclick=\"updateGrade(this)\" $checked> ${behavior['description']}
                                </label>
                            </div>
                        </td>";
        }
        
        
        return($tmp_write);
        
    }
    
    private function buildTemplateBehavior($behavior,$group_increment,$criteria_increment,$behavior_increment)
    {
        $tmp_behavior_name='Behavior_'.$group_increment.'_'.$criteria_increment.'_'.$behavior_increment;
        
        $tmp_write="<td scope=\"rowgroup\">
                    <div class=\"form-group has-success has-feedback\">
                        <label class=\"control-label\" for=\"behaviorname0_0\">{BEHAVIOR_NAME}</label>
                        <input id=\"${tmp_behavior_name}\" name=\"${tmp_behavior_name}\" type=\"text\" class=\"form-control\" placeholder=\"${behavior['description']}\" value=\"${behavior['description']}\" aria-describedby=\"${tmp_behavior_name}WarningStatus\" onkeyup=\"validate(this)\">
                        <span class=\"glyphicon glyphicon-ok form-control-feedback\" aria-hidden=\"true\"></span>
                        <span id=\"${tmp_behavior_name}WarningStatus\" class=\"sr-only\">(ok)</span>
                    </div>
                </td>";
        
        return($tmp_write);
        
    }
    
    private function buildGradeCriteria($criteria,$group_increment,$criteria_increment)
    {
        $tmp_criteria_name='Criteria_'.$group_increment.'_'.$criteria_increment;
        $tmp_comment_name='Comment_'.$group_increment.'_'.$criteria_increment;
        
        $tmp_write="<td scope=\"rowgroup\">
                        ${criteria['criteria']}
                    </td>";
        $tmp_comment='';
        
        foreach($criteria['behaviors'] as $behavior_increment => $behavior){
            $tmp_write.=$this->buildGradeBehavior($behavior,$group_increment,$criteria_increment,$behavior_increment);
            
            // is there a comment for this behavior?
            foreach($this->user_data as $k => $user_data){
                if($user_data['rubric_behavior_id']==$behavior['behavior_id']){
                    $tmp_comment=$user_data['behavior_comment'];
                }                
            }
                        
        }
        if($this->student_view){
            $tmp_write.="<td scope=\"rowgroup\">                        
                            $tmp_comment 
                        </td>";
        }else{
            $tmp_write.="<td scope=\"rowgroup\">                        
                            <input type=\"text\" name=\"$tmp_comment_name\" value=\"$tmp_comment\" placeholder=\"{COMMENT}\"> 
                        </td>";
        }
        
        return($tmp_write);
        
    }
    
    private function buildTemplateCriteria($criteria,$group_increment,$criteria_increment)
    {
        $tmp_criteria_name='Criteria_'.$group_increment.'_'.$criteria_increment;
        
        $tmp_write="<td scope=\"rowgroup\">
                        <div class=\"form-group has-success has-feedback\">
                            <label class=\"control-label\" for=\"${tmp_criteria_name}\">{CRITERIA_NAME}</label>                        
                            <div class=\"input-group\">
                                <span class=\"input-group-addon\">
                                    <input type=\"checkbox\" id=\"${tmp_criteria_name}_checkbox\">
                                </span>
                                <input id=\"${tmp_criteria_name}\" name=\"${tmp_criteria_name}\" type=\"text\" class=\"form-control\" placeholder=\"${criteria['criteria']}\" value=\"${criteria['criteria']}\" aria-describedby=\"${tmp_group_name}WarningStatus\" onkeyup=\"validate(this)\">                                                        
                            </div>
                            <span class=\"glyphicon glyphicon-ok form-control-feedback\" aria-hidden=\"true\"></span>
                            <span id=\"${tmp_criteria_name}WarningStatus\" class=\"sr-only\">(ok)</span>
                        </div>
                    </td>";
        foreach($criteria['behaviors'] as $behavior_increment => $behavior){
            $tmp_write.=$this->buildTemplateBehavior($behavior,$group_increment,$criteria_increment,$behavior_increment);            
        }
        
        return($tmp_write);
        
    }
    
    private function buildGradeGroup($group,$group_increment)
    {
        $tmp_group_name='Group_'.$group_increment;
        $tmp_row_span=count($group['criteria']);
        $tmp_write="<tr class=\"tblrow1 small\">
                        <td scope=\"rowgroup\" rowspan=\"$tmp_row_span\">
                            ${group['group_name']}
                        </td>";
        foreach($group['criteria'] as $criteria_increment => $criteria){
            if($criteria_increment>0){
                $tmp_write.="<tr class=\"tblrow1 small\">";
            }
            $tmp_write.=$this->buildGradeCriteria($criteria,$group_increment,$criteria_increment);
            $tmp_write.="</tr>";            
        }
        
        
        return($tmp_write);
    }
    
    private function buildTemplateGroup($group,$group_increment)
    {
        $tmp_group_name='Group_'.$group_increment;
        $tmp_row_span=count($group['criteria']);
        $tmp_write="<tr class=\"tblrow1 small\">
                        <td scope=\"rowgroup\" rowspan=\"$tmp_row_span\">
                            <div class=\"form-group has-success has-feedback\">
                                <label class=\"control-label\" for=\"${tmp_group_name}\">{GROUP_NAME}</label>                        
                                <div class=\"input-group\">
                                    <span class=\"input-group-addon\">
                                        <input type=\"checkbox\" id=\"${tmp_group_name}_checkbox\">
                                    </span>
                                    <input id=\"${tmp_group_name}\" name=\"${tmp_group_name}\" type=\"text\" class=\"form-control\" placeholder=\"${group['group_name']}\" value=\"${group['group_name']}\" aria-describedby=\"${tmp_group_name}WarningStatus\" onkeyup=\"validate(this)\">                                                        
                                </div>
                                <span class=\"glyphicon glyphicon-ok form-control-feedback\" aria-hidden=\"true\"></span>
                                <span id=\"${tmp_group_name}WarningStatus\" class=\"sr-only\">(ok)</span>
                            </div>
                        </td>";
        foreach($group['criteria'] as $criteria_increment => $criteria){
            if($criteria_increment>0){
                $tmp_write.="<tr class=\"tblrow1 small\">";
            }
            $tmp_write.=$this->buildTemplateCriteria($criteria,$group_increment,$criteria_increment);
            $tmp_write.="</tr>";            
        }
        
        
        return($tmp_write);
    }
    
    private function getMinMaxLabel()
    {
        $min=$max=0;
        foreach($this->rubric_data['labels'] as $k => $label){
            if($k==0){
                $min=$max=$label['weight'];
            }else{
                if($label['weight']>$max){
                    $max=$label['weight'];
                }
                if($label['weight']<$min){
                    $min=$label['weight'];
                }                
            }
        }
        return(array('min'=>$min,'max'=>$max));
    }
    
    private function buildGradeCard()
    {
        $point_range=$this->getMinMaxLabel();
        
        $colspan=count($this->rubric_data['labels'])+1;
        
        $tmp_write="";
        foreach($this->rubric_data['groups'] as $group_increment => $group){
            
            $min_points=0;
            $max_points=number_format(count($group['criteria'])*$point_range['max'],2);
            
            //calculate min_points
            if(isset($this->user_data)){
                
                foreach($group['criteria'] as $c => $criteria){
                    foreach($criteria['behaviors'] as $b => $behavior){
                        foreach($this->user_data as $u => $user_data){
                            if($user_data['rubric_behavior_id']==$behavior['behavior_id']){
                                //get weigth from label
                                foreach($this->rubric_data['labels'] as $_k => $label){
                                    if($label['rubric_label_id']==$user_data['rubric_label_id']){
                                        $min_points+=$label['weight'];
                                        break;
                                    }                    
                                }
                            }
                        }
                        
                    }
                    
                }
                
            }
            
            $min_points=number_format($min_points,2);            
            
            $tmp_write.=$this->buildGradeGroup($group,$group_increment);
            $tmp_write.="            
                        <tr>
                            <th colspan=\"2\" scope=\"rowgroup\" class=\"text-right\">{TOTAL}</th>
                            <td colspan=\"$colspan\">$min_points {OUT_OF} $max_points</td>                        
                        </tr>";
        }
        return($tmp_write);
        
    }
    
    private function buildTemplateCard()
    {
        $point_range=$this->getMinMaxLabel();
        
        $colspan=count($this->rubric_data['labels']);
        
        $tmp_write="";
        foreach($this->rubric_data['groups'] as $group_increment => $group){
            
            $min_points=number_format(count($group['criteria'])*$point_range['min'],2);
            $max_points=number_format(count($group['criteria'])*$point_range['max'],2);
            
            $tmp_write.=$this->buildTemplateGroup($group,$group_increment);
            $tmp_write.="            
                        <tr>
                            <th colspan=\"2\" scope=\"rowgroup\" class=\"text-right\">{GROUP_POINT}</th>
                            <td colspan=\"$colspan\">$min_points - $max_points</td>
                        
                        </tr>";
        }
        return($tmp_write);
        
    }
    
    
    private function buildTemplateLabels()
    {
        $tmp_write="";
        foreach($this->rubric_data['labels'] as $k => $label){
            $tmp_write.="<th scope=\"col\" class=\"col-sm-2\">
                            <div class=\"form-group has-success has-feedback\">
                                <label class=\"control-label\" for=\"Label${k}\">{LABEL}</label>
                                <input id=\"Label${k}\" name=\"Label${k}\" type=\"text\" class=\"form-control\" placeholder=\"".$label['label']."\" value=\"".$label['label']."\" aria-describedby=\"Label${k}WarningStatus\" onkeyup=\"validate(this)\" onblur=\"recalculate()\">
                                <span class=\"glyphicon glyphicon-ok form-control-feedback\" aria-hidden=\"true\"></span>
                                <span id=\"Label${k}WarningStatus\" class=\"sr-only\">(ok)</span>
                            </div>
                            <div class=\"form-group has-success has-feedback\">
                                <label class=\"control-label\" for=\"Points${k}\">{POINT}</label>
                                <input id=\"Points${k}\" name=\"Points${k}\" type=\"text\" class=\"form-control\" placeholder=\"".$label['weight']."\" value=\"".$label['weight']."\" aria-describedby=\"Points${k}WarningStatus\" onkeyup=\"validate(this)\" onblur=\"recalculate()\">
                                <span class=\"glyphicon glyphicon-ok form-control-feedback\" aria-hidden=\"true\"></span>
                                <span id=\"Points${k}WarningStatus\" class=\"sr-only\">(ok)</span>
                            </div>
                        </th>";
            
            
        }
        return($tmp_write);
        
    }
    
    private function buildGradeLabels()
    {
        $tmp_write="";
        foreach($this->rubric_data['labels'] as $k => $label){
            $tmp_write.="<th scope=\"col\">
                            ".$label['label']." (".$label['weight'].")
                        </th>";
        }
        return($tmp_write);
        
    }
    
    private function buildCompleteTemplate()
    {
        // build min / max point range for overall
        $point_range=$this->getMinMaxLabel();
        $min_points=$max_points=0;
        foreach($this->rubric_data['groups'] as $k => $group){
            $min_points+=count($group['criteria'])*$point_range['min'];
            $max_points+=count($group['criteria'])*$point_range['max'];
        }
        $min_points=number_format($min_points,2);
        $max_points=number_format($max_points,2);
        
        // define colspan for tfoot
        $colspan=count($this->rubric_data['labels']);
        
        // here we temporarily build the template, then destroy it after
        $filename="./Services/Tracking/templates/default/tpl.lp_rubricform_generated_".time().".html";
        
        $write="<div id=\"jkn_div_rubric\" class=\"table-responsive\" style=\"margin-top: 20px;\">
    
                    <table id=\"jkn_table_rubric\" class=\"table table-striped\">
                    
                        <thead>            
                            <tr>                            
                                <th scope=\"col\">
                                    {GROUP}
                                </th>
                                
                                <th scope=\"col\">
                                    {CRITERIA}
                                </th>".
                                $this->buildTemplateLabels().                                
                            "</tr>            
                        </thead>
                        
                        <tfoot>
                            <tr>
                                <th colspan=\"2\">{OVERALL_POINT}</th>
                                <td colspan=\"$colspan\">$min_points - $max_points</td>
                            </tr>
                        </tfoot>
                        
                        <tbody>".
                            $this->buildTemplateCard().
                        "</tbody>
                    </table>
                </div>
            </form>";
        
        file_put_contents($filename,$write);  
        return($filename);
        
    }
    
    private function buildGradeTemplate()
    {        
        $point_range=$this->getMinMaxLabel();
        $max_points=0;
        $min_points=0;
        foreach($this->rubric_data['groups'] as $k => $group){        
            $max_points+=count($group['criteria'])*$point_range['max'];
        }
        $max_points=number_format($max_points,2);
        
        if(isset($this->user_data)){
            foreach($this->user_data as $k => $user_data){
                foreach($this->rubric_data['labels'] as $_k => $label){
                    if($label['rubric_label_id']==$user_data['rubric_label_id']){
                        $min_points+=$label['weight'];
                        break;
                    }                    
                }
            }
        }
        $min_points=number_format($min_points,2);
        
        $colspan=count($this->rubric_data['labels'])+1;
        // here we temporarily build the template, then destroy it after
        $filename="./Services/Tracking/templates/default/tpl.lp_rubricgrade_generated_".time().".html";
        
        $write="<div id=\"jkn_div_rubric\" class=\"table-responsive\">
    
                    <table id=\"jkn_table_rubric\" class=\"table table-striped\">
                    
                        <thead>            
                            <tr>                            
                                <th scope=\"col\">
                                    {GROUP}
                                </th>
                                
                                <th scope=\"col\">
                                    {CRITERIA}
                                </th>".
                                $this->buildGradeLabels().
                                "<th scope=\"col\">
                                    {COMMENT}
                                </th>
                            </tr>            
                        </thead>
                        
                        <tfoot>
                            <tr>
                                <th colspan=\"2\" class=\"text-right\">{GRAND_TOTAL}</th>
                                <td colspan=\"$colspan\">$min_points {OUT_OF} $max_points</td>
                            </tr>
                        </tfoot>
                        
                        <tbody>".
                            $this->buildGradeCard().
                        "</tbody>
                    </table>
                </div>
            </form>";   
        
        file_put_contents($filename,$write);
        return($filename);
        
    }
    
}

?>
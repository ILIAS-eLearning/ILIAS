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
class ilLPRubricGradeGUI extends ilLPTableBaseGUI
{
    protected $lng;
    protected $tpl;
    protected $rubric_data;
    protected $user_data;
    protected $user_history;
    protected $user_history_id;
    protected $passing_grade;
    protected $incomplete;
    protected $rubric_grade_locked;
    protected $grade_lock_owner;
    private $student_view = false;
    private $pdf_view = false;


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
    public function setRubricGradeLocked($rubric_grade_locked)
    {
        $this->rubric_grade_locked=$rubric_grade_locked;
    }

    public function setGradeLockOwner($grade_lock_owner)
    {
        $this->grade_lock_owner = $grade_lock_owner;
    }

    public function setRubricData($rubric_data)
    {
        $this->rubric_data=$rubric_data;
    }

    public function setUserData($user_data)
    {
        $this->user_data=$user_data;
    }

    public function setUserHistory($user_history)
    {
        $this->user_history = $user_history;
    }

    public function setUserHistoryId($user_history_id)
    {
        $this->user_history_id = $user_history_id;
    }

    private function getRubricGradeFormHeader($user_full_name)
    {
        // configure the header for content windows
        $rubric_heading_tpl=new ilTemplate('tpl.lp_rubricgrade_heading.html',true,true,'Services/Tracking');

        if($this->user_history_id === 'current'){
            $version = $this->lng->txt('rubric_current');
        }elseif(!is_null($this->user_history_id)) {
            $version = '('.$this->user_history[$this->user_history_id]['create_date'].')';
        }

        $rubric_heading_tpl->setVariable('RUBRIC_HEADER',$this->lng->txt('trac_rubric').$version);
        $rubric_heading_tpl->setVariable('USER_FULL_NAME',$user_full_name);


        if($this->student_view)
        {
           $tmp_user = ilObjectFactory::getInstanceByObjId($this->rubric_data['grader'][0]['grader'],false);

            if(!empty($tmp_user)) {
                $rubric_heading_tpl->setVariable('RUBRIC_GRADER',' ('.$this->lng->txt('rubric_graded_by').': '.$tmp_user->getFullName().')');
            }
        }
        return($rubric_heading_tpl);
    }

    private function getRubricGradeFormCommandRow($form_action,$user_id)
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        global $ilUser;
        //configure the command row
        $rubric_commandrow_tpl=new ilTemplate('tpl.lp_rubricgrade_commandrow.html',true,true,'Services/Tracking');

        if($this->user_history_id !== 'current' && !is_null($this->user_history_id))
        {
            $rubric_commandrow_tpl->setVariable('HISTORY_DISABLED','disabled');
        }

        $rubric_commandrow_tpl->setVariable('RUBRIC_SAVE',$this->lng->txt('save'));
        $rubric_commandrow_tpl->setVariable('RUBRIC_REGRADE',$this->lng->txt('rubric_regrade'));
        $rubric_commandrow_tpl->setVariable('RUBRIC_EXPORT',$this->lng->txt('rubric_option_export_pdf'));
        if(!is_null($this->rubric_grade_locked)) {
            $rubric_commandrow_tpl->setVariable('RUBRIC_DISABLED','disabled');
            $rubric_commandrow_tpl->setVariable('RUBRIC_LOCK',$this->lng->txt('rubric_card_unlock'));
            $tmp_user = ilObjectFactory::getInstanceByObjId($this->grade_lock_owner, false);

            if($this->grade_lock_owner !== $ilUser->getId())
            {
                $rubric_commandrow_tpl->setVariable('USER_LOCK','disabled');
            }
            ilUtil::sendInfo($this->lng->txt('rubric_locked_grade_info').' '.$tmp_user->getFullName().' '.$this->rubric_grade_locked);
        }else{
            $rubric_commandrow_tpl->setVariable('RUBRIC_LOCK',$this->lng->txt('rubric_card_lock'));
        }


        $select_prop=new ilSelectInputGUI('Title','grader_history');
        $options = array();
        $last_entry = end($this->user_history);

        foreach($this->user_history as $k=>$user_history)
        {
            $grade_text = $user_history == $last_entry?$this->lng->txt('rubric_graded_by'):$this->lng->txt('rubric_regraded_by');
            $options[$user_history['rubric_history_id']] = $user_history['create_date'].' '.$grade_text.' '.ilObject::_lookupTitle($user_history['owner']) ;
        }

        if(!array_key_exists('current',$this->user_history)){
            $options = array('current'=>$this->lng->txt('no_current_rubric_grade'))+$options;
        }
        $select_prop->setOptions($options);
        $select_prop->setValue($this->user_history_id);
        $rubric_commandrow_tpl->setVariable('RUBRIC_COMMANDROW_HISTORY_SELECT',$select_prop->render());
        $rubric_commandrow_tpl->setVariable('RUBRIC_VIEW_HISTORY',$this->lng->txt('view'));
        $rubric_commandrow_tpl->setVariable('FORM_ACTION',$form_action);

        $rubric_commandrow_tpl->setVariable('USER_ID',$user_id);

        return($rubric_commandrow_tpl);
    }

    private function getRubricStudentGradeFormCommandRow($form_action,$user_id)
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        //configure the command row
        $rubric_commandrow_tpl=new ilTemplate('tpl.lp_rubricgrade_student_commandrow.html',true,true,'Services/Tracking');

        $select_prop=new ilSelectInputGUI('Title','grader_history');
        $options = array();
        $last_entry = end($this->user_history);

        foreach($this->user_history as $k=>$user_history)
        {
            $grade_text = $user_history == $last_entry?$this->lng->txt('rubric_graded_by'):$this->lng->txt('rubric_regraded_by');
            $options[$user_history['rubric_history_id']] = $user_history['create_date'].' '.$grade_text.' '.ilObject::_lookupTitle($user_history['owner']) ;
        }

        if(!array_key_exists('current',$this->user_history)){
            $options = array('current'=>$this->lng->txt('no_current_rubric_grade'))+$options;
        }
        $select_prop->setValue($this->user_history_id);

        $select_prop->setOptions($options);
        $rubric_commandrow_tpl->setVariable('RUBRIC_COMMANDROW_HISTORY_SELECT',$select_prop->render());


        $rubric_commandrow_tpl->setVariable('RUBRIC_EXPORT',$this->lng->txt('rubric_option_export_pdf'));
        $rubric_commandrow_tpl->setVariable('RUBRIC_VIEW_HISTORY',$this->lng->txt('view'));
        $rubric_commandrow_tpl->setVariable('FORM_ACTION',$form_action);
        $rubric_commandrow_tpl->setVariable('USER_ID',$user_id);

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
            'GRADE_RANGE'=>'rubric_grade_range',
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


    public function getStudentViewHTML($form_action, $user_full_name, $user_id)
    {
        $this->student_view=true;

        $rubric_heading_tpl=$this->getRubricGradeFormHeader($user_full_name);
        $rubric_grade_tpl=$this->getRubricGradeForm();
        $rubric_commandrow_tpl=$this->getRubricStudentGradeFormCommandRow($form_action,$user_id);
        $this->tpl->addCss('./Services/Tracking/css/ilRubricCard.css');
        return($rubric_heading_tpl->get().$rubric_commandrow_tpl->get().$rubric_grade_tpl->get());
    }

    public function getPDFViewHTML($obj_id)
    {
        $a_obj = ilObjectFactory::getInstanceByObjId($obj_id);

        $rubric_heading_tpl=$this->getRubricGradeFormHeader($a_obj->getTitle());

        $this->pdf_view=true;

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
        $this->tpl->addCss('./Services/Tracking/css/ilRubricCard.css');
    }

    private function buildGradeBehavior($behavior,$group_increment,$criteria_increment,$behavior_increment, $locatorArray, $locator)
    {
        if($this->student_view)
        {
            if($locatorArray[$locator] == true && !$this->incomplete) {
                $tmp_write.="<td class=\"range-flag\" scope=\"rowgroup\">
                        ${behavior['description']}
                    </td>";
            } else {
                $tmp_write.="<td scope=\"rowgroup\">
                        ${behavior['description']}
                    </td>";
            }
        }else{
            if($locatorArray[$locator] == true) {
                $tmp_write.="<td class=\"range-flag\" scope=\"rowgroup\">
                        ${behavior['description']}
                    </td>";
            } else {
                $tmp_write.="<td scope=\"rowgroup\">
                        ${behavior['description']}
                    </td>";
            }
        }
        return($tmp_write);
    }

    private function buildTemplateBehavior($behavior,$group_increment,$criteria_increment,$behavior_increment)
    {
        $tmp_behavior_name='Behavior_'.$group_increment.'_'.$criteria_increment.'_'.$behavior_increment;

        $tmp_write="<td scope=\"rowgroup\">
                    <div class=\"form-group has-success has-feedback\">
                        <label class=\"control-label\" for=\"behaviorname0_0\">{BEHAVIOR_NAME}</label>
                        <input id=\"${tmp_behavior_name}\" name=\"${tmp_behavior_name}\" type=\"text\" class=\"form-control\" placeholder=\"${behavior['description']}\" value=\"${behavior['description']}\" aria-describedby=\"${tmp_behavior_name}WarningStatus\" onkeyup=\"validate(this)\" oninput=\"validate(this)\">
                        <span class=\"glyphicon glyphicon-ok form-control-feedback\" aria-hidden=\"true\"></span>
                        <span id=\"${tmp_behavior_name}WarningStatus\" class=\"sr-only\">(ok)</span>
                    </div>
                </td>";

        return($tmp_write);

    }

    private function buildGradeCriteria($group,$criteria,$group_increment,$criteria_increment)
    {
        $tmp_criteria_name='Criteria_'.$group_increment.'_'.$criteria_increment;
        $tmp_comment_name='Comment_'.$group_increment.'_'.$criteria_increment;

        if(!is_null($this->rubric_grade_locked)){
            $disabled = "disabled='disabled'";
        }elseif(!is_null($this->user_history_id) && $this->user_history_id !=='current')
        {
            $disabled = "disabled='disabled'";
        }else{
            $disabled = '';
        }

        $tmp_write="<td scope=\"rowgroup\">
                        ${criteria['criteria']}
                    </td>";
        $tmp_comment='';
        $tmp_point='';
        //get comment and point value
        foreach($this->user_data as $u => $user_data){
            if($user_data['criteria_point'] == NULL){
                $this->incomplete = true;
            }
            if($user_data['rubric_criteria_id']==$criteria['criteria_id']){
                $tmp_comment=$user_data['criteria_comment'];
                $tmp_point=$user_data['criteria_point'];
            }
        }
        $locatorArray = array();
        $locator = 0;

        foreach($group['weights'] as $weight)
        {
            $locator++;
            if((float)$weight['weight_min']<(float)$weight['weight_max'])
            {
                $min_points=$weight['weight_min'];
                $max_points=$weight['weight_max'];
            }else{
                $min_points=$weight['weight_max'];
                $max_points=$weight['weight_min'];
            }
            if( $tmp_point >= $min_points && $tmp_point <= $max_points)
            {
                //echo "Point:".$tmp_point." ID:".$weight['rubric_weight_id']."</br>";
                $locatorArray[$locator] = 'true';
            }
        }
        //get behaviors
        $locator = 0;
        foreach($criteria['behaviors'] as $behavior_increment => $behavior){
            $locator++;
            $tmp_write.=$this->buildGradeBehavior($behavior,$group_increment,$criteria_increment,$behavior_increment, $locatorArray, $locator);
        }
        if($this->student_view || $this->pdf_view ){
            if($this->incomplete){
                $tmp_point = '';
                $tmp_comment = '';
            }
            $tmp_write.="<td class=\"grade-point\" scope=\"rowgroup\">
                            $tmp_point
                        </td>
                        <td scope=\"rowgroup\">
                            $tmp_comment
                        </td>";
        }else{
            $tmp_id="Grade${group_increment}_${criteria_increment}";
            $tmp_write.="<td scope=\"rowgroup\">
                            <input id=\"${tmp_id}\" $disabled name=\"${tmp_id}\" type=\"text\" class=\"form-control\" placeholder=\"Grade\" value=\"$tmp_point\" onkeyup=\"verifyGrade(this)\" oninput=\"verifyGrade(this)\">
                        </td>
                        <td scope=\"rowgroup\">
                            <textarea name=\"$tmp_comment_name\"  $disabled value=\"$tmp_comment\" class=\"form-control\" placeholder=\"{COMMENT}\">$tmp_comment</textarea>
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
                                <input id=\"${tmp_criteria_name}\" name=\"${tmp_criteria_name}\" type=\"text\" class=\"form-control\" placeholder=\"${criteria['criteria']}\" value=\"${criteria['criteria']}\" aria-describedby=\"${tmp_group_name}WarningStatus\" onkeyup=\"validate(this)\" oninput=\"validate(this)\">
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

    private function buildGradeGroupPoints($weights)
    {
        $tmp_write="<tr class=\"tblrow1 range-row small\">
                        <th>&nbsp;</th>
                        <th style=\"text-align: left;\">Range</th>";

        foreach($weights as $k => $weight){
            $tmp_write.="<th>${weight['weight_min']} - ${weight['weight_max']}";
        }
        $tmp_write.="<th colspan=\"2\">&nbsp;</th>
                    </tr>";
        return($tmp_write);
    }

    private function buildGradeGroup($group,$group_increment)
    {
        $tmp_group_name='Group_'.$group_increment;
        $tmp_row_span=count($group['criteria']);
        $tmp_write="<tr class=\"tblrow1 first-group small\">
                        <td class=\"big-block\" scope=\"rowgroup\" rowspan=\"$tmp_row_span\">
                            ${group['group_name']}
                        </td>";
        foreach($group['criteria'] as $criteria_increment => $criteria){
            if($criteria_increment>0){
                $tmp_write.="<tr class=\"tblrow1 small\">";
            }
            $tmp_write.=$this->buildGradeCriteria($group,$criteria,$group_increment,$criteria_increment);
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
                                    <input id=\"${tmp_group_name}\" name=\"${tmp_group_name}\" type=\"text\" class=\"form-control\" placeholder=\"${group['group_name']}\" value=\"${group['group_name']}\" aria-describedby=\"${tmp_group_name}WarningStatus\" onkeyup=\"validate(this)\" oninput=\"validate(this)\">
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

    private function buildTemplateGroupPoints($weights,$group_id)
    {
        $group_id++;//increment, base 1
        $tmp_script="<script type=\"text/javascript\">
                    $( document ).ready(function() {";
        $tmp_write="<tr class=\"tblrow1 small\">
                        <th scope=\"col\" class=\"col-sm-2\">
                            &nbsp;
                        </th>
                        <th scope=\"col\" class=\"col-sm-2\">
                            &nbsp;
                        </th>";

        foreach($weights as $k => $weight){
            $tmp_name="Points${group_id}_${k}";
            $tmp_write.="<th scope=\"col\">
                            <div class=\"form-group\">
                                <label class=\"control-label\" for=\"$tmp_name\">{POINT}</label>
                            </div>
                           <input id=\"$tmp_name\" name=\"$tmp_name\" type=\"text\" value=\"[${weight['weight_min']},${weight['weight_max']}]\"/>
                        </th>";
        }



        return(array('tmp_write'=>$tmp_write,'tmp_script'=>$tmp_script));

    }

    private function getMinMaxLabel($weights)
    {
        $min_points=$max_points=0;
        foreach($weights as $k => $weight){
            if($k==0){

                if((float)$weight['weight_min']<(float)$weight['weight_max'])
                {
                    $min_points=$weight['weight_min'];
                    $max_points=$weight['weight_max'];
                }else{
                    $min_points=$weight['weight_max'];
                    $max_points=$weight['weight_min'];
                }
            }else{
                if ((float)$weight['weight_min'] < (float)$weight['weight_max']){
                    //weightmin = brokenrange[0] weightmax = brokenrange[1]
                    if((float)$weight['weight_max']>$max_points)
                    {
                        $max_points = $weight['weight_max'];
                    }
                    if((float)$weight['weight_min'] < $min_points)
                    {
                        $min_points = $weight['weight_min'];
                    }
                }else{
                    if((float)$weight['weight_min']>$max_points)
                    {
                        $max_points = $weight['weight_min'];
                    }
                    if((float)$weight['weight_max'] < $min_points) {
                        $min_points = $weight['weight_max'];
                    }
                }
            }
        }

        return(array('min'=>$min_points,'max'=>$max_points));
    }

    private function buildGradeCard()
    {
        $colspan=count($this->rubric_data['labels'])+2;

        $tmp_write="<tbody>";
        foreach($this->rubric_data['groups'] as $group_increment => $group){

            $point_range=$this->getMinMaxLabel($group['weights']);

            $min_points=0;
            $max_points=count($group['criteria'])*$point_range['max'];

            //calculate min_points
            if(isset($this->user_data)){

                foreach($group['criteria'] as $c => $criteria){

                    foreach($this->user_data as $u => $user_data){
                        if($user_data['criteria_point'] == NULL){
                            $this->incomplete = true;
                        }
                        if($user_data['rubric_criteria_id']==$criteria['criteria_id']){
                            $min_points+=$user_data['criteria_point'];
                        }

                    }

                }
            }
            if($this->incomplete && $this->student_view)
            {
                $min_points = 0;
            }
            $min_points=$min_points;
            $tmp_write.=$this->buildGradeGroupPoints($group['weights']);
            $tmp_write.=$this->buildGradeGroup($group,$group_increment);
            $tmp_write.="
                        <tr class=\"total-row\">
                            <th colspan=\"2\" scope=\"rowgroup\" class=\"text-right\">{TOTAL}</th>
                            <td colspan=\"$colspan\">$min_points {OUT_OF} $max_points</td>
                        </tr></tbody>";
        }
        return($tmp_write);

    }


    private function buildTemplateLabels()
    {
        $tmp_write="";
        foreach($this->rubric_data['labels'] as $k => $label){
            $tmp_write.="<th scope=\"col\" class=\"\">
                            <div class=\"form-group has-success has-feedback\">
                                <label class=\"control-label\" for=\"Label${k}\">{LABEL}</label>
                                <input id=\"Label${k}\" name=\"Label${k}\" type=\"text\" class=\"form-control\" placeholder=\"".$label['label']."\" value=\"".$label['label']."\" aria-describedby=\"Label${k}WarningStatus\" onkeyup=\"validate(this)\" onblur=\"recalculate(this)\" oninput=\"validate(this)\">
                                <span class=\"glyphicon glyphicon-ok form-control-feedback\" aria-hidden=\"true\"></span>
                                <span id=\"Label${k}WarningStatus\" class=\"sr-only\">(ok)</span>
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
                            ".$label['label']."
                        </th>";
        }
        return($tmp_write);

    }

    private function buildCompleteTemplate()
    {
        // build min / max point range for overall
        $min_points=$max_points=0;
        foreach($this->rubric_data['groups'] as $k => $group){
            $point_range=$this->getMinMaxLabel($group['weights']);
            $min_points+=count($group['criteria'])*$point_range['min'];
            $max_points+=count($group['criteria'])*$point_range['max'];
        }
        $min_points=$min_points;
        $max_points=$max_points;

        // define colspan for tfoot
        $colspan=count($this->rubric_data['labels']);

        // here we temporarily build the template, then destroy it after
        $filename="./Services/Tracking/templates/default/tpl.lp_rubricform_generated_".time().".html";

        $write="<div id=\"jkn_div_rubric\" class=\"table-responsive\" style=\"margin-top: 20px;\">

                    <table id=\"jkn_table_rubric\" class=\"table table-rubric-style table-striped\">

                        <thead>
                            <tr>
                                <th colspan=\"2\">&nbsp;</th>".
            $this->buildTemplateLabels().
            "</tr>
                        </thead>
                        <tbody>".
            $this->buildTemplateCard().
            "</tbody>
                        <tfoot>
                            <tr>
                                <th colspan=\"2\">{OVERALL_POINT}</th>
                                <td colspan=\"$colspan\">$min_points - $max_points</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>";

        file_put_contents($filename,$write);
        return($filename);

    }

    private function buildGradeTemplate()
    {
        $overall_max_points=0;
        $overall_min_points=0;
        foreach($this->rubric_data['groups'] as $k => $group){
            $point_range=$this->getMinMaxLabel($group['weights']);
            $overall_max_points+=count($group['criteria'])*$point_range['max'];
            if(isset($this->user_data)){
                foreach($group['criteria'] as $c => $criteria){
                    foreach($this->user_data as $u => $user_data){
                        if($user_data['criteria_point'] == NULL){
                            $this->incomplete = true;
                        }
                        if($criteria['criteria_id']==$user_data['rubric_criteria_id']){
                            $overall_min_points+=$user_data['criteria_point'];
                        }
                    }
                }
            }
        }
        if($this->incomplete && $this->student_view){
            $overall_min_points = 0;
        }
        $colspan=count($this->rubric_data['labels'])+2;
        // here we temporarily build the template, then destroy it after
        $filename="./Services/Tracking/templates/default/tpl.lp_rubricgrade_generated_".time().".html";

        $write="<div id=\"jkn_div_rubric\" class=\"table-responsive\">

                    <table id=\"jkn_table_rubric\" style=\"table-layout: fixed;\" class=\"table table-rubric-style table-striped\">

                        <thead>
                            <tr>
                                <th colspan=\"2\">&nbsp;</th>".

            $this->buildGradeLabels().

            "
                                <th scope=\"col\">
                                    Grade
                                </th>
                                <th scope=\"col\">
                                    {COMMENT}
                                </th>
                            </tr>
                        </thead> ".
            $this->buildGradeCard().
            "<tfoot>
                            <tr>
                                <th colspan=\"2\" class=\"text - right\">{GRAND_TOTAL}</th>
                                <td colspan=\"$colspan\">$overall_min_points {OUT_OF} $overall_max_points</td>
                            </tr>
                        </tfoot>
                        </table>
                </div>
            </form>";

        file_put_contents($filename,$write);
        return($filename);

    }
}

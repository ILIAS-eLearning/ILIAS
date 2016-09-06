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
    protected $rubric_locked;
    protected $rubric_owner;
    protected $rubric_mode;

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

    public function setRubricLocked($rubric_locked)
    {
        $this->rubric_locked=$rubric_locked;
    }

    public function setRubricOwner($rubric_owner)
    {
        $this->rubric_owner = $rubric_owner;
    }

    public function setRubricMode($rubric_mode)
    {
        $this->rubric_mode = $rubric_mode;
    }

    private function getRubricCardFormHeader()
    {
        // configure the header for content windows
        $rubric_heading_tpl=new ilTemplate('tpl.lp_rubricform_heading.html',true,true,'Services/Tracking');


        $title = $this->rubric_mode === ilLPRubricCard::RUBRIC_MODE_GRADER?
            $this->lng->txt('trac_rubric_grader'):$this->lng->txt('trac_rubric_developer');
        $rubric_heading_tpl->setVariable('RUBRIC_HEADER',$title);

        return($rubric_heading_tpl);
    }


    private function getDeveloperRubricCardFormCommandRow($form_action)
    {

        global $ilUser;
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        //configure the command row
        $rubric_commandrow_tpl=new ilTemplate('tpl.lp_rubricform_commandrow.html',true,true,'Services/Tracking');
        $select_prop=new ilSelectInputGUI('Title','selected_cmdrubric');
        $options=array(
            'behavior_1'=>$this->lng->txt('rubric_option_behavior_1'),
            'behavior_2'=>$this->lng->txt('rubric_option_behavior_2'),
            'behavior_3'=>$this->lng->txt('rubric_option_behavior_3'),
            'behavior_4'=>$this->lng->txt('rubric_option_behavior_4'),
            'behavior_5'=>$this->lng->txt('rubric_option_behavior_5'),
            //'behavior_6'=>$this->lng->txt('rubric_option_behavior_6'),
            'add_group'=>$this->lng->txt('rubric_option_add_group'),
            'del_group'=>$this->lng->txt('rubric_option_del_group'),
            'add_criteria'=>$this->lng->txt('rubric_option_add_criteria'),
            'del_criteria'=>$this->lng->txt('rubric_option_del_criteria'),
        );
        $select_prop->setOptions($options);
        $rubric_commandrow_tpl->setVariable('RURBRIC_COMMANDROW_SELECT',$select_prop->render());
        $rubric_commandrow_tpl->setVariable('RUBRIC_SAVE',$this->lng->txt('save'));
        $rubric_commandrow_tpl->setVariable('RUBRIC_EXECUTE',$this->lng->txt('execute'));
        $rubric_commandrow_tpl->setVariable('FORM_ACTION',$form_action);
        $rubric_commandrow_tpl->setVariable('PASSING_GRADE_VALUE',"$this->passing_grade");
        if(!is_null($this->rubric_locked)) {
            $rubric_commandrow_tpl->setVariable('RUBRIC_DISABLED','disabled');
            $rubric_commandrow_tpl->setVariable('RUBRIC_LOCK',$this->lng->txt('rubric_card_unlock'));
            $tmp_user = ilObjectFactory::getInstanceByObjId($this->rubric_owner, false);
            if($this->rubric_owner !== $ilUser->getId())
            {
                $rubric_commandrow_tpl->setVariable('USER_LOCK','disabled');
            }
            ilUtil::sendInfo($this->lng->txt('rubric_locked_info').' '.$tmp_user->getFullName().' '.$this->rubric_locked);
        }else{
            $rubric_commandrow_tpl->setVariable('RUBRIC_LOCK',$this->lng->txt('rubric_card_lock'));
        }
        $rubric_commandrow_tpl->setVariable('EXPORT',$this->lng->txt('rubric_option_export_pdf'));
        return($rubric_commandrow_tpl);
    }

    private function getGraderRubricCardFormCommandRow($form_action)
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        //configure the command row
        $rubric_commandrow_tpl=new ilTemplate('tpl.lp_rubricform_grader_commandrow.html',true,true,'Services/Tracking');
        $rubric_commandrow_tpl->setVariable('FORM_ACTION',$form_action);
        $rubric_commandrow_tpl->setVariable('PASSING_GRADE_VALUE',"$this->passing_grade");
        $rubric_commandrow_tpl->setVariable('RUBRIC_SAVE',$this->lng->txt('save'));
        $rubric_commandrow_tpl->setVariable('EXPORT',$this->lng->txt('rubric_option_export_pdf'));
        $rubric_commandrow_tpl->setVariable('RUBRIC_READONLY','readonly="readonly"');
        return($rubric_commandrow_tpl);
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
            'GROUP_POINT'=>'rubric_point_range_group',
        );

        $rubric_form_tpl=new ilTemplate('tpl.lp_rubricform.html',true,true,'Services/Tracking');
        if(!is_null($this->rubric_locked)) {
            $rubric_form_tpl->setVariable('RUBRIC_DISABLED','disabled');
        }
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

        if(!is_null($this->rubric_locked)) {
            $rubric_form_tpl->setVariable('RUBRIC_DISABLED','disabled=""');
        }


        if($this->rubric_mode === ilLPRubricCard::RUBRIC_MODE_GRADER){
            $rubric_form_tpl->setVariable('RUBRIC_READONLY','readonly="readonly"');
        }

        //load language files
        foreach($language_variables as $lang_key => $lang_label){
            $rubric_form_tpl->setVariable($lang_key,$this->lng->txt($lang_label));
        }

        // remove temporary template file
        unlink($filename);

        //return template
        return($rubric_form_tpl);
    }

    public function getRubricCard($form_action)
    {
        // get the required templates
        $rubric_heading_tpl=$this->getRubricCardFormHeader();

        if($this->rubric_mode === ilLPRubricCard::RUBRIC_MODE_DEVELOPER){
            $rubric_commandrow_tpl=$this->getDeveloperRubricCardFormCommandRow($form_action);
        }else{
            $rubric_commandrow_tpl=$this->getGraderRubricCardFormCommandRow($form_action);
        }

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
        $this->tpl->addCss('./Services/Tracking/css/ilRubricCard.css');

    }

    private function buildTemplateBehavior($behavior,$group_increment,$criteria_increment,$behavior_increment)
    {
        $tmp_behavior_name='Behavior_'.$group_increment.'_'.$criteria_increment.'_'.$behavior_increment;

        $tmp_write="<td scope=\"rowgroup\">
                    <div class=\"form-group has-success has-feedback\">
                        <label class=\"control-label\" for=\"behaviorname0_0\">{BEHAVIOR_NAME}</label>
                        <textarea id=\"${tmp_behavior_name}\" {RUBRIC_DISABLED} name=\"${tmp_behavior_name}\" class=\"form-control\" placeholder=\"${behavior['description']}\" value=\"${behavior['description']}\" aria-describedby=\"${tmp_behavior_name}WarningStatus\" onkeyup=\"validate(this)\" oninput=\"validate(this)\">${behavior['description']}</textarea>
                        <span class=\"glyphicon glyphicon-ok form-control-feedback\" aria-hidden=\"true\"></span>
                        <span id=\"${tmp_behavior_name}WarningStatus\" class=\"sr-only\">(ok)</span>
                    </div>
                </td>";

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
                                    <input type=\"checkbox\" {RUBRIC_DISABLED} id=\"${tmp_criteria_name}_checkbox\" >
                                </span>
                                <input id=\"${tmp_criteria_name}\" {RUBRIC_DISABLED} name=\"${tmp_criteria_name}\" type=\"text\" class=\"form-control\" placeholder=\"${criteria['criteria']}\" value=\"${criteria['criteria']}\" aria-describedby=\"${tmp_group_name}WarningStatus\" onkeyup=\"validate(this)\" oninput=\"validate(this)\">
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
                                        <input {RUBRIC_DISABLED} type=\"checkbox\" id=\"${tmp_group_name}_checkbox\">
                                    </span>
                                    <input id=\"${tmp_group_name}\" {RUBRIC_DISABLED}  name=\"${tmp_group_name}\" type=\"text\" class=\"form-control\" placeholder=\"${group['group_name']}\" value=\"${group['group_name']}\" aria-describedby=\"${tmp_group_name}WarningStatus\" onkeyup=\"validate(this)\" oninput=\"validate(this)\">
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
        //$tmp_script="<script type=\"text/javascript\">
        //            $( document ).ready(function() {";
        $tmp_write="<tr class=\"tblrow1 small\">
                        <th scope=\"col\" class=\"col-sm-2\">
                            &nbsp;
                        </th>
                        <th scope=\"col\" class=\"col-sm-2\">
                            &nbsp;
                        </th>";
        foreach($weights as $k => $weight){

            $div_class="has-success";
            $span_class="glyphicon-ok";
            $span_innerhtml="(ok)";

            //is this an overlapping range
            foreach($weights as $_k => $_weight)
            {
                // don't compare it to itself
                if($k!=$_k)
                {
                    if($weight['weight_min']>=$_weight['weight_min']&&$weight['weight_min']<=$_weight['weight_max'])
                    {
                        $div_class="has-error";
                        $span_class="glyphicon-remove";
                        $span_innerhtml="(error)";
                    }
                    if($weight['weight_max']>=$_weight['weight_min']&&$weight['weight_max']<=$_weight['weight_max'])
                    {
                        $div_class="has-error";
                        $span_class="glyphicon-remove";
                        $span_innerhtml="(error)";
                    }
                }
            }

            if(preg_match('/^\d{1,8}(?:\.\d{0,2})?$/',$weight['weight_min'])===0||preg_match('/^\d{1,8}(?:\.\d{0,2})?$/',$weight['weight_max'])===0)
            {
                $div_class="has-error";
                $span_class="glyphicon-remove";
                $span_innerhtml="(error)";
            }

            $tmp_name="Points${group_id}_${k}";
            $tmp_write.="<th scope=\"col\">
                            <div class=\"form-group point-input $div_class has-feedback\">
                                <label class=\"control-label\" for=\"$tmp_name\">{POINT}</label>
                                <input id=\"$tmp_name\" {RUBRIC_READONLY} {RUBRIC_DISABLED} name=\"$tmp_name\" type=\"text\" class=\"form-control\" value=\"${weight['weight_min']}-${weight['weight_max']}\" onkeyup=\"validate(this)\" onblur=\"recalculate(this)\" oninput=\"validate(this)\"/>
                               <span class=\"glyphicon $span_class form-control-feedback\" aria-hidden=\"true\"></span>
                               <span id=\"${tmp_name}WarningStatus\" class=\"sr-only\">$span_innerhtml</span>
                            </div>
                        </th>";
        }



        return(array('tmp_write'=>$tmp_write));

    }

    private function getMinMaxLabel($weights)
    {
        //figure out min / max points for group
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


    private function buildTemplateCard()
    {
        $colspan=count($this->rubric_data['labels']);

        $tmp_write="";
        foreach($this->rubric_data['groups'] as $group_increment => $group){

            $point_range=$this->getMinMaxLabel($group['weights']);

            $min_points=count($group['criteria'])*$point_range['min'];
            $max_points=count($group['criteria'])*$point_range['max'];

            $tmp=$this->buildTemplateGroupPoints($group['weights'],$group_increment);
            $tmp_write.=$tmp['tmp_write'];

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
            $tmp_write.="<th scope=\"col\">
                            <div class=\"form-group has-success has-feedback\">
                                <label class=\"control-label\" for=\"Label${k}\">{LABEL}</label>
                                <input {RUBRIC_READONLY} {RUBRIC_DISABLED} id=\"Label${k}\" name=\"Label${k}\" type=\"text\" class=\"form-control\" placeholder=\"".$label['label']."\" value=\"".$label['label']."\" aria-describedby=\"Label${k}WarningStatus\" onkeyup=\"validate(this)\" onblur=\"recalculate(this)\" oninput=\"validate(this)\">
                                <span class=\"glyphicon glyphicon-ok form-control-feedback\" aria-hidden=\"true\"></span>
                                <span id=\"Label${k}WarningStatus\" class=\"sr-only\">(ok)</span>
                            </div>
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

                    <table id=\"jkn_table_rubric\" class=\"table table-striped\">

                        <thead>
                            <tr>
                                <th colspan=\"2\">&nbsp;</th>".
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


}

?>

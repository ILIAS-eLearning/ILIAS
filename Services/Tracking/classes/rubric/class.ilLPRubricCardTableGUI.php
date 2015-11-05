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

	/**
	 * Constructor
	 */	
    function __construct()
	{	   
	   global $tpl,$lng;
       $this->lng=$lng;
       $this->tpl=$tpl;
	}
    
    private function getRubricCardFormHeader()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");        
        
        // configure the header for content windows
        $rubric_heading_tpl=new ilTemplate('tpl.lp_rubricform_heading.html',true,true,'Services/Tracking');
        
        $rubric_heading_tpl->setVariable('RUBRIC_HEADER',$this->lng->txt('trac_rubric'));
        
        return($rubric_heading_tpl);  
    }
    
    private function getRubricCardFormCommandRow($form_action)
    {
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
        );
        
        $rubric_form_tpl=new ilTemplate('tpl.lp_rubricform.html',true,true,'Services/Tracking');
        
        //load language files        
        foreach($language_variables as $lang_key => $lang_label){
            $rubric_form_tpl->setVariable($lang_key,$this->lng->txt($lang_label));
        }
        
        return($rubric_form_tpl);
    }
    
    
    public function getRubricCard($form_action)
    {
        // is there already a rubric card?
        
        // get the required templates
        $rubric_heading_tpl=$this->getRubricCardFormHeader();        
        $rubric_commandrow_tpl=$this->getRubricCardFormCommandRow($form_action);              
        $rubric_form_tpl=$this->getRubricCardForm();
        
        // append all templates into ilTemplate
        $this->tpl->setContent(
            $rubric_heading_tpl->get().            
            $rubric_commandrow_tpl->get().
            $rubric_form_tpl->get()            
        ); 
        
        // add in our javascript file
        $this->tpl->addJavaScript('./Services/Tracking/js/ilRubricCard.js');
    }
    
}

?>
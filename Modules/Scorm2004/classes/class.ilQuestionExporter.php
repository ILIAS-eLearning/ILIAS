<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Modules/TestQuestionPool/classes/class.assQuestionGUI.php");

/**
 * Scorm 2004 Question Exporter
 *
 * @author Hendrik Holtmann <holtmann@me.com>
 *
 * @version $Id: class.ilQuestionExporter.php 12658 2006-11-29 08:51:48Z akill $
 *
 * @ingroup ModulesScormAicc
 */
class ilQuestionExporter
{
	static $exported = array(); //json data for all exported questions (class variable)
	static $mobs = array(); //json data for all mobs  (class variable)
	static $media_files = array(); //json data for all files  (class variable)
	
	var $db;			// database object
	var $ilias;			// ilias object
	var $ref_id;		// reference ID
	var $inst_id;		// installation id
	var $q_gui;			// Question GUI object
	var $tpl;			// question template
	var $json;			// json object for current question
	var $json_decoded;	// json object (decoded) for current question
	var $preview_mode;	// preview mode activated yes/no
	
	/**
	 * Constructor
	 * @access	public
	 */
	public function ilQuestionExporter($a_preview_mode = false)
	{
		global $ilDB, $ilias, $lng;

		$this->ref_id =& $a_ref_id;

		$this->db =& $ilDB;
		$this->lng = $lng;

		$this->inst_id = IL_INST_ID;
		
		$this->preview_mode = $a_preview_mode;
		
		$this->tpl = new ilTemplate("tpl.question_export.html", true, true, "Modules/Scorm2004");
		
		// fix for bug 5386, alex 29.10.2009
		if (!$a_preview_mode)
		{
			$this->tpl->setVariable("FORM_BEGIN", "<form onsubmit='return false;'>");
			$this->tpl->setVariable("FORM_END", "</form>");
		}

	}
	
	
	public function exportQuestion($a_ref_id, $a_image_path = null, $a_output_mode = "presentation") {
		
		if ($a_ref_id != "")
		{
			$inst_id = ilInternalLink::_extractInstOfTarget($a_ref_id);
			if (!($inst_id > 0))
			{
				$q_id = ilInternalLink::_extractObjIdOfTarget($a_ref_id);
			}
		} 

		$this->q_gui = assQuestionGUI::_getQuestionGUI("", $q_id);

		if (!is_object($this->q_gui->object))
		{
			return "Error: Question not found.";
		}

		$type = $this->q_gui->object->getQuestionType();
		if (method_exists($this,$type))
		{
			$this->q_gui->object->setExportImagePath($a_image_path);
			$this->q_gui->object->feedbackOBJ->setPageObjectOutputMode($a_output_mode);
			$this->json = $this->q_gui->object->toJSON();
			$this->json_decoded = json_decode($this->json);
			self::$exported[$this->json_decoded->id] = $this->json;
			self::$mobs[$this->json_decoded->id] = $this->json_decoded->mobs;
			return $this->$type();
		} else {
			return "Error: Question Type not implemented/Question editing not finished";
		}
	}
	
	static public function indicateNewSco() {
		self::$exported = array();
		self::$mobs = array(); 
		self::$media_files = array(); 
	}
	
	static public function getMobs() {
		$allmobs = array();
		foreach (self::$mobs as $key => $value) {
			for ($i=0;$i<count($mobs[$key]);$i++) {
				array_push($allmobs,$mobs[$key][$i]);
			}
		}
		return $allmobs;
	}
	
	static public function getFiles() {
		return self::$media_files;
	}
	
	static public function questionsJS() {
		$exportstring ='var questions = new Array();';
		foreach (self::$exported as $key => $value) {
			$exportstring .= "questions[$key]= $value;";
		}
		return $exportstring;
	}
	
	private function setHeaderFooter()
	{
		$this->tpl->setCurrentBlock("common");
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		$this->tpl->setVariable("VAL_TYPE", $this->json_decoded->type);
		$this->tpl->parseCurrentBlock();
	}
	
	private function assSingleChoice()
	{
		$this->tpl->setCurrentBlock("singlechoice");
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		if($this->json_decoded->path)
		{
			$this->tpl->setVariable("HANDLE_IMAGES",
				"ilias.questions.handleMCImages(".$this->json_decoded->id.");");
		}
		$this->tpl->parseCurrentBlock();
		foreach ($this->json_decoded->answers as $answer) {
			if ($answer->image!="") {
				array_push(self::$media_files,$this->q_gui->object->getImagePath().$answer->image);
				if (is_file($this->q_gui->object->getImagePath()."thumb.".$answer->image))
				{
					array_push(self::$media_files,$this->q_gui->object->getImagePath()."thumb.".$answer->image);
				}
			}
		}
//		$this->setHeaderFooter();
		return $this->tpl->get();
	}
	
	private function assMultipleChoice() {
		$this->tpl->setCurrentBlock("multiplechoice");
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		if($this->json_decoded->path)
		{
			$this->tpl->setVariable("HANDLE_IMAGES",
				"ilias.questions.handleMCImages(".$this->json_decoded->id.");");
		}
		$this->tpl->parseCurrentBlock();
		foreach ($this->json_decoded->answers as $answer) {
			if ($answer->image!="") {
				array_push(self::$media_files,$this->q_gui->object->getImagePath().$answer->image);
				array_push(self::$media_files,$this->q_gui->object->getImagePath()."thumb.".$answer->image);
			}
		}
//		$this->setHeaderFooter();
		return $this->tpl->get();
	}


	private function assKprimChoice()
	{
		global $tpl;
		$tpl->addCss('Modules/Test/templates/default/ta.css');
		
		$this->tpl->setCurrentBlock("kprimchoice");
		
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		
		if( $this->preview_mode )
		{
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		
		if( $this->json_decoded->path )
		{
			$this->tpl->setVariable("HANDLE_IMAGES",
				"ilias.questions.handleMCImages(".$this->json_decoded->id.");");
		}
		
		$this->tpl->setVariable('OPTION_LABEL_TRUE', $this->json_decoded->trueOptionLabel);
		$this->tpl->setVariable('OPTION_LABEL_FALSE', $this->json_decoded->falseOptionLabel);
		
		$this->tpl->setVariable('VALUE_TRUE', 1);
		$this->tpl->setVariable('VALUE_FALSE', 0);
		
		$this->tpl->parseCurrentBlock();
		
		foreach( $this->json_decoded->answers as $answer )
		{
			if( $answer->image != "" )
			{
				self::$media_files[] = $answer->getImageFsPath();
				self::$media_files[] = $answer->getThumbFsPath();
			}
		}
		
		//		$this->setHeaderFooter();
		
		return $this->tpl->get();
	}
	
	private function assTextQuestion() {
		$maxlength = $this->json_decoded->maxlength == 0 ? 4096 : $this->json_decoded->maxlength;
		$this->tpl->setCurrentBlock("textquestion");
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		$this->tpl->setVariable("VAL_MAXLENGTH", $maxlength);
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		$this->tpl->parseCurrentBlock();
//		$this->setHeaderFooter();
		return $this->tpl->get();
	}
	
	private function assClozeTest() {
		$this->tpl->setCurrentBlock("clozequestion");
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		$this->tpl->parseCurrentBlock();
//		$this->setHeaderFooter();
		return $this->tpl->get();
	}
	
	private function assOrderingQuestion() {
		$this->tpl->setCurrentBlock("orderingquestion");
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		if($this->q_gui->object->getOrderingType() == OQ_PICTURES)
		{
			$this->tpl->setVariable("VAL_SUBTYPE", "_images");
			$this->tpl->setVariable("HANDLE_IMAGES",
				"ilias.questions.handleOrderingImages(".$this->json_decoded->id.");");

			foreach ($this->json_decoded->answers as $answer) {
				if ($answer->answertext!="") {
					array_push(self::$media_files,$this->q_gui->object->getImagePath().$answer->answertext);
					array_push(self::$media_files,$this->q_gui->object->getImagePath()."thumb.".$answer->answertext);
				}
			}
		}
		else
		{
			$this->tpl->setVariable("VAL_SUBTYPE", "_terms");
		}
		$this->tpl->parseCurrentBlock();
//		$this->setHeaderFooter();
		return $this->tpl->get();	
	}
	
	private function assMatchingQuestion() {
		global $tpl;
		$tpl->addJavaScript('Modules/TestQuestionPool/js/ilMatchingQuestion.js');
		$tpl->addCss('Modules/TestQuestionPool/templates/default/test_javascript.css');
		$this->tpl->setCurrentBlock("matchingquestion");
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		$this->tpl->setVariable("BTN_LABEL_RESET", $this->lng->txt("reset_terms"));
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		$this->tpl->parseCurrentBlock();
//		$this->setHeaderFooter();
		return $this->tpl->get();	
	}
	
	private function assImagemapQuestion() {
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		array_push(self::$media_files,$this->q_gui->object->getImagePath().$this->q_gui->object->getImageFilename());
		$this->tpl->setCurrentBlock("mapareas");
		$areas = $this->json_decoded->answers;
		//set areas in PHP cause of inteference between pure and highlighter
		foreach ($areas as $area) {
			$this->tpl->setVariable("VAL_COORDS", $area->coords);
			$this->tpl->setVariable("VAL_ORDER", $area->order);
			$this->tpl->setVariable("VAL_AREA", $area->area);
			$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
			$this->tpl->parseCurrentBlock();	
		}
		$this->tpl->setCurrentBlock("imagemapquestion");
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		$this->tpl->parseCurrentBlock();
//		$this->setHeaderFooter();	
		return $this->tpl->get();
	}

	private function assTextSubset() {
		$this->tpl->setCurrentBlock("textsubset");
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		$this->tpl->setVariable("VAL_MAXLENGTH", $maxlength);
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		$this->tpl->parseCurrentBlock();
//		$this->setHeaderFooter();
		return $this->tpl->get();
	}

	private function assOrderingHorizontal() {
		$this->tpl->setCurrentBlock("orderinghorizontal");
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		$this->tpl->parseCurrentBlock();
//		$this->setHeaderFooter();
		return $this->tpl->get();	
	}

	private function assErrorText() {
		$this->tpl->setCurrentBlock("errortext");
		$this->tpl->setVariable("VAL_ID", $this->json_decoded->id);
		$this->tpl->setVariable("TXT_SUBMIT_ANSWERS", $this->lng->txt("cont_submit_answers"));
		if ($this->preview_mode) {
			$this->tpl->setVariable("VAL_NO_DISPLAY", "style=\"display:none\"");
		}
		$this->tpl->parseCurrentBlock();
//		$this->setHeaderFooter();
		return $this->tpl->get();
	}
}

?>

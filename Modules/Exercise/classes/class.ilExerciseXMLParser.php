<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'classes/class.ilSaxParser.php';
include_once 'Services/Tracking/classes/class.ilChangeEvent.php';
include_once 'Modules/Exercise/classes/class.ilExerciseException.php';
include_once 'Modules/Exercise/classes/class.ilExerciseXMLWriter.php';

/**
* Exercise XML Parser which completes/updates a given exercise by an xml string.
*
* @author Roland Küstermann <roland@kuestermann.com>
* @version $Id: class.ilObjectXMLParser.php 12811 2006-12-08 18:37:44Z akill $
*
* @ingroup ModulesExercise
*
* @extends ilSaxParser
*/
class ilExerciseXMLParser extends ilSaxParser
{

    static $CONTENT_NOT_COMPRESSED = 0;
    static $CONTENT_GZ_COMPRESSED = 1;
    static $CONTENT_ZLIB_COMPRESSED = 2;

    /**
	 * Exercise object which has been parsed
	 *
	 * @var ilObjExercise
	 */
    var $exercise;

    /**
     * this will be matched against the id in the xml
     * in case we want to update an exercise
     *
     * @var int
     */
    var $obj_id;


    /**
     * result of parsing and updating
     *
     * @var boolean
     */
    var $result;

    /**
     * Content compression mode, defaults to no compression
     *
     * @var int
     */
    var $mode;
    
    /**
     * Current Exercise Assignment
     * @var ilExAssignment
     */
    var $assignment;
    
    /**
     * Storage for exercise related files
     * @var ilFSStorageExercise
     */
    var $storage;

    /**
	* Constructor
	*
	* @param   ilExercise  $exercise   existing exercise object
	* @param	string		$a_xml_file			xml data
	* @param   int $obj_id obj id of exercise which is to be updated
	* @access	public
	*/
	function ilExerciseXMLParser(& $exercise, $a_xml_data, $obj_id = -1)
	{
// @todo: needs to be revised for multiple assignments per exercise

		parent::ilSaxParser();
		$this->exercise = $exercise;
		// get all assignments and choose first one if exists, otherwise create
		$assignments = ilExAssignment::getAssignmentDataOfExercise($exercise->getId());
		if (count ($assignments) > 0) {
			$this->assignment = new ilExAssignment($assignments [0]["id"]);
		} else {
			$this->assignment = new ilExAssignment();
			$this->assignment->setExerciseId($exercise->getId());
			$this->assignment->save();
		}
		
		include_once ("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$this->storage = new ilFSStorageExercise ( $this->exercise->getId(), $this->assignment->getId());
		$this->storage->create();
		$this->storage->init();
		
		$this->setXMLContent($a_xml_data);
		$this->obj_id = $obj_id;
		$this->result = false;
	}


	/**
	* set event handlers
	*
	* @param	resource	reference to the xml parser
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	* handler for begin of element
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_name				element name
	* @param	array		$a_attribs			element attributes array
	* @throws   ilExerciseException   when obj id != - 1 and if it it does not match the id in the xml
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		global $ilErr;

		switch($a_name)
		{
			case 'Exercise':
			    if (isset($a_attribs["obj_id"]))
			    {
                   $read_obj_id = ilUtil::__extractId($a_attribs["obj_id"], IL_INST_ID);
			       if ($this->obj_id != -1 && (int) $read_obj_id  != -1 && (int) $this->obj_id != (int) $read_obj_id)
			       {
            	       throw new ilExerciseException ("Object IDs (xml $read_obj_id and argument ".$this->obj_id.") do not match!", ilExerciseException::$ID_MISMATCH);
                   }
			    }
				break;
			case 'Member':
			    $this->usr_action = $a_attribs["action"];
			    $this->usr_id = ilUtil::__extractId($a_attribs["usr_id"], IL_INST_ID);
				break;

			case 'File':
                $this->file_action = $a_attribs["action"];
				break;
			case 'Content':
                $this->mode = ilExerciseXMLParser::$CONTENT_NOT_COMPRESSED;
			    if ($a_attribs["mode"] == "GZIP")
			    {
                    if (!function_exists("gzdecode"))
                        throw new  ilExerciseException ("Deflating with gzip is not supported",  ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);

			        $this->mode = ilExerciseXMLParser::$CONTENT_GZ_COMPRESSED;
			    } elseif ($a_attribs["mode"] == "ZLIB")
			    {
                    if (!function_exists("gzuncompress"))
                        throw new ilExerciseException ("Deflating with zlib (compress/uncompress) is not supported",  ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);

			        $this->mode = ilExerciseXMLParser::$CONTENT_ZLIB_COMPRESSED;
			    }
			    break;
			case 'Marking':
			     $this->status = $a_attribs["status"];
			     if ($this->status == ilExerciseXMLWriter::$STATUS_NOT_GRADED)
			         $this->status = "notgraded";
			     elseif ($this->status == ilExerciseXMLWriter::$STATUS_PASSED)
			         $this->status = "passed";
			     else 
			         $this->status = "failed";
			     break;

		}
	}



	/**
	* handler for end of element
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_name				element name
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
		switch($a_name)
		{
			case 'Exercise':
                $this->result = true;
				break;
			case 'Title':
			    $this->exercise->setTitle(trim($this->cdata));
			    $this->assignment->setTitle(trim($this->cdata));
				break;
			case 'Description':
			    $this->exercise->setDescription(trim($this->cdata));
				break;
			case 'Instruction':
				$this->assignment->setInstruction(trim($this->cdata));
				break;
			case 'DueDate':
				$this->assignment->setDeadLine(trim($this->cdata));
				break;
			case 'Member':
   			    $this->updateMember($this->usr_id, $this->usr_action);
   			    // update marking after update member.
   			  	$this->updateMarking($this->usr_id);
			    break;			    
			case 'Filename':
			    $this->file_name = trim($this->cdata);
			    break;
			case 'Content':
			    $this->file_content = trim($this->cdata);
			    break;
			case 'File':
               	$this->updateFile($this->file_name, $this->file_content, $this->file_action);
			    break;
			case 'Comment':
			     $this->comment = trim($this->cdata);
			     break;
			case 'Notice':
			     $this->notice = trim($this->cdata);
			     break;
			case 'Mark':
			     $this->mark = trim($this->cdata);
			     break;			     
			case 'Marking':			   
			     // see Member end tag
			     break;			    
		}

		$this->cdata = '';

		return;
	}

	/**
	* handler for character data
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_data				character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		if($a_data != "\n")
		{
			$this->cdata .= $a_data;
		}
	}


	/**
	 * update member object according to given action
	 *
	 * @param int $user_id
	 * @param string $action can be Attach or Detach
	 */
	private function updateMember ($user_id, $action) {
       if (!is_int($user_id) || $user_id <= 0) {
           return;
       }
	   $memberObject = new ilExerciseMembers($this->exercise);

	   if ($action == "Attach" && !$memberObject->isAssigned($user_id))
	   {
            $memberObject->assignMember ($user_id);
       }

       if ($action == "Detach" && $memberObject->isAssigned($user_id))
       {
            $memberObject->deassignMember ($user_id);
       }
	}

	/**
	 * update file according to filename
	 *
	 * @param string $filename
	 * @param string $content  base 64 encoded string
	 * @param string $action can be Attach or Detach
	 */
	private function updateFile ($filename, $b64encodedContent, $action)
	{
       if (strlen($filename) == 0) {
           return;
       }
		 $filename= $this->storage->getAbsolutePath()."/".$filename;
		
	    if ($action == "Attach")
        {
           $content = base64_decode((string) $b64encodedContent);
           if ($this->mode == ilExerciseXMLParser::$CONTENT_GZ_COMPRESSED) {
                $content = gzdecode($content);
	       }elseif ($this->mode ==ilExerciseXMLParser::$CONTENT_ZLIB_COMPRESSED) {
                $content = gzuncompress($content);
	       }
	      
	       //echo $filename;
	       $this->storage->writeToFile($content, $filename);
           
        }
        if ($action == "Detach")
        {
            $this->storage->deleteFile($filename);
        }
	}

	/**
	 * starts parsing an changes object by side effect.
	 *
	 * @throws ilExerciseException when obj id != - 1 and if it it does not match the id in the xml
	 * @return boolean true, if no errors happend.
	 *
	 */
	public function start () {
	    $this->startParsing();
	    return $this->result > 0;
	}
	
	/**
	 * update marking of member
	 *
	 * @param int $usr_id
	 */
	private function updateMarking($usr_id) {
	    if (isset($this->mark)) 
	    {			
			ilExAssignment::updateMarkOfUser($this->assignment->getId(), $usr_id, ilUtil::stripSlashes($this->mark));
	    }
		if (isset($this->comment))
		{

			ilExAssignment::updateCommentForUser($this->assignment->getId(), $usr_id, ilUtil::stripSlashes($this->comment));
		}
	    
	    //$memberObject = $this->exercise->members_obj;
	    if (isset ( $this->status )){
	    	
			ilExAssignment::updateStatusOfUser ( $this->assignment->getId (), $usr_id, ilUtil::stripSlashes ( $this->status ) );
	    }
	    if (isset($this->notice)){
			 ilExAssignment::updateNoticeForUser($this->assignment->getId(), $usr_id, ilUtil::stripSlashes($this->notice));
	    }
	    // reset variables
	    $this->mark = null;
	    $this->status = null;
	    $this->notice = null;
	    $this->comment = null;
	}
	
	public function getAssignment () {
		return $this->assignment;
	}

}
?>
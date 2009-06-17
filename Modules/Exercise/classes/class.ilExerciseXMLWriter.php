<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./classes/class.ilXmlWriter.php";

/**
* XML writer class
*
* Class to simplify manual writing of xml documents.
* It only supports writing xml sequentially, because the xml document
* is saved in a string with no additional structure information.
* The author is responsible for well-formedness and validity
* of the xml document.
*
* @author Roland Küstermann <Roland@kuestermann.com>
* @version $Id: class.ilExerciseXMLWriter.php,v 1.3 2005/11/04 12:50:24 smeyer Exp $
*
* @ingroup ModulesExercise
*/
class ilExerciseXMLWriter extends ilXmlWriter
{

    static $CONTENT_ATTACH_NO = 0;
    static $CONTENT_ATTACH_ENCODED = 1;
    static $CONTENT_ATTACH_ZLIB_ENCODED = 2;
    static $CONTENT_ATTACH_GZIP_ENCODED = 3;
    
    static $STATUS_NOT_GRADED = "NOT_GRADED";
    static $STATUS_PASSED = "PASSED";
    static $STATUS_FAILED = "FAILED";
	/**
	 * if true, file contents will be attached as base64
	 *
	 * @var boolean
	 */
    var $attachFileContents;
    
    
    /**
     * if true, members will be attach to xml
     *
     * @var boolean
     */
    var $attachMembers;

	/**
	 * Exercise Object
	 *
	 * @var ilObjExercise
	 */
	var $exercise;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	function ilExerciseXMLWriter()
	{
		parent::ilXmlWriter();
		$this->attachFileContents = ilExerciseXMLWriter::$CONTENT_ATTACH_NO;
	}


	function setExercise(&  $exercise)
	{
		$this->exercise = & $exercise;
	}

    /**
     * set attachment content mode
     *
     * @param int $attachFileContents
     * @throws  ilExerciseException if mode is not supported
     */
	function setAttachFileContents($attachFileContents)
	{
	     if ($attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_GZIP_ENCODED && !function_exists("gzencode"))
		 {
		      throw new ilExerciseException("Inflating with gzip is not supported",  ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);
         }
         if ($attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_ZLIB_ENCODED && !function_exists("gzcompress"))
         {
            throw new ilExerciseException("Inflating with zlib (compress/uncompress) is not supported",  ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);
         }

		$this->attachFileContents = $attachFileContents;
	}


	function start()
	{
		$this->__buildHeader();

		$attribs =array ("obj_id" => "il_".IL_INST_ID."_exc_".$this->exercise->getId());

		if ($this->exercise->getOwner())
            $attribs["owner"] = "il_".IL_INST_ID."_usr_".$this->exercise->getOwner();

        $this->xmlStartTag("Exercise", $attribs);

        $this->xmlElement("Title", null,$this->exercise->getTitle());
        $this->xmlElement("Description",  null,$this->exercise->getDescription());
        $this->xmlElement("Instruction",  null,$this->exercise->getInstruction());
        $this->xmlElement("DueDate",  null,$this->exercise->getTimestamp());
        $this->xmlStartTag("Files");

        /**
         * @var  ilFileDataExercise $exerciseFileData
         */
        $exerciseFileData = $this->exercise->file_obj;
        $files = $exerciseFileData->getFiles();
        if (count($files))
        {
            foreach ($files as $file)
            {
                $this->xmlStartTag("File", array ("size" => $file["size"] ));
                $this->xmlElement("Filename", null, $file["name"]);
                if ($this->attachFileContents)
                {
                    $filename = $file["fullpath"];
                    if (@is_file($filename))
                    {
                        $content = @file_get_contents($filename);
                        $attribs = array ("mode"=>"PLAIN");
                        if ($this->attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_ZLIB_ENCODED)
                        {
                            $attribs = array ("mode"=>"ZLIB");
                            $content = gzcompress($content, 9);
                        }
                         elseif ($this->attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_GZIP_ENCODED)
                        {
                            $attribs = array ("mode"=>"GZIP");
                            $content = gzencode($content, 9);
                        }
                        $content = base64_encode($content);
                        $this->xmlElement("Content",$attribs, $content);
                    }
                }
                $this->xmlEndTag("File");
            }
        }
        $this->xmlEndTag("Files");
        if ($this->attachMembers)
        {
            $this->xmlStartTag("Members");
            $members = $this->exercise->getMemberListData();
            if (count($members))
            {
                foreach ($members as $member)
                {  
                    $this->xmlStartTag("Member", 
                        array ("usr_id" => "il_".IL_INST_ID."_usr_".$member["usr_id"]));
                    $this->attachMarking ($member);
                    $this->xmlEndTag("Member");
                }
            }
            $this->xmlEndTag("Members");
        }


        $this->xmlEndTag("Exercise");
		$this->__buildFooter();

		return true;
	}

	function getXML()
	{
		return $this->xmlDumpMem(FALSE);
	}


	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE Exercise PUBLIC \"-//ILIAS//DTD ExerciseAdministration//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_exercise_3_10.dtd\">");
		$this->xmlSetGenCmt("Exercise Object");
		$this->xmlHeader();

		return true;
	}

	function __buildFooter()
	{
	    
	}
	
	/**
	 * write access to property attchMarkings
	 *
	 * @param boolean $value
	 */
	public function setAttachMembers ($value) {
	    $this->attachMembers = $value ? true : false;
	}
	
	/**
	 * attach marking tag to member 
	 *
	 * @param array $a_member
	 */
	private function attachMarking ($a_member) 
	{
	    include_once 'Services/Tracking/classes/class.ilLPMarks.php';

	    $marks = new ilLPMarks($this->exercise->getId(), $a_member["usr_id"]);
	    if ($a_member["status"] ==  "notgraded")
	    {
	        $status = ilExerciseXMLWriter::$STATUS_NOT_GRADED;
	    } elseif ($a_member["status"] == "failed")
	    {
	        $status = ilExerciseXMLWriter::$STATUS_FAILED;
	    } else 
	    {
	        $status = ilExerciseXMLWriter::$STATUS_PASSED;
	    } 
	    $this->xmlStartTag("Marking", array (
	    	"status" => $status 
	    	));
	    $this->xmlElement("Mark", null, $marks->getMark());
	    $this->xmlElement("Notice", null, $a_member["notice"]);
	    $this->xmlElement("Comment", null, $marks->getComment());
	    $this->xmlEndTag("Marking");
	}

}


?>

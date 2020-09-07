<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";
include_once "./Modules/Exercise/classes/class.ilExAssignment.php";

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
    public static $CONTENT_ATTACH_NO = 0;
    public static $CONTENT_ATTACH_ENCODED = 1;
    public static $CONTENT_ATTACH_ZLIB_ENCODED = 2;
    public static $CONTENT_ATTACH_GZIP_ENCODED = 3;
    
    public static $STATUS_NOT_GRADED = "NOT_GRADED";
    public static $STATUS_PASSED = "PASSED";
    public static $STATUS_FAILED = "FAILED";
    /**
     * if true, file contents will be attached as base64
     *
     * @var boolean
     */
    public $attachFileContents;
    
    /**
     * if true, members will be attach to xml
     *
     * @var boolean
     */
    public $attachMembers;
    
    /**
     * Exercise Object
     *
     * @var ilObjExercise
     */
    public $exercise;
    
    /**
     * constructor
     * @param	string	xml version
     * @param	string	output encoding
     * @param	string	input encoding
     * @access	public
     */
    public function __construct()
    {
        // @todo: needs to be revised for multiple assignments per exercise
        //die ("Needs revision for ILIAS 4.1");
        parent::__construct();
        $this->attachFileContents = ilExerciseXMLWriter::$CONTENT_ATTACH_NO;
    }
    
    /**
     *
     * set exercise object
     * @param ilObjExercise $exercise
     */
    
    public function setExercise($exercise)
    {
        $this->exercise = $exercise;
    }
    
    /**
     * set attachment content mode
     *
     * @param int $attachFileContents
     * @throws  ilExerciseException if mode is not supported
     */
    public function setAttachFileContents($attachFileContents)
    {
        if ($attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_GZIP_ENCODED && !function_exists("gzencode")) {
            throw new ilExerciseException("Inflating with gzip is not supported", ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);
        }
        if ($attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_ZLIB_ENCODED && !function_exists("gzcompress")) {
            throw new ilExerciseException("Inflating with zlib (compress/uncompress) is not supported", ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);
        }
        
        $this->attachFileContents = $attachFileContents;
    }
    
    public function start()
    {
        $this->__buildHeader();
        
        $attribs = array("obj_id" => "il_" . IL_INST_ID . "_exc_" . $this->exercise->getId() );
        
        if ($this->exercise->getOwner()) {
            $attribs ["owner"] = "il_" . IL_INST_ID . "_usr_" . $this->exercise->getOwner();
        }
        
        $this->xmlStartTag("Exercise", $attribs);
        
        //todo: create new dtd for new assignment structure
        $this->xmlElement("Title", null, $this->exercise->getTitle());
        $this->xmlElement("Description", null, $this->exercise->getDescription());
        //$this->xmlElement("Instruction",  null,$this->exercise->getInstruction());
        //$this->xmlElement("DueDate",  null,$this->exercise->getTimestamp());
        

        //todo: as a workaround use first assignment for compatibility with old exercise dtd
        $assignments = ilExAssignment::getAssignmentDataOfExercise($this->exercise->getId());
        
        if (count($assignments) > 0) {
            foreach ($assignments as $assignment) {
                $this->xmlStartTag("Assignment");
                $this->xmlElement("Instruction", null, $assignment ["instruction"]);
                $this->xmlElement("DueDate", null, $assignment ["deadline"]);
                
                $this->handleAssignmentFiles($this->exercise->getId(), $assignment ["id"]);
                if ($this->attachMembers) {
                    $this->handleAssignmentMembers($this->exercise->getId(), $assignment ["id"]);
                }
                $this->xmlEndTag("Assignment");
            }
        }
        

        $this->xmlEndTag("Exercise");
        $this->__buildFooter();
        
        return true;
    }
    
    public function getXML()
    {
        return $this->xmlDumpMem(false);
    }
    
    public function __buildHeader()
    {
        $this->xmlSetDtdDef("<!DOCTYPE Exercise PUBLIC \"-//ILIAS//DTD ExerciseAdministration//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_exercise_4_4.dtd\">");
        $this->xmlSetGenCmt("Exercise Object");
        $this->xmlHeader();
        
        return true;
    }
    
    public function __buildFooter()
    {
    }
    
    /**
     * write access to property attchMarkings
     *
     * @param boolean $value
     */
    public function setAttachMembers($value)
    {
        $this->attachMembers = $value ? true : false;
    }
    
    /**
     * attach marking tag to member for given assignment
     *
     * @param int $user_id
     * @param int $assignment_id
     */
    private function attachMarking($user_id, $assignment_id)
    {
        $ass = new ilExAssignment($assignment_id);

        $amark = $ass->getMemberStatus($user_id)->getMark();
        $astatus = $ass->getMemberStatus($user_id)->getStatus();
        $acomment = $ass->getMemberStatus($user_id)->getComment();
        $anotice = $ass->getMemberStatus($user_id)->getNotice();
        
        
        if ($astatus == "notgraded") {
            $status = ilExerciseXMLWriter::$STATUS_NOT_GRADED;
        } elseif ($astatus == "failed") {
            $status = ilExerciseXMLWriter::$STATUS_FAILED;
        } else {
            $status = ilExerciseXMLWriter::$STATUS_PASSED;
        }
        
        $this->xmlStartTag("Marking", array("status" => $status ));
        $this->xmlElement("Mark", null, $amark);
        $this->xmlElement("Notice", null, $anotice);
        $this->xmlElement("Comment", null, $acomment);
        $this->xmlEndTag("Marking");
    }
    
    private function handleAssignmentFiles($ex_id, $as_id)
    {
        $this->xmlStartTag("Files");
        include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
        $storage = new ilFSStorageExercise($ex_id, $as_id);
        $files = $storage->getFiles();
        
        if (count($files)) {
            foreach ($files as $file) {
                $this->xmlStartTag("File", array("size" => $file ["size"] ));
                $this->xmlElement("Filename", null, $file ["name"]);
                if ($this->attachFileContents) {
                    $filename = $file ["fullpath"];
                    if (@is_file($filename)) {
                        $content = @file_get_contents($filename);
                        $attribs = array("mode" => "PLAIN" );
                        if ($this->attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_ZLIB_ENCODED) {
                            $attribs = array("mode" => "ZLIB" );
                            $content = gzcompress($content, 9);
                        } elseif ($this->attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_GZIP_ENCODED) {
                            $attribs = array("mode" => "GZIP" );
                            $content = gzencode($content, 9);
                        }
                        $content = base64_encode($content);
                        $this->xmlElement("Content", $attribs, $content);
                    }
                }
                $this->xmlEndTag("File");
            }
        }
        $this->xmlEndTag("Files");
    }
    
    /**
     * create xml for files per assignment
     *
     * @param integer $ex_id exercise id
     * @param array $assignments assignment id
     */
    private function handleAssignmentMembers($ex_id, $assignment_id)
    {
        $this->xmlStartTag("Members");
        include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
        $members = ilExerciseMembers::_getMembers($ex_id);
        if (count($members)) {
            foreach ($members as $member_id) {
                $this->xmlStartTag("Member", array("usr_id" => "il_" . IL_INST_ID . "_usr_" . $member_id  ));
                
                $name = ilObjUser::_lookupName($member_id);
                
                $this->xmlElement("Firstname", array(), $name['firstname']);
                $this->xmlElement("Lastname", array(), $name['lastname']);
                $this->xmlElement("Login", array(), $name['login']);
                $this->attachMarking($member_id, $assignment_id);
                $this->xmlEndTag("Member");
            }
        }
        $this->xmlEndTag("Members");
    }
}

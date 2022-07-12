<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
/**
 * XML writer class
 * Class to simplify manual writing of xml documents.
 * It only supports writing xml sequentially, because the xml document
 * is saved in a string with no additional structure information.
 * The author is responsible for well-formedness and validity
 * of the xml document.
 * @author Roland Küstermann <Roland@kuestermann.com>
 */
class ilExerciseXMLWriter extends ilXmlWriter
{
    public static int $CONTENT_ATTACH_NO = 0;
    public static int $CONTENT_ATTACH_ENCODED = 1;
    public static int $CONTENT_ATTACH_ZLIB_ENCODED = 2;
    public static int $CONTENT_ATTACH_GZIP_ENCODED = 3;
    
    public static string $STATUS_NOT_GRADED = "NOT_GRADED";
    public static string $STATUS_PASSED = "PASSED";
    public static string $STATUS_FAILED = "FAILED";

    public bool $attachFileContents; // if true, file contents will be attached as base64
    public bool $attachMembers; // if true, members will be attach to xml
    public ilObjExercise $exercise;
    
    public function __construct()
    {
        // @todo: needs to be revised for multiple assignments per exercise
        //die ("Needs revision for ILIAS 4.1");
        parent::__construct();
        $this->attachFileContents = ilExerciseXMLWriter::$CONTENT_ATTACH_NO;
    }
    
    public function setExercise(ilObjExercise $exercise) : void
    {
        $this->exercise = $exercise;
    }
    
    /**
     * set attachment content mode
     * @throws  ilExerciseException if mode is not supported
     */
    public function setAttachFileContents(int $attachFileContents) : void
    {
        if ($attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_GZIP_ENCODED && !function_exists("gzencode")) {
            throw new ilExerciseException("Inflating with gzip is not supported", ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);
        }
        if ($attachFileContents == ilExerciseXMLWriter::$CONTENT_ATTACH_ZLIB_ENCODED && !function_exists("gzcompress")) {
            throw new ilExerciseException("Inflating with zlib (compress/uncompress) is not supported", ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);
        }
        
        $this->attachFileContents = $attachFileContents;
    }
    
    public function start() : bool
    {
        $this->__buildHeader();
        
        $attribs = array("obj_id" => "il_" . IL_INST_ID . "_exc_" . $this->exercise->getId() );
        
        if ($this->exercise->getOwner() !== 0) {
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
    
    public function getXML() : string
    {
        return $this->xmlDumpMem(false);
    }
    
    public function __buildHeader() : bool
    {
        $this->xmlSetDtdDef("<!DOCTYPE Exercise PUBLIC \"-//ILIAS//DTD ExerciseAdministration//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_exercise_4_4.dtd\">");
        $this->xmlSetGenCmt("Exercise Object");
        $this->xmlHeader();
        
        return true;
    }
    
    public function __buildFooter() : void
    {
    }
    
    /**
     * write access to property attchMarkings
     */
    public function setAttachMembers(bool $value) : void
    {
        $this->attachMembers = $value;
    }
    
    /**
     * attach marking tag to member for given assignment
     */
    private function attachMarking(
        int $user_id,
        int $assignment_id
    ) : void {
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
    
    private function handleAssignmentFiles(
        int $ex_id,
        int $as_id
    ) : void {
        $this->xmlStartTag("Files");
        $storage = new ilFSStorageExercise($ex_id, $as_id);
        $files = $storage->getFiles();
        
        if (count($files)) {
            foreach ($files as $file) {
                $this->xmlStartTag("File", array("size" => $file ["size"] ));
                $this->xmlElement("Filename", null, $file ["name"]);
                if ($this->attachFileContents) {
                    $filename = $file ["fullpath"];
                    if (is_file($filename)) {
                        $content = file_get_contents($filename);
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
     */
    private function handleAssignmentMembers(
        int $ex_id,
        int $assignment_id
    ) : void {
        $this->xmlStartTag("Members");
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

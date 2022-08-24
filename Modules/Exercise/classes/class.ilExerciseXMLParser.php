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
 * Exercise XML Parser which completes/updates a given exercise by an xml string.
 * @author Roland Küstermann <roland@kuestermann.com>
 */
class ilExerciseXMLParser extends ilSaxParser
{
    public static int $CONTENT_NOT_COMPRESSED = 0;
    public static int $CONTENT_GZ_COMPRESSED = 1;
    public static int $CONTENT_ZLIB_COMPRESSED = 2;
    protected string $cdata;
    protected string $mark;
    protected string $notice;
    protected string $comment;
    protected string $file_content;
    protected string $file_name;
    protected string $status;
    protected string $file_action;
    protected int $usr_id;
    protected string $usr_action;

    public ilObjExercise $exercise;
    public int $obj_id;
    public bool $result;
    public int $mode;
    public ilExAssignment $assignment;
    public ilFSStorageExercise $storage;

    public function __construct(
        ilObjExercise $exercise,
        string $a_xml_data,
        int $obj_id = -1
    ) {
        // @todo: needs to be revised for multiple assignments per exercise

        parent::__construct();

        $this->exercise = $exercise;
        // get all assignments and choose first one if exists, otherwise create
        $assignments = ilExAssignment::getAssignmentDataOfExercise($exercise->getId());
        if (count($assignments) > 0) {
            $this->assignment = new ilExAssignment($assignments [0]["id"]);
        } else {
            $this->assignment = new ilExAssignment();
            $this->assignment->setExerciseId($exercise->getId());
            $this->assignment->save();
        }

        $this->storage = new ilFSStorageExercise($this->exercise->getId(), $this->assignment->getId());
        $this->storage->create();
        $this->storage->init();

        $this->setXMLContent($a_xml_data);
        $this->obj_id = $obj_id;
        $this->result = false;
    }


    public function setHandlers($a_xml_parser): void
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    /**
    * handler for begin of element
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_name				element name
    * @param	array		$a_attribs			element attributes array
    * @throws   ilExerciseException   when obj id != - 1 and if it it does not match the id in the xml
    */
    public function handlerBeginTag($a_xml_parser, string $a_name, array $a_attribs): void
    {
        switch ($a_name) {
            case 'Exercise':
                if (isset($a_attribs["obj_id"])) {
                    $read_obj_id = ilUtil::__extractId($a_attribs["obj_id"], IL_INST_ID);
                    if ($this->obj_id != -1 && (int) $read_obj_id != -1 && $this->obj_id !== (int) $read_obj_id) {
                        throw new ilExerciseException("Object IDs (xml $read_obj_id and argument " . $this->obj_id . ") do not match!", ilExerciseException::$ID_MISMATCH);
                    }
                }
                break;
            case 'Member':
                $this->usr_action = $a_attribs["action"];
                $this->usr_id = (int) ilUtil::__extractId($a_attribs["usr_id"], IL_INST_ID);
                break;

            case 'File':
                $this->file_action = $a_attribs["action"];
                break;
            case 'Content':
                $this->mode = ilExerciseXMLParser::$CONTENT_NOT_COMPRESSED;
                if ($a_attribs["mode"] == "GZIP") {
                    if (!function_exists("gzdecode")) {
                        throw new  ilExerciseException("Deflating with gzip is not supported", ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);
                    }

                    $this->mode = ilExerciseXMLParser::$CONTENT_GZ_COMPRESSED;
                } elseif ($a_attribs["mode"] == "ZLIB") {
                    if (!function_exists("gzuncompress")) {
                        throw new ilExerciseException("Deflating with zlib (compress/uncompress) is not supported", ilExerciseException::$ID_DEFLATE_METHOD_MISMATCH);
                    }

                    $this->mode = ilExerciseXMLParser::$CONTENT_ZLIB_COMPRESSED;
                }
                break;
            case 'Marking':
                 $this->status = $a_attribs["status"];
                 if ($this->status == ilExerciseXMLWriter::$STATUS_NOT_GRADED) {
                     $this->status = "notgraded";
                 } elseif ($this->status == ilExerciseXMLWriter::$STATUS_PASSED) {
                     $this->status = "passed";
                 } else {
                     $this->status = "failed";
                 }
                 break;

        }
    }



    /**
    * handler for end of element
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_name				element name
    */
    public function handlerEndTag($a_xml_parser, string $a_name): void
    {
        switch ($a_name) {
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
                $this->assignment->setDeadline(trim($this->cdata));
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
    }

    /**
    * handler for character data
    *
    * @param	resource	$a_xml_parser		xml parser
    * @param	string		$a_data				character data
    */
    public function handlerCharacterData($a_xml_parser, string $a_data): void
    {
        if ($a_data != "\n") {
            $this->cdata .= $a_data;
        }
    }


    /**
     * update member object according to given action
     */
    private function updateMember(int $user_id, string $action): void
    {
        if (!is_int($user_id) || $user_id <= 0) {
            return;
        }
        $memberObject = new ilExerciseMembers($this->exercise);

        if ($action == "Attach" && !$memberObject->isAssigned($user_id)) {
            $memberObject->assignMember($user_id);
        }

        if ($action == "Detach" && $memberObject->isAssigned($user_id)) {
            $memberObject->deassignMember($user_id);
        }
    }

    /**
     * update file according to filename
     *
     * @param string $filename
     * @param string $content  base 64 encoded string
     * @param string $action can be Attach or Detach
     */
    private function updateFile(
        string $filename,
        string $b64encodedContent,
        string $action
    ): void {
        if (strlen($filename) == 0) {
            return;
        }
        $filename = $this->storage->getAbsolutePath() . "/" . $filename;

        if ($action == "Attach") {
            $content = base64_decode($b64encodedContent);
            if ($this->mode == ilExerciseXMLParser::$CONTENT_GZ_COMPRESSED) {
                $content = gzdecode($content);
            } elseif ($this->mode == ilExerciseXMLParser::$CONTENT_ZLIB_COMPRESSED) {
                $content = gzuncompress($content);
            }

            //echo $filename;
            //$this->storage->writeToFile($content, $filename);
        }
        if ($action == "Detach") {
            //$this->storage->deleteFile($filename);
        }
    }

    /**
     * starts parsing an changes object by side effect.
     *
     * @throws ilExerciseException when obj id != - 1 and if it it does not match the id in the xml
     * @return boolean true, if no errors happend.
     *
     */
    public function start(): bool
    {
        $this->startParsing();
        return $this->result > 0;
    }

    /**
     * update marking of member
     *
     * @param int $usr_id
     */
    private function updateMarking(int $usr_id): void
    {
        $member_status = $this->assignment->getMemberStatus($usr_id);
        if (isset($this->mark)) {
            $member_status->setMark(ilUtil::stripSlashes($this->mark));
        }
        if (isset($this->comment)) {
            $member_status->setComment(ilUtil::stripSlashes($this->comment));
        }
        if (isset($this->status)) {
            $member_status->setStatus(ilUtil::stripSlashes($this->status));
        }
        if (isset($this->notice)) {
            $member_status->setNotice(ilUtil::stripSlashes($this->notice));
        }
        $member_status->update();

        // reset variables
        $this->mark = null;
        $this->status = null;
        $this->notice = null;
        $this->comment = null;
    }

    public function getAssignment(): ilExAssignment
    {
        return $this->assignment;
    }
}

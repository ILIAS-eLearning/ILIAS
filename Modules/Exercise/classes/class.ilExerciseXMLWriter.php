<?php

/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
   	+-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

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

include_once "./classes/class.ilXmlWriter.php";

class ilExerciseXMLWriter extends ilXmlWriter
{

    static $CONTENT_ATTACH_NO = 0;
    static $CONTENT_ATTACH_ENCODED = 1;
    static $CONTENT_ATTACH_ZLIB_ENCODED = 2;
    static $CONTENT_ATTACH_GZIP_ENCODED = 3;
	/**
	 * if true, file contents will be attached as base64
	 *
	 * @var boolean
	 */
    var $attachFileContents;

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

        $this->xmlStartTag("Members");
        $members = $this->exercise->getMemberListData();
        if (count($members))
        {
            foreach ($members as $member)
            {
                $this->xmlElement("Member", array ("usr_id" => "il_".IL_INST_ID."_usr_".$member["usr_id"]));
            }
        }
        $this->xmlEndTag("Members");


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
		$this->xmlSetDtdDef("<!DOCTYPE Exercise PUBLIC \"-//ILIAS//DTD ExerciseAdministration//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_exercise_3_8.dtd\">");
		$this->xmlSetGenCmt("Exercise Object");
		$this->xmlHeader();

		return true;
	}

	function __buildFooter()
	{

	}

}


?>

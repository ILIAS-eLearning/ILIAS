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
	/**
	 * if true, file contents will be attached as base64
	 *
	 * @var boolean
	 */
    var $attachFileContents = false;

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
	}


	function setExercise(&  $exercise)
	{
		$this->exercise = & $exercise;
	}

	function setAttachFileContents($attachFileContents)
	{
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
                    $this->xmlElement("Content",null, base64_encode(
                        file_get_contents($file["fullpath"])
                    ));
                }
                $this->xmlEndTag("File");
            }
        }

        $this->xmlEndTag("Files");

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

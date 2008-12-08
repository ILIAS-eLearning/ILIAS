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
* @ingroup ModulesFile
*/

include_once "./classes/class.ilXmlWriter.php";


class ilFileXMLWriter extends ilXmlWriter
{
    static $CONTENT_ATTACH_NO = 0;
    static $CONTENT_ATTACH_ENCODED = 1;
    static $CONTENT_ATTACH_ZLIB_ENCODED = 2;
    static $CONTENT_ATTACH_GZIP_ENCODED = 3;
    /**
	 * if true, file contents will be attached as base64
	 *
	 * @var int
	 */
    var $attachFileContents;

	/**
	 * Exercise Object
	 *
	 * @var ilObjFile
	 */
	var $file;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	function ilFileXMLWriter()
	{
		parent::ilXmlWriter();
		$this->attachFileContents = ilFileXMLWriter::$CONTENT_ATTACH_NO;
	}


	function setFile(ilObjFile  $file)
	{
		$this->file = & $file;
	}




    /**
     * set attachment content mode
     *
     * @param int $attachFileContents
     * @throws  ilExerciseException if mode is not supported
     */
	function setAttachFileContents($attachFileContents)
	{
		 if ($attachFileContents == ilFileXMLWriter::$CONTENT_ATTACH_GZIP_ENCODED && !function_exists("gzencode"))
		 {
		      throw new ilFileException ("Inflating with gzip is not supported", ilFileException::$ID_DEFLATE_METHOD_MISMATCH);
         }
         if ($attachFileContents == ilFileXMLWriter::$CONTENT_ATTACH_ZLIB_ENCODED && !function_exists("gzcompress"))
         {
            throw new ilFileException ("Inflating with zlib (compress/uncompress) is not supported", ilFileException::$ID_DEFLATE_METHOD_MISMATCH);
         }
 	     $this->attachFileContents = $attachFileContents;
	}


	function start()
	{
		$this->__buildHeader();

		$attribs =array (
		  "obj_id" => "il_".IL_INST_ID."_file_".$this->file->getId(),
		  "version" => $this->file->getVersion(),
		  "size" => $this->file->getFileSize(),
		  "type" => $this->file->getFileType()
		);

        $this->xmlStartTag("File", $attribs);
        $this->xmlElement("Filename",null,$this->file->getFileName());

		$this->xmlElement("Title",  null,$this->file->getTitle());
        $this->xmlElement("Description",  null,$this->file->getDescription());


        if ($this->attachFileContents)
        {
            $filename = $this->file->getDirectory($this->file->getVersion())."/".$this->file->getFileName();
            if (@is_file($filename))
            {
                $content = @file_get_contents($filename);
                $attribs = array("mode" =>"PLAIN");
                if ($this->attachFileContents == ilFileXMLWriter::$CONTENT_ATTACH_ZLIB_ENCODED)
                {
                    $attribs ["mode"] ="ZLIB";
                    $content = @gzcompress($content, 9);
                }elseif ($this->attachFileContents == ilFileXMLWriter::$CONTENT_ATTACH_GZIP_ENCODED)
                {
                    $attribs ["mode"] ="GZIP";
                    $content = @gzencode($content, 9);
                }
                $content = base64_encode($content);
                $this->xmlElement("Content",$attribs, $content);
            }

        }

        include_once("classes/class.ilHistory.php");

		$versions = ilHistory::_getEntriesForObject($this->file->getId(), $this->file->getType());

		if (count($versions)) {
		    $this->xmlStartTag("Versions");
		    foreach ($versions as $version) {
		        $info_params = $version["info_params"];
		        list($filename,$history_id) = split(",",$info_params);
		        $attribs = array (
		          "id" => $history_id,
		          "date" => ilUtil::date_mysql2time($version["date"]),
		          "usr_id" => "il_".IL_INST_ID."_usr_".$version["user_id"]
		        );
		        $this->xmlElement("Version", $attribs);
		    }
            $this->xmlEndTag("Versions");

		}

        $this->xmlEndTag("File");

		$this->__buildFooter();

		return true;
	}

	function getXML()
	{
		return $this->xmlDumpMem(false);
	}


	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE File PUBLIC \"-//ILIAS//DTD FileAdministration//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_file_3_8.dtd\">");
		$this->xmlSetGenCmt("Exercise Object");
		$this->xmlHeader();

		return true;
	}

	function __buildFooter()
	{

	}

}


?>

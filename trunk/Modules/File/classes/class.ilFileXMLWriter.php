<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

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
class ilFileXMLWriter extends ilXmlWriter
{
    static $CONTENT_ATTACH_NO = 0;
    static $CONTENT_ATTACH_ENCODED = 1;
    static $CONTENT_ATTACH_ZLIB_ENCODED = 2;
    static $CONTENT_ATTACH_GZIP_ENCODED = 3;
	static $CONTENT_ATTACH_COPY = 4;

	// begin-patch fm
	static $CONTENT_ATTACH_REST = 5;
	// end-patch fm

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

	var $omit_header = false;

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
	 * Set omit header
	 *
	 * @param	boolean	omit header
	 */
	function setOmitHeader($a_val)
	{
		$this->omit_header = $a_val;
	}

	/**
	 * Get omit header
	 *
	 * @return	boolean	omit header
	 */
	function getOmitHeader()
	{
		return $this->omit_header;
	}

	/**
	 * Set file target directories
	 *
	 * @param	string	relative file target directory
	 * @param	string	absolute file target directory
	 */
	function setFileTargetDirectories($a_rel, $a_abs)
	{
		$this->target_dir_relative = $a_rel;
		$this->target_dir_absolute = $a_abs;
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
        $this->xmlElement("Rating",  null,(int)$this->file->hasRating());

        if ($this->attachFileContents)
        {
            $filename = $this->file->getDirectory($this->file->getVersion())."/".$this->file->getFileName();
            if (@is_file($filename))
            {
				if ($this->attachFileContents == ilFileXMLWriter::$CONTENT_ATTACH_COPY)
				{
					$attribs = array("mode" =>"COPY");
					copy($filename, $this->target_dir_absolute."/".$this->file->getFileName());
					$content = $this->target_dir_relative."/".$this->file->getFileName();
					$this->xmlElement("Content",$attribs, $content);
				}
				// begin-patch fm
				elseif($this->attachFileContents == ilFileXMLWriter::$CONTENT_ATTACH_REST)
				{
					$attribs = array('mode' => "REST");
					include_once './Services/WebServices/Rest/classes/class.ilRestFileStorage.php';
					$fs = new ilRestFileStorage();
					$tmpname = $fs->storeFileForRest(base64_encode(@file_get_contents($filename)));
					$this->xmlElement("Content",$attribs, $tmpname);
				}
				// end-patch fm
				else
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

        }

        include_once("./Services/History/classes/class.ilHistory.php");

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
		if (!$this->getOmitHeader())
		{
			$this->xmlSetDtdDef("<!DOCTYPE File PUBLIC \"-//ILIAS//DTD FileAdministration//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_file_3_8.dtd\">");
			$this->xmlSetGenCmt("Exercise Object");
			$this->xmlHeader();
		}

		return true;
	}

	function __buildFooter()
	{

	}

}


?>

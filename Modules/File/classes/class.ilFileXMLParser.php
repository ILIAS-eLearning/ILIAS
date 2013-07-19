<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Exercise XML Parser which completes/updates a given file by an xml string.
*
* @author Roland Küstermann <roland@kuestermann.com>
* @version $Id: class.ilObjectXMLParser.php 12811 2006-12-08 18:37:44Z akill $
*
* @ingroup ModulesExercise
*
* @extends ilSaxParser
*/

include_once './Services/Xml/classes/class.ilSaxParser.php';
include_once 'Modules/File/classes/class.ilFileException.php';
include_once 'Services/Utilities/classes/class.ilFileUtils.php';


class ilFileXMLParser extends ilSaxParser
{
    static $CONTENT_NOT_COMPRESSED = 0;
    static $CONTENT_GZ_COMPRESSED = 1;
    static $CONTENT_ZLIB_COMPRESSED = 2;
	static $CONTENT_COPY = 4;
	// begin-patch fm
	static $CONTENT_REST = 5;
	// end-patch fm

	/**
	 * Exercise object which has been parsed
	 *
	 * @var ilObjFile
	 */
    var $file;

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
     * file contents, base64 encoded
     *
     * @var string
     */
    //var $content;
    
    /**
    *	file of temporary file where we store the file content instead of in memory
    *
    * @var string
    */
    var $tmpFilename;
    

    /**
	* Constructor
	*
	* @param   ilObjFile  $file existing file object
	* @param	string		$a_xml_file			xml data
	* @param   int $obj_id obj id of exercise which is to be updated
	* @access	public
	*/
	function ilFileXMLParser(& $file, $a_xml_data, $obj_id = -1, $mode = 0)
	{
		parent::ilSaxParser();
		$this->file = $file;
		$this->setXMLContent($a_xml_data);
		$this->obj_id = $obj_id;
		$this->result = false;
		$this->mode = $mode;
	}

	/**
	 * Set import directory
	 *
	 * @param	string	import directory
	 */
	function setImportDirectory($a_val)
	{
		$this->importDirectory = $a_val;
	}

	/**
	 * Get import directory
	 *
	 * @return	string	import directory
	 */
	function getImportDirectory()
	{
		return $this->importDirectory;
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
	* @throws   ilFileException   when obj id != - 1 and if it it does not match the id in the xml
	*                              or deflation mode is not supported
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		global $ilErr;

		global $ilLog;
		
		switch($a_name)
		{
			case 'File':
			    if (isset($a_attribs["obj_id"]))
			    {
                   $read_obj_id = ilUtil::__extractId($a_attribs["obj_id"], IL_INST_ID);
			       if ($this->obj_id != -1 && (int) $read_obj_id != -1 && (int) $this->obj_id != (int) $read_obj_id)
			       {
            	       throw new ilFileException ("Object IDs (xml $read_obj_id and argument ".$this->obj_id.") do not match!", ilFileException::$ID_MISMATCH);
                   }
			    }
                if (isset($a_attribs["type"]))
                {
					$this->file->setFileType($a_attribs["type"]);
                }
                   $this->file->setVersion($this->file->getVersion() + 1);
				break;
			case 'Content':
				$this->tmpFilename = ilUtil::ilTempnam();
			    $this->mode = ilFileXMLParser::$CONTENT_NOT_COMPRESSED;
			    $this->isReadingFile = true;
#echo $a_attribs["mode"];
			    if (isset($a_attribs["mode"]))
				{
			        if($a_attribs["mode"] == "GZIP")
			        {
                        if (!function_exists("gzread"))
                            throw new ilFileException ("Deflating with gzip is not supported", ilFileException::$ID_DEFLATE_METHOD_MISMATCH);

			            $this->mode = ilFileXMLParser::$CONTENT_GZ_COMPRESSED;
			        }
					elseif ($a_attribs["mode"] == "ZLIB")
			        {
                        if (!function_exists("gzuncompress"))
                             throw new ilFileException ("Deflating with zlib (compress/uncompress) is not supported", ilFileException::$ID_DEFLATE_METHOD_MISMATCH);

			            $this->mode = ilFileXMLParser::$CONTENT_ZLIB_COMPRESSED;
			        }
					elseif ($a_attribs["mode"] == "COPY")
			        {
			            $this->mode = ilFileXMLParser::$CONTENT_COPY;
			        }
					// begin-patch fm
					elseif($a_attribs['mode'] == 'REST')
					{
						$this->mode = ilFileXMLParser::$CONTENT_REST;
					}
					// end-patch fm
			    }
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
	    $this->cdata = trim($this->cdata);

		$GLOBALS['ilLog']->write(__METHOD__.': '.$this->cdata);

		switch($a_name)
		{
			case 'File':
			      $this->result = true;    
				break;
			case 'Filename':			    
			    if (strlen($this->cdata) == 0)
			          throw new ilFileException("Filename ist missing!");
			    
			    $this->file->setFilename($this->cdata);
			    $this->file->setTitle($this->cdata);
			    
				break;
			case 'Title':
   			    $this->file->setTitle(trim($this->cdata));
   			    break;
			case 'Description':
			    $this->file->setDescription(trim($this->cdata));
				break;
			case 'Rating':
			    $this->file->setRating((bool)$this->cdata);
				break;
			case 'Content':
				$GLOBALS['ilLog']->write($this->mode);
				$this->isReadingFile = false;
				$baseDecodedFilename = ilUtil::ilTempnam();
				if ($this->mode == ilFileXMLParser::$CONTENT_COPY)
				{
					$this->tmpFilename = $this->getImportDirectory()."/".$this->cdata;
				}
				// begin-patch fm
				elseif($this->mode == ilFileXMLParser::$CONTENT_REST)
				{
					include_once './Services/WebServices/Rest/classes/class.ilRestFileStorage.php';
					$storage = new ilRestFileStorage();
					$this->tmpFilename = $storage->getStoredFilePath($this->cdata);
					if(!ilFileUtils::fastBase64Decode($this->tmpFilename, $baseDecodedFilename))
					{
						throw new ilFileException("Base64-Decoding failed", ilFileException::$DECOMPRESSION_FAILED);
					}
					$this->tmpFilename = $baseDecodedFilename;
				}
				// end-patch fm
				else
				{
					if (!ilFileUtils::fastBase64Decode($this->tmpFilename, $baseDecodedFilename))
					{
							throw new ilFileException ("Base64-Decoding failed", ilFileException::$DECOMPRESSION_FAILED);
					}
					if ($this->mode == ilFileXMLParser::$CONTENT_GZ_COMPRESSED)
					{
						if (!ilFileUtils::fastGunzip ($baseDecodedFilename, $this->tmpFilename))
						{
							throw new ilFileException ("Deflating with fastzunzip failed", ilFileException::$DECOMPRESSION_FAILED);
						}
						unlink ($baseDecodedFilename);
					}
					elseif ($this->mode == ilFileXMLParser::$CONTENT_ZLIB_COMPRESSED)
					{
						if (!ilFileUtils::fastGunzip ($baseDecodedFilename, $this->tmpFilename))
						{
							throw new ilFileException ("Deflating with fastDecompress failed", ilFileException::$DECOMPRESSION_FAILED);
						}
						unlink ($baseDecodedFilename);
					}
					else
					{
						$this->tmpFilename = $baseDecodedFilename;
					}
				}
				//$this->content = $content;
				$this->file->setFileSize(filesize($this->tmpFilename)); // strlen($this->content));
				
				// if no file type is given => lookup mime type
				if(!$this->file->getFileType())
				{
					global $ilLog;
					
					#$ilLog->write(__METHOD__.': Trying to detect mime type...');
					include_once('./Services/Utilities/classes/class.ilFileUtils.php');
					$this->file->setFileType(ilFileUtils::_lookupMimeType($this->tmpFilename));
				}
				
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
			// begin-patch fm
			if ($this->isReadingFile && $this->mode != ilFileXMLParser::$CONTENT_COPY && $this->mode != ilFileXMLParser::$CONTENT_REST)
			// begin-patch fm
			{
  			$handle = fopen($this->tmpFilename, "a");
				fwrite ($handle, $a_data);
				fclose ($handle);
			} else
				$this->cdata .= $a_data;
		}
	}

	/**
	 * update file according to filename and version, does not update history
	 * has to be called after (!) file save for new objects, since file storage will be initialised with obj id.
	 *
	 */
	public function setFileContents ()
	{
		global $ilLog;
		
		#$ilLog->write(__METHOD__.' '.filesize($this->tmpFilename));

		if (filesize ($this->tmpFilename) == 0) {
			return;
		}

		$filedir = $this->file->getDirectory($this->file->getVersion());
		#$ilLog->write(__METHOD__.' '.$filedir);
		
		if (!is_dir($filedir))
		{
			$this->file->createDirectory();
			ilUtil::makeDir($filedir);
		}
		   
		$filename = $filedir."/".$this->file->getFileName();
		if (file_exists($filename))
			unlink($filename);
//echo "-".$this->tmpFilename."-".$filename."-"; exit;
		return rename($this->tmpFilename, $filename);
	   // @file_put_contents($filename, $this->content);
	}


	/**
	 * update file according to filename and version and create history entry
	 * has to be called after (!) file save for new objects, since file storage will be initialised with obj id.
	 *
	 */
	public function updateFileContents ()
	{
      if ($this->setFileContents()) 
      {
	    	require_once("./Services/History/classes/class.ilHistory.php");
				ilHistory::_createEntry($this->file->getId(), "replace", $this->file->getFilename().",".$this->file->getVersion());
				$this->file->addNewsNotification("file_updated");	
			}
	}

	/**
	 * starts parsing an changes object by side effect.
	 *
	 * @throws ilFileException when obj id != - 1 and if it it does not match the id in the xml
	 * @return boolean true, if no errors happend.
	 *
	 */
	public function start () {
	    $this->startParsing();
	    return $this->result > 0;
	}

}
?>
<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
* This class represents a file property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilFileInputGUI extends ilSubEnabledFormPropertyGUI
{
	private $filename;
	private $filename_post;
	
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		global $lng;
		
		parent::__construct($a_title, $a_postvar);
		$this->setType("file");
		$this->setHiddenTitle("(".$lng->txt("form_file_input").")");
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		if (!is_array($a_values[$this->getPostVar()]))
		{
			$this->setValue($a_values[$this->getPostVar()]);
		}
		$this->setFilename($a_values[$this->getFileNamePostVar()]);
	}

	/**
	* Set Value. (used for displaying file title of existing file below input field)
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getValue()
	{
		return $this->value;
	}
	
	/**
	 * Set filename value (if filename selection is enabled)
	 *  
	 * @param string $a_val
	 */
	public function setFilename($a_val)
	{
		$this->filename = $a_val;
	}
	
	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getFilename()
	{
		return $this->filename;
	}
	
	

	/**
	* Set Accepted Suffixes.
	*
	* @param	array	$a_suffixes	Accepted Suffixes
	*/
	function setSuffixes($a_suffixes)
	{
		$this->suffixes = $a_suffixes;
	}

	/**
	* Get Accepted Suffixes.
	*
	* @return	array	Accepted Suffixes
	*/
	function getSuffixes()
	{
		return $this->suffixes;
	}
	
	/**
	 * If enabled, users get the possibility to enter a filename for the uploaded file 
	 *
	 * @access public
	 * @param string post variable
	 * 
	 */
	public function enableFileNameSelection($a_post_var)
	{
	 	$this->filename_selection = true;
	 	$this->filename_post = $a_post_var;
	}
	
	/**
	 * Check if filename selection is enabled
	 *
	 * @access public
	 * @return bool enabled/disabled 
	 */
	public function isFileNameSelectionEnabled()
	{
	 	return $this->filename_selection ? true : false;
	}
	
	/**
	 * Get file name post var
	 *
	 * @access public
	 * @param string file name post var
	 * 
	 */
	public function getFileNamePostVar()
	{
	 	return $this->filename_post;
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;

		// remove trailing '/'
		while (substr($_FILES[$this->getPostVar()]["name"],-1) == '/')
		{
			$_FILES[$this->getPostVar()]["name"] = substr($_FILES[$this->getPostVar()]["name"],0,-1);
		}

		$filename = $_FILES[$this->getPostVar()]["name"];
		$filename_arr = pathinfo($_FILES[$this->getPostVar()]["name"]);
		$suffix = $filename_arr["extension"];
		$mimetype = $_FILES[$this->getPostVar()]["type"];
		$size_bytes = $_FILES[$this->getPostVar()]["size"];
		$temp_name = $_FILES[$this->getPostVar()]["tmp_name"];
		$error = $_FILES[$this->getPostVar()]["error"];
		$_POST[$this->getPostVar()] = $_FILES[$this->getPostVar()];

		// error handling
		if ($error > 0)
		{
			switch ($error)
			{
				case UPLOAD_ERR_INI_SIZE:
					$this->setAlert($lng->txt("form_msg_file_size_exceeds"));
					return false;
					break;
					 
				case UPLOAD_ERR_FORM_SIZE:
					$this->setAlert($lng->txt("form_msg_file_size_exceeds"));
					return false;
					break;
	
				case UPLOAD_ERR_PARTIAL:
					$this->setAlert($lng->txt("form_msg_file_partially_uploaded"));
					return false;
					break;
	
				case UPLOAD_ERR_NO_FILE:
					if ($this->getRequired())
					{
						if (!strlen($this->getValue()))
						{
							$this->setAlert($lng->txt("form_msg_file_no_upload"));
							return false;
						}
					}
					break;
	 
				case UPLOAD_ERR_NO_TMP_DIR:
					$this->setAlert($lng->txt("form_msg_file_missing_tmp_dir"));
					return false;
					break;
					 
				case UPLOAD_ERR_CANT_WRITE:
					$this->setAlert($lng->txt("form_msg_file_cannot_write_to_disk"));
					return false;
					break;
	 
				case UPLOAD_ERR_EXTENSION:
					$this->setAlert($lng->txt("form_msg_file_upload_stopped_ext"));
					return false;
					break;
			}
		}
		
		// check suffixes
		if ($_FILES[$this->getPostVar()]["tmp_name"] != "" &&
			is_array($this->getSuffixes()))
		{
			if (!in_array(strtolower($suffix), $this->getSuffixes()))
			{
				$this->setAlert($lng->txt("form_msg_file_wrong_file_type"));
				return false;
			}
		}
		
		// virus handling
		if ($_FILES[$this->getPostVar()]["tmp_name"] != "")
		{
			$vir = ilUtil::virusHandling($temp_name, $filename);
			if ($vir[0] == false)
			{
				$this->setAlert($lng->txt("form_msg_file_virus_found")."<br />".$vir[1]);
				return false;
			}
		}
		
		return true;
	}

	/**
	* Insert property html
	*/
	function insert(&$a_tpl)
	{
		global $lng;
		
		$f_tpl = new ilTemplate("tpl.prop_file.html", true, true, "Services/Form");
		
		
		// show filename selection if enabled
		if($this->isFileNameSelectionEnabled())
		{
			$f_tpl->setCurrentBlock('filename');
			$f_tpl->setVariable('POST_FILENAME',$this->getFileNamePostVar());
			$f_tpl->setVariable('VAL_FILENAME',$this->getFilename());
			$f_tpl->setVariable('FILENAME_ID',$this->getFieldId());
			$f_tpl->setVAriable('TXT_FILENAME_HINT',$lng->txt('if_no_title_then_filename'));
			$f_tpl->parseCurrentBlock();
		}
		else
		{
			if (trim($this->getValue() != ""))
			{
				$f_tpl->setCurrentBlock('prop_file_propval');
				$f_tpl->setVariable('FILE_VAL', $this->getValue());
				$f_tpl->parseCurrentBlock();
			}
		}

		$this->outputSuffixes($f_tpl);
		
		$f_tpl->setVariable("POST_VAR", $this->getPostVar());
		$f_tpl->setVariable("ID", $this->getFieldId());
		$f_tpl->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice")." ".
			$this->getMaxFileSizeString());
			
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $f_tpl->get());
		$a_tpl->parseCurrentBlock();
	}

	protected function outputSuffixes($a_tpl, $a_block = "allowed_suffixes")
	{
		global $lng;
		
		if (is_array($this->getSuffixes()))
		{
			$suff_str = $delim = "";
			foreach($this->getSuffixes() as $suffix)
			{
				$suff_str.= $delim.".".$suffix;
				$delim = ", ";
			}
			$a_tpl->setCurrentBlock($a_block);
			$a_tpl->setVariable("TXT_ALLOWED_SUFFIXES",
				$lng->txt("file_allowed_suffixes")." ".$suff_str);
			$a_tpl->parseCurrentBlock();
		}
	}
	
	protected function getMaxFileSizeString()
	{
		// get the value for the maximal uploadable filesize from the php.ini (if available)
		$umf = get_cfg_var("upload_max_filesize");
		// get the value for the maximal post data from the php.ini (if available)
		$pms = get_cfg_var("post_max_size");
		
		//convert from short-string representation to "real" bytes
		$multiplier_a=array("K"=>1024, "M"=>1024*1024, "G"=>1024*1024*1024);
		
		$umf_parts=preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
        $pms_parts=preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
        
        if (count($umf_parts) == 2) { $umf = $umf_parts[0]*$multiplier_a[$umf_parts[1]]; }
        if (count($pms_parts) == 2) { $pms = $pms_parts[0]*$multiplier_a[$pms_parts[1]]; }
        
        // use the smaller one as limit
		$max_filesize = min($umf, $pms);

		if (!$max_filesize) $max_filesize=max($umf, $pms);
	
    	//format for display in mega-bytes
		$max_filesize = sprintf("%.1f MB",$max_filesize/1024/1024);
		
		return $max_filesize;
	}
}

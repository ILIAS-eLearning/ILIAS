<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/exceptions/class.ilImportException.php"); 
 
/** 
 * manifest.xml file not found-exception for import
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$ 
 * 
 */
class ilManifestFileNotFoundImportException extends ilImportException
{
	private $manifest_dir = "";
	private $tmp_dir = "";
	
	/**
	 * Set manifest directory
	 *
	 * @param string $a_val manifest directory	
	 */
	function setManifestDir($a_val)
	{
		$this->manifest_dir = $a_val;
	}
	
	/**
	 * Get manifest directory
	 *
	 * @return string manifest directory
	 */
	function getManifestDir()
	{
		return $this->manifest_dir;
	}
	
	/**
	 * Set temporary directory
	 *
	 * @param string $a_val temporary directory	
	 */
	function setTmpDir($a_val)
	{
		$this->tmp_dir = $a_val;
	}
	
	/**
	 * Get temporary directory
	 *
	 * @return string temporary directory
	 */
	function getTmpDir()
	{
		return $this->tmp_dir;
	}
}
?>

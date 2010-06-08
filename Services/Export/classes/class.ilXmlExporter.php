<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Xml Exporter class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
abstract class ilXmlExporter
{
	protected $dir_relative;
	protected $dir_absolute;

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{

	}

	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		target release
	 * @param	string		id
	 * @return	string		xml string
	 */
	abstract public function getXmlRepresentation($a_entity, $a_target_release, $a_id);

	abstract public function init();


	/**
	 * Export directories
	 *
	 * @param	string		relative directory
	 * @param	string		absolute directory
	 */
	public function setExportDirectories($a_dir_relative, $a_dir_absolute)
	{
		$this->dir_relative = $a_dir_relative;
		$this->dir_absolute = $a_dir_absolute;
	}

	/**
	 * Get relative export directory
	 *
	 * @return	string	relative directory
	 */
	function getRelativeExportDirectory()
	{
		return $this->dir_relative;
	}

	/**
	 * Get absolute export directory
	 *
	 * @return	string	absolute directory
	 */
	function getAbsoluteExportDirectory()
	{
		return $this->dir_absolute;
	}

	/**
	 * Get head dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
	{
		return array();
	}

	/**
	 * Get tail dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
		return array();
	}

}
?>

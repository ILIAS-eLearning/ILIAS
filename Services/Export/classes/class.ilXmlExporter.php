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
	 * @param	mixed		string or array of ids
	 * @return	string		xml string
	 */
	abstract public function getXmlRepresentation($a_entity, $a_target_release, $a_ids);

	abstract public function init();

	/**
	 * Get export start tag
	 *
	 * @param	string	enity
	 * @param	string	target release
	 * @return	string	start tag
	 */
	function getExportStartTag($a_entity, $a_target_release)
	{
		return '<export install_id="'.IL_INST_ID.'" install_url="'.ILIAS_HTTP_PATH.'" '.
			'entity="'.$a_entity.'" version="'.$a_target_release.'">';
	}

	/**
	 * Get export end tag
	 * 
	 * @return	string	end tag
	 */
	function getExportEndTag()
	{
		return "</export>";
	}

	/**
	 * Get export start tag
	 *
	 * @param	string	id
	 * @return	string	start tag
	 */
	function getExportRecordStartTag($a_id)
	{
		return '<export_rec id="'.$a_id.'" >';
	}

	/**
	 * Get export end tag
	 *
	 * @return	string	end tag
	 */
	function getExportRecordEndTag()
	{
		return "</export_rec>";
	}

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	public function setExportDirectories($a_dir_relative, $a_dir_absolute)
	{
		$this->dir_relative = $a_dir_relative;
		$this->dir_absolute = $a_dir_absolute;
	}

	/**
	 * Get relative export directory
	 *
	 * @param
	 * @return
	 */
	function getRelativeExportDirectory()
	{
		return $this->dir_relative;
	}

	/**
	 * Get absolute export directory
	 *
	 * @param
	 * @return
	 */
	function getAbsoluteExportDirectory()
	{
		return $this->dir_absolute;
	}

	/**
	 * Get head dependencies
	 *
	 * @param
	 * @return
	 */
	public function getXmlExportHeadDependencies($a_target_release, $a_id)
	{
		return array();
	}

	/**
	 * Get tail dependencies
	 *
	 * @param
	 * @return
	 */
	public function getXmlExportTailDependencies($a_target_release, $a_id)
	{
		return array();
	}

}
?>

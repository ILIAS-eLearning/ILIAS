<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Services/Preview/classes/class.ilPreviewRenderer.php");
require_once("Services/Preview/classes/class.ilFilePreviewRenderer.php");

/**
 * Displays an overview of all loaded preview renderers.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilRendererTableGUI extends ilTable2GUI
{
	/**
	 * Creates a new ilRendererTableGUI instance.
	 * 
	 * @param ilObjFileGUI $a_parent_obj The parent object.
	 * @param string $a_parent_cmd The parent command.
	 * @param int $a_file_id The id of the file object
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		// general properties
		$this->setRowTemplate("tpl.renderer_row.html", "Services/Preview");
		$this->setLimit(9999);
		$this->setEnableHeader(true);
		$this->disable("footer");
		$this->setExternalSorting(true);
		$this->setEnableTitle(true);
		$this->setTitle($lng->txt("loaded_preview_renderers"));
		
		$this->addColumn($lng->txt("name"));
		$this->addColumn($lng->txt("type"));
		$this->addColumn($lng->txt("renderer_supported_repo_types"));
		$this->addColumn($lng->txt("renderer_supported_file_types"));
	}

	/**
	 * Standard Version of Fill Row. Most likely to
	 * be overwritten by derived class.
	 */
	protected function fillRow($renderer)
	{
		global $lng, $ilCtrl, $ilAccess;
		
		$name = $renderer->getName();
		$type = $lng->txt("renderer_type_" . ($renderer->isPlugin() ? "plugin" : "builtin"));
		
		$repo_types = array();
		foreach ($renderer->getSupportedRepositoryTypes() as $repo_type)
			$repo_types[] = $lng->txt($repo_type);
		
		// supports files?
		$file_types = "";
		if ($renderer instanceof ilFilePreviewRenderer)
		{
			$file_types = implode(", ", $renderer->getSupportedFileFormats());
		}
		
		// fill template
		$this->tpl->setVariable("TXT_NAME", $name);
		$this->tpl->setVariable("TXT_TYPE", $type);
		$this->tpl->setVariable("TXT_REPO_TYPES", implode(", ", $repo_types));
		$this->tpl->setVariable("TXT_FILE_TYPES", $file_types);
	}
}
?>
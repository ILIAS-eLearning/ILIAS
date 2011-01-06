<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Special item handling (e.g. entry page)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004SpecialItem
{
	/**
	 * Export special item to scorm
	 */
	static function exportScorm($a_inst, $a_target_dir, $expLog)
	{
		ilUtil::makeDir($a_target_dir.'/entry_page');

		// similar to ilSCORM2004Sco->exportHTMLPagebjects (use template!)

		$tpl = new ilTemplate("tpl.sco.html", true, true, "Modules/Scorm2004");
		$tpl->setCurrentBlock("page");
		$tpl->setVariable("PAGE", "Entry Page...");
		$content = $tpl->get();

		fputs(fopen($a_target_dir.'/entry_page/index.html','w+'), $content);
	}

	/**
	 * Add entry page item XML to writer
	 */
	static function addEntryPageItemXML($a_writer, $a_slm_obj)
	{
		$a_writer->xmlStartTag("item", array(
			"identifier" => "il_".IL_INST_ID."_entry_page_".$a_slm_obj->getId(),
			"identifierref" => "il_".IL_INST_ID."_entry_page_".$a_slm_obj->getId()."_ref",
			"isvisible" => false
		)
		);
		$a_writer->xmlElement("title", array(), "Entry Page");
		$a_writer->xmlElement("imsss:sequencing", array());
		$a_writer->xmlEndTag("item");
	}

	/**
	 * Add entry page resource XML to writer
	 */
	static function addEntryPageResourceXML($a_writer, $a_slm_obj)
	{
		$a_writer->xmlStartTag("resource", array(
			"identifier" => "il_".IL_INST_ID."_entry_page_".$a_slm_obj->getId()."_ref",
			"type" => "webcontent",
			"adlcp:scormType" => "asset",
			"href" => "entry_page/index.html"
			)
		);
		$a_writer->xmlEndTag("resource");
	}

}
?>

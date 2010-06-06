<?php 
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for Xml exporter classes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
interface ilXmlExporter
{
	public function setExportDirectories($a_dir_relative, $a_dir_absolute);

	public function getXmlRepresentation($a_entity, $a_target_release, $a_ids);

	public function getXmlExportHeadDependencies($a_target_release, $a_id);

	public function getXmlExportTailDependencies($a_target_release, $a_id);
}
?>
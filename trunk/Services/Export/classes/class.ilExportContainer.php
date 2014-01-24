<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilExportFileInfo.php';
include_once './Services/Export/classes/class.ilExport.php';


/**
* Export Container
*
* @author	Stefan Meyer <meyer@leifos.com>
* @version	$Id$
* @ingroup	ServicesExport
*/
class ilExportContainer extends ilExport
{
	private $cont_export_dir = '';
	private $cont_manifest_writer = null;
	private $eo = null;
	
	
	/**
	 * Constructor
	 * @param ilExportOptions $eo
	 * @return 
	 */
	public function __construct(ilExportOptions $eo)
	{
		$this->eo = $eo;
		parent::__construct();		
	}
	
	/**
	 * Export a container
	 * 
	 * @param object $a_type
	 * @param object $a_obj_id
	 * @param object $a_target_release
	 * @return 
	 */
	public function exportObject($a_type, $a_id, $a_target_release)
	{
		// Create base export directory
		ilExport::_createExportDirectory($a_id, "xml", $a_type);
		$export_dir = ilExport::_getExportDirectory($a_id, "xml", $a_type);
		$ts = time();
		$sub_dir = $ts."__".IL_INST_ID."__".$a_type."_".$a_id;
		
		$this->cont_export_dir = $export_dir.DIRECTORY_SEPARATOR.$sub_dir;
		ilUtil::makeDirParents($this->cont_export_dir);
		
		$GLOBALS['ilLog']->write(__METHOD__.' using base directory: '.$this->export_run_dir);
		
		$this->manifestWriterBegin($a_type, $a_id, $a_target_release);
		$this->addContainer();
		$this->addSubitems($a_id,$a_type,$a_target_release);
		$this->manifestWriterEnd($a_type, $a_id, $a_target_release);

		ilUtil::zip($this->cont_export_dir, $this->cont_export_dir.'.zip');
		ilUtil::delDir($this->cont_export_dir);
	}
	
	/**
	 * Write container manifest
	 * @return 
	 */
	protected function manifestWriterBegin($a_type, $a_id, $a_target_release)
	{
		$GLOBALS['ilLog']->write(__METHOD__.': wrinting manifest');
		include_once "./Services/Xml/classes/class.ilXmlWriter.php";
		$this->cont_manifest_writer = new ilXmlWriter();
		$this->cont_manifest_writer->xmlHeader();
		$this->cont_manifest_writer->xmlStartTag(
			'Manifest',
			array(
				"MainEntity" => $a_type, 
				"Title" => ilObject::_lookupTitle($a_id), 
				"TargetRelease" => $a_target_release,
				"InstallationId" => IL_INST_ID, 
				"InstallationUrl" => ILIAS_HTTP_PATH)
		);
	}
	
	/**
	 * Add container description
	 * @return 
	 */
	protected function addContainer()
	{
		
	}
	
	
	/**
	 * Add subitems
	 * @param object $a_id
	 * @param object $a_type
	 * @return 
	 */
	protected function addSubitems($a_id,$a_type,$a_target_release)
	{
		$GLOBALS['ilLog']->write(__METHOD__);
		$set_number = 1;
		foreach($this->eo->getSubitemsForExport() as $ref_id)
		{
			// get last export file
			$obj_id = ilObject::_lookupObjId($ref_id);
			
			$expi = ilExportFileInfo::lookupLastExport($obj_id, 'xml',$a_target_release);
			
			if(!$expi instanceof ilExportFileInfo)
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Cannot find export file for refId '.$ref_id.', type '.ilObject::_lookupType($a_id));
				continue;				
			}
			
			$exp_dir = ilExport::_getExportDirectory($obj_id,'xml',ilObject::_lookupType($obj_id));
			$exp_full = $exp_dir.DIRECTORY_SEPARATOR.$expi->getFilename();
			
			$GLOBALS['ilLog']->write(__METHOD__.': zip path '.$exp_full);
			
			// Unzip
			ilUtil::unzip($exp_full,true,false);
			
			// create set directory
			ilUtil::makeDirParents($this->cont_export_dir.DIRECTORY_SEPARATOR.'set_'.$set_number);
			
			// cut .zip
			$new_path_rel = 'set_'.$set_number.DIRECTORY_SEPARATOR.$expi->getBasename();
			$new_path_abs = $this->cont_export_dir.DIRECTORY_SEPARATOR.$new_path_rel;
			
			$GLOBALS['ilLog']->write(__METHOD__.': '.$new_path_rel.' '.$new_path_abs);

			// Move export
			rename(
				$exp_dir.DIRECTORY_SEPARATOR.$expi->getBasename(),
				$new_path_abs
			);
			
			$GLOBALS['ilLog']->write($exp_dir.DIRECTORY_SEPARATOR.$expi->getBasename().' -> '.$new_path_abs);
			
			// Delete latest container xml of source
			if($a_id == $obj_id)
			{
				$expi->delete();
				if(file_exists($exp_full))
				{
					$GLOBALS['ilLog']->write(__METHOD__.': Deleting'. $exp_full);
					unlink($exp_full);
				}
			}
			
			$this->cont_manifest_writer->xmlElement(
				'ExportSet',
				array(
					'Path' 	=> $new_path_rel,
					'Type'	=> ilObject::_lookupType($obj_id)
				)
			);
			
			
			++$set_number;
		}
	}
	
	/**
	 * Write manifest footer
	 * @param object $a_type
	 * @param object $a_id
	 * @param object $a_target_release
	 * @return 
	 */
	protected function manifestWriterEnd($a_type, $a_id, $a_target_release)
	{
		$this->cont_manifest_writer->xmlEndTag('Manifest');
		$GLOBALS['ilLog']->write(__METHOD__.': '.$this->cont_export_dir.DIRECTORY_SEPARATOR.'manifest.xml');
		$this->cont_manifest_writer->xmlDumpFile($this->cont_export_dir.DIRECTORY_SEPARATOR.'manifest.xml',true);
	}
}
?>
<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilPDFHelper.php';
require_once 'Services/Utilities/classes/class.ilUtil.php';
require_once 'Services/Calendar/classes/class.ilDatePresentation.php';

/**
 * @author  Stefan Hecken <stefan.hecken@concepts-and-training.de> 
 * @version $Id$
 *
 */
class gevDecentralTrainingCreateSchedulePDF
{
	/**
	 * @var string
	 */
	private $format = 'A4';

	/**
	 * @var string
	 */
	private $unit = 'cm';

	/**
	 * @var string
	 */
	private $mode = 'L';

	/**
	 * @var array
	 */
	protected static $supported_fonts = array(
		'Courier',
		'Helvetica',
		'Symbol',
		'Times-Roman',
		'ZapfDingbats',
		'Arial',
		'Times'
	);

	public function __construct($a_crs_id) {
		global $lng;

		$this->lng = $lng;
		$this->crs_utils = gevCourseUtils::getInstance($a_crs_id);
		require_once("Services/GEV/Utils/classes/class.gevCourseBuildingBlockUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$this->blocks 	= gevCourseBuildingBlockUtils::getAllCourseBuildingBlocks(gevObjectUtils::getRefId($a_crs_id));
		$this->pdf 		= new ilPDFHelper($this->mode, $this->unit, $this->format);
		$this->customId = "";
		
		$this->spaceLeft = 1.0;
		$this->spaceRight = 1.0;
		$this->lastYValue = 0;
		$this->maxRight = 28;
		$this->maxPageHeight = 19;
		$this->pageCounter = 0;

		$this->headFontSize = 11;
		$this->headerFontName = "Arial";
		$this->headerText = "Ablaufplan";
		$this->headerBold = "B";
		$this->headerSpaceTop = 1.5;

		$this->crsInfoFontSize = 10;
		$this->crsInfoFontName = "Arial";
		$this->crsInfoText = "Generelle Angaben zum Training";
		$this->crsInfoBold = "B";
		$this->crsInfoNoBold = "";
		$this->crsInfoSpaceTop = 3.0;
		$this->crsInfoSpaceTopAdd = 0.5;
		$this->crsInfoSecondColoumnSpaceLeft = 7;

		$this->crsBlockFontSize = 10;
		$this->crsBlockFontName = "Arial";
		$this->crsBlockBold = "B";
		$this->crsBlockNoBold = "";
		$this->crsBlockSpaceTop = 2.0;
		$this->crsBlockSpaceTopAdd = 0.5;
		$this->crsBlockSecondColoumnSpaceLeft = 7;
		$this->crsBlockColoumnSpace = 0.3;
		$this->number_couloums_after_time = 3;
		$this->crsBlockSpaceUeAndWp = 2;
		$this->crsBlockMultiplikatorColoumnWidth = $this->number_couloums_after_time + 1.2;
		
		$this->crsFooterFontSize = 8;
		$this->crsFooterFontName = "Arial";
		$this->crsFooterNoBold = "";

	}

	/**
	 * Generates the PDF containing the bill and delivers it to the client.
	 * @throws ilException
	 */
	public function deliver($a_filename = "Bill.pdf") {
		$temp_file = ilUtil::ilTempnam();
		$this->build($temp_file);
		ilUtil::deliverFile($temp_file, $a_filename, 'application/pdf', false, true);
	}

	/**
	 * Generates the PDF containing the Bill for the given Bill and stores it at the given path.
	 * @param string $a_path Storage path in file system, e.g. /srv/www/htdocs/ilias/pdfs/output.pdf
	 * @throws ilException
	 */
	public function build($a_path) {
		$file_info = pathinfo($a_path);

		if(!is_writeable($file_info['dirname']))
		{
			throw new ilException('Cannot write file to directory: ' . $file_info['dirname']);
		}

		$this->pdf->SetMargins(0, 0, $this->spaceRight);
		$this->pdf->AliasNbPages();
		$this->pdf->AddPage();
		$this->pageCounter++;

		$this->createHeadline();
		$this->createCrsInfo();
		$this->createBlockInfo();
		$this->createFooterRow();

		$this->pdf->Output($a_path, 'F');
	}

	/**
	 *
	 */
	private function createHeadline() {
		$this->pdf->SetFont($this->headerFontName, $this->headerBold, $this->headerFontSize);
		$this->pdf->WriteText($this->spaceLeft, $this->headerSpaceTop, $this->headerText);
	}

	/**
	*
	*/
	private function createCrsInfo() {
		$meta_data = $this->crs_utils->getListMetaData();
		$this->customId = $meta_data["Nummer der Maßnahme"];
		$trainers = explode(", ", $meta_data["Trainer"]);
		$jump_trainers = count($trainers) - 1;

		$this->pdf->SetFont($this->crsInfoFontName, $this->crsInfoBold, $this->crsInfoFontSize);
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop, $this->crsInfoText);

		$this->pdf->Line($this->spaceLeft,$this->pdf->getY() + 0.2,$this->maxRight,$this->pdf->getY() + 0.2);
		$this->pdf->Line($this->spaceLeft,$this->pdf->getY() + 0.3,$this->maxRight,$this->pdf->getY() + 0.3);

		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (2 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Titel"));
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (3 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Untertitel"));
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (4 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Nummer der Maßnahme"));
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (5 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Datum"));
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (6 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Veranstaltungsort"));
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (7 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Bildungspunkte"));
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (8 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Trainer"));
		//$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + ((9 + $jump_trainers) * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Trainingsbetreuer"));
		//$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + ((10 + $jump_trainers) * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Fachlich verantwortlich"));
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + ((9 + $jump_trainers) * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars("Bei Rückfragen"));

		$this->pdf->SetFont($this->crsInfoFontName, $this->crsInfoNoBold, $this->crsInfoFontSize);
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (2 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars($meta_data["Titel"]));
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (3 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars($meta_data["Untertitel"]));
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (4 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars($meta_data["Nummer der Maßnahme"]));
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (5 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars($meta_data["Datum"]));
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (6 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars($meta_data["Veranstaltungsort"]));
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (7 * $this->crsInfoSpaceTopAdd), $this->encodeSpecialChars($meta_data["Bildungspunkte"]));
		
		foreach ($trainers as $key => $value) {
			$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + ((8+$key) * $this->crsInfoSpaceTopAdd), $value);
		}
		
		//$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + ((9 + $jump_trainers) * $this->crsInfoSpaceTopAdd), $meta_data["Trainingsbetreuer"]);
		/*$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft
								,$this->crsInfoSpaceTop + ((10 + $jump_trainers) * $this->crsInfoSpaceTopAdd)
								, $meta_data["Fachlich verantwortlich"]);*/
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + ((9 + $jump_trainers) * $this->crsInfoSpaceTopAdd), $meta_data["Bei Rückfragen"]);
		
		$this->pdf->Line($this->spaceLeft,$this->pdf->getY() + 1,$this->maxRight,$this->pdf->getY() + 1);
		$this->pdf->Line($this->spaceLeft,$this->pdf->getY() + 1.1,$this->maxRight,$this->pdf->getY() + 1.1);
	}

	/**
	*
	*/
	private function createBlockInfo() {
		$date_string_width = $this->pdf->GetStringWidth("99:99:99") + $this->crsBlockColoumnSpace;
		$coloumn_width = ((($this->maxRight-4) - $this->spaceLeft) / $this->number_couloums_after_time) - ($this->crsBlockMultiplikatorColoumnWidth * $this->crsBlockColoumnSpace);

		$x_firstColoumn = $this->spaceLeft;
		$x_secondColoumn = $x_firstColoumn + $date_string_width;
		$x_thirdColoumn = $x_secondColoumn + $date_string_width;
		$x_fourthColoumn = $x_thirdColoumn + $coloumn_width + $this->crsBlockColoumnSpace;
		$x_fithColoumn = $x_fourthColoumn + $coloumn_width + $this->crsBlockColoumnSpace;
		$x_sixthColoumn = $x_fithColoumn + $coloumn_width + $this->crsBlockColoumnSpace;
		$x_seventhColoumn = $x_sixthColoumn + $this->crsBlockSpaceUeAndWp + $this->crsBlockColoumnSpace;

		$y_value = $this->pdf->getY() + $this->crsBlockSpaceTop;

		$this->pdf->SetFont($this->crsBlockFontName, $this->crsBlockBold, $this->crsBlockFontSize);
		$this->pdf->WriteText($x_firstColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_from")));
		$this->pdf->WriteText($x_secondColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_to")));
		$this->pdf->WriteText($x_thirdColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_block")));
		$this->pdf->WriteText($x_fourthColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_content")));
		$this->pdf->WriteText($x_fithColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_lern_dest")));
		$this->pdf->WriteText($x_sixthColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_building_ue")));
		$this->pdf->WriteText($x_seventhColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_building_wp")));

		$this->pdf->Line($this->spaceLeft,$y_value + 0.2,$this->maxRight,$y_value + 0.2);
		$this->pdf->Line($this->spaceLeft,$y_value + 0.3,$this->maxRight,$y_value + 0.3);

		$this->pdf->setXY($this->pdf->GetX(), $y_value + 0.3);

		$this->pdf->SetFont($this->crsBlockFontName, $this->crsBlockNoBold, $this->crsBlockFontSize);
		$jumper = 0;
		foreach ($this->blocks as $key => $value) {
			$y_value = $this->pdf->GetY() + $this->crsBlockSpaceTopAdd;

			if($y_value + $max_height - (2*$this->crsBlockSpaceTopAdd) > $this->maxPageHeight) {
				$this->createFooterRow();
				$this->pdf->AddPage();
				$this->pageCounter++;

				$y_value = $this->pdf->getY() + $this->crsBlockSpaceTop;
				$this->pdf->SetFont($this->crsBlockFontName, $this->crsBlockBold, $this->crsBlockFontSize);
				$this->pdf->WriteText($x_firstColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_from")));
				$this->pdf->WriteText($x_secondColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_to")));
				$this->pdf->WriteText($x_thirdColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_block")));
				$this->pdf->WriteText($x_fourthColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_content")));
				$this->pdf->WriteText($x_fithColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_crs_building_block_lern_dest")));
				$this->pdf->WriteText($x_sixthColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_building_ue")));
				$this->pdf->WriteText($x_seventhColoumn, $y_value, $this->encodeSpecialChars($this->lng->txt("gev_dec_building_wp")));

				$this->pdf->Line($this->spaceLeft,$y_value + 0.2,$this->maxRight,$y_value + 0.2);
				$this->pdf->Line($this->spaceLeft,$y_value + 0.3,$this->maxRight,$y_value + 0.3);
				$this->pdf->SetFont($this->crsBlockFontName, $this->crsBlockNoBold, $this->crsBlockFontSize);
				$y_value += $this->crsBlockSpaceTopAdd + 0.3;
			}
			
			$base = $value->getBuildingBlock();
			$max_height = $this->calcMaxRowHeight($base->getTitle(),$base->getContent(), $base->getLearningDestination(), $coloumn_width);

			$this->pdf->WriteText($x_firstColoumn, $y_value, $this->encodeSpecialChars($value->getStartTime()));
			$this->pdf->WriteText($x_secondColoumn, $y_value, $this->encodeSpecialChars($value->getEndTime()));

			$y_value -= $this->crsBlockSpaceTopAdd - (0.14 + $jumper);

			$this->pdf->setXY($x_thirdColoumn - 0.1, $y_value);
			$this->pdf->MultiCell($coloumn_width,$this->crsBlockSpaceTopAdd,$this->encodeSpecialChars($base->getTitle()),0,"");

			$this->pdf->setXY($x_fourthColoumn - 0.1, $y_value);
			$this->pdf->MultiCell($coloumn_width,$this->crsBlockSpaceTopAdd,$this->encodeSpecialChars($base->getContent()),0,"");

			$this->pdf->setXY($x_fithColoumn - 0.1, $y_value);
			$this->pdf->MultiCell($coloumn_width,$this->crsBlockSpaceTopAdd,$this->encodeSpecialChars($base->getLearningDestination()),0,"");

			$this->pdf->setXY($x_sixthColoumn - 0.1, $y_value);
			$this->pdf->MultiCell($coloumn_width,$this->crsBlockSpaceTopAdd,$this->encodeSpecialChars($value->getPracticeSession()),0,"");

			$this->pdf->setXY($x_seventhColoumn - 0.1, $y_value);
			$this->pdf->MultiCell($coloumn_width,$this->crsBlockSpaceTopAdd,$this->encodeSpecialChars($value->getCreditPoints()),0,"");

			$this->pdf->setXY($x_firstColoumn, $y_value + $max_height - (2*$this->crsBlockSpaceTopAdd));
			$jumper += 0.01;
		}
	}

	private function createFooterRow() {
		$this->pdf->SetFont($this->crsFooterFontName, $this->crsFooterNoBold, $this->crsFooterFontSize);
		$this->pdf->WriteText($this->spaceLeft, 20, $this->encodeSpecialChars("Seite ".$this->pageCounter));
		$this->pdf->WriteText($this->spaceLeft + 2, 20, $this->encodeSpecialChars("Nummer der Maßnahme ".$this->customId));
	}

	private function calcMaxRowHeight($block_title,$content,$targets, $width) {
		$heights = array();
		$heights[] = $this->pdf->NbLines($width,$block_title);
		$heights[] = $this->pdf->NbLines($width,$content);
		$heights[] = $this->pdf->NbLines($width,$targets);

		return max($heights);
	}

	/**
	 * @param  string $text
	 * @return string
	 */
	private function encodeSpecialChars($text)
	{
		$text = iconv('UTF-8', 'windows-1252', $text);

		return $text;
	}
}
<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Billing/classes/class.ilPDFHelper.php';
require_once 'Services/Utilities/classes/class.ilUtil.php';
require_once 'Services/Calendar/classes/class.ilDatePresentation.php';

/**
 * @author  Maximilian Frings <mfrings@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 *
 * ATTENTION: DO NOT EVER REUSE AN INSTANCE OF THIS CLASS AFTER A BILL PDF
 *            WAS CREATED.
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
		
		$this->headFontSize = 11;
		$this->headerFontName = "Arial";
		$this->headerText = "Ablaufplan";
		$this->headerBold = "B";
		$this->headerSpaceTop = 2.5;

		$this->crsInfoFontSize = 10;
		$this->crsInfoFontName = "Arial";
		$this->crsInfoText = "Generelle Angaben zum Training";
		$this->crsInfoBold = "B";
		$this->crsInfoNoBold = "";
		$this->crsInfoSpaceTop = 4.0;
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
		
		$this->spaceLeft = 1.0;
		$this->spaceRight = 1.0;
		$this->lastYValue = 0;
		$this->maxRight = 28;
		$this->number_couloums_after_time = 3;

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

		$this->createHeadline();
		$this->createCrsInfo();
		$this->createBlockInfo();
		/*$this->createAboutTitleDate();
		$this->createSalutationPretext();
		$this->createCalculationHeadline($fontHeight);
		$this->createCalculation($fontHeight, $this->distanceindex);
		$this->createCalculationUnderline($fontHeight, $this->distanceindex, $this->sumPreTax, $this->sumVAT, $this->sumAfterTax);
		$this->createPosttextGreeting($fontHeight, $this->distanceindex);
		$this->createPageNumber();
		$this->resetDeliverMembers();*/

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
		$trainers = explode(", ", $meta_data["Trainer"]);
		$jump_trainers = count($trainers) - 1;

		$this->pdf->SetFont($this->crsInfoFontName, $this->crsInfoBold, $this->crsInfoFontSize);
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop, $this->crsInfoText);
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (2 * $this->crsInfoSpaceTopAdd), "Titel");
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (3 * $this->crsInfoSpaceTopAdd), "Untertitel");
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (4 * $this->crsInfoSpaceTopAdd), "Nummer der Maßnahme");
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (5 * $this->crsInfoSpaceTopAdd), "Datum");
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (6 * $this->crsInfoSpaceTopAdd), "Veranstaltungsort");
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (7 * $this->crsInfoSpaceTopAdd), "Bildungspunkte");
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + (8 * $this->crsInfoSpaceTopAdd), "Trainer");
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + ((9 + $jump_trainers) * $this->crsInfoSpaceTopAdd), "Trainingsbetreuer");
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + ((10 + $jump_trainers) * $this->crsInfoSpaceTopAdd), "Fachlich verantwortlich");
		$this->pdf->WriteText($this->spaceLeft, $this->crsInfoSpaceTop + ((11 + $jump_trainers) * $this->crsInfoSpaceTopAdd), "Bei Rückfragen");

		$this->pdf->SetFont($this->crsInfoFontName, $this->crsInfoNoBold, $this->crsInfoFontSize);
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (2 * $this->crsInfoSpaceTopAdd), $meta_data["Titel"]);
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (3 * $this->crsInfoSpaceTopAdd), $meta_data["Untertitel"]);
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (4 * $this->crsInfoSpaceTopAdd), $meta_data["Nummer der Maßnahme"]);
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (5 * $this->crsInfoSpaceTopAdd), $meta_data["Datum"]);
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (6 * $this->crsInfoSpaceTopAdd), $meta_data["Veranstaltungsort"]);
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + (7 * $this->crsInfoSpaceTopAdd), $meta_data["Bildungspunkte"]);
		
		foreach ($trainers as $key => $value) {
			$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + ((8+$key) * $this->crsInfoSpaceTopAdd), $value);
		}
		
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + ((9 + $jump_trainers) * $this->crsInfoSpaceTopAdd), $meta_data["Trainingsbetreuer"]);
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft
								,$this->crsInfoSpaceTop + ((10 + $jump_trainers) * $this->crsInfoSpaceTopAdd)
								, $meta_data["Fachlich verantwortlich"]);
		$this->pdf->WriteText($this->crsInfoSecondColoumnSpaceLeft, $this->crsInfoSpaceTop + ((11 + $jump_trainers) * $this->crsInfoSpaceTopAdd), $meta_data["Bei Rückfragen"]);
	
		$this->lastYValue = $this->crsInfoSpaceTop + ((11 + $jump_trainers) * $this->crsInfoSpaceTopAdd);
	}

	/**
	*
	*/
	private function createBlockInfo() {
		$date_string_width = $this->pdf->GetStringWidth("99:99:99") + $this->crsBlockColoumnSpace;
		$coloumn_width = (($this->maxRight - $this->spaceLeft) / $this->number_couloums_after_time) - $this->crsBlockColoumnSpace;

		$x_firstColoumn = $this->spaceLeft;
		$x_secondColoumn = $x_firstColoumn + $date_string_width;
		$x_thirdColoumn = $x_secondColoumn + $date_string_width;
		$x_fourthColoumn = $x_thirdColoumn + $coloumn_width + $this->crsBlockColoumnSpace;
		$x_fithColoumn = $x_fourthColoumn + $coloumn_width + $this->crsBlockColoumnSpace;

		$y_value = $this->lastYValue + $this->crsBlockSpaceTop;

		$this->pdf->SetFont($this->crsBlockFontName, $this->crsBlockBold, $this->crsBlockFontSize);
		$this->pdf->WriteText($x_firstColoumn, $y_value, $this->lng->txt("gev_dec_crs_building_block_from"));
		$this->pdf->WriteText($x_secondColoumn, $y_value, $this->lng->txt("gev_dec_crs_building_block_to"));
		$this->pdf->WriteText($x_thirdColoumn, $y_value, $this->lng->txt("gev_dec_crs_building_block_block"));
		$this->pdf->WriteText($x_fourthColoumn, $y_value, $this->lng->txt("gev_dec_crs_building_block_content"));
		$this->pdf->WriteText($x_fithColoumn, $y_value, $this->lng->txt("gev_dec_crs_building_block_lern_dest"));

		
		$this->pdf->SetFont($this->crsBlockFontName, $this->crsBlockNoBold, $this->crsBlockFontSize);
		

		$y_value = $this->pdf->GetY() + $this->crsBlockSpaceTop;

		$this->pdf->SetFont($this->crsBlockFontName, $this->crsBlockNoBold, $this->crsBlockFontSize);
		$this->pdf->WriteText($this->spaceLeft, $y_value, "99:99:99");
		$this->pdf->WriteText($this->spaceLeft + $date_string_width, $y_value, "99:99:99");
		/*$this->pdf->MultiCell($bla,$this->crsBlockSpaceTopAdd, "sdasdölskdjfkjsadkf ökasfjkl aslfjljsdk flksadhf  ashdfjlk dsfklhasdf aslkjfhklsdh flasdjhfka jdsfkljsdhaf aslkjfhkljsdhf asdfklj hsdaklf haslkjfh flkjasdhfk jsadfkhsalgfhglöiasdh l asdjhfkjlshaf gdhksgfkhgsjhds", 0, 'J', false);*/

		foreach ($this->blocks as $key => $value) {
			$this->pdf->WriteText($this->spaceLeft, $this->lastYValue + $this->crsBlockSpaceTop, "99:99:99");
			$this->pdf->WriteText($this->spaceLeft + $date_string_width, $this->lastYValue + $this->crsBlockSpaceTop, "99:99:99");
		}
	}

	private function calcMaxRowHeight($block_title,$content,$targets, $width) {
		$h_block_title = $this->pdf->NbLines($width,$block_title);
		$h_content = $this->pdf->NbLines($width,$content);
		$h_targets = $this->pdf->NbLines($width,$targets);

		if($h_block_title > $h_content && $h_block_title > $h_target) {
			return $h_block_title;
		}

		if($h_content > $h_block_title && $h_content > $h_target) {
			return $h_content;
		}

		return $h_target;
	}
}
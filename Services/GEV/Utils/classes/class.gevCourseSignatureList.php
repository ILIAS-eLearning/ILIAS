<?php

/**
*	Signature list for trainings, fed by ILIAS participation list
*	@description Variable MultiSpaceTables are developed by olivier@fpdf.org.
*/
require_once "Services/Billing/lib/fpdf/fpdf.php";
require_once "Services/GEV/Utils/classes/class.gevUserUtils.php";
class gevCourseSignatureList extends fpdf {
	const ADDITIONAL_LINES = 5;
	protected $img = "Customizing/global/skin/genv/images/HeaderIcon.png";
	protected $metadata;
	protected $participant_ids;
	protected $gLng;
	public function __construct(gevCourseUtils $crs_utils) {
		global $lng;

		$this->gLng = $lng;
		$trainer_list = $crs_utils->getTrainers();
		foreach($trainer_list as &$user_id) {
			$user_utils = gevUserUtils::getInstance($user_id);
			$name = $user_utils->getFirstname()." ".$user_utils->getLastname();
			$email = $user_utils->getEmail();
			$user_id = $name." (".$email.")";
		}

		$venue = $crs_utils->getVenueTitle();

		if($venue === null || $venue == "") {
			$venue = $crs_utils->getVenueFreeText();
		}

		$this->metadata = array(
			"Titel" => $crs_utils->getTitle()
			, "Untertitel" => $crs_utils->getSubtitle()
			, "Nummer der Maßnahme" => $crs_utils->getCustomId()
			, "Datum" => ($crs_utils->getStartDate() !== null && $crs_utils->getEndDate() !== null)
						 	? ilDatePresentation::formatPeriod($crs_utils->getStartDate(), $crs_utils->getEndDate())
							: ""
			, "Veranstaltungsort" => $venue
			, "Trainer" => implode(", ", $trainer_list)
			, "Trainingsbetreuer" => $crs_utils->getMainAdminName(). " (".$crs_utils->getMainAdminContactInfo().")"
			, "Fachlich verantwortlich" => $crs_utils->getTrainingOfficerContactInfo());

		$this->participant_ids = $crs_utils->getParticipants();

		parent::__construct();
		$this->AliasNbPages();
		$this->AddPage();
		$this->buildMetaTable();
		$this->buildParticipantsTable();
		ob_clean();
	}

	/**
	* get all the participants and write them into table including some UDF
	*/
	protected function buildParticipantsTable() {

		$this->SetWidths(array(45,45,45,55));
		$this->SetFont('Arial','B',10);
		$this->Row(array($this->gLng->txt("lastname"),$this->gLng->txt("firstname"),$this->gLng->txt("objs_orgu"),$this->gLng->txt("gev_signature")));
		$this->SetFont('Arial','',10);
		$y0 = $this->GetY();
		$participants = array();
		foreach ($this->participant_ids as $usr_id) {
			$usr_utils = gevUserUtils::getInstance($usr_id);
			$firstname =  $usr_utils->getFirstname();
			$lastname = $usr_utils->getLastname();
			$orgus = $usr_utils->getAllOrgUnitTitlesUserIsMember();
			$orgus = implode(",",$orgus);
			$participants["$lastname $firstname"] = array($firstname, $lastname, $orgus);
		}
		
		ksort($participants, SORT_NATURAL | SORT_FLAG_CASE);
		
		foreach ($participants as $participant) {
			$this->Row(array(utf8_decode($participant[1]),utf8_decode($participant[0]),utf8_decode($participant[2]),""));
		}
		/**
		*	5 additional lines for people, who were not registered via ILIAS
		*/
		for( $count = 0; $count < self::ADDITIONAL_LINES; $count++) {
			$this->Row(array("","","",""));
		}
	}

	/**
	* Training metadata
	*/
	protected function buildMetaTable() {
		$this->SetLinewidth(0.1);
		$this->SetFont('Arial','B',10);
		$this->Cell(0,8,'Generelle Angaben zum Training','B');
		$this->Ln();
		$this->Cell(0,0.7,'','B');
		$this->Ln();
		foreach ($this->metadata as $key => $value) {
			$this->Cell(45,8,utf8_decode($key),1,'L');
			$this->SetFont('Arial','',10);
			$this->MultiCell(0,8,utf8_decode($value),1,'L');
			$this->SetFont('Arial','B',10);
			$this->Ln(0);
		}
		$this->Ln(10);
		$this->SetLinewidth(0.2);
	}

	/**
	* Build header, image, training title...
	*/
	public function Header() {
		$this->SetFont('Arial','B',14);
		$this->Cell(30,10,'Unterschriftenliste');
		$this->Ln(5);
		$this->SetFont('Arial','',8);
		$header_info = utf8_decode($this->metadata["Titel"]);
		if($this->metadata["Nummer der Maßnahme"]) {
			$header_info .= ", ".$this->metadata["Nummer der Maßnahme"];
		}
		$this->Cell(20,10,$header_info);
		$this->Image($this->img,170,6,30);
		$this->Ln(30);
	}

	/**
	* Build footer.
	*/
	public function Footer() {	
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// Arial 8
		$this->SetFont('Arial','',10);
		// Page number
		$this->Cell(0,10,'Seite '.$this->PageNo().' von {nb}' ,0,0,'C');
	}

	/**
	* MultiSpaceTables follows
	*/
	protected $widths;
	protected $aligns;

	public function SetWidths($w) {
	    //Set the array of column widths
	    $this->widths=$w;
	}

	public function SetAligns($a) {
	    //Set the array of column alignments
	    $this->aligns=$a;
	}

	public function Row($data)	{
		//Calculate the height of the row
		$nb=0;
		for($i=0;$i<count($data);$i++)
		    $nb=max($nb, $this->NbLines($this->widths[$i], $data[$i]));
		$h=10*$nb;
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->widths[$i];
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Draw the border
			$this->Rect($x, $y, $w, $h);
			//Print the text
			$this->MultiCell($w, 10, $data[$i], 0, $a);
			//Put the position to the right of the cell
			$this->SetXY($x+$w, $y);
		}
		//Go to the next line
		$this->Ln($h);
	}

	public function CheckPageBreak($h) {
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
			$this->AddPage($this->CurOrientation);
	}

	public function NbLines($w, $txt) {
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r", '', $txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
				}
				else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}

}
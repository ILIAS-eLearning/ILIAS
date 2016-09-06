<?php
namespace CaT\Plugins\TalentAssessment\Observations;
require_once "Services/Billing/lib/fpdf/fpdf.php";

class ReportPDFHelper extends \fpdf implements ReportWriter {

	protected $image_location 
		= "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment"
			."/templates/img/GEV_POT-ergebnisbericht_v4_nurBG.png";

	public function BackgrondImage($image_location) {
		$this->image_location;		
	}
	
	public function AddPage() {
		parent::AddPage();
		$this->Image($this->image_location,0,0, $this->w, $this->h);
		$this->SetXY(0,0);
	}

	public function StringPositionedAbsolute($x, $y, $text, $text_allign = 'L',
		$dx = 0, $dy = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $fill = array()) {
		if(count($fill) > 0) {
			$this->SetFillColor($fill[0],$fill[1],$fill[2]);
		}
		$this->SetXY($x,$y);
		$this->SetFont($font,$options,$size);
		$this->Cell($dx,$dy,$text,0,0,$text_allign,count($fill) > 0);
	}

	public function StringPositionedRelativeY($y, $text, $text_allign = 'L',
		$dx = 0, $dy = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $fill = array()) {

		$this->StringPositionedAbsolute($this->left_indent, $this->GetY() + $y, $text, $text_allign,
			$dx, $dy, $font, $size, $text_options,
			$fill);
	}

	public function TextPositionedAbsolute($x, $y, $text, $text_allign = 'L',
		$dx = 0, $dy = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $fill = array()) {
		if(count($fill) > 0) {
			$this->SetFillColor($fill[0],$fill[1],$fill[2]);
		}
		$this->SetXY($x,$y);
		$this->SetFont($font,$options,$size);
		$this->MultiCell($dx,$dy,$text,0,0,$text_allign,count($fill) > 0);
	}

	public function TextPositionedRelativeY($y, $text, $text_allign = 'L',
		$dx = 0, $dy = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $fill = array()) {
		$this->ln($y);
		$this->TextPositionedAbsolute($this->left_indent, $this->GetY(), $text, $text_allign,
			$dx, $dy, $font, $size, $text_options,
			$fill);
	}
}
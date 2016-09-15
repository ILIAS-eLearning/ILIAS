<?php
namespace CaT\Plugins\TalentAssessment\Observations;
require_once "Services/Billing/lib/fpdf/fpdf.php";

class ReportPDFWriter extends \fpdf implements ReportWriter {

	public function BackgroundImage($bg_image_location) {
		$this->bg_image_location = $bg_image_location;		
	}

	public function LeftIndent($left_indent) {
		$this->left_indent = $left_indent;
	}
	
	public function AddPage() {
		parent::AddPage();
		$this->Image($this->bg_image_location, 0, 0, $this->w, $this->h);
		$this->SetXY(0,0);
	}

	public function StringPositionedAbsolute($x, $y, $text, $text_allign = 'L',
		$w = 0, $h = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $font_color = array(0,0,0)) {

		$this->SetTextColorArray($font_color);
		$this->SetXY($x,$y);
		$this->SetFont($font,$text_options,$size);
		$this->Cell($w,$h !== 0 ? $h : $size/2, $text, 0, 0, $text_allign, 0);
		$this->SetTextColor(0,0,0);
	}

	public function StringPositionedRelativeY($y, $text, $text_allign = 'L',
		$w = 0, $h = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $font_color = array(0,0,0)) {
		$this->SetTextColorArray($font_color);
		$this->StringPositionedAbsolute(
			$this->left_indent, $this->GetY() + $y, $text, $text_allign,
			$w, $h, $font, $size, $text_options,
			$font_color);
		$this->SetTextColor(0,0,0);
	}

	public function TextPositionedAbsolute($x, $y, $text, $text_allign = 'L',
		$w = 0, $h = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $font_color = array(0,0,0)) {
		$this->SetTextColorArray($font_color);
		$this->SetXY($x,$y);
		$this->SetFont($font,$options,$size);
		$this->MultiCell($w,$h !== 0 ? $h : $size / 2, $text, 0, $text_allign, 0);
		$this->SetTextColor(0,0,0);
	}

	public function TextPositionedRelativeY($y, $text, $text_allign = 'L',
		$w = 0, $h = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $font_color = array(0,0,0)) {
		$this->SetTextColorArray($font_color);
		$this->ln($y);
		$this->TextPositionedAbsolute($this->left_indent, $this->GetY(), $text, $text_allign,
			$w, $h, $font, $size, $text_options,
			$font_color);
		$this->SetTextColor(0,0,0);
	}

	public function ImagePositionAbsolute($x,$y,$w,$h,$image_location) {
		$dims = getimagesize($image_location);

		$h = $w*$dims[1]/$dims[0];

		$this->Image($image_location,$x,$y, $w, $h, 'PNG');
		$this->SetXY($this->left_indent,$this->GetY()+$h);
	}

	public function ImagePositionRelativeY($y,$w,$h,$image_location) {
		$this->ln($y);
		$this->Image($image_location,$this->left_indent, $this->GetY(), $w, $h, 'PNG');
	}

	public function TextPositionedNextline($text, $text_allign = 'L',
		$w = 0, $h = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $font_color = array(0,0,0)) {
		$this->ln();
		$this->SetXY($this->left_indent,$this->GetY());
		$this->SetTextColorArray($font_color);
		$this->SetFont($font,$text_options,$size);
		$this->MultiCell($w, $h !== 0 ? $h : $size/2, $text, 0, $text_allign, 0);
		$this->SetTextColor(0,0,0);
	}

	public function StringPositionedNextline($text, $text_allign = 'L',
		$w = 0, $h = 0, $font = 'Arial', $size = 10, $text_options = '',
		 array $font_color = array()) {
		$this->ln();
		$this->SetTextColorArray($font_color);
		$this->SetFont($font,$text_options,$size);
		$this->Cell($w, $h !== 0 ? $h : $size/2, $text, 0, 0, $text_allign, 0);
		$this->SetTextColor(0,0,0);
	}

	protected function SetTextColorArray(array $font_color = array()) {
		if(count($font_color) === 3) {
			$this->SetTextColor($font_color[0],$font_color[1],$font_color[2]);
		}
	}

	public function Output($name='', $dest='') {
		if($this->PageNo() > 1) {
			throw new \ilException("too long");
		}
		parent::Output($name, $dest);
	}
}
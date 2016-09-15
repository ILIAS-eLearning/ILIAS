<?php

namespace CaT\Plugins\TalentAssessment\Observations;

class ilResultPDF {
	public function __construct($settings, $actions, \Closure $txt) {
		$this->txt = $txt;
		$this->settings = $settings;
		$this->actions = $actions;
	}

	public function show($name = '', $dest = '') {
		$pdf = $this->createPDF();
		$pdf->Draw($name, $dest);
	}

	protected function createPDF() {
		$pdf_write = new ReportPDFWriter();
		$pdf = new ReportPreview($pdf_write);
		$pdf->SetBackground("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/templates/images/result_bg.png");
		$pdf->LeftIndent(19);
		$pdf->TextWidth(170);

		$pdf->TitlePosition(45, 12);
		$pdf->TitleFontSettings('Arial', 18 , '', array(255,255,255));

		$pdf->Title($this->encodeSpecialChars(sprintf($this->txt("pdf_title_text"), $this->actions->getCareerGoalTitle($this->settings->getCareerGoalId()))));

		$pdf->NamePosition(21, 45.5);
		$pdf->NameFontSettings('Arial', 14 , '', array(0,0,0));
		$pdf->Name($this->encodeSpecialChars($this->settings->getFirstname()." ".$this->settings->getLastname()));

		$pdf->OrguPosition(110, 45.5);
		$pdf->OrguFontSettings('Arial', 14 , '', array(0,0,0));
		$pdf->Orgu($this->encodeSpecialChars($this->actions->getOrgUnitTitle($this->settings->getOrgUnit())));

		$pdf->DatePosition(161, 45.5);
		$pdf->DateFontSettings('Arial', 14 , '', array(0,0,0));
		$date = $this->settings->getStartdate()->get(IL_CAL_DATE);
		$date = explode("-", $date);
		$pdf->Date($date[2].".".$date[1].".".$date[0]);

		$gui = new ilObservationsDiagrammGUI($this->settings, $this->actions, $this->txt);
		$graph = $gui->getSVGData();
		$svg_converter = new SVGConverter($graph);
		$destination = $svg_converter->ConvertAndReturnPath();

		$pdf->GraphPosition(20, 55);

		$pdf->Graph($destination);

		$pdf->SummaryTitlePositionOffset(15);

		$pdf->SummaryTitleFontSettings('Arial', 14 , '', array(0,0,0));
		$pdf->SummaryTitle($this->txt("pdf_summary_title"));

		$pdf->Summary($this->encodeSpecialChars($this->settings->getResultComment()));

		$judgement_text = $this->settings->getTextForPotential();
		$judgement_text = $this->fillPlaceholder($this->encodeSpecialChars($judgement_text));
		$pdf->Judgement($judgement_text);

		return $pdf;
	}

	protected function fillPlaceholder($judgement_text) {
		$judgement_text = str_replace("[VORNAME]", $this->settings->getFirstname(), $judgement_text);
		$judgement_text = str_replace("[NACHNAME]", $this->settings->getLastname(), $judgement_text);
		$judgement_text = str_replace("[KARRIEREZIEL]", $this->actions->getCareerGoalTitle($this->settings->getCareerGoalId()), $judgement_text);

		return $judgement_text;
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt($code) {
		assert('is_string($code)');

		$txt = $this->txt;

		return $txt($code);
	}

	private function encodeSpecialChars($text) {
		$text = utf8_decode($text);

		return $text;
	}
}
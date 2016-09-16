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
		$pdf->draw($name, $dest);
	}

	protected function createPDF() {
		$pdf_write = new ReportPDFWriter();
		$pdf_write->AddFont("OpenSans", "", "OpenSans-Regular.php");
		$pdf_write->AddFont("OpenSans", "semibold", "Opensans-semibold.php");
		$pdf = new ReportPreview($pdf_write);
		$pdf->setBackground("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/templates/images/result_bg.png");
		$pdf->leftIndent(19);
		$pdf->textWidth(170);

		$gev_red = array(197, 49, 28);

		$pdf->titlePosition(42, 14);
		$pdf->titleFontSettings('OpenSans', 14, '', array(255,255,255));

		$pdf->title($this->encodeSpecialChars(sprintf($this->txt("pdf_title_text"), $this->actions->getCareerGoalTitle($this->settings->getCareerGoalId()))));

		$pdf->namePosition(21, 46);
		$pdf->nameFontSettings('OpenSans', 11 , 'semibold', $gev_red);
		$pdf->name($this->encodeSpecialChars($this->settings->getFirstname()." ".$this->settings->getLastname()));

		$pdf->orguPosition(110, 46);
		$pdf->orguFontSettings('OpenSans', 11 , 'semibold', $gev_red);
		$pdf->orgu($this->encodeSpecialChars($this->actions->getOrgUnitTitle($this->settings->getOrgUnit())));

		$pdf->datePosition(161, 46);
		$pdf->dateFontSettings('OpenSans', 11 , 'semibold', $gev_red);
		$date = $this->settings->getStartdate()->get(IL_CAL_DATE);
		$date = explode("-", $date);
		$pdf->date($date[2].".".$date[1].".".$date[0]);

		$gui = new ilObservationsDiagrammGUI($this->settings, $this->actions, $this->txt);
		$graph = $gui->getSVGData();
		$svg_converter = new SVGConverter();
		$destination = $svg_converter->convertAndReturnPath($graph);

		$pdf->graphPosition(20, 55);

		$pdf->graph($destination);

		$pdf->summaryTitlePositionOffset(15);

		$pdf->summaryTitleFontSettings('OpenSans', 12 , 'semibold', $gev_red);
		$pdf->summaryTitle($this->txt("pdf_summary_title"));

		$pdf->summary($this->encodeSpecialChars($this->settings->getResultComment()));

		$judgement_text = $this->settings->getTextForPotential();
		$judgement_text = $this->fillPlaceholder($this->encodeSpecialChars($judgement_text));
		$pdf->judgement($judgement_text);

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

<?php
namespace CaT\Plugins\TalentAssessment\Observations;
require_once "Services/Billing/lib/fpdf/fpdf.php";
class ReportPreview {

	public function __construct(ReportPDFWriter $writer) {
		$this->writer = $writer;
	}

	/**
	 * Set the left page indent, for relative-y-positioned objects.
	 * @param	int	$x
	 */
	public function leftIndent($x) {
		$this->writer->leftIndent($x);
	}

	/**
	 * Set the text width starting from the left indent.
	 */
	public function textWidth($w) {
		$this->text_width = $w;
	}

	/**
	 * Set the background image by its path will repeat every single page.
	 */
	public function setBackground($image_path) {
		$this->writer->backgroundImage($image_path);
	}

	public function titlePosition($x,$y) {
		$this->title_x = $x;
		$this->title_y = $y;
	}

	private $title_font = 'Arial';
	private $title_fontsize = 10;
	private $title_options = '';
	private $title_color = array(0,0,0);

	public function titleFontSettings($font = 'Arial',$size = 10 ,$options = '', array $color = array(0,0,0)) {
		$this->title_font = $font;
		$this->title_fontsize = $size;
		$this->title_options = $options;
		$this->title_color = $color;
	}

	public function title($title) {
		$this->title = $title;
	}

	public function namePosition($x,$y) {
		$this->name_x = $x;
		$this->name_y = $y;
	}

	private $name_font = 'Arial';
	private $name_fontsize = 10;
	private $name_options = '';
	private $name_color = array(195,28,28);

	public function nameFontSettings($font = 'Arial',$size = 10 ,$options = '', array $color = array(195,28,28)) {
		$this->name_font = $font;
		$this->name_fontsize = $size;
		$this->name_options = $options;
		$this->name_color = $color;
	}

	public function name($name) {
		$this->name = $name;
	}

	public function orguPosition($x,$y) {
		$this->orgu_x = $x;
		$this->orgu_y = $y;
	}

	private $orgu_font = 'Arial';
	private $orgu_fontsize = 10;
	private $orgu_options = '';
	private $orgu_color = array(195,28,28);

	public function orguFontSettings($font = 'Arial',$size = 10 ,$options = '', array $color = array(195,28,28)) {
		$this->orgu_font = $font;
		$this->orgu_fontsize = $size;
		$this->orgu_options = $options;
		$this->orgu_color = $color;
	}

	public function orgu($orgu) {
		$this->orgu = $orgu;
	}

	public function datePosition($x,$y) {
		$this->date_x = $x;
		$this->date_y = $y;
	}

	private $date_font = 'Arial';
	private $date_fontsize = 10;
	private $date_options = '';
	private $date_color = array(195,28,28);

	public function dateFontSettings($font = 'Arial',$size = 10 ,$options = '', array $color = array(195,28,28)) {
		$this->date_font = $font;
		$this->date_fontsize = $size;
		$this->date_options = $options;
		$this->date_color = $color;
	}

	public function date($date) {
		$this->date = $date;
	}

	public function graphPosition($x,$y) {
		$this->graph_x = $x;
		$this->graph_y = $y;
	}

	public function graph($graph_file_location) {
		$this->graph = $graph_file_location;
	}

	public function summaryTitlePositionOffset($dy) {
		$this->summary_title_dy = $dy;
	}

	private $summary_title_font = 'Arial';
	private $summary_title_fontsize = 10;
	private $summary_title_options = '';
	private $summary_title_color = array(195,28,28);

	public function summaryTitleFontSettings($font = 'Arial',$size = 10 ,$options = '', array $color = array(195,28,28)) {
		$this->summary_title_font = $font;
		$this->summary_title_fontsize = $size;
		$this->summary_title_options = $options;
		$this->summary_title_color = $color;
	}

	public function summaryTitle($summary_title) {
		$this->summary_title = $summary_title;
	}

	private $summary_font = 'Arial';
	private $summary_fontsize = 10;
	private $summary_options = '';
	private $summary_color = array(0,0,0);

	public function summaryFontSettings($font = 'Arial',$size = 10 ,$options = '', array $color = array(0,0,0)) {
		$this->summary_font = $font;
		$this->summary_fontsize = $size;
		$this->summary_options = $options;
		$this->summary_color = $color;
	}

	public function summary($summary) {
		$this->summary = $summary;
	}

	private $judgement_font = 'Arial';
	private $judgement_fontsize = 10;
	private $judgement_options = '';
	private $judgement_color = array(195,28,28);

	public function judgementFontSettings($font = 'Arial',$size = 10 ,$options = '', array $color = array(195,28,28)) {
		$this->judgement_font = $font;
		$this->judgement_fontsize = $size;
		$this->judgement_options = $options;
		$this->judgement_color = $color;
	}

	public function judgement($judgement) {
		$this->judgement = $judgement;
	}

	public function draw($name = '', $dest = '') {
		$this->writer->AddPage();
		$this->drawTitle();
		$this->drawName();
		$this->drawDate();
		$this->drawOrgu();
		$this->drawGraph();
		$this->drawSummaryTitle();
		$this->drawSummary();
		$this->drawJudgement();

		$this->writer->Output($name, $dest);
	}

	protected function drawTitle() {
		$this->writer->stringPositionedAbsolute(
			$this->title_x, $this->title_y, $this->title, 'L',
			0, 0,
			$this->title_font, 
			$this->title_fontsize, 
			$this->title_options,
			$this->title_color);
	}

	protected function drawName() {
		$this->writer->stringPositionedAbsolute(
			$this->name_x, $this->name_y, $this->name, 'L',
			0, 0,
			$this->name_font,
			$this->name_fontsize,
			$this->name_options,
			$this->name_color);
	}

	protected function drawDate() {
		$this->writer->stringPositionedAbsolute(
			$this->date_x, $this->date_y, $this->date, 'L',
			0, 0,
			$this->date_font,
			$this->date_fontsize,
			$this->date_options,
			$this->date_color);
	}

	protected function drawOrgu() {
		$this->writer->stringPositionedAbsolute(
			$this->orgu_x, $this->orgu_y, $this->orgu, 'L',
			0, 0,
			$this->orgu_font,
			$this->orgu_fontsize,
			$this->orgu_options,
			$this->orgu_color);
	}

	protected function drawGraph() {
		$this->writer->imagePositionAbsolute($this->graph_x,$this->graph_y,$this->text_width,0,$this->graph);
	}

	protected function drawSummaryTitle() {
		$this->writer->stringPositionedRelativeY($this->summary_title_dy,$this->summary_title, 'L',
			0, 0,
			$this->summary_title_font,
			$this->summary_title_fontsize,
			$this->summary_title_options,
			$this->summary_title_color);
	}

	protected function drawSummary() {
		$this->writer->textPositionedNextLine($this->summary, 'L',
			$this->text_width, 0,
			$this->summary_font,
			$this->summary_fontsize,
			$this->summary_options,
			$this->summary_color);
	}

	protected function drawJudgement() {
		$this->writer->textPositionedNextLine($this->judgement, 'L',
			$this->text_width, 0,
			$this->judgement_font,
			$this->judgement_fontsize,
			$this->judgement_options,
			$this->judgement_color);
	}
}
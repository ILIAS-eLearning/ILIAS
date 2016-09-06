<?php
namespace CaT\Plugins\TalentAssessment\Observations;
require_once "Services/Billing/lib/fpdf/fpdf.php";
class ReportPreview {

	public function __construct(ReportPDFWriter $writer) {
		$this->writer = $writer;
	}

	public function TitlePosition($x,$y) {
		$this->title_x = $x;
		$this->title_y = $y;
	}

	public function Title($title,$font = 'Arial',$size = 10 ,$options = '', array $color) {
		$this->title = $title;
		$this->title_font = $font;
		$this->title_fontsize = $size;
		$this->title_options = $options;
		$this->title_color = $color;
	}

	public function NamePosition($x,$y) {
		$this->name_x = $x;
		$this->name_y = $y;
	}

	public function Name($name,$font = 'Arial',$size = 10 ,$options = '', array $color) {
		$this->name = $name;
		$this->name_font = $font;
		$this->name_fontsize = $size;
		$this->name_options = $options;
		$this->name_color = $color;
	}

	public function OrguPosition($x,$y) {
		$this->orgu_x = $x;
		$this->orgu_y = $y;
	}

	public function Orgu($orgu, $font = 'Arial',$size = 10 ,$options = '', array $color) {
		$this->orgu = $orgu;
		$this->orgu_font = $font;
		$this->orgu_fontsize = $size;
		$this->orgu_options = $options;
		$this->orgu_color = $color;
	}

	public function DatePosition($x,$y) {
		$this->date_x = $x;
		$this->date_y = $y;
	}

	public function Date($date, $font = 'Arial',$size = 10 ,$options = '', array $color) {
		$this->date = $date;
		$this->date_font = $font;
		$this->date_fontsize = $size;
		$this->date_options = $options;
		$this->date_color = $color;
	}

	public function GraphPosition($x,$y) {
		$this->graph_x = $x;
		$this->graph_y = $y;
	}

	public function Graph($graph_file_location) {
		$this->graph = $graph_file_location;
	}

	public function Draw() {
		$this->DrawTitle();
		$this->DrawName();
		$this->DrawDate();
		$this->DrawOrgu();
		$this->DrawGraph();

		
	}

	protected function  DrawTitle() {
		$this->writer->StringPositionedAbsolute(
			$this->title_x, $this->title_y, $this->title, 'L',
			0, 0, $this->title_font, $this->title_fontsize, $this->title_options,
			$this->title_color);
	}

	protected function  DrawName() {
		$this->writer->StringPositionedAbsolute(
			$this->name_x, $this->name_y, $this->name, 'L',
			0, 0, $this->name_font, $this->name_fontsize, $this->name_options,
			$this->name_color);
	}

	protected function  DrawDate() {
		$this->writer->StringPositionedAbsolute(
			$this->date_x, $this->date_y, $this->date, 'L',
			0, 0, $this->date_font, $this->date_fontsize, $this->date_options,
			$this->date_color);
	}

	protected function  DrawOrgu() {
		$this->writer->StringPositionedAbsolute(
			$this->orgu_x, $this->orgu_y, $this->orgu, 'L',
			0, 0, $this->orgu_font, $this->orgu_fontsize, $this->orgu_options,
			$this->orgu_color);
	}
}
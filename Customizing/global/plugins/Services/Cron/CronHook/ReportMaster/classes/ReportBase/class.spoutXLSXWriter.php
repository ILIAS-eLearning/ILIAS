<?php


require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/interfaces/interface.ExcelWriter.php';

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\StyleBuilder;

class spoutXLSXWriter implements ExcelWriter {

	protected $tmp_file_path;
	protected $sheets = array();
	protected $spout_xlsx;
	protected $current_style;

	public function __construct() {
		$this->spout_xlsx = WriterFactory::create(Type::XLSX);
		$this->tmp_file_path = tempnam(sys_get_temp_dir(), 'xlsx_write');
		$this->spout_xlsx->openToFile($this->tmp_file_path);
	}

	/**
	 * @inheritdoc
	 */
	public function setRowFormatBold() {
		$this->current_style = (new StyleBuilder())
									->setFontBold()
									->setShouldWrapText()
									->build();
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function setRowFormatWrap() {
		$this->current_style = (new StyleBuilder())
									->setShouldWrapText()
									->build();
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function addSheet($name) {
		if(array_key_exists($name, $this->sheets)) {
			throw new InvalidArgumentException("Sheet with $name allready exists in document");
		}
		if(count($this->sheets) === 0) {
			$this->sheets[$name] = $this->spout_xlsx->getCurrentSheet();
		} else {
			$this->sheets[$name] = $this->spout_xlsx->addNewSheetAndMakeItCurrent();
		}
		$this->sheets[$name]->setName($name);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function setSheet($name) {
		if(!array_key_exists($name, $this->sheets)) {
			throw new InvalidArgumentException("Sheet with $name dows not yet exists in document");
		}
		$this->spout_xlsx->setCurrentSheet($this->sheets[$name]);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function writeRow(array $row) {
		$this->spout_xlsx->addRowWithStyle($row,$this->current_style);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function offerDownload($filename) {
		$this->spout_xlsx->close();
		if(!$this->checkFileEnding($filename)) {
			throw new InvalidArgumentException("spoutXLSXWriter: wrong file name provided. .xlsx expected.");
		}
		ilUtil::deliverFile($this->tmp_file_path, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',false, true, true);
	}

	/**
	 * Files should end with .xlsx
	 * @var string $filename
	 * @return bool
	 */
	protected function checkFileEnding($filename) {
		return preg_match("#.+\.xlsx$#",$filename) === 1 ? true : false;
	}
}
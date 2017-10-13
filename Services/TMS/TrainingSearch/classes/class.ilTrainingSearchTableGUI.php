<?php

/**
 * cat-tms-patch start
 */

require_once("Services/TMS/TrainingSearch/classes/Helper.php");

/**
 * Table gui to present cokable courses
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilTrainingSearchTableGUI {

	/**
	 * @var ilTrainingSearchGUI
	 */
	protected $parent;

	/**
	 * @var ilLanguage
	 */
	protected $g_lng;

	public function __construct(ilTrainingSearchGUI $parent, Helper $helper) {
		$this->parent = $parent;

		global $DIC;
		$this->g_lng = $DIC->language();

		$this->helper = $helper;

		$this->g_lng->loadLanguageModule('tms');
	}

	/**
	 * Set data to show in table
	 *
	 * @param mixed[] 	$data
	 *
	 * @return void
	 */
	public function setData(array $data) {
		$this->data = $data;
	}

	/**
	 * Get data should me shown in table
	 *
	 * @return mixed[]
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Renders the presentation table
	 *
	 * @return string
	 */
	public function render() {
		global $DIC;
		$f = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		//build table
		$ptable = $f->table()->presentation(
			$this->g_lng->txt("header"), //title
			array(),
			function ($row, BookableCourse $record, $ui_factory, $environment) { //mapping-closure
				return $row
					->withTitle($record->getTitleValue())
					->withSubTitle($record->getSubTitleValue())
					->withImportantFields($record->getImportantFields())
					->withContent($ui_factory->listing()->descriptive($record->getDetailFields()))
					->withFurtherFields($record->getFurtherFields())
					->withButtons(
						array(
							$ui_factory->button()->standard
								( $this->g_lng->txt("book_course")
								, $this->parent->getBookingLink($record)
								)
						)
					);
			}
		);

		$data = $this->getData();

		//apply data to table and render
		return $renderer->render($ptable->withData($data));
	}
}

/**
 * cat-tms-patch end
 */

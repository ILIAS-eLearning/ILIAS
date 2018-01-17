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

	public function __construct(ilTrainingSearchGUI $parent, Helper $helper, $search_user_id) {
		$this->parent = $parent;

		global $DIC;
		$this->g_lng = $DIC->language();
		$this->g_ctrl = $DIC->ctrl();
		$this->g_user = $DIC->user();
		$this->search_user_id = $search_user_id;
		$this->primary = true;

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
	 * @param 	ILIAS\UI\Component\Component[] 	$view_constrols
	 * @param	int			$offset
	 * @param	int|null	$limit
	 * @return 	string
	 */
	public function render($view_constrols, $offset = 0, $limit = null) {
		global $DIC;
		$f = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		//build table
		$ptable = $f->table()->presentation(
			$this->g_lng->txt("header"), //title
			$view_constrols,
			function ($row, BookableCourse $record, $ui_factory, $environment) { //mapping-closure
				$buttons = array();
				$this->primary = true;
				$search_actions = $record->getSearchActionLinks($this->g_ctrl, $this->search_user_id, $this->search_user_id != $this->g_user->getId());

				foreach ($search_actions as $label => $search_action) {
					$buttons[] = $this->createButton($label, $search_action, $ui_factory);
				}

				return $row
					->withTitle($record->getTitleValue())
					->withSubTitle($record->getSubTitleValue())
					->withImportantFields($record->getImportantFields())
					->withContent($ui_factory->listing()->descriptive($record->getDetailFields()))
					->withFurtherFields($record->getFurtherFields())
					->withButtons($buttons);
			}
		);

		$data = array_slice($this->getData(), $offset, $limit);

		//apply data to table and render
		return $renderer->render($ptable->withData($data));
	}

	/**
	 * Create an ui button
	 *
	 * @param string 	$link
	 *
	 * @return Button
	 */
	protected function createButton($label, $link, $ui_factory) {
		if($this->primary) {
			$this->primary = false;
			return $ui_factory->button()->primary
							( $label,
								$link
							);
		}

		return $ui_factory->button()->standard
							( $label,
								$link
							);
	}
}

/**
 * cat-tms-patch end
 */

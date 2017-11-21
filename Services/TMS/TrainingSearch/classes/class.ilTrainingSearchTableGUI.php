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
	public function render($view_constrols) {
		global $DIC;
		$f = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		//build table
		$ptable = $f->table()->presentation(
			$this->g_lng->txt("header"), //title
			$view_constrols,
			function ($row, BookableCourse $record, $ui_factory, $environment) { //mapping-closure
				return $row
					->withTitle($record->getTitleValue())
					->withSubTitle($record->getSubTitleValue())
					->withImportantFields($record->getImportantFields())
					->withContent($ui_factory->listing()->descriptive($record->getDetailFields()))
					->withFurtherFields($record->getFurtherFields())
					->withButtons($record->getBookButton($this->g_lng->txt("book_course"), $this->parent->getBookingLink($record), $this->search_user_id)
					);
			}
		);

		$data = $this->getData();

		//apply data to table and render
		return $renderer->render($ptable->withData($data));
	}

	/**
	 * Get all sorting and filter items for the table
	 *
	 * @param 	$f
	 *
	 * @return Sortation[]
	 */
	protected function getSortationObjects($f) {
		$ret = array();
		require_once("Services/Component/classes/class.ilPluginAdmin.php");

		if(ilPluginAdmin::isPluginActive('xccl')) {
			$plugin = ilPluginAdmin::getPluginObjectById('xccl');
			$actions = $plugin->getActions();
			$link = $this->g_ctrl->getLinkTarget($this->parent, ilTrainingSearchGUI::CMD_QUICKFILTER);

			$options = array(null => "Alle");
			$ret[] = $f->viewControl()->sortation($options + $actions->getTypeOptions())
						->withTargetURL($link, Helper::F_TYPE)
						->withLabel($plugin->txt("conf_options_type"));

			$ret[] = $f->viewControl()->sortation($options + $actions->getTopicOptions())
						->withTargetURL($link, Helper::F_TOPIC)
						->withLabel($plugin->txt("conf_options_topic"));
		}

		$link = $this->g_ctrl->getLinkTarget($this->parent, ilTrainingSearchGUI::CMD_SORT);
		$ret[] = $f->viewControl()->sortation($this->helper->getSortOptions())
						->withTargetURL($link, Helper::F_SORT_VALUE)
						->withLabel($this->g_lng->txt("sorting"));

		$link = $this->g_ctrl->getLinkTarget($this->parent, ilTrainingSearchGUI::CMD_CHANGE_USER);
		$ret[] = $f->viewControl()->sortation($this->helper->getUserWhereCurrentCanBookFor((int)$this->g_user->getId()))
						->withTargetURL($link, Helper::S_USER)
						->withLabel($this->g_lng->txt("employees"));

		return $ret;
	}
}

/**
 * cat-tms-patch end
 */

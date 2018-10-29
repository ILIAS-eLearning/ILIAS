<?php

/**
 * Class ilMMItemTranslationTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemTranslationTableGUI extends ilTable2GUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	/**
	 * @var ilMMItemFacadeInterface
	 */
	private $item_facade;


	/**
	 * ilMMItemTranslationTableGUI constructor.
	 *
	 * @param ilMMItemTranslationGUI  $a_parent_obj
	 * @param ilMMItemFacadeInterface $item_facade
	 */
	public function __construct(ilMMItemTranslationGUI $a_parent_obj, ilMMItemFacadeInterface $item_facade) {
		$table_id = self::class;
		$this->item_facade = $item_facade;
		$this->setId($table_id);
		$this->setPrefix($table_id);
		$this->setFormName($table_id);
		parent::__construct($a_parent_obj);
		$this->ctrl()->saveParameter($a_parent_obj, $this->getNavParameter());
		$this->setRowTemplate("tpl.translation_row.html", "Services/MainMenu");
		$this->setFormAction($this->ctrl()->getFormAction($a_parent_obj));
		$this->setExternalSorting(true);
		$this->setDefaultOrderField("id");
		$this->setDefaultOrderDirection("asc");
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->initColumns();

		$this->addCommandButton(
			ilMMItemTranslationGUI::CMD_SAVE_TRANSLATIONS, $this->lng()
			->txt("save")
		);
		$this->addCommandButton(
			ilBiblTranslationGUI::CMD_DELETE_TRANSLATIONS, $this->lng()
			->txt("delete")
		);

		$this->parseData();
	}




	protected function initColumns() {
		$this->addColumn($this->lng()->txt('mm_translation_select'), '', '15px', true);
		$this->addColumn($this->lng()->txt('mm_translation_lang'));
		$this->addColumn($this->lng()->txt('mm_translation_trans'));
	}


	protected function parseData() {
		ilMMItemTranslationStorage::storeDefaultTranslation($this->item_facade->identification(), $this->item_facade->getDefaultTitle());
		$this->setData(ilMMItemTranslationStorage::where(['identification' => $this->item_facade->getId()])->getArray());
	}


	/**
	 * @inheritDoc
	 */
	protected function fillRow($a_set) {
		/**
		 * @var $translation ilMMItemTranslationStorage
		 */
		$translation = ilMMItemTranslationStorage::find($a_set['id']);

		$this->tpl->setVariable('ID', $translation->getId());
		$this->tpl->setVariable('LANGUAGE', $translation->getLanguageKey());
		$this->tpl->setVariable('TEXT', $translation->getTranslation());
	}
}

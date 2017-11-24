<?php

/**
 * Class ilBibliographicDetailsGUI
 * The detailled view on each entry
 *
 * @ilCtrl_Calls ilObjBibliographicDetailsGUI: ilBibliographicGUI
 */
class ilBibliographicDetailsGUI {

	/**
	 * @var ilObjBibliographic
	 */
	public $bibl_obj;
	/**
	 * @var \ilBiblEntry
	 */
	public $entry;
	/**
	 * @var \ilBiblTypeFactory
	 */
	protected $bib_type_factory;
	/**
	 * @var \ilBiblTranslationFactory
	 */
	protected $translation_factory;
	/**
	 * @var \ilBiblFieldFactory
	 */
	protected $field_factory;
	/**
	 * @var \ilBiblFieldFilterFactory
	 */
	protected $filter_factory;
	/**
	 * @var \ilBiblAttributeFactory
	 */
	protected $attribute_factory;



	/**
	 * ilBibliographicDetailsGUI constructor.
	 *
	 * @param \ilObjBibliographic $bibl_obj
	 * @param \ilBiblEntry        $entry
	 */
	public function __construct(\ilObjBibliographic $bibl_obj, \ilBiblEntry $entry) {
		$this->bibl_obj = $bibl_obj;
		$this->entry = $entry;
		$this->bib_type_factory = new ilBiblTypeFactory();

		$this->attribute_factory = new ilBiblAttributeFactory();
		$this->type_factory = new ilBiblTypeFactory();
		$this->filter_factory = new ilBiblFieldFilterFactory();
		if(is_object($this->bibl_obj)) {
			$type = $this->type_factory->getInstanceForType($this->bibl_obj->getFileType());
			$this->field_factory = new ilBiblFieldFactory($type);
			$this->translation_factory = new ilBiblTranslationFactory($this->field_factory);
		}
	}


	/**
	 * @param ilObjBibliographic $bibl_obj
	 * @param                    $entry_id
	 *
	 * @return ilBibliographicDetailsGUI
	 */
	public static function getInstance(ilObjBibliographic $bibl_obj, $entry_id) {
		$obj = new self($bibl_obj, ilBiblEntry::getInstance($bibl_obj->getFileTypeAsString(), $entry_id));

		return $obj;
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		global $DIC;
		$tpl = $DIC['tpl'];
		$ilTabs = $DIC['ilTabs'];
		$ilCtrl = $DIC['ilCtrl'];
		$lng = $DIC['lng'];
		$ilHelp = $DIC['ilHelp'];
		/**
		 * @var $ilHelp ilHelpGUI
		 */
		$ilHelp->setScreenIdComponent('bibl');

		$form = new ilPropertyFormGUI();
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, 'showContent'));
		$form->setTitle($lng->txt('detail_view'));
		// add link button if a link is defined in the settings
		$set = new ilSetting("bibl");
		$link = $set->get(strtolower($this->bibl_obj->getFileTypeAsString()));
		if (!empty($link)) {
			$form->addCommandButton('autoLink', 'Link');
		}

		/*
		 * 1) foreach
		 */

		$attributes = $this->entry->getAttributes();
		//translate array key in order to sort by those keys
		foreach ($attributes as $key => $attribute) {
			//Check if there is a specific language entry
			if ($lng->exists($key)) {
				$strDescTranslated = $lng->txt($key);
			} //If not: get the default language entry
			else {
				$arrKey = explode("_", $key);
				if ($this->bib_type_factory->getInstanceForString($arrKey[0])->isStandardField($arrKey[2])) {
					$strDescTranslated = $lng->txt($arrKey[0] . "_default_" . $arrKey[2]);
				} else {
					$strDescTranslated = $arrKey[2];
				}
			}
			unset($attributes[$key]);
			$attributes[$strDescTranslated] = $attribute;
		}
		// sort attributes alphabetically by their array-key
		ksort($attributes, SORT_STRING);
		//$array_of_attribute_objects = $this->attribute_factory->convertIlBiblAttributesToObjects($attributes);
		$attributes = $this->field_factory->sortAttributesByFieldPosition($attributes);
		// render attributes to html
		foreach ($attributes as $key => $attribute) {
			$ci = new ilCustomInputGUI($key);
			$ci->setHTML(self::prepareLatex($attribute));
			$form->addItem($ci);
		}
		// generate/render links to libraries
		$settings = ilBibliographicSetting::getAll();
		foreach ($settings as $set) {
			$ci = new ilCustomInputGUI($set->getName());
			$ci->setHtml($set->getButton($this->bibl_obj, $this->entry));
			$form->addItem($ci);
		}
		$tpl->setPermanentLink("bibl", $this->bibl_obj->getRefId(), "_"
		                                                            . $_GET[ilObjBibliographicGUI::P_ENTRY_ID]);

		// set content and title
		return $form->getHTML();
		//Permanent Link
	}


	/**
	 * This feature has to be discussed by JF first
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public static function prepareLatex($string) {
		return $string;
		static $init;
		$ilMathJax = ilMathJax::getInstance();
		if (!$init) {
			$ilMathJax->init();
			$init = true;
		}

		//		$string = preg_replace('/\\$\\\\(.*)\\$/u', '[tex]$1[/tex]', $string);
		$string = preg_replace('/\\$(.*)\\$/u', '[tex]$1[/tex]', $string);

		return $ilMathJax->insertLatexImages($string);
	}
}
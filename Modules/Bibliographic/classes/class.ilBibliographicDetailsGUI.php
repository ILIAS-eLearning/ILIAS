<?php

/**
 * Class ilBibliographicDetailsGUI
 * The detailled view on each entry
 *
 * @ilCtrl_Calls ilObjBibliographicDetailsGUI: ilBibliographicGUI
 */
class ilBibliographicDetailsGUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	/**
	 * @var \ilBiblEntry
	 */
	public $entry;
	/**
	 * @var \ilBiblFactoryFacade
	 */
	protected $facade;


	/**
	 * ilBibliographicDetailsGUI constructor.
	 *
	 * @param \ilBiblEntry         $entry
	 * @param \ilBiblFactoryFacade $facade
	 */
	public function __construct(\ilBiblEntry $entry, ilBiblFactoryFacade $facade) {
		$this->facade = $facade;
		$this->entry = $entry;
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		global $DIC;

		$ilHelp = $DIC['ilHelp'];
		/**
		 * @var $ilHelp ilHelpGUI
		 */
		$ilHelp->setScreenIdComponent('bibl');

		$form = new ilPropertyFormGUI();
		$this->tabs()->clearTargets();
		$this->tabs()->setBackTarget($this->lng()->txt("back"), $this->ctrl()
		                                                             ->getLinkTarget($this, 'showContent'));
		$form->setTitle($this->lng()->txt('detail_view'));
		// add link button if a link is defined in the settings
		$set = new ilSetting("bibl");
		$link = $set->get(strtolower($this->facade->iliasObject()->getFileTypeAsString()));
		if (!empty($link)) {
			$form->addCommandButton('autoLink', 'Link');
		}

		/*
		 * 1) foreach
		 */

		$attributes = $this->entry->getAttributes();

		// TODO Sorting

		// sort attributes alphabetically by their array-key
		ksort($attributes, SORT_STRING);
		$array_of_attribute_objects = $this->facade->attributeFactory()
		                                           ->convertIlBiblAttributesToObjects($attributes);
		$attributes = $this->facade->fieldFactory()
		                           ->sortAttributesByFieldPosition($array_of_attribute_objects);
		// render attributes to html
		foreach ($attributes as $key => $attribute) {
			$ci = new ilCustomInputGUI($this->facade->translationFactory()
			                                        ->translateAttributeString($this->facade->iliasObject()
			                                                                                ->getFileType(), $key));
			$ci->setHTML(self::prepareLatex($attribute));
			$form->addItem($ci);
		}
		// generate/render links to libraries
		$settings = ilBibliographicSetting::getAll();
		foreach ($settings as $set) {
			$ci = new ilCustomInputGUI($set->getName());
			$ci->setHtml($set->getButton($this->facade->iliasObject(), $this->entry));
			$form->addItem($ci);
		}
		$this->tpl()->setPermanentLink("bibl", $this->facade->iliasObject()->getRefId(), "_"
		                                                                                 . $_GET[ilObjBibliographicGUI::P_ENTRY_ID]);

		return $form->getHTML();
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
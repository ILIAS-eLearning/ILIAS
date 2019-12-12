<?php

/**
 * Class ilBiblEntryDetailPresentationGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblEntryDetailPresentationGUI
{
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
     * ilBiblEntryPresentationGUI constructor.
     *
     * @param \ilBiblEntry         $entry
     * @param \ilBiblFactoryFacade $facade
     */
    public function __construct(\ilBiblEntry $entry, ilBiblFactoryFacade $facade)
    {
        $this->facade = $facade;
        $this->entry = $entry;
    }


    private function initHelp()
    {
        global $DIC;

        $ilHelp = $DIC['ilHelp'];
        /**
         * @var $ilHelp ilHelpGUI
         */
        $ilHelp->setScreenIdComponent('bibl');
    }


    private function initTabs()
    {
        $this->tabs()->clearTargets();
        $this->tabs()->setBackTarget(
            $this->lng()->txt("back"),
            $this->ctrl()->getLinkTargetByClass(ilObjBibliographicGUI::class, ilObjBibliographicGUI::CMD_SHOW_CONTENT)
        );
    }


    /**
     * @return string
     */
    public function getHTML()
    {
        $this->initHelp();
        $this->initTabs();

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng()->txt('detail_view'));

        $this->renderAttributes($form);
        $this->renderLibraries($form);

        $this->tpl()->setPermanentLink(
            "bibl",
            $this->facade->iliasRefId(),
            "_" . (int) $_GET[ilObjBibliographicGUI::P_ENTRY_ID]
        );

        return $form->getHTML();
    }


    /**
     * @param \ilPropertyFormGUI $form
     */
    protected function renderAttributes(ilPropertyFormGUI $form)
    {
        $attributes = $this->facade->attributeFactory()->getAttributesForEntry($this->entry);
        $sorted = $this->facade->attributeFactory()->sortAttributes($attributes);

        foreach ($sorted as $attribute) {
            $translated = $this->facade->translationFactory()->translateAttribute($attribute);
            $ci = new ilNonEditableValueGUI($translated);
            $ci->setValue(self::prepareLatex($attribute->getValue()));
            $form->addItem($ci);
        }
    }


    /**
     * @param \ilPropertyFormGUI $form
     */
    protected function renderLibraries(ilPropertyFormGUI $form)
    {
        // generate/render links to libraries
        // TODO REFACTOR
        $settings = $this->facade->libraryFactory()->getAll();
        foreach ($settings as $set) {
            $ci = new ilCustomInputGUI($set->getName());
            $presentation = new ilBiblLibraryPresentationGUI($set, $this->facade);
            $ci->setHtml($presentation->getButton($this->facade, $this->entry));
            $form->addItem($ci);
        }
    }


    /**
     * This feature has to be discussed by JF first
     *
     * @param $string
     *
     * @return string
     */
    public static function prepareLatex($string)
    {
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

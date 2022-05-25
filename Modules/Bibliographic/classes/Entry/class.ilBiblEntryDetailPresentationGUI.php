<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\UI\Component\Deck\Deck;
use ILIAS\UI\Component\Panel\Standard;
use ILIAS\UI\Component\Panel\Sub;

/**
 * Class ilBiblEntryDetailPresentationGUI
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblEntryDetailPresentationGUI
{
    public \ilBiblEntry $entry;
    protected \ILIAS\UI\Renderer $ui_renderer;
    protected \ILIAS\UI\Factory $ui_factory;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected \ilBiblFactoryFacade $facade;


    /**
     * ilBiblEntryPresentationGUI constructor.
     */
    public function __construct(\ilBiblEntry $entry, ilBiblFactoryFacade $facade)
    {
        global $DIC;
        $this->help = $DIC['ilHelp'];
        $this->facade = $facade;
        $this->entry = $entry;
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->initHelp();
        $this->initTabs();
        $this->initPermanentLink();
    }


    private function initHelp() : void
    {
        $this->help->setScreenIdComponent('bibl');
    }


    private function initTabs() : void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTargetByClass(ilObjBibliographicGUI::class, ilObjBibliographicGUI::CMD_SHOW_CONTENT)
        );
    }

    private function initPermanentLink() : void
    {
        $this->main_tpl->setPermanentLink(
            "bibl",
            $this->facade->iliasRefId(),
            "_" . $this->entry->getId()
        );
    }


    public function getHTML() : string
    {
        $sub_panels = [
            $this->getOverviewPanel()
        ];

        if (($libraries = $this->getLibrariesDeck()) !== null) {
            $sub_panels[] = $libraries;
        }

        return $this->ui_renderer->render(
            $this->ui_factory->panel()->report($this->lng->txt('detail_view'), $sub_panels)
        );
    }

    private function getLibrariesDeck() : ?Sub
    {
        $settings = $this->facade->libraryFactory()->getAll();
        if (count($settings) === 0) {
            return null;
        }

        $data = [];

        foreach ($settings as $set) {
            $presentation = new ilBiblLibraryPresentationGUI($set, $this->facade);
            $data[$set->getName()] = $presentation->getButton($this->facade, $this->entry);
        }

        return $this->ui_factory->panel()->sub(
            $this->lng->txt('bibl_settings_libraries'),
            $this->ui_factory->listing()->characteristicValue()->text($data)
        );
    }

    private function getOverviewPanel() : Sub
    {
        $attributes = $this->facade->attributeFactory()->getAttributesForEntry($this->entry);
        $sorted = $this->facade->attributeFactory()->sortAttributes($attributes);
        $data = [];
        foreach ($sorted as $attribute) {
            $translated = $this->facade->translationFactory()->translateAttribute($attribute);
            $data[$translated] = $attribute->getValue();
        }

        $content = $this->ui_factory->listing()->characteristicValue()->text($data);

        return $this->ui_factory->panel()->sub('', $content);
    }
}

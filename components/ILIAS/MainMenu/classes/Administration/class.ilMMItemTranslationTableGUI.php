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

declare(strict_types=1);

/**
 * Class ilMMItemTranslationTableGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemTranslationTableGUI extends ilTable2GUI
{
    //    private ilCtrl $ctrl;
    //    private ilLanguage $lng;

    /**
     * ilMMItemTranslationTableGUI constructor.
     * @param ilMMItemTranslationGUI  $a_parent_obj
     * @param ilMMItemFacadeInterface $item_facade
     */
    public function __construct(ilMMItemTranslationGUI $a_parent_obj, private ilMMItemFacadeInterface $item_facade)
    {
        $table_id = self::class;
        $this->setId($table_id);
        $this->setPrefix($table_id);
        $this->setFormName($table_id);
        parent::__construct($a_parent_obj);
        $this->ctrl->saveParameter($a_parent_obj, $this->getNavParameter());
        $this->setRowTemplate("tpl.translation_row.html", "components/ILIAS/MainMenu");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setExternalSorting(true);
        $this->setDefaultOrderField("id");
        $this->setDefaultOrderDirection("asc");
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->initColumns();
        $this->initCommands();
        $this->lng->loadLanguageModule("meta");

        $this->addCommandButton(
            ilMMItemTranslationGUI::CMD_SAVE_TRANSLATIONS,
            $this->lng
                ->txt("save")
        );
        $this->addMultiCommand(
            ilBiblTranslationGUI::CMD_DELETE_TRANSLATIONS,
            $this->lng
                ->txt("delete")
        );

        $this->parseData();
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->lng->txt('mm_translation_select'), '', '15px', true);
        $this->addColumn($this->lng->txt('mm_translation_lang'));
        $this->addColumn($this->lng->txt('mm_translation_trans'));
    }

    protected function initCommands(): void
    {
        $this->addMultiCommand(ilBiblTranslationGUI::CMD_DELETE_TRANSLATIONS, $this->lng
            ->txt("delete"));
    }

    protected function parseData(): void
    {
        $this->setData(ilMMItemTranslationStorage::where(['identification' => $this->item_facade->getId()])->getArray());
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    protected function fillRow(array $a_set): void
    {
        /**
         * @var $translation ilMMItemTranslationStorage
         */
        $translation = ilMMItemTranslationStorage::find($a_set['id']);

        $this->tpl->setVariable('ID', $translation->getId());
        $this->tpl->setVariable('LANGUAGE', $this->lng->txt("meta_l_" . $translation->getLanguageKey()));
        $this->tpl->setVariable('TEXT', $translation->getTranslation());
    }
}

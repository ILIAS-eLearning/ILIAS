<?php

declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * @ingroup ServicesAdvancedMetaData
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilAdvancedMDRecordLanguageTableGUI extends ilTable2GUI
{
    private const RECORD_LANGUAGE_TABLE_ID_PREFIX = 'adv_md_record_language_';

    private const COL_LANGUAGE = 'language';
    private const COL_LANGUAGE_CODE = 'code';
    private const COL_DEFAULT = 'default';
    private const COL_ACTIVE = 'active';
    private const COL_INSTALLED = 'installed';

    private const CMD_SAVE_ACTION = 'saveTranslations';

    private Factory $ui_factory;
    private Renderer $ui_renderer;
    private ilAdvancedMDRecord $record;
    private ilAdvancedMDRecordTranslations $record_translation;

    public function __construct(ilAdvancedMDRecord $record, object $a_parent_obj, string $a_parent_cmd = "")
    {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        $this->record = $record;
        $this->record_translation = ilAdvancedMDRecordTranslations::getInstanceByRecordId($this->record->getRecordId());
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setId(
            self::RECORD_LANGUAGE_TABLE_ID_PREFIX . $this->record->getRecordId()
        );
    }

    /**
     * Init table
     */
    public function init(): void
    {
        $this->lng->loadLanguageModule('meta');
        $this->setTitle($this->lng->txt('md_adv_record_lng_table'));

        $this->addColumn('', 'f', '1px');
        $this->addColumn($this->lng->txt('md_adv_record_lng_table_lng'), self::COL_LANGUAGE, '25%');
        $this->addColumn($this->lng->txt('md_adv_record_lng_table_default'), self::COL_DEFAULT, '25%');
        $this->addColumn($this->lng->txt('md_adv_record_lng_table_active'), self::COL_ACTIVE, '25%');
        $this->addColumn($this->lng->txt('md_adv_record_lng_table_inst'), self::COL_INSTALLED, '25%');

        $this->addMultiCommand(self::CMD_SAVE_ACTION, $this->lng->txt('md_adv_record_activate_languages'));
        $this->setSelectAllCheckbox('active_languages');
        $this->enable('select_all');

        $this->setRowTemplate('tpl.record_language_selection_row.html', 'Services/AdvancedMetaData');
        $this->setDefaultOrderField(self::COL_LANGUAGE);
        $this->setDefaultOrderDirection('asc');

        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
    }

    /**
     * Parse content
     */
    public function parse(): void
    {
        $all_languages = $this->readLanguages();
        $installed_languages = ilLanguage::_getInstalledLanguages();

        $rows = [];
        foreach ($all_languages as $language_code) {
            $row = [];
            $row[self::COL_LANGUAGE_CODE] = $language_code;
            $row[self::COL_LANGUAGE] = $this->lng->txt('meta_l_' . $language_code);
            $row[self::COL_INSTALLED] = in_array($language_code, $installed_languages);
            if ($this->record_translation->isConfigured($language_code)) {
                $row[self::COL_ACTIVE] = true;
                $translation = $this->record_translation->getTranslation($language_code);
                $row[self::COL_DEFAULT] = ($translation->getLangKey() == $this->record->getDefaultLanguage());
            } else {
                $row[self::COL_ACTIVE] = false;
                $row[self::COL_DEFAULT] = false;
            }
            $rows[] = $row;
        }
        $this->setMaxCount(count($rows));
        $this->setData($rows);
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set[self::COL_LANGUAGE_CODE]);

        if ($a_set[self::COL_ACTIVE]) {
            $this->tpl->setVariable('ACTIVATION_CHECKED', 'checked="checked"');
        }

        $this->tpl->setVariable('TXT_LANGUAGE', $a_set[self::COL_LANGUAGE]);
        if ($a_set[self::COL_DEFAULT]) {
            $this->tpl->setVariable('DEFAULT_CHECKED', 'checked="checked"');
        }
        if ($a_set[self::COL_ACTIVE]) {
            $this->tpl->setVariable(
                'GLYPH_ACTIVE',
                $this->ui_renderer->render(
                    $this->ui_factory->symbol()->glyph()->apply()
                )
            );
        }
        if ($a_set[self::COL_INSTALLED]) {
            $this->tpl->setVariable(
                'GLYPH_INSTALLED',
                $this->ui_renderer->render(
                    $this->ui_factory->symbol()->glyph()->apply()
                )
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function readLanguages(): array
    {
        $languages = ilObject::_getObjectsByType('lng');
        $parsed_languages = [];
        foreach ($languages as $language) {
            $parsed_languages[] = $language['title'];
        }
        return $parsed_languages;
    }
}

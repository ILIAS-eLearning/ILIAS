<?php declare(strict_types=1);

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

/**
 * Class for category export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCategoryXmlWriter extends ilXmlWriter
{
    public const MODE_SOAP = 1;
    public const MODE_EXPORT = 2;

    protected ilSetting $settings;
    private int $mode = self::MODE_SOAP;
    private ?ilObjCategory $category;

    public function __construct(ilObjCategory $cat = null)
    {
        global $DIC;

        $this->settings = $DIC->settings();
        parent::__construct();

        $this->category = $cat;
    }

    public function setMode(int $a_mode) : void
    {
        $this->mode = $a_mode;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    public function getCategory() : ilObjCategory
    {
        return $this->category;
    }

    public function export(bool $a_with_header = true) : void
    {
        if ($this->getMode() === self::MODE_EXPORT) {
            if ($a_with_header) {
                $this->buildHeader();
            }
            $this->buildCategory();
            $this->buildTranslations();
            ilContainerSortingSettings::_exportContainerSortingSettings($this, $this->getCategory()->getId());
            ilContainer::_exportContainerSettings($this, $this->category->getId());
            $this->buildFooter();
        }
    }

    public function getXml() : string
    {
        return $this->xmlDumpMem(false);
    }

    // Build xml header
    protected function buildHeader() : bool
    {
        $ilSetting = $this->settings;

        $this->xmlSetDtdDef("<!DOCTYPE category PUBLIC \"-//ILIAS//DTD Group//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_cat_4_5.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS category " . $this->getCategory()->getId() . " of installation " . $ilSetting->get('inst_id') . ".");
        $this->xmlHeader();

        return true;
    }

    // Build category start tag
    protected function buildCategory() : void
    {
        $this->xmlStartTag('Category');
    }
    
    // category end tag
    protected function buildFooter() : void
    {
        $this->xmlEndTag('Category');
    }
    
    // Add Translations
    protected function buildTranslations() : void
    {
        $this->xmlStartTag('Translations');
        
        $translations = $this->getCategory()->getObjectTranslation()->getLanguages();
        foreach ($translations as $translation) {
            $this->xmlStartTag(
                'Translation',
                [
                    'default' => (int) $translation->isDefault(),
                    'language' => $translation->getLanguageCode()
                ]
            );
            $this->xmlElement('Title', [], $translation->getTitle());
            $this->xmlElement('Description', [], $translation->getDescription());
            $this->xmlEndTag('Translation');
        }
        $this->xmlEndTag('Translations');
    }
}

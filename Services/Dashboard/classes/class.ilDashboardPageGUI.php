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
 * @ilCtrl_Calls ilDashboardPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_isCalledBy ilDashboardPageGUI: ilAdministrationGUI, ilDashboardPageLanguageSelectGUI
 */
class ilDashboardPageGUI extends ilPageObjectGUI
{
    public function __construct(string $lang)
    {
        if ($lang === '') {
            throw new ilCOPageException('No language provided for Dashboard page');
        }
        $page = new ilDashboardPage();
        if (!ilPageObject::_exists($page->getParentType(), $page->getParentId(), $lang)) {
            $page->setId($page->getParentId());
            $page->setLanguage($lang);
            $page->create();
        }
        parent::__construct($page->getParentType(), $page->getParentId(), 0, false, $lang);
    }


    public function afterConstructor(): void
    {
        $this->ctrl->setParameter($this, $this->getPageObject()->getParentType() . '_lang', $this->getLanguage());
        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(ilObjStyleSheet::lookupObjectStyle($this->getId())));
    }

    public function finishEditing(): void
    {
        $this->ctrl->redirectByClass(ilObjDashboardSettingsGUI::class, 'editCustomization');
    }

    public function executeCommand(): string
    {
        if ($this->ctrl->getCmd() === 'delete') {
            $this->getPageObject()->delete();
            $this->finishEditing();
        }
        return parent::executeCommand();
    }

    public function getAdditionalPageActions(): array
    {
        $this->ctrl->setParameterByClass(ilObjDashboardSettingsGUI::class, $this->getParentType() . '_lang', $this->getLanguage());
        return [$this->ui->factory()->link()->standard(
            $this->lng->txt("obj_sty"),
            $this->ctrl->getLinkTargetByClass([
                ilObjDashboardSettingsGUI::class,
                ilObjectContentStyleSettingsGUI::class
            ], "")
        )];
    }

    public static function isLanguageAvailable(string $lang): bool
    {
        $page = new ilDashboardPage();
        return ilPageObject::_exists($page->getParentType(), $page->getParentId(), $lang);
    }
}

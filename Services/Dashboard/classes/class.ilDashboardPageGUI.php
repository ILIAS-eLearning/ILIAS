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
 * @ilCtrl_Calls ilContainerPageGUI: ilNoteGUI
 * @ilCtrl_Calls ilContainerPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 */
class ilDashboardPageGUI extends ilPageObjectGUI
{
    public function afterConstructor(): void
    {
        $this->setId(ilDashboardPage::ID);
        $this->getPageObject()->setId($this->getId());
        $lang = $this->request->getStringArray('dash_lang')[0] ?? $this->request->getString('dash_lang');
        if ($lang === '') {
            if ($this->checkLangPageAvailable($this->getId(), $this->user->getLanguage())) {
                $lang = $this->user->getLanguage();
            } elseif ($this->checkLangPageAvailable($this->getId(), $this->lng->getDefaultLanguage())) {
                $lang = $this->lng->getDefaultLanguage();
            }
        }
        if ($lang !== '') {
            $this->setLanguage($lang);
            $this->ctrl->setParameterByClass($this::class, 'dash_lang', $lang);
            $this->getPageObject()->setLanguage($this->getLanguage());
            if (!$this->checkLangPageAvailable($this->getId(), $lang)) {
                $this->getPageObject()->create();
            }
            $this->initPageObject();
        }
    }

    public function finishEditing(): void
    {
        $this->ctrl->redirectByClass(ilDashboardGUI::class);
    }

    public function executeCommand(): string
    {
        if ($this->ctrl->getCmd() === 'delete') {
            $this->getPageObject()->delete();
            $this->finishEditing();
        }
        return parent::executeCommand();
    }
}

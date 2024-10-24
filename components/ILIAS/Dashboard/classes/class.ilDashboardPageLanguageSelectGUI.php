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

use ILIAS\Dashboard\DataRetrieval\Language;
use ILIAS\Data\URI;
use ILIAS\DI\Container;
use ILIAS\UI\URLBuilder;

/**
 * @@ilCtrl_isCalledBy ilDashboardPageLanguageSelectGUI: ilObjDashboardSettingsGUI
 */
class ilDashboardPageLanguageSelectGUI
{
    protected Container $dic;
    private ilDashboardPage $page;

    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
        $this->page = new ilDashboardPage();
    }

    public function executeCommand(): void
    {
        if ($this->dic->ctrl()->getNextClass() === strtolower(ilDashboardPageGUI::class)) {
            $lang = $this->dic->http()->wrapper()->query()->retrieve(
                $this->page->getParentType() . '_lang',
                $this->dic->refinery()->kindlyTo()->listOf($this->dic->refinery()->to()->string())
            )[0] ?? '';
            $this->dic->ui()->mainTemplate()->setContent($this->dic->ctrl()->forwardCommand(new ilDashboardPageGUI($lang)));
        } else {
            if ($this->dic->language()->getInstalledLanguages() === [$this->dic->language()->getDefaultLanguage()]) {
                $this->dic->ctrl()->setParameterByClass(ilDashboardPageGUI::class, 'dshs_lang', $this->dic->language()->getDefaultLanguage());
                $this->dic->ctrl()->redirectByClass(ilDashboardPageGUI::class, 'edit');
            }
            $this->select();
        }
    }

    public function select(): void
    {
        $url_builder = new URLBuilder(new URI(
            ILIAS_HTTP_PATH . '/' . $this->dic->ctrl()->getLinkTargetByClass(ilDashboardPageGUI::class, 'edit')
        ));
        list($builder, $token) = $url_builder->acquireParameters([$this->page->getParentType()], 'lang');
        $actions[] = $this->dic->ui()->factory()->table()->action()->single(
            $this->dic->language()->txt('edit'),
            $builder,
            $token
        );

        $url_builder = $url_builder->withURI(new URI(
            ILIAS_HTTP_PATH . '/' . $this->dic->ctrl()->getLinkTargetByClass(ilDashboardPageGUI::class, 'delete')
        ));
        list($builder, $token) = $url_builder->acquireParameters([$this->page->getParentType()], 'lang');
        $actions[] = $this->dic->ui()->factory()->table()->action()->single(
            $this->dic->language()->txt('dash_co_delete'),
            $builder,
            $token
        );

        $table = $this->dic->ui()->factory()->table()->data(
            $this->dic->language()->txt('dash_co_lang'),
            [
                'name' => $this->dic->ui()->factory()->table()->column()->text($this->dic->language()->txt('language'))->withIsSortable(false),
                'user_count' => $this->dic->ui()->factory()->table()->column()->text($this->dic->language()->txt('users'))->withIsSortable(false)
            ],
            new Language()
        )->withActions($actions);

        $this->dic->ui()->mainTemplate()->setContent(
            $this->dic->ui()->renderer()->render($table->withRequest($this->dic->http()->request()))
        );
    }
}

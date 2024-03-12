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
 * @ilCtrl_Calls ilDashboardPageLanguageSelectGUI: ilDashboardPageGUI
 */
class ilDashboardPageLanguageSelectGUI
{
    protected Container $dic;
    public function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }

    public function executeCommand(): void
    {
        if ($this->dic->ctrl()->getNextClass() === strtolower(ilDashboardPageGUI::class)) {
            $ret = $this->dic->ctrl()->forwardCommand(new ilDashboardPageGUI(ilDashboardPage::PARENT_TYPE, 0));
            if ($ret !== "") {
                $this->dic->ui()->mainTemplate()->setContent($ret);
            }
        } else {
            if ($this->dic->language()->getInstalledLanguages() === [$this->dic->language()->getDefaultLanguage()]) {
                $this->dic->ctrl()->setParameterByClass(ilDashboardPageGUI::class, 'dash_lang', $this->dic->language()->getDefaultLanguage());
                $this->dic->ctrl()->redirectByClass(ilDashboardPageGUI::class, 'edit');
            }
            $this->select();
        }
    }

    public function select(): void
    {
        $this->dic->ui()->mainTemplate()->setOnScreenMessage(
            $this->dic->ui()->mainTemplate()::MESSAGE_TYPE_QUESTION,
            $this->dic->language()->txt('dash_page_edit_info'),
            true
        );

        $url_builder = new URLBuilder(new URI(
            ILIAS_HTTP_PATH . '/' . $this->dic->ctrl()->getLinkTargetByClass(ilDashboardPageGUI::class, 'edit')
        ));
        list($builder, $token) = $url_builder->acquireParameters([ilDashboardPage::PARENT_TYPE], 'lang');
        $actions[] = $this->dic->ui()->factory()->table()->action()->single(
            $this->dic->language()->txt('edit'),
            $builder,
            $token
        );

        $url_builder = $url_builder->withURI(new URI(
            ILIAS_HTTP_PATH . '/' . $this->dic->ctrl()->getLinkTargetByClass(ilDashboardPageGUI::class, 'delete')
        ));
        list($builder, $token) = $url_builder->acquireParameters([ilDashboardPage::PARENT_TYPE], 'lang');
        $actions[] = $this->dic->ui()->factory()->table()->action()->single(
            $this->dic->language()->txt('delete'),
            $builder,
            $token
        );

        $table = $this->dic->ui()->factory()->table()->data(
            $this->dic->language()->txt('languages'),
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

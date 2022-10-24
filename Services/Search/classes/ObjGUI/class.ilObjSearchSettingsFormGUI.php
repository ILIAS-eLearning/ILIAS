<?php

declare(strict_types=1);
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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\DI\UIServices;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard as StandardForm;

/**
* @author Tim Schmitz <schmitz@leifos.com>
*/
class ilObjSearchSettingsFormGUI
{
    protected GlobalHttpState $http;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected Factory $factory;
    protected Renderer $renderer;

    protected ilObjSearchRpcClientCoordinator $coordinator;

    public function __construct(
        GlobalHttpState $http,
        ilCtrlInterface $ctrl,
        ilLanguage $lng,
        UIServices $ui,
        ilObjSearchRpcClientCoordinator $coordinator
    ) {
        $this->http = $http;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $ui->mainTemplate();
        $this->factory = $ui->factory();
        $this->renderer = $ui->renderer();
        $this->coordinator = $coordinator;
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case 'readOnly':
                $this->showForm(true);
                break;

            case 'edit':
                $this->showForm(false);
                break;

            case 'permDenied':
                $this->showPermissionDenied();
                break;

            case 'update':
                $this->update();
                break;

            default:
                throw new ilObjSearchSettingsGUIException(
                    'Invalid command for ilObjSearchSettingsFormGUI: ' . $cmd
                );
        }
    }

    protected function showForm(
        bool $read_only,
        bool $get_from_post = false
    ): void {
        $form = $this->initForm($read_only);
        if ($get_from_post) {
            $form = $form->withRequest($this->http->request());
        }
        $this->tpl->setContent($this->renderer->render($form));
    }

    protected function update(): void
    {
        $form = $this->initForm(false)
                     ->withRequest($this->http->request());

        if (!$form->getData()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
            $this->showForm(false, true);
            return;
        }

        $settings = $this->getSettings();
        $data = $form->getData()['section'];

        $settings->setMaxHits((int) $data['max_hits']);

        switch ((int) $data['search_type']) {
            case ilSearchSettings::LIKE_SEARCH:
                $settings->enableLucene(false);
                break;
            case ilSearchSettings::LUCENE_SEARCH:
                $settings->enableLucene(true);
                break;
        }
        $settings->setDefaultOperator((int) $data['operator']);
        $settings->enableLuceneItemFilter(!is_null($data['filter']));
        if (!is_null($data['filter'])) {
            $settings->setLuceneItemFilter((array) $data['filter']);
        }
        $settings->setHideAdvancedSearch((bool) $data['hide_adv_search']);
        $settings->setAutoCompleteLength((int) $data['auto_complete_length']);
        $settings->showInactiveUser((bool) $data['inactive_user']);
        $settings->showLimitedUser((bool) $data['limited_user']);
        $settings->enableDateFilter((bool) $data['cdate']);
        $settings->enableLuceneUserSearch((bool) $data['user_search_enabled']);
        $settings->update();

        // refresh lucene server
        try {
            if ($settings->enabledLucene()) {
                $this->coordinator->refreshLuceneSettings();
            }
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            ilSession::clear('search_last_class');
            $this->ctrl->redirect($this, 'edit');
        } catch (Exception $exception) {
            $this->tpl->setOnScreenMessage('failure', $exception->getMessage());
            $this->showForm(false);
        }
    }

    protected function showPermissionDenied(): void
    {
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
        $this->ctrl->redirect($this, 'readOnly');
    }

    protected function initForm(bool $read_only): StandardForm
    {
        $settings = $this->getSettings();
        $field_factory = $this->factory->input()->field();

        // Max hits
        $values = [];
        for ($value = 5; $value <= 50; $value += 5) {
            $values[$value] = $value;
        }
        $hits = $field_factory->select(
            $this->lng->txt('seas_max_hits'),
            $values,
            $this->lng->txt('seas_max_hits_info')
        )->withValue($settings->getMaxHits())
         ->withRequired(true);

        // Search type
        $type = $field_factory->radio(
            $this->lng->txt('search_type')
        )->withOption(
            (string) ilSearchSettings::LIKE_SEARCH,
            $this->lng->txt('search_direct'),
            $this->lng->txt('search_like_info')
        )->withOption(
            (string) ilSearchSettings::LUCENE_SEARCH,
            $this->lng->txt('search_lucene'),
            $this->lng->txt('java_server_info')
        )->withRequired(true);

        if ($settings->enabledLucene()) {
            $type = $type->withValue((string) ilSearchSettings::LUCENE_SEARCH);
        } else {
            $type = $type->withValue((string) ilSearchSettings::LIKE_SEARCH);
        }

        // Default operator
        $operator = $field_factory->radio(
            $this->lng->txt('lucene_default_operator'),
            $this->lng->txt('lucene_default_operator_info')
        )->withOption(
            (string) ilSearchSettings::OPERATOR_AND,
            $this->lng->txt('lucene_and')
        )->withOption(
            (string) ilSearchSettings::OPERATOR_OR,
            $this->lng->txt('lucene_or')
        )->withRequired(true)
         ->withValue((string) $settings->getDefaultOperator());

        // User search
        $user_search = $field_factory->checkbox(
            $this->lng->txt('search_user_search_form'),
            $this->lng->txt('search_user_search_info_form')
        )->withValue($settings->isLuceneUserSearchEnabled());

        // Item filter
        $filter = $settings->getLuceneItemFilter();
        $checks = [];
        foreach (ilSearchSettings::getLuceneItemFilterDefinitions() as $obj => $def) {
            $checks[$obj] = $field_factory->checkbox(
                $this->lng->txt($def['trans'])
            )->withValue(isset($filter[$obj]) && $filter[$obj]);
        }

        $item_filter = $field_factory->optionalGroup(
            $checks,
            $this->lng->txt('search_item_filter_form'),
            $this->lng->txt('search_item_filter_form_info')
        );
        if (!$settings->isLuceneItemFilterEnabled()) {
            $item_filter = $item_filter->withValue(null);
        }

        // Filter by date
        $cdate = $field_factory->checkbox(
            $this->lng->txt('search_cdate_filter'),
            $this->lng->txt('search_cdate_filter_info')
        )->withValue($settings->isDateFilterEnabled());

        // hide advanced search
        $hide_adv = $field_factory->checkbox(
            $this->lng->txt('search_hide_adv_search')
        )->withValue($settings->getHideAdvancedSearch());

        // number of auto complete entries
        $options = [
            5 => 5,
            10 => 10,
            20 => 20,
            30 => 30
        ];
        $val = ($settings->getAutoCompleteLength() > 0)
            ? $settings->getAutoCompleteLength()
            : 10;
        $auto_complete = $field_factory->select(
            $this->lng->txt('search_auto_complete_length'),
            $options
        )->withValue($val);

        // Show inactive users
        $inactive_user = $field_factory->checkbox(
            $this->lng->txt('search_show_inactive_user'),
            $this->lng->txt('search_show_inactive_user_info')
        )->withValue($settings->isInactiveUserVisible());

        // Show limited users
        $limited_user = $field_factory->checkbox(
            $this->lng->txt('search_show_limited_user'),
            $this->lng->txt('search_show_limited_user_info')
        )->withValue($settings->isLimitedUserVisible());

        /**
         * TODO: Split up the form into two or three sections.
         */
        $section = $this->factory->input()->field()->section(
            [
                'max_hits' => $hits,
                'search_type' => $type,
                'operator' => $operator,
                'user_search_enabled' => $user_search,
                'filter' => $item_filter,
                'cdate' => $cdate,
                'hide_adv_search' => $hide_adv,
                'auto_complete_length' => $auto_complete,
                'inactive_user' => $inactive_user,
                'limited_user' => $limited_user
            ],
            $this->lng->txt('seas_settings')
        )->withDisabled($read_only);

        if ($read_only) {
            $action = $this->ctrl->getFormAction($this, 'permDenied');
        } else {
            $action = $this->ctrl->getFormAction($this, 'update');
        }

        return $this->factory->input()->container()->form()->standard(
            $action,
            ['section' => $section]
        );
    }

    protected function getSettings(): ilSearchSettings
    {
        return ilSearchSettings::getInstance();
    }
}

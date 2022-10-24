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
use ILIAS\Refinery\Factory as RefFactory;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Standard as StandardForm;

/**
 * @author Tim Schmitz <schmitz@leifos.com>
 */
class ilObjSearchLuceneSettingsFormGUI
{
    protected GlobalHttpState $http;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected Factory $factory;
    protected Renderer $renderer;
    protected RefFactory $refinery;
    protected ilObjUser $user;

    protected ilObjSearchRpcClientCoordinator $coordinator;

    public function __construct(
        GlobalHttpState $http,
        ilCtrlInterface $ctrl,
        ilLanguage $lng,
        UIServices $ui,
        RefFactory $refinery,
        ilObjUser $user,
        ilObjSearchRpcClientCoordinator $coordinator
    ) {
        $this->http = $http;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->tpl = $ui->mainTemplate();
        $this->factory = $ui->factory();
        $this->renderer = $ui->renderer();
        $this->refinery = $refinery;
        $this->user = $user;
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
                    'Invalid command for ilObjSearchLuceneSettingsFormGUI: ' . $cmd
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

        $settings->setFragmentCount((int) $data['fragmentCount']);
        $settings->setFragmentSize((int) $data['fragmentSize']);
        $settings->setMaxSubitems((int) $data['maxSubitems']);
        $settings->showRelevance((bool) $data['relevance']);
        $settings->enableLuceneMimeFilter(!is_null($data['mime']));
        if (!is_null($data['mime'])) {
            $settings->setLuceneMimeFilter((array) $data['mime']);
        }
        $settings->showSubRelevance((bool) $data['relevance']['subrelevance']);
        $settings->enablePrefixWildcardQuery((bool) $data['prefix']);
        $settings->setLastIndexTime(new ilDateTime(
            $data['last_index']->getTimestamp(),
            IL_CAL_UNIX
        ));
        $settings->update();

        // refresh lucene server
        try {
            if ($settings->enabledLucene()) {
                $this->coordinator->refreshLuceneSettings();
            }
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'), true);
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

        // Item filter
        $filter = $settings->getLuceneMimeFilter();
        $checks = [];
        foreach (ilSearchSettings::getLuceneMimeFilterDefinitions() as $mime => $def) {
            $checks[$mime] = $field_factory->checkbox(
                $this->lng->txt($def['trans'])
            )->withValue(isset($filter[$mime]) && $filter[$mime]);
        }

        $item_filter = $field_factory->optionalGroup(
            $checks,
            $this->lng->txt('search_mime_filter_form'),
            $this->lng->txt('search_mime_filter_form_info')
        );
        if (!$settings->isLuceneMimeFilterEnabled()) {
            $item_filter = $item_filter->withValue(null);
        }

        // Prefix
        $prefix = $field_factory->checkbox(
            $this->lng->txt('lucene_prefix_wildcard'),
            $this->lng->txt('lucene_prefix_wildcard_info')
        )->withValue($settings->isPrefixWildcardQueryEnabled());

        // Number of fragments
        $frag_count = $field_factory->numeric(
            $this->lng->txt('lucene_num_fragments'),
            $this->lng->txt('lucene_num_frag_info')
        )->withValue($settings->getFragmentCount())
         ->withRequired(true)
         ->withAdditionalTransformation(
             $this->refinery->int()->isLessThanOrEqual(10)
         )->withAdditionalTransformation(
             $this->refinery->int()->isGreaterThanOrEqual(1)
         );

        // Size of fragments
        $frag_size = $field_factory->numeric(
            $this->lng->txt('lucene_size_fragments'),
            $this->lng->txt('lucene_size_frag_info')
        )->withValue($settings->getFragmentSize())
         ->withRequired(true)
         ->withAdditionalTransformation(
             $this->refinery->int()->isLessThanOrEqual(1000)
         )->withAdditionalTransformation(
             $this->refinery->int()->isGreaterThanOrEqual(10)
         );

        // Number of sub-items
        $max_sub = $field_factory->numeric(
            $this->lng->txt('lucene_max_sub'),
            $this->lng->txt('lucene_max_sub_info')
        )->withValue($settings->getMaxSubitems())
         ->withRequired(true)
         ->withAdditionalTransformation(
             $this->refinery->int()->isLessThanOrEqual(10)
         )->withAdditionalTransformation(
             $this->refinery->int()->isGreaterThanOrEqual(1)
         );

        // Relevance
        $subrel = $field_factory->checkbox(
            $this->lng->txt('lucene_show_sub_relevance')
        )->withValue($settings->isSubRelevanceVisible());

        $relevance = $field_factory->optionalGroup(
            ['subrelevance' => $subrel],
            $this->lng->txt('lucene_relevance'),
            $this->lng->txt('lucene_show_relevance_info')
        );
        if (!$settings->isRelevanceVisible()) {
            $relevance = $relevance->withValue(null);
        }

        // Last Index
        $timezone = $this->user->getTimeZone();
        $datetime = new DateTime(
            '@' . $settings->getLastIndexTime()->get(IL_CAL_UNIX)
        );
        $datetime->setTimezone(new DateTimeZone($timezone));
        $last_index = $field_factory->dateTime(
            $this->lng->txt('lucene_last_index_time'),
            $this->lng->txt('lucene_last_index_time_info')
        )->withRequired(true)
         ->withUseTime(true)
         ->withTimezone($timezone);
        $last_index = $last_index->withValue(
            $datetime->format($last_index->getFormat()->toString() . ' H:i')
        );

        /**
         * TODO: Split up the form into two or three sections.
         */
        $section = $this->factory->input()->field()->section(
            [
                'mime' => $item_filter,
                'prefix' => $prefix,
                'fragmentCount' => $frag_count,
                'fragmentSize' => $frag_size,
                'maxSubitems' => $max_sub,
                'relevance' => $relevance,
                'last_index' => $last_index
            ],
            $this->lng->txt('lucene_settings_title')
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

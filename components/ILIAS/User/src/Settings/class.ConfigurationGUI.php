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

namespace ILIAS\User\Settings;

use ILIAS\User\LocalDIC;
use ILIAS\User\RedirectOnMissingWrite;
use ILIAS\User\PropertyAttributes;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\URI;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use Psr\Http\Message\ServerRequestInterface;

class ConfigurationGUI implements DataRetrieval
{
    use RedirectOnMissingWrite;

    private readonly Repository $user_settings_repository;
    private readonly URLBuilder $url_builder;
    private readonly URLBuilderToken $action_token;
    private readonly URLBuilderToken $setting_id_token;

    private array $available_settings;

    public function __construct(
        private readonly \ILIAS\Language\Language $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilAccess $access,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly ServerRequestInterface $request,
        private readonly RequestWrapper $request_wrapper,
        private readonly HttpService $http
    ) {
        $this->user_settings_repository = LocalDIC::dic()[Repository::class];
        $this->available_settings = $this->user_settings_repository->get();

        $url_builder = new URLBuilder(new URI(ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(self::class, 'action')));
        [
            $this->url_builder,
            $this->action_token,
            $this->setting_id_token
        ] = $url_builder->acquireParameters(
            ['user', 'settings'],
            'table_action',
            'setting'
        );
    }

    public function executeCommand(): void
    {
        $this->redirectOnMissingWrite($this->access, $this->ctrl, $this->tpl, $this->lng);
        $cmd = $this->ctrl->getCmd() . 'Cmd';
        $this->$cmd();
    }

    public function showCmd(?RoundTripModal $modal = null): void
    {
        $content = [
            $this->buildTable()
        ];

        if ($modal !== null) {
            $content[] = $modal;
        }

        $this->tpl->setContent(
            $this->ui_renderer->render($content)
        );
    }

    public function actionCmd(): void
    {
        $this->http->saveResponse(
            $this->http->response()->withBody(
                Streams::ofString(
                    $this->ui_renderer->renderAsync($this->buildEditModal())
                )
            )
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    public function saveCmd(): void
    {
        $modal = $this->buildEditModal()->withRequest($this->request);
        $data = $modal->getData();
        if ($data === null) {
            $this->showCmd(
                $modal->withOnLoad($modal->getShowSignal())
            );
            return;
        }

        $this->user_settings_repository->storeConfiguration($data['setting']);
        $this->available_settings = $this->user_settings_repository->get();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('usr_settings_saved'));
        $this->showCmd();
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $this->sortRows($order);
        foreach ($this->available_settings as $setting) {
            yield $setting->getTableRow(
                $row_builder,
                $this->lng
            );
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count($this->available_settings);
    }

    private function buildTable(): DataTable
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('user_settings'),
            $this->getColumns()
        )->withActions([
            $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit_setting'),
                $this->url_builder,
                $this->setting_id_token
            )->withAsync(true)
        ])->withRequest($this->request);
    }

    private function getColumns(): array
    {
        $cf = $this->ui_factory->table()->column();
        return [
            'field' => $cf->text($this->lng->txt('user_field'))->withIsSortable(true),
            'changeable_by_user' => $cf->boolean(
                $this->lng->txt(
                    PropertyAttributes::ChangeableByUser->value
                ),
                $this->ui_factory->symbol()->glyph()->checked(),
                $this->ui_factory->symbol()->glyph()->unchecked()
            )->withIsSortable(true),
            'changeable_in_local_user_administration' => $cf->boolean(
                $this->lng->txt(
                    PropertyAttributes::ChangeableInLocalUserAdministration->value
                ),
                $this->ui_factory->symbol()->glyph()->checked(),
                $this->ui_factory->symbol()->glyph()->unchecked()
            )->withIsSortable(true),
            'export' => $cf->boolean(
                $this->lng->txt(
                    PropertyAttributes::Export->value
                ),
                $this->ui_factory->symbol()->glyph()->checked(),
                $this->ui_factory->symbol()->glyph()->unchecked()
            )->withIsSortable(true)
        ];
    }

    private function buildEditModal(): RoundTripModal
    {
        $identifier = $this->retrieveIdentifierFromQuery();
        $this->ctrl->setParameterByClass(self::class, $this->setting_id_token->getName(), $identifier);
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('edit_setting'),
            null,
            $this->user_settings_repository->getByIdentifier(
                $identifier
            )->getForm(
                $this->lng,
                $this->ui_factory->input()->field(),
                $this->refinery
            ),
            $this->ctrl->getFormActionByClass(self::class, 'save')
        );
    }

    private function retrieveIdentifierFromQuery(): string
    {
        $identifier = $this->request_wrapper->retrieve(
            $this->setting_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->string()
                )
            ])
        );

        if (is_array($identifier)) {
            return $identifier[0];
        }
        return $identifier;
    }

    private function sortRows(Order $order): void
    {
        $order_array = $order->get();
        $key = array_key_first($order_array);
        $factor = array_shift($order_array) === 'ASC' ? 1 : -1;
        if ($key === 'field') {
            usort(
                $this->available_settings,
                fn(Setting $v1, Setting $v2): int =>
                    $factor * ($v1->getLabel($this->lng) <=> $v2->getLabel($this->lng))
            );
        }

        if ($key === 'export') {
            usort(
                $this->available_settings,
                fn(Setting $v1, Setting $v2): int =>
                    $factor * ($v1->export() <=> $v2->export())
            );
        }

        if ($key === 'changeable_by_user') {
            usort(
                $this->available_settings,
                fn(Setting $v1, Setting $v2): int =>
                    $factor * ($v1->isChangeableByUser() <=> $v2->isChangeableByUser())
            );
        }

        if ($key === 'changeable_in_local_user_administration') {
            usort(
                $this->available_settings,
                fn(Setting $v1, Setting $v2): int =>
                    $factor * ($v1->isChangeableInLocalUserAdministration() <=> $v2->isChangeableInLocalUserAdministration())
            );
        }
    }
}

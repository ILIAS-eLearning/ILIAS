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

namespace ILIAS\User\Profile\Fields;

use ILIAS\User\LocalDIC;
use ILIAS\User\RedirectOnMissingWrite;
use ILIAS\User\PropertyAttributes;
use ILIAS\User\Profile\ChangeListeners\UserFieldAttributesChangeListener;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
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

    private const string CHANGED_ATTRIBUTES_PARAMETER = 'ca';

    private readonly Repository $profile_fields_repository;
    private readonly URLBuilder $url_builder;
    private readonly URLBuilderToken $action_token;
    private readonly URLBuilderToken $field_id_token;

    private array $available_change_listeners;
    private array $available_fields;

    public function __construct(
        private readonly \ILIAS\Language\Language $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilAppEventHandler $event,
        private readonly \ilAccess $access,
        private readonly \ilSetting $settings,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly ServerRequestInterface $request,
        private readonly RequestWrapper $request_wrapper,
        private readonly HttpService $http
    ) {
        $this->available_change_listeners = LocalDIC::dic()['profile.fields.changelisteners'];
        $this->profile_fields_repository = LocalDIC::dic()['profile.fields.repository'];
        $this->available_fields = $this->profile_fields_repository->get();

        $url_builder = new URLBuilder(
            new URI(
                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                    [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, self::class],
                    'action'
                )
            )
        );
        [
            $this->url_builder,
            $this->action_token,
            $this->field_id_token
        ] = $url_builder->acquireParameters(
            ['profile', 'fields'],
            'table_action',
            'field'
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
                    $this->ui_renderer->renderAsync(
                        $this->buildEditModal(
                            $this->profile_fields_repository->getByIdentifier(
                                $this->retrieveIdentifierFromQuery()
                            )
                        )
                    )
                )
            )
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    public function saveCmd(): void {
        $field = $this->profile_fields_repository->getByIdentifier(
            $this->retrieveIdentifierFromQuery()
        );
        $modal = $this->buildEditModal($field)->withRequest($this->request);
        $data = $modal->getData();
        if ($data === null) {
            $this->showCmd($modal->withOnLoad($modal->getShowSignal()));
            return;
        }

        if ($data['field']->isRequired() && ! $data['field']->isVisibleInRegistration()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('invalid_visible_required_options_selected'));
            $this->showCmd();
            return;
        }

        $listeners_to_notify = $this->getListenersToNotifyByChangedValues($field, $data['field']);
        if ($listeners_to_notify !== []) {
            $this->setChangedAttributesParameter($listeners_to_notify);
            $modal = $this->buildChangeListenerConfirmationModal(
                $listeners_to_notify,
                $data['field']
            );
            $this->showCmd($modal->withOnLoad($modal->getShowSignal()));
            return;
        }

        $this->storeFieldAndContinue($data['field']);
    }

    public function saveAfterListenerConfirmationCmd(): void
    {
        $field = $this->profile_fields_repository->getByIdentifier(
            $this->retrieveIdentifierFromQuery()
        );

        $data = $this->buildChangeListenerConfirmationModal(
            $this->getListenersToNotifyByInterests(
                $field,
                $this->retrieveChangedAttributesFromQuery()
            ),
            $field,
        )->withRequest($this->request)->getData();

        if ($data === null || $data['field']->isRequired() && !$data['field']->isVisibleInRegistration()) {
            $this->showCmd();
            return;
        }

        $this->storeFieldAndContinue($data['field']);

        $this->event->raise(
            'components/ILIAS/User',
            'onUserFieldAttributesChanged',
            $field->getChangedAttributes($data['field'])
        );
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
        foreach ($this->available_fields as $setting) {
            yield $setting->getTableRow(
                $row_builder,
                $this->lng,
                $this->ui_factory,
                $this->ui_renderer,
                $this->refinery,
                $this->settings
            );
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        return count($this->available_fields);
    }

    private function storeFieldAndContinue(Field $field): void
    {
        $this->profile_fields_repository->storeConfiguration($field);
        $this->available_fields = $this->profile_fields_repository->get();
        \ilMemberAgreement::_reset();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('usr_settings_saved'));
        $this->showCmd();
    }

    private function buildTable(): DataTable
    {
        return $this->ui_factory->table()->data(
            $this,
            $this->lng->txt('profile_fields'),
            $this->getColumns()
        )->withActions([
            $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit_field'),
                $this->url_builder,
                $this->field_id_token
            )->withAsync(true)
        ])->withRequest($this->request);
    }

    private function getColumns(): array
    {
        $cf = $this->ui_factory->table()->column();
        return [
            'field' => $cf->text($this->lng->txt('user_field'))->withIsSortable(true),
            'access' => $cf->text($this->lng->txt('access'))->withIsSortable(false),
            'required' => $cf->boolean(
                $this->lng->txt(
                    PropertyAttributes::Required->getLanguageVariable()
                ),
                $this->ui_factory->symbol()->glyph()->checked(),
                $this->ui_factory->symbol()->glyph()->unchecked()
            )->withIsSortable(true),
            'export' => $cf->boolean(
                $this->lng->txt(
                    PropertyAttributes::Export->getLanguageVariable()
                ),
                $this->ui_factory->symbol()->glyph()->checked(),
                $this->ui_factory->symbol()->glyph()->unchecked()
            )->withIsSortable(true),
            'searchable' => $cf->boolean(
                $this->lng->txt(
                    PropertyAttributes::Searchable->getLanguageVariable()
                ),
                $this->ui_factory->symbol()->glyph()->checked(),
                $this->ui_factory->symbol()->glyph()->unchecked()
            )->withIsSortable(true),
            'available_in_certificates' => $cf->boolean(
                $this->lng->txt(
                    PropertyAttributes::AvailableInCertificates->getLanguageVariable()
                ),
                $this->ui_factory->symbol()->glyph()->checked(),
                $this->ui_factory->symbol()->glyph()->unchecked()
            )->withIsSortable(true)
        ];
    }

    private function sortRows(Order $order): void
    {
        $order_array = $order->get();
        $key = array_key_first($order_array);
        $factor = array_shift($order_array) === 'ASC' ? 1 : -1;
        if ($key === 'field') {
            usort(
                $this->available_fields,
                fn(Field $v1, Field $v2): int =>
                    $factor * ($this->lng->txt($v1->getLanguageVariable()) <=> $this->lng->txt($v2->getLanguageVariable()))
            );
        }

        if ($key === 'export') {
            usort(
                $this->available_fields,
                fn(Field $v1, Field $v2): int =>
                    $factor * ($v1->export() <=> $v2->export())
            );
        }

        if ($key === 'required') {
            usort(
                $this->available_fields,
                fn(Field $v1, Field $v2): int =>
                    $factor * ($v1->isRequired() <=> $v2->isRequired())
            );
        }

        if ($key === 'searchable') {
            usort(
                $this->available_fields,
                fn(Field $v1, Field $v2): int =>
                    $factor * ($v1->isSearchable() <=> $v2->isSearchable())
            );
        }

        if ($key === 'available_in_certificates') {
            usort(
                $this->available_fields,
                fn(Field $v1, Field $v2): int =>
                    $factor * ($v1->isAvailableInCertificates() <=> $v2->isAvailableInCertificates())
            );
        }
    }

    private function buildEditModal(
        Field $field
    ): RoundTripModal {
        $identifier = $this->retrieveIdentifierFromQuery();
        $this->ctrl->setParameterByClass(self::class, $this->field_id_token->getName(), $identifier);
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('edit_field'),
            null,
            $field->getForm(
                $this->lng,
                $this->ui_factory->input()->field(),
                $this->refinery
            ),
            $this->ctrl->getFormActionByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, self::class],
                'save'
            )
        );
    }

    private function buildChangeListenerConfirmationModal(
        array $listeners_to_notify,
        Field $field
    ): RoundTripModal {
        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('usr_field_change_components_listening'),
            $this->ui_factory->messageBox()->confirmation(
                $this->ui_renderer->render(
                    $this->buildListingOfListeners($listeners_to_notify, $field->getLanguageVariable())
                )
            ),
            $field->getHiddenForm($this->ui_factory->input()->field(), $this->refinery),
            $this->ctrl->getFormActionByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, self::class],
                'saveAfterListenerConfirmation'
            )
        );
    }

    private function buildListingOfListeners(
        array $listeners_to_notify,
        string $field_lang_var
    ): DescriptiveListing {
        return $this->ui_factory->listing()->descriptive(
            array_reduce(
                $listeners_to_notify,
                function (array $c, UserFieldAttributesChangeListener $v) use ($field_lang_var): array {
                    $c[$v->getComponentName()] = $v->getDescriptionForField(
                        $this->lng,
                        $field_lang_var,
                        $v->isInterestedInAttribute()->getLanguageVariable()
                    );
                    return $c;
                },
                []
            )
        );
    }

    private function getListenersToNotifyByChangedValues(
        Field $old_field,
        Field $new_field
    ): array {
        return array_reduce(
            $this->available_change_listeners,
            static function (
                array $c,
                string $listener_class
            ) use ($old_field, $new_field): array {
                $listener = new $listener_class();
                $field_definition_class = $listener->isInterestedInField();

                if ($old_field->getIdentifier() === (new $field_definition_class())->getIdentifier()
                    && $old_field->retrieveValueByPropertyAttribute($listener->isInterestedInAttribute())
                        !== $new_field->retrieveValueByPropertyAttribute($listener->isInterestedInAttribute())) {
                    $c[] = $listener;
                }

                return $c;
            },
            []
        );
    }

    /**
     * @param array<PropertyAttributes> $attribute
     * @return array<UserFieldAttributesChangeListener>
     */
    private function getListenersToNotifyByInterests(
        Field $field,
        array $attributes
    ): array {
        return array_reduce(
            $this->available_change_listeners,
            function (
                array $c,
                string $listener_class
            ) use ($field, $attributes): array {
                $listener = new $listener_class();
                if ($field->getIdentifier() === $this->profile_fields_repository->getByClass(
                            $listener->isInterestedInField()
                        )->getIdentifier()
                    && in_array($listener->isInterestedInAttribute(), $attributes)) {
                    $c[] = $listener;
                }

                return $c;
            },
            []
        );
    }

    private function retrieveIdentifierFromQuery(): string
    {
        $identifier = $this->request_wrapper->retrieve(
            $this->field_id_token->getName(),
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

    private function setChangedAttributesParameter(array $listeners_to_notify): void
    {
        $this->ctrl->setParameterByClass(
            self::class,
            self::CHANGED_ATTRIBUTES_PARAMETER,
            implode(
                ',',
                array_map(
                    fn (UserFieldAttributesChangeListener $v): string => $v->isInterestedInAttribute()->value,
                    $listeners_to_notify
                )
            )
        );
    }

    private function retrieveChangedAttributesFromQuery(): array
    {
        if (!$this->request_wrapper->has(self::CHANGED_ATTRIBUTES_PARAMETER)) {
            return [];
        }

        return $this->request_wrapper->retrieve(
            self::CHANGED_ATTRIBUTES_PARAMETER,
            $this->refinery->custom()->transformation(
                fn (string $v): array => array_reduce(
                    explode(',', $v),
                    static function (array $c, string $v): array {
                        $a = PropertyAttributes::tryFrom($v);
                        if ($a !== null) {
                            $c[] = $a;
                        }
                        return $c;
                    },
                    []
                )
            )
        );
    }
}

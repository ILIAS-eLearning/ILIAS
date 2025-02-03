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


use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Table;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Range;
use ILIAS\Data\Order;

/**
 * Class ilOrgUnitTypeGUI
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitTypeGUI
{
    private ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilTabsGUI $tabs;
    private ilAccessHandler $access;
    private ilToolbarGUI $toolbar;
    private \ilSetting $settings;
    protected ilLanguage $lng;
    protected \ILIAS\UI\Component\Link\Factory $link_factory;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected Refinery $refinery;
    protected ServerRequestInterface $request;
    protected DataFactory $data_factory;
    protected URLBuilder $url_builder;
    protected array $query_namespace;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $row_id_token;
    protected ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $query;

    /**
     * @param ilObjOrgUnitGUI $parent_gui
     */
    public function __construct(
        private ilObjOrgUnitGUI $parent_gui
    ) {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->lng->loadLanguageModule('orgu');
        $this->ctrl->saveParameter($this, 'type_id');
        $this->lng->loadLanguageModule('meta');
        $this->checkAccess();
        $this->link_factory = $DIC['ui.factory']->link();
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->refinery = $DIC['refinery'];
        $this->request = $DIC->http()->request();
        $this->lng->loadLanguageModule('meta');

        $this->data_factory = new DataFactory();
        $here_uri = $this->data_factory->uri(
            $this->request->getUri()->__toString()
        );
        $this->url_builder = new URLBuilder($here_uri);
        $this->query_namespace = ['orgu', 'typeedit'];
        list($url_builder, $action_token, $row_id_token) =
            $this->url_builder->acquireParameters($this->query_namespace, "action", "row_ids");
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->row_id_token = $row_id_token;
        $this->query = $DIC->http()->wrapper()->query();
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        if ($this->query->has($this->action_token->getName())) {
            $cmd = $this->query->retrieve(
                $this->action_token->getName(),
                $this->refinery->to()->string()
            );
        }

        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case strtolower(ilOrgUnitTypeUploadHandlerGUI::class):
                $this->ctrl->forwardCommand(
                    new ilOrgUnitTypeUploadHandlerGUI()
                );
                break;

            case '':
                switch ($cmd) {
                    case '':
                    case 'listTypes':
                        $this->listTypes();
                        break;
                    case 'add':
                        $this->edit();
                        break;
                    case 'edit':
                        $this->setSubTabsEdit('general');
                        $this->edit();
                        break;
                    case 'editCustomIcons':
                        $this->setSubTabsEdit('custom_icons');
                        $this->editCustomIcons();
                        break;
                    case 'editAMD':
                        $this->setSubTabsEdit('amd');
                        $this->editAMD();
                        break;
                    case 'updateAMD':
                        $this->setSubTabsEdit('amd');
                        $this->updateAMD();
                        break;
                    case 'updateCustomIcons':
                        $this->setSubTabsEdit('custom_icons');
                        $this->updateCustomIcons();
                        break;
                    case 'create':
                        $this->create();
                        break;
                    case 'update':
                        $this->setSubTabsEdit('general');
                        $this->update();
                        break;
                    case 'delete':
                        $this->delete();
                        break;
                }
                break;
        }
    }

    private function checkAccess(): void
    {
        if (!$this->access->checkAccess("write", "", $this->parent_gui->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this->parent_gui);
        }
    }

    private function setSubTabsEdit(string $active_tab_id): void
    {

        $url_builder = $this->url_builder
            ->withParameter($this->row_id_token, [$this->getRowIdFromQuery()]);

        $this->tabs->addSubTab(
            'general',
            $this->lng->txt('meta_general'),
            $this->getSingleTypeLinkTarget('edit')
        );

        if ($this->settings->get('custom_icons')) {
            $this->tabs->addSubTab(
                'custom_icons',
                $this->lng->txt('icon_settings'),
                $this->getSingleTypeLinkTarget('editCustomIcons')
            );
        }
        if (count(ilOrgUnitType::getAvailableAdvancedMDRecordIds())) {
            $this->tabs->addSubTab(
                'amd',
                $this->lng->txt('md_advanced'),
                $this->getSingleTypeLinkTarget('editAMD')
            );
        }
        $this->tabs->setSubTabActive($active_tab_id);
    }

    protected function getIconForm(
        ?string $section_title = null,
        ?string $current_identifier = null
    ): StandardForm {
        $handler_gui = new ilOrgUnitTypeUploadHandlerGUI();

        $input = $this->ui_factory->input()->field()->file(
            $handler_gui,
            $this->lng->txt('icon'),
            $this->lng->txt('file_allowed_suffixes') . ' .svg'
        );

        if($current_identifier) {
            $input = $input->withValue([$current_identifier]);
        }

        $section = $this->ui_factory->input()->field()->section(
            ['iconfile' => $input],
            $section_title . $this->lng->txt('orgu_type_custom_icon'),
            $this->lng->txt('orgu_type_custom_icon_info')
        );

        $form_action = $this->getSingleTypeLinkTarget('updateCustomIcons');
        $form = $this->ui_factory->input()->container()->form()->standard(
            $form_action,
            [$section]
        );

        $form = $form->withAdditionalTransformation(
            $this->refinery->custom()->transformation(
                function ($values) {
                    return array_shift($values)['iconfile'];
                }
            )
        );
        return $form;
    }

    private function editCustomIcons(): void
    {
        $type = $this->getCurrentOrgUnitType();
        $form = $this->getIconForm(
            $type->getTitle() . ': ',
            $type->getIconIdentifier()
        );
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    private function updateCustomIcons(): void
    {
        $type = $this->getCurrentOrgUnitType();
        $form = $this->getIconForm(
            $type->getTitle() . ': ',
            $type->getIconIdentifier()
        )
        ->withRequest($this->request);

        $data = $form->getData();

        if(!is_null($data)) {
            $new_icon_id = current($data) ? current($data) : '';
            $identifier = $type->getIconIdentifier();
            if($identifier && $new_icon_id == '') {
                $type->removeIconFromIrss($identifier);
            }
            $type = $type->withIconIdentifier($new_icon_id);
            $type->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setContent($this->ui_renderer->render($form));
        }
    }

    /**
     * @return ilAdvancedMDRecord[]
     */
    protected function getAvailableAMDRecords(): array
    {
        return ilOrgUnitType::getAvailableAdvancedMDRecords();
    }

    protected function getCurrentOrgUnitType(): ilOrgUnitType
    {
        $type_id = $this->getRowIdFromQuery();
        return new ilOrgUnitType($type_id);
    }

    protected function getRowIdFromQuery(): int
    {
        if($this->query->has($this->row_id_token->getName())) {
            return $this->query->retrieve(
                $this->row_id_token->getName(),
                $this->refinery->custom()->transformation(fn($v) => (int)array_shift($v))
            );
        }
        return 0;
    }

    public function getSingleTypeLinkTarget(string $action, ?int $type_id = null): string
    {
        $target_id = $type_id ? [$type_id] : [$this->getRowIdFromQuery()];
        return $this->url_builder
            ->withParameter($this->row_id_token, $target_id)
            ->withParameter($this->action_token, $action)
            ->buildURI()->__toString();
    }

    protected function getAmdForm(
        string $action,
        array $available_records,
        ilOrgUnitType $type
    ): StandardForm {
        $options = [];
        foreach ($available_records as $record) {
            $options[$record->getRecordId()] = $record->getTitle();
        }
        $selected_ids = $type->getAssignedAdvancedMDRecordIds();

        $trafo = $this->refinery->custom()->transformation(
            fn($v) => is_array($v) ? array_shift($v) : []
        );

        $field = $this->ui_factory->input()->field()->multiselect(
            $this->lng->txt('orgu_type_available_amd_sets'),
            $options
        )
        ->withValue($selected_ids);

        $section = $this->ui_factory->input()->field()->section(
            [$field],
            $this->lng->txt('orgu_type_assign_amd_sets')
        )
        ->withAdditionalTransformation($trafo);

        $store = $this->refinery->custom()->transformation(
            function (?array $record_ids) use ($type, $selected_ids) {
                $record_ids = $record_ids ?? [];
                $record_ids_removed = array_diff($selected_ids, $record_ids);
                $record_ids_added = array_diff($record_ids, $selected_ids);
                foreach ($record_ids_added as $record_id) {
                    $type->assignAdvancedMDRecord((int)$record_id);
                }
                foreach ($record_ids_removed as $record_id) {
                    $type->deassignAdvancedMdRecord((int)$record_id);
                }
                return true;
            }
        );

        return $this->ui_factory->input()->container()->form()->standard($action, [$section])
           ->withAdditionalTransformation($trafo)
           ->withAdditionalTransformation($store);
    }

    private function editAMD(): void
    {
        $form = $this->getAmdForm(
            $this->getSingleTypeLinkTarget('updateAMD'),
            $this->getAvailableAMDRecords(),
            $this->getCurrentOrgUnitType()
        );
        $this->tpl->setContent(
            $this->ui_renderer->render($form)
        );
    }

    private function updateAMD(): void
    {
        $form = $this->getAmdForm(
            $this->getSingleTypeLinkTarget('updateAMD'),
            $this->getAvailableAMDRecords(),
            $this->getCurrentOrgUnitType()
        )
        ->withRequest($this->request);

        if($form->getData()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('error'), true);
            $this->tpl->setContent($this->ui_renderer->render($form));
        }
    }

    /**
     * Display all types in a table with actions to edit/delete
     */
    private function listTypes(): void
    {
        $url = $this->ctrl->getLinkTarget($this, 'add');
        $link = $this->link_factory->standard(
            $this->lng->txt('orgu_type_add'),
            $url
        );
        $this->toolbar->addComponent($link);

        $table = $this->getTable()
            ->withRequest($this->request);
        $this->tpl->setContent(
            $this->ui_renderer->render($table)
        );
    }

    protected function getTable(): Table\Data
    {
        $columns = [
            'title' => $this->ui_factory->table()->column()->text($this->lng->txt("title")),
            'description' => $this->ui_factory->table()->column()->text($this->lng->txt("description")),
            'default_language' => $this->ui_factory->table()->column()->status($this->lng->txt("default_language")),
            'icon' => $this->ui_factory->table()->column()->text($this->lng->txt("icon"))
                ->withIsSortable(false),
        ];

        $actions = [
            'edit' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $this->url_builder->withParameter($this->action_token, "edit"),
                $this->row_id_token
            ),
            'delete' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('delete'),
                $this->url_builder->withParameter($this->action_token, "delete"),
                $this->row_id_token
            ),
        ];

        return $this->ui_factory->table()
            ->data('', $columns, $this->getTableDataRetrieval())
            ->withId('orgu_types')
            ->withActions($actions);
    }

    protected function getTableDataRetrieval(): Table\DataRetrieval
    {
        return new class (
            \ilOrgUnitType::getAllTypes(),
            $this->ui_factory,
            $this->ui_renderer
        ) implements Table\DataRetrieval {
            public function __construct(
                protected array $data,
                protected UIFactory $ui_factory,
                protected UIRenderer $ui_renderer
            ) {
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return count($this->data);
            }

            public function getRows(
                Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Range $range,
                Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                $records = array_map(
                    fn($type) => [
                        'id' => $type->getId(),
                        'title' => $type->getTitle(),
                        'description' => $type->getDescription(),
                        'default_language' => $type->getDefaultLang(),
                        'icon' => $type->getIconIdentifier() ?
                            $this->renderIcon($type->getIconSrc()) : '',
                    ],
                    $this->data
                );
                if ($order) {
                    list($order_field, $order_direction) = $order->join([], fn($ret, $key, $value) => [$key, $value]);
                    usort($records, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
                    if ($order_direction === 'DESC') {
                        $records = array_reverse($records);
                    }
                }
                if ($range) {
                    $records = array_slice($records, $range->getStart(), $range->getLength());
                }
                foreach ($records as $record) {
                    $row_id = (string)$record['id'];
                    yield $row_builder->buildDataRow($row_id, $record);
                }
            }

            protected function renderIcon(string $src): string
            {
                return $this->ui_renderer->render(
                    $this->ui_factory->symbol()->icon()->custom($src, '')
                );
            }
        };
    }

    protected function getEditForm(ilOrgUnitType $type): StandardForm
    {
        $title = $this->lng->txt('orgu_type_add');
        $action = $this->getSingleTypeLinkTarget('update');
        if($type->getId()) {
            $title = $this->lng->txt('orgu_type_edit');
        }

        $f = $this->ui_factory->input()->field();
        $sections = [];
        $options = [];
        foreach ($this->lng->getInstalledLanguages() as $lang_code) {
            $options[$lang_code] = $this->lng->txt("meta_l_{$lang_code}");
            $sections[] = $f->section(
                [
                    $f->hidden()->withValue($lang_code),
                    $f->text($this->lng->txt('title'))
                        ->withValue($type->getTitle($lang_code)),
                    $f->textarea($this->lng->txt('description'))
                        ->withValue($type->getDescription($lang_code) ?? ''),
                ],
                $options[$lang_code]
            );
        }

        array_unshift(
            $sections,
            $f->section(
                [$f->select($this->lng->txt('default_language'), $options)
                    ->withRequired(true)
                    ->withValue($type->getDefaultLang())
                ],
                $title
            )
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn($v) => array_shift($v)
                )
            )
        );

        $form = $this->ui_factory->input()->container()->form()
            ->standard($action, $sections)
            ->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    function ($v) use ($type) { //: ilOrgUnitType
                        try {

                            $type->setDefaultLang(array_shift($v));
                            foreach ($v as $lang_entry) {
                                list($lang_code, $title, $description) = $lang_entry;
                                $type->setTitle($title, $lang_code);
                                $type->setDescription($description, $lang_code);
                            }
                            return $type;
                        } catch(ilOrgUnitTypePluginException $e) {
                            return $e->getMessage();
                        }
                    }
                )
            );

        return $form;
    }

    /**
     * Display form to edit an existing OrgUnit type
     */
    private function edit(): void
    {
        $type = $this->getCurrentOrgUnitType();
        $form = $this->getEditForm($type);
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    /**
     * Update (save) type
     */
    private function update(): void
    {
        $form = $this->getEditForm($this->getCurrentOrgUnitType())
            ->withRequest($this->request);

        $type = $form->getData();
        if($type && $type instanceof ilOrgUnitType) {
            $type->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this);
        } else {
            $this->tpl->setOnScreenMessage('failure', $type);
            $this->tpl->setContent($this->ui_renderer->render($form));
        }
    }

    /**
     * Delete a type
     */
    private function delete(): void
    {
        $type = $this->getCurrentOrgUnitType();
        try {
            $type->delete();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('orgu_type_msg_deleted'), true);
            $this->ctrl->redirect($this);
        } catch (ilException $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            $this->ctrl->redirect($this);
        }
    }
}

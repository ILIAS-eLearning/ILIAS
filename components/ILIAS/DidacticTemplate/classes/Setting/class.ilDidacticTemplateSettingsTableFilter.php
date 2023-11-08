<?php

declare(strict_types=1);

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class ilDidacticTemplateSettingsTableFilter
{
    protected const FILTER_ID = 'otpl_templates_table';

    protected const FILTER_NAME_ICON = 'icon';
    protected const FILTER_NAME_TITLE = 'title';
    protected const FILTER_NAME_TYPE = 'type';
    protected const FILTER_NAME_SCOPE = 'scope';
    protected const FILTER_NAME_ACTIVE = 'active';

    protected const FILTER_ON = 1;
    protected const FILTER_OFF = 2;
    protected const FILTER_GLOBAL = 1;
    protected const FILTER_LOCAL = 2;

    private array $input_activation_config = [
        self::FILTER_NAME_ICON => false,
        self::FILTER_NAME_TITLE => true,
        self::FILTER_NAME_TYPE => true,
        self::FILTER_NAME_SCOPE => false,
        self::FILTER_NAME_ACTIVE => true
    ];

    private ilLanguage $lng;
    private Factory $ui_factory;
    private Renderer $ui_renderer;
    private ilUIService $ui_service;
    private string $target_url;
    private ?ILIAS\UI\Component\Input\Container\Filter\Filter $filter = null;

    /**
     * @var array
     */
    private array $filter_values = [];

    public function __construct(string $target_url)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_service = $DIC->uiService();

        $this->target_url = $target_url;
    }

    /**
     * Init Filter
     */
    public function init(): void
    {
        $inputs[self::FILTER_NAME_ICON] = $this->ui_factory->input()->field()->select(
            $this->lng->txt('icon'),
            [
                self::FILTER_ON => $this->lng->txt('didactic_filter_with_icon'),
                self::FILTER_OFF => $this->lng->txt('didactic_filter_without_icon')
            ]
        );

        $inputs[self::FILTER_NAME_TITLE] = $this->ui_factory->input()->field()->text(
            $this->lng->txt('title')
        );

        $options = [];
        foreach (ilDidacticTemplateSettings::lookupAssignedObjectTypes() as $type) {
            $options[$type] = $this->lng->txt('objs_' . $type);
        }
        asort($options);
        $inputs[self::FILTER_NAME_TYPE] = $this->ui_factory->input()->field()->select(
            $this->lng->txt('type'),
            $options
        );

        $inputs[self::FILTER_NAME_SCOPE] = $this->ui_factory->input()->field()->select(
            $this->lng->txt('didactic_scope'),
            [
                self::FILTER_GLOBAL => $this->lng->txt('didactic_global'),
                self::FILTER_LOCAL => $this->lng->txt('didactic_local')
            ]
        );

        $inputs[self::FILTER_NAME_ACTIVE] = $this->ui_factory->input()->field()->select(
            $this->lng->txt('status'),
            [
                self::FILTER_ON => $this->lng->txt('active'),
                self::FILTER_OFF => $this->lng->txt('inactive')
            ]
        );

        $this->filter = $this->ui_service->filter()->standard(
            self::FILTER_ID,
            $this->target_url,
            $inputs,
            $this->input_activation_config,
            true,
            true
        );
    }

    public function render(): string
    {
        return $this->ui_renderer->render(
            [
                $this->filter
            ]
        );
    }

    /**
     * @param ilDidacticTemplateSetting[] $settings
     * @return ilDidacticTemplateSetting[]
     */
    public function filter(array $settings): array
    {
        $this->loadFilterValues();

        $filtered = [];
        foreach ($settings as $setting) {
            if ($this->isFiltered($setting)) {
                continue;
            }
            $filtered[] = $setting;
        }

        return $filtered;
    }

    protected function isFiltered(ilDidacticTemplateSetting $setting): bool
    {
        if (!$this->filter->isActivated()) {
            return false;
        }

        $value = (string) $this->getFilterValue(self::FILTER_NAME_ICON);
        if ($value) {
            if ($value === (string) self::FILTER_ON && $setting->getIconHandler()->getAbsolutePath() === '') {
                return true;
            }
            if ($value === (string) self::FILTER_OFF && $setting->getIconHandler()->getAbsolutePath() !== '') {
                return true;
            }
        }

        $value = (string) $this->getFilterValue(self::FILTER_NAME_TITLE);
        if ($value !== '') {
            $title_string = ($setting->getPresentationTitle() . ' ' . $setting->getPresentationDescription());
            $title_string .= (' ' . $setting->getInfo());
            if (ilStr::strIPos($title_string, $value) === false) {
                return true;
            }
        }

        $value = (string) $this->getFilterValue(self::FILTER_NAME_TYPE);
        if ($value !== '') {
            $assigned = $setting->getAssignments();
            if (!in_array($value, $assigned)) {
                return true;
            }
        }

        $value = (string) $this->getFilterValue(self::FILTER_NAME_SCOPE);
        if ($value) {
            $is_local = (bool) count($setting->getEffectiveFrom());

            print_r($setting->getEffectiveFrom());
            if ($value === (string) self::FILTER_GLOBAL && $is_local) {
                return true;
            }
            if ($value === (string) self::FILTER_LOCAL && !$is_local) {
                return true;
            }
        }

        $value = (string) $this->getFilterValue(self::FILTER_NAME_ACTIVE);
        if ($value) {
            if ($value === (string) self::FILTER_ON && !$setting->isEnabled()) {
                return true;
            }
            if ($value === (string) self::FILTER_OFF && $setting->isEnabled()) {
                return true;
            }
        }

        return false;
    }

    protected function getFilterValue(string $name)
    {
        return $this->filter_values[$name] ?? null;
    }

    protected function loadFilterValues(): void
    {
        foreach ($this->filter->getInputs() as $name => $input) {
            $this->filter_values[$name] = $input->getValue();
        }
    }
}

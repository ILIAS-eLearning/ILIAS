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

namespace ILIAS\Repository\Form;

use ILIAS\Object\ilObjectDIC;
use ILIAS\DI\Container;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectPropertyTileImage;

trait StdObjProperties
{
    protected \ilObjectPropertiesAgregator $object_prop;

    protected function initStdObjProperties(Container $DIC)
    {
        $this->object_prop = ilObjectDIC::dic()['object_properties_agregator'];
    }

    public function addStdTitleAndDescription(
        int $obj_id,
        string $type
    ): FormAdapterGUI {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $inputs = $obj_prop
            ->getPropertyTitleAndDescription()
            ->toForm($this->lng, $this->ui->factory()->input()->field(), $this->refinery)->getInputs();
        $this->addField("title", $inputs[0]);
        $this->addField("description", $inputs[1]);
        return $this;
    }

    public function addStdTitle(
        int $obj_id,
        string $type
    ): FormAdapterGUI {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $inputs = $obj_prop
            ->getPropertyTitleAndDescription()
            ->toForm($this->lng, $this->ui->factory()->input()->field(), $this->refinery)->getInputs();
        $this->addField("title", $inputs[0]);
        return $this;
    }

    public function saveStdTitleAndDescription(
        int $obj_id,
        string $type
    ): void {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $obj_prop->storePropertyTitleAndDescription(
            new \ilObjectPropertyTitleAndDescription(
                $this->getData("title"),
                $this->getData("description")
            )
        );
    }

    public function saveStdTitle(
        int $obj_id,
        string $type
    ): void {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $obj_prop->storePropertyTitleAndDescription(
            new \ilObjectPropertyTitleAndDescription(
                $this->getData("title"),
                ""
            )
        );
    }

    public function addStdTile(
        int $obj_id,
        string $type
    ): FormAdapterGUI {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $input = $obj_prop->getPropertyTileImage()
                          ->toForm($this->lng, $this->ui->factory()->input()->field(), $this->refinery);
        $this->addField("tile", $input, true);
        return $this;
    }

    public function saveStdTile(
        int $obj_id,
        string $type
    ): void {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $obj_prop->storePropertyTileImage($this->getData("tile"));
    }

    public function addOnline(
        int $obj_id,
        string $type
    ): FormAdapterGUI {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $input = $obj_prop->getPropertyIsOnline()
                          ->toForm($this->lng, $this->ui->factory()->input()->field(), $this->refinery);
        $this->addField("is_online", $input, true);
        return $this;
    }

    public function saveOnline(
        int $obj_id,
        string $type
    ): void {
        $obj_prop = $this->object_prop->getFor($obj_id, $type);
        $obj_prop->storePropertyIsOnline($this->getData("is_online"));
    }

    public function addStdAvailability(int $ref_id, $visibility_info = ""): self
    {
        $lng = $this->lng;
        $activation = \ilObjectActivation::getItem($ref_id);
        $start = $end = $visibility = null;
        if (($activation["timing_type"] ?? null) === \ilObjectActivation::TIMINGS_ACTIVATION) {
            $start = (int) $activation["timing_start"];
            $end = (int) $activation["timing_end"];
            $visibility = (bool) $activation["visible"];
        }
        $enabled = ($end > 0) || ($start > 0);
        $form = $this
            ->optional(
                "limited",
                $lng->txt("rep_time_based_availability"),
                "",
                $enabled
            )
            ->dateTimeDuration(
                "availability",
                $this->lng->txt("rep_time_period"),
                "",
                new \ilDateTime($start, IL_CAL_UNIX),
                new \ilDateTime($end, IL_CAL_UNIX)
            )
            ->checkbox(
                "visibility",
                $this->lng->txt("rep_activation_limited_visibility"),
                $visibility_info,
                $visibility
            )
            ->end();
        return $form;
    }

    public function saveStdAvailability(int $ref_id): void
    {
        $item = new \ilObjectActivation();
        if (!$this->getData("limited")) {
            $item->setTimingType(\ilObjectActivation::TIMINGS_DEACTIVATED);
        } else {
            $avail = $this->getData("availability");
            $from = $to = null;
            if (!is_null($avail) && !is_null($avail[0])) {
                $from = $avail[0]->getUnixTime();
            }
            if (!is_null($avail) && !is_null($avail[1])) {
                $to = $avail[1]->getUnixTime();
            }
            if ($from > 0 || $to > 0) {
                $item->setTimingType(\ilObjectActivation::TIMINGS_ACTIVATION);
                $item->setTimingStart($from);
                $item->setTimingEnd($to);
                $item->toggleVisible($this->getData("visibility"));
            } else {
                $item->setTimingType(\ilObjectActivation::TIMINGS_DEACTIVATED);
            }
        }
        $item->update($ref_id);
    }


    public function addAdditionalFeatures(
        int $obj_id,
        array $services
    ): self {
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule("obj");

        $form = $this->section("add", $lng->txt("obj_features"));

        // (local) custom metadata
        if (in_array(\ilObjectServiceSettingsGUI::CUSTOM_METADATA, $services)) {
            $form = $this->checkbox(
                \ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                $lng->txt('obj_tool_setting_custom_metadata'),
                $lng->txt('obj_tool_setting_custom_metadata_info'),
                (bool) \ilContainer::_lookupContainerSetting(
                    $obj_id,
                    \ilObjectServiceSettingsGUI::CUSTOM_METADATA
                )
            );
        }

        // taxonomies
        if (in_array(\ilObjectServiceSettingsGUI::TAXONOMIES, $services)) {
            $form = $this->checkbox(
                \ilObjectServiceSettingsGUI::TAXONOMIES,
                $lng->txt('obj_tool_setting_taxonomies'),
                "",
                (bool) \ilContainer::_lookupContainerSetting(
                    $obj_id,
                    \ilObjectServiceSettingsGUI::TAXONOMIES
                )
            );
        }


        return $form;
    }

    public function saveAdditionalFeatures(
        int $obj_id,
        array $services
    ): void {
        // (local) custom metadata
        $key = \ilObjectServiceSettingsGUI::CUSTOM_METADATA;
        if (in_array($key, $services)) {
            \ilContainer::_writeContainerSetting($obj_id, $key, (string) $this->getData($key));
        }
        // taxonomies
        $key = \ilObjectServiceSettingsGUI::TAXONOMIES;
        if (in_array($key, $services)) {
            \ilContainer::_writeContainerSetting($obj_id, $key, (string) $this->getData($key));
        }
    }

    public function addDidacticTemplates(
        string $type,
        int $ref_id,
        bool $creation_mode,
        array $additional_template_options = []
    ): self {
        list($existing_exclusive, $options) = $this->buildDidacticTemplateOptions(
            $type,
            $ref_id,
            $additional_template_options
        );

        if (sizeof($options) < 2) {
            return $this;
        }

        // workaround for containers in edit mode
        if (!$creation_mode) {
            $current_value = 'dtpl_' . \ilDidacticTemplateObjSettings::lookupTemplateId($ref_id);

            if (!in_array(
                $current_value,
                array_keys($options)
            ) || ($existing_exclusive && $current_value == "dtpl_0")) {
                //add or rename actual value to not available
                $options[$current_value] = [$this->lng->txt('not_available')];
            }
        } else {
            if ($existing_exclusive) {
                //if an exclusive template exists use the second template as default value - Whatever the f*** that means!
                $keys = array_keys($options);
                $current_value = $keys[1];
            } else {
                $current_value = 'dtpl_0';
            }
        }

        $form = $this->radio(
            'didactic_type',
            $this->lng->txt('type'),
            "",
            $current_value
        );

        foreach ($options as $id => $data) {
            /*
            if ($existing_exclusive && $id == 'dtpl_0') {
                //set default disabled if an exclusive template exists
                $option->setDisabled(true);
            }*/
            $form = $this->radioOption(
                (string) $id,
                $data[0] ?? '',
                $data[1] ?? ''
            );
        }
        return $form;
    }

    private function buildDidacticTemplateOptions(
        string $type,
        int $ref_id,
        array $additional_template_options = []
    ): array {
        $this->lng->loadLanguageModule('didactic');
        $existing_exclusive = false;
        $options = [];
        $options['dtpl_0'] = [
            $this->lng->txt('didactic_default_type'),
            sprintf(
                $this->lng->txt('didactic_default_type_info'),
                $this->lng->txt('objs_' . $type)
            )
        ];

        $templates = \ilDidacticTemplateSettings::getInstanceByObjectType($type)->getTemplates();
        if ($templates) {
            foreach ($templates as $template) {
                if ($template->isEffective((int) $ref_id)) {
                    $options["dtpl_" . $template->getId()] = [
                        $template->getPresentationTitle(),
                        $template->getPresentationDescription()
                    ];

                    if ($template->isExclusive()) {
                        $existing_exclusive = true;
                    }
                }
            }
        }

        return [$existing_exclusive, array_merge($options, $additional_template_options)];
    }

    public function redirectToDidacticConfirmationIfChanged(
        int $ref_id,
        string $type,
        string $gui_class,
        array $additional_template_options = []
    ): void {
        list($existing_exclusive, $options) = $this->buildDidacticTemplateOptions(
            $type,
            $ref_id,
            $additional_template_options
        );

        if (sizeof($options) < 2) {
            return;
        }

        $current_tpl_id = \ilDidacticTemplateObjSettings::lookupTemplateId(
            $ref_id
        );
        $new_tpl_id = $this->getData('didactic_type');

        if ($new_tpl_id !== $current_tpl_id) {
            // redirect to didactic template confirmation
            $this->ctrl->redirect([$gui_class, \ilDidacticTemplateGUI::class], "confirmTemplateSwitch");
            return;
        }
    }

}

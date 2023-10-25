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

namespace ILIAS\Object\Properties;

use ILIAS\Object\Properties\ObjectReferenceProperties\ObjectReferencePropertiesRepository;
use ILIAS\Object\Properties\ObjectReferenceProperties\ObjectAvailabilityPeriodProperty;
use ILIAS\UI\Component\Button\Standard as StandardButton;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\Implementation\Component\Listing\Unordered as UnorderedListing;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use Psr\Http\Message\ServerRequestInterface;

class MultiObjectPropertiesManipulator
{
    public function __construct(
        private readonly ObjectReferencePropertiesRepository $object_reference_properties_repo,
        private readonly \ilObjectPropertiesAgregator $properties_agregator,
        private readonly \ilLanguage $language,
        private readonly \ilCtrlInterface $ctrl,
        private readonly \ilObjUser $user,
        private readonly UIFactory $ui_factory,
        private readonly \ilGlobalTemplateInterface $tpl,
        private readonly Refinery $refinery
    ) {
    }

    public function getAvailabilityPeriodButton(): StandardButton
    {
        $on_load_code = function ($id) {
            return "document.getElementById('$id')"
                . '.addEventListener("click", '
                . '(e) => {e.preventDefault();'
                . 'e.target.setAttribute("name", "cmd[editAvailabilityPeriod]");'
                . 'e.target.form.requestSubmit(e.target);});';
        };

        return $this->ui_factory->button()->standard(
            $this->language->txt('edit_availability_period'),
            ''
        )->withAdditionalOnLoadCode($on_load_code);
    }

    public function getEditAvailabilityPeriodPropertiesModal(
        array $ref_ids,
        \ilObjectGUI $parent_gui
    ): ?RoundTripModal {
        if ($ref_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->language->txt('no_objects_selected'));
            return null;
        }

        $this->object_reference_properties_repo->preload($ref_ids);
        $this->properties_agregator->preload($ref_ids);

        $items = $this->getItemsForRefIds($ref_ids);

        $post_url = $this->ctrl->getFormAction($parent_gui, 'saveAvailabilityPeriod');

        return $this->buildModal($post_url, $items, $ref_ids, $this->areAllElementsEqual($ref_ids));
    }

    public function saveEditAvailabilityPeriodPropertiesModal(
        \ilObjectGUI $parent_gui,
        ServerRequestInterface $request
    ): ?RoundTripModal {
        $post_url = $this->ctrl->getFormAction($parent_gui, 'saveAvailabilityPeriod');
        $availability_period_modal = $this->buildModal($post_url)
            ->withRequest($request);
        $data = $availability_period_modal->getData();
        if ($data === null) {
            return $availability_period_modal;
        }
        $ref_ids = explode(',', $data['affected_items']);
        $availability_period_property = $data['enable_availability_period'];
        $this->saveAvailabilityPeriodPropertyForObjectRefIds($ref_ids, $availability_period_property);
        return null;
    }

    private function buildModal(
        string $post_url,
        ?array $items = null,
        array $ref_ids = null,
        bool $all_settings_are_equal = false
    ): RoundTripModal {
        $ref_id_for_value = null;

        if ($ref_ids !== null && $all_settings_are_equal) {
            $ref_id_for_value = $ref_ids[0];
        }
        $modal_factory = $this->ui_factory->modal();
        $content = $items;

        $input_fields = $this->buildForm($ref_id_for_value);

        if ($ref_ids !== null) {
            $input_fields['affected_items'] = $input_fields['affected_items']->withValue(implode(',', $ref_ids));
        }

        if ($ref_ids !== null && !$all_settings_are_equal) {
            $content = [
                $this->ui_factory->messageBox()->info($this->language->txt('unequal_items_for_availability_period_message')),
            ] + $items;
        }
        return $modal_factory->roundtrip($this->language->txt('edit_availability_period'), $content, $input_fields, $post_url);
    }

    /**
     *
     * @return array<ILIAS\UI\Component\Input\Input>
     */
    private function buildForm(?int $ref_id_for_values): array
    {
        $data_factory = new DataFactory();
        $date_format = $this->user->getDateFormat();
        $environment = [
            'user_time_zone' => $this->user->getTimeZone(),
            'user_date_format' => $data_factory->dateFormat()->withTime24($date_format)
        ];

        $input_fields = [];
        $input_fields['enable_availability_period'] = $this->object_reference_properties_repo->getFor($ref_id_for_values)->getPropertyAvailabilityPeriod()->toForm(
            $this->language,
            $this->ui_factory->input()->field(),
            $this->refinery,
            $environment
        );
        $input_fields['affected_items'] = $this->ui_factory->input()->field()->hidden();

        return $input_fields;
    }

    private function saveAvailabilityPeriodPropertyForObjectRefIds(
        array $object_reference_ids,
        ObjectAvailabilityPeriodProperty $property
    ): void {
        foreach ($object_reference_ids as $object_reference_id) {
            $this->object_reference_properties_repo->storePropertyAvailabilityPeriod(
                $property->withObjectReferenceId((int) $object_reference_id)
            );
        }
    }

    /**
     * @param array<int> $ref_ids
     */
    private function areAllElementsEqual(array $ref_ids): bool
    {
        $previous_element = $this->object_reference_properties_repo->getFor(array_shift($ref_ids))->getPropertyAvailabilityPeriod();
        foreach ($ref_ids as $ref_id) {
            $current_element = $this->object_reference_properties_repo->getFor($ref_id)->getPropertyAvailabilityPeriod();
            if ($current_element->getAvailabilityPeriodEnabled() === false
                && $previous_element->getAvailabilityPeriodEnabled() === false) {
                return true;
            }

            if ($current_element->getAvailabilityPeriodEnabled() !== $previous_element->getAvailabilityPeriodEnabled()
                || $current_element->getAvailabilityPeriodStart() != $previous_element->getAvailabilityPeriodStart()
                || $current_element->getAvailabilityPeriodEnd() != $previous_element->getAvailabilityPeriodEnd()
                || $current_element->getVisibleWhenDisabled() !== $previous_element->getVisibleWhenDisabled()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array<int> $ref_ids
     * @return array<ILIAS\UI\Component\Item\Shy>
     */
    private function getItemsForRefIds(array $object_reference_ids): array
    {
        $items = [];
        foreach ($object_reference_ids as $object_reference_id) {
            $object_id = $this->object_reference_properties_repo->getFor(
                $object_reference_id
            )->getObjectId();
            $object_properties = $this->properties_agregator->getFor(
                $object_id
            );
            $title = $object_properties->getPropertyTitleAndDescription()->getTitle();
            $icon = $this->ui_factory->symbol()->icon()->custom(
                \ilObject::getIconForReference($object_reference_id, $object_id, ''),
                $title
            );
            $key = 'obj_' . $object_reference_id;
            $items[$key] = $this->ui_factory->item()->shy($title)->withLeadIcon($icon);
        }
        return $items;
    }
}

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
use ILIAS\Object\Properties\ObjectReferenceProperties\ObjectTimeLimitsProperty;
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

    public function getTimeLimitsButton(): StandardButton
    {
        $on_load_code = function ($id) {
            return "document.getElementById('$id')"
                . '.addEventListener("click", '
                . '(e) => {e.preventDefault();'
                . 'e.target.setAttribute("name", "cmd[editTimeLimits]");'
                . 'e.target.form.requestSubmit(e.target);});';
        };

        return $this->ui_factory->button()->standard(
            $this->language->txt('edit_time_limits'),
            ''
        )->withAdditionalOnLoadCode($on_load_code);
    }

    public function getEditTimeLimitsPropertiesModal(
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
        $list = $this->ui_factory->listing()->unordered($items);
        $post_url = $this->ctrl->getFormAction($parent_gui, 'saveTimeLimits');

        return $this->buildModal($post_url, $list, $ref_ids, $this->areAllElementsEqual($ref_ids));
    }

    public function saveEditTimeLimitsPropertiesModal(
        \ilObjectGUI $parent_gui,
        ServerRequestInterface $request
    ): ?RoundTripModal {
        $post_url = $this->ctrl->getFormAction($parent_gui, 'saveTimeLimits');
        $time_based_activation_modal = $this->buildModal($post_url)
            ->withRequest($request);
        $data = $time_based_activation_modal->getData();
        if ($data === null) {
            return $time_based_activation_modal;
        }
        $ref_ids = explode(',', $data['affected_items']);
        $time_based_activation_property = $data['time_based_activation'];
        $this->saveTimeLimitsPropertyForObjectRefIds($ref_ids, $time_based_activation_property);
        return null;
    }

    private function buildModal(
        string $post_url,
        ?UnorderedListing $list = null,
        array $ref_ids = null,
        bool $all_settings_are_equal = false
    ): RoundTripModal {
        $ref_id_for_value = null;

        if ($ref_ids !== null && $all_settings_are_equal) {
            $ref_id_for_value = $ref_ids[0];
        }
        $modal_factory = $this->ui_factory->modal();
        $content = $list;

        $input_fields = $this->buildForm($ref_id_for_value);

        if ($ref_ids !== null) {
            $input_fields['affected_items'] = $input_fields['affected_items']->withValue(implode(',', $ref_ids));
        }

        if ($ref_ids !== null && !$all_settings_are_equal) {
            $content = [
                $this->ui_factory->messageBox()->info($this->language->txt('unequal_items_for_time_limits_message')),
                $list
            ];
        }
        return $modal_factory->roundtrip($this->language->txt('edit_time_limits'), $content, $input_fields, $post_url);
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
        $input_fields['time_based_activation'] = $this->object_reference_properties_repo->getFor($ref_id_for_values)->getPropertyTimeLimits()->toForm(
            $this->language,
            $this->ui_factory->input()->field(),
            $this->refinery,
            $environment
        );
        $input_fields['affected_items'] = $this->ui_factory->input()->field()->hidden();

        return $input_fields;
    }

    private function saveTimeLimitsPropertyForObjectRefIds(
        array $object_reference_ids,
        ObjectTimeLimitsProperty $property
    ): void {
        foreach ($object_reference_ids as $object_reference_id) {
            $this->object_reference_properties_repo->storePropertyTimeLimits(
                $property->withObjectReferenceId((int) $object_reference_id)
            );
        }
    }

    /**
     * @param array<int> $ref_ids
     */
    private function areAllElementsEqual(array $ref_ids): bool
    {
        $previous_element = $this->object_reference_properties_repo->getFor(array_shift($ref_ids))->getPropertyTimeLimits();
        foreach ($ref_ids as $ref_id) {
            $current_element = $this->object_reference_properties_repo->getFor($ref_id)->getPropertyTimeLimits();
            if ($current_element->getTimeLimitsEnabled() === false
                && $previous_element->getTimeLimitsEnabled() === false) {
                return true;
            }

            if ($current_element->getTimeLimitsEnabled() !== $previous_element->getTimeLimitsEnabled()
                || $current_element->getTimeLimitStart() != $previous_element->getTimeLimitStart()
                || $current_element->getTimeLimitEnd() != $previous_element->getTimeLimitEnd()
                || $current_element->getVisibleWhenDisabled() !== $previous_element->getVisibleWhenDisabled()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array<int> $ref_ids
     * @return array<ILIAS\UI\Component\Item\Standard>
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
            $items[$key] = $this->ui_factory->item()->standard($title)->withLeadIcon($icon);
        }
        return $items;
    }
}

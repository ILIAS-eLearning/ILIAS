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

class ilDclTextRecordRepresentation extends ilDclBaseRecordRepresentation
{
    public const LINK_MAX_LENGTH = 40;

    public function getHTML(bool $link = true, array $options = []): string
    {
        $value = $this->getRecordField()->getValue();

        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());

        $field = $this->getField();

        $links = [];
        if ($field->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            $url = $value['link'];
            $value = $value['title'] ?: $this->shortenLink($url);
            if ($link) {
                if (substr($url, 0, 3) === 'www') {
                    $url = 'https://' . $url;
                } elseif (filter_var($url, FILTER_VALIDATE_EMAIL)) {
                    $url = "mailto:" . $url;
                }
                $links['dcl_open_url'] = $url;
            }
        }
        if ($field->hasProperty(ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_TEXT) && $link) {
            if ($this->http->wrapper()->query()->has('tableview_id')) {
                $tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id', $this->refinery->kindlyTo()->int());
            } else {
                $tableview_id = $this->getRecord()->getTable()->getFirstTableViewId($this->user->getId());
            }
            if (
                ilDclDetailedViewDefinition::exists($tableview_id) &&
                ilDclDetailedViewDefinition::_lookupActive($tableview_id, ilDclDetailedViewDefinition::PARENT_TYPE)
            ) {
                $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, 'record_id', $this->getRecord()->getId());
                $links['dcl_open_detail_view'] = $this->ctrl->getLinkTargetByClass(ilDclDetailedViewGUI::class, 'renderRecord');
                $this->ctrl->clearParameterByClass(ilDclDetailedViewGUI::class, 'record_id');
            }
        }

        $value = nl2br((string) $value);

        switch (count($links)) {
            case 0:
                return $value;
            case 1:
                $key = array_keys($links)[0];
                return $this->renderer->render(
                    $this->factory->link()->standard(
                        $value,
                        reset($links)
                    )->withOpenInNewViewport($key === 'dcl_open_url')
                );
            case 2:
            default:
                $ui_links = [];
                foreach ($links as $key => $link) {
                    $ui_links[] = $this->factory->link()->standard(
                        $this->lng->txt($key),
                        $link
                    )->withOpenInNewViewport($key === 'dcl_open_url');
                }
                return $this->renderer->render(
                    $this->factory->dropdown()->standard(
                        $ui_links
                    )->withLabel($value)
                );
        }

    }

    protected function shortenLink(string $value): string
    {
        $value = preg_replace('/^(https?:\/\/)?(www\.)?/', '', $value);
        $half = (int) ((self::LINK_MAX_LENGTH - 4) / 2);
        $value = preg_replace('/^(.{' . ($half + 1) . '})(.{4,})(.{' . $half . '})$/', '\1...\3', $value);

        return $value;
    }

    public function fillFormInput(ilPropertyFormGUI $form): void
    {
        $input_field = $form->getItemByPostVar('field_' . $this->getField()->getId());
        $raw_input = $this->getFormInput();

        $value = is_array($raw_input) ? $raw_input['link'] : $raw_input;
        $value = is_string($value) ? $value : "";
        $field_values = [];
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_URL)) {
            $field_values["field_" . $this->getRecordField()->getField()->getId() . "_title"] = (isset($raw_input['title'])) ? $raw_input['title'] : '';
        }

        $field_values["field_" . $this->getRecordField()->getField()->getId()] = $value;
        $input_field->setValueByArray($field_values);
    }
}

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

use ILIAS\UI\Component\Image\Image;

class ilDclMobRecordRepresentation extends ilDclFileRecordRepresentation
{
    public function getHTML(bool $link = true, array $options = []): string
    {
        $value = $this->getRecordField()->getValue();

        if (is_null($value)) {
            return "";
        }

        $mob = new ilObjMediaObject($value);
        $item = $mob->getMediaItem('Standard');
        $component = match (explode('/', (string) $item?->getFormat())[0] ?? '') {
            'image' => $this->factory->image()->responsive($item->getLocationSrc(), $mob->getTitle()),
            'video' => $this->factory->player()->video($item->getLocationSrc()),
            'audio' => $this->factory->player()->audio($item->getLocationSrc()),
            default => $this->factory->image()->responsive('', $mob->getTitle()),
        };

        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_LINK_DETAIL_PAGE_MOB) && $link) {
            if ($this->http->wrapper()->query()->has('tableview_id')) {
                $tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id', $this->refinery->kindlyTo()->int());
            } else {
                $tableview_id = $this->getRecord()->getTable()->getFirstTableViewId($this->user->getId());
            }
            $page = new ilDclDetailedViewDefinitionGUI($tableview_id);
            if ($page->getPageObject()->isActive()) {
                $this->ctrl->setParameterByClass(ilDclDetailedViewGUI::class, 'record_id', $this->getRecord()->getId());
                $link = $this->ctrl->getLinkTargetByClass(ilDclDetailedViewGUI::class, 'renderRecord');
                $this->ctrl->clearParameterByClass(ilDclDetailedViewGUI::class, 'record_id');
                if ($component instanceof Image) {
                    $component = $component->withAction($link);
                } else {
                    $component = [$component, $this->factory->link()->standard($this->lng->txt('details'), $link)];
                }
            }
        }

        return $this->renderer->render($component);
    }

    public function parseFormInput($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null || !ilObject2::_exists((int) $value) || ilObject2::_lookupType((int) $value) != 'mob') {
            return '';
        }

        return $value;
    }
}

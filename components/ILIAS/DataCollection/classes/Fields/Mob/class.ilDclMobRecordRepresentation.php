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
        $value = $this->record_field->getValue();

        if ($value === null) {
            return '';
        }

        $rid = $this->irss->manage()->find($value);
        if ($rid === null || null === $revision = $this->irss->manage()->getCurrentRevision($rid)) {
            return $this->lng->txt('file_not_found');
        }

        $src = $this->irss->consume()->src($rid)->getSrc();

        $component = match (explode('/', $revision->getInformation()->getMimeType())[0] ?? '') {
            'image' => $this->factory->image()->responsive($src, $revision->getTitle()),
            'video' => $this->factory->player()->video($src),
            'audio' => $this->factory->player()->audio($src),
            default => $this->factory->link()->standard($revision->getTitle(), $src),
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
}

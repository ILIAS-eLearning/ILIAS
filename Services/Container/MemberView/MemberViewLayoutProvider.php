<?php

namespace ILIAS\Container\Screen;

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

use ILIAS\Data\URI;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\MainControls\ModeInfo;
use ilLink;
use ilMemberViewSettings;
use ilObject;

/**
 * Class MemberViewLayoutProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MemberViewLayoutProvider extends AbstractModificationProvider
{
    /**
     * @inheritDoc
     */
    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->repository();
    }

    public static function getMemberViewModeInfo(\ILIAS\DI\Container $dic): ?ModeInfo
    {
        $mv = ilMemberViewSettings::getInstance();
        if (!$mv->isActive()) {
            return null;
        }
        $ref_id = $mv->getCurrentRefId();
        $url = new URI(ilLink::_getLink(
            $ref_id,
            ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
            ['mv' => 0]
        ));

        $modeinfo = $dic->ui()->factory()->mainControls()->modeInfo(
            $dic->language()->txt('mem_view_long'),
            $url
        );

        return $modeinfo;
    }

    /**
     * @inheritDoc
     */
    public function getPageBuilderDecorator(CalledContexts $screen_context_stack): ?PageBuilderModification
    {
        $mv_mode_info = self::getMemberViewModeInfo($this->dic);
        if (is_null($mv_mode_info)) {
            return null;
        }

        return $this->factory->page()
            ->withLowPriority()
            ->withModification(
                static function (PagePartProvider $parts) use ($mv_mode_info): Page {
                    $p = new StandardPageBuilder();
                    $page = $p->build($parts);
                    return $page->withModeInfo($mv_mode_info);
                }
            );
    }
}

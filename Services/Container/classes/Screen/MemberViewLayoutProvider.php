<?php namespace ILIAS\Container\Screen;

use ILIAS\Data\URI;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\Layout\Page\Standard;
use ILIAS\UI\Component\MainControls\ModeInfo;
use ilLink;
use ilMemberViewSettings;
use ilObject;

/**
 * Class MemberViewLayoutProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MemberViewLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{
    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->repository();
    }

    public static function getMemberViewModeInfo(\ILIAS\DI\Container $dic) : ?Modeinfo
    {
        $mv = ilMemberViewSettings::getInstance();
        if (!$mv->isActive()) {
            return null;
        }
        $ref_id = $mv->getCurrentRefId();
        $url = new URI(ilLink::_getLink(
            $ref_id,
            ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
            array('mv' => 0)
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
    public function getPageBuilderDecorator(CalledContexts $screen_context_stack) : ?PageBuilderModification
    {
        $mv_mode_info = self::getMemberViewModeInfo($this->dic);
        if (is_null($mv_mode_info)) {
            return null;
        }

        return $this->factory->page()
            ->withLowPriority()
            ->withModification(
                function (PagePartProvider $parts) use ($mv_mode_info) : Page {
                    $p = new StandardPageBuilder();
                    $page = $p->build($parts);
                    return $page->withModeInfo($mv_mode_info);
                }
            );
    }
}

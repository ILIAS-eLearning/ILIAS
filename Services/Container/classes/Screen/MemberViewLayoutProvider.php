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


    /**
     * @inheritDoc
     */
    public function getPageBuilderDecorator(CalledContexts $screen_context_stack) : ?PageBuilderModification
    {
        if (!$screen_context_stack->current()->hasReferenceId() || $this->dic["lti"]->isActive()) {
            return null;
        }

        $mv = ilMemberViewSettings::getInstance();
        if ($mv->isActive()) {
            $ref_id = $mv->getCurrentRefId();

            return $this->factory->page()->withHighPriority()->withModification(
                function (PagePartProvider $i) use ($ref_id) : Page {
                    $url = new URI(ilLink::_getLink(
                        $ref_id,
                        ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
                        array('mv' => 0)
                    ));

                    $p = new StandardPageBuilder();
                    $page = $p->build($i);

                    /**
                     * @var $page Standard
                     */
                    return $page->withModeInfo($this->dic->ui()->factory()->mainControls()->modeInfo($this->dic->language()->txt('mem_view_long'), $url));
                }
            );
        }

        return null;
    }
}

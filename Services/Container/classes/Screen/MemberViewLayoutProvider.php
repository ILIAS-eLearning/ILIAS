<?php namespace ILIAS\Container\Screen;

use ILIAS\GlobalScreen\Scope\Layout\Factory\LogoModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Image\Image;
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
    public function getLogoModification(CalledContexts $screen_context_stack) : ?LogoModification
    {
        if (!$screen_context_stack->current()->hasReferenceId()) {
            return null;
        }

        $mv = ilMemberViewSettings::getInstance();
        if ($mv->isActive()) {
            return $this->globalScreen()->layout()->factory()->logo()->withModification(function (Image $current) use ($mv) : Image {
                $ref_id = $mv->getCurrentRefId();

                $image = $this->dic->ui()->factory()->image()->responsive("https://www.colourbox.com/preview/5559052-icon-user-red.jpg", "mv");
                if ($ref_id) {
                    $url = ilLink::_getLink(
                        $ref_id,
                        ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
                        array('mv' => 0)
                    );
                    $image = $image->withAction($url);
                }

                return $image;
            })->withHighPriority();
        }

        return null;
    }
}

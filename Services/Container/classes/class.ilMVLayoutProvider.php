<?php

use ILIAS\GlobalScreen\Scope\Layout\Factory\Logo;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\UI\Component\Image\Image;

/**
 * Class ilMVLayoutProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMVLayoutProvider extends AbstractModificationProvider implements ModificationProvider
{

    public function getLogoModifier() : ?Logo
    {
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
            });
        }

        return null;
    }
}

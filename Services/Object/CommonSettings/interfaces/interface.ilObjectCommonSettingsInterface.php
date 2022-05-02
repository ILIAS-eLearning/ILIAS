<?php declare(strict_types=1);

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
 
/**
 * Common settings/properties for objects. Any repository object setting/property that is needed
 * by multiple objects should be managed by this sub service.
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
interface ilObjectCommonSettingsInterface
{
    /**
     * Get form adapter (currently only for legacy form using ilPropertyFormGUI) for adding and saving
     * common settings to and from forms.
     * @todo In the future a method form() should also act on new ui form containers.
     */
    public function legacyForm(ilPropertyFormGUI $form, ilObject $object) : ilObjectCommonSettingFormAdapter;

    /**
     * Tile image sub service. Tile images are used in deck of cards view of repository containers.
     */
    public function tileImage() : ilObjectTileImageFactory;
}

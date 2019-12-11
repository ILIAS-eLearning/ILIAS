<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Common settings/properties for objects. Any repository object setting/property that is needed
 * by multiple objects should be managed by this subservice.
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
interface ilObjectCommonSettingsInterface
{
    /**
     * Get form adapter (currently only for legacy form using ilPropertyFormGUI) for adding and saving
     * common settings to and from forms.
     *
     * @todo In the future a method form() should also act on new ui form containers.
     *
     * @param ilPropertyFormGUI $form
     * @param ilObject $object
     * @return ilObjectCommonSettingFormAdapter
     */
    public function legacyForm(ilPropertyFormGUI $form, ilObject $object) : ilObjectCommonSettingFormAdapter;

    /**
     * Tile image subservice. Tile images are used in deck of cards view of repository containers.
     *
     * @return ilObjectTileImageFactory
     */
    public function tileImage() : ilObjectTileImageFactory;
}

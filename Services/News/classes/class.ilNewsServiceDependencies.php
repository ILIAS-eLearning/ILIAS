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

/**
 * News service dependencies
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsServiceDependencies
{
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected ilNewsObjectAdapterInterface $obj_adapter;
    protected ilObjUser $user;

    public function __construct(
        ilLanguage $lng,
        ilSetting $settings,
        ilObjUser $user,
        ilNewsObjectAdapterInterface $obj_adapter
    ) {
        $this->lng = $lng;
        $this->settings = $settings;
        $this->user = $user;
        $this->obj_adapter = $obj_adapter;
    }

    /**
     * Get object adapter
     */
    public function obj() : ilNewsObjectAdapterInterface
    {
        return $this->obj_adapter;
    }

    public function language() : ilLanguage
    {
        return $this->lng;
    }

    public function settings() : ilSetting
    {
        return $this->settings;
    }

    // Get current user
    public function user() : ilObjUser
    {
        return $this->user;
    }
}

<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Object service
 *
 * @author killing@leifos.de
 * @ingroup ServiceObject
 */
interface ilObjectServiceInterface
{
    /**
     * Get common settings subservice
     *
     * @return ilObjectCommonSettings
     */
    public function commonSettings() : ilObjectCommonSettings;
}

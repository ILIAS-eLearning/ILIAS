<?php

declare(strict_types=1);

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
 * Class ilCmiXapiUserAutocomplete
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiUserAutocomplete extends ilUserAutoComplete
{
    protected int $objId;

    public function __construct(int $objId)
    {
        parent::__construct();
        $this->objId = $objId;
    }

    protected function getFromPart(): string
    {
        global $DIC;

        $fromPart = parent::getFromPart();
        return $fromPart . "
			INNER JOIN (SELECT DISTINCT usr_id, obj_id FROM cmix_users) c
			ON c.obj_id = {$DIC->database()->quote($this->objId, 'integer')}
			AND c.usr_id = ud.usr_id
		";
    }
}

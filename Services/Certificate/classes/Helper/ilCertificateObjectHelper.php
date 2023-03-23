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

declare(strict_types=1);

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateObjectHelper
{
    /**
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public function getInstanceByObjId(int $objectId, bool $stop_on_error = true): ?ilObject
    {
        return ilObjectFactory::getInstanceByObjId($objectId, $stop_on_error);
    }

    public function lookupObjId(int $refId): int
    {
        return ilObject::_lookupObjId($refId);
    }

    public function lookupType(int $objectId): string
    {
        return ilObject::_lookupType($objectId);
    }

    public function lookupTitle(int $objectId): string
    {
        return ilObject::_lookupTitle($objectId);
    }
}

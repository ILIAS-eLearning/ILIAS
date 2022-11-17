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

namespace ILIAS\ResourceStorage\Identification;

/**
 * Class MicrotimeIdentificationGenerator
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class MicrotimeIdentificationGenerator implements IdentificationGenerator
{
    /**
     * @throws \Exception
     */
    public function getUniqueResourceIdentification(): ResourceIdentification
    {
        return new ResourceIdentification((string)microtime(true));
    }
}

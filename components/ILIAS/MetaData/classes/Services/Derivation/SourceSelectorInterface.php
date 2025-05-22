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

namespace ILIAS\MetaData\Services\Derivation;

interface SourceSelectorInterface
{
    /**
     * Choose an ILIAS object as the source. See {@see \ILIAS\MetaData\Services\ServicesInterface::read()}
     * for a description of the parameters.
     */
    public function fromObject(int $obj_id, int $sub_id, string $type): DerivatorInterface;

    /**
     * Choose some basic properties to generate a new LOM set from.
     * The title must be set.
     *
     * @throws \ilMDServicesException if title is empty string
     */
    public function fromBasicProperties(
        string $title,
        string $description = '',
        string $language = ''
    ): DerivatorInterface;
}

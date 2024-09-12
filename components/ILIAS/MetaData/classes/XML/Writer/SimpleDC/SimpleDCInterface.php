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

namespace ILIAS\MetaData\XML\Writer\SimpleDC;

use ILIAS\MetaData\Elements\SetInterface;

interface SimpleDCInterface
{
    /**
     * Generates XML in SimpleDC format from a LOM set, specifically
     * for a repository object.
     *
     * Currently, this does not write information from markers to xml,
     * so calling this with a set from a standard reader will give
     * back empty XML.
     * This should be changed before adding XML to the API.
     */
    public function write(
        SetInterface $set,
        int $object_ref_id
    ): \SimpleXMLElement;
}

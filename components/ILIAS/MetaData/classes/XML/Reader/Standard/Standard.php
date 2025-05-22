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

namespace ILIAS\MetaData\XML\Reader\Standard;

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\XML\Version;
use ILIAS\MetaData\XML\Reader\ReaderInterface;

class Standard implements ReaderInterface
{
    protected ReaderInterface $structurally_coupled;
    protected ReaderInterface $legacy;

    public function __construct(
        ReaderInterface $structurally_coupled,
        ReaderInterface $legacy
    ) {
        $this->structurally_coupled = $structurally_coupled;
        $this->legacy = $legacy;
    }

    public function read(
        \SimpleXMLElement $xml,
        Version $version
    ): SetInterface {
        switch ($version) {
            case Version::V4_1_0:
                return $this->legacy->read($xml, $version);

            case Version::V10_0:
            default:
                return $this->structurally_coupled->read($xml, $version);
        }
    }
}

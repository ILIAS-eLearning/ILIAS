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

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Flavour\Flavour;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class FlavourURLs
{
    private SrcBuilder $src_builder;
    private Flavour $flavour;

    public function __construct(SrcBuilder $src_builder, Flavour $flavour)
    {
        $this->src_builder = $src_builder;
        $this->flavour = $flavour;
    }

    public function getURLs(bool $signed = false): \Generator
    {
        yield from $this->src_builder->getFlavourURLs($this->flavour, $signed);
    }

    public function getURLsAsArray(bool $signed = false): array
    {
        return iterator_to_array($this->getURLs($signed));
    }
}

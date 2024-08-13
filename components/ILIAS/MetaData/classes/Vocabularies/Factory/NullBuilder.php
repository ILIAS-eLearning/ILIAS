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

namespace ILIAS\MetaData\Vocabularies\Factory;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Vocabularies\NullVocabulary;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;

class NullBuilder implements BuilderInterface
{
    public function withCondition(
        string $value,
        PathInterface $path
    ): BuilderInterface {
        return new NullBuilder();
    }

    public function withIsDeactivated(bool $deactivated): BuilderInterface
    {
        return new NullBuilder();
    }

    public function withDisallowsCustomInputs(bool $no_custom_inputs): BuilderInterface
    {
        return new NullBuilder();
    }

    public function get(): VocabularyInterface
    {
        return new NullVocabulary();
    }
}

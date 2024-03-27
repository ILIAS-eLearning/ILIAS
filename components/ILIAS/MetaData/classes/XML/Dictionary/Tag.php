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

namespace ILIAS\MetaData\XML\Dictionary;

use ILIAS\MetaData\Structure\Dictionaries\Tags\Tag as BaseTag;
use ILIAS\MetaData\XML\Version;
use ILIAS\MetaData\XML\SpecialCase;

class Tag extends BaseTag implements TagInterface
{
    protected Version $version;

    /**
     * @var SpecialCase[]
     */
    protected array $specialities = [];

    public function __construct(
        Version $version,
        SpecialCase ...$specialities
    ) {
        $this->version = $version;
        $this->specialities = $specialities;
        parent::__construct();
    }

    public function version(): Version
    {
        return $this->version;
    }

    public function isExportedAsLangString(): bool
    {
        return in_array(
            SpecialCase::LANGSTRING,
            $this->specialities
        );
    }

    public function isTranslatedAsCopyright(): bool
    {
        return in_array(
            SpecialCase::COPYRIGHT,
            $this->specialities
        );
    }

    public function isOmitted(): bool
    {
        return in_array(
            SpecialCase::OMITTED,
            $this->specialities
        );
    }

    public function isExportedAsAttribute(): bool
    {
        return in_array(
            SpecialCase::AS_ATTRIBUTE,
            $this->specialities
        );
    }
}

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

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\String\Transformation\LevenshteinTransformation;
use ILIAS\Refinery\String\Transformation\UTFNormalTransformation;

class UTFNormal
{
    /**
     * Normalization Form C (NFC), also known as Canonical Decomposition followed by Canonical Composition.
     */
    public function formC(): Transformation
    {
        return new UTFNormalTransformation(UTFNormalTransformation::FORM_C);
    }

    /**
     * Normalization Form D (NFD), also known as Canonical Decomposition.
     */
    public function formD(): Transformation
    {
        return new UTFNormalTransformation(UTFNormalTransformation::FORM_D);
    }

    /**
     * Normalization Form KC (NFKC), also known as Compatibility Decomposition followed by Canonical Composition.
     */
    public function formKD(): Transformation
    {
        return new UTFNormalTransformation(UTFNormalTransformation::FORM_KD);
    }

    /**
     * Normalization Form KD (NFKD), also known as Compatibility Decomposition.
     */
    public function formKC(): Transformation
    {
        return new UTFNormalTransformation(UTFNormalTransformation::FORM_KC);
    }
}

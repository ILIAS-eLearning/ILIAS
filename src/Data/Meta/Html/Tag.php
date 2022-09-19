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
 */

declare(strict_types=1);

namespace ILIAS\Data\Meta\Html;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class Tag
{
    /**
     * This method MUST return the valid HTML markup for this tag.
     *
     * It's possible that several meta-tags must be returned, but please comply
     * with MDNs documentation.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta
     */
    abstract public function toHtml(): string;
}

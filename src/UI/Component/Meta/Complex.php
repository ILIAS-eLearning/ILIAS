<?php declare(strict_types=1);

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

namespace ILIAS\UI\Component\Meta;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface Complex extends Standard
{
    public const ATTRIBUTE_NAME = 'name';
    public const ATTRIBUTE_PROPERTY = 'property';
    public const ATTRIBUTE_HTTP_EQUIV = 'http-equiv';
    public const ATTRIBUTE_ITEM_PROP = 'itemprop';

    public function getContent() : string;
}

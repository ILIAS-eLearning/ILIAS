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

namespace ILIAS\Data\Meta\Html\OpenGraph;

use ILIAS\Data\Meta\Html\TagCollection;
use ILIAS\Data\Meta\Html\Tag as HTMLTag;
use ILIAS\Data\Meta\Html\Undefined;
use ILIAS\Data\URI;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory
{
    public function website(
        string $title,
        URI $link,
        ?string $description = null,
        ?URI $image_url = null,
    ): TagCollection {
        return new TagCollection(
            new Text('og:title', $title),
            new Text('og:type', 'website'),
            new Link('og:url', $link),
            (null !== $description) ? new Text('og:description', $description) : new Undefined(),
            (null !== $description) ? new Text('og:description', $description) : new Undefined(),
        );
    }
}

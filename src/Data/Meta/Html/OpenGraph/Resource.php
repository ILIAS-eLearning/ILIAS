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
use ILIAS\Data\URI;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
abstract class Resource extends TagCollection
{
    public function __construct(
        URI $resource_url,
        string $mime_type,
        HTMLTag ...$additional_tags,
    ) {
        parent::__construct(
            new Link($this->getPropertyName(), $resource_url),
            new Text("{$this->getPropertyName()}:type", $mime_type),
            ...$additional_tags
        );
    }

    abstract protected function getPropertyName(): string;
}

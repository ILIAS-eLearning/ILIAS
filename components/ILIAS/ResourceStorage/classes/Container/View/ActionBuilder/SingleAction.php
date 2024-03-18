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

namespace ILIAS\components\ResourceStorage\Container\View\ActionBuilder;

use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class SingleAction extends Action
{
    public function __construct(
        string $label,
        URI $action,
        private bool $async = false,
        private bool $bulk = false,
        private bool $supports_directories = false,
        private array $supported_mime_types = ['*']
    ) {
        parent::__construct($label, $action);
    }

    public function isAsync(): bool
    {
        return $this->async;
    }

    public function isBulk(): bool
    {
        return $this->bulk;
    }

    public function supportsDirectories(): bool
    {
        return $this->supports_directories;
    }

    public function getSupportedMimeTypes(): array
    {
        return $this->supported_mime_types;
    }

}

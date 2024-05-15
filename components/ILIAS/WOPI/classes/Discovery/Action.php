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

namespace ILIAS\components\WOPI\Discovery;

use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Action
{
    public function __construct(
        private int $id,
        private string $name,
        private string $extension,
        private URI $launcher_url,
        private ?string $url_appendix = null,
        private ?string $target_ext = null
    ) {
    }

    public function withId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getLauncherUrl(): URI
    {
        return $this->launcher_url;
    }

    public function getUrlAppendix(): ?string
    {
        return $this->url_appendix;
    }

    public function getTargetExtension(): ?string
    {
        return $this->target_ext;
    }

}

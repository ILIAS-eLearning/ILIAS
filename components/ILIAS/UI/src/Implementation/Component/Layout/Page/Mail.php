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

namespace ILIAS\UI\Implementation\Component\Layout\Page;

use ILIAS\UI\Component\Layout\Page;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\Data\Link;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use InvalidArgumentException;

class Mail implements Page\Mail
{
    use ComponentHelper;
    use JavaScriptBindable;

    public function __construct(
        protected string $stylesheet_path,
        protected string $logo_url,
        protected string $installation_title,
        protected Content $html_content,
        protected Link $footer_url,
    ) {
        if (!is_readable($this->getStyleSheetPath())) {
            throw new InvalidArgumentException("Could not read stylesheet at {$this->getStyleSheetPath()}.");
        }

        if (!str_starts_with($this->getLogoURL(), 'cid:') && !str_starts_with($this->getLogoURL(), 'data:')) {
            throw new InvalidArgumentException('The logo URL must be a cid or data URL.');
        }
    }

    public function getContent(): array
    {
        return [
            $this->html_content,
        ];
    }

    public function getLogoURL(): string
    {
        return $this->logo_url;
    }

    public function getInstallationTitle(): string
    {
        return $this->installation_title;
    }

    public function getStyleSheetPath(): string
    {
        return $this->stylesheet_path;
    }

    public function getFooterURL(): Link
    {
        return $this->footer_url;
    }
}

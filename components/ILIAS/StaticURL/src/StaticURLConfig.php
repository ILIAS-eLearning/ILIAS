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

namespace ILIAS\StaticURL;

use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\StaticURL\Shortlinks\Handler;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 */
class StaticURLConfig implements Configuration
{
    public function get(Config $config): mixed
    {
        return match ($config) {
            Config::BASE_URL => \ILIAS_HTTP_PATH,
            Config::REWRITE_POSSIBLE => \ilRobotSettings::getInstance()?->robotSupportEnabled() ?? false,
            Config::SHORTLINK_NAMESPACE => Handler::SHORTLINK_NAMESPACE,
            Config::STATIC_LINK_ENDPOINT => $this->get(Config::REWRITE_POSSIBLE)
                ? StandardURIBuilder::SHORT
                : StandardURIBuilder::LONG,
            Config::ULTRA_SHORT => false,
            default => null,
        };
    }

}

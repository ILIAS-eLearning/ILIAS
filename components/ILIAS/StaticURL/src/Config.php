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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
enum Config: string
{
    case BASE_URL = 'base_url';
    case REWRITE_POSSIBLE = 'rewrite_possible';
    case STATIC_LINK_ENDPOINT = 'static_link_endpoint';
    case SHORTLINK_NAMESPACE = 'shortlink_alternative';
    case ULTRA_SHORT = 'ultra_short';

}

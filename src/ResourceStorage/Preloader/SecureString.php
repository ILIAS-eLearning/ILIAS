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
 *
 *********************************************************************/

namespace ILIAS\ResourceStorage\Preloader;

/**
 * Trait SecureString
 *
 * @internal
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait SecureString
{
    protected function secure(string $string) : string
    {
        return htmlspecialchars(strip_tags($string), ENT_QUOTES, 'UTF-8');
        ;
    }
}

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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait ilBibliographicSecureString
{
    protected function secure(string $string) : string
    {
        return htmlspecialchars(
            strip_tags(
                preg_replace('#\p{C}+#u', '', $string)
            ),
            ENT_QUOTES,
            'UTF-8',
            false
        );
    }
}

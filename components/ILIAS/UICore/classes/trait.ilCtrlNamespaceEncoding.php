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

/**
 * This absolute monstrosity of an encoding/decoding trait exists because
 * ILIAS strips `\` from request URLs. This prevents ilCtrl from using
 * PSR-4 namespaced command- and base-classes until we either work around
 * this issue or drop these as arguments from the URL – which we could, but
 * decide not to pursue, because we should focus on migrating towards a
 * real routing component (see https://docu.ilias.de/go/wiki/wpage_8780_1357).
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @noinspection AutoloadingIssuesInspection
 */
trait ilCtrlNamespaceEncoding
{
    private static string $psr4_namespace_delimiter = '\\';
    private static string $ilctrl_namespace_delimiter = '.';

    public function encodeNamespaceForUrl(string $psr4_namespace): string
    {
        return str_replace(self::$psr4_namespace_delimiter, self::$ilctrl_namespace_delimiter, $psr4_namespace);
    }

    public function decodeNamespaceFromUrl(string $ilctrl_namespace): string
    {
        return str_replace(self::$ilctrl_namespace_delimiter, self::$psr4_namespace_delimiter, $ilctrl_namespace);
    }
}

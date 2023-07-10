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

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\ResourceStorage\Consumer\InlineSrcBuilder;
use ILIAS\ResourceStorage\Consumer\SrcBuilder;
use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;

/**
 * Class ilWACSrcBuilder
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilWACSrcBuilder extends InlineSrcBuilder implements SrcBuilder
{
    public const WAC_BASE_URL = './Services/WebAccessChecker/wac.php';

    public function getResourceURL(Revision $revision, bool $signed = true): string
    {
        $access_key = $revision->maybeGetToken()->getAccessKey();

        return $this->signURL($access_key, $signed);
    }


    public function getFlavourURLs(Flavour $flavour, bool $signed = true): \Generator
    {
        foreach ($flavour->getAccessTokens() as $index => $token) {
            if ($token->hasInMemoryStream()) {
                yield from parent::getFlavourURLs($flavour, $signed);
            } else {
                $access_key = $token->getAccessKey();
                yield $this->signURL($access_key, $signed);
            }
        }
    }

    protected function signURL(string $access_key, bool $sign): string
    {
        $url = "./data/" . CLIENT_NAME . "/sec/rs/" . $access_key;
        if ($sign === false) {
            return $url;
        }
        return ilWACSignedPath::signFile($url);
    }
}

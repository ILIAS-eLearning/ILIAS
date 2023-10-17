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
use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\FileDelivery\Services;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilSecureTokenSrcBuilder implements SrcBuilder
{
    public function __construct(
        private Services $file_delivery,
    ) {
    }

    public function getRevisionURL(Revision $revision, bool $signed = true): string
    {
        // get stream from revision
        $stream = $revision->maybeStreamResolver()?->getStream();

        return (string) $this->file_delivery->buildTokenURL(
            $stream,
            $revision->getTitle(),
            Disposition::INLINE,
            $GLOBALS['ilUser']->getId() ?? 0,
            1
        );
    }

    public function getFlavourURLs(Flavour $flavour, bool $signed = true): \Generator
    {
        foreach ($flavour->getStreamResolvers() as $stream_resolver) {
            yield (string) $this->file_delivery->buildTokenURL(
                $stream_resolver->getStream(),
                '',
                Disposition::INLINE,
                $GLOBALS['ilUser']->getId() ?? 0,
                1
            );
        }
    }

}

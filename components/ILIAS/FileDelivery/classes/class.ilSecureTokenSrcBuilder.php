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
use ILIAS\ResourceStorage\Consumer\InlineSrcBuilder;
use ILIAS\ResourceStorage\Consumer\SrcBuilder;
use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\FileDelivery\Services;
use ILIAS\ResourceStorage\Repositories;
use ILIAS\ResourceStorage\Manager\Manager;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilSecureTokenSrcBuilder implements SrcBuilder
{
    private InlineSrcBuilder $inline;

    public function __construct(
        private Services $file_delivery
    ) {
        $this->inline = new InlineSrcBuilder($file_delivery);
    }

    public function getRevisionURL(
        Revision $revision,
        bool $signed = true,
        float $valid_for_at_least_minutes = 60.0,
        ?string $filename = null
    ): string {
        // get stream from revision
        $stream = $revision->maybeStreamResolver()?->getStream();

        return (string) $this->file_delivery->buildTokenURL(
            $stream,
            $filename ?? $revision->getTitle(),
            Disposition::INLINE,
            $GLOBALS['ilUser']->getId() ?? 0,
            (int) (ceil($valid_for_at_least_minutes / 60))
        );
    }

    public function getFlavourURLs(Flavour $flavour, bool $signed = true): \Generator
    {
        try {
            global $DIC;

            $filename = $DIC
                ->resourceStorage()
                ->manage()
                ->getResource($flavour->getResourceId())
                ->getSpecificRevision($flavour->getRevision())
                ?->getInformation()
                ->getTitle() ?? '';

            $frindly_name = preg_replace(
                '/[^a-zA-Z0-9-_]/',
                '',
                strtolower($flavour->getDefinition()->getInternalName())
            );
        } catch (Throwable) {
            $filename = '';
            $frindly_name = 'fl';
        }


        foreach ($flavour->getStreamResolvers() as $i => $stream_resolver) {
            $stream = $stream_resolver->getStream();
            if ($stream_resolver->isInMemory() || $stream->getMetadata()['uri'] === 'php://memory') {
                // must deliver as data uri
                yield $this->inline->buildDataURLFromStream($stream);
            } else {

                yield (string) $this->file_delivery->buildTokenURL(
                    $stream,
                    $frindly_name . '_' . $i . '_' . $filename,
                    Disposition::INLINE,
                    $GLOBALS['ilUser']->getId() ?? 0,
                    1
                );
            }
        }
    }

}

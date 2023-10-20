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

namespace ILIAS\FileDelivery\Delivery\ResponseBuilder;

use Psr\Http\Message\ResponseInterface;
use ILIAS\FileDelivery\Token\Data\Stream;
use ILIAS\Filesystem\Stream\FileStream;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class XAccelResponseBuilder implements ResponseBuilder
{
    private const DATA = 'data';
    private const SECURED_DATA = 'secured-data';
    private const X_ACCEL_REDIRECT_HEADER = 'X-Accel-Redirect';

    public function getName(): string
    {
        return 'x-accel';
    }

    public function buildForStream(
        ResponseInterface $response,
        FileStream $stream,
    ): ResponseInterface {
        $path_to_file = $stream->getStream()->getMetadata('uri');
        if (str_starts_with($path_to_file, './' . self::DATA . '/')) {
            $path_to_file = str_replace(
                './' . self::DATA . '/',
                '/' . self::SECURED_DATA
                . '/',
                $path_to_file
            );
        }

        return $response->withHeader(
            self::X_ACCEL_REDIRECT_HEADER,
            $path_to_file
        );
    }

    public function supportStreaming(): bool
    {
        return true;
    }

    public function supportFileDeletion(): bool
    {
        return false;
    }

    public function supportsInlineDelivery(): bool
    {
        return true;
    }

    public function supportsAttachmentDelivery(): bool
    {
        return true;
    }
}

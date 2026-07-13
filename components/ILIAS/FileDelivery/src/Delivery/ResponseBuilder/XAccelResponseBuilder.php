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
use ILIAS\Filesystem\Stream\FileStream;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class XAccelResponseBuilder implements ResponseBuilder
{
    /**
     * @var string
     */
    private const DATA = 'data';
    /**
     * @var string
     */
    private const SECURED_DATA = 'secured-data';
    /**
     * @var string
     */
    private const SECURED_EXT_DATA = 'secured-ext-data';
    /**
     * @var string
     */
    private const X_ACCEL_REDIRECT_HEADER = 'X-Accel-Redirect';

    public function __construct(private string $external_data_dir)
    {
        $this->external_data_dir = rtrim($this->external_data_dir, '/') . '/';
    }

    public function getName(): string
    {
        return 'x-accel';
    }

    public function buildForStream(
        ServerRequestInterface $request,
        ResponseInterface $response,
        FileStream $stream,
    ): ResponseInterface {
        $path_to_file = $stream->getMetadata('uri');
        if (str_starts_with((string) $path_to_file, './' . self::DATA . '/')) {
            $path_to_file = str_replace(
                './' . self::DATA . '/',
                '/' . self::SECURED_DATA
                . '/',
                $path_to_file
            );
        } elseif (str_starts_with((string) $path_to_file, $this->external_data_dir)) {
            $path_to_file = str_replace(
                $this->external_data_dir,
                '/' . self::SECURED_EXT_DATA . '/',
                $path_to_file
            );
        }

        return $response->withHeader(
            self::X_ACCEL_REDIRECT_HEADER,
            $path_to_file
        );
    }

    public function supportPartial(): bool
    {
        return true;
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

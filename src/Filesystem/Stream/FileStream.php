<?php

namespace ILIAS\Filesystem\Stream;

use Psr\Http\Message\StreamInterface;

/**
 * Interface FileStream
 *
 * The base interface for all filesystem streams.
 * This interface looses the coupling of the filesystem api consumer to the PSR-7 stream interface but ensures compatibility with the
 * streams of the http service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @public
 */
interface FileStream extends StreamInterface
{
}

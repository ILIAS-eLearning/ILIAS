<?php

declare(strict_types=1);

use Sabre\HTTP;

include '../bootstrap.php';

class DummyStream
{
    private $position;

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $this->position = 0;

        return true;
    }

    public function stream_read(int $count): string
    {
        $this->position += $count;

        return random_bytes($count);
    }

    public function stream_tell(): int
    {
        return $this->position;
    }

    public function stream_eof(): bool
    {
        return $this->position > 25 * 1024 * 1024;
    }

    public function stream_close(): void
    {
        file_put_contents(sys_get_temp_dir().'/dummy_stream_read_counter', $this->position);
    }
}

/*
 * The DummyStream wrapper has two functions:
 * - Provide dummy data.
 * - Count how many bytes have been read.
 */
stream_wrapper_register('dummy', DummyStream::class);

/*
 * Overwrite default connection handling.
 * The default behaviour is however for your script to be aborted when the remote client disconnects.
 *
 * Nextcloud/ownCloud set ignore_user_abort(true) on purpose to work around
 * some edge cases where the default behavior would end a script too early.
 *
 * https://github.com/owncloud/core/issues/22370
 * https://github.com/owncloud/core/pull/26775
 */
ignore_user_abort(true);

$body = fopen('dummy://hello', 'r');

$response = new HTTP\Response();
$response->setStatus(200);
$response->addHeader('Content-Length', 25 * 1024 * 1024);
$response->setBody($body);

HTTP\Sapi::sendResponse($response);

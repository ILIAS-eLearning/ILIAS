<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Stream;

use ILIAS\Filesystem\Util\PHPStreamFunctions;

/**
 * Class Stream
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @since 5.3
 * @version 1.0.0
 *
 * @internal
 */
class Stream implements FileStream
{
    const MASK_ACCESS_READ = 01;
    const MASK_ACCESS_WRITE = 02;
    const MASK_ACCESS_READ_WRITE = 03;

    private static $accessMap = [
        'r' => self::MASK_ACCESS_READ,
        'w+' => self::MASK_ACCESS_READ_WRITE,
        'r+' => self::MASK_ACCESS_READ_WRITE,
        'x+' => self::MASK_ACCESS_READ_WRITE,
        'c+' => self::MASK_ACCESS_READ_WRITE,
        'rb' => self::MASK_ACCESS_READ,
        'w+b' => self::MASK_ACCESS_READ_WRITE,
        'r+b' => self::MASK_ACCESS_READ_WRITE,
        'x+b' => self::MASK_ACCESS_READ_WRITE,
        'c+b' => self::MASK_ACCESS_READ_WRITE,
        'rt' => self::MASK_ACCESS_READ,
        'w+t' => self::MASK_ACCESS_READ_WRITE,
        'r+t' => self::MASK_ACCESS_READ_WRITE,
        'x+t' => self::MASK_ACCESS_READ_WRITE,
        'c+t' => self::MASK_ACCESS_READ_WRITE,
        'a+' => self::MASK_ACCESS_READ_WRITE,
        'w' => self::MASK_ACCESS_WRITE,
        'rw' => self::MASK_ACCESS_WRITE,
        'wb' => self::MASK_ACCESS_WRITE,
        'a' => self::MASK_ACCESS_WRITE
    ];

    /**
     * @var bool $readable
     */
    private $readable;
    /**
     * @var bool $writeable
     */
    private $writeable;
    /**
     * @var bool $seekable
     */
    private $seekable;
    /**
     * @var resource $stream
     */
    private $stream;
    /**
     * @var int $size
     */
    private $size;
    /**
     * @var string $uri
     */
    private $uri;
    /**
     * @var string[] $customMetadata
     */
    private $customMetadata;


    /**
     * Stream constructor.
     *
     * @param resource         $stream   The resource which should be wrapped by the Stream.
     * @param StreamOptions    $options  The additional options which are accessible via getMetadata
     */
    public function __construct($stream, StreamOptions $options = null)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a valid resource but "' . gettype($stream) . '" was given.');
        }

        if ($options !== null) {
            $this->customMetadata = $options->getMetadata();
            $this->size = ($options->getSize() !== -1) ? $options->getSize() : null;
        } else {
            $this->customMetadata = [];
        }

        $this->stream = $stream;

        $meta = stream_get_meta_data($this->stream);
        $mode = $meta['mode'];

        $this->readable = array_key_exists($mode, self::$accessMap) && boolval(self::$accessMap[$mode] & self::MASK_ACCESS_READ);
        $this->writeable = array_key_exists($mode, self::$accessMap) && boolval(self::$accessMap[$mode] & self::MASK_ACCESS_WRITE);
        $this->seekable = boolval($meta['seekable']);
        $this->uri = $this->getMetadata('uri');
    }


    /**
     * @inheritDoc
     */
    public function close()
    {
        if ($this->stream !== null && is_resource($this->stream)) {
            PHPStreamFunctions::fclose($this->stream);
        }

        $this->detach();
    }


    /**
     * @inheritDoc
     */
    public function detach()
    {
        $stream = $this->stream;
        $this->stream = $this->size = $this->uri = null;

        return $stream;
    }


    /**
     * @inheritDoc
     */
    public function getSize()
    {

        //check if we know the size
        if ($this->size !== null) {
            return $this->size;
        }

        //check if stream is detached
        if ($this->stream === null) {
            return null;
        }
        
        //clear stat cache if we got a uri (indicates that we have a file resource)
        if ($this->uri !== null) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);
        if (array_key_exists('size', $stats)) {
            $this->size = $stats['size'];
            return $this->size;
        }

        //unable to determine stream size
        return null;
    }


    /**
     * @inheritDoc
     */
    public function tell()
    {
        $this->assertStreamAttached();

        $result = PHPStreamFunctions::ftell($this->stream);

        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }


    /**
     * @inheritDoc
     */
    public function eof()
    {
        $this->assertStreamAttached();

        return feof($this->stream);
    }


    /**
     * @inheritDoc
     */
    public function isSeekable()
    {
        return $this->seekable;
    }


    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->assertStreamAttached();

        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (PHPStreamFunctions::fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException("Unable to seek to stream position \"$offset\" with whence \"$whence\"");
        }
    }


    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->seek(0);
    }


    /**
     * @inheritDoc
     */
    public function isWritable()
    {
        return $this->writeable;
    }


    /**
     * @inheritDoc
     */
    public function write($string)
    {
        $this->assertStreamAttached();

        if (!$this->isWritable()) {
            throw new \RuntimeException('Can not write to a non-writable stream');
        }

        //we can't know the new size
        $this->size = null;
        $result = PHPStreamFunctions::fwrite($this->stream, $string);

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }


    /**
     * @inheritDoc
     */
    public function isReadable()
    {
        return $this->readable;
    }


    /**
     * @inheritDoc
     */
    public function read($length)
    {
        $this->assertStreamAttached();

        if (!$this->isReadable()) {
            throw new \RuntimeException('Can not read from non-readable stream');
        }
        
        if ($length < 0) {
            throw new \RuntimeException('Length parameter must not be negative');
        }

        if ($length === 0) {
            return '';
        }
        
        $junk = PHPStreamFunctions::fread($this->stream, $length);
        if ($junk === false) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $junk;
    }


    /**
     * @inheritDoc
     */
    public function getContents()
    {
        $this->assertStreamAttached();
        
        $content = PHPStreamFunctions::stream_get_contents($this->stream);

        if ($content === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $content;
    }


    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {

        //return empty array if stream is detached
        if ($this->stream === null) {
            return [];
        }

        //return merged metadata if key is missing
        if ($key === null) {
            return array_merge(stream_get_meta_data($this->stream), $this->customMetadata);
        }

        //return value if key was provided

        //try fetch data from custom metadata
        if (array_key_exists($key, $this->customMetadata)) {
            return $this->customMetadata[$key];
        }

        //try to fetch data from php resource metadata
        $meta = stream_get_meta_data($this->stream);
        if (array_key_exists($key, $meta)) {
            return $meta[$key];
        }

        //the key was not found in standard and custom metadata.
        return null;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        try {
            $this->rewind();
            return strval($this->getContents());
        } catch (\Exception $ex) {
            //to string must not throw an error.
            return '';
        }
    }


    /**
     * @inheritDoc
     */
    public function __destruct()
    {

        //cleanup the resource on object destruction if the stream is not detached.
        if (!is_null($this->stream)) {
            $this->close();
        }
    }


    /**
     * Checks if the stream is attached to the wrapper.
     * An exception if thrown if the stream is already detached.
     *
     * @throws \RuntimeException Thrown if the stream is already detached.
     */
    private function assertStreamAttached()
    {
        if ($this->stream === null) {
            throw new \RuntimeException('Stream is detached');
        }
    }
}

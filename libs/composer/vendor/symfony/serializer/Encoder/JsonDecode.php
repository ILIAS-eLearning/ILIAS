<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Encoder;

use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * Decodes JSON data.
 *
 * @author Sander Coolen <sander@jibber.nl>
 */
class JsonDecode implements DecoderInterface
{
    protected $serializer;

    /**
     * True to return the result as an associative array, false for a nested stdClass hierarchy.
     */
    const ASSOCIATIVE = 'json_decode_associative';

    const OPTIONS = 'json_decode_options';

    /**
     * Specifies the recursion depth.
     */
    const RECURSION_DEPTH = 'json_decode_recursion_depth';

    private $defaultContext = [
        self::ASSOCIATIVE => false,
        self::OPTIONS => 0,
        self::RECURSION_DEPTH => 512,
    ];

    /**
     * Constructs a new JsonDecode instance.
     *
     * @param array $defaultContext
     */
    public function __construct($defaultContext = [], int $depth = 512)
    {
        if (!\is_array($defaultContext)) {
            @trigger_error(sprintf('Using constructor parameters that are not a default context is deprecated since Symfony 4.2, use the "%s" and "%s" keys of the context instead.', self::ASSOCIATIVE, self::RECURSION_DEPTH), E_USER_DEPRECATED);

            $defaultContext = [
                self::ASSOCIATIVE => (bool) $defaultContext,
                self::RECURSION_DEPTH => $depth,
            ];
        }

        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * Decodes data.
     *
     * @param string $data    The encoded JSON string to decode
     * @param string $format  Must be set to JsonEncoder::FORMAT
     * @param array  $context An optional set of options for the JSON decoder; see below
     *
     * The $context array is a simple key=>value array, with the following supported keys:
     *
     * json_decode_associative: boolean
     *      If true, returns the object as an associative array.
     *      If false, returns the object as nested stdClass
     *      If not specified, this method will use the default set in JsonDecode::__construct
     *
     * json_decode_recursion_depth: integer
     *      Specifies the maximum recursion depth
     *      If not specified, this method will use the default set in JsonDecode::__construct
     *
     * json_decode_options: integer
     *      Specifies additional options as per documentation for json_decode
     *
     * @return mixed
     *
     * @throws NotEncodableValueException
     *
     * @see http://php.net/json_decode json_decode
     */
    public function decode($data, $format, array $context = [])
    {
        $associative = $context[self::ASSOCIATIVE] ?? $this->defaultContext[self::ASSOCIATIVE];
        $recursionDepth = $context[self::RECURSION_DEPTH] ?? $this->defaultContext[self::RECURSION_DEPTH];
        $options = $context[self::OPTIONS] ?? $this->defaultContext[self::OPTIONS];

        $decodedData = json_decode($data, $associative, $recursionDepth, $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new NotEncodableValueException(json_last_error_msg());
        }

        return $decodedData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return JsonEncoder::FORMAT === $format;
    }
}

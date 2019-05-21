<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Normalizer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Normalizes an instance of {@see \DateInterval} to an interval string.
 * Denormalizes an interval string to an instance of {@see \DateInterval}.
 *
 * @author Jérôme Parmentier <jerome@prmntr.me>
 */
class DateIntervalNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    const FORMAT_KEY = 'dateinterval_format';

    private $defaultContext = [
        self::FORMAT_KEY => 'P%yY%mM%dDT%hH%iM%sS',
    ];

    /**
     * @param array $defaultContext
     */
    public function __construct($defaultContext = [])
    {
        if (!\is_array($defaultContext)) {
            @trigger_error(sprintf('The "format" parameter is deprecated since Symfony 4.2, use the "%s" key of the context instead.', self::FORMAT_KEY), E_USER_DEPRECATED);

            $defaultContext = [self::FORMAT_KEY => (string) $defaultContext];
        }

        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof \DateInterval) {
            throw new InvalidArgumentException('The object must be an instance of "\DateInterval".');
        }

        return $object->format($context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateInterval;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return __CLASS__ === \get_class($this);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!\is_string($data)) {
            throw new InvalidArgumentException(sprintf('Data expected to be a string, %s given.', \gettype($data)));
        }

        if (!$this->isISO8601($data)) {
            throw new UnexpectedValueException('Expected a valid ISO 8601 interval string.');
        }

        $dateIntervalFormat = $context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY];

        $valuePattern = '/^'.preg_replace('/%([yYmMdDhHiIsSwW])(\w)/', '(?P<$1>\d+)$2', $dateIntervalFormat).'$/';
        if (!preg_match($valuePattern, $data)) {
            throw new UnexpectedValueException(sprintf('Value "%s" contains intervals not accepted by format "%s".', $data, $dateIntervalFormat));
        }

        try {
            return new \DateInterval($data);
        } catch (\Exception $e) {
            throw new UnexpectedValueException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return \DateInterval::class === $type;
    }

    private function isISO8601($string)
    {
        return preg_match('/^P(?=\w*(?:\d|%\w))(?:\d+Y|%[yY]Y)?(?:\d+M|%[mM]M)?(?:(?:\d+D|%[dD]D)|(?:\d+W|%[wW]W))?(?:T(?:\d+H|[hH]H)?(?:\d+M|[iI]M)?(?:\d+S|[sS]S)?)?$/', $string);
    }
}

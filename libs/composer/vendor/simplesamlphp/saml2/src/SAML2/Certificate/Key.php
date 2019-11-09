<?php

namespace SAML2\Certificate;

use SAML2\Certificate\Exception\InvalidKeyUsageException;
use SAML2\Exception\InvalidArgumentException;

/**
 * Simple DTO wrapper for (X509) keys. Implements ArrayAccess
 * for easier backwards compatibility.
 */
class Key implements \ArrayAccess
{
    // Possible key usages
    const USAGE_SIGNING = 'signing';
    const USAGE_ENCRYPTION = 'encryption';

    /**
     * @var array
     */
    protected $keyData = [];


    /**
     * @param array $keyData
     */
    public function __construct(array $keyData)
    {
        // forcing usage of offsetSet
        foreach ($keyData as $property => $value) {
            $this->offsetSet($property, $value);
        }
    }


    /**
     * Whether or not the key is configured to be used for usage given
     *
     * @param  string $usage
     * @return bool
     */
    public function canBeUsedFor($usage)
    {
        if (!in_array($usage, static::getValidKeyUsages(), true)) {
            throw new InvalidKeyUsageException($usage);
        }

        return isset($this->keyData[$usage]) && $this->keyData[$usage];
    }


    /**
     * Returns the list of valid key usage options
     * @return array
     */
    public static function getValidKeyUsages()
    {
        return [
            self::USAGE_ENCRYPTION,
            self::USAGE_SIGNING
        ];
    }


    /**
     * @param mixed $offset
     * @throws InvalidArgumentException
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        return array_key_exists($offset, $this->keyData);
    }


    /**
     * @param mixed $offset
     * @throws InvalidArgumentException
     * @return string
     */
    public function offsetGet($offset)
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        return $this->keyData[$offset];
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws InvalidArgumentException
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        $this->keyData[$offset] = $value;
    }


    /**
     * @param mixed $offset
     * @throws InvalidArgumentException
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        unset($this->keyData[$offset]);
    }
}

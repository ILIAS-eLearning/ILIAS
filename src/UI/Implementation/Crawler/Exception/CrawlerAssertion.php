<?php declare(strict_types=1);

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler\Exception;

/**
 * Tests properties and throws exceptions if not met
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */
class CrawlerAssertion
{
    protected ?Factory $f = null;

    public function __construct()
    {
        $this->f = new Factory();
    }

    /**
     * @param	mixed $array
     * @throws	CrawlerException
     */
    public function isArray($array) : void
    {
        if (!is_array($array)) {
            throw $this->f->exception(CrawlerException::ARRAY_EXPECTED, $array);
        }
    }

    /**
     * @param	mixed $string
     * @throws	CrawlerException
     */
    public function isString($string, bool $allow_empty = true) : void
    {
        if (!is_string($string)) {
            if (is_array($string)) {
                $string = json_encode($string);
            }
            throw $this->f->exception(CrawlerException::STRING_EXPECTED, (string) $string);
        }
        if (!$allow_empty && $string == "") {
            throw $this->f->exception(CrawlerException::EMPTY_STRING, (string) $string);
        }
    }

    /**
     * @param	mixed	$index
     * @throws	CrawlerException
     */
    public function isIndex($index, array $array) : void
    {
        if (!array_key_exists($index, $array)) {
            throw $this->f->exception(CrawlerException::INVALID_INDEX, strval($index));
        }
    }

    /**
     * @param	mixed	$index
     * @throws	CrawlerException
     */
    public function isNotIndex($index, array $array) : void
    {
        if (array_key_exists($index, $array)) {
            throw $this->f->exception(CrawlerException::DUPLICATE_ENTRY, strval($index));
        }
    }

    /**
     * @param	mixed	$index
     * @throws	CrawlerException
     */
    public function hasIndex(array $array, $index) : void
    {
        if (!array_key_exists($index, $array)) {
            throw $this->f->exception(CrawlerException::MISSING_INDEX, strval($index));
        }
    }

    /**
     * @param	mixed	$element
     * @throws	CrawlerException
     */
    public function isTypeOf($element, string $class_name) : void
    {
        if (!get_class($element) == $class_name) {
            throw $this->f->exception(
                CrawlerException::INVALID_TYPE,
                "Expected: " . $class_name . " got " . get_class($element)
            );
        }
    }
}

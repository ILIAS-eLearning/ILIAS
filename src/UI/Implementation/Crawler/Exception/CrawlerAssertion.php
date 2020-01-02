<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Crawler\Exception;

/**
 * Tests properties and throws exceptions if not met
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class CrawlerAssertion
{
    /**
     * @var Factory
     */
    protected $f = null;

    public function __construct()
    {
        $this->f = new Factory();
    }

    /**
     * @param	$array
     * @throws	CrawlerException
     */
    public function isArray($array)
    {
        if (!is_array($array)) {
            throw $this->f->exception(CrawlerException::ARRAY_EXPECTED, $array);
        }
    }

    /**
     * @param	$string
     * @param	bool|true $allow_empty
     * @throws	CrawlerException
     */
    public function isString($string, $allow_empty = true)
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
     * @param	array	$array
     * @throws	CrawlerException
     */
    public function isIndex($index, array $array)
    {
        if (!array_key_exists($index, $array)) {
            throw $this->f->exception(CrawlerException::INVALID_INDEX, $index);
        }
    }

    /**
     * @param	mixed	$index
     * @param	array	$array
     * @throws	CrawlerException
     */
    public function isNotIndex($index, array $array)
    {
        if (array_key_exists($index, $array)) {
            throw $this->f->exception(CrawlerException::DUPLICATE_ENTRY, $index);
        }
    }

    /**
     * @param	array	$array
     * @param	mixed	$index
     * @throws	CrawlerException
     */
    public function hasIndex($array, $index)
    {
        if (!array_key_exists($index, $array)) {
            throw $this->f->exception(CrawlerException::MISSING_INDEX, $index);
        }
    }

    /**
     * @param	mixed	$element
     * @param	string	$class_name
     * @throws	CrawlerException
     */
    public function isTypeOf($element, $class_name)
    {
        if (!get_class($element) == $class_name) {
            throw $this->f->exception(CrawlerException::INVALID_TYPE, "Expected: " . $class_name . " got " . get_class($element));
        }
    }
}

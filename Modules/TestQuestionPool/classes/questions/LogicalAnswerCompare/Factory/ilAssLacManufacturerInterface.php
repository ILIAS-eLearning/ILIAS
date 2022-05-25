<?php
/**
 * Class ManufacturerInterface
 *
 * Date: 25.03.13
 * Time: 15:33
 * @author Thomas Joußen <tjoussen@databay.de>
 */

interface ilAssLacManufacturerInterface
{

    /**
     * Get an singleton of the manufacturer
     *
     * @return ilAssLacManufacturerInterface
     */
    public static function _getInstance() : ?ilAssLacManufacturerInterface;

    /**
     * Create a new specific Composite object which is representing the delivered Attribute
     */
    public function manufacture(string $attribute) : ilAssLacAbstractComposite;

    /**
     * @return string
     */
    public function getPattern() : string;

    /**
     * Matches a delivered string with a the pattern returned by getPattern implemented in the explicit Manufacturer
     * @param string $subject
     * @return array
     *@see ManufacturerInterface::getPattern()
     */
    public function match(string $subject) : array;
}

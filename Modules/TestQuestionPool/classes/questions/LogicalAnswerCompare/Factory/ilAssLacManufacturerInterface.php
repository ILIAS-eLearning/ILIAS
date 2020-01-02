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
    public static function _getInstance();

    /**
     * Create a new specific Composite object which is representing the delivered Attribute
     *
     * @param string $attribute
     *
     * @return ilAssLacAbstractComposite
     */
    public function manufacture($attribute);

    /**
     * @return string
     */
    public function getPattern();

    /**
     * Matches a delivered string with a the pattern returned by getPattern implemented in the explicit Manufacturer
     *
     * @param string $subject
     *
     * @see ManufacturerInterface::getPattern()
     * @return array
     */
    public function match($subject);
}

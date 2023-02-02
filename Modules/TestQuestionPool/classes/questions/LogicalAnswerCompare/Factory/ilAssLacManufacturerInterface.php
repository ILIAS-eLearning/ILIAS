<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public static function _getInstance(): ?ilAssLacManufacturerInterface;

    /**
     * Create a new specific Composite object which is representing the delivered Attribute
     */
    public function manufacture(string $attribute): ilAssLacAbstractComposite;

    /**
     * @return string
     */
    public function getPattern(): string;

    /**
     * Matches a delivered string with a the pattern returned by getPattern implemented in the explicit Manufacturer
     * @param string $subject
     * @return array
     *@see ManufacturerInterface::getPattern()
     */
    public function match(string $subject): array;
}

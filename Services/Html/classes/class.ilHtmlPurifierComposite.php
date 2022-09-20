<?php

declare(strict_types=1);

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
 * Composite for nesting multiple purifiers
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilHtmlPurifierComposite implements ilHtmlPurifierInterface
{
    /** @var ilHtmlPurifierInterface[]  */
    private array $purifiers = [];

    /**
     * Adds a node to composite
     * @param ilHtmlPurifierInterface $purifier Instance of ilHtmlPurifierInterface
     * @return bool True if instance could be added, otherwise false
     */
    public function addPurifier(ilHtmlPurifierInterface $purifier): bool
    {
        if (!in_array($purifier, $this->purifiers, true)) {
            $this->purifiers[] = $purifier;
            return true;
        }

        return false;
    }

    /**
     * Removes a node from composite
     * @param ilHtmlPurifierInterface $purifier Instance of ilHtmlPurifierInterface
     * @return bool True if instance could be removed, otherwise false
     */
    public function removePurifier(ilHtmlPurifierInterface $purifier): bool
    {
        $key = array_search($purifier, $this->purifiers, true);
        if (false === $key) {
            return false;
        }
        unset($this->purifiers[$key]);

        return true;
    }

    public function purify(string $html): string
    {
        foreach ($this->purifiers as $purifier) {
            $html = $purifier->purify($html);
        }

        return $html;
    }

    public function purifyArray(array $htmlCollection): array
    {
        foreach ($htmlCollection as $key => $html) {
            if (!is_string($html)) {
                throw new InvalidArgumentException(sprintf(
                    'The element on index %s is not of type string: %s',
                    $key,
                    print_r($html, true)
                ));
            }
        }

        foreach ($htmlCollection as $key => $html) {
            foreach ($this->purifiers as $purifier) {
                $html = $purifier->purify($html);
            }

            $htmlCollection[$key] = $html;
        }

        return $htmlCollection;
    }
}

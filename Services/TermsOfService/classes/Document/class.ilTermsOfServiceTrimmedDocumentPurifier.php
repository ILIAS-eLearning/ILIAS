<?php declare(strict_types=1);

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
 * Class ilTermsOfServiceTrimmedDocumentPurifier
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceTrimmedDocumentPurifier implements ilHtmlPurifierInterface
{
    protected ilHtmlPurifierInterface $inner;

    public function __construct(ilHtmlPurifierInterface $inner)
    {
        $this->inner = $inner;
    }

    public function purify(string $html) : string
    {
        return trim($this->inner->purify($html));
    }

    public function purifyArray(array $htmlCollection) : array
    {
        foreach ($htmlCollection as $key => $html) {
            $htmlCollection[$key] = $this->purify($html);
        }

        return $htmlCollection;
    }
}

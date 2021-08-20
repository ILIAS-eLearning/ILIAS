<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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

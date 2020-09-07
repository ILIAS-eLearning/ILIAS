<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceTrimmedDocumentPurifier
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceTrimmedDocumentPurifier implements \ilHtmlPurifierInterface
{
    /**
     * @var \ilHtmlPurifierInterface
     */
    protected $inner;

    /**
     * ilTermsOfServiceTrimmedDocumentPurifier constructor.
     * @param \ilHtmlPurifierInterface $inner
     */
    public function __construct(\ilHtmlPurifierInterface $inner)
    {
        $this->inner = $inner;
    }

    /**
     * @inheritdoc
     */
    public function purify($a_html)
    {
        return trim($this->inner->purify($a_html));
    }

    /**
     * @inheritdoc
     */
    public function purifyArray(array $a_array_of_html)
    {
        foreach ($a_array_of_html as $key => $html) {
            $a_array_of_html[$key] = $this->purify($html);
        }

        return $a_array_of_html;
    }
}

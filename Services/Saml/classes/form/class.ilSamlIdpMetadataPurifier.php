<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlIdpMetadataPurifier
 */
class ilSamlIdpMetadataPurifier implements ilHtmlPurifierInterface
{
    /**
     * @inheritdoc
     */
    public function purify(string $html) : string
    {
        return $html;
    }

    /**
     * @inheritdoc
     */
    public function purifyArray(array $htmlCollection) : array
    {
        foreach ($htmlCollection as $key => $html) {
            $html = $this->purify($html);
            $htmlCollection[$key] = $html;
        }

        return $htmlCollection;
    }
}
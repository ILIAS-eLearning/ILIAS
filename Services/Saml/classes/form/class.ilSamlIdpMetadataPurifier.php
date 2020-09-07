<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Html/interfaces/interface.ilHtmlPurifierInterface.php';

/**
 * Class ilSamlIdpMetadataPurifier
 */
class ilSamlIdpMetadataPurifier implements ilHtmlPurifierInterface
{
    /**
     * @inheritdoc
     */
    public function purify($a_html)
    {
        return $a_html;
    }

    /**
     * @inheritdoc
     */
    public function purifyArray(array $a_array_of_html)
    {
        foreach ($a_array_of_html as $key => $html) {
            $html = $this->purify($html);
            $a_array_of_html[$key] = $html;
        }

        return $a_array_of_html;
    }
}

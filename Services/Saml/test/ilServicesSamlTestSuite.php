<?php declare(strict_types=1);
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';

use PHPUnit\Framework\TestSuite;

/**
 * Class ilServicesSamlTestSuite
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilServicesSamlTestSuite extends TestSuite
{
    public static function suite() : self
    {
        $suite = new self();

        require_once 'Services/Saml/test/ilSamlMappedUserAttributeValueParserTest.php';
        $suite->addTestSuite(ilSamlMappedUserAttributeValueParserTest::class);

        require_once 'Services/Saml/test/ilSamlIdpXmlMetadataParserTest.php';
        $suite->addTestSuite(ilSamlIdpXmlMetadataParserTest::class);

        return $suite;
    }
}

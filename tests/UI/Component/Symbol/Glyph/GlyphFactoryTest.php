<?php declare(strict_types=1);

require_once 'tests/UI/AbstractFactoryTest.php';

class GlyphFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "collapse" => ["context" => false],
        "expand" => ["context" => false],
        "user" => ["context" => false],
        "mail" => ["context" => false],
        "notification" => ["context" => false],
        "tag" => ["context" => false],
        "note" => ["context" => false],
        "comment" => ["context" => false],
        "sortAscending" => ["context" => false],
        "sortDescending" => ["context" => false],
        "briefcase" => ["context" => false],
        "reset" => ["context" => false],
        "apply" => ["context" => false],
        "close" => ["context" => false],
        "settings" => ["context" => false],
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\Symbol\\Glyph\\Factory';
}

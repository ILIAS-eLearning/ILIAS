<?php

use PHPUnit\Framework\TestCase;

/**
 * Wiki util test. Tests mostly mediawiki code.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class WikiUtilTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    /**
     * Test make URL title
     */
    public function testRefId(): void
    {
        $input_expected = [
            ["a", "a"]
            ,["z", "z"]
            ,["0", "0"]
            ,[" ", "_"]
            ,["_", "_"]
            ,["!", "%21"]
            ,["§", "%C2%A7"]
            ,["$", "%24"]
            ,["%", "%25"]
            ,["&", "%26"]
            ,["/", "%2F"]
            ,["(", "%28"]
            ,["+", "%2B"]
            ,[";", "%3B"]
            ,[":", "%3A"]
            ,["-", "-"]
            ,["#", "%23"]
            ,["\x00", ""]
            ,["\n", ""]
            ,["\r", ""]
        ];
        foreach ($input_expected as $ie) {
            $result = ilWikiUtil::makeUrlTitle($ie[0]);

            $this->assertEquals(
                $ie[1],
                $result
            );
        }
    }
}

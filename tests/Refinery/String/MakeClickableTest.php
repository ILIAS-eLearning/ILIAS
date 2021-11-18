<?php declare(strict_types=1);

namespace ILIAS\Tests\Refinery\String;

use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\String\MakeClickable;

class MakeClickableTest extends TestCase
{
    public function testConstruct() : void
    {
        $this->assertInstanceOf(MakeClickable::class, new MakeClickable());
    }

    public function testTransformFailure() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $clickable = new MakeClickable();

        $clickable->transform(3);
    }

    /**
     * @dataProvider provideInput
     */
    public function testTransformSuccess(string $expected, string $input) : void
    {
        $clickable = new MakeClickable();

        $this->assertEquals($expected, $clickable->transform($input));
    }

    public function provideInput() : array
    {
        return [
            'test empty string' => ['', ''],
            'test no links' => ['Aliquam erat volutpat.  Nunc eleifend leo vitae magna. <strong>Sed bibendum</strong>donec vitae dolor<p><b magna="vitae"> phasellus purus</b></p> Nunc aliquet, augue nec adipiscing interdum, lacus tellus malesuada massa, quis varius mi purus non odio.', 'Aliquam erat volutpat.  Nunc eleifend leo vitae magna. <strong>Sed bibendum</strong>donec vitae dolor<p><b magna="vitae"> phasellus purus</b></p> Nunc aliquet, augue nec adipiscing interdum, lacus tellus malesuada massa, quis varius mi purus non odio.'],
            'test only link' => ['<a href="https://www.ilias.de">https://www.ilias.de</a>', 'https://www.ilias.de'],
            'test simple link string' => [' <a href="https://www.ilias.de">https://www.ilias.de</a> ', ' https://www.ilias.de '],
            'test no nesting' => ['Phasellus at dui in ligula mollis ultricies <span><a href="https://www.ilias.de">https://www.ilias.de</a></span> <a href="baba">Nullam rutrum</a>', 'Phasellus at dui in ligula mollis ultricies <span>https://www.ilias.de</span> <a href="baba">Nullam rutrum</a>'],
            'match with links after match' => [' Nullam rutrum <a href="https://www.ilias.de">https://www.ilias.de</a> <a href="baba">Integer placerat tristique nisl</a>', ' Nullam rutrum https://www.ilias.de <a href="baba">Integer placerat tristique nisl</a>'],
            'test surrounded by diamonds' => ['><a href="https://www.ilias.de">https://www.ilias.de</a><', '>https://www.ilias.de<'],
            'test surrounded by other links' => ['><a href="https://www.ilias.de">www.ilias.de</a><a href="www.ilias.de">www.ilias.de</a><a href="https://www.ilias.de">www.ilias.de</a>', '>www.ilias.de<a href="www.ilias.de">www.ilias.de</a>www.ilias.de'],
            'test links next to each other' => ['<a href="https://www.ilias.de">www.ilias.de</a> <a href="https://www.ilias.de">www.ilias.de</a> <a href="https://www.ilias.de">www.ilias.de</a>', 'www.ilias.de www.ilias.de www.ilias.de'],
            'test multiple + nested' => ['Praesent fermentum tempor tellus <span>Aliquam erat volutpat<b><a href="www.ilias.de"><b>curabitur lacinia pulvinar nibh</b> www.ilias.de</a>nunc eleifend leo vitae magnawww.ilias.denunc rutrum turpis sed pede <a href="https://www.ilias.de">www.ilias.de</a><a href="www.ilias.de">www.ilias.de</a><a href="https://www.ilias.de">www.ilias.de</a>', 'Praesent fermentum tempor tellus <span>Aliquam erat volutpat<b><a href="www.ilias.de"><b>curabitur lacinia pulvinar nibh</b> www.ilias.de</a>nunc eleifend leo vitae magnawww.ilias.denunc rutrum turpis sed pede www.ilias.de<a href="www.ilias.de">www.ilias.de</a>www.ilias.de'],
            'test with properties' => ['<a href="https://www.ilias.de">www.ilias.de</a> <a purus="pretium" href="www.ilias.de" tellus="bibendum">www.ilias.de</a>', 'www.ilias.de <a purus="pretium" href="www.ilias.de" tellus="bibendum">www.ilias.de</a>'],
            'test with tabs' => ['Mauris ac felis vel velit tristique imperdiet <a	href="www.ilias.de">www.ilias.de</a>', 'Mauris ac felis vel velit tristique imperdiet <a	href="www.ilias.de">www.ilias.de</a>'],
            'test example input with newlines' => ["Das ist eine URL: <a href=\"https://www.ilias.de\">https://www.ilias.de</a>\nDas ist sogar ein Link: <a href=\"https://www.ilias.de\">https://www.ilias.de</a>\nDas ist ein Link hinter einem Wort: <a href=\"https://www.ilias.de\">Link</a> und noch mehr Text.", "Das ist eine URL: https://www.ilias.de\nDas ist sogar ein Link: <a href=\"https://www.ilias.de\">https://www.ilias.de</a>\nDas ist ein Link hinter einem Wort: <a href=\"https://www.ilias.de\">Link</a> und noch mehr Text."],
            'test without protocol to URL with protocol' => ['<a href="https://www.ilias.de">www.ilias.de</a>', 'www.ilias.de'],
            'test link with parameters' => ['<a href="http://ilias.de/ilias.php?ref_id=29&admin_mode=settings&cmd=view&cmdClass=ilobjdashboardsettingsgui&cmdNode=1c:lg&baseClass=ilAdministrationGUI">http://ilias.de/ilias.php?ref_id=29&admin_mode=settings&cmd=view&cmdClass=ilobjdashboardsettingsgui&cmdNode=1c:lg&baseClass=ilAdministrationGUI</a>', 'http://ilias.de/ilias.php?ref_id=29&admin_mode=settings&cmd=view&cmdClass=ilobjdashboardsettingsgui&cmdNode=1c:lg&baseClass=ilAdministrationGUI']
        ];
    }
}

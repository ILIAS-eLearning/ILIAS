<?php
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
 
require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

class ToastClientHtmlTest extends ILIAS_UI_TestBase
{
    public function getUIFactory() : NoUIFactory
    {
        return new class extends NoUIFactory {
            public function button() : C\Button\Factory
            {
                return new I\Component\Button\Factory();
            }
        };
    }

    public function getToastFactory() : \ILIAS\UI\Implementation\Component\Toast\Factory
    {
        return new ILIAS\UI\Implementation\Component\Toast\Factory(
            $this->createMock(ILIAS\UI\Implementation\Component\SignalGenerator::class)
        );
    }

    public function getIconFactory() : \ILIAS\UI\Implementation\Component\Symbol\Icon\Factory
    {
        return new ILIAS\UI\Implementation\Component\Symbol\Icon\Factory();
    }

    public function testRenderClientHtml() : void
    {
        $expected_html = file_get_contents(__DIR__ . "/../../Client/Toast/ToastTest.html");

        $rendered_html = '<head>
          <title>Toast Test HTML</title>
          <script src="../../../../src/UI/templates/js/Toast/toast.js"></script>
          <script>document.il = il</script>
        </head>
        <body>
          {CONTAINER}
        </body>';

        $container = $this->getToastFactory()->container()->withAdditionalToast(
            $this->getToastFactory()->standard(
                'Title',
                $this->getIconFactory()->standard('mail', 'Test')
            )
                                    ->withVanishTime(5000)
                                    ->withDelayTime(500)
                                    ->withDescription('Description')
                                    ->withAction('https://www.ilias.de')
        );

        $rendered_html = str_replace('{CONTAINER}', $this->getDefaultRenderer()->render($container), $rendered_html);
        $rendered_html = preg_replace('/id=".*?"/', '', $rendered_html);

        $this->assertEquals($this->brutallyTrimHTML($expected_html), $this->brutallyTrimHTML($rendered_html));
    }
}

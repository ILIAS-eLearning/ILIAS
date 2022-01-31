<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

require_once __DIR__ . '/ilMathJaxBaseTest.php';

/**
 * Testing the MathJax class
 */
class ilMathJaxTest extends ilMathJaxBaseTest
{
    public function testInstanceCanBeCreated() : void
    {
        $config = $this->getEmptyConfig();
        $mathjax = ilMathJax::getIndependent($this->getEmptyConfig(), $this->getFactoryMock());
        $this->assertInstanceOf('ilMathJax', $mathjax);
    }

    /**
     * @depends testInstanceCanBeCreated
     * @dataProvider clientSideData
     */
    public function testClientSideRendering(int $limiter, string $input, ?string $start, ?string $end, string $expected) : void
    {
        $config = $this->getEmptyConfig()->withClientEnabled(true)->withClientLimiter($limiter);
        $mathjax = ilMathJax::getIndependent($config, $this->getFactoryMock());
        $result = $mathjax->insertLatexImages($input, $start, $end);
        $this->assertEquals($expected, $result, 'input: ' . $input);
    }

    public function clientSideData() : array
    {
        return  [
            [0, '[tex]e=m*c^2[/tex]', null, null, '\(e=m*c^2\)'],
            [1, '[tex]e=m*c^2[/tex]', null, null, '[tex]e=m*c^2[/tex]'],
            [2, '[tex]e=m*c^2[/tex]', null, null, '<span class="math">e=m*c^2</span>'],
            [1, '<span class="math">e=m*c^2</span>', '<span class="math">', '</span>', '[tex]e=m*c^2[/tex]'],
            [0, '[tex]e=m*c^2[/tex][tex]e=m*c^2[/tex]', null, null, '\(e=m*c^2\)\(e=m*c^2\)'],
            // char beween
            [0, '[tex]e=m*c^2[/tex]#[tex]e=m*c^2[/tex]', null, null, '\(e=m*c^2\)#\(e=m*c^2\)'],
            [0, '#[tex]e=m*c^2[/tex]#[tex]e=m*c^2[/tex]', null, null, '#\(e=m*c^2\)#\(e=m*c^2\)'],
            [0, '#[tex]e=m*c^2[/tex]#[tex]e=m*c^2[/tex]#', null, null, '#\(e=m*c^2\)#\(e=m*c^2\)#'],
            // multibyte char
            [0, '[tex]e=m*c^2[/tex]♥[tex]e=m*c^2[/tex]', null, null, '\(e=m*c^2\)♥\(e=m*c^2\)'],
            [0, '♥[tex]e=m*c^2[/tex]♥[tex]e=m*c^2[/tex]', null, null, '♥\(e=m*c^2\)♥\(e=m*c^2\)'],
            [0, '♥[tex]e=m*c^2[/tex]♥[tex]e=m*c^2[/tex]♥', null, null, '♥\(e=m*c^2\)♥\(e=m*c^2\)♥'],
            // start ignored until end is found
            [0, '[tex]e=m*c^2[tex]e=m*c^2[/tex]', null, null, '\(e=m*c^2[tex]e=m*c^2\)'],
            // whole expression ignored if no end is found
            [0, '[tex]e=m*c^2[/tex][tex]e=m*c^2', null, null, '\(e=m*c^2\)[tex]e=m*c^2'],
        ];
    }

    /**
     * @depends testInstanceCanBeCreated
     * @dataProvider serverSideData
     */
    public function testServerSideRendering(string $purpose, ?string $imagefile, string $expected) : void
    {
        $input = '[tex]f(x)=\int_{-\infty}^x e^{-t^2}dt[/tex]';

        $config = $this->getEmptyConfig()
                       ->withServerEnabled(true)
                       ->withServerForBrowser($purpose == 'browser')
                       ->withServerForExport($purpose == 'export')
                       ->withServerForPdf($purpose == 'pdf');

        $mathjax = ilMathJax::getIndependent($config, $this->getFactoryMock($imagefile))->init($purpose);
        $result = $mathjax->insertLatexImages($input);
        $head = substr($result, 0, 60);
        $this->assertEquals($expected, $head, 'purpose: ' . $purpose);
    }

    public function serverSideData() : array
    {
        return  [
            ['browser', 'example.svg', '<svg xmlns:xlink="http://www.w3.org/1999/xlink" width="17.47'],
            ['export', 'example.svg', '<img src="data:image/svg+xml;base64,PHN2ZyB4bWxuczp4bGluaz0i'],
            ['pdf', 'example.png', '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYA'],
            ['deferred_pdf', null, '[tex]f(x)=\int_{-\infty}^x e^{-t^2}dt[/tex]']
        ];
    }
}

<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Refinery\String;

require_once 'libs/composer/vendor/autoload.php';

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use PHPUnit\Framework\TestCase;
use stdClass;
use InvalidArgumentException;
use ILIAS\Refinery\String\EstimatedReadingTime;

class EstimatedReadingTimeTest extends TestCase
{
    private const TEXT = <<<EOT
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
EOT;
    private const HTML = <<<EOT
<div>Lorem ipsum dolor <span style="color: red;">sit amet</span>, <img src="#" /> consetetur sadipscing elitr, sed diam nonumy eirmod <img src="#" />  tempor invidunt <img src="#" />  ut labore et dolore <img src="#" />  magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor <img src="#" />  sit amet. <img src="#" />  Lorem ipsum dolor <img src="#" />  sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, <img src="#" />  sed diam voluptua. <img src="#" />  At vero eos et accusam et justo duo dolores et ea rebum. Stet <img src="#" />  clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</div>
EOT;

    private const HTML_WITH_NON_XHTML_URLS = <<<EOT
<a href="http://www.ilias.de=foo=1&bar=2">ILIAS</a>
EOT;

    private Refinery $refinery;

    protected function setUp(): void
    {
        $this->refinery = new Refinery(
            $this->createMock(DataFactory::class),
            $this->createMock(ilLanguage::class)
        );

        parent::setUp();
    }

    /**
     * @return array[]
     */
    public function inputProvider(): array
    {
        return [
            [5],
            [6.3],
            [[]],
            [new stdClass()],
            [true],
            [null],
            [static function (): void {
            }],
        ];
    }

    /**
     * @return array[]
     */
    public function unsupportedButKnownEntitiesProvider(): array
    {
        return array_map(static function (string $entity): array {
            return [
                '<div>Lorem ipsum dolor <' . $entity . '></' . $entity . '></div>'
            ];
        }, [
            'figure',
            'figcaption',
            'nav',
            'section',
            'header',
            'article',
            'footer',
            'aside',
            'noindex',
        ]);
    }

    /**
     * @dataProvider inputProvider
     * @param mixed $from
     */
    public function testExceptionIsRaisedIfInputIsNotAString($from): void
    {
        $this->expectException(InvalidArgumentException::class);
        $readingTimeTrafo = $this->refinery->string()->estimatedReadingTime(true);

        $readingTimeTrafo->transform($from);
    }

    public function testReadingTimeForPlainTextCanBeDetermined(): void
    {
        $readingTimeTrafo = $this->refinery->string()->estimatedReadingTime(true);
        $this->assertEquals(
            1,
            $readingTimeTrafo->transform(self::TEXT)
        );
    }

    public function testReadingTimeForHtmlFragmentCanBeDetermined(): void
    {
        $text = self::HTML;

        $readingTimeTrafo = $this->refinery->string()->estimatedReadingTime(true);
        $this->assertEquals(
            2,
            $readingTimeTrafo->transform($text)
        );

        $onlyTextReadingTimeInfo = $this->refinery->string()->estimatedReadingTime();
        $this->assertEquals(
            1,
            $onlyTextReadingTimeInfo->transform($text)
        );
    }

    public function testSolitaryPunctuationCharactersMustNotAffectReadingTime(): void
    {
        $textSegmentWithPunctuation = 'Lorem ipsum <img src="#" />, and some other text... ';
        $repetitions = 300; // 275 repetitions result in an additional minute, if the `,` would be considered

        $readingTimeTrafo = $this->refinery->string()->estimatedReadingTime(true);

        $text = str_repeat($textSegmentWithPunctuation, $repetitions);

        $timeInMinutes = $readingTimeTrafo->transform($text);
        $this->assertEquals(23, $timeInMinutes);

        $textSegmentWithoutPunctuation = 'Lorem ipsum <img src="#" /> and some other text... ';
        $text = str_repeat($textSegmentWithoutPunctuation, $repetitions);

        $timeInMinutes = $readingTimeTrafo->transform($text);
        $this->assertEquals(23, $timeInMinutes);
    }

    public function testXTHMLCommentsMustNotAffectReadingTime(): void
    {
        $text = self::HTML;

        $comment = '<script><!--a comment--></script>';
        $repetitions = 300;
        $text .= str_repeat($comment, $repetitions);

        $onlyTextReadingTimeInfo = $this->refinery->string()->estimatedReadingTime();
        $this->assertEquals(
            1,
            $onlyTextReadingTimeInfo->transform($text)
        );
    }

    /**
     * @dataProvider unsupportedButKnownEntitiesProvider
     * @param string $text
     */
    public function testNoExceptionIsRaisedIfHtmlContainsUnsupportedEntities(string $text): void
    {
        $reading_time_trafo = $this->refinery->string()->estimatedReadingTime(true);

        $reading_time = $reading_time_trafo->transform($text);

        $this->assertIsInt($reading_time);
        $this->assertGreaterThan(0, $reading_time);
    }

    public function testNoExceptionIsRaisedIfHtmlContainsAmpersandInUrls(): void
    {
        $reading_time_trafo = $this->refinery->string()->estimatedReadingTime(true);

        $reading_time = $reading_time_trafo->transform(self::HTML_WITH_NON_XHTML_URLS);

        $this->assertIsInt($reading_time);
        $this->assertGreaterThan(0, $reading_time);
    }
}

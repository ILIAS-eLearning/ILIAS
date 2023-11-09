<?php declare(strict_types=1);

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

use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastProvider;

require_once(__DIR__ . "/../BaseToastSetUp.php");

/**
 * Class DummyProviderTest
 */
class DummyToastProviderTest extends BaseToastSetUp
{
    public function testConstruct(): void
    {
        $provider = $this->getDummyToastProviderWithToasts([]);
        $this->assertInstanceOf(AbstractToastProvider::class, $provider);
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Tests\Fixtures;

class AbstractDummySecondChild extends AbstractDummy
{
    public $baz;

    public function __construct($foo = null, $baz = null)
    {
        parent::__construct($foo);

        $this->baz = $baz;
    }
}

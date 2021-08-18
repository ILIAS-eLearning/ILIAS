<?php

namespace ILIAS\HTTP;
/** @noRector */

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractBaseTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RequestInterface
     */
    protected $request_interface;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->request_interface = $this->createMock(ServerRequestInterface::class);
    }
}


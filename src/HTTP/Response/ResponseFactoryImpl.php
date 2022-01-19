<?php

namespace ILIAS\HTTP\Response;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ResponseFactoryImpl
 *
 * This class creates new psr-7 compliant Response
 * and decouples the used library from ILIAS components.
 *
 * The currently used psr-7 implementation is created and published by guzzle under the MIT license.
 * source: https://github.com/guzzle/psr7
 *
 * @package ILIAS\HTTP\Response
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 */
class ResponseFactoryImpl implements ResponseFactory
{

    /**
     * @inheritdoc
     */
    public function create() : ResponseInterface
    {
        return new Response();
    }
}

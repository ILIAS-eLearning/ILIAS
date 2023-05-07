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

declare(strict_types=1);
namespace ILIAS\GlobalScreen\Identification;

/**
 * Class IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ToolIdentificationProviderInterface extends IdentificationProviderInterface
{
    /**
     * @param string $identifier_string this is a identifier which is only known
     *                                  to your component. The GlobalScreen services uses
     *                                  this string together with e.g. the
     *                                  classname of your provider to stack
     *                                  items or to ask your provider for
     *                                  further infos.
     *                                  The identification you get can be
     *                                  serialized and is used e.g. to store in
     *                                  database and cache. you don't need to
     *                                  take care of storing this.
     * @param bool   $ignore_context
     * @return IdentificationInterface use this CoreIdentification to put into your
     *                                  GlobalScreen-elements.
     */
    public function contextAwareIdentifier(string $identifier_string, bool $ignore_context = false) : IdentificationInterface;
}

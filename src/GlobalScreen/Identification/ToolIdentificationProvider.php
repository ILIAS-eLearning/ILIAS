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

use LogicException;/**
 * Class CoreIdentificationProvider
 * @see    IdentificationProviderInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

class ToolIdentificationProvider extends CoreIdentificationProvider implements ToolIdentificationProviderInterface
{
    /**
     * @inheritDoc
     */
    public function contextAwareIdentifier(string $identifier_string, bool $ignore_context = false) : IdentificationInterface
    {
        if ($ignore_context) {
            return parent::identifier($identifier_string);
        }
        global $DIC;

        $get = $DIC->http()->request()->getQueryParams();
        if (isset($get['ref_id'])) {
            $identifier_string .= '_' . $get['ref_id'];
        }

        return parent::identifier($identifier_string);
    }

    /**
     * @inheritDoc
     */
    public function identifier(string $identifier_string) : IdentificationInterface
    {
        throw new LogicException('Tools must use contextAwareIdentifier');
    }
}

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

namespace ILIAS\Setup\Activities;

use ILIAS\Data\Description;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\Data\Text;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * This is a stub: the status of the installation is not collected yet.
 *
 * It is a safe one, though. What the setup knows - versions, pending updates,
 * parts of the configuration - is nothing for everybody with a session, so
 * nobody is allowed to perform this until it does something and is guarded by
 * a real check for administrators. Until then every call is answered with a
 * refusal instead of a `TypeError`.
 */
class GetStatus extends \ILIAS\Component\Activities\Query
{
    public function __construct(
        private readonly DataFactory $data = new DataFactory(),
    ) {
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument(
            "Reports the status of the installation.\n\n"
            . "Not available yet: nobody is allowed to perform it."
        );
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->section([], 'Get the status of the installation');
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        return $f->object(
            $this->data->text()->markdown()->simpleDocument('The status of the installation.'),
            []
        );
    }

    /**
     * Nobody, until there is something to report and a check for
     * administrators to guard it.
     */
    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        return false;
    }

    public function perform(mixed $parameters): mixed
    {
        throw new \LogicException('The status of the installation is not available yet.');
    }

    /**
     * @param array<string, mixed> $raw_parameters
     */
    public function maybePerformAs(InputFactory $input_factory, int $usr_id, array $raw_parameters): Result
    {
        if (!$this->isAllowedToPerform($usr_id, $raw_parameters)) {
            return $this->data->error(
                new \DomainException('You are not allowed to get the status of the installation.', 403)
            );
        }

        try {
            return $this->data->ok($this->perform($raw_parameters));
        } catch (\Exception $e) {
            return $this->data->error($e);
        }
    }
}

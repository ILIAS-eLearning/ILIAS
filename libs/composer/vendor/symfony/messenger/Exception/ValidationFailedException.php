<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Messenger\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @experimental in 4.2
 */
class ValidationFailedException extends \RuntimeException implements ExceptionInterface
{
    private $violations;
    private $violatingMessage;

    /**
     * @param object $violatingMessage
     */
    public function __construct($violatingMessage, ConstraintViolationListInterface $violations)
    {
        $this->violatingMessage = $violatingMessage;
        $this->violations = $violations;

        parent::__construct(sprintf('Message of type "%s" failed validation.', \get_class($this->violatingMessage)));
    }

    public function getViolatingMessage()
    {
        return $this->violatingMessage;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}

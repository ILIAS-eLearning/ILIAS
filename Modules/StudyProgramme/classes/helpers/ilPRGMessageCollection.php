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

/**
 * Holds information about multi-actions,
 * mainly in context of member-assignemnts and status changes
 */
class ilPRGMessageCollection
{
    protected array $success = [];
    protected array $error = [];
    protected string $description = '';

    public function withNewTopic(string $description) : ilPRGMessageCollection
    {
        $clone = clone $this;
        $clone->success = [];
        $clone->error = [];
        $clone->description = $description;
        return $clone;
    }
    
    /**
     * @return string[]
     */
    public function getSuccess() : array
    {
        return $this->success;
    }
    
    /**
     * @return string[]
     */
    public function getErrors() : array
    {
        return $this->error;
    }

    public function hasSuccess() : bool
    {
        return count($this->success) > 0;
    }

    public function hasErrors() : bool
    {
        return count($this->error) > 0;
    }

    public function hasAnyMessages() : bool
    {
        return count($this->error) > 0 || count($this->success) > 0;
    }
    
    public function getDescription() : string
    {
        return $this->description;
    }

    public function add(bool $success, string $message, string $record_identitifer) : void
    {
        $entry = [$message, $record_identitifer];
        if ($success) {
            $this->success[] = $entry;
        } else {
            $this->error[] = $entry;
        }
    }
}

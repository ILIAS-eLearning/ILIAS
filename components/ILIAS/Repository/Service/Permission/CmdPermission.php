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

namespace ILIAS\Repository\Permission;

abstract class CmdPermission implements CmdPermissionInterface
{
    public function __construct(
        protected \ilLanguage $lng,
        protected ?\ilGlobalTemplateInterface $tpl = null,
        protected ?\ilCtrlInterface $ctrl = null,
    ) {
    }

    public function isRequestCommandPermitted(): bool
    {
        if (is_null($this->ctrl)) {
            return false;
        }
        return $this->isCommandPermitted(
            $this->getRequestCommand(),
            $this->getRequestNodeId(),
            (string) $this?->getRequestEntity()->getType(),
            (string) $this?->getRequestEntity()->getId()
        );
    }

    public function isTableCommandPermitted(string $cmd, string $entity_id): bool
    {
        if (is_null($this->ctrl)) {
            return false;
        }
        return $this->isCommandPermitted(
            $cmd,
            $this->getRequestNodeId(),
            (string) $this?->getRequestEntity()->getType(),
            $entity_id
        );
    }

    public function getPermittedCommand(): string
    {
        if ($this->isRequestCommandPermitted()) {
            return $this->getRequestCommand();
        }
        return "";
    }

    public function getRequestCommand(): string
    {
        return $this->ctrl->getCmd($this->getDefaultCommand());
    }

    protected function isClass(string $class): bool
    {
        if (is_null($this->ctrl)) {
            return false;
        }
        $cmd_class = $this->ctrl->getCmdClass();
        return $cmd_class === strtolower($class);
    }

    public function getDefaultCommand(): string
    {
        return "";
    }


    /**
     * @throws \ILIAS\Repository\Permission\ilNoCmdPermissionException
     */
    public function checkCommand(string $cmd, int $node_id, string $entity, string $entity_id = ""): void
    {
        if (!$this->isCommandPermitted($cmd, $node_id, $entity, $entity_id)) {
            throw new \ILIAS\Repository\Permission\ilNoCmdPermissionException("No permission to execute command $cmd on $entity $entity_id");
        }
    }

    protected function cmdEntity(
        string $type,
        string $id = ""
    ): CmdEntity {
        return new CmdEntity(
            $type,
            $id
        );
    }

    public function forwardPermitted(
        object $from_gui,
        object $to_gui
    ): void {
        if (is_null($this->ctrl)) {
            return;
        }
        if ($this->isForwardPermitted(get_class($from_gui), get_class($to_gui))) {
            $this->ctrl->forwardCommand($to_gui);
            return;
        }
        if ($this->access->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->setParameterByClass("ilRepositoryGUI", "ref_id", ROOT_FOLDER_ID);
            $this->ctrl->redirectByClass("ilRepositoryGUI");
        }
        throw new \ilPermissionException($this->lng->txt("permission_denied"));
    }

    /**
     * For intermediate solutions that don't implement the concept for all subclasses yet
     */
    public function classImplementsMethodDirectly(string $className, string $method): bool
    {
        try {
            $class = new \ReflectionClass($className);
        } catch (\Exception $e) {
            return false;
        }

        return $class->hasMethod($method)
            && $class->getMethod($method)->getDeclaringClass()->getName() === $class->getName();
    }
}

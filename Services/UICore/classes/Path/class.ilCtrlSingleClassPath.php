<?php declare(strict_types = 1);

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
 */

/**
 * Class ilCtrlSingleClassPath
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilCtrlSingleClassPath extends ilCtrlAbstractPath
{
    /**
     * @var ilCtrlContextInterface
     */
    private ilCtrlContextInterface $context;

    /**
     * ilCtrlSingleClassPath Constructor
     *
     * @param ilCtrlStructureInterface $structure
     * @param ilCtrlContextInterface   $context
     * @param string                   $target_class
     */
    public function __construct(ilCtrlStructureInterface $structure, ilCtrlContextInterface $context, string $target_class)
    {
        parent::__construct($structure);

        $this->context = $context;

        try {
            $this->cid_path = $this->getCidPathByClass($target_class);
        } catch (ilCtrlException $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * Returns a cid path that reaches from the current context's
     * baseclass to the given class.
     *
     * If the given class cannot be reached from the context's
     * baseclass this instance must be given a class array instead.
     *
     * @param string $target_class
     * @return string
     * @throws ilCtrlException if the class has no relations or cannot
     *                         reach the baseclass of this context.
     */
    private function getCidPathByClass(string $target_class) : string
    {
        $target_cid = $this->structure->getClassCidByName($target_class);

        // abort if the target class is unknown.
        if (null === $target_cid) {
            throw new ilCtrlException("Class '$target_class' was not found in the control structure, try `composer du` to read artifacts.");
        }

        // if the target class is already the current command
        // class of this context, nothing has to be changed.
        if ($target_cid === $this->context->getPath()->getCurrentCid()) {
            return $this->context->getPath()->getCidPath();
        }

        // check if the target is related to a class within
        // the current context's path.
        $related_class_path = $this->getPathToRelatedClassInContext($this->context, $target_class);
        if (null !== $related_class_path) {
            return $this->appendCid($target_cid, $related_class_path);
        }

        // fix https://mantis.ilias.de/view.php?id=33094:
        // prioritise baseclasses less than relationships,
        // therefore test at last if the target class is a
        // baseclass.
        if ($this->structure->isBaseClass($target_class)) {
            return $target_cid;
        }

        throw new ilCtrlException("ilCtrl cannot find a path for '$target_class' that reaches '{$this->context->getBaseClass()}'");
    }
}

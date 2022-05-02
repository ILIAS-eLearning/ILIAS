<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlSingleClassPath
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
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

        // if the target class is a known baseclass the
        // class cid can be returned.
        if ($this->structure->isBaseClass($target_class)) {
            return $target_cid;
        }

        // if the target class is already the current command
        // class of this context, nothing has to be changed.
        if ($target_cid === $this->context->getPath()->getCurrentCid()) {
            return $this->context->getPath()->getCidPath();
        }

        // check if the target is related to a class within
        // the current context's path.
        $related_class_path = $this->getPathToRelatedClassInContext($this->context, $target_class);
        if (null === $related_class_path) {
            throw new ilCtrlException("ilCtrl cannot find a path for '$target_class' that reaches '{$this->context->getBaseClass()}'");
        }

        return $this->appendCid($target_cid, $related_class_path);
    }
}

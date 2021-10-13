<?php

/**
 * Class ilCtrlArrayClassPath
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlArrayClassPath extends ilCtrlAbstractPath
{
    /**
     * ilCtrlArrayClassPath Constructor
     *
     * @param ilCtrlStructureInterface $structure
     * @param string[]                 $target_classes
     */
    public function __construct(ilCtrlStructureInterface $structure, array $target_classes)
    {
        parent::__construct($structure);

        try {
            $this->cid_path = $this->getCidPathByArray($target_classes);
        } catch (ilCtrlException $exception) {
            $this->exception = $exception;
            $this->cid_path  = null;
        }
    }

    /**
     * Generates a cid path from the given class array.
     *
     * If the given class array does not contain a valid
     * path an according exception will be thrown.
     *
     * @param string[] $target_classes
     * @return string
     * @throws ilCtrlException if classes within the classes array
     *                         are not related.
     */
    private function getCidPathByArray(array $target_classes) : string
    {
        // abort if the target class (array) is empty or
        // the baseclass of the class array is unknown.
        if (empty($target_classes) || !$this->structure->isBaseClass($target_classes[0])) {
            throw new ilCtrlException("First class provided in array must be a known baseclass.");
        }

        $cid_path = $previous_class = null;
        foreach ($target_classes as $current_class) {
            $current_cid = $this->structure->getClassCidByName($current_class);

            // abort if the current class cannot be found.
            if (null === $current_cid) {
                throw new ilCtrlException("Class '$current_class' was not found in the control structure, try `composer du` to read artifacts.");
            }

            // abort if the current and previous classes are
            // not related.
            if (null !== $previous_class && !$this->isClassParentOf($previous_class, $current_class)) {
                throw new ilCtrlException("Class '$current_class' is not a child of '$previous_class'.");
            }

            $cid_path = $this->appendCid($current_cid, $cid_path);
            $previous_class = $current_class;
        }

        return $cid_path;
    }
}
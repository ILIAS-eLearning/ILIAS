<?php

//require_once 'Services/UICore/classes/Setup/class.ilCtrlStructureReader.php';
//
//$r = new ilCtrlStructureReader();
//$a = $r->readStructure();

class Test
{
    protected array $references;

    protected array $paths;

    private int $calls;

    public function __construct()
    {
        $this->references = include 'test_array.php';
        $this->calls = 0;
        $this->paths = [];
    }

    public function buildPaths() : void
    {
        foreach ($this->references as $class => $refs) {
            if ('iladvancedmdsettingsgui' === $class) {
                $k = 1;
            }

            $class_cid = $this->references[$class]['cid'];

            // add path entry in refs if not existent
            if (!isset($this->references[$class]['paths'])) {
                $this->references[$class]['paths'] = [];
            }

            $parents =& $refs['parents'];
            if (!empty($parents)) {
                foreach ($parents as $parent) {
                    $parent_cid = $this->references[$parent]['cid'];
                    $path = "$parent_cid:$class_cid";

                    $this->references[$class]['paths'][$path] = $parent;

                    if (!empty($this->references[$parent]['parents'])) {
                        $this->buildBranchPaths($class, $parent, $path);
                    }
                }
            }
        }
    }

    public function buildBranchPaths(string $origin, string $current_class, string $path) : void
    {
        $this->calls++;

        $origin_cid = $this->references[$origin]['cid'];
        $current_cid = $this->references[$current_class]['cid'];
        $cid_array = explode(':', $path);

        $parents =& $this->references[$current_class]['parents'];
        if (!empty($parents)) {
            foreach ($parents as $parent) {
                $parent_cid = $this->references[$parent]['cid'];
                if (in_array($parent_cid, $cid_array, true)) {
                    $curr_path = "$current_cid:" . str_replace([$origin_cid, $current_cid], ['', $origin_cid], $path);
                    $this->references[$origin]['paths'][$curr_path] = 'vise-versa';
                } else {
                    $curr_path = "$parent_cid:$path";
                    $this->references[$origin]['paths'][$curr_path] = $parent;
                    $upper_parents =& $this->references[$parent]['parents'];
                    if (!empty($upper_parents)) {
                        foreach ($upper_parents as $upper_parent) {
                            if (!empty($this->references[$upper_parent]['parents'])) {
                                $this->buildBranchPaths($origin, $parent, $curr_path);
                            }
                        }
                    }
                }
            }
        }
    }

    public function gatherParents() : void
    {
        $parents = [];
        foreach ($this->references as $class => $refs) {
            if ('iladvancedmdsettingsgui' === $class) {
                $k = 1;
            }

            $curr_parents = $this->getParentsRecursively($class);
            if (!empty($curr_parents)) {
                $parents[$class] = $curr_parents;
                $x = 2;
            }
        }

        $x = 1;
    }

    public function getParentsRecursively(string $target_class, string $current_path = null) : array
    {
        $target_class_parents =& $this->references[$target_class]['parents'];
        $target_class_cid     =& $this->references[$target_class]['cid'];

        // abort if an infinite loop might happen.
        $cid_array = explode(':', $current_path);
        if (in_array($target_class_cid, $cid_array, true)) {
            return [];
        }

        if (2 <= count($cid_array) && $target_class_cid === $cid_array[1]) {
            $path = "$target_class_cid:" . str_replace(":$target_class_cid", '', $current_path);
            return [
                $path => null,
            ];
        }

        // abort if the target cid cannot be found or
        // if the target class is an orphan.
        if (empty($target_class_parents)) {
            return [];
        }

        // initialize the current path if not provided,
        // else append the target class cid.
        $current_path = (null !== $current_path) ?
            $this->prependPath($current_path, $target_class_cid) :
            $target_class_cid
        ;

        // map the target classes parents to the current path
        $parents[$current_path] = $target_class_parents;

        // fetch derived parents for all parent objects of the
        // current target class.
        foreach ($parents[$current_path] as $parent_class) {
            // only process parent class if it exists (in the
            // control structure).
            $parent_class_cid =& $this->references[$parent_class]['cid'];
            if (null !== $parent_class_cid) {
                $parent_class_parents = $this->getParentsRecursively($parent_class, $current_path);
                if (!empty($parent_class_parents)) {
                    // if the parent has further parents, map them to their path.
                    foreach ($parent_class_parents as $parent_path => $parent) {
                        $parents[$parent_path] = $parent;
                    }
                } else {
                    // if the parent class is an orphan, set null mapped
                    // to the parent's path.
                    $parents[$this->prependPath($current_path, $parent_class_cid)] = null;
                }
            }
        }

        return $parents;
    }

    private function prependPath(string $path, string $cid) : string
    {
        return "$cid:$path";
    }
}

$t = new Test();
$t->gatherParents();

exit;
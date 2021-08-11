<?php

class ilPHPOnlyFilterIterator extends RecursiveFilterIterator
{


    /**
     * @inheritDoc
     */
    public function accept()
    {
        $current = $this->current();
        return $current->isFile();// && preg_match("/\.php$/ui", $this->current()->getFilename());
    }

    public function __toString()
    {
        return $this->current()->getPathname();
    }
}

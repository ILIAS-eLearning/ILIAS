<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlDirectoryIterator
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlDirectoryIterator implements ilCtrlIteratorInterface
{
    /**
     * @var string regex pattern that matches the name between
     *             two dots. This assumes the naming is properly
     *             done - that classnames match filenames.
     */
    private const CLASS_NAME_REGEX = '/(?<=\.)(.*)(?=\.)/';

    /**
     * @var Iterator
     */
    private Iterator $iterator;

    /**
     * ilCtrlDirectoryIterator Constructor
     *
     * @param string $directory_path
     */
    public function __construct(string $directory_path)
    {
        $this->iterator = new RecursiveDirectoryIterator(
            $directory_path,
             FilesystemIterator::KEY_AS_PATHNAME |
            FilesystemIterator::CURRENT_AS_FILEINFO |
            FilesystemIterator::SKIP_DOTS
        );

        $this->iterator = new RecursiveIteratorIterator(
            $this->iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    /**
     * @inheritDoc
     */
    public function current() : string
    {
        return $this->iterator->key();
    }

    /**
     * @inheritDoc
     */
    public function next() : void
    {
        $this->iterator->next();
    }

    /**
     * @inheritDoc
     */
    public function key() : string
    {
        // at this point, the current filename already passed the
        // ILIAS naming-convention check, therefore the following
        // statements will return the proper object name.
        preg_match(self::CLASS_NAME_REGEX, $this->getCurrentFileName(), $matches);
        return $matches[0];
    }

    /**
     * Returns true if there are files left that match the regex of
     * @see ilCtrlStructureReader::REGEX_GUI_CLASS_NAME.
     *
     * @inheritDoc
     */
    public function valid() : bool
    {
        $file_name = $this->getCurrentFileName();
        if (!preg_match(ilCtrlStructureReader::REGEX_GUI_CLASS_NAME, $file_name)) {
            // advance iterator and recursively check the filename
            // if the iterator is still valid.
            $this->next();
            if ($this->iterator->valid()) {
                return $this->valid();
            }

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rewind() : void
    {
        $this->iterator->rewind();
    }

    /**
     * Helper function that returns the filename of the current
     * file pointed to.
     *
     * @return string
     */
    private function getCurrentFileName() : string
    {
        /** @var $file_info SplFileInfo */
        $file_info = $this->iterator->current();
        return $file_info->getFilename();
    }
}
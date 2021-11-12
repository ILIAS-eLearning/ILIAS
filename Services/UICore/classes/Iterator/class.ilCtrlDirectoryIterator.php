<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilCtrlDirectoryIteratorTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlDirectoryIterator implements ilCtrlIteratorInterface
{
    /**
     * @var string regex pattern that matches the name between
     *             two dots. This assumes the naming is properly
     *             done - that classnames match filenames.
     */
    private const CLASS_NAME_REGEX = '/(?<=\.)(.*)(?=\.)/';

    /**
     * @var array<string, SplFileInfo>
     */
    private array $data;

    /**
     * ilCtrlDirectoryIteratorTest Constructor
     *
     * @param string $directory_path
     */
    public function __construct(string $directory_path)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory_path,
                FilesystemIterator::KEY_AS_PATHNAME |
                FilesystemIterator::CURRENT_AS_FILEINFO |
                FilesystemIterator::SKIP_DOTS
            )
        );

        // we do this in order to sort the files alphabetically,
        // if we were to iterate over the recursive iterator the
        // order would be (rather) arbitrary.
        $this->data = iterator_to_array($iterator);
        ksort($this->data);
    }

    /**
     * @inheritDoc
     */
    public function current() : ?string
    {
        $value = current($this->data);
        if ($value instanceof SplFileInfo) {
            return $value->getRealPath();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function next() : void
    {
        next($this->data);
    }

    /**
     * @inheritDoc
     */
    public function key() : ?string
    {
        if ($this->valid()) {
            preg_match(self::CLASS_NAME_REGEX, $this->getCurrentFileName(), $matches);
            return $matches[0] ?? null;
        }

        return null;
    }

    /**
     * Returns true if there are files left that match the regex of
     * @see ilCtrlStructureReader::REGEX_GUI_CLASS_NAME.
     *
     * @inheritDoc
     */
    public function valid() : bool
    {
        // if the current key is null the iterator is finished.
        if (null === key($this->data)) {
            return false;
        }

        $file_name = $this->getCurrentFileName();
        if (!preg_match(ilCtrlStructureReader::REGEX_GUI_CLASS_NAME, $file_name)) {
            // advance iterator and recursively check the filename
            // if the iterator is still valid.
            $this->next();
            if (null !== key($this->data)) {
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
        reset($this->data);
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
        $file_info = current($this->data);
        return $file_info->getFilename();
    }
}
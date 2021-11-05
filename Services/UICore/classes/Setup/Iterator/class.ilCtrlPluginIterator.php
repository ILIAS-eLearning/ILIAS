<?php

/**
 * Class ilCtrlPluginIterator
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlPluginIterator implements Iterator
{
    /**
     * @var Iterator
     */
    private Iterator $iterator;

    /**
     * ilCtrlPluginIterator Constructor
     */
    public function __construct()
    {
        $this->iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                __DIR__ . "/../../../../../Customizing/global/plugins/",
                FilesystemIterator::SKIP_DOTS
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function current() : string
    {
        /** @var $file_info SplFileInfo */
        $file_info = $this->iterator->current();
        return $file_info->getPath();
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
     *
     * @throws ilCtrlException if the plugin directory does not contain
     *                         a valid plugin.php file.
     */
    public function key() : string
    {
        /** @var $file_info SplFileInfo */
        $file_info = $this->iterator->current();
        $file_path = $file_info->getPath();

        try {
            require $file_info->getPath() . '/' . $file_info->getFilename();
            if (!isset($id)) {
                throw new ilCtrlException("Plugin directory '$file_path' does not contain a valid 'plugin.php' file.");
            }
        } catch (Throwable $t) {
            throw new ilCtrlException("Plugin directory '$file_path' does not contain a 'plugin.php' file.");
        }

        return $id;
    }

    /**
     * @inheritDoc
     */
    public function valid() : bool
    {
        /** @var $file_info SplFileInfo */
        $file_info = $this->iterator->current();
        if ('plugin.php' !== $file_info->getFilename()) {
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
}
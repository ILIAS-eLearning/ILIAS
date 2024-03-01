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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilAssQuestionProcessLockFileStorage extends ilFileSystemAbstractionStorage
{
    private string $sub_path;

    public function __construct(int $question_id, int $user_id)
    {
        parent::__construct(ilFileSystemAbstractionStorage::STORAGE_DATA, true, $question_id);

        $this->initSubPath($user_id);
    }

    /**
     * Get path prefix. Prefix that will be prepended to the path
     * No trailing slash. E.g ilFiles for files
     *
     * @access protected
     *
     * @return string path prefix e.g files
     */
    protected function getPathPrefix(): string
    {
        return 'ilAssQuestionProcessLocks';
    }

    /**
     * Get directory name. E.g for files => file
     * Only relative path, no trailing slash
     * '_<obj_id>' will be appended automatically
     *
     * @access protected
     *
     * @return string directory name
     */
    protected function getPathPostfix(): string
    {
        return 'question';
    }

    public function getPath(): string
    {
        return parent::getPath() . '/' . $this->sub_path;
    }

    public function create(): void
    {
        set_error_handler(function ($severity, $message, $file, $line): void {
            throw new ErrorException($message, $severity, 0, $file, $line);
        });

        try {
            parent::create($this->getPath());
            restore_error_handler();
        } catch (Exception $e) {
            restore_error_handler();
        }

        if (!$this->getFileSystemService()->has($this->path)) {
            throw new ErrorException(sprintf('Could not find directory: %s', $this->getPath()));
        }
    }

    private function initSubPath(int $user_id): void
    {
        $user_id = (string) $user_id;

        $path = array();

        for ($i = 0, $max = strlen($user_id); $i < $max; $i++) {
            $path[] = substr($user_id, $i, 1);
        }

        $this->sub_path = implode('/', $path);
    }
}

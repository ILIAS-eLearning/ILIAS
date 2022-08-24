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
 * Class ilWorkflowDefinitionRepository
 */
class ilWorkflowDefinitionRepository
{
    private const FILE_EXTENSTION_BPMN2 = 'bpmn2';
    private const FILE_EXTENSTION_PHP = 'php';
    private const FILE_PREFIX = 'wsd.il';

    protected ilDBInterface $db;
    protected \ILIAS\Filesystem\FilesystemsImpl $fs;
    protected string $path;
    protected bool $definitionsLoaded = false;
    /**
     * @var array
     */
    protected array $definitions = [];

    public function __construct(ilDBInterface $db, \ILIAS\Filesystem\Filesystems $fs, string $path)
    {
        $this->db = $db;
        $this->fs = $fs;
        $this->path = $path;
    }

    protected function lazyLoadWorkflowDefinitions(): void
    {
        if ($this->definitionsLoaded) {
            return;
        }
        $this->definitionsLoaded = true;

        $query = 'SELECT workflow_class, COUNT(workflow_id) total, SUM(active) active
				  FROM wfe_workflows
				  GROUP BY workflow_class';
        $result = $this->db->query($query);

        $stats = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $stats[$row['workflow_class']] = ['total' => $row['total'], 'active' => $row['active']];
        }

        if (!$this->fs->storage()->hasDir($this->path)) {
            $this->definitions = [];
            return;
        }

        $contents = $this->fs->storage()->listContents($this->path, false);
        $contents = array_filter($contents, static function (ILIAS\Filesystem\DTO\Metadata $file): bool {
            if (!$file->isFile()) {
                return false;
            }

            $fileParts = pathinfo($file->getPath());

            return $fileParts['extension'] === self::FILE_EXTENSTION_BPMN2;
        });

        $prefixLength = strlen(self::FILE_PREFIX);

        $definitions = [];
        foreach ($contents as $file) {
            $fileParts = pathinfo($file->getPath());
            $extensionLength = strlen($fileParts['extension']) + 1;

            $definition = [];

            $definition['file'] = $fileParts['basename'];
            $definition['id'] = $fileParts['filename'];

            $parts = explode('_', substr($fileParts['basename'], $prefixLength, $extensionLength * -1));

            $definition['status'] = 1;
            if (!$this->fs->storage()->has($this->path . '/' . $definition['id'] . '.php')) {
                $definition['status'] = 0;
            }
            $definition['version'] = substr(array_pop($parts), 1);
            $definition['title'] = implode(' ', $parts);
            $definition['instances'] = $stats[$definition['id'] . '.php'];

            $definitions[$definition['id']] = $definition;
        }

        $this->definitions = $definitions;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $this->lazyLoadWorkflowDefinitions();
        return $this->definitions;
    }

    public function has(string $id): bool
    {
        $this->lazyLoadWorkflowDefinitions();
        return isset($this->definitions[$id]);
    }

    /**
     * @param string $id
     * @return array
     * @throws ilWorkflowEngineException
     */
    public function getById(string $id): array
    {
        $this->lazyLoadWorkflowDefinitions();
        if (!$this->has($id)) {
            throw new ilWorkflowEngineException(sprintf("Could not find definition for id: %s", $id));
        }

        return $this->definitions[$id];
    }
}

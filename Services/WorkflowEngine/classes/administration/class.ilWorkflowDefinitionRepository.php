<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilWorkflowDefinitionRepository
 */
class ilWorkflowDefinitionRepository
{
    const FILE_EXTENSTION_BPMN2 = 'bpmn2';
    const FILE_EXTENSTION_PHP = 'php';

    const FILE_PREFIX = 'wsd.il';

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ILIAS\Filesystem\FilesystemsImpl
     */
    protected $fs;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $definitionsLoaded = false;

    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * ilWorkflowDefinitionRepository constructor.
     * @param ilDBInterface                 $db
     * @param \ILIAS\Filesystem\Filesystems $fs
     * @param string $path
     */
    public function __construct(\ilDBInterface $db, \ILIAS\Filesystem\Filesystems $fs, $path)
    {
        $this->db = $db;
        $this->fs = $fs;
        $this->path = $path;
    }

    /**
     *
     */
    protected function lazyLoadWorkflowDefinitions()
    {
        if ($this->definitionsLoaded) {
            return;
        }
        $this->definitionsLoaded = true;

        $query = 'SELECT workflow_class, COUNT(workflow_id) total, SUM(active) active
				  FROM wfe_workflows
				  GROUP BY workflow_class';
        $result = $this->db->query($query);

        $stats = array();
        while ($row = $this->db->fetchAssoc($result)) {
            $stats[$row['workflow_class']] = array('total' => $row['total'], 'active' => $row['active']);
        }

        if (!$this->fs->storage()->hasDir($this->path)) {
            $this->definitions = array();
            return;
        }

        $contents = $this->fs->storage()->listContents($this->path, false);
        $contents = array_filter($contents, function (ILIAS\Filesystem\DTO\Metadata $file) {
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
    public function getAll()
    {
        $this->lazyLoadWorkflowDefinitions();
        return $this->definitions;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        $this->lazyLoadWorkflowDefinitions();
        return isset($this->definitions[$id]);
    }

    /**
     * @param string $id
     * @return array
     * @throws \ilWorkflowEngineException
     */
    public function getById($id)
    {
        $this->lazyLoadWorkflowDefinitions();
        if (!$this->has($id)) {
            throw new \ilWorkflowEngineException(sprintf("Could not find definition for id: %s", $id));
        }

        return $this->definitions[$id];
    }
}

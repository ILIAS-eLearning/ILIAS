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

declare(strict_types=1);

namespace ILIAS\Tree;

use ILIAS\Logging\Logger\LoggerInterface;
use ILIAS\Logging\Logger\LoggerFactory;
use ILIAS\Language\Language;
use ILIAS\Administration\Setting;

/**
 * @template-extends \ilTree
 */

/**
 * Builder class for ilTree instances.
 * Provides a fluent interface for constructing tree instances with various configurations.
 *
 * @author ILIAS Development
 * @ingroup ServicesTree
 */
class TreeBuilder
{
    private int $tree_id;
    private int $root_id = 0;
    private ?\ilDBInterface $db = null;
    private ?LoggerInterface $logger = null;
    private ?Setting $setting = null;
    private ?\ilAppEventHandler $event_handler = null;
    private ?\ilObjectDataCache $obj_data_cache = null;
    private ?\ilObjectDefinition $obj_definition = null;
    private ?\ilObjUser $user = null;
    private ?Language $language = null;

    /**
     * Create a new TreeBuilder instance.
     *
     * @param int $tree_id The tree identifier (required)
     */
    public function __construct(int $tree_id)
    {
        $this->tree_id = $tree_id;
    }

    /**
     * Set the tree identifier.
     *
     * @param int $tree_id Tree identifier
     * @return $this
     */
    public function withTreeId(int $tree_id): self
    {
        $this->tree_id = $tree_id;
        return $this;
    }

    /**
     * Set the root node identifier.
     *
     * @param int $root_id Root node identifier (defaults to ROOT_FOLDER_ID if 0)
     * @return $this
     */
    public function withRootId(int $root_id): self
    {
        $this->root_id = $root_id;
        return $this;
    }

    /**
     * Set the database interface.
     *
     * @param \ilDBInterface $db Database interface
     * @return $this
     */
    public function withDatabase(\ilDBInterface $db): self
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Set the logger.
     *
     * @param LoggerInterface $logger Logger instance
     * @return $this
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Set the settings.
     *
     * @param Setting $setting Settings instance
     * @return $this
     */
    public function withSetting(Setting $setting): self
    {
        $this->setting = $setting;
        return $this;
    }

    /**
     * Set the event handler.
     *
     * @param \ilAppEventHandler $event_handler Event handler
     * @return $this
     */
    public function withEventHandler(\ilAppEventHandler $event_handler): self
    {
        $this->event_handler = $event_handler;
        return $this;
    }

    /**
     * Set the object data cache.
     *
     * @param \ilObjectDataCache $obj_data_cache Object data cache
     * @return $this
     */
    public function withObjectDataCache(\ilObjectDataCache $obj_data_cache): self
    {
        $this->obj_data_cache = $obj_data_cache;
        return $this;
    }

    /**
     * Set the object definition.
     *
     * @param \ilObjectDefinition $obj_definition Object definition
     * @return $this
     */
    public function withObjectDefinition(\ilObjectDefinition $obj_definition): self
    {
        $this->obj_definition = $obj_definition;
        return $this;
    }

    /**
     * Set the current user.
     *
     * @param \ilObjUser $user User object
     * @return $this
     */
    public function withUser(\ilObjUser $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Set the language handler.
     *
     * @param Language $language Language instance
     * @return $this
     */
    public function withLanguage(Language $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Configure the builder for a setup environment with minimal dependencies.
     * This is a convenience method that sets up the builder with null/fallback values
     * for dependencies that may not be available in setup contexts.
     *
     * @param \ilDBInterface $db Required database connection
     * @return $this
     */
    public function forSetupEnvironment(\ilDBInterface $db, Setting $setting): self
    {
        $this->db = $db;
        $this->setting = $setting;
        $this->logger = new \ILIAS\Tree\Logging\NullLogger();
        $this->language = new \ILIAS\Tree\Language\NullLanguage();
        return $this;
    }

    /**
     * Configure the builder for a main tree (default repository tree).
     *
     * @return $this
     */
    public function forMainTree(): self
    {
        $this->root_id = defined("ROOT_FOLDER_ID") ? ROOT_FOLDER_ID : 0;

        return $this;
    }

    /**
     * Configure the builder for a subtree.
     *
     * @param int $root_id The root node ID for the subtree
     * @return $this
     */
    public function forSubTree(int $root_id): self
    {
        $this->root_id = $root_id;
        return $this;
    }

    /**
     * Build and return the ilTree instance.
     *
     * @return \ilTree The constructed tree instance
     */
    public function build(): \ilTree
    {
        return new \ilTree(
            $this->tree_id,
            $this->root_id,
            $this->db,
            $this->logger,
            $this->setting,
            $this->event_handler,
            $this->obj_data_cache,
            $this->obj_definition,
            $this->user,
            $this->language
        );
    }
}

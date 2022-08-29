<?php

declare(strict_types=1);

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
 * Class ilMailTemplateContextService
 */
class ilMailTemplateContextService
{
    /**
     * @param string[] $a_new_templates
     */
    public static function clearFromXml(string $a_component, array $a_new_templates): void
    {
        global $DIC;
        if (!$DIC->database()->tableExists('mail_tpl_ctx')) {
            return;
        }
        $persisted_templates = [];
        $query = 'SELECT id FROM mail_tpl_ctx WHERE component = ' . $DIC->database()->quote($a_component, 'text');
        $set = $DIC->database()->query($query);
        while ($row = $DIC->database()->fetchAssoc($set)) {
            $persisted_templates[] = $row['id'];
        }

        if ($persisted_templates !== []) {
            if ($a_new_templates !== []) {
                foreach ($persisted_templates as $id) {
                    if (!in_array($id, $a_new_templates, true)) {
                        $DIC->database()->manipulate(
                            'DELETE FROM mail_tpl_ctx WHERE component = ' . $DIC->database()->quote(
                                $a_component,
                                'text'
                            ) . ' AND id = ' . $DIC->database()->quote($id, 'text')
                        );
                    }
                }
            } else {
                $DIC->database()->manipulate(
                    'DELETE FROM mail_tpl_ctx WHERE component = ' . $DIC->database()->quote(
                        $a_component,
                        'text'
                    )
                );
            }
        }
    }

    public static function insertFromXML(string $a_component, string $a_id, string $a_class, ?string $a_path): void
    {
        global $DIC;

        if (!$DIC->database()->tableExists('mail_tpl_ctx')) {
            return;
        }

        $context = self::getContextInstance($a_component, $a_id, $a_class, $a_path, true);
        if ($context instanceof ilMailTemplateContext) {
            self::createEntry($context, $a_component, $a_class, $a_path);
        }
    }

    /**
     * @throws ilMailException
     */
    public static function getTemplateContextById(string $a_id): ilMailTemplateContext
    {
        $contexts = self::getTemplateContexts([$a_id]);
        $first_context = current($contexts);
        if (!($first_context instanceof ilMailTemplateContext) || $first_context->getId() !== $a_id) {
            throw new ilMailException(sprintf("Could not find a mail template context with id: %s", $a_id));
        }

        return $first_context;
    }

    /**
     * Returns an array of mail template contexts, the key of each entry matches its id
     * @param string[] $a_id
     * @return ilMailTemplateContext[]
     */
    public static function getTemplateContexts(?array $a_id = null): array
    {
        global $DIC;
        $templates = [];

        $query = 'SELECT * FROM mail_tpl_ctx';
        $where = [];
        if (is_array($a_id) && count($a_id)) {
            $where[] = $DIC->database()->in('id', $a_id, false, 'text');
        }
        if ($where !== []) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }

        $set = $DIC->database()->query($query);
        while ($row = $DIC->database()->fetchAssoc($set)) {
            $context = self::getContextInstance($row['component'], $row['id'], $row['class'], $row['path']);
            if ($context instanceof ilMailTemplateContext) {
                $templates[$context->getId()] = $context;
            }
        }

        return $templates;
    }

    protected static function getContextInstance(
        string $a_component,
        string $a_id,
        string $a_class,
        ?string $a_path,
        bool $isCreationContext = false
    ): ?ilMailTemplateContext {
        if (!$a_path) {
            $a_path = $a_component . '/classes/';
        }
        $class_file = $a_path . 'class.' . $a_class . '.php';

        if (class_exists($a_class) && file_exists($class_file)) {
            if ($isCreationContext) {
                $reflClass = new ReflectionClass($a_class);
                $context = $reflClass->newInstanceWithoutConstructor();
            } else {
                $context = new $a_class();
            }

            if (($context instanceof ilMailTemplateContext) && $context->getId() === $a_id) {
                return $context;
            }
        }
        return null;
    }

    protected static function createEntry(
        ilMailTemplateContext $a_context,
        string $a_component,
        string $a_class,
        ?string $a_path
    ): void {
        global $DIC;

        $query = "SELECT id FROM mail_tpl_ctx WHERE id = %s";
        $res = $DIC->database()->queryF($query, ['text'], [$a_context->getId()]);
        $row = $DIC->database()->fetchAssoc($res);
        $row_id = $row['id'] ?? null;
        $context_exists = ($row_id === $a_context->getId());

        if (!$context_exists) {
            $DIC->database()->insert('mail_tpl_ctx', [
                'id' => ['text', $a_context->getId()],
                'component' => ['text', $a_component],
                'class' => ['text', $a_class],
                'path' => ['text', $a_path]
            ]);
        } else {
            $DIC->database()->update('mail_tpl_ctx', [
                'component' => ['text', $a_component],
                'class' => ['text', $a_class],
                'path' => ['text', $a_path]
            ], [
                'id' => ['text', $a_context->getId()]
            ]);
        }
    }
}

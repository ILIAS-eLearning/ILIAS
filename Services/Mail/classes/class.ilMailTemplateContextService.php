<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplateContextService
 */
class ilMailTemplateContextService
{
    /**
     * @param string $a_component
     * @param array $a_new_templates
     */
    public static function clearFromXml($a_component, array $a_new_templates)
    {
        global $DIC;

        if (!$DIC->database()->tableExists('mail_tpl_ctx')) {
            return;
        }

        $persisted_templates = array();
        $query = 'SELECT id FROM mail_tpl_ctx WHERE component = ' . $DIC->database()->quote($a_component, 'text');
        $set = $DIC->database()->query($query);
        while ($row = $DIC->database()->fetchAssoc($set)) {
            $persisted_templates[] = $row['id'];
        }

        if (count($persisted_templates)) {
            if (count($a_new_templates)) {
                foreach ($persisted_templates as $id) {
                    if (!in_array($id, $a_new_templates)) {
                        $DIC->database()->manipulate(
                            'DELETE FROM mail_tpl_ctx WHERE component = ' . $DIC->database()->quote(
                                $a_component,
                                'text'
                            ) . ' AND id = ' . $DIC->database()->quote($id, 'text')
                        );
                        $DIC['ilLog']->debug("Mail Template XML - Context " . $id . " in class " . $a_component . " deleted.");
                    }
                }
            } else {
                $DIC->database()->manipulate('DELETE FROM mail_tpl_ctx WHERE component = ' . $DIC->database()->quote(
                    $a_component,
                    'text'
                ));
                $DIC['ilLog']->debug("Mail Template XML - All contexts deleted for " . $a_component . " as component is inactive.");
            }
        }
    }

    /**
     * @param string $a_component
     * @param string $a_id
     * @param string $a_class
     * @param string $a_path
     */
    public static function insertFromXML($a_component, $a_id, $a_class, $a_path)
    {
        global $DIC;

        if (!$DIC->database()->tableExists('mail_tpl_ctx')) {
            return;
        }

        $context = self::getContextInstance($a_component, $a_id, $a_class, $a_path);
        if ($context instanceof ilMailTemplateContext) {
            self::createEntry($context, $a_component, $a_class, $a_path);
        }
    }

    /**
     * @param string $a_id
     * @return ilMailTemplateContext
     * @throws ilMailException
     */
    public static function getTemplateContextById($a_id)
    {
        $contexts = self::getTemplateContexts($a_id);
        $first_context = current($contexts);
        if (!($first_context instanceof ilMailTemplateContext) || $first_context->getId() != $a_id) {
            require_once 'Services/Mail/exceptions/class.ilMailException.php';
            throw new ilMailException(sprintf("Could not find a mail template context with id: %s", $a_id));
        }
        return $first_context;
    }

    /**
     * Returns an array of mail template contexts, the key of each entry matches its id
     * @param null|string|array $a_id
     * @return ilMailTemplateContext[]
     */
    public static function getTemplateContexts($a_id = null)
    {
        global $DIC;

        $templates = array();

        if ($a_id && !is_array($a_id)) {
            $a_id = array($a_id);
        }

        $query = 'SELECT * FROM mail_tpl_ctx';
        $where = array();
        if ($a_id) {
            $where[] = $DIC->database()->in('id', $a_id, false, 'text');
        }
        if (count($where)) {
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


    /**
     * @param string $a_component
     * @param string $a_id
     * @param string $a_class
     * @param string $a_path
     * @return null|ilMailTemplateContext
     */
    protected static function getContextInstance($a_component, $a_id, $a_class, $a_path)
    {
        global $DIC;

        $mess = '';

        if (!$a_path) {
            $a_path = $a_component . '/classes/';
        }
        $class_file = $a_path . 'class.' . $a_class . '.php';

        if (file_exists($class_file)) {
            require_once $class_file;
            if (class_exists($a_class)) {
                $context = new $a_class();
                if ($context instanceof ilMailTemplateContext) {
                    if ($context->getId() == $a_id) {
                        return $context;
                    } else {
                        $mess .= " - context id mismatch";
                    }
                } else {
                    $mess .= " - does not extend ilMailTemplateContext";
                }
            } else {
                $mess = "- class not found in file";
            }
        } else {
            $mess = " - class file not found";
        }

        $DIC['ilLog']->debug("Mail Template XML - Context " . $a_id . " in class " . $a_class . " (" . $class_file . ") is invalid." . $mess);
    }

    /**
     * @param ilMailTemplateContext $a_context
     * @param string $a_component
     * @param string $a_class
     * @param string $a_path
     */
    protected static function createEntry(ilMailTemplateContext $a_context, $a_component, $a_class, $a_path)
    {
        global $DIC;

        $query = "SELECT id FROM mail_tpl_ctx WHERE id = %s";
        $res = $DIC->database()->queryF($query, array('text'), array($a_context->getId()));
        $row = $DIC->database()->fetchAssoc($res);
        $context_exists = ($row['id'] == $a_context->getId());

        if (!$context_exists) {
            $DIC->database()->insert('mail_tpl_ctx', array(
                'id' => array('text', $a_context->getId()),
                'component' => array('text', $a_component),
                'class' => array('text', $a_class),
                'path' => array('text', $a_path)
            ));

            $DIC['ilLog']->debug("Mail Template  XML - Context " . $a_context->getId() . " in class " . $a_class . " added.");
        } else {
            $DIC->database()->update('mail_tpl_ctx', array(
                'component' => array('text', $a_component),
                'class' => array('text', $a_class),
                'path' => array('text', $a_path)
            ), array(
                'id' => array('text', $a_context->getId())
            ));

            $DIC['ilLog']->debug("Mail Template  XML - Context " . $a_context->getId() . " in class " . $a_class . " updated.");
        }
    }
}

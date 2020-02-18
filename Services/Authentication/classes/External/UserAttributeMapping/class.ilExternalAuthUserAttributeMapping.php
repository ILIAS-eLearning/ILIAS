<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Authentication/classes/External/UserAttributeMapping/class.ilExternalAuthUserAttributeMappingRule.php';

/**
 * Class ilExternalAuthUserAttributeMapping
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilExternalAuthUserAttributeMapping implements ArrayAccess, Countable, Iterator
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var string
     */
    protected $authMode = '';

    /**
     * @var int
     */
    protected $authSourceId;

    /**
     * @var ilExternalAuthUserAttributeMappingRule[]
     */
    protected $mapping = array();

    /**
     * ilExternalAuthUserAttributeMapping constructor.
     * @param string $authMode
     * @param int    $authSourceId
     */
    public function __construct($authMode, $authSourceId = 0)
    {
        assert(is_string($authMode));
        assert(is_numeric($authSourceId));

        $this->db = $GLOBALS['DIC']->database();

        $this->setAuthMode($authMode);
        $this->setAuthSourceId($authSourceId);

        $this->read();
    }

    /**
     * @return int
     */
    public function getAuthSourceId()
    {
        return $this->authSourceId;
    }

    /**
     * @param int $authSourceId
     */
    public function setAuthSourceId($authSourceId)
    {
        $this->authSourceId = $authSourceId;
    }

    /**
     * @return string
     */
    public function getAuthMode()
    {
        return $this->authMode;
    }

    /**
     * @param string $authMode
     */
    public function setAuthMode($authMode)
    {
        $this->authMode = $authMode;
    }

    /**
     * @return ilExternalAuthUserAttributeMappingRule
     */
    public function getEmptyRule()
    {
        return new ilExternalAuthUserAttributeMappingRule();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->mapping[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->mapping[$offset] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->mapping[] = $value;
        } else {
            $this->mapping[$offset] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->mapping[$offset]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->mapping);
    }

    /**
     * @return ilExternalAuthUserAttributeMappingRule
     */
    public function current()
    {
        return current($this->mapping);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->mapping);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->mapping);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return current($this->mapping);
    }

    public function rewind()
    {
        reset($this->mapping);
    }

    /**
     *
     */
    protected function read()
    {
        $this->mapping = array();

        $res = $this->db->queryF(
            'SELECT * FROM auth_ext_attr_mapping WHERE auth_mode = %s AND auth_src_id = %s',
            array('text', 'integer'),
            array($this->getAuthMode(), $this->getAuthSourceId())
        );
        while ($row = $this->db->fetchAssoc($res)) {
            $rule = $this->getEmptyRule();
            $rule->setAttribute($row['attribute']);
            $rule->setExternalAttribute($row['ext_attribute']);
            $rule->updateAutomatically((bool) $row['update_automatically']);

            $this->mapping[$rule->getAttribute()] = $rule;
        }
    }

    /**
     *
     */
    public function save()
    {
        foreach ($this->mapping as $rule) {
            $this->db->replace(
                'auth_ext_attr_mapping',
                array(
                    'auth_mode'   => array('text', $this->getAuthMode()),
                    'auth_src_id' => array('integer', $this->getAuthSourceId()),
                    'attribute'   => array('text', $rule->getAttribute())
                ),
                array(
                    'ext_attribute'        => array('text', $rule->getExternalAttribute()),
                    'update_automatically' => array('integer', (int) $rule->isAutomaticallyUpdated())
                )
            );
        }
    }

    /**
     *
     */
    public function delete()
    {
        $this->mapping = array();
        $this->db->manipulateF(
            'DELETE FROM auth_ext_attr_mapping WHERE auth_mode = %s AND auth_src_id = %s',
            array('text', 'integer'),
            array($this->getAuthMode(), $this->getAuthSourceId())
        );
    }
}

<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Representation of html entities. 
 */

require_once("formlets/checking.php");

final class HTMLEntity {
    private $_name; // string
    private $_attributes; // string
    private $_content; //

    public function name() {
        return $this->_name;
    } 

    public function attributes() {
        return $this->_attributes;
    }

    public function attribute($name, $value = null) {
        if ($value === null) {
            if (array_key_exists($name, $this->_attributes)) 
                return $this->_attributes[$name];
            else
                return null;
        }
        else {
            guardIsString($name);
            guardIsString($value);
            return $this->_attribute($this->attributes(), $name, $value);    
        }
    }

    private function _attribute($attributes, $name, $value) {
        $attributes[$name] = $value;
        return new HTMLEntity($this->name(), $attributes, $this->content());
    }

    public function content() {
        return $this->_content;
    }

    public function __construct($name, $attributes, $content) {
        if ($name !== null)
            guardIsString($name);
        guardIsArray($attributes);
        foreach($attributes as $key => $value) {
            guardIsString($key);
        }
        if (!is_string($content) && $content !== null) {
            $content = flatten($content);
            guardIsArray($content);
            foreach($content as $value) {
                guardIsHTMLEntity($value);
            }
        }
        $this->_name = $name;
        $this->_attributes = $attributes;
        $this->_content = $content;
    }

    public function concat(HTMLEntity $right) {
        return new HTMLEntity (null, array(), array($this, $right));
    }

    public function render() {
        return $this->renderWithOptions(true, false); 
    }

    public function renderWithOptions($fallback_tag, $force_tag) {
        if ($this->content() !== null)
            $content = []; 
        else
            $content = null;

        if (is_string($this->content())) {
            $content[] = $this->content();
        }
        elseif ($this->content() !== null) {
            foreach($this->content() as $cont) {
                if (is_string($cont))
                    $content[] = $cont;
                else
                    $content[] = $cont->renderWithOptions($fallback_tag, $force_tag); 
            }
        }

        if ($this->name() !== null)
            return HTMLEntityRenderers::render( $this->name()
                                              , $this->attributes()
                                              , $content ? implode("", $content) : null
                                              , $fallback_tag
                                              , $force_tag
                                              );
        else
            return $content ? implode("", $content) : "";
    }
}

function tag($name, $attributes, $content = null) {
    return new HTMLEntity($name, $attributes, $content);
}

function literal($content) {
    guardIsString($content); 
    return new HTMLEntity(null, array(), $content);
}

/******************************************************************************
 * A registry for functions to render html tags.
 */

final class HTMLEntityRenderers {
    private static $_registry = array();
    
    public static function register($entity_name, $fn_name, $overwrite = false) {
        if (!$overwrite && array_key_exists($entity_name, self::$_registry)) {
            die("HTMLEntityRenderers::register: builder for $tag_name already registered."); 
        }
        self::$_registry[$entity_name] = $fn_name;
    }

    private static function registered($entity_name) {
        return array_key_exists($entity_name, self::$_registry);
    }

    private static function call($entity_name, $arr) {
        return call_user_func_array(self::$_registry[$entity_name], $arr);
    }

    public static function render($entity_name, $attributes, $content
                          , $fallback_tag, $force_tag) {
        if (   (!self::registered($entity_name) && $fallback_tag)  
            || $force_tag
           ) {
            if ($content !== null)
                return "<$entity_name".keysAndValuesToHTMLAttributes($attributes)." >"
                      .$content
                      ."</$entity_name>"
                      ;
            else 
                return "<$entity_name".keysAndValuesToHTMLAttributes($attributes)." />";
        }
        if (!self::registered($entity_name)) {
            die("HTMLEntityRenderers::render: no builder for $entity_name.");
        }
        $res = static::call($entity_name, array($attributes, $content));
        if ($res instanceof HTMLEntity) {
            return $res->renderWithOptions($fallback_tag, $force_tag); 
        }
        if (!is_string($res)) {
            die("HTMLEntityRenderers::render: builder for $entity_name does not return string.");
        }
        return $res;
    }
}

?>

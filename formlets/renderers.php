<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Standard renderers for html entities.
 */

function render_text_input($attributes, $content) {
    $attributes["type"] = "text";
    
    $label = null;
    if (array_key_exists("label", $attributes)) {
        $label = $attributes["label"];
        unset($attributes["label"]);
    }

    $errors = null;
    if (array_key_exists("errors", $attributes)) {
        $errors = $attributes["errors"];
        unset($attributes["errors"]);
    }

    $entity = tag("input", $attributes);
    if ($label !== null)
        $entity = labeled("text_input", $label, $entity);
    if ($errors !== null)
        $entity = append_errors($entity, $errors); 
    return $entity;
}
HTMLEntityRenderers::register("text_input", "render_text_input");


function render_checkbox($attributes, $content) {
    $attributes["type"] = "checkbox";

    $label = null;
    if (array_key_exists("label", $attributes)) {
        $label = $attributes["label"];
        unset($attributes["label"]);
    }
    
    $errors = null; 
    if (array_key_exists("errors", $attributes)) {
        $errors = $attributes["errors"];
        unset($attributes["errors"]);
    }
    
    $entity = tag("input", $attributes);
    if ($label !== null) 
        $entity = labeled("checkbox", $label, $entity);
    if ($errors !== null) 
        $entity = append_errors($entity, $errors); 
    return $entity;
}
HTMLEntityRenderers::register("checkbox", "render_checkbox");


function render_error($attributes, $content) {
    return "<span class='error'>$content</span>";
}
HTMLEntityRenderers::register("error", "render_error");


function render_submit_button($attributes, $content) {
    $attributes["type"] = "submit";
    return tag("input", $attributes);
}
HTMLEntityRenderers::register("submit_button", "render_submit_button");


function labeled($what, $label, HTMLEntity $entity) {
    $id = $entity->attribute("id");
    if ($id === null) {
        $id = $entity->attribute("name");
        $entity = $entity->attribute("id", $id);
    }     
    $l = tag("label", array("for" => $id), $label);
    if (in_array($what, array("checkbox")))
        return $entity->concat($l);
    else
        return $l->concat($entity);
}

function append_errors(HTMLEntity $entity, $errors) {
    foreach ($errors as $error) {
        $entity = $entity->concat(tag("error", array(), $error));
    }
    return $entity;
}

?>

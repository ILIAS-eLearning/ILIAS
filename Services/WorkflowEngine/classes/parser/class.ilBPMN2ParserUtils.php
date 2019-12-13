<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBPMN2ParserUtils
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilBPMN2ParserUtils
{
    #region XML to Array conversion
    // as per http://php.net/manual/en/simplexmlelement.children.php#100603

    /**
     * @param string $xml_string
     *
     * @return mixed
     */
    public function load_string($xml_string)
    {
        $node=@simplexml_load_string($xml_string);
        return $this->add_node($node);
    }

    /**
     * @param        $node
     * @param null   $parent
     * @param string $namespace
     * @param bool   $recursive
     *
     * @return mixed
     */
    private function add_node($node, &$parent=null, $namespace='', $recursive=false)
    {
        $namespaces = $node->getNameSpaces(true);
        $content="$node";

        $r['name']=$node->getName();
        if (!$recursive) {
            $tmp=array_keys($node->getNameSpaces(false));
            $r['namespace']=$tmp[0];
            $r['namespaces']=$namespaces;
        }
        if ($namespace) {
            $r['namespace']=$namespace;
        }
        if ($content) {
            $r['content']=$content;
        }

        foreach ($namespaces as $pre=>$ns) {
            foreach ($node->children($ns) as $k=>$v) {
                $this->add_node($v, $r['children'], $pre, true);
            }
            foreach ($node->attributes($ns) as $k=>$v) {
                $r['attributes'][$k]="$pre:$v";
            }
        }

        foreach ($node->children() as $k=>$v) {
            $this->add_node($v, $r['children'], '', true);
        }

        foreach ($node->attributes() as $k=>$v) {
            $r['attributes'][$k]="$v";
        }

        $parent[]=&$r;

        return $parent[0];
    }

    #endregion

    /**
     * @param string $xsID
     *
     * @return string
     */
    public static function xsIDToPHPVarname($xsID)
    {
        /*
         * The type xsd:ID is used for an attribute that uniquely identifies an element in an XML document. An xsd:ID
         * value must be an NCName. This means that it must start with a letter or underscore, and can only contain
         * letters, digits, underscores, hyphens, and periods.
         *
         * xsd:ID carries several additional constraints:
         *
         *    * Their values must be unique within an XML instance, regardless of the attribute's name or
         *      its element name.
         *    * A complex type cannot include more than one attribute of type xsd:ID, or any type derived from xsd:ID.
         *    * xsd:ID attributes cannot have default or fixed values specified.
         *
         * This differs from PHP variable name rules.
         * To overcome this, we need to address possible hyphens and periods in xsIDs, here they are replaced.
         */
        $xsID_converted = str_replace('.', '__period__', $xsID);
        $xsID_converted = str_replace('-', '__hyphen__', $xsID_converted);
        return $xsID_converted;
    }

    /**
     * @param srting $start_event_ref
     * @param string $type
     * @param array  $bpmn2_array
     *
     * @return array
     */
    public static function extractILIASEventDefinitionFromProcess($start_event_ref, $type, $bpmn2_array)
    {
        $descriptor_extension = array();
        $subject_extension    = array();
        $context_extension    = array();
        $timeframe_extension  = array();

        foreach ($bpmn2_array['children'] as $element) {
            if ($element['name'] == $type && $element['attributes']['id'] == $start_event_ref) {
                $bpmn_extension_elements = $element['children'][0];
                $extension_elements      = $bpmn_extension_elements['children'][0]['children'];

                foreach ($extension_elements as $child) {
                    $prefix = 'ilias:';
                    if ($child['namespace'] == 'ilias') {
                        $prefix = '';
                    }
                    if ($child['name'] == $prefix . 'eventDescriptor') {
                        $descriptor_extension = $child;
                    }
                    if ($child['name'] == $prefix . 'eventSubject') {
                        $subject_extension = $child;
                    }

                    if ($child['name'] == $prefix . 'eventContext') {
                        $context_extension = $child;
                    }

                    if ($child['name'] == $prefix . 'eventTimeframe') {
                        $timeframe_extension = $child;
                    }
                }
            }
        }

        $event_definition = array(
            'type'            => $descriptor_extension['attributes']['type'],
            'content'         => $descriptor_extension['attributes']['name'],
            'subject_type'    => $subject_extension['attributes']['type'],
            'subject_id'      => $subject_extension['attributes']['id'],
            'context_type'    => $context_extension['attributes']['type'],
            'context_id'      => $context_extension['attributes']['id'],
            'listening_start' => $timeframe_extension['attributes']['start'],
            'listening_end'   => $timeframe_extension['attributes']['end']
        );
        
        return $event_definition;
    }

    /**
     * @param string $start_event_ref
     * @param string $type
     * @param array  $bpmn2_array
     *
     * @return array
     */
    public static function extractTimeDateEventDefinitionFromElement($start_event_ref, $type, $bpmn2_array)
    {
        $content = '';
        foreach ($bpmn2_array['children'] as $elements) {
            foreach ($elements['children'] as $element) {
                if ($element['name'] == $type) {
                    foreach ((array) $element['children'] as $event_child) {
                        if ($event_child['name'] == 'timerEventDefinition') {
                            if ($event_child['children'][0]['name'] == 'timeDate') {
                                $content = $event_child['children'][0]['content'];
                                $start = date('U', strtotime($content));
                                $end = 0;

                                return array(
                                    'type' 				=> 'time_passed',
                                    'content'			=> 'time_passed',
                                    'subject_type'		=> 'none',
                                    'subject_id'		=> 0,
                                    'context_type'		=> 'none',
                                    'context_id'		=> 0,
                                    'listening_start'	=> $start,
                                    'listening_end'		=> $end
                                );
                            }

                            if ($event_child['children'][0]['name'] == 'timeDuration') {
                                $content = $event_child['children'][0]['content'];
                                $interval = new \DateInterval(strtotime($content));
                                $duration = ($interval->d * 24 * 60 * 60) + ($interval->h * 60 * 60) +
                                            ($interval->i * 60) + $interval->s;

                                return array(
                                    'type' 				=> 'time_passed',
                                    'content'			=> 'time_passed',
                                    'subject_type'		=> 'none',
                                    'subject_id'		=> 0,
                                    'context_type'		=> 'none',
                                    'context_id'		=> 0,
                                    'listening_relative'=> 1,
                                    'listening_interval'=> $duration
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $element
     *
     * @return array
     */
    public static function extractILIASLibraryCallDefinitionFromElement($element)
    {
        $library_call = array();
        foreach ($element['children'] as $child) {
            if ($child['name'] == 'extensionElements') {
                foreach ($child['children'] as $extension) {
                    $prefix = 'ilias:';
                    if ($extension['namespace'] == 'ilias') {
                        $prefix = '';
                    }
                    if ($extension['name'] == $prefix . 'properties') {
                        if ($extension['children'][0]['name'] == $prefix . 'libraryCall') {
                            $library_call = $extension['children'][0]['attributes'];
                            break;
                        }
                    }
                }
            }
        }

        // TODO: This must consult Service Disco for details!

        return array(
            'include_filename'	=> $library_call['location'],
            'class_and_method'		=> $library_call['api'] . '::' . $library_call['method']
        );
    }

    /**
     * @param array $element
     *
     * @return string
     */
    public static function extractScriptDefinitionFromElement($element)
    {
        $code = '';
        foreach ($element['children'] as $child) {
            if ($child['name'] == 'script') {
                $code = $child['content'];
            }
        }
        return $code;
    }

    /**
     * @param array $element
     *
     * @return null
     */
    public static function extractDataNamingFromElement($element)
    {
        if (!isset($element['children'])) {
            return null;
        }

        foreach ($element['children'] as $child) {
            if ($child['name'] == 'extensionElements') {
                foreach ($child['children'] as $extension) {
                    $prefix = 'ilias:';
                    if ($extension['children'][0]['namespace'] == 'ilias') {
                        $prefix = '';
                    }
                    if ($extension['name'] == $prefix . 'properties') {
                        if ($extension['children'][0]['name'] == $prefix . 'property') {
                            $attributes = $extension['children'][0]['attributes'];
                            return $attributes['value'];
                            break;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param array $element
     *
     * @return null|array
     */
    public static function extractILIASInputPropertiesFromElement($element)
    {
        if (!isset($element['children'])) {
            return null;
        }

        $retval = null;
        foreach ((array) $element['children'] as $child) {
            if ($child['name'] == 'extensionElements') {
                foreach ($child['children'] as $extension) {
                    $prefix = 'ilias:';
                    if ($extension['children'][0]['namespace'] == 'ilias') {
                        $prefix = '';
                    }
                    if ($extension['name'] == $prefix . 'properties') {
                        foreach ((array) $extension['children'] as $child) {
                            if ($child['name'] == 'inputproperty') {
                                $retval[$child['attributes']['name']] = $child['attributes']['value'];
                            }
                        }
                    }
                }
            }
        }
        return $retval;
    }

    /**
     * @param array $element
     *
     * @return null|array
     */
    public static function extractILIASDataObjectDefinitionFromElement($element)
    {
        if (!isset($element['children'])) {
            return null;
        }

        $retval = null;
        foreach ((array) $element['children'] as $child) {
            if ($child['name'] == 'extensionElements') {
                foreach ($child['children'] as $extension) {
                    $prefix = 'ilias:';
                    if ($extension['children'][0]['namespace'] == 'ilias') {
                        $prefix = '';
                    }
                    if ($extension['name'] == $prefix . 'properties') {
                        foreach ((array) $extension['children'] as $child) {
                            if ($child['name'] == 'dataobject') {
                                $retval['role'] = $child['attributes']['role'];
                                $retval['type'] = $child['attributes']['type'];
                            }
                        }
                    }
                }
            }
        }
        return $retval;
    }

    /**
     * @param array $element
     *
     * @return null|array
     */
    public static function extractILIASMessageDefinitionFromElement($element)
    {
        if (!isset($element['children'])) {
            return null;
        }

        $retval = null;
        foreach ((array) $element['children'] as $child) {
            if ($child['name'] == 'extensionElements') {
                foreach ($child['children'] as $extension) {
                    $prefix = 'ilias:';
                    if ($extension['children'][0]['namespace'] == 'ilias') {
                        $prefix = '';
                    }
                    if ($extension['name'] == $prefix . 'properties') {
                        foreach ((array) $extension['children'] as $child) {
                            if ($child['attributes']['name'] == 'mailtext') {
                                $retval['mailtext'] = base64_encode($child['content']);
                            }
                        }
                    }
                }
            }
        }

        return $retval;
    }
}

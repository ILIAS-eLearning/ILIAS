<?php

abstract class ilWebDAVMountInstructionsDocumentProcessorBase implements ilWebDAVMountInstructionsDocumentProcessor
{
    public function parseInstructionsToAssocArray(string $a_raw_mount_instructions) : array
    {
        $processing_text = $a_raw_mount_instructions;

        $found_instructions = array();

        // Search for pairs, as long as pairs are found (search at a minimum 1 time)
        do {
            $pair_found = false;

            $open_tag_pos = strpos($processing_text, '[');
            $open_tag_end_pos = strpos($processing_text, ']');

            // Is there a [ and a ] and are they in this order?
            if ($open_tag_pos !== false && $open_tag_end_pos !== false && $open_tag_pos < $open_tag_end_pos) {
                // Extract text between the square brackets "[tag_name]" and create the endtag [/tag_name]
                $tag_name = substr($processing_text, $open_tag_pos + 1, $open_tag_end_pos - $open_tag_pos - 1);
                $close_tag = "[/$tag_name]";

                $close_tag_pos = strpos($processing_text, $close_tag);

                if ($close_tag_pos !== false && $open_tag_end_pos < $close_tag_pos) {
                    $found_instructions[$tag_name] = substr($processing_text, $open_tag_end_pos + 1, $close_tag_pos - $open_tag_end_pos - 1);

                    $processing_text = substr($processing_text, $close_tag_pos + strlen($close_tag));
                    $pair_found = true;
                } else {
                    $processing_text = substr($processing_text, $open_tag_end_pos + 1);
                    $pair_found = true;
                }
            }
        } while ($pair_found);
        
        if (count($found_instructions) === 0) {
            $found_instructions = [ $a_raw_mount_instructions ];
        }

        return $found_instructions;
    }
}

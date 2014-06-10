<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailTypeAdapter.php';


/**
 * ilSampleMailTypeAdapter is a sample, tutorial and reference implementation for the centralized template management.
 * Along with the necessary methods a verbose set of comments and documentation is provided for other developers, who
 * wish to use this service.
 * This class may serve as a starting point to build your own types, the places that need to be changed are marked
 * with to-do markers, so you can copy and customize the class for your needs.
 */
class ilSampleMailTypeAdapter extends ilMailTypeAdapter
{
	public function getCategoryNameLocalized($category_name, $lng)
	{
		return 'Sample';
	}

	public function getTemplateTypeLocalized($category_name, $template_type, $lng)
	{
		return 'Example 1';
	}

	public function getPlaceholdersLocalized($category_name = '', $template_type = '', $lng = '')
	{
		$placeholders = array(
			array(
				'placeholder_code'          => 'PLACEHOLDER_1',
				'placeholder_name'          => 'Placeholder 1',
				'placeholder_description'   => 'The first example placeholder in a series of placeholders'
			),
			array(
				'placeholder_code'          => 'PLACEHOLDER_2',
				'placeholder_name'          => 'Placeholder 2',
				'placeholder_description'   => 'The second example placeholder in a series of placeholders'
			),
			array(
				'placeholder_code'          => 'PLACEHOLDER_3',
				'placeholder_name'          => 'Placeholder 3',
				'placeholder_description'   => 'The third example placeholder in a series of placeholders'
			),
			array(
				'placeholder_code'          => 'PLACEHOLDER_4',
				'placeholder_name'          => 'Placeholder 4',
				'placeholder_description'   => 'The fourth example placeholder in a series of placeholders'
			)
		);

		return $placeholders;
	}
	
	public function getPlaceHolderPreviews($category_name = '', $template_type = '', $lng = '')
	{
		$placeholders = array(
			array(
				'placeholder_code'          => 'PLACEHOLDER_1',
				'placeholder_content'       => 'Placeholder 1'
			),
			array(
				'placeholder_code'          => 'PLACEHOLDER_2',
				'placeholder_content'       => 'Placeholder 2'
			),
			array(
				'placeholder_code'          => 'PLACEHOLDER_3',
				'placeholder_content'       => 'Placeholder 3'
			),
			array(
				'placeholder_code'          => 'PLACEHOLDER_4',
				'placeholder_content'       => 'Placeholder 4'
			)
		);

		return $placeholders;		
	}
	
	public function hasAttachmentsPreview()
	{
		return true;
	}
	
	public function getAttachmentsPreview()
	{
		return array('unzip_test_file.zip');
	}

}

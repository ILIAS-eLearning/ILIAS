<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplateFrameSettingsEntity
 */
class ilMailTemplateFrameSettingsEntity
{
	/**
	 * @var ilDB|null
	 */
	protected $db = null;

	/**
	 * @var ilSetting|null
	 */
	protected $settings = null;
	
	/**
	 * @var string
	 */
	protected $plain_text_frame = '';

	/**
	 * @var string
	 */
	protected $html_frame = '';

	/**
	 * @var string
	 */
	protected $image_styles = '';

	/**
	 * @var string
	 */
	protected $image_name;

	/**
	 * @param ilDB      $db
	 * @param ilSetting $settings
	 */
	public function __construct(ilDB $db, ilSetting $settings)
	{
		$this->db       = $db;
		$this->settings = $settings;

		$this->init();
		$this->read();
	}

	/**
	 * 
	 */
	protected function init()
	{
		ilUtil::makeDir($this->getFileSystemBasePath());
	}

	/**
	 * @return string
	 */
	public static function getFileSystemBasePath()
	{
		return ilUtil::getWebspaceDir() . '/mail_templates';
	}

	/**
	 * @return string
	 */
	public function getHtmlFrame()
	{
		return $this->html_frame;
	}

	/**
	 * @param string $html_frame
	 */
	public function setHtmlFrame($html_frame)
	{
		$this->html_frame = $html_frame;
	}

	/**
	 * @return string
	 */
	public function getImageStyles()
	{
		return $this->image_styles;
	}

	/**
	 * @param string $image_styles
	 */
	public function setImageStyles($image_styles)
	{
		$this->image_styles = $image_styles;
	}

	/**
	 * @return string
	 */
	public function getPlainTextFrame()
	{
		return $this->plain_text_frame;
	}

	/**
	 * @param string $plain_text_frame
	 */
	public function setPlainTextFrame($plain_text_frame)
	{
		$this->plain_text_frame = $plain_text_frame;
	}

	/**
	 * @return string
	 */
	public function getImageName()
	{
		return $this->image_name;
	}

	/**
	 * @param string $image_name
	 */
	public function setImageName($image_name)
	{
		$this->image_name = $image_name;
	}

	/**
	 * @return bool
	 */
	public function doesImageExist()
	{
		return strlen($this->getImageName()) && is_file($this->getFileSystemBasePath() . '/' . $this->getImageName()) && is_readable($this->getFileSystemBasePath() . '/' . $this->getImageName());
	}

	/**
	 * 
	 */
	public function read()
	{
		$this->setPlainTextFrame($this->settings->get('mail_tpl_frame_plain_txt'));
		$this->setHtmlFrame($this->settings->get('mail_tpl_frame_html'));
		$this->setImageName($this->settings->get('mail_tpl_frame_img_name'));
		$this->setImageStyles($this->settings->get('mail_tpl_frame_img_styles'));
	}

	/**
	 * Persists the settings kept in memory
	 */
	public function save()
	{
		$this->settings->set('mail_tpl_frame_plain_txt', $this->getPlainTextFrame());
		$this->settings->set('mail_tpl_frame_html', $this->getHtmlFrame());
		$this->settings->set('mail_tpl_frame_img_name', $this->getImageName());
		$this->settings->set('mail_tpl_frame_img_styles', $this->getImageStyles());
	}

	/**
	 * 
	 */
	public function deleteImage()
	{
		if($this->doesImageExist())
		{
			@unlink($this->getFileSystemBasePath() . '/' . $this->getImageName());
		}
		$this->setImageName('');
	}

	/**
	 * @param string $tmp_name
	 * @param string $file_name
	 */
	public function uploadImage($tmp_name, $file_name)
	{
		$this->deleteImage();
		ilUtil::moveUploadedFile($tmp_name, $file_name, $this->getFileSystemBasePath() . '/' . $file_name);
		$this->setImageName($file_name);
	}
}
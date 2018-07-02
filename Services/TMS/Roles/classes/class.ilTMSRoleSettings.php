<?php

/**
 * Keeps special tms settings for roles
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-traning.de>
 */
class ilTMSRoleSettings {
	/**
	 * @var int
	 */
	protected $role_id;

	/**
	 * @var bool
	 */
	protected $hide_breadcrumb;

	/**
	 * @var bool
	 */
	protected $hide_menu_tree;

	/**
	 * @param int 	$role_id
	 * @param bool 	$hide_breadcrumb
	 * @param bool 	$hide_menu_tree
	 */
	public function __construct($role_id, $hide_breadcrumb, $hide_menu_tree) {
		assert('is_int($role_id)');
		assert('is_bool($hide_breadcrumb)');
		assert('is_bool($hide_menu_tree)');

		$this->role_id = $role_id;
		$this->hide_breadcrumb = $hide_breadcrumb;
		$this->hide_menu_tree = $hide_menu_tree;
	}

	/**
	 * Get the obj id
	 *
	 * @return int
	 */
	public function getRoleId() {
		return $this->role_id;
	}

	/**
	 * Breadcrump navigation should be restricted
	 *
	 * @return bool
	 */
	public function getHideBreadcrumb() {
		return $this->hide_breadcrumb;
	}

	/**
	 * Show navigation tree
	 *
	 * @return bool
	 */
	public function getHideMenuTree() {
		return $this->hide_menu_tree;
	}

	/**
	 * Get clone with new value to restrict breadcrumb
	 *
	 * @param bool 	$hide_breadcrumb
	 *
	 * @return this
	 */
	public function withHideBreadcrumb($hide_breadcrumb) {
		assert('is_bool($hide_breadcrumb)');
		$clone = clone $this;
		$clone->hide_breadcrumb = $hide_breadcrumb;
		return $clone;
	}

	/**
	 * Get clone with new value to hide navigation tree
	 *
	 * @param bool 	$hide_menu_tree
	 *
	 * @return this
	 */
	public function withHideMenuTree($hide_menu_tree) {
		assert('is_bool($hide_menu_tree)');
		$clone = clone $this;
		$clone->hide_menu_tree = $hide_menu_tree;
		return $clone;
	}
}
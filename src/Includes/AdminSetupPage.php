<?php

namespace Tangram\Includes;

use Tangram\Helpers\View;

/**
 * Register the administration menu for this plugin into the WordPress Dashboard menu.
 */
class AdminSetupPage
{
	public $set;

	public function __construct($set)
	{
		$this->set = $set;
		add_action('admin_menu', [$this, 'add_plugin_admin_menu']);
		$hook = 'plugin_action_links_' . plugin_basename(plugin_dir_path(__DIR__) . $this->set['plugin_name'] . '.php');
		add_filter($hook, [$this, 'add_action_links']);
		add_action('admin_init', [$this, 'options_update']);
	}

	/**
	 * Add a settings page for this plugin to the Settings menu.
	 */
	public function add_plugin_admin_menu()
	{
		add_options_page(
			'Tangram Projects and Base Options Functions Setup',
			'Tangram Projects',
			'manage_options',
			$this->set['plugin_name'],
			array($this, 'display_plugin_setup_page')
		);
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	public function add_action_links($links)
	{
		$settings_link = [
			'<a href="' . admin_url('options-general.php?page=' . $this->set['plugin_name']) . '">' . __('Settings', $this->set['text_domain']) . '</a>',
		];
		return array_merge($settings_link, $links);
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public function display_plugin_setup_page()
	{
		$renderer = new View();
		echo $renderer->render('admin-settings-form.php', $this->set);
	}

	/**
	 * Validate options
	 */
	public function validate($input)
	{
		$valid = array();
		$valid['catalog'] = (isset($input['catalog']) && !empty($input['catalog'])) ? $input['catalog'] : '';
		return $valid;
	}

	/**
	 * Update all options
	 */
	public function options_update()
	{
		register_setting($this->set['plugin_name'], $this->set['plugin_name'], [$this, 'validate']);
	}

}

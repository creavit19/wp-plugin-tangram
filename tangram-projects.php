<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://tangram.ua
 * @since             1.0.0
 * @package           Tangram_projects
 *
 * @wordpress-plugin
 * Plugin Name:       Tangram Projects
 */

require __DIR__ . '/vendor/autoload.php';

use Tangram\TangramProjects;
use Tangram\Init\Activator;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

define('TANGRAM_PROJECTS_NAME', 'tangram_projects');

function get_project_date()
{
	return get_post_meta(get_queried_object_id(), 'project_r_date', true);
}

function get_project_url()
{
	$options = get_option(TANGRAM_PROJECTS_NAME);
	return get_site_url() . '/' . $options['catalog'] . '/' . get_post_meta(get_the_ID(), 'project_p_link', true);
}

function activate_tangram_projects()
{
	Activator::activate();
}

register_activation_hook(__FILE__, 'activate_tangram_projects');

function run_tangram_projects_plugin()
{
	$plugin = new TangramProjects([
		'plugin_name' => TANGRAM_PROJECTS_NAME,
		'plugin_version' => '1.0.0',
		'text_domain' => 'tangram_projects'
	]);
	$plugin->registerAdminSetupPage();
	$plugin->registerPostType();
	$plugin->createMetaBox();
	$plugin->enableBasicAuth();
	$plugin->addRestApi();
	$plugin->enableLocale();
}

run_tangram_projects_plugin();

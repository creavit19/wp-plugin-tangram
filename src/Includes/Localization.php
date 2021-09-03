<?php

namespace Tangram\Includes;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 */

class Localization
{
	/**
	 * Includes translation file
	 * @param $set
	 */
	public function __construct($set)
	{
		load_plugin_textdomain(
			$set['text_domain'],
			false,
			dirname(__DIR__) . '/Locales/'
		);
	}

}

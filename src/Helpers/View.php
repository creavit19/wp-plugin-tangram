<?php

namespace Tangram\Helpers;

class View
{

	/**
	 * Basic view path
	 * @var string
	 */
	protected string $viewPath;

	public function __construct()
	{
		$this->viewPath = dirname(__DIR__) . '/views/';
	}

	/**
	 * Gives prepared view
	 * @param string $view - view file name
	 * @param array $data - array with variables for the view
	 * @return false|string
	 */
	public function render(string $view, array $data = [])
	{
		$viewPath = $this->viewPath . $view;
		ob_start();
		extract($data);
		require_once $viewPath;
		return ob_get_clean();
	}

}

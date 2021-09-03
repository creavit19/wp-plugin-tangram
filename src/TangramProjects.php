<?php

namespace Tangram;

use Tangram\Includes\AdminSetupPage;
use Tangram\Includes\BasicAuth;
use Tangram\Includes\MetaBoxs;
use Tangram\Includes\PostType;
use Tangram\Includes\RestApi;

class TangramProjects
{
	protected $set;

	public function __construct($set)
	{
		$this->set = $set;
	}

	public function registerAdminSetupPage()
	{
		new AdminSetupPage($this->set);
	}

	public function registerPostType()
	{
		new PostType($this->set);
	}

	public function createMetaBox()
	{
		new MetaBoxs($this->set);
	}

	public function addRestApi()
	{
		new RestApi($this->set);
	}

	public function enableBasicAuth()
	{
		new BasicAuth();
	}

}

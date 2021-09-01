<?php

namespace Tangram\Includes;


use Tangram\Helpers\MetaBox;

/**
* Create meta boxs
*/
class MetaBoxs
{
	public $set;

	public function __construct($set){

		$this->set = $set;

		new MetaBox(
			[
				'id'	=>	'project', // Metabox ID and custom field name prefix
				'name'	=>	__('Project', $this->set['text_domain']), // meta box title
				'post'	=>	['tangram_project'], // post types for which you want to display the metabox
				'pos'	=>	'advanced', // location - context parameter of add_meta_box() function
				'pri'	=>	'high', // priority - the priority parameter of the add_meta_box() function
				'cap'	=>	'edit_posts', // what rights the user should have
				'args'	=>	[
					[
						'id'			=>	'r_date',
						'title'			=>	__('Release date', $this->set['text_domain']),
						'type'			=>	'date', // тип
						'placeholder'		=>	__('Enter date', $this->set['text_domain']),
						'desc'			=>	'',
						'cap'			=>	'edit_posts'
					],
					[
						'id'			=>	'p_link',
						'title'			=>	__('Link to project', $this->set['text_domain']),
						'type'			=>	'text',
						'placeholder'		=>	__('Enter link', $this->set['text_domain']),
						'desc'			=>	'',
						'cap'			=>	'edit_posts'
					]
				]
			]
		);

	}

}

<?php

namespace Tangram\Init;

class Activator
{
	public static function activate(){
		do_action('activation_' . TANGRAM_PROJECTS_NAME);
		$terms = [
			[
				'term' => 'WordPress',
				'slug' => 'wordpress'
			],
			[
				'term' => 'Laravel',
				'slug' => 'laravel'
			],
			[
				'term' => 'Vue',
				'slug' => 'vue'
			],
			[
				'term' => 'React',
				'slug' => 'react'
			],
			[
				'term' => 'Symfony',
				'slug' => 'symfony'
			]
		];
		foreach($terms as $term){
			wp_insert_term( $term['term'], 'project_categories', [
				'description' => '',
				'parent'      => 0,
				'slug'        => $term['slug'],
			] );
		}
	}
}

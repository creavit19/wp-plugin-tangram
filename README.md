# Tangram Projects
This plugin adds custom posttypes and realizes access to their data through REST API 
#
### Using REST API
###
#### To get the data of all posts:
GET: http://one.wordpress.test/wp-json/tangram/v1/projects/
###
#### To get the data of one post by id (for example id = 44)
GET: http://one.wordpress.test/wp-json/tangram/v1/project/44
###
#### To create a new post
POST: http://one.wordpress.test/wp-json/tangram/v1/project/create
####
With request data:
```js
{
  'post_title': 'Some post title',
  'post_name': 'some_post_slug',
  'post_content': 'some post content.... ',
  'project_categories': { '<term_slug>': '<term_id>', 'Laravel': '32' }
}
```
###
#### To update a post by id (for example id = 51)
POST: http://one.wordpress.test/wp-json/tangram/v1/project/51
####
With the above shown request data.
###
#### To delete a post by id (for example id = 44)
DELETE: http://one.wordpress.test/wp-json/tangram/v1/project/44

Carbon Admin Columns Manager
============================

This plugin provides an easy way to add, remove and manage columns from WordPress administration screens for post, users and taxonomy listing.

------

## Usage

**Modify pages list columns**
Remove date and author columns from page listing screen, and add 2 extra columns (`color` and `view count`). The values for the extra columns are fetched from certain post meta fields.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('post', array('page') )
  ->remove( array('date', 'author') )
  ->add( array(
		Carbon_Admin_Column::create('Color')
		   ->set_field('color'),
		Carbon_Admin_Column::create('Views Count')
		   ->set_field('views_count'),
	 ));
?>
```

**Print column values with callback function**
Add an extra column to page listing screen: views. Every page with more than 1000 views is rendered with a special CSS class `popular-page`.

This is achieved by using a custom callback function for printing the content of the column for the particular `post_id`.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('post', array('page') )
  ->add( array(
		Carbon_Admin_Column::create('Views Count')
		   ->set_callback('crb_admin_render_view_count_col'),
	 ));

function crb_admin_render_view_count_col( $post_id ) {
	$views_count = get_post_meta($post_id, 'views_count', 1);
	$views_count = intval($views_count);

	if ($views_count > 1000) {
		$views_count = '<span class="popular-page">' . $views_count . '</span>';
	}

	return $views_count;
}
?>
```

**Modify custom post type columns**
Remove date and author columns from the `crb_cars` custom post type listing screen, and add 2 extra columns (`model` and `price`). The values for the extra columns are fetched from certain post meta fields.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('post', array('crb_cars') )
  ->remove( array('date', 'author') )
  ->add( array(
		Carbon_Admin_Column::create('Model')
		   ->set_field('_crb_car_model'),
		Carbon_Admin_Column::create('Price')
		   ->set_field('_crb_car_price'),
	 ));
?>
```

**Render featured image in the listing table**

To show the featured image for the `post`, `page` and `crb_cars` post types, use a custom callback function that accepts the `$post_id` as a parameter.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('post', array('page', 'post', 'crb_cars') )
  ->add( array(
		Carbon_Admin_Column::create('Thumbnail')
		   ->set_callback('crb_column_render_post_thumbnail'),
	 ));

function crb_column_render_post_thumbnail( $post_id ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$thumbnail = get_the_post_thumbnail($post_id, 'my_backend_image_size');
	} else {
		$thumbnail = '';
	}

	return $thumbnail;
}
?>
```

**Modify taxonomy columns**

Remove slug and count columns from categories and tags listing screens, and add 2 extra columns (`image` and `subtitle`). The values for the extra columns are fetched from certain term meta fields.
Showing the term image is achieved by using a custom callback function and showing the term subtitle is achieved by using the field meta key.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('taxonomy', array('category', 'post_tag') )
  ->remove( array('description', 'posts') )
  ->add( array(
		Carbon_Admin_Column::create('Subtitle')
		   ->set_field('crb_term_subtitle'),
		Carbon_Admin_Column::create('Image')
		   ->set_callback('crb_column_render_term_image'),
	 ));

function crb_column_render_term_image( $term_id ) {
	if ( $term_image_id = get_term_meta($term_id, 'crb_term_image') ) {
		$term_image = wp_get_attachment_image($term_image_id, 'my_backend_image_size');
	} else {
		$term_image = '';
	}

	return $term_image;
}
?>
```

**Modify user columns**

Remove posts, role and e-mail columns from users listing screen, and add 2 extra columns (`is user active` and `user registration date`). The values for the extra columns are fetched from user meta fields.
Showing the user status (active / inactive) is achieved by using the field meta key, while showing the user registration date is achieved by using a custom callback function.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('user')
  ->remove( array('email', 'role', 'posts') )
  ->add( array(
		Carbon_Admin_Column::create('Is Active')
		   ->set_field('crb_user_status'),
		Carbon_Admin_Column::create('Registration Date')
		   ->set_callback('crb_column_get_user_registration_date'),
	 ));

function crb_column_get_user_registration_date( $user_id ) {
	$user = get_user_by( 'id', $user_id );
	$user_registration_date = $user->data->user_registered;

	$friendly_date_text = date( 'd F, Y', strtotime( $user_registration_date ) );

	return $friendly_date_text;
}
?>
```

**How to deal with the responsive version of the WordPress admin**

By default WordPress hides all additional columns when the device width is equal or less than 800 pixels.
However, the unnecessary columns can be removed or their values can be combined into a single column by using a callback function.

The example below removes the `date`, `author` and `comments` columns from page listing screen, and adds a single column showing the `author`, `comments`, `date` and `thumbnail`.
In this example we will use the regular post function to retrieve the information. Each column will display the post data that corresponds to the listed entry.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('post', array('page') )
	->remove( array('date', 'author', 'comments') )
	->add( array(
		Carbon_Admin_Column::create('Page Information')
			->set_callback('crb_column_page_information'),
	) );

function crb_column_page_information( $page_id ) {
	ob_start();
	?>
	<ul>
		<?php if ( has_post_thumbnail() ): ?>
			<li>
				<?php the_post_thumbnail( 'thumbnail' ) ?>
			</li>
		<?php endif ?>
		<li>
			Posted on : <?php the_time('F jS, Y ') ?>
		</li>
		<li>
			 <?php printf(__('Posted by : %s', 'crb'), get_the_author()) ?>
		</li>
		<li>
			<?php comments_popup_link( __('No Comments', 'crb'), __('1 Comment', 'crb'), __('% Comments', 'crb') ); ?>
		</li>
	</ul>
	<?php
	$page_information_content = ob_get_clean();

	return $page_information_content;
}
?>
```

**Create a sortable column in the WordPress admin**

Create a sortable column on page listing screen that sorts the pages by their views count.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('post', array('page') )
	->add( array(
		Carbon_Admin_Column::create('Page Information')
			->set_sort_field('views_count'), # that value will be accessible as 'orderby' get parameter.
			->set_callback('crb_column_page_information'),
	) );

add_action('pre_get_posts', 'crb_sort_pages_by_their_views_count');
function crb_sort_pages_by_their_views_count( $query ) {

	if (
		is_admin()
		&& $query->is_main_query()
		&& get_query_var( 'post_type' )==='page'
		&& !empty( $_GET['orderby'] )
		&& $_GET['orderby']==='orderby_custom_column'
	) {

		$query->set('orderby', 'meta_value_num');
		$query->set('meta_key', '_crb_page_views');
	}

	return $query;
}
?>
```

Create a sortable column on user listing screen that sorts the users by their status (active / inactive).

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('user')
	->add( array(
		Carbon_Admin_Column::create('Is User Active')
			->set_field('_crb_is_user_active')
			->set_sort_field('orderby_status'),
	) );

add_action( 'pre_get_users', 'crb_pre_user_query' );
function crb_pre_user_query( $user_query ) {
	global $wpdb;

	if (
		is_admin()
		&& !empty($_GET['orderby'])
		&& $_GET['orderby']==='orderby_status'
	) {
		$user_query->set('meta_key', '_crb_is_user_active');
		$user_query->set('orderby', 'meta_value');
	}
}
?>
```

**Set custom width to a column in the WordPress admin**

Set different width to Color, Views Count and Status columns for the page listing screen. Color column width is in percents while the View Count column width is in pixels. The width for the Status column is passed as an integer, which will automatically treat it as a pixel value.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('post', array('page') )
	->remove( array('date', 'author') )
	->add( array(
		Carbon_Admin_Column::create('Color')
			->set_width( '80%' )
			->set_field('color'),
		Carbon_Admin_Column::create('Views Count')
			->set_width( '25px' )
			->set_field('views_count'),
		Carbon_Admin_Column::create('Status')
			->set_width( 100 )
			->set_field('status'),
	));
?>
```

Please, note that WordPress administration is responsive and you should use a reasonable number of columns, as well as reasonable width for each one of them. For example, having a column width of 600 pixels might cause issues on mobile devices.

**How to set custom column name and to add specific style to it?**

By default the column names are generated randomly unless custom name is specified.

Setting up custom name for Image column on page listing screen and setting maximum image width.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('post', array('page') )
	->remove( array('date', 'author') )
	->add( array(
		Carbon_Admin_Column::create('Color')
			->set_name( 'crb-column-page-thumbnail' )
			->set_width( '100px' )
			->set_field('color'),
	 ));
?>
```

CSS:

```css
<style type="text/css">
	/* column heading */
	#carbon-crb-column-page-thumbnail {
		font-weight : bold;
	}

	/* column value */
	.crb-column-page-thumbnail img {
		max-width : 100%;
		height : auto;
	}
</style>
```

**How to reorder the columns on the admin listing screeen?**

Moving page thumbnail column between the post checkbox and title columns. It's required that you set a column name with `set_name()` and then to specify the new order with `sort()`. All columns that are not specified in the array that you pass to the `sort()` method will be moved to the end, keeping their default order.

Code:

```php
<?php
Carbon_Admin_Columns_Manager::modify_columns('post', array('page') )
	->sort( array('cb', 'crb-thumbnail-column') )
	->add( array(
		Carbon_Admin_Column::create('Thumbnail')
			->set_name( 'crb-thumbnail-column' )
			->set_callback('crb_column_thumbnail'),
	 ));
?>
```

------

## Useful Callback Functions

The functions below are just for reference and are not defined in the plugin.

**Page/Post Thumbnail**
Use the following callback function to display the post thumbnail photo.

Code :

```php
<?php
function crb_column_thumbnail( $post_id ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, 'admin_thumbnails' );
	}
}
?>
```

**Page Template**
Use the following callback function to display the page template name.

Code :

```php
<?php
function crb_column_page_template( $page_id ) {
	$page_template_name = array_search(
		get_post_meta( $page_id, '_wp_page_template', true ),
		get_page_templates()
	);

	if ( $page_template_name === false ) {
		$page_template_name = 'Default';
	}

	return $page_template_name;
}
?>
```

**Page Sidebar**
Use the following callback function to display the sidebar that is selected for each page.

Code :

```php
<?php
function crb_column_page_template( $page_id ) {

	$sidebar = get_post_meta( $page_id, 'crb_custom_sidebar', true );

	if ( empty($sidebar) ) {
		$sidebar = 'Default Sidebar';
	}

	return $page_template_name;
}
?>
```

------

## Package Summary

**Columns Manager**
There are three types of manager for Posts, Taxonomies and Users listing pages.

*Posts Manager*

Code :

```php
<?php
$post_types = array( 'post_type_one', 'post_type_two' );

$columns_to_remove = array( 'column_one', 'column_two' );

$custom_column_order = array( 'column_name_five', 'column_name_four', 'column_name_three' );

$columns_to_add = array( $columns_code_goes_here );

Carbon_Admin_Columns_Manager::modify_columns('post', $post_types )
	->remove( $columns_to_remove ) # remove unnecessary columns
	->sort( $custom_column_order ) # set custom column order
	->add( $columns_to_add ) # add new columns
?>
```

*Taxonomy Manager*

Code :

```php
<?php
$taxonomies = array( 'taxonomy_name_one', 'taxonomy_name_two' );

$columns_to_remove = array( 'column_one', 'column_two' );

$custom_column_order = array( 'column_name_five', 'column_name_four', 'column_name_three' );

$columns_to_add = array( $columns_code_goes_here );

Carbon_Admin_Columns_Manager::modify_columns('taxonomy', $taxonomies )
	->remove( $columns_to_remove ) # remove unnecessary columns
	->sort( $custom_column_order ) # set custom column order
	->add( $columns_to_add ) # add new columns
?>
```

*Users Manager*

Code :

```php
<?php
$columns_to_remove = array( 'column_one', 'column_two' );

$custom_column_order = array( 'column_name_five', 'column_name_four', 'column_name_three' );

$columns_to_add = array( $columns_code_goes_here );

Carbon_Admin_Columns_Manager::modify_columns('user')
	->remove( $columns_to_remove ) # remove unnecessary columns
	->sort( $custom_column_order ) # set custom column order
	->add( $columns_to_add ) # add new columns
?>
```

**Column**

*Column that lists meta value by a given meta key*

Code :

```php
<?php
$column_name = __('My Column Name', 'crb');

$meta_key = '_crb_meta_key';

Carbon_Admin_Column::create( $column_name )
	->set_field( $meta_key ),
?>
```

*Column that prints its value through callback function*

Code :

```php
<?php
$column_name = __('My Column Name', 'crb');

$callback_function_name = 'crb_callback_function';

Carbon_Admin_Column::create( $column_name )
	->set_callback( $callback_function_name ),

/**
 * Callback Function
 *
 * @param $object_id Post ID, Term ID or User Id according to the Manager
 */
function crb_callback_function( $object_id ) {
	# posts
	return get_post_meta( $object_id, '_crb_meta_key', true );

	# terms
	return get_term_meta( $object_id, '_crb_meta_key' );

	# users
	return get_user_meta( $object_id, '_crb_meta_key', true );
}
?>
```

*Available Column Functions*

* `::create( $param )`, string, column label
* `set_field( $param )`, string, meta key, cannot be used along with `set_callback()`
* `set_callback( $param )`, string, callback function name, cannot be used along with `set_field()`
* `set_name( $param )`, string, unque column name that can be used for column sorting or styling. by default the column name is generated randomly
* `set_sort_field( $param )`, string, `$_GET['orderby']` value

Code :

```php
<?php
Carbon_Admin_Column::create( $column_label )
	->set_field( $meta_key )
	->set_callback( $callback_function_name )
	->set_name( $column_name )
	->set_sort_field( $crb_sorting_value )
?>
```

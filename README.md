Carbon Admin Columns Manager
============================

Библиотека за управление на WordPress колони от следните видове:
1. Потребителски колони
```PHP
<?php
Carbon_Admin_Columns_Manager::modify_users_columns()
```

2. Пост колони
```PHP
<?php
Carbon_Admin_Columns_Manager::modify_post_type_columns(array('post_type_one', 'post_type_two', 'etc.'))
```

3. Таксономиини колони
```PHP
<?php
Carbon_Admin_Columns_Manager::modify_taxonomy_columns(array('taxonomy_one', 'taxonomy_two', 'etc.'))
```

Възможни са следните манипулации:
1. Премахване на колони
```PHP
<?php
->remove(array('column_one', 'column_one', 'etc.'))
```
функцията приема масив от имена на колони

2. Добавяне на нови колони
```PHP
<?php
->add( array() )
```
функцията приема масив от обекти на колони

Създаване на колона:
```PHP
<?php
Carbon_Admin_Column::create('Име на колоната')
```

Колоните биват два вида:
1. Извежда мета стойност при зададен ключ
```PHP
<?php
->set_field('_meta_key')
```

2. Обръща се към функция за обратно извикване и предава ID-то на обекта като параметър
```PHP
<?php
->set_callback('callback_function_name')
# или
->set_callback( function( $object_id ){ /* code goes here */ } )
```

Придаване на опция за сортиране на колоната:
->set_sortable(true, 'sortable_column_key')
при сортиране 'sortable_column_key' се предава като гет параметър на orderby ( $_GET['orderby'] => sortable_column_key )
kogato 'sortable_column_key' не е зададен се генерира автоматично такъв спрямо името на колоната.


## Примери ##

Премахване на 'author', 'date' и 'comments' колони за блог постове и страници:
```PHP
<?php
Carbon_Admin_Columns_Manager::modify_post_type_columns(array('page', 'post'))
	->remove(array('author', 'date', 'comments'))
```

Добаване на колона, която извежда стойност пазена с мета ключ '_meta_key_one'
```PHP
<?php
Carbon_Admin_Columns_Manager::modify_post_type_columns(array('page', 'post'))
	->remove(array('author', 'date', 'comments'))
	->add(array(
			Carbon_Admin_Column::create('My Meta Value')
				->set_field('_meta_key_one')
		));
```

Добавяне на опция за сортиране на колоната 'My Meta Value'
```PHP
<?php
Carbon_Admin_Columns_Manager::modify_post_type_columns(array('page', 'post'))
	->remove(array('author', 'date', 'comments'))
	->add(array(
			Carbon_Admin_Column::create('My Meta Value')
				->set_sortable(true, 'sortable_column_key')
				->set_field('_meta_key_one')
		));
```

Добаване на колона, която използва функция за обратно избикване и събира стойност на два мета записа.
```PHP
<?php
Carbon_Admin_Columns_Manager::modify_post_type_columns(array('page', 'post'))
	->remove(array('author', 'date', 'comments'))
	->add(array(
			Carbon_Admin_Column::create('My Callback Column')
				->set_callback('callback_function_name')
		));
```

Примерна функция за обратно извикване (използвана в примера по-горе).
Променливата $object_id е съответно ID на пост, потребител или търм.
```PHP
<?php
function callback_function_name( $object_id ) {
	$meta_value_one = get_post_meta($object_id, '_meta_key_one', true);
	$meta_value_two = get_post_meta($object_id, '_meta_key_two', true);

	return intval($meta_value_one) + intval($meta_value_two);
}
```

Аналогично за Таксономиини и Потребителски колони.


## Дъпълнителни фрагменти с примерен код ##

```PHP
<?php
# Post types Columns
Carbon_Admin_Columns_Manager::modify_post_type_columns(array('page', 'post'))
	->remove(array('author', 'date', 'comments'))
	->add(array(
			Carbon_Admin_Column::create('My Color')
				->set_field('color')
				->set_sortable(true, 'sortable_color_key'),
			Carbon_Admin_Column::create('Callback 1')
				->set_callback('callback'),
			Carbon_Admin_Column::create('My Car')
				->set_field('car'),
			Carbon_Admin_Column::create('Callback 2')
				->set_callback('callback'),
		));

function callback($object_id) {
	$post_object = get_post($object_id);
	return '<span style="color: red">' . $post_object->post_name . '</span>';
}


# Taxonomies Columns 
Carbon_Admin_Columns_Manager::modify_taxonomy_columns(array('category', 'post_tag'))
	->remove(array('author', 'description', 'slug', 'posts'))
	->add(array(
			Carbon_Admin_Column::create('Title Color')
				->set_field('title_color')
				->set_sortable(true, 'sortable_color_key'),
			Carbon_Admin_Column::create('Callback 1')
				->set_callback('taxonomy_callback'),
			Carbon_Admin_Column::create('Text Color')
				->set_field('text_color'),
			Carbon_Admin_Column::create('Callback 2')
				->set_callback('taxonomy_callback'),
		));

function taxonomy_callback($object_id) {
	$tax_color = carbon_get_term_meta($object_id, 'title_color');
	return '<span style="color: ' . $tax_color . '">' . $tax_color . '</span>';
}


# Users Columns
Carbon_Admin_Columns_Manager::modify_users_columns()
	->remove(array('name', 'email', 'posts'))
	->add(array(
			Carbon_Admin_Column::create('Description')
				->set_field('description'),
			Carbon_Admin_Column::create('Nickname')
				->set_sortable(true, 'sortable_nickname_key')
				->set_field('nickname'),
			Carbon_Admin_Column::create('Misc Information')
				->set_callback('user_callback'),
		));

function user_callback($object_id) {
	$user = get_user_by('id', $object_id);

	$html = '<ul>';
	$html .= '<li>ID : ' . $user->ID . '</li>';
	$html .= '<li>Email : ' . $user->data->user_email . '</li>';
	$html .= '<li>Nicename : ' . $user->data->user_nicename . '</li>';
	$html .= '</ul>';

	return $html;
}
```
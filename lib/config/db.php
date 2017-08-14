<?php
return array(
	'shop_gift_product_gift' => array(
		'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
		'product_id' => array('int', 11, 'null' => 0),
		'gift_id' => array('int', 11, 'null' => 0),
		'order' => array('int', 11, 'null' => 0, 'default' => '0'),
		'quantity' => array('int', 11, 'null' => 0, 'default' => '1'),
		':keys' => array(
			'PRIMARY' => 'id',
			'product_id' => array('product_id'),
		),
	),
	'shop_gift_params' => array(
		'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
		'product_id' => array('int', 11, 'null' => 0),
		'qty' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
		'set' => array('tinyint', 1, 'null' => 0, 'default' => '0'),
		'stock_id' => array('int', 11, 'null' => 0, 'default' => '0'),
		'start' => array('date','null' => 1),
		'finish' => array('date','null' => 1),
		':keys' => array(
			'PRIMARY' => 'id',
			'product_id' => array('product_id', 'unique' => 1),
		),
	),
);
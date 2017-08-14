<?php

return array(
	'on' => array(
		'title' => 'Включить плагин',
		'description' => '',
		'control_type' => waHtmlControl::CHECKBOX,
		'value' => 0,
		'subject' => 'standart',
	),
	'gift' => array(
		'title' => 'Включить вывод блока "подарки товара" через хук frontend_product.block',
		'description' => 'для вывода блока "подарки товара" на странице товара.<br> Блок можно вывести хелпером плагина {shopGiftPlugin::gifts($product_id)}.',
		'control_type' => waHtmlControl::CHECKBOX,
		'value' => 0,
		'subject' => 'standart',
	),
	'checkout' => array(
		'title' => 'Включить вывод блока выбора подарков через хук frontend_checkout',
		'description' => 'для вывода блока выбора подарка в разделе оформления заказа',
		'control_type' => waHtmlControl::CHECKBOX,
		'value' => 0,
		'subject' => 'standart',
	),
	'products' => array(
		'title' => 'Включить вывод блока "товары с подарком"',
		'description' => 'для вывода блока "товары с подарком" на странице товара и странице корзины.<br> Блок можно вывести хелпером плагина {shopGiftPlugin::products($gift_id,$product_id)}',
		'control_type' => waHtmlControl::CHECKBOX,
		'value' => 0,
		'subject' => 'standart',
	),
	'max_product_count' => array(
		'title' => 'Максимальное количесто товаров в блоке "товары с подарком"',
		'description' => '',
		'control_type' => waHtmlControl::INPUT,
		'value' => 10,
		'class' => 'bold short numerical',
		'subject' => 'standart',
	),
	'list_id' => array(
		'title' => 'Идентификатор списка товаров',
		'description' => '',
		'control_type' => waHtmlControl::SELECT,
		'options' => shopGiftPlugin::lists(),
		'value' => '',
		'subject' => 'standart',
	),
	/*'set' => array(
		'title' => 'Компановка',
		'description' => '',
		'control_type' => waHtmlControl::RADIOGROUP,
		'options' => array(
			0 => 'каждый назначенный подарок предлагается по отдельности на выбор, при этом количество конкретного подарка не учитывается',
			1 => 'предлагается только комплект из назначенных подарков, при этом учитывается количество конкретного подарка',
		),
		'value' => 0,
		'subject' => 'standart',
	),*/
);
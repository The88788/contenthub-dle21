<?php
// ContentHub для DLE - конфигурация модуля.
// Редактируется через админ-панель: ?mod=contenthub

return [
	// Показывать страницу публикаций гостям (неавторизованным посетителям)
	'allow_guest' => true,

	// Суффикс ЧПУ страницы модуля (должен совпадать с правилом ЧПУ)
	'url_prefix' => 'contenthub',

	// Блоки страницы публикаций. Ключ = имени секции в contenthub.tpl.
	// cats - ID категорий через запятую, tpl - шаблон вывода поста (templates/ИМЯ.tpl),
	// limit - максимум постов в блоке, order/sort - сортировка (как у тега custom).
	'blocks' => [
		'cases' => [
			'enable' => 1,
			'title'  => 'Кейсы',
			'cats'   => '1',
			'tpl'    => 'cases',
			'order'  => 'date',
			'sort'   => 'desc',
			'limit'  => 5,
		],
		'prices' => [
			'enable' => 1,
			'title'  => 'Услуги и прайс',
			'cats'   => '2,9,10,11,12,14,15,16,17,18,19,20,21,22,23,24',
			'tpl'    => 'prices',
			'order'  => 'date',
			'sort'   => 'asc',
			'limit'  => 30,
		],
		'files' => [
			'enable' => 1,
			'title'  => 'Файлы',
			'cats'   => '3,4,5,7,8',
			'tpl'    => 'files',
			'order'  => 'date',
			'sort'   => 'desc',
			'limit'  => 5,
		],
	],
];

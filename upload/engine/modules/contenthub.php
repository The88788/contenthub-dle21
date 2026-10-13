<?php
/*
=====================================================
 ContentHub - Публикации пользователя (модуль для DLE 21)
 Страница всех публикаций пользователя, сгруппированных
 по блокам (кейсы, услуги, файлы и т.д.).

 v1.1.2: права категорий для блоков и тегов, allow_short, защита старых шаблонов.
 v1.1.1: единая проверка allow_guest, отклонение массивов параметров.
 v1.1.0: добавлены страницы тегов:
   /tags/        - облако всех тегов сайта (taglist.tpl)
   /tags/{тег}/  - публикации с тегом, сгруппированные по тем
                   же блокам, что и у пользователя
 URL: /user/ИМЯ/content/, /tags/, /tags/{тег}/
=====================================================
*/
if (!defined('DATALIFEENGINE')) {
	die('Hacking attempt!');
}

$ch_config = [];
if (file_exists(ENGINE_DIR . '/data/contenthub.php')) {
	$ch_config = include ENGINE_DIR . '/data/contenthub.php';
}
if (!is_array($ch_config)) $ch_config = [];
if (!isset($ch_config['allow_guest'])) $ch_config['allow_guest'] = true;
if (!isset($ch_config['blocks']) || !is_array($ch_config['blocks'])) $ch_config['blocks'] = [];

foreach (['user', 'tag'] as $ch_parameter) {
	if (isset($_REQUEST[$ch_parameter]) && !is_string($_REQUEST[$ch_parameter])) {
		http_response_code(400);
		echo 'Некорректный запрос.';
		return;
	}
}
$ch_name = isset($_REQUEST['user']) ? trim((string)$_REQUEST['user']) : '';
$ch_tag  = isset($_REQUEST['tag']) ? rawurldecode((string)$_REQUEST['tag']) : '';
$ch_tag  = htmlspecialchars(strip_tags(stripslashes(trim($ch_tag))), ENT_COMPAT, $config['charset']);
$ch_tag  = str_replace("&amp;frasl;", "/", $ch_tag);

// Режим страницы: конкретный тег, конкретный пользователь или список тегов.
$ch_mode = 'tags';
if ($ch_tag !== '') {
	$ch_mode = 'tag';
} elseif ($ch_name !== '') {
	$ch_mode = 'user';
}

require_once __DIR__ . '/contenthub.access.php';
$ch_group = $user_group[$member_id['user_group'] ?? 5] ?? [];
$ch_visibility = contenthubVisibilityWhere($ch_group, !empty($config['allow_multi_category']));
$ch_published = "p.approve = 1 AND p.date <= '" . date('Y-m-d H:i:s', $_TIME ?? time()) . "' AND (" . $ch_visibility . ")";

$ch_error = '';
$ch_notfound = false;

// Настройка применяется также к облаку тегов, до любых запросов к БД.
if (!$ch_config['allow_guest'] && !$is_logged) {
	http_response_code(403);
	$ch_error = 'Просмотр публикаций доступен только авторизованным пользователям.';
}

if (!$ch_error && $ch_mode === 'user') {

	// ---------------- Публикации пользователя ----------------
	if ($ch_name === '' && $is_logged) {
		$ch_name = $member_id['name'];
	}

	if ($ch_name === '') {
		$ch_error = 'Не указан пользователь. Откройте страницу по ссылке вида /user/имя/content/.';
	} elseif (!$ch_config['allow_guest'] && !$is_logged) {
		$ch_error = 'Просмотр публикаций доступен только авторизованным пользователям.';
	} else {
		$ch_row = $db->super_query("SELECT user_id, name, news_num FROM " . USERPREFIX . "_users WHERE name = '" . $db->safesql($ch_name) . "'");

		if (!isset($ch_row['user_id']) || !$ch_row['user_id']) {
			$ch_error = 'Пользователь "' . htmlspecialchars($ch_name, ENT_QUOTES, $config['charset']) . '" не найден.';
			$ch_notfound = true;
		} elseif (!$ch_row['news_num']) {
			$ch_error = 'У пользователя "' . htmlspecialchars($ch_row['name'], ENT_QUOTES, $config['charset']) . '" пока нет публикаций на сайте.';
		} else {
			$ch_name = $ch_row['name'];
		}
	}

} elseif (!$ch_error && $ch_mode === 'tag') {

	// ---------------- Публикации с тегом ----------------
	if (!$ch_config['allow_guest'] && !$is_logged) {
		$ch_error = 'Просмотр публикаций доступен только авторизованным пользователям.';
	} else {
		$ch_row = $db->super_query("SELECT t.tag, COUNT(*) AS news_num FROM " . PREFIX . "_tags t INNER JOIN " . PREFIX . "_post p ON (t.news_id = p.id) WHERE t.tag = '" . $db->safesql($ch_tag) . "' AND {$ch_published} GROUP BY t.tag");

		if (!isset($ch_row['tag']) || !$ch_row['news_num']) {
			$ch_error = 'Публикаций с тегом "' . $ch_tag . '" не найдено.';
			$ch_notfound = true;
		} else {
			// Каноническое написание тега из базы (в URL он в нижнем регистре).
			$ch_tag = $ch_row['tag'];
		}
	}
}

// Несуществующий пользователь/тег -> настоящий 404, а не soft 404.
if ($ch_notfound) {

	@header( "HTTP/1.1 404 Not Found" );

	if( $config['own_404'] AND file_exists(ROOT_DIR . '/404.html') ) {
		@header("Content-type: text/html; charset=utf-8");
		echo file_get_contents( ROOT_DIR . '/404.html' );
		die();
	}
}

if ($ch_mode === 'tags' && !$ch_error) {

	// ---------------- Страница /tags/ - облако тегов ----------------
	$ch_cloud = [];
	$db->query("SELECT t.tag, COUNT(*) AS news_num FROM " . PREFIX . "_tags t INNER JOIN " . PREFIX . "_post p ON (t.news_id = p.id) WHERE {$ch_published} GROUP BY t.tag ORDER BY t.tag");
	while ($ch_row = $db->get_row()) {
		$url_tag = trim($ch_row['tag']);
		$url_tag = str_replace(array("&#039;", "&quot;", "&amp;", "&amp;frasl;"), array("'", '"', "&", "&frasl;"), $url_tag);
		$ch_cloud[] = '<a class="uc-tag-chip" href="' . DLEUrl::BuildUrl('tags', ['tag' => rawurlencode(dle_strtolower($url_tag))]) . '">' . $ch_row['tag'] . '</a>';
	}
	$db->free();

	$tpl->load_template('taglist.tpl');
	$tpl->set('{tags}', implode('', $ch_cloud));

	$metatags['title'] = 'Теги - ' . $config['home_title'];
	$metatags['description'] = 'Все теги публикаций сайта ' . $config['home_title'] . ': услуги, кейсы, файлы.';
	$metatags['header_title'] = 'Теги';

} else {

	// ---------------- Страница пользователя или тега с блоками ----------------
	$tpl->load_template('contenthub.tpl');
	$tpl->set('{ch-name}', htmlspecialchars($ch_name, ENT_QUOTES, $config['charset']));
	$tpl->set('{ch-tag}', $ch_tag);
	$tpl->set('{ch-error}', $ch_error ? '<div class="uc-empty">' . $ch_error . '</div>' : '');

	// Активная ветка шаблона, неактивная вырезается целиком.
	if ($ch_mode === 'user') {
		$tpl->set('[ch-user]', '');
		$tpl->set('[/ch-user]', '');
		$tpl->set_block("'\\[ch-tag\\](.*?)\\[/ch-tag\\]'si", '');
	} else {
		$tpl->set('[ch-tag]', '');
		$tpl->set('[/ch-tag]', '');
		$tpl->set_block("'\\[ch-user\\](.*?)\\[/ch-user\\]'si", '');
	}

	if ($ch_error) {
		$tpl->set_block("'\\[ch-blocks\\](.*?)\\[/ch-blocks\\]'si", '');
	} else {
		$tpl->set('[ch-blocks]', '');
		$tpl->set('[/ch-blocks]', '');

		// Подстановка настроек блоков до компиляции, чтобы теги {custom}
		// получили готовые литеральные параметры.
		foreach ($ch_config['blocks'] as $ch_key => $ch_block) {
			if (!isset($ch_block['enable']) || !$ch_block['enable']) {
				$tpl->set_block("'\\[ch-" . $ch_key . "\\](.*?)\\[/ch-" . $ch_key . "\\]'si", '');
				continue;
			}

			// Пустой блок не выводим: ни заголовка, ни ссылки под блоком.
			$ch_where = $ch_published;
			if ($ch_mode === 'user') {
				$ch_where .= " AND p.autor = '" . $db->safesql($ch_name) . "'";
			} else {
				$ch_where .= " AND p.id IN (SELECT news_id FROM " . PREFIX . "_tags WHERE tag = '" . $db->safesql($ch_tag) . "')";
			}
			$ch_cats = preg_replace('/[^0-9,]/', '', (string)$ch_block['cats']);
			if ($ch_cats !== '') {
				$ch_cats = "'" . str_replace(",", "','", $ch_cats) . "'";
				if (!empty($config['allow_multi_category'])) {
					$ch_where .= " AND p.id IN (SELECT DISTINCT news_id FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ({$ch_cats}))";
				} else {
					$ch_where .= " AND p.category IN ({$ch_cats})";
				}
			}
			$ch_rows = $db->super_query("SELECT p.id FROM " . PREFIX . "_post p WHERE {$ch_where}", true);
			$ch_ids = array_values(array_unique(array_filter(array_map('intval', array_column($ch_rows, 'id')))));
			if (!$ch_ids) {
				$tpl->set_block("'\\[ch-" . $ch_key . "\\](.*?)\\[/ch-" . $ch_key . "\\]'si", '');
				continue;
			}

			// Constrain each block custom before compilation, including older site templates.
			$ch_pattern = '~(\\[ch-' . preg_quote($ch_key, '~') . '\\])(.*?)(\\[/ch-' . preg_quote($ch_key, '~') . '\\])~s';
			$tpl->copy_template = preg_replace_callback($ch_pattern, static function ($section) use ($ch_ids) {
				$body = preg_replace_callback('~\{custom\s+((?:[^{}]|\{[^{}]*\})*)\}~i', static function ($custom) use ($ch_ids) {
					$params = preg_replace('~\\b(?:id|cache)\\s*=\\s*([\"\'])(.*?)\\1~is', '', $custom[1]);
					return '{custom id="' . implode(',', $ch_ids) . '" cache="no" ' . trim($params) . '}';
				}, $section[2]);
				return $section[1] . $body . $section[3];
			}, $tpl->copy_template);

			$tpl->set('[ch-' . $ch_key . ']', '');
			$tpl->set('[/ch-' . $ch_key . ']', '');
			$tpl->set('{ch-' . $ch_key . '-title}', htmlspecialchars((string)$ch_block['title'], ENT_QUOTES, $config['charset']));
			$tpl->set('{ch-' . $ch_key . '-cats}', preg_replace('/[^0-9,]/', '', (string)$ch_block['cats']));
			$tpl->set('{ch-' . $ch_key . '-tpl}', preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$ch_block['tpl']));
			$tpl->set('{ch-' . $ch_key . '-order}', in_array($ch_block['order'], ['date', 'rating', 'news_read', 'comments', 'title']) ? $ch_block['order'] : 'date');
			$tpl->set('{ch-' . $ch_key . '-sort}', $ch_block['sort'] === 'asc' ? 'asc' : 'desc');
			$tpl->set('{ch-' . $ch_key . '-limit}', intval($ch_block['limit']) > 0 ? intval($ch_block['limit']) : 10);
		}

		if ($ch_mode === 'user') {
			$metatags['title'] = 'Публикации пользователя ' . $ch_name . ' - ' . $config['home_title'];
			$metatags['description'] = 'Все публикации пользователя ' . $ch_name . ' на сайте ' . $config['home_title'] . ': кейсы, услуги, файлы.';
			$metatags['header_title'] = 'Публикации пользователя ' . $ch_name;
		} else {
			$metatags['title'] = $ch_tag . ' - услуги, кейсы и файлы - ' . $config['home_title'];
			$metatags['description'] = 'Услуги и прайс, кейсы и файлы по теме «' . $ch_tag . '» на сайте ' . $config['home_title'] . '.';
			$metatags['header_title'] = $ch_tag . ': услуги, кейсы и файлы';
		}
	}
}

$tpl->compile('content');
$tpl->clear();

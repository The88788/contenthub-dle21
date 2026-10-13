<?php
/*
=====================================================
 ContentHub - Публикации пользователя (модуль для DLE 21)
 Админ-панель модуля: настройка блоков страницы публикаций.
 Доступ: ?mod=contenthub (только для группы администраторов)
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

// Доступ только администраторам (группа 1)
if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$ch_file = ENGINE_DIR . '/data/contenthub.php';

$ch_config = [];
if (file_exists($ch_file)) {
	$ch_config = include $ch_file;
}
if (!is_array($ch_config)) $ch_config = [];
if (!isset($ch_config['allow_guest'])) $ch_config['allow_guest'] = true;
if (!isset($ch_config['url_prefix'])) $ch_config['url_prefix'] = 'contenthub';
if (!isset($ch_config['blocks']) || !is_array($ch_config['blocks'])) $ch_config['blocks'] = [];

$ch_block_names = ['prices', 'cases', 'files'];

// ------------------------------------------------------------------
// Сохранение настроек
// ------------------------------------------------------------------
if ($_POST['action'] == "save") {

	if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		die( "Hacking attempt! User not found" );
	}

	$save_cfg = [];
	$save_cfg['allow_guest'] = isset($_POST['allow_guest']) ? true : false;
	$save_cfg['url_prefix']  = 'contenthub';
	$save_cfg['blocks']      = [];

	foreach ($ch_block_names as $b) {
		$cats = isset($_POST["{$b}_cats"]) ? preg_replace('/[^0-9,]/', '', $_POST["{$b}_cats"]) : '';
		$cats = trim($cats, ',');

		$save_cfg['blocks'][$b] = [
			'enable' => isset($_POST["{$b}_enable"]) ? 1 : 0,
			'title'  => isset($_POST["{$b}_title"]) ? trim(strip_tags((string)$_POST["{$b}_title"])) : '',
			'cats'   => $cats,
			'tpl'    => isset($_POST["{$b}_tpl"]) ? preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_POST["{$b}_tpl"]) : '',
			'order'  => isset($_POST["{$b}_order"]) ? (string)$_POST["{$b}_order"] : 'date',
			'sort'   => isset($_POST["{$b}_sort"]) && $_POST["{$b}_sort"] === 'asc' ? 'asc' : 'desc',
			'limit'  => isset($_POST["{$b}_limit"]) ? intval($_POST["{$b}_limit"]) : 10,
		];
		if ($save_cfg['blocks'][$b]['limit'] < 1) $save_cfg['blocks'][$b]['limit'] = 10;
		if (!$save_cfg['blocks'][$b]['title']) $save_cfg['blocks'][$b]['title'] = $b;
		if (!$save_cfg['blocks'][$b]['tpl'])   $save_cfg['blocks'][$b]['tpl']   = 'shortstory';
	}

	@file_put_contents($ch_file, "<?php\n\n// ContentHub для DLE - конфигурация модуля.\n// Редактируется через админ-панель: ?mod=contenthub\n\nreturn " . var_export($save_cfg, true) . ";\n", LOCK_EX);

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '200', 'ContentHub')" );

	msg( "success", "Настройки сохранены", "Конфигурация модуля ContentHub успешно сохранена.", "?mod=contenthub" );
}

// ------------------------------------------------------------------
// Форма настроек
// ------------------------------------------------------------------
// Версия модуля. При каждом исправлении или новой функции увеличивайте номер.
define( 'CH_VERSION', '1.1.1' );

echoheader( "<i class=\"fa fa-newspaper-o position-left\"></i><span class=\"text-semibold\">Настройки Content Hub</span> <span class=\"text-muted text-size-small\">v" . CH_VERSION . "</span>", "Публикации пользователя и тегов: блоки страниц /user/ИМЯ/content/ и /tags/ТЕГ/" );

$ch_orders = [
	'date'       => 'По дате',
	'rating'     => 'По рейтингу',
	'news_read'  => 'По просмотрам',
	'comments'   => 'По комментариям',
	'title'      => 'По заголовку',
];

$ch_sorts = [ 'desc' => 'По убыванию', 'asc' => 'По возрастанию' ];

function ch_show_row( $title = "", $description = "", $field = "" ) {
	echo "<tr>
	<td class=\"col-xs-6 col-sm-6 col-md-7\"><h6 class=\"media-heading text-semibold\">{$title}</h6><div class=\"text-muted text-size-small hidden-xs\">{$description}</div></td>
	<td class=\"col-xs-6 col-sm-6 col-md-5\">{$field}</td>
	</tr>";
}

function ch_make_dropdown( $options, $name, $selected ) {
	$output = "<select class=\"uniform\" name=\"{$name}\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"{$value}\"";
		if ( $selected == $value ) $output .= " selected ";
		$output .= ">{$description}</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function ch_make_checkbox( $name, $selected ) {
	$checked = $selected ? "checked" : "";
	return "<input class=\"switch\" type=\"checkbox\" name=\"{$name}\" value=\"1\" {$checked}>";
}

echo <<<HTML
<form method="post" name="contenthub" action="?mod=contenthub" class="form-horizontal">
<input type="hidden" name="action" value="save">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="panel panel-default">
  <div class="panel-heading">Общие настройки</div>
  <div class="table-responsive">
    <table class="table">
HTML;

ch_show_row(
	"Доступ для гостей",
	"Если выключено, страница публикаций открывается только авторизованным пользователям.",
	ch_make_checkbox("allow_guest", $ch_config['allow_guest'])
);

echo <<<HTML
    </table>
  </div>
</div>
HTML;

$ch_block_num = 0;
foreach ($ch_block_names as $b) {
	$ch_block_num++;
	$blk = isset($ch_config['blocks'][$b]) ? $ch_config['blocks'][$b] : [];
	$enable = !empty($blk['enable']);
	$title  = isset($blk['title']) ? htmlspecialchars($blk['title'], ENT_QUOTES, $config['charset']) : $b;
	$cats   = isset($blk['cats']) ? htmlspecialchars($blk['cats'], ENT_QUOTES, $config['charset']) : '';
	$tpl_n  = isset($blk['tpl']) ? htmlspecialchars($blk['tpl'], ENT_QUOTES, $config['charset']) : '';
	$order  = isset($blk['order']) ? $blk['order'] : 'date';
	$sort   = isset($blk['sort']) ? $blk['sort'] : 'desc';
	$limit  = isset($blk['limit']) ? intval($blk['limit']) : 10;

	echo <<<HTML
<div class="panel panel-default">
  <div class="panel-heading">Контент блок #{$ch_block_num}</div>
  <div class="table-responsive">
    <table class="table">
HTML;

	ch_show_row("Блок включен", "Секция [ch-{$b}] выводится на странице публикаций.", ch_make_checkbox("{$b}_enable", $enable));
	ch_show_row("Заголовок блока", "Выводится над списком постов (тег {ch-{$b}-title}).", "<input type=\"text\" class=\"form-control\" name=\"{$b}_title\" value=\"{$title}\">");
	ch_show_row("Категории (ID)", "ID категорий через запятую, например: 2,9,10. Можно указать одну категорию.", "<input type=\"text\" class=\"form-control\" name=\"{$b}_cats\" value=\"{$cats}\">");
	ch_show_row("Шаблон поста", "Имя tpl-файла из папки шаблона без расширения, например: cases, prices, files.", "<input type=\"text\" class=\"form-control\" name=\"{$b}_tpl\" value=\"{$tpl_n}\">");
	ch_show_row("Сортировка", "Поле сортировки списка постов в блоке.", ch_make_dropdown($ch_orders, "{$b}_order", $order));
	ch_show_row("Направление", "Порядок вывода постов в блоке.", ch_make_dropdown($ch_sorts, "{$b}_sort", $sort));
	ch_show_row("Лимит постов", "Максимальное количество постов в блоке.", "<input type=\"text\" class=\"form-control\" name=\"{$b}_limit\" value=\"{$limit}\">");

	echo <<<HTML
    </table>
  </div>
</div>
HTML;
}

echo <<<HTML
<div class="panel panel-default">
  <div class="panel-footer">
    <button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>Сохранить настройки</button>
  </div>
</div>
</form>
HTML;

echofooter();

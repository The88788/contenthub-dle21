{* ContentHub 1.1.0: страница публикаций по пользователю /user/ИМЯ/content/
   или по тегу /tags/ТЕГ/. Модуль оставляет одну из веток [ch-user]/[ch-tag],
   вторую вырезает целиком. Блоки собираются штатными {custom}: у пользователя
   фильтр author=, у тега - tags=. Категории, шаблон, сортировка и лимит блоков
   задаются в админ-панели (?mod=contenthub). Отключенный или пустой блок
   вырезается модулем вместе с секцией (заголовок и ссылка не выводятся).
   Подстановки тегов ch происходят ДО парсинга {custom}.
   Порядок блоков: прайс (продаём услуги) -> кейсы -> файлы.
   tpl-постов (prices/cases/files) - примеры; укажите свои в настройках. *}
<section class="uc-page">
  <h1>[ch-user]Публикации пользователя {ch-name}[/ch-user][ch-tag]{ch-tag}: услуги, кейсы и файлы[/ch-tag]</h1>
  {ch-error}
  [ch-blocks]
  [ch-user]
  [ch-prices]
  <div class="uc-block">
    <h2 class="uc-block-title">{ch-prices-title}</h2>
    <div class="price-wrap">
      <div class="price-tabs" data-price-tabs aria-label="Категории прайса">
        <a href="/prices/" data-tab="all">Все</a>
        <a href="/prices/sites/" data-tab="sites">Сайты</a>
        <a href="/prices/sites/wordpress/" data-tab="wordpress" class="ptab-sub">WordPress</a>
        <a href="/prices/sites/dle/" data-tab="dle" class="ptab-sub">DLE</a>
        <a href="/prices/sites/modx/" data-tab="modx" class="ptab-sub">MODX</a>
        <a href="/prices/sites/joomla/" data-tab="joomla" class="ptab-sub">Joomla</a>
        <a href="/prices/sites/laravel/" data-tab="laravel" class="ptab-sub">Laravel</a>
        <a href="/prices/sites/shop/" data-tab="shop" class="ptab-sub">Интернет-магазины</a>
        <a href="/prices/sites/shop/opencart/" data-tab="opencart" class="ptab-sub">OpenCart</a>
        <a href="/prices/sites/bitrix/" data-tab="bitrix" class="ptab-sub">1С-Битрикс</a>
        <a href="/prices/servers/" data-tab="servers">Серверы</a>
        <a href="/prices/servers/vpn/" data-tab="vpn" class="ptab-sub">VPN</a>
        <a href="/prices/servers/security/" data-tab="security" class="ptab-sub">Безопасность</a>
        <a href="/prices/automation/" data-tab="automation">Автоматизация</a>
        <a href="/prices/python/" data-tab="python">Python</a>
        <a href="/prices/python/django/" data-tab="django" class="ptab-sub">Django</a>
      </div>
      <div class="price-table">{custom author="{ch-name}" category="{ch-prices-cats}" template="{ch-prices-tpl}" order="{ch-prices-order}" sort="{ch-prices-sort}" limit="{ch-prices-limit}"}</div>
      <div class="price-note"><span>*</span><p>Цены указаны для ориентира и не являются публичной офертой. Большие задачи разбиваю на понятные этапы.</p><a href="/#contact" data-ym-goal="contact_cta">Получить оценку ↓</a></div>
    </div>
    <div class="uc-block-more"><a href="/prices/">Весь прайс <span>&rarr;</span></a></div>
  </div>
  [/ch-prices]
  [ch-cases]
  <div class="uc-block">
    <h2 class="uc-block-title">{ch-cases-title}</h2>
    {custom author="{ch-name}" category="{ch-cases-cats}" template="{ch-cases-tpl}" order="{ch-cases-order}" sort="{ch-cases-sort}" limit="{ch-cases-limit}"}
    <div class="uc-block-more"><a href="/cases/">Все кейсы <span>&rarr;</span></a></div>
  </div>
  [/ch-cases]
  [ch-files]
  <div class="uc-block">
    <h2 class="uc-block-title">{ch-files-title}</h2>
    {custom author="{ch-name}" category="{ch-files-cats}" template="{ch-files-tpl}" order="{ch-files-order}" sort="{ch-files-sort}" limit="{ch-files-limit}"}
    <div class="uc-block-more"><a href="/files/">Все файлы <span>&rarr;</span></a></div>
  </div>
  [/ch-files]
  [/ch-user]
  [ch-tag]
  [ch-prices]
  <div class="uc-block">
    <h2 class="uc-block-title">{ch-prices-title}</h2>
    <div class="price-wrap">
      <div class="price-tabs" data-price-tabs aria-label="Категории прайса">
        <a href="/prices/" data-tab="all">Все</a>
        <a href="/prices/sites/" data-tab="sites">Сайты</a>
        <a href="/prices/sites/wordpress/" data-tab="wordpress" class="ptab-sub">WordPress</a>
        <a href="/prices/sites/dle/" data-tab="dle" class="ptab-sub">DLE</a>
        <a href="/prices/sites/modx/" data-tab="modx" class="ptab-sub">MODX</a>
        <a href="/prices/sites/joomla/" data-tab="joomla" class="ptab-sub">Joomla</a>
        <a href="/prices/sites/laravel/" data-tab="laravel" class="ptab-sub">Laravel</a>
        <a href="/prices/sites/shop/" data-tab="shop" class="ptab-sub">Интернет-магазины</a>
        <a href="/prices/sites/shop/opencart/" data-tab="opencart" class="ptab-sub">OpenCart</a>
        <a href="/prices/sites/bitrix/" data-tab="bitrix" class="ptab-sub">1С-Битрикс</a>
        <a href="/prices/servers/" data-tab="servers">Серверы</a>
        <a href="/prices/servers/vpn/" data-tab="vpn" class="ptab-sub">VPN</a>
        <a href="/prices/servers/security/" data-tab="security" class="ptab-sub">Безопасность</a>
        <a href="/prices/automation/" data-tab="automation">Автоматизация</a>
        <a href="/prices/python/" data-tab="python">Python</a>
        <a href="/prices/python/django/" data-tab="django" class="ptab-sub">Django</a>
      </div>
      <div class="price-table">{custom tags="{ch-tag}" category="{ch-prices-cats}" template="{ch-prices-tpl}" order="{ch-prices-order}" sort="{ch-prices-sort}" limit="{ch-prices-limit}"}</div>
      <div class="price-note"><span>*</span><p>Цены указаны для ориентира и не являются публичной офертой. Большие задачи разбиваю на понятные этапы.</p><a href="/#contact" data-ym-goal="contact_cta">Получить оценку ↓</a></div>
    </div>
    <div class="uc-block-more"><a href="/prices/">Весь прайс <span>&rarr;</span></a></div>
  </div>
  [/ch-prices]
  [ch-cases]
  <div class="uc-block">
    <h2 class="uc-block-title">{ch-cases-title}</h2>
    {custom tags="{ch-tag}" category="{ch-cases-cats}" template="{ch-cases-tpl}" order="{ch-cases-order}" sort="{ch-cases-sort}" limit="{ch-cases-limit}"}
    <div class="uc-block-more"><a href="/cases/">Все кейсы <span>&rarr;</span></a></div>
  </div>
  [/ch-cases]
  [ch-files]
  <div class="uc-block">
    <h2 class="uc-block-title">{ch-files-title}</h2>
    {custom tags="{ch-tag}" category="{ch-files-cats}" template="{ch-files-tpl}" order="{ch-files-order}" sort="{ch-files-sort}" limit="{ch-files-limit}"}
    <div class="uc-block-more"><a href="/files/">Все файлы <span>&rarr;</span></a></div>
  </div>
  [/ch-files]
  [/ch-tag]
  [/ch-blocks]
</section>

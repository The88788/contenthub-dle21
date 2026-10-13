<?php
if (!defined('DATALIFEENGINE')) { http_response_code(403); exit; }

/** Category visibility of short publications, matching DLE's allow_short semantics. */
function contenthubVisibilityWhere(array $group, bool $multi): string {
    if (!isset($group['allow_short'], $group['allow_cats'], $group['not_allow_cats'])) return '0=1';
    if (!empty($group['allow_short'])) return '1=1';
    $ids = static function ($value): array {
        return array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$value)), static fn($id) => $id > 0)));
    };
    $where = [];
    if (trim((string)$group['allow_cats']) !== 'all') {
        $allowed = $ids($group['allow_cats']);
        if (!$allowed) return '0=1';
        $list = implode(',', $allowed);
        $where[] = $multi
            ? 'p.id IN (SELECT news_id FROM '.PREFIX.'_post_extras_cats WHERE cat_id IN ('.$list.'))'
            : "p.category IN ('".implode("','", $allowed)."')";
    }
    $denied = $ids($group['not_allow_cats']);
    if ($denied) {
        $list = implode(',', $denied);
        $where[] = $multi
            ? 'p.id NOT IN (SELECT news_id FROM '.PREFIX.'_post_extras_cats WHERE cat_id IN ('.$list.'))'
            : "p.category NOT IN ('".implode("','", $denied)."')";
    }
    return $where ? implode(' AND ', $where) : '1=1';
}

<?php
/**
 * Plugin Name: Athlé Sud 69 — Custom Post Types
 * Description: Enregistre les types de contenu du club (événements, résultats, records) indépendamment du thème.
 * Version: 1.0.0
 * Author: Athlé Sud 69
 */
declare(strict_types=1);

// ── Custom Post Types ────────────────────────────────────────────────────────

add_action('init', function (): void {

    register_post_type('athle_event', [
        'labels'      => [
            'name'          => 'Événements',
            'singular_name' => 'Événement',
            'add_new_item'  => 'Ajouter un événement',
            'edit_item'     => 'Modifier l\'événement',
        ],
        'public'       => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-calendar-alt',
        'supports'     => ['title', 'editor'],
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'agenda'],
    ]);

    register_post_type('athle_result', [
        'labels'      => [
            'name'          => 'Résultats',
            'singular_name' => 'Résultat',
            'add_new_item'  => 'Ajouter un résultat',
            'edit_item'     => 'Modifier le résultat',
        ],
        'public'       => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-awards',
        'supports'     => ['title', 'editor'],
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'resultats'],
    ]);

    register_post_type('athle_record', [
        'labels'      => [
            'name'          => 'Records',
            'singular_name' => 'Record',
            'add_new_item'  => 'Ajouter un record',
            'edit_item'     => 'Modifier le record',
        ],
        'public'       => false,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-star-filled',
        'supports'     => ['title'],
    ]);
});

// ── Meta boxes ───────────────────────────────────────────────────────────────

add_action('add_meta_boxes', function (): void {
    add_meta_box('athle_event_meta', 'Détails de l\'événement', 'athle_event_meta_cb', 'athle_event', 'normal');
    add_meta_box('athle_record_meta', 'Détails du record',      'athle_record_meta_cb', 'athle_record', 'normal');
});

function athle_event_meta_cb(WP_Post $post): void
{
    $date     = get_post_meta($post->ID, '_event_date', true);
    $location = get_post_meta($post->ID, '_event_location', true);
    $type     = get_post_meta($post->ID, '_event_type', true);
    wp_nonce_field('athle_event_meta', 'athle_event_nonce');
    echo '<p><label><strong>Date</strong><br>
          <input type="date" name="event_date" value="' . esc_attr($date) . '" style="width:100%;margin-top:.3rem"></label></p>';
    echo '<p><label><strong>Lieu</strong><br>
          <input type="text" name="event_location" value="' . esc_attr($location) . '" style="width:100%;margin-top:.3rem"></label></p>';
    echo '<p><label><strong>Type</strong><br>
          <select name="event_type" style="width:100%;margin-top:.3rem">';
    foreach ([
        'competition' => 'Compétition',
        'club'        => 'Événement club',
        'training'    => 'Entraînement',
        'meeting'     => 'Réunion',
        'other'       => 'Autre',
    ] as $val => $lbl) {
        echo '<option value="' . esc_attr($val) . '"' . selected($type, $val, false) . '>' . esc_html($lbl) . '</option>';
    }
    echo '</select></label></p>';
}

function athle_record_meta_cb(WP_Post $post): void
{
    $fields = [
        ['Discipline',   'record_discipline', 'text'],
        ['Athlète',      'record_athlete',    'text'],
        ['Performance',  'record_mark',       'text'],
        ['Catégorie',    'record_category',   'text'],
        ['Date',         'record_date',       'date'],
    ];
    wp_nonce_field('athle_record_meta', 'athle_record_nonce');
    foreach ($fields as [$lbl, $name, $type]) {
        $val = get_post_meta($post->ID, '_' . $name, true);
        echo '<p><label><strong>' . esc_html($lbl) . '</strong><br>
              <input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($val) . '" style="width:100%;margin-top:.3rem"></label></p>';
    }
}

// ── Colonnes admin — Événements ─────────────────────────────────────────────

add_filter('manage_athle_event_posts_columns', function (array $cols): array {
    unset($cols['date']);
    return array_merge($cols, [
        'event_date'     => 'Date',
        'event_location' => 'Lieu',
        'event_type'     => 'Type',
    ]);
});

add_action('manage_athle_event_posts_custom_column', function (string $col, int $post_id): void {
    $types = [
        'competition' => 'Compétition',
        'club'        => 'Événement club',
        'training'    => 'Entraînement',
        'meeting'     => 'Réunion',
        'other'       => 'Autre',
    ];
    match ($col) {
        'event_date'     => print(esc_html(get_post_meta($post_id, '_event_date', true) ?: '—')),
        'event_location' => print(esc_html(get_post_meta($post_id, '_event_location', true) ?: '—')),
        'event_type'     => print(esc_html($types[get_post_meta($post_id, '_event_type', true)] ?? '—')),
        default          => null,
    };
}, 10, 2);

add_filter('manage_edit-athle_event_sortable_columns', function (array $cols): array {
    $cols['event_date'] = 'event_date';
    return $cols;
});

add_action('pre_get_posts', function (WP_Query $q): void {
    if (!is_admin() || $q->get('post_type') !== 'athle_event' || !$q->is_main_query()) return;
    if ($q->get('orderby') === 'event_date') {
        $q->set('meta_key', '_event_date');
        $q->set('orderby', 'meta_value');
    }
});

// ── Sauvegarde des meta ──────────────────────────────────────────────────────

add_action('save_post_athle_event', function (int $post_id): void {
    if (!isset($_POST['athle_event_nonce']) || !wp_verify_nonce($_POST['athle_event_nonce'], 'athle_event_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (['event_date' => '_event_date', 'event_location' => '_event_location', 'event_type' => '_event_type'] as $field => $meta) {
        if (isset($_POST[$field])) update_post_meta($post_id, $meta, sanitize_text_field($_POST[$field]));
    }
});

add_action('save_post_athle_record', function (int $post_id): void {
    if (!isset($_POST['athle_record_nonce']) || !wp_verify_nonce($_POST['athle_record_nonce'], 'athle_record_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (['record_discipline', 'record_athlete', 'record_mark', 'record_category', 'record_date'] as $field) {
        if (isset($_POST[$field])) update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
    }
});

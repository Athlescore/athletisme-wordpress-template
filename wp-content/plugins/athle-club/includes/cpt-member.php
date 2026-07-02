<?php
declare(strict_types=1);

// ── CPT athle_member ─────────────────────────────────────────────────────────

add_action('init', function (): void {
    register_post_type('athle_member', [
        'labels' => [
            'name'          => 'Licenciés',
            'singular_name' => 'Licencié',
            'add_new_item'  => 'Ajouter un licencié',
            'edit_item'     => 'Modifier le licencié',
            'search_items'  => 'Rechercher un licencié',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-id',
        'supports'     => ['title'],
        'capabilities' => [
            'edit_post'          => 'manage_options',
            'edit_posts'         => 'manage_options',
            'edit_others_posts'  => 'manage_options',
            'publish_posts'      => 'manage_options',
            'read_post'          => 'manage_options',
            'read_private_posts' => 'manage_options',
            'delete_post'        => 'manage_options',
        ],
    ]);
});

// ── Meta box ─────────────────────────────────────────────────────────────────

add_action('add_meta_boxes', function (): void {
    add_meta_box('athle_member_meta', 'Informations du licencié', 'athle_member_meta_cb', 'athle_member', 'normal');
});

function athle_member_meta_cb(WP_Post $post): void
{
    $fields = [
        'member_firstname'   => ['Prénom',           'text'],
        'member_birthdate'   => ['Date de naissance','date'],
        'member_licence'     => ['N° licence FFA',   'text'],
        'member_email'       => ['Email',            'email'],
        'member_phone'       => ['Téléphone',        'tel'],
        'member_category'    => ['Catégorie',        'select'],
        'member_disciplines' => ['Disciplines',      'text'],
        'member_coach'       => ['Entraîneur',       'coach_select'],
    ];

    $cats = [
        ''        => '— Non précisée —',
        'poussin' => 'Poussin (U10)',
        'pupille' => 'Pupille (U12)',
        'benjam'  => 'Benjamin (U14)',
        'minime'  => 'Minime (U16)',
        'cadet'   => 'Cadet (U18)',
        'junior'  => 'Junior (U20)',
        'espoir'  => 'Espoir (U23)',
        'senior'  => 'Senior',
        'master'  => 'Master',
    ];

    wp_nonce_field('athle_member_meta', 'athle_member_nonce');

    echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.5rem">';
    foreach ($fields as $name => [$label, $type]) {
        $val = get_post_meta($post->ID, '_' . $name, true);
        echo '<p><label><strong>' . esc_html($label) . '</strong><br>';

        if ($type === 'select') {
            echo '<select name="' . esc_attr($name) . '" style="width:100%;margin-top:.3rem">';
            foreach ($cats as $v => $l) {
                echo '<option value="' . esc_attr($v) . '"' . selected($val, $v, false) . '>' . esc_html($l) . '</option>';
            }
            echo '</select>';
        } elseif ($type === 'coach_select') {
            $coaches = get_users(['role' => 'athle_coach']);
            echo '<select name="' . esc_attr($name) . '" style="width:100%;margin-top:.3rem">';
            echo '<option value="">— Aucun —</option>';
            foreach ($coaches as $coach) {
                echo '<option value="' . esc_attr($coach->ID) . '"' . selected($val, $coach->ID, false) . '>' . esc_html($coach->display_name) . '</option>';
            }
            echo '</select>';
        } else {
            echo '<input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($val) . '" style="width:100%;margin-top:.3rem">';
        }

        echo '</label></p>';
    }
    echo '</div>';
}

// ── Sauvegarde ───────────────────────────────────────────────────────────────

add_action('save_post_athle_member', function (int $post_id): void {
    if (!isset($_POST['athle_member_nonce']) || !wp_verify_nonce($_POST['athle_member_nonce'], 'athle_member_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $text_fields = ['member_firstname', 'member_licence', 'member_disciplines', 'member_phone'];
    $safe_fields = ['member_birthdate', 'member_category'];

    foreach ($text_fields as $f) {
        if (isset($_POST[$f])) update_post_meta($post_id, '_' . $f, sanitize_text_field($_POST[$f]));
    }
    foreach ($safe_fields as $f) {
        if (isset($_POST[$f])) update_post_meta($post_id, '_' . $f, sanitize_key($_POST[$f]));
    }
    if (isset($_POST['member_email'])) {
        update_post_meta($post_id, '_member_email', sanitize_email($_POST['member_email']));
    }
    if (isset($_POST['member_coach'])) {
        update_post_meta($post_id, '_member_coach', absint($_POST['member_coach']));
    }
});

// ── Colonnes admin ────────────────────────────────────────────────────────────

add_filter('manage_athle_member_posts_columns', function (array $cols): array {
    unset($cols['date']);
    return array_merge($cols, [
        'member_category'   => 'Catégorie',
        'member_licence'    => 'Licence FFA',
        'member_disciplines'=> 'Disciplines',
        'member_coach'      => 'Entraîneur',
    ]);
});

add_action('manage_athle_member_posts_custom_column', function (string $col, int $id): void {
    $cats = [
        'poussin' => 'Poussin', 'pupille' => 'Pupille', 'benjam' => 'Benjamin',
        'minime'  => 'Minime',  'cadet'   => 'Cadet',   'junior' => 'Junior',
        'espoir'  => 'Espoir',  'senior'  => 'Senior',  'master' => 'Master',
    ];
    match ($col) {
        'member_category'    => print(esc_html($cats[get_post_meta($id, '_member_category', true)] ?? '—')),
        'member_licence'     => print(esc_html(get_post_meta($id, '_member_licence', true) ?: '—')),
        'member_disciplines' => print(esc_html(get_post_meta($id, '_member_disciplines', true) ?: '—')),
        'member_coach'       => print(esc_html(get_user_by('id', (int) get_post_meta($id, '_member_coach', true))?->display_name ?? '—')),
        default => null,
    };
}, 10, 2);

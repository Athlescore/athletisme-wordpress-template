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
    $lat      = get_post_meta($post->ID, '_event_lat', true);
    // Pré-sélectionne le type depuis l'URL lors de la création
    $type     = get_post_meta($post->ID, '_event_type', true)
                ?: sanitize_key($_GET['event_type'] ?? '');
    wp_nonce_field('athle_event_meta', 'athle_event_nonce');
    echo '<p><label><strong>Date</strong><br>
          <input type="date" name="event_date" value="' . esc_attr($date) . '" style="width:100%;margin-top:.3rem"></label></p>';
    echo '<p><label><strong>Lieu</strong> <span style="color:#666;font-weight:400">(adresse complète — la carte se génère automatiquement à la sauvegarde)</span><br>
          <textarea name="event_location" rows="3" style="width:100%;margin-top:.3rem" placeholder="ex: Stade de Feyzin&#10;Avenue des Sports&#10;69320 Feyzin">' . esc_textarea($location) . '</textarea></label></p>';
    if ($lat) {
        echo '<p style="color:#666;font-size:.85em">📍 Carte géocodée — modifier l\'adresse et sauvegarder pour mettre à jour.</p>';
    }
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
    foreach (['event_date' => '_event_date', 'event_type' => '_event_type'] as $field => $meta) {
        if (isset($_POST[$field])) update_post_meta($post_id, $meta, sanitize_text_field($_POST[$field]));
    }
    // Géocode l'adresse si elle a changé
    if (isset($_POST['event_location'])) {
        $new_loc = sanitize_textarea_field($_POST['event_location']);
        $old_loc = get_post_meta($post_id, '_event_location', true);
        update_post_meta($post_id, '_event_location', $new_loc);
        if ($new_loc && $new_loc !== $old_loc) {
            $coords = athle_geocode_location($new_loc);
            if ($coords) {
                update_post_meta($post_id, '_event_lat', $coords['lat']);
                update_post_meta($post_id, '_event_lng', $coords['lng']);
            }
        }
    }
});

add_action('save_post_athle_record', function (int $post_id): void {
    if (!isset($_POST['athle_record_nonce']) || !wp_verify_nonce($_POST['athle_record_nonce'], 'athle_record_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (['record_discipline', 'record_athlete', 'record_mark', 'record_category', 'record_date'] as $field) {
        if (isset($_POST[$field])) update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
    }
});

// ── Géocodage Nominatim ──────────────────────────────────────────────────────

function athle_geocode_location(string $address): ?array
{
    $url      = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q'      => $address,
        'format' => 'json',
        'limit'  => 1,
    ]);
    $response = wp_remote_get($url, [
        'headers' => ['User-Agent' => 'AthlesSud69/1.0'],
        'timeout' => 5,
    ]);
    if (is_wp_error($response)) return null;
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($data[0])) return null;
    return ['lat' => (float) $data[0]['lat'], 'lng' => (float) $data[0]['lon']];
}

// ── Carte Leaflet [athle_map] ────────────────────────────────────────────────

$GLOBALS['athle_maps'] = [];

add_shortcode('athle_map', function (): string {
    global $post;
    $id  = $post->ID ?? 0;
    $lat = (float) get_post_meta($id, '_event_lat', true);
    $lng = (float) get_post_meta($id, '_event_lng', true);
    $loc = get_post_meta($id, '_event_location', true) ?: '';

    if (!$lat || !$lng) {
        return '<p style="color:#888;font-size:.9rem;border:1px dashed #ccc;padding:.6rem .9rem;border-radius:6px">'
             . '📍 Carte disponible après renseignement et sauvegarde de l\'adresse.</p>';
    }

    $map_id  = 'athle-map-' . $id;
    $title   = esc_html(get_the_title($id));
    $address = nl2br(esc_html($loc));
    $osm_url = esc_url('https://www.openstreetmap.org/?mlat=' . $lat . '&mlon=' . $lng . '#map=15/' . $lat . '/' . $lng);
    $popup   = esc_js($loc ?: $title);

    $GLOBALS['athle_maps'][$map_id] = ['lat' => $lat, 'lng' => $lng, 'popup' => $popup];

    wp_enqueue_style('leaflet',  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',  [], '1.9.4', true);
    wp_add_inline_style('leaflet', '
        .athle-map-card{border:1.5px solid var(--line,#e0e0e0);border-radius:12px;overflow:hidden;margin:1rem 0;background:var(--chalk,#fff)}
        .athle-map-card__header{display:flex;align-items:flex-start;gap:.75rem;padding:.9rem 1rem;border-bottom:1px solid var(--line,#e0e0e0)}
        .athle-map-card__header svg{flex-shrink:0;margin-top:.15rem;color:var(--track,#c0392b)}
        .athle-map-card__name{display:block;font-size:.95rem;font-weight:700;color:var(--ink,#111);margin-bottom:.15rem}
        .athle-map-card__addr{font-size:.82rem;color:var(--steel,#555);line-height:1.5}
        .athle-map-card__map{height:280px}
        .athle-map-card__footer{padding:.6rem 1rem;border-top:1px solid var(--line,#e0e0e0);font-size:.82rem}
        .athle-map-card__footer a{color:var(--track,#c0392b);font-weight:600;text-decoration:none}
        .athle-map-card__footer a:hover{text-decoration:underline}
    ');

    static $footer_hooked = false;
    if (!$footer_hooked) {
        add_action('wp_footer', function (): void {
            if (empty($GLOBALS['athle_maps'])) return;
            $cfg = wp_json_encode($GLOBALS['athle_maps']);
            echo "<script>
window.addEventListener('load',function(){
  var maps={$cfg};
  Object.entries(maps).forEach(function(e){
    var id=e[0],c=e[1];
    if(!document.getElementById(id))return;
    var m=L.map(id).setView([c.lat,c.lng],15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(m);
    L.marker([c.lat,c.lng]).addTo(m).bindPopup(c.popup).openPopup();
  });
});
</script>";
        }, 20);
        $footer_hooked = true;
    }

    $pin_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';

    return '
<div class="athle-map-card">
  <div class="athle-map-card__header">
    ' . $pin_icon . '
    <div>
      <strong class="athle-map-card__name">' . $title . '</strong>
      ' . ($address ? '<span class="athle-map-card__addr">' . $address . '</span>' : '') . '
    </div>
  </div>
  <div id="' . esc_attr($map_id) . '" class="athle-map-card__map"></div>
  <div class="athle-map-card__footer">
    <a href="' . $osm_url . '" target="_blank" rel="noopener noreferrer">Voir l\'itinéraire sur OpenStreetMap →</a>
  </div>
</div>';
});

// ── Templates de blocs par type d'événement ──────────────────────────────────

function athle_event_template(string $type): string
{
    $map   = "<!-- wp:shortcode -->\n[athle_map]\n<!-- /wp:shortcode -->";
    $infos = <<<BLOCKS
<!-- wp:heading {"level":3} -->
<h3>Informations pratiques</h3>
<!-- /wp:heading -->
<!-- wp:list -->
<ul class="wp-block-list"><li>Tarif : </li><li>Contact : </li></ul>
<!-- /wp:list -->
<!-- wp:heading {"level":3} -->
<h3>Lieu</h3>
<!-- /wp:heading -->
{$map}
BLOCKS;

    return match ($type) {
        'competition' => <<<BLOCKS
<!-- wp:paragraph -->
<p>Décrivez la compétition et les épreuves proposées.</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3>Programme</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Horaires et ordre des épreuves...</p>
<!-- /wp:paragraph -->
{$infos}
BLOCKS,
        'club' => <<<BLOCKS
<!-- wp:paragraph -->
<p>Décrivez l'événement.</p>
<!-- /wp:paragraph -->
{$infos}
BLOCKS,
        'training' => <<<BLOCKS
<!-- wp:paragraph -->
<p>Informations sur la séance d'entraînement.</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":3} -->
<h3>Lieu</h3>
<!-- /wp:heading -->
{$map}
BLOCKS,
        'meeting' => <<<BLOCKS
<!-- wp:paragraph -->
<p>Ordre du jour :</p>
<!-- /wp:paragraph -->
<!-- wp:list -->
<ul class="wp-block-list"><li></li><li></li><li></li></ul>
<!-- /wp:list -->
<!-- wp:heading {"level":3} -->
<h3>Lieu</h3>
<!-- /wp:heading -->
{$map}
BLOCKS,
        default => "<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->\n{$map}",
    };
}

add_filter('default_content', function (string $content, WP_Post $post): string {
    if ($post->post_type !== 'athle_event') return $content;
    $type = sanitize_key($_GET['event_type'] ?? '');
    return $type ? athle_event_template($type) : $content;
}, 10, 2);

// ── Liens rapides dans le menu admin ─────────────────────────────────────────

add_action('admin_menu', function (): void {
    foreach ([
        'competition' => '+ Compétition',
        'club'        => '+ Événement club',
        'training'    => '+ Entraînement',
        'meeting'     => '+ Réunion',
    ] as $type => $label) {
        add_submenu_page(
            'edit.php?post_type=athle_event',
            $label, $label,
            'edit_posts',
            'post-new.php?post_type=athle_event&event_type=' . $type
        );
    }
});

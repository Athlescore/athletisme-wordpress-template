<?php
declare(strict_types=1);

require_once get_template_directory() . '/inc/shortcodes.php';

function athle_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo');

    register_nav_menus([
        'primary' => 'Navigation principale',
        'footer'  => 'Pied de page',
    ]);

    load_theme_textdomain('athle-sud-69', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'athle_setup');

function athle_enqueue(): void
{
    // Google Fonts
    wp_enqueue_style(
        'athle-fonts',
        'https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@700;800;900&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap',
        [],
        null,
    );

    // Thème principal
    wp_enqueue_style('athle-style', get_stylesheet_uri(), ['athle-fonts'], '1.0.0');

    // JS principal
    wp_enqueue_script('athle-app', get_template_directory_uri() . '/assets/app.js', [], '1.0.0', true);

    // PWA — Service Worker + manifest
    wp_enqueue_script('athle-sw-reg', get_template_directory_uri() . '/assets/sw-register.js', [], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'athle_enqueue');

// Injecter <link rel="manifest"> et meta theme-color dans <head>
function athle_head_pwa(): void
{
    $manifest = get_template_directory_uri() . '/manifest.json';
    echo '<link rel="manifest" href="' . esc_url($manifest) . '">' . "\n";
    echo '<meta name="theme-color" content="#F0560A">' . "\n";
    echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
    echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
    echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
    echo '<meta name="apple-mobile-web-app-title" content="Athlé Sud 69">' . "\n";
}
add_action('wp_head', 'athle_head_pwa');

// Excerpts
function athle_excerpt_length(): int { return 20; }
add_filter('excerpt_length', 'athle_excerpt_length');

function athle_excerpt_more(): string { return '…'; }
add_filter('excerpt_more', 'athle_excerpt_more');

// Permettre SVG dans les médias
function athle_allow_svg(array $mimes): array
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'athle_allow_svg');

// ── Custom Post Types ────────────────────────────────────────────────────────

function athle_register_cpts(): void
{
    // Événements (calendrier)
    register_post_type('athle_event', [
        'labels'       => ['name' => 'Événements', 'singular_name' => 'Événement', 'add_new_item' => 'Ajouter un événement', 'edit_item' => 'Modifier l\'événement'],
        'public'       => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-calendar-alt',
        'supports'     => ['title', 'editor'],
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'agenda'],
    ]);

    // Résultats de compétition
    register_post_type('athle_result', [
        'labels'       => ['name' => 'Résultats', 'singular_name' => 'Résultat', 'add_new_item' => 'Ajouter un résultat', 'edit_item' => 'Modifier le résultat'],
        'public'       => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-awards',
        'supports'     => ['title', 'editor'],
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'resultats'],
    ]);

    // Records du club
    register_post_type('athle_record', [
        'labels'       => ['name' => 'Records', 'singular_name' => 'Record', 'add_new_item' => 'Ajouter un record', 'edit_item' => 'Modifier le record'],
        'public'       => false,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-star-filled',
        'supports'     => ['title'],
    ]);
}
add_action('init', 'athle_register_cpts');

// ── Meta boxes ───────────────────────────────────────────────────────────────

function athle_register_meta_boxes(): void
{
    add_meta_box('athle_event_meta', 'Détails de l\'événement', 'athle_event_meta_cb', 'athle_event', 'normal');
    add_meta_box('athle_record_meta', 'Détails du record', 'athle_record_meta_cb', 'athle_record', 'normal');
}
add_action('add_meta_boxes', 'athle_register_meta_boxes');

function athle_event_meta_cb(\WP_Post $post): void
{
    $date     = get_post_meta($post->ID, '_event_date', true);
    $location = get_post_meta($post->ID, '_event_location', true);
    $type     = get_post_meta($post->ID, '_event_type', true);
    wp_nonce_field('athle_event_meta', 'athle_event_nonce');
    echo '<p><label><strong>Date</strong><br><input type="date" name="event_date" value="' . esc_attr($date) . '" style="width:100%;margin-top:.3rem"></label></p>';
    echo '<p><label><strong>Lieu</strong><br><input type="text" name="event_location" value="' . esc_attr($location) . '" style="width:100%;margin-top:.3rem"></label></p>';
    echo '<p><label><strong>Type</strong><br><select name="event_type" style="width:100%;margin-top:.3rem">';
    foreach (['competition' => 'Compétition', 'training' => 'Entraînement', 'meeting' => 'Réunion', 'other' => 'Autre'] as $val => $lbl) {
        echo '<option value="' . esc_attr($val) . '"' . selected($type, $val, false) . '>' . esc_html($lbl) . '</option>';
    }
    echo '</select></label></p>';
}

function athle_record_meta_cb(\WP_Post $post): void
{
    $discipline = get_post_meta($post->ID, '_record_discipline', true);
    $athlete    = get_post_meta($post->ID, '_record_athlete', true);
    $mark       = get_post_meta($post->ID, '_record_mark', true);
    $category   = get_post_meta($post->ID, '_record_category', true);
    $date       = get_post_meta($post->ID, '_record_date', true);
    wp_nonce_field('athle_record_meta', 'athle_record_nonce');
    foreach ([
        ['Discipline', 'record_discipline', 'text', $discipline],
        ['Athlète',    'record_athlete',    'text', $athlete],
        ['Performance','record_mark',       'text', $mark],
        ['Catégorie',  'record_category',   'text', $category],
        ['Date',       'record_date',       'date', $date],
    ] as [$lbl, $name, $type, $val]) {
        echo '<p><label><strong>' . esc_html($lbl) . '</strong><br><input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($val) . '" style="width:100%;margin-top:.3rem"></label></p>';
    }
}

function athle_save_event_meta(int $post_id): void
{
    if (!isset($_POST['athle_event_nonce']) || !wp_verify_nonce($_POST['athle_event_nonce'], 'athle_event_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (['event_date' => '_event_date', 'event_location' => '_event_location', 'event_type' => '_event_type'] as $field => $meta) {
        if (isset($_POST[$field])) update_post_meta($post_id, $meta, sanitize_text_field($_POST[$field]));
    }
}
add_action('save_post_athle_event', 'athle_save_event_meta');

function athle_save_record_meta(int $post_id): void
{
    if (!isset($_POST['athle_record_nonce']) || !wp_verify_nonce($_POST['athle_record_nonce'], 'athle_record_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    foreach (['record_discipline' => '_record_discipline', 'record_athlete' => '_record_athlete', 'record_mark' => '_record_mark', 'record_category' => '_record_category', 'record_date' => '_record_date'] as $field => $meta) {
        if (isset($_POST[$field])) update_post_meta($post_id, $meta, sanitize_text_field($_POST[$field]));
    }
}
add_action('save_post_athle_record', 'athle_save_record_meta');

// ── Customizer — options globales ────────────────────────────────────────────

function athle_customizer(\WP_Customize_Manager $wp_customize): void
{
    // ── Hero — textes ────────────────────────────────────────────────────────
    $wp_customize->add_section('athle_hero', ['title' => 'Hero — Accueil', 'priority' => 30]);

    foreach ([
        ['hero_line1', 'Ligne titre 1', 'Courir.'],
        ['hero_line2', 'Ligne titre 2', 'Sauter.'],
        ['hero_line3', 'Ligne titre 3', 'Lancer.'],
        ['stat_members', 'Stat licenciés',   '150'],
        ['stat_years',   'Stat années',      '30'],
        ['stat_events',  'Stat disciplines', '18'],
    ] as [$id, $label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'athle_hero', 'type' => 'text']);
    }

    // Accroche — textarea multilignes
    $wp_customize->add_setting('hero_lede', [
        'default'           => 'Club d\'athlétisme basé à Feyzin et Vénissieux.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_control('hero_lede', [
        'label'   => 'Accroche (peut être sur plusieurs lignes)',
        'section' => 'athle_hero',
        'type'    => 'textarea',
    ]);

    // Outline (stroke) par ligne — checkbox
    foreach ([
        ['hero_line1_outline', 'Ligne 1 en outline', '0'],
        ['hero_line2_outline', 'Ligne 2 en outline', '1'],
        ['hero_line3_outline', 'Ligne 3 en outline', '0'],
    ] as [$id, $label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'absint']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'athle_hero', 'type' => 'checkbox']);
    }

    // ── Hero — boutons CTA ───────────────────────────────────────────────────
    $wp_customize->add_section('athle_hero_cta', ['title' => 'Hero — Boutons', 'priority' => 31]);

    $btn_styles = [
        'btn-volt'  => 'Orange (volt)',
        'btn'       => 'Sombre (ink)',
        'btn-ghost' => 'Contour',
    ];

    foreach ([1, 2] as $n) {
        [$def_text, $def_url, $def_style] = $n === 1
            ? ['Nos actualités',  '#actus', 'btn-volt']
            : ['Nous rejoindre',  '#club',  'btn-ghost'];

        $wp_customize->add_setting("hero_cta{$n}_text",  ['default' => $def_text,  'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_setting("hero_cta{$n}_url",   ['default' => $def_url,   'sanitize_callback' => 'esc_url_raw']);
        $wp_customize->add_setting("hero_cta{$n}_style", ['default' => $def_style, 'sanitize_callback' => 'sanitize_text_field']);

        $wp_customize->add_control("hero_cta{$n}_text",  ['label' => "Bouton {$n} — Texte", 'section' => 'athle_hero_cta', 'type' => 'text']);
        $wp_customize->add_control("hero_cta{$n}_url",   ['label' => "Bouton {$n} — URL",   'section' => 'athle_hero_cta', 'type' => 'url']);
        $wp_customize->add_control("hero_cta{$n}_style", ['label' => "Bouton {$n} — Style", 'section' => 'athle_hero_cta', 'type' => 'select', 'choices' => $btn_styles]);
    }

    // Bouton 2 optionnel (masquer si texte vide)
    $wp_customize->add_setting('hero_cta2_hidden', ['default' => '0', 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('hero_cta2_hidden', ['label' => 'Masquer le bouton 2', 'section' => 'athle_hero_cta', 'type' => 'checkbox']);

    // ── Contact / footer ─────────────────────────────────────────────────────
    $wp_customize->add_section('athle_contact', ['title' => 'Contact & Footer', 'priority' => 40]);
    foreach ([
        ['contact_email',    'E-mail de contact',      ''],
        ['social_instagram', 'URL Instagram',           ''],
        ['social_facebook',  'URL Facebook',            ''],
        ['footer_legal_url', 'URL mentions légales',    ''],
        ['join_url',         'URL formulaire adhésion', '#'],
        ['join_text',        'Texte section adhésion',  'Ouvert à tous les âges, du benjamin au master.'],
    ] as [$id, $label, $default]) {
        $wp_customize->add_setting($id, ['default' => $default, 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control($id, ['label' => $label, 'section' => 'athle_contact', 'type' => 'text']);
    }

    // Adresse footer — textarea multilignes
    $wp_customize->add_setting('footer_address', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ]);
    $wp_customize->add_control('footer_address', [
        'label'   => 'Adresse (peut être sur plusieurs lignes)',
        'section' => 'athle_contact',
        'type'    => 'textarea',
    ]);
}
add_action('customize_register', 'athle_customizer');

// ── Block pattern — page d'accueil ───────────────────────────────────────────

function athle_register_block_styles(): void
{
    register_block_style('core/button', [
        'name'  => 'disc-card',
        'label' => 'Carte discipline',
    ]);
}
add_action('init', 'athle_register_block_styles');

function athle_register_patterns(): void
{
    register_block_pattern_category('athle', ['label' => 'Athlé Sud 69']);

    register_block_pattern('athle/disciplines', [
        'title'       => 'Grille de disciplines',
        'description' => 'Deux colonnes : présentation à gauche, disciplines par catégorie à droite avec liens.',
        'categories'  => ['athle'],
        'content'     => <<<'DISC'
<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"3rem"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"38%"} -->
<div class="wp-block-column" style="flex-basis:38%"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Une discipline pour chacun</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"#6B7280"}}} -->
<p class="has-text-color" style="color:#6B7280">Des éveils aux compétitions, de la piste au hors-stade, du trail à la marche nordique, chacun trouve son terrain de jeu et progresse dans un groupe soudé, guidé par nos entraîneurs diplômés.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"1.5rem"}}}} -->
<div class="wp-block-buttons" style="margin-top:1.5rem"><!-- wp:button {"style":{"color":{"background":"#F0560A","text":"#ffffff"},"border":{"radius":"999px"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="#" style="border-radius:999px;background-color:#F0560A;color:#ffffff">Première séance d'essai gratuite</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"999px"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:999px">Tarifs &amp; inscriptions</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"62%"} -->
<div class="wp-block-column" style="flex-basis:62%"><!-- wp:separator {"style":{"color":{"background":"#E5E5DF"}},"className":"is-style-wide"} -->
<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-wide" style="background-color:#E5E5DF;color:#E5E5DF"/>
<!-- /wp:separator -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.7rem","letterSpacing":"0.12em","textTransform":"uppercase","fontWeight":"600"},"color":{"text":"#6B7280"}}} -->
<p class="has-text-color" style="color:#6B7280;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;font-weight:600">Jeunes</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.4rem","margin":{"bottom":"0.75rem"}}}} -->
<div class="wp-block-buttons" style="margin-bottom:0.75rem"><!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Découverte · EA → PO</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Compétition · BE → MI</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:separator {"style":{"color":{"background":"#E5E5DF"}},"className":"is-style-wide"} -->
<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-wide" style="background-color:#E5E5DF;color:#E5E5DF"/>
<!-- /wp:separator -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.7rem","letterSpacing":"0.12em","textTransform":"uppercase","fontWeight":"600"},"color":{"text":"#6B7280"}}} -->
<p class="has-text-color" style="color:#6B7280;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;font-weight:600">Piste</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.4rem","margin":{"bottom":"0.75rem"}}}} -->
<div class="wp-block-buttons" style="margin-bottom:0.75rem"><!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Sprint · Haies · CA → MA</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Sprint · Sauts · CA → MA</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Lancers · CA → MA</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Demi-fond · CA → MA</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Marche athlétique · CA → MA</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:separator {"style":{"color":{"background":"#E5E5DF"}},"className":"is-style-wide"} -->
<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-wide" style="background-color:#E5E5DF;color:#E5E5DF"/>
<!-- /wp:separator -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.7rem","letterSpacing":"0.12em","textTransform":"uppercase","fontWeight":"600"},"color":{"text":"#6B7280"}}} -->
<p class="has-text-color" style="color:#6B7280;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;font-weight:600">Running</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.4rem","margin":{"bottom":"0.75rem"}}}} -->
<div class="wp-block-buttons" style="margin-bottom:0.75rem"><!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Compétition · CA → MA</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Loisir · CA → MA</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:separator {"style":{"color":{"background":"#E5E5DF"}},"className":"is-style-wide"} -->
<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-wide" style="background-color:#E5E5DF;color:#E5E5DF"/>
<!-- /wp:separator -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.7rem","letterSpacing":"0.12em","textTransform":"uppercase","fontWeight":"600"},"color":{"text":"#6B7280"}}} -->
<p class="has-text-color" style="color:#6B7280;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;font-weight:600">Marche nordique</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline","style":{"border":{"radius":"6px"},"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem","left":"0.75rem","right":"0.75rem"}},"typography":{"fontSize":"0.75rem","fontWeight":"700","textTransform":"uppercase"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#" style="border-radius:6px;padding-top:0.5rem;padding-bottom:0.5rem;padding-left:0.75rem;padding-right:0.75rem;font-size:0.75rem;font-weight:700;text-transform:uppercase">Loisir &amp; Compétition · CA → MA</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
DISC,
    ]);

    register_block_pattern('athle/homepage', [
        'title'       => 'Page d\'accueil complète',
        'description' => 'Toutes les sections de la page d\'accueil dans l\'ordre par défaut.',
        'categories'  => ['athle'],
        'content'     => '<!-- wp:shortcode -->[athle_hero]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_news count="3"]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_calendar count="5"]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_results count="4"]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_records count="8"]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_club]<!-- /wp:shortcode -->',
    ]);
}
add_action('init', 'athle_register_patterns');

// ── Auto-remplissage de la page d'accueil à l'activation du thème ────────────

function athle_setup_front_page(): void
{
    // Ne fait rien si une page d'accueil statique est déjà configurée
    if (get_option('show_on_front') === 'page' && get_option('page_on_front')) {
        return;
    }

    // Crée la page d'accueil si elle n'existe pas
    $existing = get_page_by_path('accueil');
    $page_id  = $existing
        ? $existing->ID
        : wp_insert_post([
            'post_title'   => 'Accueil',
            'post_name'    => 'accueil',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '<!-- wp:shortcode -->[athle_hero]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_news count="3"]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_calendar count="5"]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_results count="4"]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_records count="8"]<!-- /wp:shortcode -->

<!-- wp:shortcode -->[athle_club]<!-- /wp:shortcode -->',
        ]);

    if ($page_id && !is_wp_error($page_id)) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $page_id);
    }
}
add_action('after_switch_theme', 'athle_setup_front_page');

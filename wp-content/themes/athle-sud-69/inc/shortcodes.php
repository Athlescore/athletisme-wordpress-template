<?php
declare(strict_types=1);

// Rendu d'une ligne du hero : normale ou outline selon la case cochée dans le customizer
function athle_hero_line(string $setting, string $outline_setting, string $default, string $default_outline = '0'): void {
    $text    = esc_html(get_theme_mod($setting, $default));
    $outline = (bool) get_theme_mod($outline_setting, $default_outline);
    echo $outline ? '<span class="stroke">' . $text . '</span>' : $text;
}

// ── [athle_hero] ─────────────────────────────────────────────────────────────

add_shortcode('athle_hero', function (): string {
    ob_start();
    ?>
    <section class="hero on-ink">
      <div class="hero-lanes" aria-hidden="true"></div>
      <div class="wrap">
        <h1>
          <?php athle_hero_line('hero_line1', 'hero_line1_outline', 'Courir.', '0'); ?><br>
          <?php athle_hero_line('hero_line2', 'hero_line2_outline', 'Sauter.', '1'); ?><br>
          <?php athle_hero_line('hero_line3', 'hero_line3_outline', 'Lancer.', '0'); ?>
        </h1>
        <p class="lede"><?php echo nl2br(esc_html(get_theme_mod('hero_lede', 'Club d\'athlétisme basé à Feyzin et Vénissieux.'))); ?></p>
        <div class="hero-actions">
          <?php
            $cta1_text  = get_theme_mod('hero_cta1_text',  'Nos actualités');
            $cta1_url   = get_theme_mod('hero_cta1_url',   '#actus');
            $cta1_style = get_theme_mod('hero_cta1_style', 'btn-volt');
            if ($cta1_text && $cta1_url):
          ?>
            <a href="<?php echo esc_url($cta1_url); ?>" class="btn <?php echo esc_attr($cta1_style); ?>"><?php echo esc_html($cta1_text); ?></a>
          <?php endif;
            $cta2_hidden = (bool) get_theme_mod('hero_cta2_hidden', '0');
            $cta2_text   = get_theme_mod('hero_cta2_text',  'Nous rejoindre');
            $cta2_url    = get_theme_mod('hero_cta2_url',   '#club');
            $cta2_style  = get_theme_mod('hero_cta2_style', 'btn-ghost');
            if (!$cta2_hidden && $cta2_text && $cta2_url):
          ?>
            <a href="<?php echo esc_url($cta2_url); ?>" class="btn <?php echo esc_attr($cta2_style); ?>"><?php echo esc_html($cta2_text); ?></a>
          <?php endif; ?>
        </div>
        <div class="hero-meta">
          <div class="stat"><span class="num"><?php echo esc_html(get_theme_mod('stat_members', '150')); ?>+</span><div class="lbl">Licenciés</div></div>
          <div class="stat"><span class="num"><?php echo esc_html(get_theme_mod('stat_years', '30')); ?></span><div class="lbl">Ans d'existence</div></div>
          <div class="stat"><span class="num"><?php echo esc_html(get_theme_mod('stat_events', '18')); ?></span><div class="lbl">Disciplines</div></div>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
});

// ── [athle_news count="3"] ────────────────────────────────────────────────────

add_shortcode('athle_news', function (array $atts): string {
    $a = shortcode_atts(['count' => 3, 'title' => 'Actualités', 'eyebrow' => 'Le fil du club'], $atts);
    ob_start();
    $posts   = get_posts(['numberposts' => (int) $a['count'], 'post_status' => 'publish']);
    $visuals = ['v1', 'v2', 'v3'];
    ?>
    <section id="actus" class="wrap sec-block">
      <div class="sec-head">
        <div>
          <span class="eyebrow"><?php echo esc_html($a['eyebrow']); ?></span>
          <h2><?php echo esc_html($a['title']); ?></h2>
        </div>
        <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="link">Toutes les actualités →</a>
      </div>
      <?php if (empty($posts)): ?>
        <p style="color:var(--steel);font-size:1.05rem">Aucune actualité publiée pour le moment.</p>
      <?php else: ?>
        <div class="news-grid">
          <?php foreach ($posts as $i => $post): setup_postdata($post);
            $thumb    = get_the_post_thumbnail_url($post->ID, 'large');
            $cats     = get_the_category($post->ID);
            $cat_name = $cats ? $cats[0]->name : 'Actu';
          ?>
          <article class="card reveal">
            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" style="display:contents">
              <div class="card-visual <?php echo $thumb ? 'card-visual--photo' : esc_attr($visuals[$i % 3]); ?>"
                   <?php if ($thumb): ?>style="background-image:url('<?php echo esc_url($thumb); ?>')"<?php endif; ?>>
                <span class="cat"><?php echo esc_html($cat_name); ?></span>
              </div>
              <div class="card-body">
                <span class="card-date"><?php echo get_the_date('d.m.Y', $post->ID); ?></span>
                <h3><?php echo esc_html(get_the_title($post->ID)); ?></h3>
                <p><?php echo esc_html(get_the_excerpt($post->ID)); ?></p>
                <span class="more">Lire l'article →</span>
              </div>
            </a>
          </article>
          <?php endforeach; wp_reset_postdata(); ?>
        </div>
      <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
});

// ── [athle_calendar count="5" types="competition,club"] ───────────────────

add_shortcode('athle_calendar', function (array $atts): string {
    $a = shortcode_atts([
        'count'   => 5,
        'title'   => 'Prochains événements',
        'eyebrow' => 'Agenda',
        'types'   => '',   // vide = tous les types ; ex: "competition,club"
    ], $atts);

    $mois        = ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
    $type_labels = [
        'competition' => 'Compétition',
        'club'        => 'Événement club',
        'training'    => 'Entraînement',
        'meeting'     => 'Réunion',
        'other'       => 'Autre',
    ];

    $meta_query = [
        'relation' => 'AND',
        ['key' => '_event_date', 'value' => date('Y-m-d'), 'compare' => '>=', 'type' => 'DATE'],
    ];

    if (!empty($a['types'])) {
        $allowed      = array_map('trim', explode(',', $a['types']));
        $meta_query[] = ['key' => '_event_type', 'value' => $allowed, 'compare' => 'IN'];
    }

    $events = get_posts([
        'post_type'   => 'athle_event',
        'numberposts' => (int) $a['count'],
        'post_status' => 'publish',
        'meta_key'    => '_event_date',
        'orderby'     => 'meta_value',
        'order'       => 'ASC',
        'meta_query'  => $meta_query,
    ]);

    ob_start();
    ?>
    <section id="calendrier" class="wrap sec-block">
      <div class="sec-head">
        <div>
          <span class="eyebrow"><?php echo esc_html($a['eyebrow']); ?></span>
          <h2><?php echo esc_html($a['title']); ?></h2>
        </div>
        <a href="<?php echo esc_url(get_post_type_archive_link('athle_event')); ?>" class="link">Agenda complet →</a>
      </div>
      <?php if (empty($events)): ?>
        <p style="color:var(--steel);font-size:1.05rem">Aucun événement à venir pour le moment.</p>
      <?php else: ?>
        <div class="calendar-grid">
          <?php foreach ($events as $ev):
            $date     = get_post_meta($ev->ID, '_event_date', true);
            $location = get_post_meta($ev->ID, '_event_location', true);
            $type     = get_post_meta($ev->ID, '_event_type', true) ?: 'other';
            $ts       = $date ? strtotime($date) : null;
            $label    = $type_labels[$type] ?? 'Autre';
          ?>
          <a href="<?php echo esc_url(get_permalink($ev->ID)); ?>" class="cal-card cal-type--<?php echo esc_attr($type); ?> reveal">
            <div class="cal-banner"><?php echo esc_html($label); ?></div>
            <div class="cal-body">
              <div class="cal-date">
                <span class="cal-day"><?php echo $ts ? date('d', $ts) : '—'; ?></span>
                <span class="cal-month"><?php echo $ts ? $mois[(int)date('n', $ts) - 1] : ''; ?></span>
              </div>
              <h3 class="cal-title"><?php echo esc_html($ev->post_title); ?></h3>
              <?php if ($location): ?>
                <div class="cal-where"><?php echo esc_html($location); ?></div>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; wp_reset_postdata(); ?>
        </div>
      <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
});

// ── [athle_results count="4"] ─────────────────────────────────────────────────

add_shortcode('athle_results', function (array $atts): string {
    $a = shortcode_atts(['count' => 4, 'title' => 'Derniers résultats', 'eyebrow' => 'En compétition'], $atts);

    $mois = ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
    $cats = [
        'poussin' => 'Poussin', 'pupille' => 'Pupille',
        'benjam'  => 'Benjamin','minime'  => 'Minime',
        'cadet'   => 'Cadet',  'junior'  => 'Junior',
        'espoir'  => 'Espoir', 'senior'  => 'Senior',
        'master'  => 'Master', 'mixte'   => 'Toutes catégories',
    ];

    $results = get_posts([
        'post_type'   => 'athle_result',
        'numberposts' => (int) $a['count'],
        'post_status' => 'publish',
        'meta_key'    => '_result_date',
        'orderby'     => 'meta_value',
        'order'       => 'DESC',
    ]);

    ob_start();
    ?>
    <section id="resultats" class="wrap sec-block">
      <div class="sec-head">
        <div>
          <span class="eyebrow"><?php echo esc_html($a['eyebrow']); ?></span>
          <h2><?php echo esc_html($a['title']); ?></h2>
        </div>
        <a href="<?php echo esc_url(get_post_type_archive_link('athle_result')); ?>" class="link">Tous les résultats →</a>
      </div>
      <?php if (empty($results)): ?>
        <p style="color:var(--steel);font-size:1.05rem">Aucun résultat disponible pour le moment.</p>
      <?php else: ?>
        <div class="calendar-grid">
          <?php foreach ($results as $res):
            $date     = get_post_meta($res->ID, '_result_date', true);
            $location = get_post_meta($res->ID, '_result_location', true);
            $cat      = get_post_meta($res->ID, '_result_category', true);
            $ts       = $date ? strtotime($date) : null;
            $cat_lbl  = $cats[$cat] ?? 'Résultats';
          ?>
          <a href="<?php echo esc_url(get_permalink($res->ID)); ?>" class="cal-card cal-type--result reveal">
            <div class="cal-banner"><?php echo esc_html($cat_lbl); ?></div>
            <div class="cal-body">
              <div class="cal-date">
                <span class="cal-day"><?php echo $ts ? date('d', $ts) : '—'; ?></span>
                <span class="cal-month"><?php echo $ts ? $mois[(int)date('n', $ts) - 1] : ''; ?></span>
              </div>
              <h3 class="cal-title"><?php echo esc_html($res->post_title); ?></h3>
              <?php if ($location): ?>
                <div class="cal-where"><?php echo esc_html($location); ?></div>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
});

// ── [athle_records count="8"] ─────────────────────────────────────────────────

add_shortcode('athle_records', function (array $atts): string {
    $a = shortcode_atts(['count' => 8, 'title' => 'Records récents', 'eyebrow' => 'Tableau d\'affichage'], $atts);
    ob_start();
    $records = get_posts([
        'post_type'   => 'athle_record',
        'numberposts' => (int) $a['count'],
        'post_status' => 'publish',
        'orderby'     => 'date',
        'order'       => 'DESC',
    ]);
    ?>
    <section id="records" class="records on-ink">
      <div class="wrap">
        <div class="sec-head">
          <div>
            <span class="eyebrow"><?php echo esc_html($a['eyebrow']); ?></span>
            <h2><?php echo esc_html($a['title']); ?></h2>
          </div>
        </div>
        <?php if (empty($records)): ?>
          <p style="color:var(--steel);opacity:.7;font-size:1rem">Aucun record enregistré pour le moment.</p>
        <?php else: ?>
          <div class="board">
            <?php foreach ($records as $rec):
              $discipline = get_post_meta($rec->ID, '_record_discipline', true);
              $athlete    = get_post_meta($rec->ID, '_record_athlete', true);
              $mark       = get_post_meta($rec->ID, '_record_mark', true);
              $category   = get_post_meta($rec->ID, '_record_category', true);
              $date       = get_post_meta($rec->ID, '_record_date', true);
            ?>
            <div class="row reveal">
              <div class="disc">
                <span><?php echo esc_html($discipline ?: $rec->post_title); ?></span>
                <?php if ($date): ?><span class="rec-date"><?php echo esc_html(date('d/m/Y', strtotime($date))); ?></span><?php endif; ?>
              </div>
              <div class="who"><?php echo esc_html($athlete); ?></div>
              <div class="mark"><?php echo esc_html($mark); ?></div>
              <div class="who-cat"><?php echo esc_html($category); ?></div>
            </div>
            <?php endforeach; wp_reset_postdata(); ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
    <?php
    return ob_get_clean();
});

// ── [athle_club page="club"] ──────────────────────────────────────────────────
//
// Affiche le contenu Gutenberg d'une page WordPress à l'intérieur de la section.
// Utilisation : [athle_club] → cherche la page au slug "club"
//               [athle_club page="mon-slug"] → cherche n'importe quel autre slug

add_shortcode('athle_club', function (array $atts): string {
    $a = shortcode_atts([
        'page'   => 'club',
        'id'     => 'club',
        'title'  => '',   // h2 optionnel sous l'eyebrow
    ], $atts);

    $page = get_page_by_path($a['page']);

    ob_start();

    if (!$page): ?>
      <section id="<?php echo esc_attr($a['id']); ?>" class="wrap sec-block">
        <div style="padding:2rem;background:var(--chalk);border:2px dashed var(--line);border-radius:12px;text-align:center;color:var(--steel)">
          <p style="font-weight:600;margin-bottom:.5rem">Section non configurée</p>
          <p style="font-size:.95rem">
            Créez une page WordPress avec le slug <strong><?php echo esc_html($a['page']); ?></strong>,
            composez son contenu dans l'éditeur, puis revenez ici.
          </p>
          <a href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>"
             style="display:inline-block;margin-top:1rem;color:var(--track);font-weight:600">
            + Créer la page →
          </a>
        </div>
      </section>
    <?php else:
        // Le titre de la page devient l'eyebrow par défaut
        $eyebrow = $page->post_title;
    ?>
      <section id="<?php echo esc_attr($a['id']); ?>" class="wrap sec-block">
        <div class="sec-head" style="margin-bottom:2rem">
          <div>
            <span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
            <?php if ($a['title']): ?>
              <h2><?php echo esc_html($a['title']); ?></h2>
            <?php endif; ?>
          </div>
        </div>
        <?php echo apply_filters('the_content', $page->post_content); ?>
      </section>
    <?php endif;

    return ob_get_clean();
});

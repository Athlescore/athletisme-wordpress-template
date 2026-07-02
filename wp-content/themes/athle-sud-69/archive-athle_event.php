<?php
declare(strict_types=1);

get_header();

$mois        = ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
$type_labels = [
    'competition' => 'Compétition',
    'club'        => 'Événement club',
    'training'    => 'Entraînement',
    'meeting'     => 'Réunion',
    'other'       => 'Autre',
];

$base_args = [
    'post_type'      => 'athle_event',
    'posts_per_page' => -1,
    'meta_key'       => '_event_date',
    'orderby'        => 'meta_value',
    'post_status'    => 'publish',
];

$today    = date('Y-m-d');
$upcoming = new WP_Query(array_merge($base_args, [
    'order'      => 'ASC',
    'meta_query' => [['key' => '_event_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE']],
]));
$past = new WP_Query(array_merge($base_args, [
    'order'          => 'DESC',
    'posts_per_page' => 24,
    'meta_query'     => [['key' => '_event_date', 'value' => $today, 'compare' => '<', 'type' => 'DATE']],
]));

function athle_render_event_cards(WP_Query $query, array $mois, array $type_labels): void {
    if (!$query->have_posts()):
        echo '<p style="color:var(--steel);font-size:1.05rem;padding:.5rem 0">Aucun événement.</p>';
        return;
    endif;
    echo '<div class="calendar-grid">';
    while ($query->have_posts()): $query->the_post();
        $date     = get_post_meta(get_the_ID(), '_event_date', true);
        $location = get_post_meta(get_the_ID(), '_event_location', true);
        $type     = get_post_meta(get_the_ID(), '_event_type', true) ?: 'other';
        $ts       = $date ? strtotime($date) : null;
        $label    = $type_labels[$type] ?? 'Autre';
        printf(
            '<a href="%s" class="cal-card cal-type--%s">
               <div class="cal-banner">%s</div>
               <div class="cal-body">
                 <div class="cal-date">
                   <span class="cal-day">%s</span>
                   <span class="cal-month">%s</span>
                 </div>
                 <h2 class="cal-title">%s</h2>
                 %s
               </div>
             </a>',
            esc_url(get_permalink()),
            esc_attr($type),
            esc_html($label),
            $ts ? date('d', $ts) : '—',
            $ts ? $mois[(int)date('n', $ts) - 1] : '',
            esc_html(get_the_title()),
            $location ? '<div class="cal-where">' . esc_html($location) . '</div>' : ''
        );
    endwhile;
    echo '</div>';
    wp_reset_postdata();
}
?>

<div class="wrap">
  <div class="page-hero">
    <span class="eyebrow">Calendrier</span>
    <h1 class="hero-title">Agenda</h1>
  </div>

  <div class="sec-block sec-block--sm">
    <div class="sec-head" style="margin-bottom:1.5rem">
      <div>
        <span class="eyebrow">À venir</span>
      </div>
    </div>
    <?php athle_render_event_cards($upcoming, $mois, $type_labels); ?>
  </div>

  <?php if ($past->have_posts()): ?>
  <div class="sec-block sec-block--sm">
    <div class="sec-head" style="margin-bottom:1.5rem">
      <div>
        <span class="eyebrow">Événements passés</span>
      </div>
    </div>
    <?php athle_render_event_cards($past, $mois, $type_labels); ?>
  </div>
  <?php endif; ?>

</div>

<?php get_footer();

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
?>
<div class="wrap">

  <div class="page-hero">
    <span class="eyebrow">Calendrier</span>
    <h1 class="hero-title">Agenda</h1>
    <p class="hero-sub">Compétitions et événements à venir</p>
  </div>

  <?php if (have_posts()): ?>
    <div class="calendar-grid sec-block">
      <?php while (have_posts()): the_post();
        $date     = get_post_meta(get_the_ID(), '_event_date', true);
        $location = get_post_meta(get_the_ID(), '_event_location', true);
        $type     = get_post_meta(get_the_ID(), '_event_type', true) ?: 'other';
        $ts       = $date ? strtotime($date) : null;
        $label    = $type_labels[$type] ?? 'Autre';
      ?>
      <a href="<?php the_permalink(); ?>" class="cal-card cal-type--<?php echo esc_attr($type); ?>">
        <div class="cal-banner"><?php echo esc_html($label); ?></div>
        <div class="cal-body">
          <div class="cal-date">
            <span class="cal-day"><?php echo $ts ? date('d', $ts) : '—'; ?></span>
            <span class="cal-month"><?php echo $ts ? $mois[(int)date('n', $ts) - 1] : ''; ?></span>
          </div>
          <h2 class="cal-title"><?php the_title(); ?></h2>
          <?php if ($location): ?>
            <div class="cal-where"><?php echo esc_html($location); ?></div>
          <?php endif; ?>
        </div>
      </a>
      <?php endwhile; ?>
    </div>

    <div class="pagination sec-block" style="text-align:center">
      <?php the_posts_pagination(['mid_size' => 2]); ?>
    </div>

  <?php else: ?>
    <p class="sec-block" style="color:var(--steel);font-size:1.05rem">
      Aucun événement à venir pour le moment.
    </p>
  <?php endif; ?>

</div>

<?php get_footer();

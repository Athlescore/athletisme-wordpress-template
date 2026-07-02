<?php
declare(strict_types=1);
get_header();
the_post();

$id       = get_the_ID();
$date     = get_post_meta($id, '_result_date', true);
$location = get_post_meta($id, '_result_location', true);
$category = get_post_meta($id, '_result_category', true);
$raw_rows = get_post_meta($id, '_result_rows', true);
$rows     = is_array($raw_rows) ? $raw_rows : ($raw_rows ? json_decode($raw_rows, true) : []);

$cats_lbl = [
    'poussin' => 'Poussin (U10)', 'pupille' => 'Pupille (U12)',
    'benjam'  => 'Benjamin (U14)','minime'  => 'Minime (U16)',
    'cadet'   => 'Cadet (U18)',  'junior'  => 'Junior (U20)',
    'espoir'  => 'Espoir (U23)', 'senior'  => 'Senior',
    'master'  => 'Master',       'mixte'   => 'Toutes catégories',
];
?>
<div class="wrap">
  <div class="article-head">
    <span class="eyebrow">Résultats<?php echo $category ? ' — ' . esc_html($cats_lbl[$category] ?? $category) : ''; ?></span>
    <h1><?php the_title(); ?></h1>
    <div class="article-meta">
      <?php if ($date): ?>
        <span><?php echo esc_html(date('d M Y', strtotime($date))); ?></span>
      <?php endif; ?>
      <?php if ($location): ?>
        <span class="sep">·</span>
        <span><?php echo esc_html($location); ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($rows)): ?>
<div class="wrap">
  <div class="result-table-wrap">
    <table class="result-table">
      <thead>
        <tr>
          <th>Athlète</th>
          <th>Discipline</th>
          <th>Performance</th>
          <th>Place</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
        <tr>
          <td><strong><?php echo esc_html($row['athlete'] ?? ''); ?></strong></td>
          <td><?php echo esc_html($row['disc'] ?? ''); ?></td>
          <td><strong><?php echo esc_html($row['perf'] ?? ''); ?></strong></td>
          <td><?php echo esc_html($row['place'] ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (trim(get_the_content())): ?>
<div class="wrap">
  <div class="article-body" style="margin-top:2rem">
    <?php the_content(); ?>
  </div>
</div>
<?php endif; ?>

<?php get_footer(); ?>

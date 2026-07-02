<?php
declare(strict_types=1);
get_header();
the_post();

$id       = get_the_ID();
$date     = get_post_meta($id, '_result_date', true);
$location = get_post_meta($id, '_result_location', true);
$category = get_post_meta($id, '_result_category', true);

$cats_lbl = [
    'poussin' => 'Poussin (U10)', 'pupille' => 'Pupille (U12)',
    'benjam'  => 'Benjamin (U14)','minime'  => 'Minime (U16)',
    'cadet'   => 'Cadet (U18)',  'junior'  => 'Junior (U20)',
    'espoir'  => 'Espoir (U23)', 'senior'  => 'Senior',
    'master'  => 'Master',       'mixte'   => 'Toutes catégories',
    '' => '',
];

$stored   = get_post_meta($id, '_result_rows', true);
$raw      = is_array($stored) ? $stored : ($stored ? json_decode($stored, true) : []);

// Normalise ancien format plat vers nouveau format groupé
$athletes = [];
foreach ((array) $raw as $item) {
    if (isset($item['perfs'])) {
        $athletes[] = $item;
    } else {
        $name  = $item['athlete'] ?? '';
        $entry = ['disc' => $item['disc'] ?? '', 'perf' => $item['perf'] ?? '', 'place' => $item['place'] ?? ''];
        $found = false;
        foreach ($athletes as &$a) {
            if ($a['athlete'] === $name) { $a['perfs'][] = $entry; $found = true; break; }
        }
        unset($a);
        if (!$found) $athletes[] = ['athlete' => $name, 'perfs' => [$entry]];
    }
}
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

<?php if (!empty($athletes)): ?>
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
        <?php foreach ($athletes as $item):
            $perfs = array_values($item['perfs'] ?? []);
            $count = count($perfs);
            foreach ($perfs as $i => $p): ?>
        <tr>
          <?php if ($i === 0): ?>
          <td<?php echo $count > 1 ? ' rowspan="' . $count . '" style="vertical-align:top"' : ''; ?>>
            <strong><?php echo esc_html($item['athlete']); ?></strong>
            <?php $cat = $cats_lbl[$item['category'] ?? ''] ?? ''; if ($cat): ?>
            <br><span class="athlete-cat"><?php echo esc_html($cat); ?></span>
            <?php endif; ?>
          </td>
          <?php endif; ?>
          <td><?php echo esc_html($p['disc'] ?? ''); ?></td>
          <td><strong><?php echo esc_html($p['perf'] ?? ''); ?></strong></td>
          <td><?php echo esc_html($p['place'] ?? ''); ?></td>
        </tr>
        <?php endforeach; endforeach; ?>
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

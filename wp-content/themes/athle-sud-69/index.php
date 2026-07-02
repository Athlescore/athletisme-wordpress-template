<?php get_header(); ?>

<section class="sec-block">
  <div class="wrap">
    <div class="sec-head">
      <div>
        <span class="eyebrow">Actualités</span>
        <h2>Toutes les<br>nouvelles</h2>
      </div>
    </div>
    <div class="news-grid">
      <?php if (have_posts()): while (have_posts()): the_post();
        $thumb = get_the_post_thumbnail_url(null, 'large');
        $cats  = get_the_category();
        $cat   = $cats ? $cats[0]->name : 'Actu';
      ?>
      <a href="<?php the_permalink(); ?>" class="card reveal">
        <div class="card-visual <?php echo $thumb ? 'card-visual--photo' : 'v1'; ?>"
             <?php if ($thumb): ?>style="background-image:url('<?php echo esc_url($thumb); ?>')"<?php endif; ?>>
          <span class="cat"><?php echo esc_html($cat); ?></span>
        </div>
        <div class="card-body">
          <span class="card-date"><?php echo get_the_date('d M Y'); ?></span>
          <h3><?php the_title(); ?></h3>
          <p><?php the_excerpt(); ?></p>
          <span class="more">Lire →</span>
        </div>
      </a>
      <?php endwhile; else: ?>
        <p>Aucune actualité pour le moment.</p>
      <?php endif; ?>
    </div>

    <div style="margin-top:2.5rem;display:flex;justify-content:center">
      <?php the_posts_pagination(['mid_size' => 2]); ?>
    </div>
  </div>
</section>

<?php get_footer(); ?>

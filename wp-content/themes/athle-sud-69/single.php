<?php get_header(); the_post(); ?>

<div class="wrap">
  <div class="article-head">
    <?php $cats = get_the_category(); if ($cats): ?>
      <span class="eyebrow"><?php echo esc_html($cats[0]->name); ?></span>
    <?php endif; ?>
    <h1><?php the_title(); ?></h1>
    <div class="article-meta">
      <span><?php echo get_the_date('d M Y'); ?></span>
      <span class="sep">·</span>
      <span><?php the_author(); ?></span>
    </div>
  </div>
</div>

<?php if (has_post_thumbnail()): ?>
  <div class="wrap" style="padding-block: 0 1.5rem">
    <?php the_post_thumbnail('full', ['class' => 'article-cover-img', 'loading' => 'eager']); ?>
  </div>
<?php endif; ?>

<div class="wrap">
  <div class="article-body">
    <?php the_content(); ?>
  </div>
</div>

<?php get_footer(); ?>

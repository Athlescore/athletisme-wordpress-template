<?php get_header(); the_post(); ?>

<div class="wrap">
  <div class="page-hero">
    <h1 class="hero-title"><?php the_title(); ?></h1>
  </div>
  <div class="article-body">
    <?php the_content(); ?>
  </div>
</div>

<?php get_footer(); ?>

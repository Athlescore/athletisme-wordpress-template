<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" id="site-header">
  <div class="wrap nav">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="brand" aria-label="<?php bloginfo('name'); ?> — Accueil">
      <?php
        $logo_id = get_theme_mod('custom_logo');
        if ($logo_id):
          $logo = wp_get_attachment_image_src($logo_id, 'full');
      ?>
        <span class="brand-mark"><img src="<?php echo esc_url($logo[0]); ?>" alt="" width="46" height="46"></span>
      <?php else: ?>
        <span class="brand-mark" aria-hidden="true"></span>
      <?php endif; ?>
      <span class="brand-text">
        <span class="brand-name"><?php bloginfo('name'); ?></span>
        <span class="brand-sub">Feyzin · Vénissieux</span>
      </span>
    </a>

    <nav class="nav-links" id="main-menu" aria-label="Navigation principale">
      <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'items_wrap'     => '%3$s',
          'fallback_cb'    => '__return_false',
        ]);
      ?>
      <?php if (is_user_logged_in()): ?>
        <a href="<?php echo esc_url(admin_url()); ?>" class="btn">Administration</a>
      <?php else: ?>
        <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn">Connexion</a>
      <?php endif; ?>
      <a href="#adhesion" class="btn btn-volt">Adhérer</a>
      <button class="btn pwa-install-btn" id="pwa-install-btn" hidden aria-label="Installer l'application">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="vertical-align:-.15em;margin-right:.3em">
          <path d="M10 2a1 1 0 0 1 1 1v8.586l2.293-2.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 1 1 1.414-1.414L9 11.586V3a1 1 0 0 1 1-1ZM3 15a1 1 0 0 1 1-1h12a1 1 0 0 1 0 2H4a1 1 0 0 1-1-1Z"/>
        </svg>
        Installer
      </button>
    </nav>

    <button class="nav-toggle" id="nav-toggle"
            aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="main-menu">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </button>
  </div>
</header>

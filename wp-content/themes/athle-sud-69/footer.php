<footer class="site-footer">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <div class="brand">
          <?php
            $logo_id  = get_theme_mod('custom_logo');
            $icon_url = get_site_icon_url(96);
            if ($logo_id): $logo = wp_get_attachment_image_src($logo_id, 'full'); ?>
            <span class="brand-mark"><img src="<?php echo esc_url($logo[0]); ?>" alt="" width="46" height="46"></span>
          <?php elseif ($icon_url): ?>
            <span class="brand-mark"><img src="<?php echo esc_url($icon_url); ?>" alt="" width="46" height="46"></span>
          <?php else: ?>
            <span class="brand-mark" aria-hidden="true"></span>
          <?php endif; ?>
          <span class="brand-text">
            <span class="brand-name"><?php bloginfo('name'); ?></span>
            <span class="brand-sub">Feyzin · Vénissieux</span>
          </span>
        </div>
        <p class="muted" style="margin-top:.5rem"><?php echo nl2br(esc_html(get_theme_mod('footer_address', ''))); ?></p>
      </div>

      <div>
        <h4>Le club</h4>
        <?php
          wp_nav_menu([
            'theme_location' => 'footer',
            'container'      => false,
            'items_wrap'     => '<ul>%3$s</ul>',
            'fallback_cb'    => '__return_false',
          ]);
        ?>
      </div>

      <div>
        <h4>Contact</h4>
        <ul>
          <?php $email = get_theme_mod('contact_email', ''); if ($email): ?>
            <li><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></li>
          <?php endif; ?>
          <?php $ig = get_theme_mod('social_instagram', ''); if ($ig): ?>
            <li><a href="<?php echo esc_url($ig); ?>" target="_blank" rel="noopener">Instagram</a></li>
          <?php endif; ?>
          <?php $fb = get_theme_mod('social_facebook', ''); if ($fb): ?>
            <li><a href="<?php echo esc_url($fb); ?>" target="_blank" rel="noopener">Facebook</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="foot-bottom">
      <span>© <?php echo date('Y'); ?> <?php bloginfo('name'); ?> — Association loi 1901</span>
      <span>
        <?php $legal = get_theme_mod('footer_legal_url', ''); if ($legal): ?>
          <a href="<?php echo esc_url($legal); ?>">Mentions légales</a> ·
        <?php endif; ?>
        <a href="<?php echo esc_url(get_privacy_policy_url()); ?>">Confidentialité</a>
      </span>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

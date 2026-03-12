<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<div class="collapser ignorehov section-bmi shadow section-bmi" group="quick-migration">
  <div class="header f20 pointer ignorehov transition heading-sq"><span class="bold"><?php esc_html_e('Super-quick migration', 'backup-backup') ?></span></div>
  <div class="content">
    <img src="<?php echo esc_url($this->get_asset('images', 'clocks-min.png')); ?>" class="bg-quick-migration" alt="bg-watches">
    <div class="content-above">

      <div class="f16 medium text-heading">
        <?php esc_html_e('Paste the URL you received after creating a backup:', 'backup-backup') ?>
      </div>

      <input type="text" autocomplete="off" id="bm-d-url" placeholder="<?php esc_attr_e('E.g.', 'backup-backup') ?> https://your-site.com/?backup=bmi_backup.zip&secret" class="light">

      <div class="center">
        <button type="button" id="quick-download-migration" class="f16 semibold with-icon centred">
          <div class="for-img">
            <img src="<?php echo esc_url($this->get_asset('images', 'restore-icon-min.png')); ?>" alt="restore-img">
            <span><?php esc_html_e('Restore now!', 'backup-backup') ?></span>
          </div>
        </button>
      </div>
    </div>
  </div>
</div>

<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<div class="bmi-modal" id="staging-prenotice-modal">

  <div class="bmi-modal-wrapper no-hpad" style="max-width: 660px; max-width: min(660px, 80vw);">
    <a href="#" class="bmi-modal-close">×</a>
    <div class="bmi-modal-content">

      <div class="mm30 f26 center bold black"><?php esc_html_e('Are you sure?', 'backup-backup') ?></div>

      <div class="mm30 f19 mbl center lh30 mtl" id="stg-prenotice-mode-local">
        <?php esc_html_e("Your staging site will be created on your server.", 'backup-backup'); ?><br>
        <?php esc_html_e("This will duplicate all files and related tables of this site.", 'backup-backup'); ?>
        <br /><br />
        <div class="bmi-staging-bolder-wrap">
          <b><?php esc_html_e("URL:", 'backup-backup'); ?> <a href="#!" class="secondary" id="bmi-staging-local-current-url">https://somesite.com/xyz</a></b>
        </div>
      </div>

      <div class="f19 mbl center lh30 mtl" id="stg-prenotice-mode-tastewp" style="display: none;">
        <?php printf(wp_kses_post( __( 'Your staging site will be created on an external server (%s).', 'backup-backup' )), '<a href="https://tastewp.com" target="_blank" >TasteWP</a>'); ?>
        <br>
        <?php printf(wp_kses_post(__("Your files are protected and %sstay confidential%s.", 'backup-backup')), '<a href="https://tastewp.com/?open=policy" target="_blank"', '</a>'); ?>
        <br /><br />
        <div class="bmi-staging-bolder-wrap">
          <b><?php esc_html_e("Backup to be used:", 'backup-backup'); ?> <a href="#!" class="secondary" id="bmi-staging-local-current-backup">BMI_Backup.zip</a></b>
        </div>
      </div>

      <div class="mm60 center">
        <a href="#" class="btn" id="start-entire-staging" mode="local">
          <div class="text cf">
            <div class="left inline img-now-staging-wrapper">
              <svg class="img-now-staging">
                <use xlink:href="<?php echo esc_url( $this->get_asset('images', 'local-server-2.svg#img') ); ?>"></use>
              </svg>
            </div>
            <div class="left inline">
              <div class="f18 regular"><?php esc_html_e('That\'s all fine / I don\'t care…', 'backup-backup') ?></div>
              <div class="f25 bold"><?php esc_html_e('Create the staging site!', 'backup-backup') ?></div>
            </div>
          </div>
        </a>
        <div class="text-grey text-muted mtl f18 bmi-modal-closer inline" data-close="staging-prenotice-modal">
          <?php esc_html_e('Don\'t do that & close this pop-up', 'backup-backup') ?>
        </div>
      </div>

    </div>
  </div>

</div>

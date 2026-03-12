<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<div class="bmi-modal" id="delete-confirm-modal">

  <div class="bmi-modal-wrapper" style="max-width: 380px; max-width: min(380px, 80vw);">
    <a href="#" class="bmi-modal-close">×</a>
    <div class="bmi-modal-content">

      <div class="mbl center">
        <img class="center block inline" src="<?php echo esc_url( $this->get_asset('images', 'trash.png') ); ?>" alt="trash">
      </div>

      <div class="center f24 bold mbl lh30 text1">
        <?php esc_html_e("Are you sure you want to", 'backup-backup'); ?><br>
        <?php esc_html_e("delete this backup?", 'backup-backup'); ?>
      </div>

      <div class="center f24 bold mbl lh30 text2" style="display: none;">
        <?php esc_html_e("Are you sure you want to", 'backup-backup'); ?><br>
        <?php esc_html_e("delete", 'backup-backup'); ?>
        (<span class="backup-multiple-del-count">1</span>)
        <span class="del-only-one"><?php esc_html_e("backup?", 'backup-backup'); ?></span>
        <span class="del-more-than-one" style="display: none;"><?php esc_html_e("backups?", 'backup-backup'); ?></span>
      </div>

      <div class="center f24 bold mbl lh30 text3" style="display: none;">
        <?php esc_html_e("Are you sure you want to", 'backup-backup'); ?><br>
        <?php esc_html_e("delete", 'backup-backup'); ?>
        (<span class="backup-multiple-del-count">1</span>)
        <span class="del-only-one"><?php esc_html_e("backup from your Cloud Storage(s)?", 'backup-backup'); ?></span>
        <span class="del-more-than-one"><?php esc_html_e("backups from your Cloud Storage(s)?", 'backup-backup'); ?></span>
      </div>

      <div class="center f24 bold mbl lh30 text4" style="display: none;">
        <?php esc_html_e("Are you sure you want to", 'backup-backup'); ?><br>
        <?php esc_html_e("delete", 'backup-backup'); ?>
        <?php esc_html_e("this backup from your Cloud Storage(s)?", 'backup-backup'); ?>
      </div>

      <div class="bmi-cloud-notice-text text3 text4" style="display: none;">
        <span class="del-only-one"><?php esc_html_e("(That backup is not located on your local storage)", 'backup-backup'); ?></span>
        <span class="del-more-than-one"><?php esc_html_e("(These backups are not located on your local storage)", 'backup-backup'); ?></span>
      </div>

      <div class="bmi-cloud-removal">
        <label for="remove-cloud-backup-as-well">
          <input type="checkbox" id="remove-cloud-backup-as-well">
          <span><?php esc_html_e("Delete it from Cloud Storage(s) as well?", 'backup-backup'); ?></span>
        </label>
      </div>

      <div class="center f19 mbl">
        <a href="#" id="sure_delete" class="btn bold red inline mm60">
          <?php esc_html_e("Yes, kill it!", 'backup-backup'); ?>
        </a>
      </div>

      <div class="center f19">
        <a href="#" class="bold bmi-modal-closer hoverable text-muted nodec">
          <?php esc_html_e("No, cancel", 'backup-backup'); ?>
        </a>
      </div>

    </div>
  </div>

</div>

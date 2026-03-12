<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<div class="bmi-modal" id="upload-success-modal">

  <div class="bmi-modal-wrapper" style="max-width: 645px; max-width: min(645px, 80vw);">
    <div class="bmi-modal-content">

      <div class="center">
        <div class="mbl cf center inline">
          <div class="left center">
            <img class="inline" src="<?php echo esc_url( $this->get_asset('images', 'success.png') ); ?>" alt="success">
          </div>
          <div class="left f24 bold lh50"><?php esc_html_e("Upload successful!", 'backup-backup'); ?></div>
        </div>
      </div>

      <div class="f19 mbl center lh30">
        <?php esc_html_e("We also triggered a rescan of your local folder automatically,", 'backup-backup'); ?><br>
        <?php esc_html_e("so that the file will show up in your list of backups.", 'backup-backup'); ?>
      </div>

      <div class="center">
        <a href="#" class="btn bmi-modal-closer inline mm60 center block bold nodec">
          <?php esc_html_e("Ok, close", 'backup-backup'); ?>
        </a>
      </div>

    </div>
  </div>

</div>

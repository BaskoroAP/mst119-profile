<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<div class="bmi-modal" id="upload-exist-file-modal">

  <div class="bmi-modal-wrapper" style="max-width: 564px; max-width: min(564px, 80vw);">
    <div class="bmi-modal-content">

      <div class="center">
        <div class="mbl cf center inline">
          <div class="left center">
            <img class="inline" src="<?php echo esc_url( $this->get_asset('images', 'warning-red.png') ); ?>" alt="error">
          </div>
          <div class="left f24 bold lh50 mms"><?php esc_html_e("File already exist", 'backup-backup'); ?></div>
        </div>
      </div>

      <div class="center f19 regular mbl lh30">
        <?php esc_html_e("The file you tried to upload already match,", 'backup-backup'); ?><br>
        <?php esc_html_e("name and size of file which already,", 'backup-backup'); ?><br>
        <?php esc_html_e("exist in your backup directory.", 'backup-backup'); ?><br><br>
        <?php esc_html_e("If you wish to have duplicate change name of the upload.", 'backup-backup'); ?><br>
      </div>

      <div class="center">
        <a href="#" class="btn bmi-modal-closer inline mm60 center block bold nodec">
          <?php esc_html_e("Ok, close", 'backup-backup'); ?>
        </a>
      </div>

    </div>
  </div>

</div>

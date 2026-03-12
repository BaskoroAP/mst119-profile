<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

  $stagingErrorInstruction = __('Please click on the button below to enable us to investigate.', 'backup-backup');

  $messageStagingError1 = __("That's unusual! But no worries – we're happy to look into it,%s3and fix it for you (%s1for free!%s2)", 'backup-backup');
  $messageStagingError1 = str_replace('%s1', '<b>', $messageStagingError1);
  $messageStagingError1 = str_replace('%s2', '</b>', $messageStagingError1);
  $messageStagingError1 = str_replace('%s3', '<br>', $messageStagingError1);

  $bmiTroubleshootingLogShareInfo = __("You'll share: Website URL, %s1staging logs%s2, our plugin logs & configuration, basic data about your site.", 'backup-backup');
  $bmiTroubleshootingLogShareInfo2 = __("No confidential data such as email gets shared.", 'backup-backup');

  $bmiTroubleshootingLogShareInfo = str_replace('%s1', '<a href="#" class="download-staging-log-url hoverable secondary" download="staging_logs.txt">', $bmiTroubleshootingLogShareInfo);
  $bmiTroubleshootingLogShareInfo = str_replace('%s2', '</a>', $bmiTroubleshootingLogShareInfo);

?>

<div class="bmi-modal" id="staging-error-modal">

  <div class="bmi-modal-wrapper no-hpad" style="max-width: 900px; max-width: min(900px, 80vw)">
    <a href="#" class="bmi-modal-close">×</a>
    <div class="bmi-modal-content center">

      <div class="mm60 f30 bold black flex flexcenter mb">
        <img src="<?php echo esc_url( $this->get_asset('images', 'red-cross.svg') ); ?>" alt="red-cross" width="110px">
        <?php esc_html_e('Staging site creation failed', 'backup-backup') ?>
      </div>

      <div class="mm60 f22 lh28">
        <?php echo wp_kses_post( $messageStagingError1 ); ?>
      </div>

      <div class="mm60 f22 mbl mtl lh28">
        <?php echo wp_kses_post( $stagingErrorInstruction ); ?>
      </div>

      <div class="mm60">
        <a class="btn inline semibold mm60 f16 bmi-send-troubleshooting-logs" href="#!" target="_blank">
          <?php esc_html_e('Share debug infos with BackupBliss team', 'backup-backup') ?>
        </a>
      </div>

      <div class="mm60 f16 lh28 mtl">
        <?php echo wp_kses_post( $bmiTroubleshootingLogShareInfo ); ?><br>
        <?php echo wp_kses_post( $bmiTroubleshootingLogShareInfo2 ); ?>
      </div>

      <div class="mm60 f18 center mb mtl">
        <a href="#" class="bmi-modal-closer text-muted" data-close="staging-error-modal"><?php esc_html_e('Close window', 'backup-backup') ?></a>
      </div>

    </div>
  </div>

</div>

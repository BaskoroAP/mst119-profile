<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Use
  use BMI\Plugin\Checker\System_Info as SI;

  // Exit on direct access
  if (!defined('ABSPATH')) {
    exit;
  }

  // Tooltips
  $ctl = __("Your account on Wordpress.org (where you open a new support thread) is different to the one you login to your WordPress dashboard (where you are now). If you don’t have a WordPress.org account yet, please sign up at the top right on the Support Forum page, and then scroll down on that page . It only takes a minute :) Thank you!", 'backup-backup');

  $bmiTroubleshootingLogShareInfo = __("You will share: Website URL, %s1backup logs%s2, %s3restore logs%s2, %s4staging logs%s2, our plugin logs and configuration, and basic data about your site. No confidential data, such as email, will be shared.", 'backup-backup');
  $bmiTroubleshootingLogShareInfo2 = __("No confidential data such as email gets shared.", 'backup-backup');

  $bmiTroubleshootingLogShareInfo = str_replace('%s1', '<a href="#" class="download-backup-log-url hoverable secondary" download="backup_logs.txt">', $bmiTroubleshootingLogShareInfo);
  $bmiTroubleshootingLogShareInfo = str_replace('%s3', '<a href="#" class="download-restore-log-url hoverable secondary" download="restore_logs.txt">', $bmiTroubleshootingLogShareInfo);
  $bmiTroubleshootingLogShareInfo = str_replace('%s4', '<a href="#" class="download-staging-log-url hoverable secondary" download="staging_logs.txt">', $bmiTroubleshootingLogShareInfo);
  $bmiTroubleshootingLogShareInfo = str_replace('%s2', '</a>', $bmiTroubleshootingLogShareInfo);

  $pros = false;
  if (defined('BMI_BACKUP_PRO') && BMI_BACKUP_PRO == 1) {
    $pros = true;
  }
  $allowed_html = [
  'a' => [
    'href'     => true,
    'class'    => true,
    'download' => true,
  ],
  'br' => true,
];

?>

<div class="mm mt mb f20 lh40">

  <div class="mbl">
    <?php esc_html_e("If something doesn't work, you have several options - pick one:", 'backup-backup'); ?>
  </div>

  <table class="center-table trouble-section">

    <tr class="lh28">
      <td style="width: <?php echo $pros ? '50' : '33'?>%">
        <a href="https://wordpress.org/support/plugin/backup-backup/" target="_blank" class="nodec">
          <div class="shadow">
            <div class="flex flexcenter mtl mtb">
              <div style="width: 66px; height: 63px;">
                <svg style="width: 66px; height: 63px;"><use xlink:href="<?php echo esc_url($this->get_asset('images', 'support-1.svg')); ?>#img"></use></svg>
              </div>
              <div class="semibold">
                <?php esc_html_e("Ask us in the Support forum", 'backup-backup'); ?>
              </div>
            </div>
            <div class="f16">
              <?php esc_html_e("This is your first port of call. We'll try to respond quickly!", 'backup-backup'); ?>
            </div>
          </div>
        </a>
      </td>
      <?php if (!$pros): ?>
      <td style="width: 33%">
        <a href="<?php echo esc_url(BMI_AUTHOR_URI ); ?>" target="_blank" class="nodec">
          <div class="shadow">
            <div class="flex flexcenter mtl mtb">
              <div style="width: 69px; height: 63px;">
                <svg style="width: 69px; height: 63px; margin-top: 10px;"><use xlink:href="<?php echo esc_url($this->get_asset('images', 'support-2.svg')); ?>#img"></use></svg>
              </div>
              <div class="semibold">
                <?php esc_html_e("Get the Premium Plugin", 'backup-backup'); ?>
              </div>
            </div>
            <div class="f16">
              <?php esc_html_e("...which includes support, so we can help you in more detail if you get stuck.", 'backup-backup'); ?>
            </div>
          </div>
        </a>
      </td>
      <?php endif; ?>
      <td style="width: <?php echo $pros ? '50' : '33'?>%">
        <div class="shadow" id="open_trouble_extenstion">
          <div class="flex flexcenter mtl mtb">
            <div style="width: 70px; height: 63px;">
              <svg style="width: 70px; height: 63px;"><use xlink:href="<?php echo esc_url($this->get_asset('images', 'support-3.svg')); ?>#img"></use></svg>
            </div>
            <div class="semibold">
              <?php esc_html_e("Check advanced options", 'backup-backup'); ?>
            </div>
          </div>
          <div class="f16">
            <?php esc_html_e("...in an effort to fix it yourself. Be sure you know what you are doing!", 'backup-backup'); ?>
          </div>
        </div>
      </td>
    </tr>
    <tr>
      <td class="mtlll">
        <span class="tooltip hoverable info-cursor f18" tooltip="<?php echo esc_attr($ctl); ?>"><?php esc_html_e("Cannot log in there?", 'backup-backup'); ?></span>
      </td>
      <td></td>
      <td></td>
    </tr>

  </table>

</div>

<div id="trouble_extenstion" class="f20" style="display: none; min-width: calc(100% - 45px - 45px);">

  <div class="mm">
    <div class="f26 semibold mb">
      <?php esc_html_e("Troubleshooting settings", 'backup-backup'); ?>
    </div>
  </div>

  <div class="mm bmi-troubleshooting-btn-mm">

    <div class="f20 semibold">
      <?php esc_html_e("Send troubleshooting data", 'backup-backup'); ?>
    </div>

    <div class="f16 mtll mbll bmi-troubleshooting-btn-section">
      <div class="bmi-troubleshooting-btn-text">
        <?php esc_html_e("Send us the debug information of your latest failed backup or restore, so that we can investigate.", 'backup-backup'); ?>
      </div>
      <div class="bmi-inline">
        <a href="#" class="btn bmi-send-troubleshooting-logs bmi-troubleshooting-btn mm30"><?php esc_html_e("Share debug info with the BackupBliss team.", 'backup-backup'); ?></a>
      </div>
      <div class="bmi-troubleshooting-info-logs">
        <?php echo wp_kses($bmiTroubleshootingLogShareInfo, $allowed_html); ?><br>
        <?php echo wp_kses($bmiTroubleshootingLogShareInfo2, $allowed_html); ?>
      </div>
    </div>

  </div>

  <div class="mm">
    <div class="f20 semibold">
      <?php esc_html_e("Site information", 'backup-backup'); ?>
    </div>
    <div class="f16 mtll mbll">
      <?php esc_html_e("Here is some information about your site, which may help debug if there is an issue:", 'backup-backup'); ?>
    </div>
  </div>

  <div class="mm bg-second f16 mtl mbl lh25">
    <table style="width: 100%">
      <tbody>

        <?php
          require_once BMI_INCLUDES . '/check/system_info.php';
          $info = new SI();
          $info = $info->to_array();
          $i = 0;
          foreach ($info as $key => $value) {
            $i++; ?>
            <tr class="<?php echo(($i <= 7)?'ignored-tr':'hide-show-tr'); ?>">
              <th align="left"><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></th>
              <td><?php

                if (is_object($value)) {
                  echo esc_html($value->format('Y-m-d H:i:s.u'));
                } elseif (is_array($value)) {
                  if (sizeof($value) === 0) {
                    echo '---';
                  } else {
                    if ($key == 'wp_active_themes_info') {
                      echo esc_html($value[0]['name']) . '@' . esc_html($value[0]['version']);
                    } elseif ($key == 'wp_active_plugins_info') {
                      $disp = '';
                      for ($i = 0; $i < sizeof($value); ++$i) {
                        $disp .= esc_html($value[$i]['name']) . '@' . esc_html($value[$i]['version']) . ' | ';
                      }
                      echo esc_html(rtrim($disp, ' | '));
                    } else {
                      echo esc_html(implode(' | ', $value));
                    }
                  }
                } elseif (is_bool($value)) {
                  echo $value === true ? 'true' : 'false';
                } else {
                  if (!$value || is_null($value) || strlen($value) == '0') {
                    echo '---';
                  } else {
                    echo esc_html($value);
                  }
                } ?></td>
            </tr>
            <?php
          }
        ?>
        <tr class="ignored-tr">
          <th style="width: 400px"></th>
          <td align="right">
            <span class="hoverable secondary" id="switch-show-trs" data-see="<?php echo esc_attr__( "See more", 'backup-backup'); ?>" data-hide="<?php echo esc_attr__( "Collapse", 'backup-backup'); ?>">
              <?php esc_html_e("See more", 'backup-backup'); ?>
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="mm mtll f16">
    <a href="#" class="nodec secondary hoverable" id="download-site-infos"><?php esc_html_e("Download your site info", 'backup-backup'); ?></a> <?php esc_html_e("(e.g., for easy sharing with us, so that we can debug)", 'backup-backup'); ?>
  </div>

  <div class="mm mtl semibold">
    <?php esc_html_e("Logging", 'backup-backup'); ?>
  </div>

  <div class="mm mtll f16 lh28">
    <?php esc_html_e("All backup creation and restore processes are documented in log files, which can be used to debug issues.", 'backup-backup'); ?>
    <a href="<?php echo esc_url( get_site_url() . '/?backup-migration=PROGRESS_LOGS&progress-id=complete_logs.log&bmi-id=current&t=' . time() . '&sk=' . bmi_get_config('REQUEST:SECRET') ); ?>"
       download="troubleshooting-logs.txt" class="nodec hoverable secondary">
       <?php esc_html_e("Download logs.", 'backup-backup'); ?>
    </a>
  </div>

  <div class="mm mtl semibold">
    <?php esc_html_e("Backup/restore process issues", 'backup-backup'); ?>
  </div>

  <div class="mm mtll f16 lh28">
    <?php esc_html_e("If you are sure the backup process is not running but you can't stop it, ", 'backup-backup'); ?>
    <a href="#!" id="bmi-force-backup-to-stop" class="nodec hoverable secondary">
       <?php esc_html_e("force the process to stop.", 'backup-backup'); ?>
    </a>
    <br>
    <?php esc_html_e("If you are sure the restore process is not running but you can't stop it, ", 'backup-backup'); ?>
    <a href="#!" id="bmi-force-restore-to-stop" class="nodec hoverable secondary">
       <?php esc_html_e("force the restoration to stop.", 'backup-backup'); ?>
    </a>
    <br>
    <span><?php esc_html_e('*If the process is still running after being killed, it probably is still running (for real). Wait a bit for it to fail.', 'backup-backup'); ?></span>
  </div>

  <div class="mm mtl semibold">
    <?php esc_html_e("Error: php_uname is disabled for security reasons", 'backup-backup'); ?>
  </div>

  <div class="mm mtll f16 lh28">
    <?php esc_html_e("Some hosting providers block the php_uname function, which is required by the pclzip module included in WordPress.", 'backup-backup'); ?><br>
    <?php esc_html_e("You can automatically replace the function with compatible code:", 'backup-backup'); ?>
    <a href="#" class="nodec secondary hoverable" id="fix-uname-issues"><?php esc_html_e("replace the php_uname function in the pclzip file.", 'backup-backup'); ?></a><br>
    <?php esc_html_e("You can also restore the changes with one click if something goes wrong:", 'backup-backup'); ?>
    <a href="#" class="nodec secondary hoverable" id="revert-uname-issues"><?php esc_html_e("restore the original pclzip file (it will work after the first replacement).", 'backup-backup'); ?></a>
  </div>

  <div class="mm mtl semibold">
    <?php esc_html_e("Test email", 'backup-backup'); ?>
  </div>

  <div class="mm mtll f16 lh28">
    <?php esc_html_e("If you want to know if your server is properly configured to send emails, you can test it here.", 'backup-backup'); ?><br>
    <?php esc_html_e("Remember that even if you get a success alert, there may still be some issues.", 'backup-backup'); ?> <?php esc_html_e("Check if the email is visible in your mailbox / spam folder.", 'backup-backup'); ?><br>
    <?php esc_html_e("The message will be sent to the email you provided in the ", 'backup-backup'); ?> <a href="#" class="collapser-openner nodec secondary hoverable" data-el="#other-options"><?php esc_html_e("other options", 'backup-backup'); ?></a>.<br>
    <a href="#" id="bmi_send_test_mail" class="nodec hoverable secondary"><?php esc_html_e("Click here", 'backup-backup'); ?></a> <?php esc_html_e("to send the email.", 'backup-backup'); ?><br>
  </div>

  <div class="mm mtl mbll semibold">
    <?php esc_html_e("Reset configuration", 'backup-backup'); ?>
  </div>

  <div class="mm mb f16">
    <?php esc_html_e("Please", 'backup-backup'); ?> <a href="#" class="hoverable secondary bmi-modal-opener nodec" data-modal="reset-confirm-modal"><?php esc_html_e("click here", 'backup-backup'); ?></a> <?php esc_html_e("to reset the plugin configuration.", 'backup-backup'); ?>
  </div>

  <div class="mm mb f18">
    <?php esc_html_e("If you're looking for other options not listed above, check out the", 'backup-backup'); ?> <a href="#" class="collapser-openner nodec secondary hoverable" data-el="#other-options"><?php esc_html_e('"Other options"', 'backup-backup'); ?></a> <?php esc_html_e("chapter as they might be there.", 'backup-backup'); ?>
  </div>

  <div class="mm mb f18 lh28">
    <?php esc_html_e("If your site is having issues with scheduled backups or uploading backups to remote storage, ", 'backup-backup'); ?>
    <a href="#" class="hoverable secondary nodec" id="resync-with-ping-server"><?php esc_html_e("click here to resync with our ping server.", 'backup-backup'); ?></a><br>
    <?php esc_html_e("If the issue persists, please contact support.", 'backup-backup'); ?>
  </div>
</div>

<div class="mm center f20 mb">
  <a href="#" class="text-muted close-chapters nodec"><?php esc_html_e("Collapse this chapter", 'backup-backup'); ?></a>
</div>
<div class="mbll"></div>

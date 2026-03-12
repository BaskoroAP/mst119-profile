<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

  $urlparts = parse_url(home_url());
  $domain = $urlparts['host'];

  $backupTypeBase = __('%sThis feature is available in our premium extension!%s', 'backup-backup');
  $backupType = $backupTypeBase . __('%sUpgrade to %sPremium%s today%s%s%sWe made it really affordable!%s', 'backup-backup');
  $backupType = sprintf($backupType, '<div class="bmi-center-text">', '<br>', '<a href="' . BMI_AUTHOR_URI . '" target="_blank">', '<span class="bmi-premium-bg-stars">', '</span>', '</a>', '<br>', '<b>', '</b>', '</div>');

?>

<div class="mm mt mbl">

  <div class="lh30 mbll">
    <div class="fo-title semibold"><?php esc_html_e("File name", 'backup-backup'); ?></div>
    <div class="f20"><?php esc_html_e("Your backup(s) will be given the following file name(s):", 'backup-backup'); ?></div>
  </div>

  <div class="mm mm-border">
    <div class="center f18">
      <div class="">
        <input type="text" autocomplete="off" id="backup_filename" class="bmi-text-input" value="<?php echo esc_attr( sanitize_text_field(bmi_get_config('BACKUP:NAME')) ); ?>">
        <!-- <span class="oll mrr">.zip </span> -->
        <a href="#" id="show-format-tip" class="nodec secondary hoverable"><?php esc_html_e("Huh? Explain this please", 'backup-backup'); ?></a>
      </div>
    </div>
  </div>

</div>

<div class="mm lh30 f16 bg-second mtl mbl" id="format-tip-wrp" style="display: none;">

  <div class="lh30 f18">
    <?php esc_html_e("We're giving you maximum flexibility to automatically name your backup files in the way you want. Simply use below keys:", 'backup-backup'); ?>
  </div>
  <div class="mm mtl">
    <div class="format-entry"><b>%Y</b> = <?php esc_html_e("A full numeric representation of a year, 4 digits", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%M</b> = <?php esc_html_e("A short textual representation of a month, three letters", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%D</b> = <?php esc_html_e("A textual representation of a day, three letters", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%d</b> = <?php esc_html_e("Day of the month, 2 digits with leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%j</b> = <?php esc_html_e("Day of the month without leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%m</b> = <?php esc_html_e("Numeric representation of a month, with leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%n</b> = <?php esc_html_e("Numeric representation of a month, without leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%Y</b> = <?php esc_html_e("A full numeric representation of a year, 4 digits", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%y</b> = <?php esc_html_e("A two digit representation of a year", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%a</b> = <?php esc_html_e("Lowercase Ante meridiem and Post meridiem", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%A</b> = <?php esc_html_e("Uppercase Ante meridiem and Post meridiem", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%B</b> = <?php esc_html_e("Swatch Internet time", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%g</b> = <?php esc_html_e("12-hour format of an hour without leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%G</b> = <?php esc_html_e("24-hour format of an hour without leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%h</b> = <?php esc_html_e("12-hour format of an hour with leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%H</b> = <?php esc_html_e("24-hour format of an hour with leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%i</b> = <?php esc_html_e("Minutes with leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%s</b> = <?php esc_html_e("Seconds with leading zeros", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%hash</b> = <?php esc_html_e("16 character random hash", 'backup-backup'); ?></div>
    <div class="format-entry"><b>%domain</b> = <?php esc_html_e("Current domain name of the website.", 'backup-backup'); ?><?php echo " (" . esc_html( str_replace('.', '-', sanitize_text_field($domain)) ) . ") "; ?></div>

  </div>
  <div class="lh30 f18 mtl">
    <?php esc_html_e("Extension will be automatically appended to the name during backup creation.", 'backup-backup'); ?>
  </div>
  <div class="right-align">
    <a href="#" class="hoverable nodec secondary" id="hide-format-tip"><?php esc_html_e("Hide", 'backup-backup'); ?></a>
  </div>

</div>

<hr>

<div class="mm mbl mtl">

  <div class="lh30 mbll">
    <div class="fo-title semibold"><?php esc_html_e("Zipping", 'backup-backup'); ?></div>
    <div class="f20"><?php esc_html_e("Please select the compression method of your backup files:", 'backup-backup'); ?></div>
  </div>
  
  <div class="mm mm-border">
    <table class="f20">
      <tbody>
        <tr>

          <td>
            <?php if (has_action('bmip_zipping_methods')) { ?>
              <?php do_action('bmip_zipping_methods'); ?>
            <?php } else { ?>
            <div class="lh30">

              <div class="mbll">
                <label class="container-radio">
                  Zip
                  <input type="radio" name="free_version_backup_type" value=".zip" checked>
                  <span class="checkmark-radio"></span>
                </label>
              </div>

              <div class="mbll">
                <span class="cf premium-wrapper" tooltip="<?php echo esc_attr($backupType); ?>">
                  <label class="left container-radio ml25 not-allowed">
                    Tar
                    <input type="radio" disabled name="free_version_backup_type" value=".tar">
                    <span class="checkmark-radio"></span>
                  </label>
                  <span class="left premium premium-img premium-nt5"></span>
                </span>
              </div>

              <div class="" style="width: 185px;">
                <span class="cf premium-wrapper" tooltip="<?php echo esc_attr($backupType); ?>">
                  <label class="left container-radio ml25 not-allowed">
                    Tar GZip
                    <input type="radio" disabled name="free_version_backup_type" value=".tar.gz">
                    <span class="checkmark-radio"></span>
                  </label>
                  <span class="left premium premium-img premium-nt5"></span>
                </span>
              </div>

            </div>
            <?php } ?>
          </td>

          <td>
            <div class="f16 mw850 bol lh30">
              <i><?php esc_html_e('"ZIP" is the standard choice (compression level 1). Use "Tar" (compression level 2) or "Tar.gz" (compression level 5) if you want greater compression (i.e., smaller file sizes). However, this will also put more load on the backup process.', 'backup-backup'); ?> <?php esc_html_e('It will have no effect if the server does not support particular extensions.', 'backup-backup'); ?></i>
            </div>
          </td>

        </tr>
      </tbody>
    </table>
  </div>
</div>

<hr>

<div class="mm mtl mbl overlayed">

  <?php include BMI_INCLUDES . '/dashboard/templates/premium-overlay.php'; ?>

  <div class="">
    <div class="lh30 mbll">
      <div class="fo-title semibold">
        <span class="cf premium-wrapper">
          <div class="left"><?php esc_html_e("Encryption", 'backup-backup'); ?></div>
          <span class="left premium premium-img"></span>
        </span>
      </div>
      <div class="f20"><?php esc_html_e("Do you want to encrypt and password protect your files?", 'backup-backup'); ?></div>
    </div>

    <div class="mm mm-border">
      <div class="d-flex mr60 ia-center">
        <label class="container-radio">
          <?php esc_html_e("No", 'backup-backup'); ?>
          <input type="radio" name="" value="false" checked>
          <span class="checkmark-radio"></span>
        </label>
        <label class="container-radio ml25 not-allowed">
          <?php esc_html_e("Yes", 'backup-backup'); ?>
          <input type="radio" disabled name="" value="true">
          <span class="checkmark-radio"></span>
        </label>
      </div>
    </div>
  </div>

</div>

<hr>

<div class="mm f16 mtl mbl">
  <i><?php esc_html_e("For other security settings, e.g. who can access your backup directories, please go to the", 'backup-backup'); ?> <a href="#" class="hoverable secondary nodec collapser-openner" data-el="#other-options"><?php esc_html_e("Other options", 'backup-backup'); ?></a> <?php esc_html_e("chapter.", 'backup-backup'); ?></i>
</div>

<?php include BMI_INCLUDES . '/dashboard/chapter/save-button.php'; ?>

<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

  // ThMaker
  function __th($n) {

    $ths = [__("st", 'backup-backup'), __("nd", 'backup-backup'), __("rd", 'backup-backup'), __("th", 'backup-backup')];
    $n = intval($n);
    $nah = [11, 12, 13];

    if (in_array($n, $nah)) return $ths[3];
    else {

      if (substr(''.$n, -1) == '1') return $ths[0];
      elseif (substr(''.$n, -1) == '2') return $ths[1];
      elseif (substr(''.$n, -1) == '3') return $ths[2];
      else return $ths[3];

    }

  }

?>

<div class="backup-creator cf section-bmi" id="bmi-section--cron">
  <div class="left">
    <div class="create-now pointer bmi-backup-btn one shadow" id="i-backup-creator">
      <div class="insider"></div>
      <div class="insider-2"></div>
      <div class="vcenter">
        <img src="<?php echo esc_url($this->get_asset('images', 'backup-min.svg')); ?>" alt="server-img" class="img-now">
        <div class="text">
          <span class="medium pointer">
            <?php esc_html_e('Create backup', 'backup-backup') ?> <span class="bold"><?php esc_html_e('now!', 'backup-backup') ?></span>
          </span>
        </div>
      </div>
    </div>
  </div>
  <div class="left cron-backups-wrap" id="i-backup-cron">
    <div class="cron-backups shadow relative<?php echo ((bmi_get_config('CRON:ENABLED') === false) ? ' disabled' : '') ?>">
      <div class="turned-off pointer transition"<?php echo ((bmi_get_config('CRON:ENABLED') === true) ? ' style="display: none"' : '') ?>>
        <div class="vcenter">
          <div class="fullwidth">
            <div class="cf inline lh28">
              <div class="left">
                <img src="<?php echo esc_url($this->get_asset('images', 'timemachine.svg')); ?>" alt="cron-icon" height="30px;">&nbsp;&nbsp;
              </div>
              <div class="left">
                <span class="f20 regular">
                  <?php esc_html_e('... or have', 'backup-backup') ?>
                  <span class="semibold"><?php esc_html_e('backups created automatically', 'backup-backup') ?></span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="turned-on f18"<?php echo ((bmi_get_config('CRON:ENABLED') === false) ? ' style="display: none"' : '') ?>>

        <div class="cron-a cf">
          <div class="left semibold relative">
            <?php esc_html_e("Automatic backups creation", 'backup-backup'); ?>&nbsp;
            <span class="bmi-info-icon tooltip cron-time-server" tooltip=""></span>
          </div>
          <div class="right">

            <label for="cron-btn-toggle" class="bmi-switch">
              <input type="checkbox" id="cron-btn-toggle"<?php echo ((bmi_get_config('CRON:ENABLED') === false) ? ' checked' : '') ?>>
              <div class="bmi-switch-slider round">
                <span class="on"><?php esc_html_e("On", 'backup-backup'); ?></span>
                <span class="off"><?php esc_html_e("Off", 'backup-backup'); ?></span>
              </div>
            </label>

          </div>
        </div>

        <div class="cron-bc">
          <div class="cron-b bg-second cf">
            <table class="ooo-to-pad">
              <tbody>
                <tr>
                  <td class="orr" style="min-width: 76px;"><?php esc_html_e("Create a backup every", 'backup-backup'); ?></td>
                  <td>
                    <select id="cron-period" data-parent="#bmi-section--cron" data-classes="orr" data-def="<?php echo esc_attr(sanitize_text_field(bmi_get_config('CRON:TYPE'))); ?>">
                      <option value="month"><?php esc_html_e("Month", 'backup-backup'); ?></option>
                      <option value="week"><?php esc_html_e("Week", 'backup-backup'); ?></option>
                      <option value="day"><?php esc_html_e("Day", 'backup-backup'); ?></option>
                    </select>
                  </td>
                  <td id="cron-on-word"><?php esc_html_e("on", 'backup-backup'); ?></td>
                  <td class="cron-the"><?php esc_html_e("the", 'backup-backup'); ?></td>
                  <td>
                    <select id="cron-day" data-parent="#bmi-section--cron" data-classes="left orr oll" data-def="<?php echo esc_attr(sanitize_text_field(bmi_get_config('CRON:DAY'))); ?>">
                      <?php for ($i = 0; $i < 28; ++$i): ?>
                        <?php $d = ($i+1); ?>
                        <option value="<?php echo esc_attr($d); ?>"><?php echo esc_html($d . __th($d)); ?></option>
                      <?php endfor; ?>
                    </select>
                  </td>
                  <td>
                    <select id="cron-week" data-parent="#bmi-section--cron" data-classes="orr oll" data-hide="true" data-def="<?php echo esc_attr(sanitize_text_field(bmi_get_config('CRON:WEEK'))); ?>">
                      <option value="1"><?php esc_html_e("Monday", 'backup-backup'); ?></option>
                      <option value="2"><?php esc_html_e("Tuesday", 'backup-backup'); ?></option>
                      <option value="3"><?php esc_html_e("Wednesday", 'backup-backup'); ?></option>
                      <option value="4"><?php esc_html_e("Thursday", 'backup-backup'); ?></option>
                      <option value="5"><?php esc_html_e("Friday", 'backup-backup'); ?></option>
                      <option value="6"><?php esc_html_e("Saturday", 'backup-backup'); ?></option>
                      <option value="7"><?php esc_html_e("Sunday", 'backup-backup'); ?></option>
                    </select>
                  </td>
                  <td class="orr"><?php esc_html_e("at", 'backup-backup'); ?></td>
                  <td>
                    <select id="cron-hour" data-parent="#bmi-section--cron" data-def="<?php echo esc_attr(sanitize_text_field(bmi_get_config('CRON:HOUR'))); ?>">
                      <?php for ($i = 0; $i < 24; ++$i): ?>
                        <?php $d = substr('0' . ($i), -2); ?>
                        <option value="<?php echo esc_attr($d); ?>"><?php echo esc_html($d); ?></option>
                      <?php endfor; ?>
                    </select>
                  </td>
                  <td class="orr oll"><?php esc_html_e("hours and", 'backup-backup'); ?></td>
                  <td>
                    <select id="cron-minute" data-parent="#bmi-section--cron" data-def="<?php echo esc_attr(sanitize_text_field(bmi_get_config('CRON:MINUTE'))); ?>">
                      <?php for ($i = 0; $i < 60; $i += 5): ?>
                        <?php $d = substr('0' . ($i), -2); ?>
                        <option value="<?php echo esc_attr($d); ?>"><?php echo esc_html($d); ?></option>
                      <?php endfor; ?>
                    </select>
                  </td>
                  <td class="orr"><?php esc_html_e("minutes", 'backup-backup'); ?></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="cron-c cf">
            <table class="left ooo-to-pad" style="max-width: calc(100% - 110px);">
              <tbody>
                <tr>
                  <td class="orr oll">
                    <?php esc_html_e("...and keep the last", 'backup-backup'); ?>
                  </td>
                  <td>
                    <select id="cron-keep-backups" data-parent="#bmi-section--cron" data-def="<?php echo esc_attr(sanitize_text_field(bmi_get_config('CRON:KEEP'))); ?>">
                      <?php for ($i = 0; $i < 20; ++$i): ?>
                        <option value="<?php echo esc_attr($i+1); ?>"><?php echo esc_html($i+1); ?></option>
                      <?php endfor; ?>
                    </select>
                  </td>
                  <td class="orr oll">
                    <?php esc_html_e("backups that have been created automatically.", 'backup-backup'); ?>
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="lrn-mr-btn hoverable secondary right">
              <?php esc_html_e('Learn more', 'backup-backup'); ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="mbl learn_more_about_cron" style="display: none;">
  <ol style="list-style: outside;">
    <li>
      <?php esc_html_e("Above times are", 'backup-backup'); ?>
      <b><?php esc_html_e("server times", 'backup-backup'); ?></b>
      (<?php esc_html_e("time now:", 'backup-backup'); ?> <span id="server-time-auto" data-time="<?php echo esc_attr(time()); ?>"></span>)
    </li>
    <li>
      <?php esc_html_e("For Automatic backups in the free plugin version, there needs to be", 'backup-backup'); ?>
      <b><?php esc_html_e("at least one visitor", 'backup-backup'); ?></b>
      <?php esc_html_e("so that the backup process gets triggered, as it relies on WordPress' native ", 'backup-backup'); ?>
      <a href="https://developer.wordpress.org/plugins/cron/" target="_blank" class="secondary hoverable"><?php esc_html_e("WP-Cron", 'backup-backup'); ?></a>.
      <?php esc_html_e("The ", 'backup-backup'); ?>
      <a href="https://backupbliss.com/" target="_blank" class="secondary hoverable">
      <?php esc_html_e("premium plugin", 'backup-backup'); ?></a>
      <?php esc_html_e("uses BackupBliss server trigger, so Automatic backups are always on time.", 'backup-backup'); ?>
    </li>
    <li>
      <?php esc_html_e("We suggest", 'backup-backup'); ?>
      <b><?php esc_html_e("keeping only 2 or 3 backups", 'backup-backup'); ?></b>
      <?php esc_html_e(", otherwise you may run out of space.", 'backup-backup'); ?>
    </li>
    <li>
      <b><?php echo wp_kses_post( __('Locked backups will <u>not</u> be deleted', 'backup-backup') ); ?></b>
      <?php esc_html_e("automatically. Those are indicated by a lock sign", 'backup-backup'); ?>
      <img src="<?php echo esc_url($this->get_asset('images', 'lock-min.svg')); ?>" alt="lock" class="inline" height="18px">.
      <?php esc_html_e('Manually created backups (i.e., those created by clicking on "Create backup now!") are permanently locked, while automatically created backups are by default unlocked.', 'backup-backup'); ?>
      <?php esc_html_e("You can change their lock status on the", 'backup-backup'); ?>
      <span class="secondary hoverable go-to-marbs"><?php esc_html_e("Manage & Restore Backup(s)", 'backup-backup'); ?></span>
      <?php esc_html_e("tab", 'backup-backup'); ?>.
    </li>
    <li>
      <?php esc_html_e("For", 'backup-backup'); ?>
      <b><?php esc_html_e("other triggers", 'backup-backup'); ?></b>
      <?php esc_html_e("when your backups are created, please go", 'backup-backup'); ?>
      <span class="secondary hoverable collapser-openner" data-el="#other-options"><?php esc_html_e("here", 'backup-backup'); ?></span>.
    </li>
  </ol>
  <div class="right-align hoverable secondary closer-learn-more">
    <?php esc_html_e("Collapse", 'backup-backup'); ?>
  </div>
</div>

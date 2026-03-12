<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<div class="bmi-modal" id="bfs-modal">

  <div class="bmi-modal-wrapper no-hpad" style="max-width: 1080px; max-width: min(1080px, 80vw);">
    <a href="#" class="bmi-modal-close">×</a>
    <div class="bmi-modal-content">

      <table>
        <tbody>
          <tr>

            <td>
              <div class="mm60">
                <img class="center block inline" style="height: 379px; transform: rotateY(180deg);" src="<?php echo esc_url( $this->get_asset('images', 'kitty-cat.svg') ); ?>" alt="kitty">
              </div>
            </td>
            <td>
              <div class="f20 lh30 mm" style="padding-left: 0;">
                <div class="f35 semibold mbl">
                  <?php esc_html_e("Wow, your site is", 'backup-backup'); ?> <span class="bold"><?php esc_html_e("FAT!", 'backup-backup'); ?></span>
                </div>

                <div class="mbl">
                  <?php printf(esc_html(__("For large sites (above %sGB) we point you to the premium plugin, because:", 'backup-backup')), esc_html(BMI_REV)); ?>
                </div>

                <div class="mbl">
                  <ul>
                    <li class="hasArrowRight"><?php esc_html_e("Big sites tend to be more support heavy", 'backup-backup'); ?></li>
                    <li class="hasArrowRight"><?php esc_html_e("We also need to buy food", 'backup-backup'); ?></li>
                    <li class="hasArrowRight"><?php esc_html_e("The plugin offers many features for free which other plugins don't", 'backup-backup'); ?></li>
                  </ul>
                </div>

                <div class="mbll">
                  <?php esc_html_e("We made it really affordable and it's a great way to show your appreciation and support the further development of the plugin :) Thank you!", 'backup-backup'); ?>
                </div>

                <a href="<?php echo esc_url( BMI_AUTHOR_URI ); ?>" target="_blank" class="btn center lh45">
                  <div class="f18"><?php esc_html_e("Yes, that's fair…", 'backup-backup'); ?></div>
                  <div class="f30 bold"><?php esc_html_e("Get premium", 'backup-backup'); ?></div>
                  <div class="f18"><?php esc_html_e("(which includes other cool features too!)", 'backup-backup'); ?></div>
                </a>

              </div>
            </td>

          </tr>
        </tbody>
      </table>

      <div class="center f18 mt">
        <span class="text-muted">
          <?php esc_html_e("Tip: You can also", 'backup-backup'); ?>
        </span>
        <span class="hoverable secondary">
          <a href="#" class="bmi-modal-closer secondary collapser-openner nodec" data-el="#exlucde-parts"><?php esc_html_e("exclude parts from backup", 'backup-backup'); ?></a>
        </span>
        <span class="text-muted">
          , <?php esc_html_e("which makes it smaller so that you stay below this limit.", 'backup-backup'); ?>
        </span>
      </div>

    </div>
  </div>

</div>

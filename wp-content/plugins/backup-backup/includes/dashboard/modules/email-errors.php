<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;
  use BMI\Plugin\Backup_Migration_Plugin AS BMP;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<?php if (get_option('bmi_display_email_issues', false)): ?>

<div class="error-noticer" id="email-issues">
  <div class="error-header">
    <div class="cf">
      <div class="left">
        <?php esc_html_e('We have some notices regarding most recent automated backup.', 'backup-backup'); ?>
      </div>
      <div class="right hoverable">
        <span class="bmi-error-toggle" data-expand="<?php esc_attr_e('Expand', 'backup-backup'); ?>" data-collapse="<?php esc_attr_e('Collapse', 'backup-backup'); ?>">
          <?php esc_html_e('Expand', 'backup-backup'); ?>
        </span> |
        <span id="bmi-error-dismiss">
          <?php esc_html_e('Dismiss', 'backup-backup'); ?>
        </span>
      </div>
    </div>
  </div>
  <div class="error-body">
    <?php echo wp_kses_post(get_option('bmi_display_email_issues', false)); ?>
  </div>
</div>

<?php endif; ?>

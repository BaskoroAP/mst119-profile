<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<?php if (get_transient('bmip_display_quota_issues') && !get_option('bmip_dismissed_quota_notice', false)): ?>

<div class="error-noticer" id="quota-issues">
  <div class="error-header">
    <div class="cf">
      <div class="left">
        <?php esc_html_e('We have some error regarding most recent backup upload process.', 'backup-backup'); ?>
      </div>
      <div class="right hoverable">
        <span class="bmi-error-toggle" data-expand="<?php esc_attr_e('Expand', 'backup-backup'); ?>" data-collapse="<?php esc_attr_e('Collapse', 'backup-backup'); ?>">
          <?php esc_html_e('Expand', 'backup-backup'); ?>
        </span> |
        <span id="bmip-quota-issues-dismiss">
          <?php esc_html_e('Dismiss', 'backup-backup'); ?>
        </span>
      </div>
    </div>
  </div>
  <div class="error-body">
    <?php echo wp_kses_post(get_transient('bmip_display_quota_issues')); ?>
  </div>
</div>

<?php endif; ?>

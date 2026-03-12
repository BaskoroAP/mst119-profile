<?php
// Exit on direct access
if (!defined('ABSPATH')) exit;

use BMI\Plugin\Dashboard as Dashboard;
use BMI\Plugin\BMI_Logger as Logger;
use BMI\Plugin\Backup_Migration_Plugin as BMP;

// Determine plugin versions and status
$has_premium = defined('BMI_BACKUP_PRO') && BMI_BACKUP_PRO;
$free_version = defined('BMI_VERSION') ? BMI_VERSION : null;
$premium_version = defined('BMI_PRO_VERSION') ? BMI_PRO_VERSION : null;

$notices = [];

// Check if free version needs auto-update
$free_needs_update = BMP::bmiNeedsUpdate();
$premium_needs_update = BMP::bmiNeedsUpdate(true);

// Get plugin file paths
$free_plugin_file = 'backup-backup/backup-backup.php'; // Adjust to actual plugin file
$premium_plugin_file = 'backup-backup-pro/backup-backup-pro.php'; // Adjust to actual premium plugin file

// Get admin URLs
$plugins_page_url = admin_url('plugins.php');

if ($has_premium && ($free_needs_update || $premium_needs_update)) {
  // Both free and premium versions, at least one needs update
  $notices[] = sprintf(
    __('%sWarning:%s plugin version discrepancy detected. Keeping free and premium plugin auto-updated ensures the quality of our service, provides new features, and enforces security. Please enable auto-updates %shere%s.', 'backup-backup'),
    '<strong>', '</strong>',
    '<a class="hoverable secondary" href="' . esc_url($plugins_page_url) . '">','</a>'
  );
} elseif (!$has_premium && $free_needs_update) {
  // Only free version, needs update
  // Try to get the enable auto-update link
  $auto_update_url = wp_nonce_url(
    add_query_arg(
      [
        'action' => 'toggle-auto-update',
        'plugin' => $free_plugin_file,
        'paged' => 1,
      ],
      admin_url('plugins.php')
    ),
    'updates'
  );
  
  $notices[] = sprintf(
    __('Keeping the plugin auto-updated ensures the quality of our service, provides new features and enforces security. Please enable auto-updates %shere%s.', 'backup-backup'),
    '<a href="' . esc_url($auto_update_url) . '">',
    '</a>'
  );
}

if (empty($notices)) {
  return; // No issues to display
}
?>

<div class="error-noticer" id="backupbliss-issues">
  <div class="error-header">
    <div class="cf">
      <div class="left">
        <?php esc_html_e('We have some issue(s) regarding BackupBliss.', 'backup-backup'); ?>
      </div>
      <div class="right hoverable">
        <span class="bmi-error-toggle" data-expand="<?php esc_attr_e('Expand', 'backup-backup'); ?>" data-collapse="<?php esc_attr_e('Collapse', 'backup-backup'); ?>">
          <?php esc_html_e('Expand', 'backup-backup'); ?>
        </span> |
        <span id="bmi-error-dismiss" issue-type="backupbliss" onclick="document.getElementById('backupbliss-issues').remove()">
          <?php esc_html_e('Dismiss', 'backup-backup'); ?>
        </span>
      </div>
    </div>
  </div>
  <div class="error-body">
    <?php
      echo wp_kses_post(implode("<br /><br />", $notices));
    ?>
  </div>
</div>
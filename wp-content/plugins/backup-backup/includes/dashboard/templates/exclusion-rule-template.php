<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) {
    exit;
  }

?>

<div class="mtll exclude-row exclusion_template">
  <span><?php esc_html_e("Exclude if string", 'backup-backup'); ?></span>
  <input class="exclusion_txt" type="text" autocomplete="off">
  <span class="orr"><?php esc_html_e("appears", 'backup-backup'); ?></span>
  <div class="exclusion_position inline">
    <select>
      <option value="1"><?php esc_html_e("anywhere in", 'backup-backup'); ?></option>
      <option value="2"><?php esc_html_e("at the beginning of", 'backup-backup'); ?></option>
      <option value="3"><?php esc_html_e("at the end of", 'backup-backup'); ?></option>
    </select>
  </div>
  <span class="oll orr"><?php esc_html_e("the", 'backup-backup'); ?></span>
  <div class="exclusion_where inline">
    <select>
      <option value="1"><?php esc_html_e("file name", 'backup-backup'); ?></option>
      <option value="2"><?php esc_html_e("folder name", 'backup-backup'); ?></option>
    </select>
  </div>
  <span class="oll kill-exclusion-rule">
    <img src="<?php echo esc_url( $this->get_asset('images', 'red-close-min.svg') ); ?>" alt="close-img" class="hoverable" height="15px">
  </span>
</div>

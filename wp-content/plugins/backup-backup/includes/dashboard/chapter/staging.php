<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<!-- Templates -->
<div style="display: none; visibility: none;" class="hide">

  <!-- Staging Option Row Template -->
  <?php include BMI_INCLUDES . '/dashboard/templates/stg-option-template.php'; ?>

</div>

<?php if (!file_exists(ABSPATH . '.bmi_staging')): ?>

<!-- STAGING SITES: MODE SELECTOR HEADING -->
<div class="staging-heading">
  <?php echo wp_kses_post( __('Create a <b>staging site</b> on:', 'backup-backup') ); ?>
</div>

<!-- STAGING SITES: MODE SELECTOR -->
<div class="bmi-stg-flex">
  <div class="bmi-stg-sel-box bmi-active" data-mode="local">
    <div class="bmi-stg-sel-header"><?php esc_html_e('Your server & domain', 'backup-backup'); ?></div>
    <div class="bmi-stg-sel-benefits">
      <ul>
        <li><?php echo wp_kses_post( __('Keep all files on <b>your server</b>', 'backup-backup') ); ?></li>
        <li><?php echo wp_kses_post( __('<b>Define the subpath</b> for your staging site', 'backup-backup') ); ?></li>
        <li><?php esc_html_e('Free for any size and no expiry', 'backup-backup'); ?></li>
        <li><?php echo wp_kses_post( __('<b>Free</b> for any size & <b>no expiry</b>', 'backup-backup') ); ?></li>
      </ul>
    </div>
  </div>
  <div class="bmi-stg-sel-box" data-mode="tastewp">
    <div class="bmi-stg-sel-header"><?php echo wp_kses_post( str_replace('%s1%', 'href="https://tastewp.com" target="_blank"', __('TasteWP <a %s1%>(external server)</a>', 'backup-backup')) ); ?></div>
    <div class="bmi-stg-sel-benefits">
      <ul>
        <li><?php echo wp_kses_post( str_replace('%s', 'href="https://tastewp.com/?open=privacy" target="_blank"', __('Files are <b>protected</b> and <a %s>stay confidential</a>', 'backup-backup')) ); ?></li>
        <li><?php echo wp_kses_post( __('<b>Play around</b> without impacting your live server and domain', 'backup-backup') ); ?></li>
        <li><?php echo wp_kses_post( __('Use <b>any (partial or full) backup</b> for the copy', 'backup-backup') ); ?></li>
        <li><?php echo wp_kses_post( str_replace('%s1%', 'href="https://tastewp.com/?open=login" target="_blank"', str_replace('%s2%', 'href="https://tastewp.com/premium-show" target="_blank"', __('Free for <b>up to 1GB and 2 days</b> (or 7 days if <a %s1%>logged in</a>) – remove limits by <a %s2%>upgrading</a>', 'backup-backup'))) ); ?></li>
      </ul>
    </div>
  </div>
</div>

<!-- STAGING SITES: LOCAL OPTION -->
<div class="bmi-stg-creation-box shadow bmi-stg-creation-box-local">
    <div class="bmi-stg-creation-title">
      <?php esc_html_e('Define the path for your staging site:', 'backup-backup'); ?>
    </div>
    <div class="bmi-stg-creation-content">
      <div class="bmi-stg-creation-menu">
        <span id="bmi-stg-homeurl"><?php echo esc_url( home_url() ); ?>/</span> <input type="text" autocomplete="off" id="bmi-stg-subname-input" placeholder="<?php esc_attr_e('staging', 'backup-backup'); ?>">
      </div>
    </div>
    <div class="bmi-stg-creation-button">
      <button
        c-error="<?php esc_attr_e('There was some error during validation, please refresh and try again.', 'backup-backup'); ?>"
        c-errorProcess="<?php esc_attr_e('There was some error during the process, please follow after error instructions.', 'backup-backup'); ?>"
        c-empty="<?php esc_attr_e('You have to provide some staging site name before process.', 'backup-backup'); ?>"
        c-long="<?php esc_attr_e('Staging site name cannot be longer than 24 characters.', 'backup-backup'); ?>"
        c-invalid="<?php esc_attr_e('Provided name contains prohibited characters.', 'backup-backup'); ?>"
        type="button"
        class="btn i-staging-creator-local"
        id="stg-notices-button">
        <?php esc_html_e('Create staging site!', 'backup-backup'); ?>
      </button>
    </div>
</div>

<!-- STAGING SITES: TASTEWP SELECTED -->
<div class="bmi-stg-creation-box shadow bmi-stg-creation-box-tastewp" style="display: none;">
  <div class="bmi-stg-creation-title">
    <?php esc_html_e('Which backup do you want to use for your staging site?', 'backup-backup'); ?>
  </div>
  <div class="bmi-stg-creation-content">
    <div class="bmi-stg-creation-menu">
      <input type="text" hidden id="bmi-stg-current-backup-selected" value="" autocomplete="off">
      <div class="bmi-stg-dropdown-area">
        <div class="bmi-stg-dropdown-area-selector">
          <div class="bmi-stg-option-name"><?php esc_html_e('Loading, please wait...', 'backup-backup'); ?></div>
          <span class="bmi-stg-option-date"><b><?php esc_html_e('Created:', 'backup-backup'); ?></b>&nbsp;<i>---</i></span>
          <span class="bmi-stg-option-size"><b><?php esc_html_e('Size:', 'backup-backup'); ?></b>&nbsp;<i>---</i></span>
        </div>
        <div class="bmi-stg-dropdown-area-options">
          <div class="bmi-stg-dropdown-area-inner-scroll"></div>
        </div>
      </div>
    </div>
    <div class="bmi-stg-creation-description">
      <?php echo wp_kses_post( str_replace('%s1%', '<a href="#!" class="bmi-stg-tab-backups i-backup-creator-trigger">', str_replace('%s2%', '</a>', __('To take the current live files, please first %s1%create a backup%s2%.', 'backup-backup'))) ); ?>
    </div>
  </div>
  <div class="bmi-stg-creation-button">
    <button type="button" class="btn i-staging-creator-tastewp"><?php esc_html_e('Create staging site!', 'backup-backup'); ?></button>
  </div>
</div>

<!-- STAGING SITES: TASTEWP SELECTED BUT NO BACKUPS -->
<div class="bmi-stg-creation-box shadow bmi-stg-creation-box-tastewp-empty" style="display: none;">
  <div class="bmi-stg-creation-content">
    <div class="bmi-stg-creation-description">
      <i><?php echo wp_kses_post( str_replace('%s1%', '<a href="#!" class="bmi-stg-tab-backups i-backup-creator-trigger">', str_replace('%s2%', '</a>', __('Please first %s1%create a backup%s2% so that it can be used for your staging site.', 'backup-backup'))) ); ?></i>
    </div>
  </div>
  <div class="bmi-stg-creation-button">
    <button type="button" class="btn i-backup-creator-trigger"><?php esc_html_e('Create backup!', 'backup-backup'); ?></button>
  </div>
</div>

<!-- STAGING SITES LIST HEADING -->
<div class="staging-heading">
  <?php echo wp_kses_post( __('Your <b>staging site(s)</b> list:', 'backup-backup') ); ?>
</div>

<!-- STAGING SITES LIST -->
<div class="staging-list-wrapper shadow fullwidth rbb rbt">

  <div class="f22 regular m mbl"><?php esc_html_e("Your active staging sites:", 'backup-backup'); ?></div>

  <div class="table-wrapper" id="bmi-stg-table">
    <table>
      <thead>
        <tr>
          <th>
            <div class="inline tooltip" tooltip="<?php esc_attr_e('Name of desired staging site - can be modified', 'backup-backup') ?>." data-top="5">
              <?php esc_html_e('Name', 'backup-backup') ?>
            </div>
          </th>
          <th>
            <div class="inline tooltip" tooltip="<?php esc_attr_e('URL of the staging site', 'backup-backup') ?>." data-top="5">
              <?php esc_html_e('URL', 'backup-backup') ?>
            </div>
          </th>
          <th>
            <div class="inline tooltip" tooltip="<?php esc_attr_e('Size and files at moment of creation', 'backup-backup') ?>." data-top="5">
              <?php esc_html_e('Size (files)', 'backup-backup') ?>
            </div>
          </th>
          <th>
            <div class="inline tooltip" tooltip="<?php esc_attr_e('Where the site is created', 'backup-backup') ?>." data-top="5">
              <?php esc_html_e('Server', 'backup-backup') ?>
            </div>
          </th>
          <th>
            <div class="inline tooltip" tooltip="<?php esc_attr_e('When the staging site was created', 'backup-backup') ?>." data-top="5">
              <?php esc_html_e('Creation date', 'backup-backup') ?>
            </div>
          </th>
          <th>
            <div class="inline tooltip" tooltip="<?php esc_attr_e('When the staging site will be removed', 'backup-backup') ?>." data-top="5">
              <?php esc_html_e('Expires in', 'backup-backup') ?>
            </div>
          </th>
          <th>
            <div class="inline tooltip" tooltip="<?php esc_attr_e('Move over the icons to see what you can do with staging site', 'backup-backup') ?>." data-top="5">
              <?php esc_html_e('Actions', 'backup-backup') ?>
            </div>
          </th>
        </tr>
      </thead>
      <tbody id="stg-tbody-table" data-empty="<?php esc_attr_e('You have not created any staging site yet.', 'backup-backup'); ?>" data-local="<?php esc_attr_e('Local', 'backup-backup'); ?>" data-prefix="<?php esc_attr_e('Database Prefix', 'backup-backup') ?>" data-never="<?php esc_attr_e('Never', 'backup-backup') ?>" data-display="<?php esc_attr_e('Display name', 'backup-backup') ?>" data-original="<?php esc_attr_e('Original name', 'backup-backup') ?>">
        <tr>
          <td colspan="100%" class="center"><?php esc_html_e('You have not created any staging site yet.', 'backup-backup'); ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="cf lh28 valign-staging">

    <div class="left cf">
      <div class="left">
        <img class="a1" src="<?php echo esc_url($this->get_asset('images', 'search-min.png')); ?>" width="15px" alt="image">
      </div>
      <div class="left f16">
        <?php esc_html_e('Update status of staging sites:', 'backup-backup') ?>
        <a href="#" id="rescan-for-staging" class="secondary hoverable"><?php esc_html_e('Refresh', 'backup-backup') ?></a>
        <span id="reloading-bm-stg-list" style="display: none;"><div class="spinner-loader"></div></span>
      </div>
    </div>

  </div>
</div>

<?php else: ?>

<!-- ON STAGING SITE -->
<style media="screen">
  .stg-restore-btn { display: none !important; }
</style>
<div class="bmi-stg-creation-box shadow bmi-stg-creation-box-local">
    <div class="bmi-stg-creation-title">
      <?php esc_html_e('You are already on a staging site!', 'backup-backup'); ?>
    </div>
    <div class="bmi-stg-creation-content">
      <div class="bmi-stg-creation-description">
        <br />
        <br />
        <i>
          <?php esc_html_e("Currently that's all you can do, we're working on a feature that will allow you to merge your staging site with your live site in one click.", 'backup-backup'); ?><br />
          <?php esc_html_e('For now, you can simply create a backup of this (staging) site, and restore it on your live site which will do the trick as well :)', 'backup-backup'); ?>
        </i>
      </div>
    </div>
</div>

<?php endif; ?>

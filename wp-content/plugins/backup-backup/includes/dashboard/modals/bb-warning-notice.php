<?php

// Disallow direct access
if (!defined('ABSPATH')) exit;


$preorder = 'https://backupbliss.com/pricing';

?>
<div <?php echo isset($global_warning) ? 'id="bb-warning-notice"' : ''; ?>>
<div class="bb-upload-fail-warning space-between flexcenter" <?php echo isset($global_warning) ? 'style="margin-bottom: 15px"' : ''; ?>>
    <div class="warning-text space-between flexcenter">
        <img src="<?php echo esc_url( $this->get_asset('images', 'warning-white.png') ); ?>" alt="warning" class="warning-img">
        <span class="f20">
            <?php echo wp_kses_post( $error_message ); ?>
        </span>
    </div>
    <div class="get-more-storage">
        <a href="<?php echo esc_url( BMI_AUTHOR_URI ); ?>" target="_blank"
            class="f16 bold nodec black"><?php esc_html_e("Get more storage now", 'backup-backup'); ?></a>
    </div>
</div>
<?php if (isset($global_warning)): ?>
<div class="bb-credit-text">By Backup & Migration [<a href="#" style="text-decoration: none;" id="bb-upload-fail-dismiss">X</a>]</div>
<?php endif ?>
</div>
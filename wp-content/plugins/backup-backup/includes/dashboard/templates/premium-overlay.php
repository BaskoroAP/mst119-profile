<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) {
    exit;
  }

?>

<div class="overlay-premium">
  <div class="clocks_container">
    <div>
      <img src="<?php echo esc_url( $this->get_asset('images', 'clock.svg') ); ?>" alt="clocks-bg">
    </div>
    <div class="secondary-all">
      <?php echo wp_kses_post( BMI_COMMING_SOON_PRO ); ?>
    </div>
  </div>
</div>

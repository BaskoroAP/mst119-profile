<?php
/**
 * MU Plugin Loader: MST119 - Booking Pelatihan
 *
 * Loads the standard plugin from wp-content/plugins so it runs without manual activation.
 */

if (!defined('ABSPATH')) {
	exit;
}

$mst119_booking_plugin = WP_CONTENT_DIR . '/plugins/mst119-booking-pelatihan/mst119-booking-pelatihan.php';
if (file_exists($mst119_booking_plugin)) {
	require_once $mst119_booking_plugin;
}


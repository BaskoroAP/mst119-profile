<?php
/**
 * Plugin Name: MST119 - Booking Pelatihan
 * Description: Halaman "Booking Pelatihan" + form untuk menyimpan data booking ke database (WordPress).
 * Version: 1.0.0
 * Author: MST119
 */

if (!defined('ABSPATH')) {
	exit;
}

final class MST119_Booking_Pelatihan {
	public const CPT = 'mst_booking';
	public const OPTION_PAGE_ID = 'mst119_booking_page_id';
	public const OPTION_REPORT_PAGE_ID = 'mst119_booking_report_page_id';
	public const NONCE_ACTION = 'mst119_booking_submit';
	public const NONCE_NAME = 'mst119_booking_nonce';

	public function hooks(): void {
		add_action('init', [$this, 'register_post_type']);
		add_action('init', [$this, 'maybe_bootstrap_pages'], 20);
		add_shortcode('mst119_booking_form', [$this, 'shortcode_booking_form']);
		add_shortcode('mst119_booking_report', [$this, 'shortcode_booking_report']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
		add_filter('wp_nav_menu_objects', [$this, 'rewrite_menu_links_to_booking'], 10, 2);
		add_filter('theme_mod_theme_options', [$this, 'rewrite_education_hub_notice_link']);

		add_action('admin_post_nopriv_mst119_booking_submit', [$this, 'handle_submit']);
		add_action('admin_post_mst119_booking_submit', [$this, 'handle_submit']);

		add_filter('manage_' . self::CPT . '_posts_columns', [$this, 'admin_columns']);
		add_action('manage_' . self::CPT . '_posts_custom_column', [$this, 'admin_column_values'], 10, 2);
		add_filter('manage_edit-' . self::CPT . '_sortable_columns', [$this, 'admin_sortable_columns']);
		add_action('pre_get_posts', [$this, 'admin_sorting']);
	}

	public static function activate(): void {
		$self = new self();
		$self->register_post_type();
		flush_rewrite_rules();
		$self->ensure_pages_exist();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	public function register_post_type(): void {
		register_post_type(self::CPT, [
			'labels' => [
				'name' => 'Booking Pelatihan',
				'singular_name' => 'Booking Pelatihan',
				'add_new' => 'Tambah Booking',
				'add_new_item' => 'Tambah Booking Pelatihan',
				'edit_item' => 'Ubah Booking',
				'new_item' => 'Booking Baru',
				'view_item' => 'Lihat Booking',
				'search_items' => 'Cari Booking',
				'not_found' => 'Tidak ada booking',
				'not_found_in_trash' => 'Tidak ada booking di sampah',
				'menu_name' => 'Booking Pelatihan',
			],
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 25,
			'menu_icon' => 'dashicons-clipboard',
			'supports' => ['title'],
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'has_archive' => false,
		]);
	}

	public function maybe_bootstrap_pages(): void {
		// When used as an MU plugin loader, activation hooks won't run.
		// Ensure the Booking page exists, but keep it light (no flush_rewrite_rules).
		$this->ensure_pages_exist();
	}

	private function ensure_pages_exist(): void {
		$this->ensure_booking_page_exists();
		$this->ensure_report_page_exists();
	}

	private function ensure_booking_page_exists(): void {
		$existing_page_id = (int) get_option(self::OPTION_PAGE_ID, 0);
		if ($existing_page_id > 0 && get_post_status($existing_page_id)) {
			$this->maybe_add_booking_to_menu($existing_page_id);
			return;
		}

		$page = get_page_by_path('booking-pelatihan');
		if ($page instanceof WP_Post) {
			update_option(self::OPTION_PAGE_ID, (int) $page->ID, false);
			$this->maybe_add_booking_to_menu((int) $page->ID);
			return;
		}

		$page_id = wp_insert_post([
			'post_title' => 'Booking Pelatihan',
			'post_name' => 'booking-pelatihan',
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_content' => '[mst119_booking_form]',
		], true);

		if (!is_wp_error($page_id) && is_int($page_id)) {
			update_option(self::OPTION_PAGE_ID, (int) $page_id, false);
			$this->maybe_add_booking_to_menu((int) $page_id);
		}
	}

	private function ensure_report_page_exists(): void {
		$existing_page_id = (int) get_option(self::OPTION_REPORT_PAGE_ID, 0);
		if ($existing_page_id > 0 && get_post_status($existing_page_id)) {
			$this->maybe_add_report_to_menu($existing_page_id);
			return;
		}

		$page = get_page_by_path('laporan-booking-pelatihan');
		if ($page instanceof WP_Post) {
			update_option(self::OPTION_REPORT_PAGE_ID, (int) $page->ID, false);
			$this->maybe_add_report_to_menu((int) $page->ID);
			return;
		}

		$page_id = wp_insert_post([
			'post_title' => 'Laporan Booking Pelatihan',
			'post_name' => 'laporan-booking-pelatihan',
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_content' => '[mst119_booking_report]',
		], true);

		if (!is_wp_error($page_id) && is_int($page_id)) {
			update_option(self::OPTION_REPORT_PAGE_ID, (int) $page_id, false);
			$this->maybe_add_report_to_menu((int) $page_id);
		}
	}

	public function shortcode_booking_form(array $atts = []): string {
		$page_url = $this->booking_page_url();
		$action_url = esc_url(admin_url('admin-post.php'));
		$success = isset($_GET['mst_booking']) && $_GET['mst_booking'] === 'success';

		// Intentionally do not re-populate PII from query params (privacy).
		$old = [
			'email' => '',
			'nama' => '',
			'institusi' => '',
			'hp' => '',
			'alamat' => '',
			'pelatihan' => '',
			'kota' => '',
		];

		$error = isset($_GET['mst_booking']) && $_GET['mst_booking'] === 'error'
			? (isset($_GET['reason']) ? (string) wp_unslash($_GET['reason']) : 'Mohon periksa kembali data Anda.')
			: '';

		$pelatihan_options = [
			'ACLS' => 'ACLS',
			'BTCLS' => 'BTCLS',
			'PPGDON' => 'PPGDON',
			'KKMN' => 'KKMN',
			'BLS/First AID' => 'BLS/First AID',
		];

		$kota_suggestions = $this->get_city_suggestions();

		ob_start();
		?>
		<div class="mst119-booking-form">
			<?php if ($success): ?>
				<div class="mst119-booking-alert mst119-booking-success" role="status">
					Terima kasih. Booking pelatihan Anda sudah terkirim.
				</div>
			<?php endif; ?>

			<?php if ($error !== ''): ?>
				<div class="mst119-booking-alert mst119-booking-error" role="alert">
					<?php echo esc_html($error); ?>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo $action_url; ?>">
				<input type="hidden" name="action" value="mst119_booking_submit" />
				<?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>

				<p>
					<label for="mst119_email">Email aktif</label><br />
					<input id="mst119_email" name="mst119_email" type="email" required value="<?php echo esc_attr($old['email']); ?>" />
				</p>

				<p>
					<label for="mst119_nama">Nama lengkap</label><br />
					<input id="mst119_nama" name="mst119_nama" type="text" required value="<?php echo esc_attr($old['nama']); ?>" />
				</p>

				<p>
					<label for="mst119_institusi">Institusi bekerja</label><br />
					<input id="mst119_institusi" name="mst119_institusi" type="text" required value="<?php echo esc_attr($old['institusi']); ?>" />
				</p>

				<p>
					<label for="mst119_hp">No. handphone aktif (WA)</label><br />
					<input id="mst119_hp" name="mst119_hp" type="tel" required value="<?php echo esc_attr($old['hp']); ?>" />
				</p>

				<p>
					<label for="mst119_alamat">Alamat rumah</label><br />
					<textarea id="mst119_alamat" name="mst119_alamat" rows="3" required><?php echo esc_textarea($old['alamat']); ?></textarea>
				</p>

				<p>
					<label for="mst119_pelatihan">Pelatihan yang dicari</label><br />
					<?php $mst119_first_pelatihan = true; ?>
					<?php foreach ($pelatihan_options as $value => $label): ?>
						<label style="display:block; margin: 0 0 6px;">
							<input
								type="checkbox"
								name="mst119_pelatihan[]"
								value="<?php echo esc_attr($value); ?>"
								<?php echo $mst119_first_pelatihan ? 'required' : ''; ?>
							/>
							<?php echo esc_html($label); ?>
						</label>
						<?php $mst119_first_pelatihan = false; ?>
					<?php endforeach; ?>
					<small>Pilih satu atau lebih.</small>
				</p>

				<p>
					<label for="mst119_kota">Tempat pelaksanaan yang ingin diikuti</label><br />
					<input
						id="mst119_kota"
						name="mst119_kota"
						type="text"
						list="mst119_kota_list"
						placeholder="Request kota Anda"
						required
						value="<?php echo esc_attr($old['kota']); ?>"
					/>
					<datalist id="mst119_kota_list">
						<?php foreach ($kota_suggestions as $kota): ?>
							<option value="<?php echo esc_attr($kota); ?>"></option>
						<?php endforeach; ?>
					</datalist>
					<small>Anda bisa pilih dari rekomendasi atau isi sendiri.</small>
				</p>

				<p>
					<button type="submit">Submit Booking</button>
				</p>

				<?php if ($page_url): ?>
					<input type="hidden" name="mst119_return" value="<?php echo esc_url($page_url); ?>" />
				<?php endif; ?>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public function enqueue_assets(): void {
		if (!is_singular()) {
			return;
		}

		$post = get_post();
		if (!$post instanceof WP_Post) {
			return;
		}

		$content = (string) $post->post_content;
		if (!has_shortcode($content, 'mst119_booking_form') && !has_shortcode($content, 'mst119_booking_report')) {
			return;
		}

		$css = '
			.mst119-booking-form input[type="text"],
			.mst119-booking-form input[type="email"],
			.mst119-booking-form input[type="tel"],
			.mst119-booking-form select,
			.mst119-booking-form textarea { width: 100%; max-width: 520px; }
			.mst119-booking-report input[type="text"],
			.mst119-booking-report select { width: 100%; max-width: 520px; }
			.mst119-booking-alert { padding: 12px 14px; margin: 0 0 14px; border-radius: 4px; }
			.mst119-booking-success { background: #e7f7ee; border: 1px solid #bfe8cf; }
			.mst119-booking-error { background: #fdecec; border: 1px solid #f2b8b8; }
		';

		wp_register_style('mst119-booking-pelatihan', false, [], '1.0.0');
		wp_enqueue_style('mst119-booking-pelatihan');
		wp_add_inline_style('mst119-booking-pelatihan', $css);
	}

	public function handle_submit(): void {
		if (!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce((string) $_POST[self::NONCE_NAME], self::NONCE_ACTION)) {
			$this->redirect_back('error', 'Sesi form tidak valid. Silakan coba lagi.');
		}

		$email = isset($_POST['mst119_email']) ? sanitize_email((string) wp_unslash($_POST['mst119_email'])) : '';
		$nama = isset($_POST['mst119_nama']) ? sanitize_text_field((string) wp_unslash($_POST['mst119_nama'])) : '';
		$institusi = isset($_POST['mst119_institusi']) ? sanitize_text_field((string) wp_unslash($_POST['mst119_institusi'])) : '';
		$hp_raw = isset($_POST['mst119_hp']) ? (string) wp_unslash($_POST['mst119_hp']) : '';
		$alamat = isset($_POST['mst119_alamat']) ? sanitize_textarea_field((string) wp_unslash($_POST['mst119_alamat'])) : '';
		$pelatihan_raw = isset($_POST['mst119_pelatihan']) ? wp_unslash($_POST['mst119_pelatihan']) : [];
		$kota = isset($_POST['mst119_kota']) ? sanitize_text_field((string) wp_unslash($_POST['mst119_kota'])) : '';

		$hp = preg_replace('/[^0-9+]/', '', $hp_raw);

		$allowed_pelatihan = ['ACLS', 'BTCLS', 'PPGDON', 'KKMN', 'BLS/First AID'];
		$pelatihan_list = [];
		if (is_array($pelatihan_raw)) {
			foreach ($pelatihan_raw as $item) {
				$pelatihan_list[] = sanitize_text_field((string) $item);
			}
		}
		$pelatihan_list = array_values(array_unique(array_filter($pelatihan_list, static fn($v) => is_string($v) && $v !== '')));

		if ($email === '' || !is_email($email)) {
			$this->redirect_back('error', 'Email tidak valid.');
		}
		if ($nama === '' || $institusi === '' || $alamat === '' || $kota === '') {
			$this->redirect_back('error', 'Mohon lengkapi semua kolom.');
		}
		if ($hp === '' || strlen(preg_replace('/[^0-9]/', '', $hp)) < 8) {
			$this->redirect_back('error', 'No. handphone tidak valid.');
		}
		if ($pelatihan_list === []) {
			$this->redirect_back('error', 'Pilih minimal 1 pelatihan.');
		}
		foreach ($pelatihan_list as $p) {
			if (!in_array($p, $allowed_pelatihan, true)) {
				$this->redirect_back('error', 'Pilihan pelatihan tidak valid.');
			}
		}

		$title = sprintf('%s - %s', $nama, implode(', ', $pelatihan_list));
		$post_id = wp_insert_post([
			'post_type' => self::CPT,
			'post_status' => 'private',
			'post_title' => $title,
		], true);

		if (is_wp_error($post_id)) {
			$this->redirect_back('error', 'Gagal menyimpan booking. Silakan coba lagi.');
		}

		update_post_meta($post_id, 'mst119_email', $email);
		update_post_meta($post_id, 'mst119_nama', $nama);
		update_post_meta($post_id, 'mst119_institusi', $institusi);
		update_post_meta($post_id, 'mst119_hp', $hp);
		update_post_meta($post_id, 'mst119_alamat', $alamat);
		update_post_meta($post_id, 'mst119_pelatihan', $pelatihan_list);
		update_post_meta($post_id, 'mst119_pelatihan_text', implode(', ', $pelatihan_list));
		update_post_meta($post_id, 'mst119_kota', $kota);

		$this->redirect_back('success');
	}

	public function shortcode_booking_report(array $atts = []): string {
		$allow_public = defined('MST119_BOOKING_REPORT_PUBLIC') && MST119_BOOKING_REPORT_PUBLIC;

		if (!$allow_public && (!is_user_logged_in() || !current_user_can('manage_options'))) {
			return '<p>Akses ditolak.</p>';
		}

		$search = isset($_GET['q']) ? sanitize_text_field((string) wp_unslash($_GET['q'])) : '';
		$filter_pelatihan = isset($_GET['pelatihan']) ? sanitize_text_field((string) wp_unslash($_GET['pelatihan'])) : '';
		$filter_kota = isset($_GET['kota']) ? sanitize_text_field((string) wp_unslash($_GET['kota'])) : '';
		$filter_institusi = isset($_GET['institusi']) ? sanitize_text_field((string) wp_unslash($_GET['institusi'])) : '';

		$orderby = isset($_GET['orderby']) ? sanitize_key((string) wp_unslash($_GET['orderby'])) : 'date';
		$order = isset($_GET['order']) ? strtoupper(sanitize_key((string) wp_unslash($_GET['order']))) : 'DESC';

		$allowed_orderby = ['date', 'pelatihan', 'kota', 'institusi'];
		if (!in_array($orderby, $allowed_orderby, true)) {
			$orderby = 'date';
		}
		if ($order !== 'ASC' && $order !== 'DESC') {
			$order = 'DESC';
		}

		$paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
		$per_page = 25;

		$pelatihan_options = ['ACLS', 'BTCLS', 'PPGDON', 'KKMN', 'BLS/First AID'];

		$meta_query = [];
		if ($filter_pelatihan !== '') {
			$meta_query[] = [
				'key' => 'mst119_pelatihan',
				'value' => $filter_pelatihan,
				'compare' => 'LIKE',
			];
		}
		if ($filter_kota !== '') {
			$meta_query[] = [
				'key' => 'mst119_kota',
				'value' => $filter_kota,
				'compare' => 'LIKE',
			];
		}
		if ($filter_institusi !== '') {
			$meta_query[] = [
				'key' => 'mst119_institusi',
				'value' => $filter_institusi,
				'compare' => 'LIKE',
			];
		}

		$query_args = [
			'post_type' => self::CPT,
			'post_status' => ['private', 'publish'],
			'posts_per_page' => $per_page,
			'paged' => $paged,
			'order' => $order,
			'mst119_report' => 1,
		];
		if ($meta_query !== []) {
			$query_args['meta_query'] = $meta_query;
		}

		if ($orderby === 'date') {
			$query_args['orderby'] = 'date';
		} elseif ($orderby === 'kota') {
			$query_args['meta_key'] = 'mst119_kota';
			$query_args['orderby'] = 'meta_value';
		} elseif ($orderby === 'institusi') {
			$query_args['meta_key'] = 'mst119_institusi';
			$query_args['orderby'] = 'meta_value';
		} elseif ($orderby === 'pelatihan') {
			$query_args['meta_key'] = 'mst119_pelatihan_text';
			$query_args['orderby'] = 'meta_value';
		}

		$where_filter = function (string $where, WP_Query $q) use ($search): string {
			if (!$q->get('mst119_report') || $search === '') {
				return $where;
			}

			global $wpdb;
			$like = '%' . $wpdb->esc_like($search) . '%';
			$keys = ["mst119_email", "mst119_nama", "mst119_hp", "mst119_institusi", "mst119_kota", "mst119_pelatihan_text"];
			$keys_sql = "'" . implode("','", array_map('esc_sql', $keys)) . "'";

			$extra = $wpdb->prepare(
				" AND ( {$wpdb->posts}.post_title LIKE %s OR EXISTS (
					SELECT 1 FROM {$wpdb->postmeta} pm
					WHERE pm.post_id = {$wpdb->posts}.ID
						AND pm.meta_key IN ($keys_sql)
						AND pm.meta_value LIKE %s
				) )",
				$like,
				$like
			);

			return $where . $extra;
		};

		add_filter('posts_where', $where_filter, 10, 2);
		$q = new WP_Query($query_args);
		remove_filter('posts_where', $where_filter, 10);

		$page_url = get_permalink(get_queried_object_id());
		$page_url = is_string($page_url) ? $page_url : '';

		$kota_suggestions = $this->get_city_suggestions();
		$institusi_suggestions = $this->get_institusi_suggestions();

		$params = [
			'q' => $search,
			'pelatihan' => $filter_pelatihan,
			'kota' => $filter_kota,
			'institusi' => $filter_institusi,
			'orderby' => $orderby,
			'order' => $order,
		];
		$params = array_filter($params, static fn($v) => is_string($v) && $v !== '');

		$sort_url = function (string $key) use ($page_url, $params, $orderby, $order): string {
			$next_order = ($orderby === $key && $order === 'ASC') ? 'DESC' : 'ASC';
			$new = $params;
			$new['orderby'] = $key;
			$new['order'] = $next_order;
			$new['paged'] = 1;
			return $page_url ? add_query_arg($new, $page_url) : '';
		};

		$total_pages = (int) ($q->max_num_pages ?? 1);

		ob_start();
		?>
		<div class="mst119-booking-report">
			<form method="get" action="<?php echo esc_url($page_url); ?>" style="margin: 0 0 14px;">
				<p style="margin:0 0 10px;">
					<label for="mst119_s">Pencarian</label><br />
					<input id="mst119_s" type="text" name="q" value="<?php echo esc_attr($search); ?>" placeholder="Nama, email, institusi, kota..." />
				</p>

				<p style="margin:0 0 10px;">
					<label for="mst119_filter_pelatihan">Filter pelatihan</label><br />
					<select id="mst119_filter_pelatihan" name="pelatihan">
						<option value="">— Semua —</option>
						<?php foreach ($pelatihan_options as $opt): ?>
							<option value="<?php echo esc_attr($opt); ?>" <?php selected($filter_pelatihan, $opt); ?>><?php echo esc_html($opt); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p style="margin:0 0 10px;">
					<label for="mst119_filter_kota">Filter lokasi (kota)</label><br />
					<input id="mst119_filter_kota" type="text" name="kota" list="mst119_kota_list_report" value="<?php echo esc_attr($filter_kota); ?>" />
					<datalist id="mst119_kota_list_report">
						<?php foreach ($kota_suggestions as $kota): ?>
							<option value="<?php echo esc_attr($kota); ?>"></option>
						<?php endforeach; ?>
					</datalist>
				</p>

				<p style="margin:0 0 10px;">
					<label for="mst119_filter_institusi">Filter institusi</label><br />
					<input id="mst119_filter_institusi" type="text" name="institusi" list="mst119_institusi_list_report" value="<?php echo esc_attr($filter_institusi); ?>" />
					<datalist id="mst119_institusi_list_report">
						<?php foreach ($institusi_suggestions as $inst): ?>
							<option value="<?php echo esc_attr($inst); ?>"></option>
						<?php endforeach; ?>
					</datalist>
				</p>

				<p style="margin:0 0 10px;">
					<label for="mst119_orderby">Sorting</label><br />
					<select id="mst119_orderby" name="orderby">
						<option value="date" <?php selected($orderby, 'date'); ?>>Tanggal</option>
						<option value="pelatihan" <?php selected($orderby, 'pelatihan'); ?>>Jenis pelatihan</option>
						<option value="kota" <?php selected($orderby, 'kota'); ?>>Lokasi</option>
						<option value="institusi" <?php selected($orderby, 'institusi'); ?>>Institusi</option>
					</select>
					<select name="order">
						<option value="ASC" <?php selected($order, 'ASC'); ?>>ASC</option>
						<option value="DESC" <?php selected($order, 'DESC'); ?>>DESC</option>
					</select>
				</p>

				<?php foreach ($params as $k => $v): ?>
					<?php if (!in_array($k, ['q', 'pelatihan', 'kota', 'institusi', 'orderby', 'order'], true)): ?>
						<input type="hidden" name="<?php echo esc_attr($k); ?>" value="<?php echo esc_attr($v); ?>" />
					<?php endif; ?>
				<?php endforeach; ?>

				<p style="margin:0;">
					<button type="submit">Terapkan</button>
					<a href="<?php echo esc_url($page_url); ?>" style="margin-left:10px;">Reset</a>
				</p>
			</form>

			<div style="overflow:auto;">
				<table class="widefat striped" style="min-width: 920px;">
					<thead>
						<tr>
							<th><a href="<?php echo esc_url($sort_url('date')); ?>">Tanggal</a></th>
							<th>Nama</th>
							<th>Email</th>
							<th>HP (WA)</th>
							<th><a href="<?php echo esc_url($sort_url('institusi')); ?>">Institusi</a></th>
							<th><a href="<?php echo esc_url($sort_url('pelatihan')); ?>">Pelatihan</a></th>
							<th><a href="<?php echo esc_url($sort_url('kota')); ?>">Lokasi</a></th>
						</tr>
					</thead>
					<tbody>
						<?php if ($q->have_posts()): ?>
							<?php while ($q->have_posts()): $q->the_post(); ?>
								<?php
								$post_id = get_the_ID();
								$email = (string) get_post_meta($post_id, 'mst119_email', true);
								$nama = (string) get_post_meta($post_id, 'mst119_nama', true);
								$hp = (string) get_post_meta($post_id, 'mst119_hp', true);
								$institusi = (string) get_post_meta($post_id, 'mst119_institusi', true);
								$kota = (string) get_post_meta($post_id, 'mst119_kota', true);

								$pelatihan_text = (string) get_post_meta($post_id, 'mst119_pelatihan_text', true);
								if ($pelatihan_text === '') {
									$pel = get_post_meta($post_id, 'mst119_pelatihan', true);
									if (is_array($pel)) {
										$pelatihan_text = implode(', ', array_map('sanitize_text_field', $pel));
									} elseif (is_string($pel) && $pel !== '') {
										$pelatihan_text = $pel;
									}
									if ($pelatihan_text !== '') {
										update_post_meta($post_id, 'mst119_pelatihan_text', $pelatihan_text);
									}
								}
								?>
								<tr>
									<td><?php echo esc_html(get_the_date('Y-m-d H:i')); ?></td>
									<td><?php echo esc_html($nama); ?></td>
									<td><?php echo esc_html($email); ?></td>
									<td><?php echo esc_html($hp); ?></td>
									<td><?php echo esc_html($institusi); ?></td>
									<td><?php echo esc_html($pelatihan_text); ?></td>
									<td><?php echo esc_html($kota); ?></td>
								</tr>
							<?php endwhile; ?>
							<?php wp_reset_postdata(); ?>
						<?php else: ?>
							<tr><td colspan="7">Belum ada data.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php if ($total_pages > 1 && $page_url): ?>
				<div class="mst119-report-pagination" style="margin-top: 12px;">
					<?php
					$page_params = $params;
					$page_params['paged'] = '%#%';
					echo paginate_links([
						'base' => add_query_arg($page_params, $page_url),
						'format' => '',
						'current' => $paged,
						'total' => $total_pages,
					]);
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	private function booking_page_url(): string {
		$page_id = (int) get_option(self::OPTION_PAGE_ID, 0);
		if ($page_id > 0) {
			$url = get_permalink($page_id);
			return is_string($url) ? $url : '';
		}

		$page = get_page_by_path('booking-pelatihan');
		if ($page instanceof WP_Post) {
			$url = get_permalink($page->ID);
			return is_string($url) ? $url : '';
		}

		return '';
	}

	/**
	 * Rewrite menu links that still point to Google Docs/Forms into the Booking Pelatihan page.
	 *
	 * @param WP_Post[] $sorted_menu_items
	 * @return WP_Post[]
	 */
	public function rewrite_menu_links_to_booking(array $sorted_menu_items, $args): array {
		$booking_url = $this->booking_page_url();
		if ($booking_url === '') {
			return $sorted_menu_items;
		}

		foreach ($sorted_menu_items as $item) {
			if (!$item instanceof WP_Post) {
				continue;
			}

			$url = isset($item->url) ? (string) $item->url : '';
			$title = isset($item->title) ? (string) $item->title : '';

			if ($url === '') {
				continue;
			}

			$is_google = stripos($url, 'docs.google') !== false || stripos($url, 'google.com/forms') !== false;
			$is_booking_label = stripos($title, 'booking') !== false || stripos($title, 'pelatihan') !== false;

			if ($is_google && $is_booking_label) {
				$item->url = $booking_url;
			}
		}

		return $sorted_menu_items;
	}

	/**
	 * Education Hub has a "Notice URL" (theme option). If it still points to Google Docs/Forms,
	 * rewrite it to the Booking Pelatihan page.
	 *
	 * @param array<string,mixed>|mixed $value
	 * @return array<string,mixed>|mixed
	 */
	public function rewrite_education_hub_notice_link($value) {
		if (!is_array($value)) {
			return $value;
		}

		if (!isset($value['notice_link_url']) || !is_string($value['notice_link_url'])) {
			return $value;
		}

		$url = $value['notice_link_url'];
		$is_google = stripos($url, 'docs.google') !== false || stripos($url, 'google.com/forms') !== false;
		if (!$is_google) {
			return $value;
		}

		$booking_url = $this->booking_page_url();
		if ($booking_url === '') {
			return $value;
		}

		$value['notice_link_url'] = $booking_url;
		return $value;
	}

	/**
	 * @return string[]
	 */
	private function get_city_suggestions(): array {
		global $wpdb;

		// Distinct values for meta key mst119_kota from existing bookings.
		$meta_key = 'mst119_kota';
		$post_type = self::CPT;

		$sql = "
			SELECT DISTINCT pm.meta_value
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = %s
				AND p.post_type = %s
				AND p.post_status IN ('private','publish')
				AND pm.meta_value <> ''
			ORDER BY pm.meta_value ASC
			LIMIT 50
		";

		$rows = $wpdb->get_col($wpdb->prepare($sql, $meta_key, $post_type));
		if (!is_array($rows)) {
			return [];
		}

		$rows = array_map('sanitize_text_field', $rows);
		$rows = array_values(array_unique(array_filter($rows, static fn($v) => is_string($v) && $v !== '')));
		return $rows;
	}

	/**
	 * @return string[]
	 */
	private function get_institusi_suggestions(): array {
		global $wpdb;

		$meta_key = 'mst119_institusi';
		$post_type = self::CPT;

		$sql = "
			SELECT DISTINCT pm.meta_value
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key = %s
				AND p.post_type = %s
				AND p.post_status IN ('private','publish')
				AND pm.meta_value <> ''
			ORDER BY pm.meta_value ASC
			LIMIT 50
		";

		$rows = $wpdb->get_col($wpdb->prepare($sql, $meta_key, $post_type));
		if (!is_array($rows)) {
			return [];
		}

		$rows = array_map('sanitize_text_field', $rows);
		$rows = array_values(array_unique(array_filter($rows, static fn($v) => is_string($v) && $v !== '')));
		return $rows;
	}

	private function redirect_back(string $status, string $reason = ''): void {
		$return = isset($_POST['mst119_return']) ? esc_url_raw((string) wp_unslash($_POST['mst119_return'])) : '';
		$url = $return !== '' ? $return : (wp_get_referer() ?: home_url('/'));

		$args = ['mst_booking' => $status];
		if ($status === 'error' && $reason !== '') {
			$args['reason'] = $reason;
		}

		wp_safe_redirect(add_query_arg($args, $url));
		exit;
	}

	private function maybe_add_booking_to_menu(int $page_id): void {
		if ($page_id <= 0) {
			return;
		}

		$locations = get_nav_menu_locations();
		if (!is_array($locations) || $locations === []) {
			return;
		}

		$menu_id = 0;
		foreach (['quick-links', 'primary'] as $location) {
			if (!empty($locations[$location])) {
				$menu_id = (int) $locations[$location];
				break;
			}
		}
		if ($menu_id <= 0) {
			return;
		}

		$items = wp_get_nav_menu_items($menu_id);
		if (!is_array($items)) {
			return;
		}

		$already_exists = false;
		$registrasi_item_id = 0;

		foreach ($items as $item) {
			if (!$item instanceof WP_Post) {
				continue;
			}

			$title = (string) $item->title;
			if (stripos($title, 'registrasi') !== false) {
				$registrasi_item_id = (int) $item->ID;
			}

			if ((int) $item->object_id === $page_id) {
				$already_exists = true;
			}
		}

		if ($already_exists) {
			return;
		}

		wp_update_nav_menu_item($menu_id, 0, [
			'menu-item-title' => 'Booking Pelatihan',
			'menu-item-object' => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type' => 'post_type',
			'menu-item-status' => 'publish',
			'menu-item-parent-id' => $registrasi_item_id > 0 ? $registrasi_item_id : 0,
		]);
	}

	private function maybe_add_report_to_menu(int $page_id): void {
		if ($page_id <= 0) {
			return;
		}

		$locations = get_nav_menu_locations();
		if (!is_array($locations) || $locations === []) {
			return;
		}

		$menu_id = 0;
		foreach (['quick-links', 'primary'] as $location) {
			if (!empty($locations[$location])) {
				$menu_id = (int) $locations[$location];
				break;
			}
		}
		if ($menu_id <= 0) {
			return;
		}

		$items = wp_get_nav_menu_items($menu_id);
		if (!is_array($items)) {
			return;
		}

		$already_exists = false;
		$registrasi_item_id = 0;
		$booking_item_id = 0;
		$booking_page_id = (int) get_option(self::OPTION_PAGE_ID, 0);

		foreach ($items as $item) {
			if (!$item instanceof WP_Post) {
				continue;
			}

			$title = (string) $item->title;
			if (stripos($title, 'registrasi') !== false) {
				$registrasi_item_id = (int) $item->ID;
			}

			if ($booking_page_id > 0 && (int) $item->object_id === $booking_page_id) {
				$booking_item_id = (int) $item->ID;
			} elseif (stripos($title, 'booking') !== false && stripos($title, 'pelatihan') !== false) {
				$booking_item_id = (int) $item->ID;
			}

			if ((int) $item->object_id === $page_id) {
				$already_exists = true;
			}
		}

		if ($already_exists) {
			return;
		}

		$parent_id = 0;
		if ($booking_item_id > 0) {
			$parent_id = $booking_item_id;
		} elseif ($registrasi_item_id > 0) {
			$parent_id = $registrasi_item_id;
		}

		wp_update_nav_menu_item($menu_id, 0, [
			'menu-item-title' => 'Laporan Booking Pelatihan',
			'menu-item-object' => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type' => 'post_type',
			'menu-item-status' => 'publish',
			'menu-item-parent-id' => $parent_id,
		]);
	}

	/**
	 * @param array<string,string> $columns
	 * @return array<string,string>
	 */
	public function admin_columns(array $columns): array {
		$new = [];
		$new['cb'] = $columns['cb'] ?? '';
		$new['title'] = 'Nama / Pelatihan';
		$new['mst119_email'] = 'Email';
		$new['mst119_hp'] = 'HP (WA)';
		$new['mst119_institusi'] = 'Institusi';
		$new['mst119_kota'] = 'Kota';
		$new['date'] = $columns['date'] ?? 'Date';
		return $new;
	}

	public function admin_column_values(string $column, int $post_id): void {
		$meta_map = [
			'mst119_email' => 'mst119_email',
			'mst119_hp' => 'mst119_hp',
			'mst119_institusi' => 'mst119_institusi',
			'mst119_kota' => 'mst119_kota',
		];

		if (!isset($meta_map[$column])) {
			return;
		}

		$value = (string) get_post_meta($post_id, $meta_map[$column], true);
		echo esc_html($value);
	}

	/**
	 * @param array<string,string> $columns
	 * @return array<string,string>
	 */
	public function admin_sortable_columns(array $columns): array {
		$columns['mst119_kota'] = 'mst119_kota';
		return $columns;
	}

	public function admin_sorting(WP_Query $query): void {
		if (!is_admin() || !$query->is_main_query()) {
			return;
		}

		$post_type = $query->get('post_type');
		if ($post_type !== self::CPT) {
			return;
		}

		$orderby = $query->get('orderby');
		if ($orderby === 'mst119_kota') {
			$query->set('meta_key', 'mst119_kota');
			$query->set('orderby', 'meta_value');
		}
	}
}

$mst119_booking_pelatihan = new MST119_Booking_Pelatihan();
$mst119_booking_pelatihan->hooks();

register_activation_hook(__FILE__, ['MST119_Booking_Pelatihan', 'activate']);
register_deactivation_hook(__FILE__, ['MST119_Booking_Pelatihan', 'deactivate']);

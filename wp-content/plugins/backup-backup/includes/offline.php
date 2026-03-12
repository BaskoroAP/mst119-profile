<?php

  // Namespace
  namespace BMI\Plugin;
  use BMI\Plugin\Backup_Migration_Plugin as BMP;
  use BMI\Plugin\BMI_Logger as Logger;
  // Exit on direct access
  if (!defined('ABSPATH')) {
    exit;
  }

  /**
   * Offline Methods Manager
   */
  class BMI_Offline {

    public $ajaxInserted = false;

    /**
     * __construct - Initializer (loads offline modules)
     */
    function __construct() {
      add_action('bmi_ajax_offline', function($post=[]){
        if (BMI_DEBUG)
            Logger::error("FREE AJAX OFFLINE");
        require_once BMI_INCLUDES . '/ajax_offline.php';
        $ajaxoffline = new BMI_Ajax_Offline($post);
      });
      add_action('wp_ajax_bmip_keepalive', [&$this, 'initializeOfflineAjax']);
      add_action('wp_ajax_nopriv_bmip_keepalive', [&$this, 'initializeOfflineAjax']);

      // Handle Auth Handshake For M2M Connection (Ping server)
      add_action('wp_ajax_nopriv_bmip_auth_handshake', [&$this, 'bmip_handle_handshake_request']);
      add_action('wp_ajax_bmip_auth_handshake', [&$this, 'bmip_handle_handshake_request']);

      if (is_user_logged_in() && current_user_can('administrator')) {
        add_action('wp_ajax_backup_migration', [&$this, 'initializeOfflineAjax']);
      }

      add_filter('allowed_http_origins', function ($origins) {
        $origins[] = 'https://backupbliss.com';
        $origins[] = 'https://api.backupbliss.com';
        return $origins;
      });

      // $TBU = get_option('bmip_to_be_uploaded', false);
      // if ($TBU != false && (sizeof($TBU['current_upload']) > 0 || sizeof($TBU['queue']) > 0)) {
        
      // }

      add_action('admin_footer', [&$this, 'keepAliveJS']);

    }

    /**
     * initializeOfflineAjax - Initialized Offline handlers for Ajax
     *
     * @return void
     */
    public function initializeOfflineAjax() {

    
      // Check if the request comes from a logged-in admin (Browser context)
      // OR from the Ping Server (M2M context)
      $is_admin = current_user_can('manage_options') && check_ajax_referer('backup-migration-ajax', 'nonce', false);
      $is_ping_server = $this->verify_ping_server_request();

      if (!$is_admin && !$is_ping_server) {
          wp_send_json_error('Unauthorized access', 403);
          return;
      }
      // if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

        // Extend execution time
        if (BMP::isFunctionEnabled('headers_sent') && BMP::isFunctionEnabled('session_status')) {
          if (!headers_sent() && session_status() === PHP_SESSION_DISABLED) {
            if (BMP::isFunctionEnabled('ignore_user_abort')) @ignore_user_abort(true);
            if (BMP::isFunctionEnabled('set_time_limit')) @set_time_limit(16000);
            if (BMP::isFunctionEnabled('ini_set')) {
              @ini_set('max_execution_time', '259200');
              @ini_set('max_input_time', '259200');
            }
          }
        }

        if ((isset($_GET['token']) && ($_GET['token'] == 'bmip' || $_GET['token'] == 'bmi') && isset($_GET['f']))) {

          if (empty($_GET)) return;

          // Sanitize User Input
          $post = BMP::sanitize($_GET);          

        } else if ((isset($_POST['token']) && ($_POST['token'] == 'bmip' || $_POST['token'] == 'bmi') && isset($_POST['f']))) {

          if (empty($_POST)) return;

          // Sanitize User Input
          $post = BMP::sanitize($_POST);

        }

        if (!empty($post)) {
            do_action("bmi_ajax_offline", $post);
        }

        // Execution error due to time limit
        // register_shutdown_function([$this, 'execution_shutdown']);

      // }

    }

    /**
     * Verifies the Ping Server handshake.
     * @return bool true if the request is verified, otherwise it sends a JSON error response and exits.
    */
    private function verify_ping_server_request() {
        $stored_sk = get_option('bmi_sk_keepalive');
        if (!isset($_SERVER['CONTENT_TYPE']) || stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
            return false;
        }
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        $request_sk = sanitize_text_field( wp_unslash( isset($data['sk']) ? $data['sk'] : '' ) );

        if (empty($stored_sk) || empty($request_sk)) {
            return false;
        }

        // Constant-Time Comparison (Prevents Timing Attacks) when possible
        if (!hash_equals($stored_sk, $request_sk)) {
            return false;
        }

        return true;
    }

    public function keepAliveJS() {
      if ($this->ajaxInserted) return;

      ?>
      <script defer type="text/javascript" id="bmip-js-inline-remove-js">
        function objectToQueryString(obj){
          return Object.keys(obj).map(key => key + '=' + obj[key]).join('&');
        }

        function globalBMIKeepAlive() {
          let xhr = new XMLHttpRequest();
          let data = { action: "bmip_keepalive", token: "bmip", f: "refresh", nonce: "<?php echo esc_js( wp_create_nonce( 'backup-migration-ajax' ) ); ?>" };
          let url = '<?php echo esc_url_raw( admin_url("admin-ajax.php") ); ?>' + '?' + objectToQueryString(data);
          xhr.open('POST', url, true);
          xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
          xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
              let response;
              if (response = JSON.parse(xhr.responseText)) {
                if (typeof response.status != 'undefined' && response.status === 'success') {
                  //setTimeout(globalBMIKeepAlive, 3000);
                } else {
                  //setTimeout(globalBMIKeepAlive, 20000);
                }
              }
            }
          };

          xhr.send(JSON.stringify(data));
        }

        document.querySelector('#bmip-js-inline-remove-js').remove();
      </script>
      <?php

      $this->ajaxInserted = true;
    }

    function bmip_handle_handshake_request() {
        $incoming_sk = isset($_POST['sk']) ? sanitize_text_field($_POST['sk']) : '';
        $challenge   = isset($_POST['challenge']) ? sanitize_text_field($_POST['challenge']) : '';

        $stored_sk = get_option('bmi_sk_keepalive');

        if ( ! empty($stored_sk) && ! empty($incoming_sk) && hash_equals($stored_sk, $incoming_sk) ) {
            
            header('Content-Type: text/plain');
            echo esc_html( $challenge );
            exit;
            
        } else {
            header('HTTP/1.0 403 Forbidden');
            echo 'Invalid Handshake';
            exit;
        }
    }
  }

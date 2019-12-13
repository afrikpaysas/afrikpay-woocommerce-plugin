<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (isset ($_GET["id"]) && isset ($_GET["afrikpay"])) {
class WC_Gateway_Afrikpay extends WC_Payment_Gateway {}
?>
<form id="dataForm" action="<?php echo $_GET["urlafrikpay"]; ?>" method="post" target="_top">
<input type="hidden" name="quantity" value="<?php echo $_GET["quantity_1"]; ?>"/>
<input type="hidden" name="merchantid" value="<?php echo $_GET["merchantid"]; ?>" />
<input type="hidden" name="sessionid" value="<?php echo $_GET["sessionid"]; ?>" />
<input type="hidden" name="brand" value="Mon Panier" />
<input type="hidden" name="currency" value="952" /> 
<input type="hidden" name="amount" value="<?php echo $_GET["totalamount"] ?>" />
<input type="hidden" name="phonenumber" value="" />
<input type="hidden" name="purchaseref" value="<?php $json = json_decode($_GET["custom"], true); echo $json['order_id']; ?>" />
<input type="hidden" name="description" value="Description" />
<input type="hidden" name="accepturl" value="<?php echo $_GET["return"]; ?>" />
<input type="hidden" name="cancelurl" value="<?php echo $_GET["cancel_return"]; ?>" />
<input type="hidden" name="declineurl" value="<?php echo $_GET["notify_url"]; ?>" />
<input type="hidden" name="text" value="<?php echo $_GET["text"]; ?>" />
<input type="hidden" name="language" value="fr" /> 
<input type="hidden" name="autonly" value="no" />
</form>
<script type="text/javascript">
    document.getElementById('dataForm').submit(); // SUBMIT FORM
</script>
<?php

} else {
/**
 * WC_Gateway_Afrikpay class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_Afrikpay extends WC_Payment_Gateway {

	/** @var bool Whether or not logging is enabled */
	public static $log_enabled = false;

	/** @var WC_Logger Logger instance */
	public static $log = false;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'afrikpay';
		$this->has_fields         = false;
		$this->order_button_text  = __( 'Payer avec Afrikpay', 'woocommerce' );
		$this->method_title       = __( 'Afrikpay', 'woocommerce' );
		$this->method_description = sprintf( __( 'votre paiement en ligne en toute s&eacute;curit&eacute; avec Afrikpay', 'woocommerce' ), admin_url( 'admin.php?page=wc-status' ) );
		$this->supports           = array(
			'products',
			'refunds',
		);

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title          = $this->get_option( 'title' );
		$this->description    = $this->get_option( 'description' );
		$this->merchantid     = $this->get_option( 'merchantid' );
		$this->urlafrikpay = $this->get_option( 'urlafrikpay' );
		$this->debug          = 'yes' === $this->get_option( 'debug', 'no' );
		$this->identity_token = $this->get_option( 'identity_token' );

		self::$log_enabled    = $this->debug;

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'capture_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'capture_payment' ) );

		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = 'no';
		}
	}

	/**
	 * Logging method.
	 *
	 * @param string $message Log message.
	 * @param string $level   Optional. Default 'info'.
	 *     emergency|alert|critical|error|warning|notice|info|debug
	 */
	public static function log( $message, $level = 'info' ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}
			self::$log->log( $level, $message, array( 'source' => 'afrikpay' ) );
		}
	}

	/**
	 * Get gateway icon.
	 * @return string
	 */
	public function get_icon() {
		$icon_html = '';
		$icon      = (array) $this->get_icon_image( WC()->countries->get_base_country() );

		foreach ( $icon as $i ) {
			$icon_html .= '<img src="' . esc_attr( $i ) . '" alt="' . esc_attr__( 'Afrikpay acceptance mark', 'woocommerce' ) . '" />';
		}

		$icon_html .= sprintf( '<a href="%1$s" class="about_afrikpay" onclick="javascript:window.open(\'%1$s\',\'WIAfrikpay\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;">' . esc_attr__( "Creer un compte Afrikpay?", 'woocommerce' ) . '</a>', esc_url( $this->get_icon_url( WC()->countries->get_base_country() ) ) );

		return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
	}

	/**
	 * Get the Afrikpay Session ID
	 * @param  string $urlafrikpay merchantid
	 * @return string
	 */
	 public function get_session_id() {

		$sessionResult = @file_get_contents( $this->urlafrikpay  . "?merchantid=" . $this->merchantid  ); // 
		$result = explode( ':', $sessionResult ); // 
		if ( $result[0] == 'OK' ) { 
			$sessionId = $result[1]; 
		}


		return $sessionId;
	}

	protected function get_icon_url( $country ) {
		$url           = 'https://www.afrikpay.com/' . strtolower( $country );
		$home_counties = array( 'BE', 'CZ', 'DK', 'HU', 'IT', 'JP', 'NL', 'NO', 'ES', 'SE', 'TR', 'IN' );
		$countries     = array( 'DZ', 'AU', 'BH', 'BQ', 'BW', 'CA', 'CN', 'CW', 'FI', 'FR', 'DE', 'GR', 'HK', 'ID', 'JO', 'KE', 'KW', 'LU', 'MY', 'MA', 'OM', 'PH', 'PL', 'PT', 'QA', 'IE', 'RU', 'BL', 'SX', 'MF', 'SA', 'SG', 'SK', 'KR', 'SS', 'TW', 'TH', 'AE', 'GB', 'US', 'VN' );

		if ( in_array( $country, $home_counties ) ) {
			return  $url . '/webapps/mpp/home';
		} elseif ( in_array( $country, $countries ) ) {
			return $url . '/webapps/mpp/afrikpay-popup';
		} else {
			return "https://www.afrikpay.com/";
		}
	}

	/**
	 * Get Afrikpay images for a country.
	 *
	 * @param string $country Country code.
	 * @return array of image URLs
	 */
	protected function get_icon_image( $country ) {
		switch ( $country ) {
			default :
				$icon = WC_HTTPS::force_https_url( '../wp-content/plugins/afrikpay-payments-for-woocommerce/assets/images/afrikpay.png' );
			break;
		}
		return apply_filters( 'woocommerce_afrikpay_icon', $icon );
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 * @return bool
	 */
	public function is_valid_for_use() {
		return in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_afrikpay_supported_currencies', array( 'XAF' ) ) );
	}

	/**
	 * Admin Panel Options.
	 * - Options for bits like 'title' and availability on a country-by-country basis.
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
		if ( $this->is_valid_for_use() ) {
			parent::admin_options();
		} else {
			?>
			<div class="inline error"><p><strong><?php _e( 'Gateway disabled', 'woocommerce' ); ?></strong>: <?php _e( 'Afrikpay does not support your store currency.', 'woocommerce' ); ?></p></div>
			<?php
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	 
	public function init_form_fields() {
		$this->form_fields = include( 'settings-afrikpay.php' );
	}

	/**
	 * Get the transaction URL.
	 * @param  WC_Order $order
	 * @return string
	 */
	public function get_transaction_url( $order ) {
			$this->view_transaction_url = $this->urlafrikpay;
		return parent::get_transaction_url( $order );
	}

	/**
	 * Process the payment and return the result.
	 * @param  int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		include_once( 'class-wc-gateway-afrikpay-request.php' );

		$order          = wc_get_order( $order_id );
		$afrikpay_request = new WC_Gateway_Afrikpay_Request( $this );

		return array(
			'result'   => 'success',
			'redirect' => $afrikpay_request->get_request_url( $order ),
		);
	}

	/**
	 * Can the order be refunded via Afrikpay?
	 * @param  WC_Order $order
	 * @return bool
	 */
	public function can_refund_order( $order ) {
		return $order && $order->get_transaction_id();
	}

	/**
	 * Capture payment when the order is changed from on-hold to complete or processing
	 *
	 * @param  int $order_id
	 */
	public function capture_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( 'afrikpay' === $order->get_payment_method() && 'pending' === get_post_meta( $order->get_id(), '_afrikpay_status', true ) && $order->get_transaction_id() ) {

if(isset($_GET["status"])) {
if ($_GET["status"]=="OK") {

$status = $_GET["status"];
$purchaseref=$_GET["purchaseref"];
if (isset($_GET["amount"])) {
$amount=$_GET["amount"];
$currency=$_GET["currency"];
$status=$_GET["status"];
$clientid=$_GET["clientid"];
$cname=$_GET["cname"];
$mobile=$_GET["mobile"];
$paymentref=$_GET["paymentref"];
$payid=$_GET["payid"];
$gar=$_GET["gar"];
$date=$_GET["date"];
$time=$_GET["time"];
$ipaddr=$_GET["ipaddr"];
$error=$_GET["error"];
}}
			if ( ! empty( $status ) ) {
				switch ( $status ) {
					case 'OK' :
						$order->add_order_note( sprintf( __( "Le paiement s'est bien pass�: %1$s", 'woocommerce' ), $status ) );
						update_post_meta( $order->get_id(), '_afrikpay_status', $status );
						update_post_meta( $order->get_id(), '_transaction_id', $status );
					break;
					default :
						$order->add_order_note( sprintf( __( "Le paiement ne s'est pas bien pass�: %1$s", 'woocommerce' ), $status ) );
					break;
				
			}
		} }
	}}

	/**
	 * Load admin scripts.
	 *
	 * @since 3.3.0
	 */
	public function admin_scripts() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id: '';

		if ( 'woocommerce_page_wc-settings' !== $screen_id ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'woocommerce_afrikpay_admin', '../wp-content/plugins/afrikpay-payments-for-woocommerce/assets/js/afrikpay-admin' . $suffix . '.js', array(), WC_VERSION, true );
	}
}
}
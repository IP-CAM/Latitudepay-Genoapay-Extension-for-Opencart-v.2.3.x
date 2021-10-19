<?php
class ControllerExtensionPaymentLatitudePay extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('extension/payment/latitudepay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('latitudepay', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			// comment the below if you do not want to redirect outside latitudepay once options are saved
			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_sandbox'] = $this->language->get('text_sandbox');
		$data['text_production'] = $this->language->get('text_production');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_environment'] = $this->language->get('entry_environment');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_debug'] = $this->language->get('entry_debug');

		$data['entry_success_status'] = $this->language->get('entry_success_status');
		$data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$data['entry_refunded_status'] = $this->language->get('entry_refunded_status');
		$data['entry_partially_refunded_status'] = $this->language->get('entry_partially_refunded_status');

		$data['help_total'] = $this->language->get('help_total');
		$data['help_debug'] = $this->language->get('help_debug');

		$data['tab_settings'] = $this->language->get('tab_settings');
		$data['tab_order_status'] = $this->language->get('tab_order_status');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/latitudepay', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/latitudepay', 'token=' . $this->session->data['token'], true);
		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

		/**
		 * FORM FIELDS TAB 1
		 */

		if (isset($this->request->post['latitudepay_status'])) {
			$data['latitudepay_status'] = $this->request->post['latitudepay_status'];
		} else {
			$data['latitudepay_status'] = $this->config->get('latitudepay_status');
		}

		if (isset($this->request->post['latitudepay_sort_order'])) {
			$data['latitudepay_sort_order'] = $this->request->post['latitudepay_sort_order'];
		} else {
			$data['latitudepay_sort_order'] = $this->config->get('latitudepay_sort_order');
		}

		if (isset($this->request->post['latitudepay_environment'])) {
			$data['latitudepay_environment'] = $this->request->post['latitudepay_environment'];
		} else {
			$data['latitudepay_environment'] = $this->config->get('latitudepay_environment');
		}

		if (isset($this->request->post['latitudepay_production_api_key'])) {
			$data['latitudepay_production_api_key'] = $this->request->post['latitudepay_production_api_key'];
		} else {
			$data['latitudepay_production_api_key'] = $this->config->get('latitudepay_production_api_key');
		}

		if (isset($this->request->post['latitudepay_production_api_secret'])) {
			$data['latitudepay_production_api_secret'] = $this->request->post['latitudepay_production_api_secret'];
		} else {
			$data['latitudepay_production_api_secret'] = $this->config->get('latitudepay_production_api_secret');
		}

		if (isset($this->request->post['latitudepay_sandbox_api_key'])) {
			$data['latitudepay_sandbox_api_key'] = $this->request->post['latitudepay_sandbox_api_key'];
		} else {
			$data['latitudepay_sandbox_api_key'] = $this->config->get('latitudepay_sandbox_api_key');
		}

		if (isset($this->request->post['latitudepay_sandbox_api_secret'])) {
			$data['latitudepay_sandbox_api_secret'] = $this->request->post['latitudepay_sandbox_api_secret'];
		} else {
			$data['latitudepay_sandbox_api_secret'] = $this->config->get('latitudepay_sandbox_api_secret');
		}

		if (isset($this->request->post['latitudepay_minimum_total'])) {
			$data['latitudepay_minimum_total'] = $this->request->post['latitudepay_minimum_total'];
		} else {
			$data['latitudepay_minimum_total'] = $this->config->get('latitudepay_minimum_total');
		}

		if (isset($this->request->post['latitudepay_configuration_last_update'])) {
			$data['latitudepay_configuration_last_update'] = $this->request->post['latitudepay_configuration_last_update'];
		} else {
			$data['latitudepay_configuration_last_update'] = $this->config->get('latitudepay_configuration_last_update');
		}

		if (isset($this->request->post['latitudepay_debug'])) {
			$data['latitudepay_debug'] = $this->request->post['latitudepay_debug'];
		} else {
			$data['latitudepay_debug'] = $this->config->get('latitudepay_debug');
		}

		/**
		 * FORM FIELDS TAB 2
		 */

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['latitudepay_entry_success_status_id'])) {
			$data['latitudepay_entry_success_status_id'] = $this->request->post['latitudepay_entry_success_status_id'];
		} else {
			$data['latitudepay_entry_success_status_id'] = $this->config->get('latitudepay_entry_success_status_id');
		}

		if (isset($this->request->post['latitudepay_entry_pending_status_id'])) {
			$data['latitudepay_entry_pending_status_id'] = $this->request->post['latitudepay_entry_pending_status_id'];
		} else {
			$data['latitudepay_entry_pending_status_id'] = $this->config->get('latitudepay_entry_pending_status_id');
		}

		if (isset($this->request->post['latitudepay_entry_failed_status_id'])) {
			$data['latitudepay_entry_failed_status_id'] = $this->request->post['latitudepay_entry_failed_status_id'];
		} else {
			$data['latitudepay_entry_failed_status_id'] = $this->config->get('latitudepay_entry_failed_status_id');
		}

		if (isset($this->request->post['latitudepay_entry_refunded_status_id'])) {
			$data['latitudepay_entry_refunded_status_id'] = $this->request->post['latitudepay_entry_refunded_status_id'];
		} else {
			$data['latitudepay_entry_refunded_status_id'] = $this->config->get('latitudepay_entry_refunded_status_id');
		}

		if (isset($this->request->post['latitudepay_entry_partially_refunded_status_id'])) {
			$data['latitudepay_entry_partially_refunded_status_id'] = $this->request->post['latitudepay_entry_partially_refunded_status_id'];
		} else {
			$data['latitudepay_entry_partially_refunded_status_id'] = $this->config->get('latitudepay_entry_partially_refunded_status_id');
		}

		/**
		 * ERROR HANDLING
		 */

		if (isset($this->request->post['attention'])) {
			$data['attention'] = $this->request->post['attention'];
		} else {
			$data['attention'] = $this->config->get('attention');
		}

		if (isset($this->request->post['success'])) {
			$data['success'] = $this->request->post['success'];
		} else {
			$data['success'] = $this->config->get('success');
		}

		if (isset($this->request->post['error_warning'])) {
			$data['error_warning'] = $this->request->post['error_warning'];
		} else {
			$data['error_warning'] = $this->config->get('error_warning');
		}

		/**
		 * INITIALISE LATITUDEPAY
		 */

		$this->load->model('extension/payment/latitudepay');
		$this->setupLatitudePay();
		$this->getConfiguration();

		// update value immediately once saved otherwise need to exit and come back to page
		$data['latitudepay_configuration_last_update'] = $this->config->get('latitudepay_configuration_last_update');
		$data['latitudepay_minimum_total'] = $this->config->get('latitudepay_minimum_total');

		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/latitudepay', $data));
	}

	public function install() {
        if (!$this->user->hasPermission('modify', 'extension/extension/payment')) {
            return;
        }
		$this->load->model('extension/payment/latitudepay');
		$this->model_extension_payment_latitudepay->install();
		// add event hooks
		$this->load->model('extension/event');
		$this->model_extension_event->addEvent('latitudepay_product_snippet', 'catalog/view/product/product/before', 'extension/payment/latitudepay/product_snippet');
		// $this->model_extension_event->addEvent('latitudepay_cart_snippet', 'catalog/view/checkout/cart/before', 'extension/payment/latitudepay/cart_snippet');
		$this->model_extension_event->addEvent('latitudepay_refund_button', 'admin/view/sale/order_info/before', 'extension/payment/latitudepay/refund_button');
	}

	public function uninstall() {
        if (!$this->user->hasPermission('modify', 'extension/extension/payment')) {
            return;
        }
		// remove event hooks
		$this->load->model('extension/event');
		$this->model_extension_event->deleteEvent('latitudepay_product_snippet');
		// $this->model_extension_event->deleteEvent('latitudepay_cart_snippet');
		$this->model_extension_event->deleteEvent('latitudepay_refund_button');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/latitudepay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}

	public function refund_button(&$route, &$data, &$output) {
		// show refund message if available
		$refund_message = isset($this->session->data['refund_message']) ? $this->session->data['refund_message'] : null;
		if (!is_null($refund_message)){
			$data['footer'] = '<script>alert("'.$refund_message.'")</script>' . $data['footer'];
			$this->session->data['refund_message'] = null;
		}

		// show only if order_id is set, meaning inside individual order page
        if (isset($data['order_id'])) {
            $order = $this->model_sale_order->getOrder($data['order_id']);

			// show only if the payment_code is latitudepay
			if ($order['payment_code'] === "latitudepay"){
				$this->load->model('extension/payment/latitudepay');
				$latitudepay_order = $this->model_extension_payment_latitudepay->getOrder($data['order_id']);

				// show only if the latitudepay order was originally successful
				if ($latitudepay_order['status'] == 1){
					$total_amount = $this->currency->format($order['total'], $order['currency_code'], $order['currency_value'], false);
					$total_refunded = $this->model_extension_payment_latitudepay->getTotalRefunded($data['order_id']);
					$amount = $total_amount - $total_refunded;

					// show only if there is still an amount to be refunded
					if ($amount > 0){
						// prepend footer
						$data['footer'] = '
						<!-- LatitudePay Refund Button Start -->
						<script>
						var trs = document.evaluate("//tr[contains(., \"LatitudePay\")]", document, null, XPathResult.ANY_TYPE, null );
						var thisTr = trs.iterateNext();
						thisTr.insertAdjacentHTML("afterend","<td colspan=2 class=\"text-right\"><form id=\"form-refund\" method=\"POST\" action=\"'.$this->url->link('extension/payment/latitudepay/refund', 'token=' . $this->session->data['token'], true).'\">Refund Available $ <input name=\"refund-amount\" class=\"text-right\" type=\"text\" value=\"'.$amount.'\" /> <input name=\"refund-reason\" class=\"text-right\" type=\"text\" placeholder=\"Enter reason\" /> <a id=\"refund-submit-button\" class=\"btn btn-danger btn-xs\" onclick=\"submitRefund()\">Try Refund</a><input type=\"hidden\" name=\"order-id\" value=\"'.$data['order_id'].'\" /></form></td>");
						function submitRefund(){
							var refundForm = document.getElementById("form-refund");
							if (confirm("Do you want to proceed with this refund?")) {
								refundForm.submit();
							}
						}
						</script>
						<!-- LatitudePay Refund Button End -->
						' . $data['footer'];
					}
				}
			}
		}
	}

	private function redirectToOrderPage($refund_message){
		$this->session->data['refund_message'] = $refund_message;
		$this->response->redirect($this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $this->request->post['order-id'], true));
	}

	public function refund(){
		$this->load->model('sale/order');
		$this->load->model('localisation/country');
		$this->load->model('extension/payment/latitudepay');

		$refund_amount = $this->request->post['refund-amount'];
		$refund_reason = $this->request->post['refund-reason'];
		$order_id = $this->request->post['order-id'];
		$order = $this->model_extension_payment_latitudepay->getOrder($order_id);
		$transaction_token = $order['transaction_token'];
		$currency_code = $order['currency_code'];

		// obtain latitudepay details from database
		$this->api_key		= $this->config->get('latitudepay_environment') ? $this->config->get('latitudepay_production_api_key') : $this->config->get('latitudepay_sandbox_api_key');
		$this->api_secret	= $this->config->get('latitudepay_environment') ? $this->config->get('latitudepay_production_api_secret') : $this->config->get('latitudepay_sandbox_api_secret');
		$this->requestUrl	= $this->config->get('latitudepay_environment') ? "https://api.latitudepay.com" : "https://api.uat.latitudepay.com";
		$this->contentType	= "application/com.latitudepay.ecom-v3.1+json";

		// request authToken
		$response = $this->requestAuthToken();

		// handle authToken error
		if (isset($response->error)){
			$refund_message = 'Error obtaining Authorisation Token from LatitudePay: '.$response->error;
			$this->model_extension_payment_latitudepay->log($refund_message);
			$this->redirectToOrderPage($refund_message);
		}

		if (!isset($response->authToken)){
			$refund_message = 'Critical Error obtaining Authorisation Token from LatitudePay.';
			$this->model_extension_payment_latitudepay->log($refund_message);
			$this->model_extension_payment_latitudepay->log($response);
			$this->redirectToOrderPage($refund_message);
		} else {
			$this->authToken = $response->authToken;
		}

		// process refund
		$response = $this->onlineRefund($order_id, $refund_amount, $refund_reason, $transaction_token, $currency_code);

		// handle refund error
		if (isset($response->error)){
			$refund_message = 'Error refunding order using LatitudePay: '.$response->error;
			$this->model_extension_payment_latitudepay->log($refund_message);
			$this->redirectToOrderPage($refund_message);
		}

		// if refundId does not exist, exit
		if (!isset($response->refundId)){
			$refund_message = 'Critical Error refunding order using LatitudePay.';
			$this->model_extension_payment_latitudepay->log($refund_message);
			$this->model_extension_payment_latitudepay->log($response);
			$this->redirectToOrderPage($refund_message);
		}

		// refund success, add to custom refund order table
		$refund_message = 'Refund of '.$this->currency->format($refund_amount, $currency_code).' was successful via LatitudePay. Refund ID: '.$response->refundId.'. Date: '.$response->refundDate.'.';
		$this->model_extension_payment_latitudepay->log($refund_message);
		$this->model_extension_payment_latitudepay->addLatitudePayRefundOrder($order_id, $refund_amount, $response->refundId, $response->refundDate, $response->reference, $response->commissionAmount);

		// check if there's any amount that can be refunded
		$total_refunded = $this->model_extension_payment_latitudepay->getTotalRefunded($order_id);
		$amount = $order['total'] - $total_refunded;

		// if there's nothing left, mark it as fully refunded
		if ($amount = 0){
			$order_status_id = $this->config->get('latitudepay_entry_refunded_status_id');
		} else {
			// otherwise mark it as partially refunded
			$order_status_id = $this->config->get('latitudepay_entry_partially_refunded_status_id');
		}
		
		// add to order history and redirect
		$this->model_extension_payment_latitudepay->addOrderHistory($order_id, $order_status_id, $refund_message);
		$this->redirectToOrderPage($refund_message);
	}

	protected function onlineRefund($order_id, $amount, $reason, $transaction_token, $currency){
		$jsonBody = '
		{
			"amount":
			{
				"amount":'.$amount.',
				"currency":"'.$currency.'"
			},
			"reason":"'.$reason.'",
			"reference":"'.$order_id.'"
		}
		';

		$phpObject = json_decode($jsonBody,true);

		$cleanJson = $this->stringifyNestedArray($phpObject, '');
		$cleanJson = str_replace(" ", "", $cleanJson);
		$cleanJson = json_encode($cleanJson);
		$cleanJson = substr($cleanJson,1,-1);

		# Make JSON for body query only
		$json = json_encode($phpObject,true);
		$this->model_extension_payment_latitudepay->log("$json");
		# END
		
		$base64 = base64_encode($cleanJson);
		$hash = hash_hmac('sha256', $base64, $this->api_secret);
		
		$url = "$this->requestUrl/v3/sale/".$transaction_token."/refund?signature=$hash";
		$this->model_extension_payment_latitudepay->log($url);
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $this->authToken,
			'Accept: ' . $this->contentType,
			'Content-Type: ' . $this->contentType,
			'X-Idempotency-Key: ' . uniqid()
		]);
		$response = curl_exec($curl);
		curl_close($curl);
		$response = json_decode($response);
		return $response;
	}

	private function setupLatitudePay(){
		$this->api_key		= $this->config->get('latitudepay_environment') ? $this->config->get('latitudepay_production_api_key') : $this->config->get('latitudepay_sandbox_api_key');
		$this->api_secret	= $this->config->get('latitudepay_environment') ? $this->config->get('latitudepay_production_api_secret') : $this->config->get('latitudepay_sandbox_api_secret');
		$this->requestUrl	= $this->config->get('latitudepay_environment') ? "https://api.latitudepay.com" : "https://api.uat.latitudepay.com";
		$this->contentType	= "application/com.latitudepay.ecom-v3.1+json";
	}

	private function getConfiguration(){
		// check if configuration needs to be updated
		if ($this->config->get('latitudepay_configuration_last_update')){
			$last_update = $this->config->get('latitudepay_configuration_last_update');
		} else {
			$last_update = "2021-01-01 00:00:00";
		}
		$now = date("Y-m-d H:i:s");
		$timeDifference = strtotime($now) - strtotime($last_update);
		if ($timeDifference < 86400) {
			return;
		}
		
		// request authToken
		$response = $this->requestAuthToken();

		// handle authToken error
		if (isset($response->error)){
			$this->model_extension_payment_latitudepay->log('Error obtaining Authorisation Token from LatitudePay: '.$response->error);
			return;
		}

		if (!isset($response->authToken)){
			$this->model_extension_payment_latitudepay->log('Critical Error obtaining Authorisation Token from LatitudePay.');
			$this->model_extension_payment_latitudepay->log($response);
			return;
		} else {
			$this->authToken = $response->authToken;
		}

		// request configuration
		$response = $this->requestConfiguration();

		// handle configuration error
		if (isset($response->error)){
			$this->model_extension_payment_latitudepay->log('Error obtaining Configuration from LatitudePay: '.$response->error);
			return;
		}

		// if configuration exists, save then proceed
		if (!isset($response->minimumAmount)){
			$this->model_extension_payment_latitudepay->log('Critical Error obtaining Configuration from LatitudePay.');
			$this->model_extension_payment_latitudepay->log($response);
			return;
		} else {
			$this->configurationLastUpdate		= date("Y-m-d H:i:s");
			$this->configurationMessage			= $response->description;
			$this->configurationMinimumAmount	= $response->minimumAmount;
			$this->configurationMaximumAmount	= $response->maximumAmount;

			// update database directly since .tpl is readonly and doesn't save the value
			$this->model_setting_setting->editSettingValue('latitudepay', 'latitudepay_minimum_total', $this->configurationMinimumAmount,  $this->config->get('config_store_id'));
			$this->model_setting_setting->editSettingValue('latitudepay', 'latitudepay_configuration_last_update', $this->configurationLastUpdate,  $this->config->get('config_store_id'));
		}
	}

	private function requestConfiguration(){
		$url = "$this->requestUrl/v3/configuration";
		$this->model_extension_payment_latitudepay->log($url);
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Authorization: Bearer ' . $this->authToken,
			'Accept: ' . $this->contentType,
			'Content-Type: ' . $this->contentType,
		]);
		$response = curl_exec($curl);
		curl_close($curl);
		$response = json_decode($response);
		return $response;
	}

	private function requestAuthToken(){
		$url = "$this->requestUrl/v3/token";
		$this->model_extension_payment_latitudepay->log($url);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_USERPWD, $this->api_key . ":" . $this->api_secret);  
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Accept: ' . $this->contentType,
			'Content-Type: ' . $this->contentType
		]);
		$response = curl_exec($curl);
		curl_close($curl);
		$response = json_decode($response);
		return $response;
	}

	/**
	 * Go through key/value of array to strip json for lpay signing mechanism
	 */
	private function stringifyNestedArray($phpArrayObject, $cleanJson){
		if (is_array($phpArrayObject)){
			foreach($phpArrayObject as $k=>$v){
				if (is_array($v)){
					if(!is_numeric($k)){ # handle numeric keys that appear due to key/value mismatch from json <-> php object
						$cleanJson .= $k;
					}
					$cleanJson = $this->stringifyNestedArray($v, $cleanJson);
				} else {
					if(!is_numeric($k)){ # handle numeric keys that appear due to key/value mismatch from json <-> php object
						if (is_bool($v)){
							$v = $v ? 'true' : 'false';
						}
						$cleanJson .= $k.$v;
					}
				}
			}
		}
		return $cleanJson;
	}

}

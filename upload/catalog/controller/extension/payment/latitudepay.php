<?php
class ControllerExtensionPaymentLatitudePay extends Controller {
	public function index() {
		// does not need to be validated with AUD because getMethod from model makes the payment method disabled when not in AUD anyway

		// get order info from checkout session
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$checkout_amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		// enable latitudepay checkout snippet
		$data['checkout_snippet'] = '<img src="https://images.latitudepayapps.com/v2/snippet.svg?amount='.$checkout_amount.'&services=LPAY&style=checkout" /><script src="https://images.latitudepayapps.com/v2/util.js"></script>';

		$data['latitudepay_submit'] = $this->url->link('extension/payment/latitudepay/send', '', true);
		return $this->load->view('extension/payment/latitudepay', $data);
	}

	/**
	 * Enable PDP snippet using Event called in admin/controller
	 */
    public function product_snippet(&$route, &$data, &$output) {
		$currencies = array(
			'AUD'
		);
		if (in_array(strtoupper($this->session->data['currency']), $currencies)) {
			// get product info
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
			$product_amount = $this->tax->calculate(($data['special'] ? $product_info['special']  : $product_info['price']), $product_info['tax_class_id'], $this->config->get('config_tax'));

			// onsite messaging
			$product_snippet = '<img style=\"max-width: 100%\" src=\"https://images.latitudepayapps.com/v2/snippet.svg?amount='.$product_amount.'&services=LPAY&style=default\" />';

			// prepend footer
			$data['footer'] = '
			<!-- LatitudePay PDP Snippet Start -->
			<script type="text/javascript">document.getElementById("product").insertAdjacentHTML("beforebegin","'.$product_snippet.'<br><br>");</script>
			<script src="https://images.latitudepayapps.com/v2/util.js"></script>
			<!-- LatitudePay PDP Snippet End -->
			' . $data['footer'];
		}
	}

	/**
	 * Enable cart snippet using Event called in admin/controller
	 */
    public function cart_snippet(&$route, &$data, &$output) {
		$currencies = array(
			'AUD'
		);
		if (in_array(strtoupper($this->session->data['currency']), $currencies)) {
			// get order info from checkout session
			$cart_amount = $this->cart->getTotal();
			
			// onsite messaging
			$cart_snippet = '<img style=\"max-width: 100%\" src=\"https://images.latitudepayapps.com/snippet.svg?amount='.$cart_amount.'\" />';
			// prepend footer
			$data['footer'] = '
			<!-- LatitudePay PDP Snippet Start -->
			<script type="text/javascript">document.getElementById("accordion").insertAdjacentHTML("afterend","'.$cart_snippet.'");</script>
			<script src="https://images.latitudepayapps.com/util.js"></script>
			<!-- LatitudePay PDP Snippet End -->
			' . $data['footer'];
		}
	}

	/**
	 * The function executed when checking out with LatitudePay
	 */
	public function send() {
		$this->load->model('checkout/order');
		$this->load->model('localisation/country');
		$this->load->model('extension/payment/latitudepay');

		// obtain latitudepay details from database
		$this->api_key		= $this->config->get('latitudepay_environment') ? $this->config->get('latitudepay_production_api_key') : $this->config->get('latitudepay_sandbox_api_key');
		$this->api_secret	= $this->config->get('latitudepay_environment') ? $this->config->get('latitudepay_production_api_secret') : $this->config->get('latitudepay_sandbox_api_secret');
		$this->requestUrl	= $this->config->get('latitudepay_environment') ? "https://api.latitudepay.com" : "https://api.uat.latitudepay.com";
		$this->contentType	= "application/com.latitudepay.ecom-v3.1+json";

		// check configuration
		$this->getConfiguration();

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$country_info = $this->model_localisation_country->getCountry($order_info['payment_country_id']);

		$order_total = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		// if below minimum total of $20, return error
		if($order_total < $this->config->get('latitudepay_minimum_total')){
			$this->session->data['error'] = 'Minimum total for LatitudePay is $'.$this->config->get('latitudepay_minimum_total').'. Your order is only: $'.$order_total;
			$this->model_extension_payment_latitudepay->log($this->session->data['error']);
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		// request authToken
		$response = $this->requestAuthToken();

		// handle authToken error
		if (isset($response->error)){
			$this->session->data['error'] = 'Error obtaining Authorisation Token from LatitudePay: '.$response->error;
			$this->model_extension_payment_latitudepay->log($this->session->data['error']);
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		if (!isset($response->authToken)){
			$this->session->data['error'] = 'Critical Error obtaining Authorisation Token from LatitudePay.';
			$this->model_extension_payment_latitudepay->log($this->session->data['error']);
			$this->model_extension_payment_latitudepay->log($response);
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		} else {
			$this->authToken = $response->authToken;
		}

		// process sale
		$response = $this->onlineSale($order_info);

		// handle sale error
		if (isset($response->error)){
			$this->session->data['error'] = 'Error obtaining Transaction Token from LatitudePay: '.$response->error;
			$this->model_extension_payment_latitudepay->log($this->session->data['error']);
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		// if transaction token does not exist, exit
		if (!isset($response->token)){
			$this->session->data['error'] = 'Critical Error obtaining Transaction Token from LatitudePay.';
			$this->model_extension_payment_latitudepay->log($this->session->data['error']);
			$this->model_extension_payment_latitudepay->log($response);
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		// otherwise add to custom order table and redirect
		$this->model_extension_payment_latitudepay->addLatitudePayOrder($order_info['order_id'], $response->token, $response->reference, $order_total, 0, $order_info['currency_code']);
		$this->response->redirect($response->paymentUrl);
	}

	public function fail_webhook() {
		$this->load->model('extension/payment/latitudepay');
		$this->model_extension_payment_latitudepay->log('fail_webhook');

		$order_id = isset($_REQUEST['reference']) ? $_REQUEST['reference'] : null;
		if (is_null($order_id)){
			$this->session->data['error'] = 'Illegal access.';
		} else {
			$this->model_extension_payment_latitudepay->updateOrderStatus($order_id, $this->config->get('latitudepay_entry_failed_status_id'));
			$this->session->data['error'] = 'Your purchase order has been cancelled.';
		}
		$this->response->redirect($this->url->link('checkout/checkout', '', true));
	}

	public function success_webhook() {
		$this->load->model('checkout/order');
		$this->load->model('extension/payment/latitudepay');
		$this->model_extension_payment_latitudepay->log('success_webhook');

		$order_id = isset($this->request->get['reference']) ? $this->request->get['reference'] : null;
		if (is_null($order_id)){
			$this->session->data['error'] = 'Illegal access.';
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}

		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$this->model_extension_payment_latitudepay->log($url);

		if($this->verifyJsonApiResponse($url)){
			$order = $this->model_checkout_order->getOrder($order_id);
			if ($order) {
				// if order hasn't been completed, add it to order history and make it complete
				if ($order['order_status_id'] !== $this->config->get('latitudepay_entry_success_status_id')){
					$success_message = 'Payment of '.$this->currency->format($order['total'], $order['currency_code']).' was successful via LatitudePay. Transaction Token: '.$this->request->get['token'].'.';
					$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('latitudepay_entry_success_status_id'),$success_message);
					$this->model_extension_payment_latitudepay->updateLatitudePayOrderStatus($order_id, 1);
				}
				$this->response->redirect($this->url->link('checkout/success', '', true));
			} else {
				// if order does not exist, go back to checkout
				$this->session->data['error'] = 'Error occurred. Order does not exist. Please try again.';
				$this->model_extension_payment_latitudepay->log('Error occurred. Order does not exist. Please try again.');
				$this->response->redirect($this->url->link('checkout/checkout', '', true));
			}
		} else {
			// Investigate if signature mismatch happens
			$this->session->data['error'] = 'Signature mismatch. API Response has been tampered. Please investigate.';
			$this->model_extension_payment_latitudepay->log('Signature mismatch. API Response has been tampered. Please investigate.');
			$this->response->redirect($this->url->link('checkout/checkout', '', true));
		}
	}

	public function callback_webhook() {
		$this->load->model('checkout/order');
		$this->load->model('extension/payment/latitudepay');
		$this->model_extension_payment_latitudepay->log('callback_webhook');

		$order_id = isset($this->request->get['reference']) ? $this->request->get['reference'] : null;
		if (is_null($order_id))	return;

		$result = $_REQUEST['result'] ?? null;
		if (is_null($result)) return;
		if (($result != "COMPLETED")) return;

		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$this->model_extension_payment_latitudepay->log($url);

		if($this->verifyJsonApiResponse($url)){
			$order = $this->model_checkout_order->getOrder($order_id);
			if ($order) {
				// if order hasn't been completed, add it to order history and make it complete
				if ($order['order_status_id'] !== $this->config->get('latitudepay_entry_success_status_id')){
					$success_message = 'Payment of '.$this->currency->format($order['total'], $order['currency_code']).' was successful via LatitudePay Callback. Transaction Token: '.$this->request->get['token'].'.';
					$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('latitudepay_entry_success_status_id'),$success_message);
					$this->model_extension_payment_latitudepay->updateLatitudePayOrderStatus($order_id, 1);
				}
				return;
			} else {
				// if order does not exist, exit
				$this->model_extension_payment_latitudepay->log('Error occurred. Order does not exist. Please try again.');
				return;
			}
		} else {
			// Investigate if signature mismatch happens
			$this->model_extension_payment_latitudepay->log('Signature mismatch. API Response has been tampered. Please investigate.');
			return;
		}
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

	private function onlineSale($order_info){
		$originalJson = $this->jsonBuilderForSale($order_info);
		$phpObject = json_decode($originalJson,true);
	
		$cleanJson = $this->stringifyNestedArray($phpObject, '');
		$cleanJson = str_replace(" ", "", $cleanJson);
		$cleanJson = json_encode($cleanJson);
		$cleanJson = substr($cleanJson,1,-1);

		# Make JSON for body query only
		$json = json_encode($phpObject,true);
		$this->model_extension_payment_latitudepay->log($json);
		# END
		
		$base64 = base64_encode($cleanJson);
		$hash = hash_hmac('sha256', $base64, $this->api_secret);
		
		$url = "$this->requestUrl/v3/sale/online?signature=$hash";
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

	/**
	 * Build JSON body for SALE from order details
	 */
	private function jsonBuilderForSale($order_info){
		$product_items = array();
		foreach ($this->cart->getProducts() as $item) {
			$unit_price = $this->tax->calculate($item['price'], $item['tax_class_id'], $this->config->get('config_tax'));
			$product_items[] =
			'{
				"name": "'.$item['name'].'",
				"price": {
					"amount": '.$unit_price.',
					"currency": "'.$order_info['currency_code'].'"
				},
				"sku": "'.$item['model'].'",
				"quantity": '.$item['quantity'].',
				"taxIncluded": true
			}';
		}
		$comma_separated_products = implode(",", $product_items);

		$shippingMethod = $order_info['shipping_method'] ? $order_info['shipping_method'] : 'N/A';
		$shippingMethod = str_replace(array("\r\n", "\n", "\r"), '', $shippingMethod);
		$shippingCost = $order_info['shipping_method'] ? $this->session->data['shipping_method']['cost'] : 0;

		$taxAmount = 0;
        $taxes = $this->cart->getTaxes();
        foreach ($taxes as $id => $amount) {
            $taxAmount += $amount;
        }

		$jsonBody = '
		{
			"customer": {
				"mobileNumber": "'.$order_info['telephone'].'",
				"firstName": "'.$order_info['firstname'].'",
				"surname": "'.$order_info['lastname'].'",
				"email": "'.$order_info['email'].'",
				"address": {
				"addressLine1": "'.$order_info['payment_address_1'].'",
				"addressLine2": "'.$order_info['payment_address_2'].'",
				"suburb": "'.$order_info['payment_city'].'",
				"cityTown": "'.$order_info['payment_city'].'",
				"state": "'.$order_info['payment_zone'].'",
				"postcode": "'.$order_info['payment_postcode'].'",
				"countryCode": "'.$order_info['payment_iso_code_2'].'"
				},
				"dateOfBirth": "2000-12-31"
			},
			"shippingAddress": {
				"addressLine1": "'.$order_info['shipping_address_1'].'",
				"addressLine2": "'.$order_info['shipping_address_2'].'",
				"suburb": "'.$order_info['shipping_city'].'",
				"cityTown": "'.$order_info['shipping_city'].'",
				"state": "'.$order_info['shipping_zone'].'",
				"postcode": "'.$order_info['shipping_postcode'].'",
				"countryCode": "'.$order_info['shipping_iso_code_2'].'"
			},
			"billingAddress": {
				"addressLine1": "'.$order_info['payment_address_1'].'",
				"addressLine2": "'.$order_info['payment_address_2'].'",
				"suburb": "'.$order_info['payment_city'].'",
				"cityTown": "'.$order_info['payment_city'].'",
				"state": "'.$order_info['payment_zone'].'",
				"postcode": "'.$order_info['payment_postcode'].'",
				"countryCode": "'.$order_info['payment_iso_code_2'].'"
			},
			"products": [
				'.$comma_separated_products.'
			],
			"shippingLines": [
				{
				"carrier": "'.$shippingMethod.'",
				"price": {
					"amount": '.$shippingCost.',
					"currency": "'.$order_info['currency_code'].'"
				},
				"taxIncluded": false
				}
			],
			"taxAmount": {
				"amount": "'.$taxAmount.'",
				"currency": "'.$order_info['currency_code'].'"
			},
			"reference": "'.$order_info['order_id'].'",
			"totalAmount": {
				"amount": "'.$order_info['total'].'",
				"currency": "'.$order_info['currency_code'].'"
			},
			"returnUrls": {
				"successUrl": "'.$this->url->link('extension/payment/latitudepay/success_webhook', '', true).'",
				"failUrl": "'.$this->url->link('extension/payment/latitudepay/fail_webhook', '', true).'",
				"callbackUrl": "'.$this->url->link('extension/payment/latitudepay/callback_webhook', '', true).'"
			}
		}
		';
		return $jsonBody;
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

	/**
	 * Verify that the API responses are legitimate and have not been tampered with
	 */
	private function verifyJsonApiResponse($url){
		$token = $this->getValueFromString($url,'token');
		$reference = $this->getValueFromString($url,'reference');
		$message = $this->getValueFromString($url,'message');
		$result = $this->getValueFromString($url,'result');
		$signature = $this->getValueFromString($url,'signature');

		$finalString = 'token'.$token.'reference'.$reference.'message'.$message.'result'.$result;
		$finalString = str_replace(" ", "", $finalString);
		
		$base64 = base64_encode($finalString);
		$api_secret	= $this->config->get('latitudepay_environment') ? $this->config->get('latitudepay_production_api_secret') : $this->config->get('latitudepay_sandbox_api_secret');
		$hash = hash_hmac('sha256', $base64, $api_secret);

		if ($hash == $signature){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get parameters from URL
	 * https://stackoverflow.com/a/57011409
	 */
	private function getValueFromString(string $string, string $key) {
		parse_str(parse_url($string, PHP_URL_QUERY), $result);
		return isset($result[$key]) ? $result[$key] : null;
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

			// update database
			$this->model_extension_payment_latitudepay->editSettingValue('latitudepay', 'latitudepay_minimum_total', $this->configurationMinimumAmount,  $this->config->get('config_store_id'));
			$this->model_extension_payment_latitudepay->editSettingValue('latitudepay', 'latitudepay_configuration_last_update', $this->configurationLastUpdate,  $this->config->get('config_store_id'));
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
}

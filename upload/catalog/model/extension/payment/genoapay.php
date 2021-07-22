<?php

class ModelExtensionPaymentGenoapay extends Model {

	public function getMethod($address, $total) {
		$this->load->language('extension/payment/genoapay');
		$status = true;
		if ($this->config->get('genoapay_minimum_total') > $total) {
			$status = false;
		}
		$currencies = array(
			'NZD'
		);
		if (!in_array(strtoupper($this->session->data['currency']), $currencies)) {
			$status = false;
		}
		$method_data = array();
		if ($status) {
			$method_data = array(
				'code' => 'genoapay',
				'title' => 'Genoapay',
				'terms' => '',
				'sort_order' => $this->config->get('genoapay_sort_order')
			);
		}
		return $method_data;
	}

	/**
	 * Copied from /admin/model/setting/setting.php to update Genoapay configuration during checkout
	 */
	public function editSettingValue($code = '', $key = '', $value = '', $store_id = 0) {
		if (!is_array($value)) {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "', serialized = '0'  WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(json_encode($value)) . "', serialized = '1' WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
		}
	}

    /**
     * Update order status
     */
    public function updateOrderStatus($order_id, $order_status_id) {
        return $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
    }

	/**
	 * Add order into Genoapay order table
	 */
	public function addGenoapayOrder($order_id, $transaction_token, $reference, $total, $status, $currency_code) {
		return $this->db->query("INSERT INTO `" . DB_PREFIX . "genoapay_order` SET `order_id` = '" . (int)$order_id . "', `transaction_token` = '" . $this->db->escape($transaction_token) . "', `reference` = '" . $this->db->escape($reference) . "', `total` = '" . (double)$total . "', `status` = '" . (int)$status . "', `currency_code` = '" . $currency_code . "', `date_added` = now(), `date_modified` = now()");
	}

    /**
     * Update Genoapay order status
     */
    public function updateGenoapayOrderStatus($order_id, $status) {
        return $this->db->query("UPDATE `" . DB_PREFIX . "genoapay_order` SET `status` = '" . (int)$status . "', `date_modified` = NOW() WHERE `order_id` = '" . (int)$order_id . "'");
    }

	public function log($message) {
		if ($this->config->get('genoapay_debug')) {
			$log = new Log('genoapay_debug.log');
			if (is_array($message)) {
				$message = print_r($message, true); # Properly expand Arrays in logs.
			} elseif(is_object($message)) {
				/**
				 * Properly expand Objects in logs.
				 *
				 * Only use the Output Buffer if it's not currently active,
				 * or if it's empty.
				 *
				 * Note:	If the Output Buffer is active but empty, we write to it,
				 * 			read from it, then discard the contents while leaving it active.
				 *
				 * Otherwise, if $message is an Object, it will be logged as, for example:
				 * (foo Object)
				 */
				$ob_get_length = ob_get_length();
				if (!$ob_get_length) {
					if ($ob_get_length === false) {
						ob_start();
					}
					var_dump($message);
					$message = ob_get_contents();
					if ($ob_get_length === false) {
						ob_end_clean();
					} else {
						ob_clean();
					}
				} else {
					$message = '(' . get_class($message) . ' Object)';
				}
			}
			$log->write($message);
		}
	}
}

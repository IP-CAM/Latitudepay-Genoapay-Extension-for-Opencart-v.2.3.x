<?php

class ModelExtensionPaymentLatitudePay extends Model {

	public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "latitudepay_order` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`order_id` int(11) NOT NULL,
			`transaction_token` varchar(100) NOT NULL,
			`reference` varchar(100) NOT NULL,
			`total` DECIMAL( 20, 2 ) NOT NULL,
			`status` INT(1) DEFAULT NULL,
			`currency_code` CHAR(3) NOT NULL,
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "latitudepay_refund_order` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`order_id` int(11) NOT NULL,
			`refund_amount` decimal(20,2) NOT NULL,
			`refund_id` varchar(100) NOT NULL,
			`refund_date` varchar(255) NOT NULL,
			`reference` varchar(50) NOT NULL,
			`commission_amount` decimal(20,2) NOT NULL,
			`date_added` DATETIME NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");				
	}

	public function getOrder($order_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "latitudepay_order WHERE `order_id` = '$order_id'");
        return $query->row;
    }

	public function getOrderStatusOrder($order_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "latitudepay_order WHERE `order_id` = '$order_id'");
        return $query->row;
    }

	public function addLatitudePayRefundOrder($order_id, $refund_amount, $refund_id, $refund_date, $reference, $commission_amount) {
        $sql = "
        	INSERT INTO ".DB_PREFIX."latitudepay_refund_order(`order_id`, `refund_amount`, `refund_id`, `refund_date`, `reference`, `commission_amount`, `date_added`) VALUES('".$order_id."', '".$refund_amount."', '".$refund_id."', '".$refund_date."', '".$reference."', '".$commission_amount."', NOW());
        ";
        return $this->db->query($sql);
    }

    public function addOrderHistory($order_id, $order_status_id, $comment, $notify = 0) {
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
        $sql = "
        	INSERT INTO ".DB_PREFIX."order_history(`order_id`, `order_status_id`, `notify`, `comment`, `date_added`) VALUES('".$order_id."', '".$order_status_id."', '".$notify."', '".$comment."', NOW());
        ";
        return $this->db->query($sql);
    }

	public function getTotalRefunded($order_id) {
		$query = $this->db->query("SELECT SUM(`refund_amount`) AS `total` FROM `" . DB_PREFIX . "latitudepay_refund_order` WHERE `order_id` = '" . (int)$order_id . "'");
		return (double)$query->row['total'];
	}

	public function log($message) {
		if ($this->config->get('latitudepay_debug')) {
			$log = new Log('latitudepay_debug.log');
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

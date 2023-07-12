<?php
class ModelExtensionShippingGixOCRusPostCalc extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/gixocruspostcalc');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_gixocruspostcalc_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_gixocruspostcalc_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$quote_data = array();

		if ($status) {
			$weight = $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('shipping_gixocruspostcalc_weight_class_id'));
			$sub_total = $this->currency->convert($this->cart->getSubTotal(), $this->config->get('config_currency'), $this->config->get('shipping_gixocruspostcalc_currency'));

			if (isset($address['postcode']) && (utf8_strlen(trim($address['postcode'])) == 6)) {
				$filter = array(
					'weight' => $weight,
					'postcode' => $address['postcode']
				);

				$delivery = $this->postprice($filter);

				$filter = array(
					'weight'    => $weight,
					'valuation' => $sub_total,
					'postcode'  => $address['postcode']
				);

				$delivery_val = $this->postprice($filter);

				$shippings = array(
					'1' => 'simple_letter',
					'2' => 'reg_letter',
					'3' => 'val_letter',
					'4' => 'simple_parcel',
					'5' => 'reg_parcel',
					'6' => 'val_parcel',
					'7' => 'pkg',
					//'8' => 'ems',
					'8' => 'letter_reg_1class',
					'9' => 'letter_val_1class',
					'10' => 'reg_parcel1class',
					'11' => 'val_parcel1class',
					'12' => 'pkg_1class',
					'13' => 'pkg_val_1class'
				);

				if (!empty($delivery) && !empty($delivery_val)) {
					foreach ($shippings as $shipping) {
						$cost = 0;
						$insurance = 0;

						if (strpos($shipping, 'val_') !== false) {
							$cost = $delivery_val[$shipping];
							$insurance = $sub_total;
						} else {
							$cost = $delivery[$shipping];
						}
						$cost *= 1.5;
						if ((float)$cost) {
							$title = $this->language->get('text_' . $shipping);	

							if ($this->config->get('shipping_gixocruspostcalc_display_weight')) {
								$title .= ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point')) . ')';
							}

							if ($this->config->get('shipping_gixocruspostcalc_display_insurance') && $insurance) {
								$title .= ' (' . $this->language->get('text_insurance') . ' ' . $this->currency->format($insurance, $this->session->data['currency']) . ')';
							}
							if (strpos($title, 'осылка') !== false)
							{
									//echo " is posylka \n";
							
					
							$quote_data[$shipping] = array(
								'code'         => 'gixocruspostcalc.' . $shipping,
								'title'        => $title,
								'cost'         => $cost,
								'tax_class_id' => $this->config->get('shipping_gixocruspostcalc_tax_class_id'),
								'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_gixocruspostcalc_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
							);
							}
						}
					}
				}
			}
		}

		$method_data = array();

		if ($quote_data) {
			$title = '';

			if ($this->config->has('shipping_gixocruspostcalc_image') && !empty($this->config->get('shipping_gixocruspostcalc_image')) && ($this->config->get('shipping_gixocruspostcalc_image') !== 'no_image.png')) {
				if ($this->config->has('shipping_gixocruspostcalc_image_width') && !empty($this->config->get('shipping_gixocruspostcalc_image_width'))) {
					$image_width = $this->config->get('shipping_gixocruspostcalc_image_width');
				} else {
					$image_width = 50;
				}

				if ($this->config->has('shipping_gixocruspostcalc_image_height') && !empty($this->config->get('shipping_gixocruspostcalc_image_height'))) {
					$image_height = $this->config->get('shipping_gixocruspostcalc_image_height');
				} else {
					$image_height = 24;
				}

				$this->load->model('tool/image');

				$image = $this->model_tool_image->resize($this->config->get('shipping_gixocruspostcalc_image'), $image_width, $image_height);

				$title .= '<img src="' . $image . '" width="' . $image_width . '" height="' . $image_height . '" style="margin-right: 10px;" / >';
			}

			if (!empty($this->config->get('shipping_gixocruspostcalc_name')[$this->config->get('config_language_id')])) {
				$title .= trim($this->config->get('shipping_gixocruspostcalc_name')[$this->config->get('config_language_id')]);
			} else {
				$title .= $this->language->get('text_title');	
			}

			$method_data = array(
				'code'       => 'gixocruspostcalc',
				'title'      => $title,
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_gixocruspostcalc_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}

	private function postprice($data) {
		$response = array();
		$postprice = new Gixoc\PostPrice($data['weight']);

		if (isset($data['valuation'])) {
			$postprice->setValuation($data['valuation']);
		}

		if (isset($data['postcode'])) {
			$postprice->setFrom($this->config->get('shipping_gixocruspostcalc_postcode'));
			$postprice->setTo($data['postcode']);
			$response = $postprice->getRussia();
		}

		if (isset($response['error'])) {
			$error = 'GixOCRusPostCalc:: error = ' . $this->language->get('error_' . $response['error']);

			if (isset($response['data'])) {
				$error .= ', data = "' . json_encode($response['data']) . '"';
			}

			$this->log->write($error);
		} elseif (isset($response['success'])) {
			return $response['success'];
		} else {
			$error = 'GixOCRusPostCalc:: error = ' . $this->language->get('error_404');
			if (is_array($response)) {
				$error .= ', data = "' . json_encode($response) . '"';
			}
				
			$this->log->write($error);
		}

		return array();
	}
}
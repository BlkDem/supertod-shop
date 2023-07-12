<?php
class ControllerExtensionShippingnokianrussianpost extends Controller {
	private $error = array();
	private $version = '0.9.1';

	public function index() {
		$this->load->language('extension/shipping/nokianrussianpost');

		$this->document->setTitle($this->language->get('text_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_nokianrussianpost', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if ($this->request->post['apply']) {
				$this->response->redirect($this->url->link('extension/shipping/nokianrussianpost', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
			} else {
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
			}
		}

		if (isset($this->error['warning']))  {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['postcode'])) {
			$data['error_postcode'] = $this->error['postcode'];
		} else {
			$data['error_postcode'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/nokianrussianpost', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/nokianrussianpost', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['shipping_nokianrussianpost_image'])) {
			$data['shipping_nokianrussianpost_image'] = $this->request->post['shipping_nokianrussianpost_image'];
		} elseif ($this->config->has('shipping_nokianrussianpost_image')) {
			$data['shipping_nokianrussianpost_image'] = $this->config->get('shipping_nokianrussianpost_image');
		} elseif (is_file(DIR_IMAGE . 'catalog/gixoc/shipping/russianpost/russianpost_logo.png')) {
			$data['shipping_nokianrussianpost_image'] = 'catalog/gixoc/shipping/russianpost/russianpost_logo.png';
		} else {
			$data['shipping_nokianrussianpost_image'] = 'no_image.png';
		}

		$data['shipping_nokianrussianpost_thumb'] = $this->model_tool_image->resize($data['shipping_nokianrussianpost_image'], 100, 100);

		if (isset($this->request->post['shipping_nokianrussianpost_image_width'])) {
			$data['shipping_nokianrussianpost_image_width'] = $this->request->post['shipping_nokianrussianpost_image_width'];
		} elseif ($this->config->has('shipping_nokianrussianpost_image_width')) {
			$data['shipping_nokianrussianpost_image_width'] = $this->config->get('shipping_nokianrussianpost_image_width');
		} else {
			$data['shipping_nokianrussianpost_image_width'] = 50;
		}

		if (isset($this->request->post['shipping_nokianrussianpost_image_height'])) {
			$data['shipping_nokianrussianpost_image_height'] = $this->request->post['shipping_nokianrussianpost_image_height'];
		} elseif ($this->config->has('shipping_nokianrussianpost_image_height')) {
			$data['shipping_nokianrussianpost_image_height'] = $this->config->get('shipping_nokianrussianpost_image_height');
		} else {
			$data['shipping_nokianrussianpost_image_height'] = 24;
		}

		$data['shippings'] = array(
			'1' => 'simple_letter',
			'2' => 'reg_letter',
			'3' => 'val_letter',
			'4' => 'simple_parcel',
			'5' => 'reg_parcel',
			'6' => 'val_parcel',
			'7' => 'pkg',
			'8' => 'ems',
			'9' => 'letter_reg_1class',
			'10' => 'letter_val_1class',
			'11' => 'reg_parcel1class',
			'12' => 'val_parcel1class',
			'13' => 'pkg_1class',
			'14' => 'pkg_val_1class'
		);

		foreach ($data['shippings'] as $shipping) {
			$data['shippings_text'][$shipping] = $this->language->get('tab_' . $shipping);
		}

		if (isset($this->request->post['shipping_nokianrussianpost_delivery_services'])) {
			$data['shipping_nokianrussianpost_delivery_services'] = $this->request->post['shipping_nokianrussianpost_delivery_services'];
		} elseif ($this->config->get('shipping_nokianrussianpost_delivery_services')) {
			$data['shipping_nokianrussianpost_delivery_services'] = $this->config->get('shipping_nokianrussianpost_delivery_services');
		} else {
			$data['shipping_nokianrussianpost_delivery_services'] = array();
		}

		if (isset($this->request->post['shipping_nokianrussianpost_display_weight'])) {
			$data['shipping_nokianrussianpost_display_weight'] = $this->request->post['shipping_nokianrussianpost_display_weight'];
		} else {
			$data['shipping_nokianrussianpost_display_weight'] = $this->config->get('shipping_nokianrussianpost_display_weight');
		}

		if (isset($this->request->post['shipping_nokianrussianpost_display_insurance'])) {
			$data['shipping_nokianrussianpost_display_insurance'] = $this->request->post['shipping_nokianrussianpost_display_insurance'];
		} else {
			$data['shipping_nokianrussianpost_display_insurance'] = $this->config->get('shipping_nokianrussianpost_display_insurance');
		}

		if (isset($this->request->post['shipping_nokianrussianpost_weight_class_id'])) {
			$data['shipping_nokianrussianpost_weight_class_id'] = $this->request->post['shipping_nokianrussianpost_weight_class_id'];
		} elseif ($this->config->has('shipping_nokianrussianpost_weight_class_id')) {
			$data['shipping_nokianrussianpost_weight_class_id'] = $this->config->get('shipping_nokianrussianpost_weight_class_id');
		} else {
			$data['shipping_nokianrussianpost_weight_class_id'] = 2;
		}

		$this->load->model('localisation/weight_class');

		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		if (isset($this->request->post['shipping_nokianrussianpost_tax_class_id'])) {
			$data['shipping_nokianrussianpost_tax_class_id'] = $this->request->post['shipping_nokianrussianpost_tax_class_id'];
		} else {
			$data['shipping_nokianrussianpost_tax_class_id'] = $this->config->get('shipping_nokianrussianpost_tax_class_id');
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['shipping_nokianrussianpost_geo_zone_id'])) {
			$data['shipping_nokianrussianpost_geo_zone_id'] = $this->request->post['shipping_nokianrussianpost_geo_zone_id'];
		} else {
			$data['shipping_nokianrussianpost_geo_zone_id'] = $this->config->get('shipping_nokianrussianpost_geo_zone_id');
		}

		$this->load->model('localisation/currency');
		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		if (isset($data['currencies']['RUB']['code'])) {
			$currency_code = $data['currencies']['RUB']['code'];
		} else {
			$currency_code = 0;
		}

		if (isset($this->request->post['shipping_nokianrussianpost_currency'])) {
			$data['shipping_nokianrussianpost_currency'] = $this->request->post['shipping_nokianrussianpost_currency'];
		} elseif ($this->config->has('shipping_nokianrussianpost_currency')) {
			$data['shipping_nokianrussianpost_currency'] = $this->config->get('shipping_nokianrussianpost_currency');
		} else {
			$data['shipping_nokianrussianpost_currency'] = $currency_code;
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['language_id'] = $this->config->get('config_language_id');

		if (isset($this->request->post['shipping_nokianrussianpost_status'])) {
			$data['shipping_nokianrussianpost_status'] = $this->request->post['shipping_nokianrussianpost_status'];
		} else {
			$data['shipping_nokianrussianpost_status'] = $this->config->get('shipping_nokianrussianpost_status');
		}

		if (isset($this->request->post['shipping_nokianrussianpost_name'])) {
			$data['shipping_nokianrussianpost_name'] = $this->request->post['shipping_nokianrussianpost_name'];
		} else {
			$data['shipping_nokianrussianpost_name'] = $this->config->get('shipping_nokianrussianpost_name');
		}

		if (isset($this->request->post['shipping_nokianrussianpost_sort_order'])) {
			$data['shipping_nokianrussianpost_sort_order'] = $this->request->post['shipping_nokianrussianpost_sort_order'];
		} else {
			$data['shipping_nokianrussianpost_sort_order'] = $this->config->get('shipping_nokianrussianpost_sort_order');
		}

		if (isset($this->request->post['shipping_nokianrussianpost_nds'])) {
			$data['shipping_nokianrussianpost_nds'] = $this->request->post['shipping_nokianrussianpost_nds'];
		} elseif ($this->config->has('shipping_nokianrussianpost_nds')) {
			$data['shipping_nokianrussianpost_nds'] = $this->config->get('shipping_nokianrussianpost_nds');
		} else {
			$data['shipping_nokianrussianpost_nds'] =  1;
		}

		if (isset($this->request->post['shipping_nokianrussianpost_postcode'])) {
			$data['shipping_nokianrussianpost_postcode'] = $this->request->post['shipping_nokianrussianpost_postcode'];
		} else {
			$data['shipping_nokianrussianpost_postcode'] = $this->config->get('shipping_nokianrussianpost_postcode');
		}

		if ($this->checking() != $this->version) {
			$data['text_old_version'] = sprintf($this->language->get('text_old_version'), $this->version, $this->checking());
			$data['text_new_version'] = '';
		} else {
			$data['text_new_version'] = sprintf($this->language->get('text_new_version'), $this->version);
			$data['text_old_version'] = '';
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/nokianrussianpost', $data));
	}

	public function help() {
		$this->load->language('extension/shipping/nokianrussianpost');

		$json = array();

		// Check user has permission
		if ((!$this->user->hasPermission('modify', 'extension/shipping/nokianrussianpost')) || (!isset($this->request->post['key']))) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			if (isset($this->request->post['key']) && !empty($this->request->post['key'])) {
				$json['header'] = $this->language->get($this->request->post['key']);
				$json['success'] = $this->request->post['key'];

				$data['help_thanks'] = $this->load->view('extension/module/gixochelp/extension_shipping_nokianrussianpost/help_thanks', array());
				$data['email'] = $this->config->get('config_email');
				$json['header'] = $this->language->get($this->request->post['key']);
				$json['success'] = $this->load->view('extension/module/gixochelp/extension_shipping_nokianrussianpost/' . $this->request->post['key'], $data);
			} else {
				$json['error'] = $this->language->get('error_permission'); 
			}
		}

		$this->response->addHeader('Content-Type: application/json');

		$this->response->setOutput(json_encode($json));
	}

	private function checking(){
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, 'https://gixoc.ru/index.php?route=api/version&domain=' . $this->request->server['HTTP_HOST'] . '&module=ruspostcalc&version=' . $this->version);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl, CURLOPT_TIMEOUT, 5);

		$response = curl_exec($curl);

		curl_close($curl);

		if ($response) {
			$result = $this->db->escape(htmlspecialchars($response));

			if (isset($result['version'])) {
				return $result['version'];
			}
		}

		return $this->version;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/nokianrussianpost')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (utf8_strlen(trim($this->request->post['shipping_nokianrussianpost_postcode'])) !== 6) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}

		return !$this->error;
	}
}
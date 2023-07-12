<?php

class ControllerCheckoutBoxberry extends Controller
{
    public function selectIssuePoint()
    {
        $json = array();
        $issue_point_id = isset($this->request->get['issue_point_id']) ? $this->request->get['issue_point_id'] : 0;
        $prepaid = isset($this->request->get['prepaid']) ? $this->request->get['prepaid'] : 0;
        $json['skip'] = 1;
        $json['id'] = $issue_point_id;
        if ($issue_point_id) {
            $this->load->model('extension/shipping/boxberry');
            $issue_point = $this->model_extension_shipping_boxberry->getIssuePointById($issue_point_id, $prepaid);
            if ($issue_point) {
                $session_data = [
                    'boxberry_shipping_city_id' => $issue_point['CityCode'],
                    'boxberry_shipping_city_name' => $issue_point['CityName'],
                    'boxberry_shipping_issue_point_prepaid' => $prepaid ? 1 : 0,
                    'boxberry_shipping_issue_point_id' . ($prepaid ? '_prepaid' : '') => $issue_point_id,
                    'boxberry_shipping_addr1' => $issue_point['Address'],
                    'boxberry_shipping_addr2' => $issue_point['Phone'] . ', ' . (isset($issue_point['WorkSchedule'])
                            ? $issue_point['WorkSchedule'] : $issue_point['WorkShedule'])
                ];
                foreach ($session_data as $key => $value) {
                    $this->session->data[$key] = $value;
                }
                $json['city'] = $this->session->data['boxberry_shipping_city_name'];
                $json['addr1'] = $issue_point['AddressReduce'];
                $json['skip'] = 0;
            } else
                $json['id'] = 0;
        }
        $this->response->addHeader('Content-Type: application/json; charset=utf-8');
        $this->response->setOutput(json_encode($json));
    }

    public function getIssuePoint()
    {
        $this->load->language('checkout/checkout');
        $this->load->model('extension/shipping/boxberry');
        if ($data['quote'] = $this->{'model_extension_shipping_boxberry'}->getQuoteIssuePoint($this->session->data['shipping_address'])) {

             $this->response->setOutput($this->load->view('checkout/boxberry_issue_point', $data));
        }
    }

    public function guestShippingSave()
    {

        $this->load->language('checkout/checkout');

        $json = array();
        if ($this->customer->isLogged()) {
            $json['redirect'] = $this->url->link('checkout/checkout', '', true);
        }
        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock()
                && !$this->config->get('config_stock_checkout'))) {
            $json['redirect'] = $this->url->link('checkout/cart');
        }
        if (!$this->config->get('config_checkout_guest') || $this->config->get('config_customer_price') || $this->cart->hasDownload()) {
            $json['redirect'] = $this->url->link('checkout/checkout', '', true);
        }
        if (!$json) {
            $firstname = utf8_strlen(trim($this->request->post['firstname']));
            if (($firstname < 1) || ($firstname > 32)) {
                $json['error']['firstname'] = $this->language->get('error_firstname');
            }
            $lastname = utf8_strlen(trim($this->request->post['lastname']));
            if (($lastname < 1) || ($lastname > 32)) {
                $json['error']['lastname'] = $this->language->get('error_lastname');
            }
            $address_1 = utf8_strlen(trim($this->request->post['address_1']));
            if (($address_1 < 3) || ($address_1 > 128)) {
                $json['error']['address_1'] = $this->language->get('error_address_1');
            }
            $city = utf8_strlen(trim($this->request->post['city']));
            if (($city < 2) || ($city > 128)) {
                $json['error']['city'] = $this->language->get('error_city');
            }
            $this->load->model('localisation/country');
            $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);
            if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2
                    || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
                $json['error']['postcode'] = $this->language->get('error_postcode');
            }
            if ($this->request->post['country_id'] == '') {
                $json['error']['country'] = $this->language->get('error_country');
            }
            if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == ''
                || !is_numeric($this->request->post['zone_id'])) {
                $json['error']['zone'] = $this->language->get('error_zone');
            }
        }
        if (!$json) {
            $shipping_address = ['firstname', 'lastname', 'company', 'address_1', 'address_2', 'postcode', 'city', 'country_id', 'zone_id'];
            foreach ($shipping_address as $key) {
                $this->session->data['shipping_address'][$key] = $this->request->post[$key];
            }
            $this->load->model('localisation/country');
            $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);
            if ($country_info) {
                $this->session->data['shipping_address']['country'] = $country_info['name'];
                $this->session->data['shipping_address']['iso_code_2'] = $country_info['iso_code_2'];
                $this->session->data['shipping_address']['iso_code_3'] = $country_info['iso_code_3'];
                $this->session->data['shipping_address']['address_format'] = $country_info['address_format'];
            } else {
                $this->session->data['shipping_address']['country'] = '';
                $this->session->data['shipping_address']['iso_code_2'] = '';
                $this->session->data['shipping_address']['iso_code_3'] = '';
                $this->session->data['shipping_address']['address_format'] = '';
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}

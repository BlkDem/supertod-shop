<?php

use Boxberry\Client\Client;

if (!class_exists('Client')) {
    require_once DIR_SYSTEM . 'library/boxberry/autoload.php';
}

class ModelExtensionShippingBoxberry extends Model
{
    private $maxDimension = 250;
    private $area = '';

    public function getQuote($address)
    {

        if(isset($this->session->data['payment_methods'])){
            $point_id = trim(str_replace('#','',$this->session->data['shipping_address']['address_2']));

            $client = new Client();
            $this->load->model('boxberry/point');
            $client->setKey($this->config->get('shipping_boxberry_api_token'));
            $client->setApiUrl($this->config->get('shipping_boxberry_api_url'));
            $description = $client->getPointsDescription();
            $description->setCode($point_id);
            $description->setPhoto(0);

            try{
                $point = $client->execute($description);
                $areaArr = explode(' ',$point->getArea());
                $this->area = $areaArr[0];
            } catch (Exception $ex){

            }
        }

        $session = &$this->session;
        $unsetKeys = [
            'fail_city',
            'fail_zip',
            'fail_cost_courier_delivery',
            'fail_cost_issue_point',
            'fail_cost_courier_delivery_prepaid',
            'fail_cost_issue_point_prepaid',
            'fail_weight',
            'fail_size',
            'issue_point_id',
            'issue_point_id_prepaid'
        ];
        if ($this->simplecheckout == false) {
            foreach ($unsetKeys as $unsetKey) {
                unset($session->data['boxberry_shipping_' . $unsetKey]);
            }
        }

        $method_data = array();
        $cartItems = $this->cart->getProducts();
        $weights = [];
        $weights[] = 0;
        foreach ($cartItems as $cartItem) {
            $width = $this->length->convert($cartItem['width'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id'));
            $height = $this->length->convert($cartItem['height'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id'));
            $length = $this->length->convert($cartItem['length'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id'));
            $weights[] = $this->weight->convert($cartItem['weight'], $cartItem['weight_class_id'], $this->config->get('shipping_boxberry_weight_class_id'));

            if (($width + $height + $length) > $this->maxDimension) {
                return $method_data;
            }
        }

        $weight = $this->weight->convert($this->cart->getWeight(),
            $this->config->get('config_weight_class_id'), $this->config->get('shipping_boxberry_weight_class_id'));

        if (($this->config->get('shipping_boxberry_weight_max')>0 && ($weight > $this->config->get('shipping_boxberry_weight_max')))
            || ($this->config->get('shipping_boxberry_weight_min')>=0 && ($weight < $this->config->get('shipping_boxberry_weight_min')))) {
            return $method_data;
        }
        if ($this->config->get('shipping_boxberry_status')) {
            $quote_data = array();
            if ($city = $this->getBoxberryCity()) {
                $this->getListPoints($city, 0);
            }
            if ($this->config->get('shipping_boxberry_pickup_status')) {
                /*--------------IssuePoint-------------------*/
                $html = '';
                $point = null;
                if (!$city) {
                    $session->data['boxberry_shipping_fail_city'] = 'Доставка до выбранного места невозможна';
                } else {

                    try {
                        $this->load->model('boxberry/point');
                        $issue_pointId = $this->getIssuePointByCity($city['code'], 0);

                        if ($issue_pointId
                            && ($point = $this->getIssuePointById($issue_pointId, 0))) {
                            $res = $this->getIssuePointCost($address, 0);
                            if (!empty($res)) {

                                list($issue_point_cost, $issue_point_period) = $res;
                                if ($issue_point_period) {
                                    $issue_point_period_text = '&nbsp;<i>(срок доставки - ' . $issue_point_period . ' ' . trim($this->getPeriod($issue_point_period, 'рабочий день', 'рабочих дня', 'рабочих дней')) . ')</i>';
                                } else {
                                    $issue_point_period_text = '';
                                }
                                $cartItems = $this->cart->getProducts();
                                if (count($cartItems) > 1) {
                                    $cartItem = [
                                        'width' => 0,
                                        'height' => 0,
                                        'length' => 0
                                    ];
                                } else {
                                    $cartItem = array_shift($cartItems);
                                    if ((int)$cartItem['quantity']>1) {
                                        $cartItem = [
                                            'width' => 0,
                                            'height' => 0,
                                            'length' => 0
                                        ];
                                    }

                                }
                                $html .= '
                <a id="boxberry-issue_point-link" href="#"
                   data-boxberry-open="true"
       data-type="boxberryDeliverySelf"
       data-boxberry-token=' . $this->config->get('shipping_boxberry_widget_key') . '
       data-boxberry-city=""
       data-api-url=' . $this->config->get('shipping_boxberry_api_url') . '
       data-sucrh=' . $this->config->get('shipping_boxberry_pickup_sucrh') . '  
       data-boxberry-weight=' . implode(',', $weights) . '
       data-boxberry-width=' . ($cartItem['width'] ? $this->length->convert($cartItem['width'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
       data-boxberry-height=' . ($cartItem['height'] ? $this->length->convert($cartItem['height'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
       data-boxberry-length=' . ($cartItem['length'] ? $this->length->convert($cartItem['length'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
       data-ordersum=' . $this->currency->convert($this->cart->getTotal(), $this->config->get('config_currency'), 'RUB') . '
       data-class="boxberryDeliverySelf"
       data-order-id=""
                   > Выбрать пункт выдачи на карте' . '</a>';

                                $quote_data['pickup'] = array(
                                    'code' => 'boxberry.pickup',
                                    'title' => $this->config->get('shipping_boxberry_pickup_name') ? $this->config->get('shipping_boxberry_pickup_name')
                                        : $this->language->get('boxberry_pickup_description'),
                                    'cost' => $issue_point_cost,
                                    'tax_class_id' => 0,
                                    'text' => $this->currency->format($this->tax->calculate($issue_point_cost, 0, $this->config->get('config_tax')), $this->config->get('config_currency')) . $issue_point_period_text,
                                    'boxberry_html' => $html
                                );
                            }
                        }
                    } catch (Exception $e) {

                        $session->data['boxberry_shipping_fail_cost_issue_point'] = 'Не удалось вычислить стоимость доставки';
                    }
                  //*---------------------------------*/
                }
            }
            if ($city) {
                $this->getListPoints($city, 1);
            }
            if ($this->config->get('shipping_boxberry_pickup_prepaid_status')) {
                /*--------------IssuePoint prepaid-------------------*/
                $html = '';
                $point = null;
                if ($city) {
                    try {
                        $this->load->model('boxberry/point');
                        $issue_pointId = $this->getIssuePointByCity($city['code'], 1);

                        if ($issue_pointId
                            && ($point = $this->getIssuePointById($issue_pointId, 1))) {
                            $res = $this->getIssuePointCost($address, 1);
                            if (!empty($res)) {
                            list($issue_point_cost, $issue_point_period) = $res;
                            if ($issue_point_period) {
                                $issue_point_period_text = '&nbsp;<i>(срок доставки - ' . $issue_point_period . ' ' . trim($this->getPeriod($issue_point_period, 'рабочий день', 'рабочих дня', 'рабочих дней')) . ')</i>';
                            } else {
                                $issue_point_period_text = '';
                            }
                            $cartItems = $this->cart->getProducts();
                            if (count($cartItems) > 1) {
                                $cartItem = [
                                    'width' => 0,
                                    'height' => 0,
                                    'length' => 0
                                ];
                            } else {
                                $cartItem = array_shift($cartItems);
                                if ((int)$cartItem['quantity']>1) {
                                    $cartItem = [
                                        'width' => 0,
                                        'height' => 0,
                                        'length' => 0
                                    ];
                                }
                            }
                            $html .= '
            <a id="boxberry-issue_point-link-prepaid" href="#"
               data-boxberry-open="true"
   data-type="boxberryDeliverySelfPrepaid"
   data-boxberry-token=' . $this->config->get('shipping_boxberry_widget_key') . '
   data-api-url=' . $this->config->get('shipping_boxberry_api_url') . '
   data-sucrh=' . $this->config->get('shipping_boxberry_pickup_prepaid_sucrh') . '
   data-boxberry-city=""
   data-boxberry-weight=' . implode(',', $weights) . '
   data-boxberry-width=' . ($cartItem['width'] ? $this->length->convert($cartItem['width'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
   data-boxberry-height=' . ($cartItem['height'] ? $this->length->convert($cartItem['height'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
   data-boxberry-length=' . ($cartItem['length'] ? $this->length->convert($cartItem['length'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
   data-ordersum=' . $this->currency->convert($this->cart->getTotal(), $this->config->get('config_currency'), 'RUB') . '
   data-class="boxberryDeliverySelfPrepaid"
   data-order-id=""
               > Выбрать пункт выдачи на карте</a>';
                            $quote_data['pickup_prepaid'] = array(
                                'code' => 'boxberry.pickup_prepaid',
                                'title' => $this->config->get('shipping_boxberry_pickup_prepaid_name') ? $this->config->get('shipping_boxberry_pickup_prepaid_name')
                                    : $this->language->get('boxberry_pickup_prepaid_description'),
                                'cost' => $issue_point_cost,
                                'tax_class_id' => 0,
                                'text' => $this->currency->format($this->tax->calculate($issue_point_cost, 0, $this->config->get('config_tax')), $this->config->get('config_currency')) . $issue_point_period_text,
                                'boxberry_html' => $html
                            );
                        }
                    }
                    } catch (Exception $e) {
                        $session->data['boxberry_shipping_fail_cost_issue_point_prepaid'] = 'Не удалось вычислить стоимость доставки';
                    }
                }
                /*---------------------------------*/
            }
            $zip = $this->getBoxberryZip();
            if ($this->config->get('shipping_boxberry_courier_delivery_status')) {
                /*---------------CourierDelivery---------------*/
                if (!$zip) {
                    $session->data['boxberry_shipping_fail_zip'] = 'Доставка до выбранного места невозможна';
                } else {
                    try {
                        list($courier_delivery_cost, $courier_delivery_period) = $this->getCourierDeliveryCost($address, 0);
                        if ($courier_delivery_period) {
                            $courier_delivery_period_text = '&nbsp;<i>(срок доставки - ' . $courier_delivery_period .' '. trim($this->getPeriod($courier_delivery_period,'рабочий день','рабочих дня','рабочих дней')) .')</i>';
                        } else {
                            $courier_delivery_period_text = '';
                        }
                        $quote_data['courier_delivery'] = array(
                            'code' => 'boxberry.courier_delivery',
                            'title' => $this->config->get('shipping_boxberry_courier_delivery_name') ? $this->config->get('shipping_boxberry_courier_delivery_name')
                                : $this->language->get('boxberry_courier_delivery_description'),
                            'cost' => $courier_delivery_cost,
                            'tax_class_id' => 0,
                            'text' => $this->currency->format($this->tax->calculate($courier_delivery_cost, 0, $this->config->get('config_tax')), $this->config->get('config_currency')) . $courier_delivery_period_text
                        );
                    } catch (Exception $e) {
                        $session->data['boxberry_shipping_fail_cost_courier_delivery'] = 'Не удалось вычислить стоимость доставки';
                    }
                }
                /*---------------------------------*/
            }

            if ($this->config->get('shipping_boxberry_courier_delivery_prepaid_status')) {
                /*---------------CourierDelivery prepaid---------------*/
                if ($zip) {
                    try {
                        list($courier_delivery_cost, $courier_delivery_period) = $this->getCourierDeliveryCost($address, 1);
                        if ($courier_delivery_period) {
                            $courier_delivery_period_text = '&nbsp;<i>(срок доставки - ' . $courier_delivery_period . ' '.trim($this->getPeriod($courier_delivery_period,'рабочий день','рабочих дня','рабочих дней')).')</i>';
                        } else {
                            $courier_delivery_period_text = '';
                        }
                        $quote_data['courier_delivery_prepaid'] = array(
                            'code' => 'boxberry.courier_delivery_prepaid',
                            'title' => $this->config->get('shipping_boxberry_courier_delivery_prepaid_name') ? $this->config->get('shipping_boxberry_courier_delivery_prepaid_name')
                                : $this->language->get('boxberry_courier_delivery_prepaid_description'),
                            'cost' => $courier_delivery_cost,
                            'tax_class_id' => 0,
                            'text' => $this->currency->format($this->tax->calculate($courier_delivery_cost, 0, $this->config->get('config_tax')), $this->config->get('config_currency')) . $courier_delivery_period_text
                        );
                    } catch (Exception $e) {
                        $session->data['boxberry_shipping_fail_cost_courier_delivery_prepaid'] = 'Не удалось вычислить стоимость доставки';
                    }
                }

                /*---------------------------------*/
            }

            if ($quote_data) {
                $widget_url = '<script type="text/javascript" src="'.$this->config->get('shipping_boxberry_widget_url').'"></script>';
                if (isset($quote_data['pickup'])){
                    $quote_data['pickup']['boxberry_html'] = $quote_data['pickup']['boxberry_html'].$widget_url;
                } elseif(isset( $quote_data['pickup_prepaid'])) {
                    $quote_data['pickup_prepaid']['boxberry_html'] = $quote_data['pickup_prepaid']['boxberry_html'].$widget_url;
                }

                $method_data = array(
                    'code' => 'boxberry',
                    'title' => $this->language->get('heading_title'),
                    'quote' => $quote_data,
                    'sort_order' => $this->config->get('shipping_boxberry_sort_order'),
                    'error' => false,
                );
            }
        }


        return $method_data;
    }

    public function getQuoteIssuePoint($address)
    {
        $this->load->language('extension/shipping/boxberry');

        $quote_data = array();

        $cartItems = $this->cart->getProducts();
        $weights = [];
        $weights[] = 0;

        foreach ($cartItems as $cartItem) {
            $weights[] = $this->weight->convert($cartItem['weight'], $cartItem['weight_class_id'], $this->config->get('shipping_boxberry_weight_class_id'));
        }

       if ($this->session->data['boxberry_shipping_issue_point_prepaid']) {
            /*--------------IssuePoint prepaid-------------------*/
            unset($this->session->data['boxberry_shipping_fail_cost_issue_point_prepaid']);
            if ($city = $this->getboxberryCity()) {
                $this->getListPoints($city, 1);
            }
            $html = '';
            $point = null;
            try {
                $this->load->model('boxberry/point');
                if ($this->session->data['boxberry_shipping_issue_point_id_prepaid']
                    && ($point = $this->getIssuePointById($this->session->data['boxberry_shipping_issue_point_id_prepaid'], 1))) {
                    $res = $this->getIssuePointCost($address, 1);
                    if (!empty($res)) {

                        list($issue_point_cost, $issue_point_period) = $res;
                        if ($issue_point_period) {
                            $issue_point_period_text = '&nbsp;<i>(срок доставки - ' . $issue_point_period . ' ' . trim($this->getPeriod($issue_point_period, 'рабочий день', 'рабочих дня', 'рабочих дней')) . ')</i>';
                        } else {
                            $issue_point_period_text = '';
                        }
                        $html .=
                            '<div id="boxberry-issue_point-block" style="margin: 9px;">
    <b><span id="boxberry-issue_point-name">' . $point['Name'] . '</span></b>
    <br><span id="boxberry-issue_point-addr-short">' . $point['AddressReduce'] . '</span>
    <br>Телефон: <span id="boxberry-issue_point-phone">' . $point['Phone'] . '</span>, часы работы: <span id="boxberry-issue_point-work">' .
                            (isset($point['WorkSchedule']) ? $point['WorkSchedule'] : $point['WorkShedule']) . '</span></div>';

                        if (count($cartItems) > 1) {
                            $cartItem = [
                                'width' => 0,
                                'height' => 0,
                                'length' => 0
                            ];
                        } else {
                            $cartItem = array_shift($cartItems);
                            if ((int)$cartItem['quantity']>1) {
                                $cartItem = [
                                    'width' => 0,
                                    'height' => 0,
                                    'length' => 0
                                ];
                            }
                        }
                        $html .= '
                <a id="boxberry-issue_point-link-prepaid" href="#"
                   data-boxberry-open="true"
       data-type="boxberryDeliverySelfPrepaid"
       data-boxberry-token=' . $this->config->get('shipping_boxberry_widget_key') . '
       data-boxberry-city=""
       data-api-url=' . $this->config->get('shipping_boxberry_api_url') . '
       data-sucrh=' . $this->config->get('shipping_boxberry_pickup_prepaid_sucrh') . '
       data-boxberry-weight=' . implode(',', $weights) . '
       data-boxberry-width=' . ($cartItem['width'] ? $this->length->convert($cartItem['width'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
       data-boxberry-height=' . ($cartItem['height'] ? $this->length->convert($cartItem['height'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
       data-boxberry-length=' . ($cartItem['length'] ? $this->length->convert($cartItem['length'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
       data-ordersum=' . $this->currency->convert($this->cart->getTotal(), $this->config->get('config_currency'), 'RUB') . '
       data-class="boxberryDeliverySelfPrepaid"
       data-order-id=""
                   > Выбрать ' . ($point ? 'другой' : '') . '</a>';

                        $quote_data = array(
                            'code' => 'boxberry.pickup_prepaid',
                            'title' => $this->config->get('shipping_boxberry_pickup_prepaid_name') ? $this->config->get('shipping_boxberry_pickup_prepaid_name')
                                : $this->language->get('boxberry_pickup_prepaid_description'),
                            'cost' => $issue_point_cost,
                            'tax_class_id' => 0,
                            'text' => $this->currency->format($this->tax->calculate($issue_point_cost, 0, $this->config->get('config_tax')), $this->config->get('config_currency')) . $issue_point_period_text,
                            'boxberry_html' => $html
                        );

                        $this->session->data['shipping_methods']['boxberry']['quote']['pickup_prepaid'] = $quote_data;
                    }
                }
            } catch (Exception $e) {
                $this->session->data['boxberry_shipping_fail_cost_issue_point_prepaid'] = 'Не удалось вычислить стоимость доставки';
            }
            /*---------------------------------*/
        } else {
            /*--------------IssuePoint-------------------*/
            unset($this->session->data['boxberry_shipping_fail_cost_issue_point']);
            if ($city = $this->getBoxberryCity()) {
                $this->getListPoints($city, 0);
            }
            $html = '';
            $point = null;

            try {
                $this->load->model('boxberry/point');
                if ($this->session->data['boxberry_shipping_issue_point_id']
                    && ($point = $this->getIssuePointById($this->session->data['boxberry_shipping_issue_point_id'], 0))) {
                    //    echo "<pre>";print_r($point);print_r($city);print_r($this->session->data);exit;
                    $res = $this->getIssuePointCost($address, 0);
                    if (!empty($res)) {
                        list($issue_point_cost, $issue_point_period) = $res;
                        if ($issue_point_period) {
                            $issue_point_period_text = '&nbsp;<i>(срок доставки - ' . $issue_point_period . ' ' . trim($this->getPeriod($issue_point_period, 'рабочий день', 'рабочих дня', 'рабочих дней')) . ')</i>';
                        } else {
                            $issue_point_period_text = '';
                        }
                        $html .=
                            '<div id="boxberry-issue_point-block" style="margin: 9px;">
<b><span id="boxberry-issue_point-name">' . $point['Name'] . '</span></b>
<br><span id="boxberry-issue_point-addr-short">' . $point['AddressReduce'] . '</span>
<br>Телефон: <span id="boxberry-issue_point-phone">' . $point['Phone'] . '</span>, часы работы: <span id="boxberry-issue_point-work">' .
                            (isset($point['WorkSchedule']) ? $point['WorkSchedule'] : $point['WorkShedule']) . '</span></div>';

                        if (count($cartItems) > 1) {
                            $cartItem = [
                                'width' => 0,
                                'height' => 0,
                                'length' => 0
                            ];
                        } else {
                            $cartItem = array_shift($cartItems);
                            if ((int)$cartItem['quantity']>1) {
                                $cartItem = [
                                    'width' => 0,
                                    'height' => 0,
                                    'length' => 0
                                ];
                            }
                        }
                        $html .= '
            <a id="boxberry-issue_point-link" href="#"
               data-boxberry-open="true"
   data-type="boxberryDeliverySelf"
   data-boxberry-token=' . $this->config->get('shipping_boxberry_widget_key') . '
   data-boxberry-city=""
   data-api-url=' . $this->config->get('shipping_boxberry_api_url') . '
   data-sucrh=' . $this->config->get('shipping_boxberry_pickup_sucrh') . '
   data-boxberry-weight=' . implode(',', $weights) . '
   data-boxberry-width=' . ($cartItem['width'] ? $this->length->convert($cartItem['width'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
   data-boxberry-height=' . ($cartItem['height'] ? $this->length->convert($cartItem['height'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
   data-boxberry-length=' . ($cartItem['length'] ? $this->length->convert($cartItem['length'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0) . '
   data-ordersum=' . $this->currency->convert($this->cart->getTotal(), $this->config->get('config_currency'), 'RUB') . '
   data-class="boxberryDeliverySelf"
   data-order-id=""
               > Выбрать ' . ($point ? 'другой' : '') . '</a>';

                        $quote_data = array(
                            'code' => 'boxberry.pickup',
                            'title' => $this->config->get('shipping_boxberry_pickup_name') ? $this->config->get('shipping_boxberry_pickup_name')
                                : $this->language->get('boxberry_pickup_description'),
                            'cost' => $issue_point_cost,
                            'tax_class_id' => 0,
                            'text' => $this->currency->format($this->tax->calculate($issue_point_cost, 0, $this->config->get('config_tax')), $this->config->get('config_currency')) . $issue_point_period_text,
                            'boxberry_html' => $html
                        );
                        $this->session->data['shipping_methods']['boxberry']['quote']['pickup'] = $quote_data;
                    }
                }
            } catch (Exception $e) {
                $this->session->data['boxberry_shipping_fail_cost_issue_point'] = 'Не удалось вычислить стоимость доставки';
            }
            /*---------------------------------*/
        }

        return $quote_data;
    }

    public function getIssuePointById($issue_point_id, $prepaid)
    {
        $this->load->model('boxberry/point');
        if ((!$point = $this->model_boxberry_point->getPoint($issue_point_id, $prepaid)) || ($point && (strtotime($point['expired']) <= time()))) {
            $client = new Client();
            $this->load->model('boxberry/point');
            $client->setKey($this->config->get('shipping_boxberry_api_token'));
            $client->setApiUrl($this->config->get('shipping_boxberry_api_url'));
            $description = $client->getPointsDescription();
            $description->setCode($issue_point_id);
            $description->setPhoto(0);
            try {
                $point = $client->execute($description);
                $point = [
                    'code' => $issue_point_id,
                    'city_code' => $point->getCityCode(),
                    'data' => $point->getData(),
                    'prepaid' => $prepaid
                ];
                $this->model_boxberry_point->addPoint($point);

                return $point['data'];
            } catch (Exception $e) {

            }
        }

        return json_decode($point['data'], 1);
    }

    public function getBoxberryCity()
    {
        $client = new Client();
        $this->load->model('setting/setting');
        $this->load->model('boxberry/city');
        $this->load->model('boxberry/point');
        $this->load->model('boxberry/expired');

        $client->setKey($this->config->get('shipping_boxberry_api_token'));
        $client->setApiUrl($this->config->get('shipping_boxberry_api_url'));
        $city_code = null;
        $array_cities = [];
        if (!($expired = $this->model_boxberry_expired->getExpired(DB_PREFIX . 'boxberry_cities')) || (strtotime($expired) <= time())) {
            $listCities = $client->getListCities();
            try {
                $boxberry_cities = $client->execute($listCities);
            } catch (Exception $e) {
                $boxberry_cities = [];
            }
            foreach ($boxberry_cities as $city) {
                $this->model_boxberry_city->addCity([
                    'code' => $city->getCode(),
                    'name' => $city->getName(),
                    'region' => $city->getRegion(),
                    'data' => $city->getData()
                ]);
                /** var /Boxberry/Models/City $city */
                $array_cities[$city->getCode()] = $city->getData();
            }
            $this->model_boxberry_expired->addExpired(['table' => DB_PREFIX . 'boxberry_cities']);
        }
        $region  = ($this->area !='')  ? $this->area : $this->session->data['shipping_address']['zone'];
        $city = $this->model_boxberry_city->getCityByName(str_replace('ё','е',$this->session->data['shipping_address']['city']), $region);

        return $city;
    }

    public function getListPoints($city, $prepaid = 0)
    {
        $this->load->model('boxberry/expired');
        $this->load->model('boxberry/point');
        $city_code = $city['code'];
        if (!($expired = $this->model_boxberry_expired->getExpired(DB_PREFIX . 'boxberry_cities')) || (strtotime($expired) <= time())
            || !$this->model_boxberry_point->getPoints($city_code, $prepaid)) {
            $client = new Client();
            $client->setKey($this->config->get('shipping_boxberry_api_token'));
            $client->setApiUrl($this->config->get('shipping_boxberry_api_url'));
            $listPoints = $client->getListPoints();
            $listPoints->setCityCode($city_code);
            if ($prepaid)
                $listPoints->setPrepaid(1);
            try {
                $boxberry_points = $client->execute($listPoints);
            } catch (Exception $e) {
                $boxberry_points = [];
            }
            foreach ($boxberry_points as $point) {
                $this->model_boxberry_point->addPoint([
                    'code' => $point->getCode(),
                    'city_code' => $city_code,
                    'data' => $point->getData(),
                    'prepaid' => $prepaid
                ]);
            }
        }
    }

    public function getIssuePointByCity($code, $prepaid = 0)
    {
        $this->load->model('boxberry/point');
        if ($point = $this->model_boxberry_point->getPointByCity($code, $prepaid)) {
            return $point['code'];
        }

        return $point;
    }

    public function getBoxberryZip()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "boxberry_listzips` (
      `zip` VARCHAR(128) NOT NULL,
      `city` VARCHAR(128),
      `area` VARCHAR(128),
      PRIMARY KEY (`zip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;");

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "boxberry_zips`;");

        $client = new Client();
        $this->load->model('setting/setting');
        $this->load->model('boxberry/zip');
        $this->load->model('boxberry/city');
        $this->load->model('boxberry/expired');

        $client->setKey($this->config->get('shipping_boxberry_api_token'));
        $client->setApiUrl($this->config->get('shipping_boxberry_api_url'));
        $city_code = null;
        $array_zips = [];

        if (!($expired = $this->model_boxberry_expired->getExpired(DB_PREFIX . 'boxberry_listzips')) || (strtotime($expired) <= time())) {
            $listZips = $client->getListZips();
            try {
                $boxberry_zips = $client->execute($listZips);
            } catch (Exception $e) {
                $boxberry_zips = [];
            }
            foreach ($boxberry_zips as $zip) {
                $this->model_boxberry_zip->addZip([
                    'zip' => $zip->getZip(),
                    'city' => $zip->getCity(),
                    'area' => $zip->getArea()
                ]);
                /** var /Boxberry/Models/Zip $zip */
                $array_zips[$zip->getZip()] = $zip->getZip();
            }
            $this->model_boxberry_expired->addExpired(['table' => DB_PREFIX . 'boxberry_listzips']);
        }
        $region  = ($this->area !='')  ? $this->area : $this->session->data['shipping_address']['zone'];
        $zip_search = $this->model_boxberry_zip->getZipByCity(str_replace('ё','е',$this->session->data['shipping_address']['city']), $region);
        if ($zip_search == true) {
            $zip = array_flip($zip_search);
            $zip_found = array_keys($zip, 'zip');
            return implode($zip_found);
        }
        return false;
    }

    protected function getIssuePointCost($address, $prepaid)
    {

        $period = 0;
        $client = new Client();
        $client->setKey($this->config->get('shipping_boxberry_api_token'));
        $client->setApiUrl($this->config->get('shipping_boxberry_api_url'));
        $deliveryCosts = $client->getDeliveryCosts();
        $deliveryCosts->setWeight($this->weight->convert($this->cart->getWeight(),
            $this->config->get('config_weight_class_id'), $this->config->get('shipping_boxberry_weight_class_id')));
        $cartItems = $this->cart->getProducts();
        if (count($cartItems) > 1) {
            $deliveryCosts->setDepth(0);
            $deliveryCosts->setHeight(0);
            $deliveryCosts->setWidth(0);
        } else {
            $cartItem = array_shift($cartItems);

            if ((int)$cartItem['quantity']>1){
                $deliveryCosts->setDepth(0);
                $deliveryCosts->setHeight(0);
                $deliveryCosts->setWidth(0);
            }else {
                $deliveryCosts->setDepth($cartItem['length'] ? $this->length->convert($cartItem['length'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0);
                $deliveryCosts->setHeight($cartItem['height'] ?$this->length->convert($cartItem['height'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0);
                $deliveryCosts->setWidth($cartItem['width'] ? $this->length->convert($cartItem['width'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0);
            }

        }

        $sucrh = $prepaid == 1 ? $this->config->get('shipping_boxberry_pickup_prepaid_sucrh') : $this->config->get('shipping_boxberry_pickup_sucrh');
        $deliveryCosts->setSurch($sucrh);
        $deliveryCosts->setDeliverysum(0);
        $deliveryCosts->setPaysum($prepaid ? 0 : $this->cart->getTotal());
        $deliveryCosts->setOrdersum($this->cart->getTotal());
        $deliveryCosts->setCms('opencart');
        $deliveryCosts->setUrl($_SERVER['HTTP_HOST']);
        $deliveryCosts->setVersion('1.6');

        if (isset($this->session->data['boxberry_shipping_issue_point_id' . ($prepaid ? '_prepaid' : '')])
            && ($target = ($this->session->data['boxberry_shipping_issue_point_id' . ($prepaid ? '_prepaid' : '')]))) {

            $deliveryCosts->setTarget($target);
        } else {
            $listCities = $client->getListCities();
            try {
                $boxberry_cities = $client->execute($listCities);
            } catch (Exception $e) {
                $boxberry_cities = [];
            }

            $city_code = null;

            if ($this->area == ''){
                foreach ($boxberry_cities as $city) {
                    if ((mb_strtoupper($city->getName())) == (mb_strtoupper(str_replace('ё','е',$address['city']))) || mb_strtoupper($city->getName()) == mb_strtoupper(mb_substr(str_replace('ё','е',$address['city']), 0, 1)) . mb_substr($address['city'], 1)) {
                        $city_code = $city->getCode();
                        break;
                    }
                }
            } else {
                $fullAddress = false;
                foreach ($boxberry_cities as $city){

                    if ((mb_strtoupper($city->getName()) == trim(mb_strtoupper($address['city'])) || mb_strtoupper($city->getName()) == trim(mb_strtoupper(mb_substr($address['city'], 0, 1))) . trim(mb_substr($address['city'], 1))) && ($this->area && stristr(mb_strtoupper($city->getRegion()), trim(mb_strtoupper($this->area))) !== false )){

                        $city_code = $city->getCode();
                        $fullAddress = true;
                        break;
                    }
                }

                if (!$fullAddress){
                    foreach ($boxberry_cities as $city)
                    {
                        /** var /Boxberry/Models/City $city */
                        if ((mb_strtoupper($city->getName())) == trim(mb_strtoupper($address['city'])) || mb_strtoupper($city->getName()) == trim(mb_strtoupper(mb_substr($address['city'], 0, 1))) . trim(mb_substr($address['city'], 1))) {
                            $city_code = $city->getCode();
                            break;
                        }
                    }
                }
            }

            $blockCityModel = $client::getWidgetSettings();

            $blockCityModel->setType(1);

            $blockCity = $client->execute($blockCityModel);

            $blockCityCodes = $blockCity->getResult()[1]['CityCode'];


            if (in_array($city_code,$blockCityCodes)){

                return [];
            }

            if ($city_code !== null) {
                $listPoints = $client->getListPoints();
                $listPoints->setCityCode($city_code);
                $listPoints->setCityCode($city_code);
                if ($prepaid)
                    $listPoints->setPrepaid(1);
                try {
                    $listPointsCollection = $client->execute($listPoints);

                } catch (Exception $e) {

                }
                if (isset($listPointsCollection) && isset($listPointsCollection[0])) {
                    $deliveryCosts->setTarget($listPointsCollection[0]->getCode());
                }
            }
            unset($city_code, $listCities, $listPointsCollection);
        }



        $responseObject = $client->execute($deliveryCosts);
        $costReceived = $responseObject->getPrice();
        $widgetSettings = $client->getWidgetSettings();
        $responseSettings = $client->execute($widgetSettings);
        if (!$responseSettings->getHide_delivery_day()) {
            $period = $responseObject->getDeliveryPeriod();
        }
        unset($responseObject, $responseSettings);

        return [$this->currency->convert($costReceived,
            'RUB', $this->config->get('config_currency')), $period];
    }

    protected function getCourierDeliveryCost($address, $prepaid)
    {
        $period = 0;
        $client = new Client();
        $client->setKey($this->config->get('shipping_boxberry_api_token'));
        $client->setApiUrl($this->config->get('shipping_boxberry_api_url'));

        $deliveryCosts = $client->getDeliveryCosts();
        $deliveryCosts->setWeight($this->weight->convert($this->cart->getWeight(),
            $this->config->get('config_weight_class_id'), $this->config->get('shipping_boxberry_weight_class_id')));
        $cartItems = $this->cart->getProducts();
        if (count($cartItems) > 1) {
            $deliveryCosts->setDepth(0);
            $deliveryCosts->setHeight(0);
            $deliveryCosts->setWidth(0);
        } else {
            $cartItem = array_shift($cartItems);

            if ((int)$cartItem['quantity']>1){
                $deliveryCosts->setDepth(0);
                $deliveryCosts->setHeight(0);
                $deliveryCosts->setWidth(0);
            }else {
                $deliveryCosts->setDepth($cartItem['length'] ? $this->length->convert($cartItem['length'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0);
                $deliveryCosts->setHeight($cartItem['height'] ?$this->length->convert($cartItem['height'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0);
                $deliveryCosts->setWidth($cartItem['width'] ? $this->length->convert($cartItem['width'], $cartItem['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0);
            }

        }

        $sucrh = $prepaid == 1 ? $this->config->get('shipping_boxberry_courier_delivery_prepaid_sucrh') : $this->config->get('shipping_boxberry_courier_delivery_sucrh');
        $deliveryCosts->setSurch($sucrh);

        $deliveryCosts->setDeliverysum(0);
        $deliveryCosts->setPaysum($prepaid ? 0 : $this->cart->getTotal());
        $deliveryCosts->setOrdersum($this->cart->getTotal());
        $deliveryCosts->setZip($this->getBoxberryZip());
        $deliveryCosts->setCms('opencart');
        $deliveryCosts->setUrl($_SERVER['HTTP_HOST']);
        $deliveryCosts->setVersion('1.6');
        $responseObject = $client->execute($deliveryCosts);
        $costReceived = $responseObject->getPrice();
        $widgetSettings = $client->getWidgetSettings();
        $responseSettings = $client->execute($widgetSettings);
        if (!$responseSettings->getHide_delivery_day()) {
            $period = $responseObject->getDeliveryPeriod();
        }
        unset($responseObject, $responseSettings);

        return [$this->currency->convert($costReceived,
            'RUB', $this->config->get('config_currency')), $period];
    }

    public function getPeriod($count, $form1, $form2, $form3)
    {
        $count = abs($count) % 100;
        $lcount = $count % 10;
        if ($count >= 11 && $count <= 19) return($form3);
        if ($lcount >= 2 && $lcount <= 4) return($form2);
        if ($lcount == 1) return($form1);
        return $form3;
    }
}


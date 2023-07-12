<?php

use Boxberry\Client\Client;

if (!class_exists('Client')) {
    require_once DIR_SYSTEM . 'library/boxberry/autoload.php';
}

class ControllerSaleBoxberry extends Controller
{
    public function index()
    {
        $this->load->language('sale/boxberry');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('boxberry/delivery');

        $this->getList();
    }

    protected function getList()
    {
        $tokenName = 'user_token';
        if (strpos(VERSION, '2.') === 0) {
            $tokenName = 'token';

            $this->session->data['user_token'] = $this->session->data[$tokenName];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'order_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $tokenName . '=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/boxberry', $tokenName . '=' . $this->session->data['user_token'] . $url, true)
        );
        $data['deliveries'] = array();

        $data = array_merge($data, $this->load->language('sale/boxberry'));
        $data = array_merge($data, $this->load->language('extension/extension'));

        $filter_data = array(
            'filter_order_id' => $filter_order_id,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );
        $data['shipping_boxberry_widget_key'] = $this->config->get('shipping_boxberry_widget_key');
        $data['deliveries'] = $this->model_boxberry_delivery->getDeliveries($filter_data);

        foreach ($data['deliveries'] as &$delivery) {
            $products = $this->model_boxberry_delivery->getProducts($delivery['order_id']);
            if (!isset($products[0])) {
                continue;
            }
            $product = $products[0];
            if ((count($products) > 1) || ((count($products) == 1) && ($product['quantity'] > 1))) {
                $product['width'] = 0;
                $product['height'] = 0;
                $product['length'] = 0;
            } else {
                $product['width'] ?: $product['width'] = $this->config->get('shipping_boxberry_width');
                $product['height'] ?: $product['height'] = $this->config->get('shipping_boxberry_height');
                $product['length'] ?: $product['length'] = $this->config->get('shipping_boxberry_length');
            }
            $delivery['code'] = $product['shipping_code'];

            if (!in_array($delivery['code'], ['boxberry.pickup', 'boxberry.pickup_prepaid']) || !$delivery['error']) {
                continue;
            }
            $delivery['class'] = $delivery['code'] === 'boxberry.pickup' ? 'boxberryDeliverySelf' : 'boxberryDeliverySelfPrepaid';
            $delivery['city'] = $product['shipping_zone'] . ' ' . $product['shipping_city'];
            $delivery['width'] = $product['width'] ? $this->length->convert($product['width'], $product['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0;
            $delivery['height'] = $product['height'] ? $this->length->convert($product['height'], $product['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0;
            $delivery['length'] = $product['length'] ? $this->length->convert($product['length'], $product['length_class_id'], $this->config->get('shipping_boxberry_length_class_id')) : 0;
            $total = 0;
            foreach ($products as $product) {
                $total += $product['total'];
            }
            $delivery['ordersum'] = $this->currency->convert($total, $this->config->get('config_currency'), 'RUB');

            $weight = 0;
            foreach ($products as $product) {
                if ($product['shipping']) {
                    $product['weight'] > 0 ?: $product['weight'] = $this->config->get('shipping_boxberry_weight');
                    $weight += $this->weight->convert($product['weight'] * $product['quantity'],
                        $product['weight_class_id'], $this->config->get('config_weight_class_id'));
                }
            }
            $delivery['weight'] = $this->weight->convert($weight, $this->config->get('config_cart_weight'),
                $this->config->get('shipping_boxberry_weight_class_id'));

        }

        $data['user_token'] = $this->session->data['user_token'];

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

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (strpos(VERSION, '2.') !== false) {
            $data['sort_order_id'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=order_id' . $url . '&token=' .$this->session->data['token'], true);
            $data['sort_im_id'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=im_id' . $url . '&token=' .$this->session->data['token'], true);
            $data['sort_label'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=label' . $url . '&token=' . $this->session->data['token'], true);
            $data['sort_error'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=error' . $url . '&token=' . $this->session->data['token'], true);
            $data['sort_boxberry_to_point'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=boxberry_to_point' . $url . '&token=' . $this->session->data['token'], true);
            $data['sort_address'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=address' . $url . '&token=' . $this->session->data['token'], true);
        } else {
            $data['sort_order_id'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=order_id' . $url, true);
            $data['sort_im_id'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=im_id' . $url, true);
            $data['sort_label'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=label' . $url, true);
            $data['sort_error'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=error' . $url, true);
            $data['sort_boxberry_to_point'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=boxberry_to_point' . $url, true);
            $data['sort_address'] = $this->url->link('sale/boxberry', 'user_token=' . $this->session->data['user_token'] . '&sort=address' . $url, true);
        }

        $url = '';
        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $total = count($data['deliveries']);
        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total - $this->config->get('config_limit_admin'))) ? $total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total, ceil($total / $this->config->get('config_limit_admin')));
        $data['filter_order_id'] = $filter_order_id;
        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
        $data['api_token'] = '';
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('sale/delivery_list', $data));
    }

    public function change_issue_point()
    {
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->request->post['order_id']);



        $issue_point_id = $this->request->post['id'];
        $city = $this->request->post['name'];
        if (!in_array($order_info['shipping_code'], ['boxberry.pickup', 'boxberry.pickup_prepaid'])) {
            return;
        }
        $order_data = [
            'shipping_address_1' => '# ' . $issue_point_id . $this->request->post['address'],
            'shipping_address_2' => '# ' . $issue_point_id . $this->request->post['address'],
            'shipping_postcode' => $this->request->post['zip'],
            'shipping_city' => $city
        ];
        $this->model_checkout_order->editOrder($order_info['order_id'], $order_data);

        $this->load->model('checkout/order');
        $this->load->model('boxberry/delivery');
        $boxberryDelivery = $this->model_boxberry_delivery->getDelivery($order_info['order_id']);
        $this->load->model("extension/shipping/boxberry");

        $region = $order_info['shipping_zone'];
        $address = $order_data['shipping_address_1'];
        $email = $order_info['email'];
        $phone = $order_info['telephone'];
        $receiver = $order_info['lastname']. ' '. $order_info['firstname'];

        $im_id = null;
        $label = null;

        if (strpos($order_info['shipping_address_1'], '#') !== false) {
            $boxberry_to_point = explode(' ', $order_info['shipping_address_1']);
            $boxberry_to_point = $boxberry_to_point[1];
        }
        if (strpos($order_info['shipping_address_2'], '#') !== false) {
            $boxberry_to_point = explode(' ', $order_info['shipping_address_2']);
            $boxberry_to_point = $boxberry_to_point[1];
        }
        if ($boxberryDelivery === null) {
            } else {
            $im_id = $boxberryDelivery['im_id'];
            $label = $boxberryDelivery['label'];
        }

        $error = '';

        if (strlen($boxberryDelivery['error']) > 0) {
            $client = new \Boxberry\Client\Client();
            $client->setKey($this->config->get('shipping_boxberry_api_token'));
            $client->setApiUrl($this->config->get('shipping_boxberry_api_url'));

            $parselCreate = $client->getParselCreate();
            $parsel = new  \Boxberry\Models\Parsel();
            $parsel->setOrderId($order_info['order_id']);
            $parsel->setPrice($order_info['total']);

            $order_totals = $this->model_checkout_order->getOrderTotals($order_info['order_id']);


            foreach ($order_totals as $total) {
                $totals[$total['code']] = $total['value'];
            }
            $delivery_cost = isset($totals['shipping']) ?
                $this->currency->convert($totals['shipping'], $order_info['currency_code'], 'RUB') : 0;
            $parsel->setDeliverySum($delivery_cost);
            if (
                $order_info['shipping_code'] == 'boxberry.pickup_prepaid'
            ) {
                $parsel->setPaymentSum(0);
            } else {
                $parsel->setPaymentSum($this->currency->convert($order_info['total'], $order_info['currency_code'], 'RUB'));
            }

            $customer = new \Boxberry\Models\Customer();
            $customer->setFio($receiver);
            $customer->setEmail($email);
            $customer->setPhone($phone);
            $parsel->setCustomer($customer);

            $items = new \Boxberry\Collections\Items();
            $orderItems = $this->model_checkout_order->getOrderProducts($order_info['order_id']);

            foreach ($orderItems as $key => $orderItem) {
                $item = new \Boxberry\Models\Item();
                $item->setId($orderItem['product_id']);
                $item->setName($orderItem['name']);
                $item->setPrice($this->currency->convert($orderItem['price'], $this->config->get('config_currency'), 'RUB'));
                $item->setQuantity($orderItem['quantity']);
                $items[] = $item;
            }
            $parsel->setItems($items);
            $parsel->setWeights(['weight' => $this->weight->convert($this->cart->getWeight(),
                $this->config->get('config_weight_class_id'), $this->config->get('shipping_boxberry_weight_class_id'))]);
            $shop = array(
                'name' => '',
                'name1' => $this->config->get('shipping_boxberry_from_point')
            );

            $parsel->setVid(1);
            if (strlen($boxberry_to_point) === 0) {

            }
            $shop['name'] = $boxberry_to_point;
            $parsel->setShop($shop);
            $parselCreate->setParsel($parsel);

            try {
                $answer = $client->execute($parselCreate);
                $im_id = $answer->getTrack();
                $label = $answer->getLabel();

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            $data = array(
                'order_id' => $order_info['order_id'],
                'im_id' => $im_id,
                'label' => $label,
                'boxberry_to_point' => $boxberry_to_point,
                'address' => $address,
                'error' => $error
            );
            $this->model_boxberry_delivery->addDelivery($data);
        }
    }
}
<?php

use Boxberry\Client\Client;

if (!class_exists('Client')) {
    require_once DIR_SYSTEM . 'library/boxberry/autoload.php';
}


class ShippingBoxberry extends Controller
{
    private $error = [];

    public $fields = [
        'text_enabled',
        'text_disabled',
        'text_api_token',
        'text_api_url',
        'text_status',
        'text_sort_order',
        'text_weight',
        'text_weight_min',
        'text_weight_max',
        'button_save',
        'button_cancel',
        'text_height',
        'text_width',
        'text_depth',
        'text_size',
        'text_pickup_status',
        'text_pickup_prepaid_status',
        'text_courier_delivery_status',
        'text_courier_delivery_prepaid_status',
        'text_pickup_sucrh',
        'text_pickup_prepaid_sucrh',
        'text_courier_delivery_sucrh',
        'text_courier_delivery_prepaid_sucrh',
        'text_order_status'
    ];

    public function index()
    {
        $load = &$this->load;
        $session = &$this->session;
        $request = &$this->request;
        $url = &$this->url;

        $data = array();

        $tokenName = 'user_token';
        if (strpos(VERSION, '2.') === 0) {
            $tokenName = 'token';
        }

        $data['user_token'] = $session->data[$tokenName];

        $data = array_merge($data, $load->language('extension/shipping/boxberry'));

        $language = $this->language;
        $this->document->setTitle($language->get('heading_title'));
        $load->model('setting/setting');
        $load->model('localisation/order_status');

        $data['route'] = "extension/shipping/boxberry";

        if (($request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $load->model('localisation/weight_class');
          /*  if (($weight = $this->model_localisation_weight_class->getWeightClassDescriptionByUnit('g'))
                || ($weight = $this->model_localisation_weight_class->getWeightClassDescriptionByUnit('г'))) {
                $request->post['shipping_boxberry_weight_class_id'] = $weight['weight_class_id'];
            }
            $load->model('localisation/length_class');
            $length = $this->model_localisation_length_class->getLengthClassDescriptionByUnit('cm') ?: $this->model_localisation_length_class->getLengthClassDescriptionByUnit('см');
            if ($length) {
                $request->post['shipping_boxberry_length_class_id'] = $length['length_class_id'];
            }*/


            $client = new Client();
            $client->setKey($request->post['shipping_boxberry_api_token']);
            $client->setApiUrl($request->post['shipping_boxberry_api_url']);
            $keyIntegration = $client->getKeyIntegration();
            try {
                $response = $client->execute($keyIntegration);
                $request->post['shipping_boxberry_widget_key'] = $response->getKeyIntegration();
                $this->model_setting_setting->editSetting('shipping_boxberry', $request->post);
                $this->model_setting_setting->editSetting('boxberry', array(
                    'boxberry_status' => $request->post['shipping_boxberry_status']
                ));
                $session->data['success'] = $language->get('text_success');

                if (strpos(VERSION, '2.') === 0) {
                    $this->response->redirect($url->link("extension/extension", $tokenName . '=' . $data['user_token'] . '&type=shipping', true));
                } else {
                    $this->response->redirect($url->link("marketplace/extension", $tokenName . '=' . $session->data['user_token'] . '&type=shipping', true));
                }

            } catch (Exception $e) {
                if ($e->getMessage()=='Ошибка обращения к сервису доставки Boxberry'){
                    $this->error['warning'] = 'Указан неверный URL API сервиса';
                } elseif ($e->getMessage()=='Нет доступа'){
                    $this->error['warning'] = 'Указан неверный API-токен';
                } elseif ($e->getMessage()=='Ваша учетная запись заблокирована'){
                    $this->error['warning'] = 'Учетная запись с данным API-токеном заблокирована';
                }

            }
        }
        foreach ($this->fields as $field) {
            $data[$field] = $language->get($field);
        }
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['breadcrumbs'] = [
            [
                'text' => $language->get('text_home'),
                'href' => $url->link("common/dashboard", $tokenName . '=' . $data['user_token'] . '&type=shipping', true)
            ],
            [
                'text' => $language->get('text_shipping'),
                'href' => $url->link("extension/extension",
                    $tokenName . '=' . $data['user_token'] . '&type=shipping', true)
            ],
            [
                'text' => $language->get('heading_title'),
                'href' => $url->link("extension/shipping/boxberry", $tokenName . '=' . $data['user_token'] . '&type=shipping', true)
            ]
        ];
        $data['action'] = $url->link("extension/shipping/boxberry", $tokenName . '=' . $data['user_token'] . '&type=shipping', true);
        $data['cancel'] = $url->link("extension/extension", $tokenName . '=' . $data['user_token'] . '&type=shipping', true);
        $this->fields = [
            ['name' => 'shipping_boxberry_api_url', 'default' => isset($request->post['shipping_boxberry_api_url']) ? $request->post['shipping_boxberry_api_url'] : 'https://api.boxberry.ru/json.php'],
            ['name' => 'shipping_boxberry_widget_url', 'default' =>  isset($request->post['shipping_boxberry_widget_url']) ? $request->post['shipping_boxberry_widget_url'] :'https://points.boxberry.de/js/boxberry.js'],
            ['name' => 'shipping_boxberry_api_token', 'default' => isset($request->post['shipping_boxberry_api_token']) ? $request->post['shipping_boxberry_api_token'] : ''],
            ['name' => 'shipping_boxberry_sort_order', 'default' =>isset($request->post['shipping_boxberry_sort_order']) ? $request->post['shipping_boxberry_sort_order'] : ''],
            ['name' => 'shipping_boxberry_status', 'default' => isset($request->post['shipping_boxberry_status']) ? $request->post['shipping_boxberry_status'] : ''],
            ['name' => 'shipping_boxberry_weight', 'default' => isset($request->post['shipping_boxberry_weight']) ? $request->post['shipping_boxberry_weight'] : ''],
            ['name' => 'shipping_boxberry_weight_min', 'default' => isset($request->post['shipping_boxberry_weight_min']) ? $request->post['shipping_boxberry_weight_min'] : ''],
            ['name' => 'shipping_boxberry_weight_max', 'default' => isset($request->post['shipping_boxberry_weight_max']) ? $request->post['shipping_boxberry_weight_max'] : ''],
            ['name' => 'shipping_boxberry_size', 'default' => isset($request->post['shipping_boxberry_size']) ? $request->post['shipping_boxberry_size'] : ''],
            ['name' => 'shipping_boxberry_width', 'default' => isset($request->post['shipping_boxberry_width']) ? $request->post['shipping_boxberry_width'] : ''],
            ['name' => 'shipping_boxberry_height', 'default' => isset($request->post['shipping_boxberry_height']) ? $request->post['shipping_boxberry_height'] : ''],
            ['name' => 'shipping_boxberry_depth', 'default' => isset($request->post['shipping_boxberry_depth']) ? $request->post['shipping_boxberry_depth'] : ''],
            ['name' => 'shipping_boxberry_order_status', 'default' => isset($request->post['shipping_boxberry_order_status']) ? $request->post['shipping_boxberry_order_status'] : ''],
            ['name' => 'shipping_boxberry_weight_class_id', 'default' => isset($request->post['shipping_boxberry_weight_class_id']) ? $request->post['shipping_boxberry_weight_class_id'] :'' ],
            ['name' => 'shipping_boxberry_length_class_id', 'default' => isset($request->post['shipping_boxberry_length_class_id']) ? $request->post['shipping_boxberry_length_class_id'] :'' ],
            ['name' => 'shipping_boxberry_pickup_status', 'default' => isset($request->post['shipping_boxberry_pickup_status']) ? $request->post['shipping_boxberry_pickup_status'] : ''],
            ['name' => 'shipping_boxberry_pickup_prepaid_status', 'default' => isset($request->post['shipping_boxberry_pickup_prepaid_status']) ? $request->post['shipping_boxberry_pickup_prepaid_status'] : ''],
            ['name' => 'shipping_boxberry_courier_delivery_status', 'default' => isset($request->post['shipping_boxberry_courier_delivery_status']) ? $request->post['shipping_boxberry_courier_delivery_status'] : ''],
            ['name' => 'shipping_boxberry_courier_delivery_prepaid_status', 'default' => isset($request->post['shipping_boxberry_courier_delivery_prepaid_status']) ? $request->post['shipping_boxberry_courier_delivery_prepaid_status'] : ''],
            ['name' => 'shipping_boxberry_pickup_sucrh', 'default' =>isset($request->post['shipping_boxberry_pickup_sucrh']) ? $request->post['shipping_boxberry_pickup_sucrh'] : '1'],
            ['name' => 'shipping_boxberry_pickup_prepaid_sucrh', 'default' => isset($request->post['shipping_boxberry_pickup_prepaid_sucrh']) ? $request->post['shipping_boxberry_pickup_prepaid_sucrh'] : '1'],
            ['name' => 'shipping_boxberry_courier_delivery_sucrh', 'default' => isset($request->post['shipping_boxberry_courier_delivery_sucrh']) ? $request->post['shipping_boxberry_courier_delivery_sucrh'] : '1'],
            ['name' => 'shipping_boxberry_courier_delivery_prepaid_sucrh', 'default' => isset($request->post['shipping_boxberry_courier_delivery_prepaid_sucrh']) ? $request->post['shipping_boxberry_courier_delivery_prepaid_sucrh'] : '1'],
            ['name' => 'shipping_boxberry_courier_delivery_prepaid_status', 'default' => isset($request->post['shipping_boxberry_courier_delivery_prepaid_status']) ? $request->post['shipping_boxberry_courier_delivery_prepaid_status'] : ''],
            ['name' => 'shipping_boxberry_pickup_parselcreate_auto', 'default' =>isset($request->post['shipping_boxberry_pickup_parselcreate_auto']) ? $request->post['shipping_boxberry_pickup_parselcreate_auto'] : '1'],
            ['name' => 'shipping_boxberry_pickup_prepaid_parselcreate_auto', 'default' => isset($request->post['shipping_boxberry_pickup_prepaid_parselcreate_auto']) ? $request->post['shipping_boxberry_pickup_prepaid_parselcreate_auto'] : '1'],
            ['name' => 'shipping_boxberry_courier_delivery_parselcreate_auto', 'default' => isset($request->post['shipping_boxberry_courier_delivery_parselcreate_auto']) ? $request->post['shipping_boxberry_courier_delivery_parselcreate_auto'] : '1'],
            ['name' => 'shipping_boxberry_courier_delivery_prepaid_parselcreate_auto', 'default' => isset($request->post['shipping_boxberry_courier_delivery_prepaid_parselcreate_auto']) ? $request->post['shipping_boxberry_courier_delivery_prepaid_parselcreate_auto'] : '1'],
            ['name' => 'shipping_boxberry_pickup_parselsend_auto', 'default' =>isset($request->post['shipping_boxberry_pickup_parselsend_auto']) ? $request->post['shipping_boxberry_pickup_parselsend_auto'] : ''],
            ['name' => 'shipping_boxberry_pickup_prepaid_parselsend_auto', 'default' => isset($request->post['shipping_boxberry_pickup_prepaid_parselsend_auto']) ? $request->post['shipping_boxberry_pickup_prepaid_parselsend_auto'] : ''],
            ['name' => 'shipping_boxberry_courier_delivery_parselsend_auto', 'default' => isset($request->post['shipping_boxberry_courier_delivery_parselsend_auto']) ? $request->post['shipping_boxberry_courier_delivery_parselsend_auto'] : ''],
            ['name' => 'shipping_boxberry_courier_delivery_prepaid_parselsend_auto', 'default' => isset($request->post['shipping_boxberry_courier_delivery_prepaid_parselsend_auto']) ? $request->post['shipping_boxberry_courier_delivery_prepaid_parselsend_auto'] : ''],
            ['name' => 'shipping_boxberry_pickup_name', 'default' => isset($request->post['shipping_boxberry_pickup_name']) ? $request->post['shipping_boxberry_pickup_name'] : $language->get('text_pickup_description')],
            ['name' => 'shipping_boxberry_pickup_prepaid_name', 'default' => isset($request->post['shipping_boxberry_pickup_prepaid_name']) ? $request->post['shipping_boxberry_pickup_prepaid_name'] : $language->get('text_pickup_prepaid_description')],
            ['name' => 'shipping_boxberry_courier_delivery_name', 'default' => isset($request->post['shipping_boxberry_courier_delivery_name']) ? $request->post['shipping_boxberry_courier_delivery_name'] : $language->get('text_courier_delivery_description')],
            ['name' => 'shipping_boxberry_courier_delivery_prepaid_name', 'default' => isset($request->post['shipping_boxberry_courier_delivery_prepaid_name']) ? $request->post['shipping_boxberry_courier_delivery_prepaid_name'] : $language->get('text_courier_delivery_prepaid_description')]

        ];
        $config = $this->config;
        foreach ($this->fields as $field) {
            $fieldName = $field['name'];
            $configValue = $config->get($fieldName);
            $data[$fieldName] = isset($request->post[$fieldName]) ? $request->post[$fieldName] : null;
            $data[$fieldName] = $data[$fieldName] === null ? $configValue : null;
            $data[$fieldName] = $data[$fieldName] === null ? $field['default'] : $data[$fieldName];
        }
        $load->model('localisation/weight_class');
        $load->model('localisation/length_class');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['header'] = $load->controller('common/header');
        $data['column_left'] = $load->controller('common/column_left');
        $data['footer'] = $load->controller('common/footer');
        $data['weight_ids'] = $this->model_localisation_weight_class->getWeightClasses();
        $data['length_ids'] = $this->model_localisation_length_class->getLengthClasses();

        $this->response->setOutput($load->view("extension/shipping/boxberry", $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', "extension/shipping/boxberry")) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!$this->request->post['shipping_boxberry_api_token']) {
            $this->error['warning'] = $this->language->get('error_api_key');
        }
        if (!$this->request->post['shipping_boxberry_api_url']) {
            $this->error['warning'] = 'Не указан Url для API сервиса!';
        }
        if (!$this->request->post['shipping_boxberry_weight']) {
            $this->error['warning'] = 'Не указан вес по умолчанию!';
        }
        if (!$this->request->post['shipping_boxberry_widget_url']) {
            $this->error['warning'] = 'Не указан Url для виджета';
        }
        return !$this->error;
    }

    public function install()
    {
        $load = $this->load;

        $load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'sale/boxberry');

        if (strpos(VERSION, '2.') === 0) {
            $this->load->model('extension/event');
            $this->model_extension_event->deleteEvent('boxberry_order_status_edit');
            $this->model_extension_event->addEvent(
                'boxberry_order_status_edit',
                'catalog/model/checkout/order/addOrderHistory/after',
                'event/boxberry/addOrderHistory',
                1,
                1
            );
        } else {
            $load->model('setting/event');
            $this->model_setting_event->deleteEvent('boxberry_order_status_edit');
            $this->model_setting_event->addEvent(
                'boxberry_order_status_edit',
                'catalog/model/checkout/order/addOrderHistory/after',
                'event/boxberry/addOrderHistory',
                1,
                1
            );
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "boxberry_cities` (
      `code` VARCHAR(128) NOT NULL,
      `name` VARCHAR(128),
      `region` VARCHAR(128),
      `data` text,
      PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "boxberry_deliveries` (
      `order_id` int(10) NOT NULL,      
      `im_id` VARCHAR(255),
      `label` VARCHAR(255),
      `boxberry_to_point` VARCHAR(15),
      `address` VARCHAR(255),
      `error` VARCHAR(255),
      PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "boxberry_points` (
      `code` VARCHAR(128) NOT NULL,
      `city_code` VARCHAR(128),
      `data` text,
      `expired` datetime,
      `prepaid` tinyint(1),
      PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "boxberry_listzips` (
      `zip` VARCHAR(128) NOT NULL,
      `city` VARCHAR(128),
      `area` VARCHAR(128),
      PRIMARY KEY (`zip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "boxberry_expired` (
      `table` VARCHAR(128) NOT NULL,
      `expired` datetime,
      PRIMARY KEY (`table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;");

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "boxberry_zips`;");
    }

    public function uninstall()
    {
        $this->load->model('user/user_group');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'sale/boxberry');

        if (strpos(VERSION, '2.') === 0) {
            $this->load->model('extension/event');
            $this->model_extension_event->deleteEvent('boxberry_order_status_edit');
        } else {
            $this->load->model('setting/event');
            $this->model_setting_event->deleteEvent('boxberry_order_status_edit');
        }

        $db = $this->db;
        $db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "boxberry_cities`;");
        $db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "boxberry_deliveries`;");
        $db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "boxberry_points`;");
        $db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "boxberry_zips`;");
        $db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "boxberry_listzips`;");
        $db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "boxberry_expired`;");
    }
}

class ControllerExtensionShippingBoxberry extends ShippingBoxberry
{
}

class ControllerShippingBoxberry extends ShippingBoxberry
{
}

?>
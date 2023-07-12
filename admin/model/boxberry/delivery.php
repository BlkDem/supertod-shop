<?php

class ModelBoxberryDelivery extends Model
{
    public function getDelivery($order)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_deliveries WHERE `order_id` = '" . (int)$order . "'");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return null;
        }
    }

    public function getDeliveries($filter_data)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "boxberry_deliveries ";
        if (isset($filter_data['filter_order_id']) && $filter_data['filter_order_id']) {
            $sql .= "WHERE order_id=" . (int)$filter_data['filter_order_id'];
        }
        if (isset($filter_data['sort']) && $filter_data['sort']) {
            $order = 'ASC';
            if (isset($filter_data['order']) && ($filter_data['order'] == 'DESC')) {
                $order = 'DESC';
            }
            $sql .= " ORDER BY " . $this->db->escape($filter_data['sort']) . " " . $order;
        }
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getProducts($order_id)
    {
        $query = $this->db->query("SELECT 
" . DB_PREFIX . "order_product.quantity, 
" . DB_PREFIX . "order_product.price, 
" . DB_PREFIX . "order_product.total,
" . DB_PREFIX . "product.shipping,
" . DB_PREFIX . "product.weight,
" . DB_PREFIX . "product.weight_class_id,
" . DB_PREFIX . "product.length,
" . DB_PREFIX . "product.width,
" . DB_PREFIX . "product.height,
" . DB_PREFIX . "product.length_class_id,
" . DB_PREFIX . "product.tax_class_id,
" . DB_PREFIX . "product.quantity as product_quantity,
" . DB_PREFIX . "order.shipping_city,
" . DB_PREFIX . "order.shipping_zone,
" . DB_PREFIX . "order.shipping_code
 FROM " . DB_PREFIX . "order_product INNER JOIN " . DB_PREFIX . "product
             ON " . DB_PREFIX . "product.product_id=" . DB_PREFIX . "order_product.product_id
              INNER JOIN " . DB_PREFIX . "order ON " . DB_PREFIX . "order.order_id=" . DB_PREFIX . "order_product.order_id WHERE " . DB_PREFIX . "order_product.order_id = " . (int)$order_id);

        return $query->rows;
    }

    public function addDelivery($data)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "boxberry_deliveries` SET 
        order_id = '" . $this->db->escape($data['order_id'])
            . "', im_id = '" . $this->db->escape($data['im_id'])
            . "', label = '" . $this->db->escape($data['label'])
            . "', boxberry_to_point = '" . $this->db->escape($data['boxberry_to_point'])
            . "', address = '" . $this->db->escape($data['address'])
            . "', error = '" . $this->db->escape($data['error'])
            . "' ON DUPLICATE KEY UPDATE 
            im_id = '" . $this->db->escape($data['im_id'])
            . "', label = '" . $this->db->escape($data['label'])
            . "', boxberry_to_point = '" . $this->db->escape($data['boxberry_to_point'])
            . "', address = '" . $this->db->escape($data['address'])
            . "', error = '" . $this->db->escape($data['error'])
            . "'");
        $delivery_id = $this->db->getLastId();

        return $delivery_id;
    }
}
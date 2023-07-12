<?php

class ModelBoxberryDelivery extends Model
{
    public function getDelivery($order)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_deliveries WHERE `order_id` = '" . $this->db->escape($order) . "'");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return null;
        }
    }

    public function getDeliveries()
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_deliveries ");

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
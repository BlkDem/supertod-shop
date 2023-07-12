<?php

class ModelBoxberryPoint extends Model
{
    public function getPoint($code, $prepaid)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_points WHERE `code` = '" . $this->db->escape($code) . "' and `prepaid` = " . $prepaid);
        if ($query->num_rows) {
            return $query->row;
        } else {
            return null;
        }
    }

    public function getPointData($code)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_points WHERE `code` = '" . $this->db->escape($code) . "'");
        if ($query->num_rows) {
            return json_decode($query->row['data'], 1);
        } else {
            return null;
        }
    }

    public function getPointByCity($code, $prepaid = 0)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_points WHERE `city_code` = '" . $this->db->escape($code) . "'
         and `prepaid` = " . $prepaid);
        if ($query->num_rows) {
            return $query->row;
        } else {
            return null;
        }
    }

    public function getPoints($city, $prepaid)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_points WHERE `city_code` = '" . $this->db->escape($city) . "'
                and `prepaid` = " . $prepaid);

        return $query->num_rows;
    }

    public function addPoint($data)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "boxberry_points` SET 
        code = '" . $this->db->escape($data['code'])
            . "', city_code = '" . $this->db->escape($data['city_code'])
            . "', data = '" . $this->db->escape(json_encode($data['data'], JSON_UNESCAPED_UNICODE))
            . "', expired = NOW() + INTERVAL 1 DAY"
            . ", prepaid = " . $data['prepaid']
            . " ON DUPLICATE KEY UPDATE 
        city_code = '" . $this->db->escape($data['city_code'])
            . "', data = '" . $this->db->escape(json_encode($data['data'], JSON_UNESCAPED_UNICODE))
            . "', expired = NOW() + INTERVAL 1 DAY"
            . ", prepaid = " . $data['prepaid']);
        $point_id = $this->db->getLastId();

        return $point_id;
    }
}
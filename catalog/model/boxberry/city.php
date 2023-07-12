<?php

class ModelBoxberryCity extends Model
{
    public function getCity($code)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_city WHERE `code` = '" . $this->db->escape($code) . "'");
        if ($query->num_rows) {
            $data = $query->row;
            $data['data'] = json_decode($data['data'], 1);
            return $data;
        } else {
            return null;
        }
    }

    public function getCities()
    {
        $data = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_cities ");
        foreach ($query->rows as $result) {
            $data[$result['code']] = json_decode($result['data'], 1);
        }

        return $data;
    }

    public function addCity($data)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "boxberry_cities` SET 
        code = '" . $this->db->escape($data['code'])
            . "', name = '" . $this->db->escape($data['name'])
            . "', region = '" . $this->db->escape($data['region'])
            . "', data = '" . json_encode($data['data'], JSON_UNESCAPED_UNICODE)
            . "' ON DUPLICATE KEY UPDATE 
            name = '" . $this->db->escape($data['name'])
            . "', region = '" . $this->db->escape($data['region'])
            . "', data = '" . json_encode($data['data'], JSON_UNESCAPED_UNICODE)
            . "'");
        $city_id = $this->db->getLastId();

        return $city_id;
    }

    public function getCityByName($city, $region)
    {
        $data = null;
        $region = $region ? str_replace(' область', '', $region) : '';
        $sql = "SELECT * FROM " . DB_PREFIX . "boxberry_cities WHERE name='" . $this->db->escape($city) . "'";
        if ($region) {
            $sql .= " AND region='" . $region . "'";
        }
        $query = $this->db->query($sql);
        if ($query->num_rows) {
            $data = $query->row;
            $data['data'] = json_decode($data['data'], 1);

            return $data;
        } else {
            if ($region) {
                $sql = "SELECT * FROM " . DB_PREFIX . "boxberry_cities WHERE name='" . $this->db->escape($city) . "'";
                $query = $this->db->query($sql);
                if ($query->num_rows) {
                    $data = $query->row;
                    $data['data'] = json_decode($data['data'], 1);
                    return $data;
                }
            }
        }

        return $data;
    }
}
<?php

class ModelBoxberryZip extends Model
{
    public function getZip($zip)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_listzips WHERE `zip` = '" . $this->db->escape($zip) . "'");
        if ($query->num_rows) {
            return $query->row;
        } else {
            return null;
        }
    }

    public function getZips()
    {
        $data = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_listzips");
        foreach ($query->rows as $result) {
            $data[$result['zip']] = $result;
        }

        return $data;
    }

    public function addZip($data)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "boxberry_listzips` SET 
        zip = '" . $this->db->escape($data['zip'])
            . "', city = '" . $this->db->escape($data['city'])
            . "', area = '" . $this->db->escape($data['area'])
            . "' ON DUPLICATE KEY UPDATE 
            city = '" . $this->db->escape($data['city'])
            . "', area = '" . $this->db->escape($data['area'])
            . "'");
        $zip_id = $this->db->getLastId();

        return $zip_id;
    }
    public function getZipByCity($city, $region)
    {
        $data = null;
        $region = $region ? str_replace(' область', '', $region) : '';
        $sql = "SELECT * FROM " . DB_PREFIX . "boxberry_listzips WHERE city='" . $this->db->escape($city) . "'";
        if ($region) {
            $sql .= " AND area='" . $region . "'";
        }
        $query = $this->db->query($sql);
        if ($query->num_rows) {
            $data = $query->row;
            return $data;
        } else {
            if ($region) {
                $sql = "SELECT * FROM " . DB_PREFIX . "boxberry_listzips WHERE city='" . $this->db->escape($city) . "'";
                $query = $this->db->query($sql);
                if ($query->num_rows) {
                    $data = $query->row;
                    return $data;
                }
            }
        }

        return $data;
    }

}
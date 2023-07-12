<?php

class ModelBoxberryExpired extends Model
{
    public function getExpired($table)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "boxberry_expired WHERE `table` = '" . $this->db->escape($table) . "'");
        if ($query->num_rows) {
            return $query->row['expired'];
        } else {
            return null;
        }
    }

    public function addExpired($data)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "boxberry_expired` SET 
        `table` = '" . $this->db->escape($data['table'])
            . "', expired = NOW() + INTERVAL 1 DAY"
            . " ON DUPLICATE KEY UPDATE expired = NOW() + INTERVAL 1 DAY");
        $date_id = $this->db->getLastId();

        return $date_id;
    }
}
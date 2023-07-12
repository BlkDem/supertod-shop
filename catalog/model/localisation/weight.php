<?php
class ModelLocalisationWeight extends Model
{
    public function getWeightClassDescriptionByUnit($unit)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "weight_class_description WHERE unit = '" . $this->db->escape($unit) . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }
}
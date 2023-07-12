<?php //0046b
use Boxberry\Client\Client;

if (!class_exists('Client')) {
    require_once DIR_SYSTEM . 'library/boxberry/autoload.php';
}

class ModelExtensionShippingBoxberry extends Model
{
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
}
<?php
namespace GixOC;
class PostPrice {
	const URL = 'https://postprice.ru/engine/';

	protected $mass = 0;
	protected $vat = 0;

	private $from = 0;
	private $to = 0;
	private $valuation = 0;
	private $oversized = 0;

	public function __construct($mass, $vat = 1) {
		$this->mass = $mass;
		$this->vat = $vat;
	}

	public function setTo($to) {
		$this->to = $to;
	}

	public function setOversized($oversized) {
		$this->oversized = $oversized;
	}

	public function setFrom($from) {
		$this->from = $from;
	}

	public function setValuation($valuation) {
		$this->valuation = $valuation;
	}

	public function getRussia(){
		$response = array();

		if (!$this->from) {
			return array('error' => 10);
		}

		if (!$this->to) {
			return array('error' => 11);
		}

		$url = 'russia/api.php?from=' . $this->from . '&to=' . $this->to . '&mass=' . $this->mass;

		if ($this->valuation) {
			$url .= '&valuation=' . $this->valuation;
		}

		if ($this->vat) {
			$url .= '&vat=' . $this->vat;
		}

		if ($this->oversized) {
			$url .= '&oversized=' . $this->oversized;
		}

		$response = $this->call($url);
		return $response;
	}

	private function call($url) {
		$curl = curl_init();

		$options = [
			CURLOPT_URL => self::URL . $url,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => null,
			CURLOPT_POSTFIELDS => null,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 5,
		];

		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);

		if (!$response) {
			return array('error' => 1);
		}

		$result = json_decode($response, true);
		if (isset($result['code']) && ($result['code'] == 100)) {
			return array('success' => $result);
		} elseif (isset($result['code']) && ($result['code'] != 100)) {
			return array('error' => $result['code'], 'data' => $result);
		} else {
			return array('error' => 2, 'data' => $result);
		}

		curl_close($curl);
		return false;
	}
}
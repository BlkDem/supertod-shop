<?php
/**
 *
 *  * This file is part of Boxberry Api.
 *  *
 *  * (c) 2016, T. I. R. Ltd.
 *  * Evgeniy Mosunov, Alexander Borovikov
 *  *
 *  * For the full copyright and license information, please view LICENSE
 *  * file that was distributed with this source code
 *  *
 *  * File: City.php
 *  * Created: 26.07.2016
 *  *
 */

namespace Boxberry\Models;
use Boxberry\Collections\ListCitiesCollection;

/**
 * Class City
 * @package Boxberry\Models
 */
class City extends AbstractModel
{
    /**
     * @var string $name
     */
    protected $name = null;
    /**
     * @var string $name
     */
    protected $code = null;
    protected $region = null;
    protected $country_code = null;
    protected $uniq_name = null;
    protected $pickup_point = null;
    protected $courier_delivery = null;
    protected $data = null;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function setRegion($region)
    {
        $this->region = $region;
    }

    public function getCountryCode()
    {
        return $this->country_code;
    }

    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
    }

    public function getUniqName()
    {
        return $this->uniq_name;
    }

    public function setUniqName($uniq_name)
    {
        $this->uniq_name = $uniq_name;
    }

    public function getPickupPoint()
    {
        return $this->pickup_point;
    }

    public function setPickupPoint($pickup_point)
    {
        $this->pickup_point = $pickup_point;
    }

    public function getCourierDelivery()
    {
        return $this->courier_delivery;
    }

    public function setCourierDelivery($courier_delivery)
    {
        $this->courier_delivery = $courier_delivery;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
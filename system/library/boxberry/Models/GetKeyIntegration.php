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
 *  * File: ParselCreateResponse.php
 *  * Created: 26.07.2016
 *  *
 */

namespace Boxberry\Models;


/**
 * Class ParselCreateResponse
 * @package Boxberry\Client
 */
class GetKeyIntegration extends AbstractModel
{
    protected $key_integration = '';

    public function __construct($data = null)
    {
        if ($data === null) {
            return;
        }
            foreach ($data as $propertyName => $propertyValue) {
                $methodName = 'set_' . $propertyName;
                if (method_exists($this, $methodName)) {
                    $this->$methodName($propertyValue);
                }
            }
    }

    public function getKeyIntegration()
    {
        return $this->key_integration;
    }

    public function set_key($key)
    {
        $this->key_integration = $key;
    }
}
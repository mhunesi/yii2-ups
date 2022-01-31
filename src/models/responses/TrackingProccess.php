<?php
namespace mhunesi\ups\models\responses;

use yii\base\Model;

class TrackingProccess extends Model
{    
    /**
     * Date
     *
     * @var string
     */
    public $Date;

    /**
     * StatusCode
     *
     * @var string
     */
    public $StatusCode;

    /**
     * Description
     *
     * @var string
     */
    public $Description;

    /**
     * Location
     *
     * @var string
     */
    public $Location;
}
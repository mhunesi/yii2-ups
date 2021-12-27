<?php

namespace mhunesi\ups\models\requests;

use yii\base\Model;

class UpsShipmentModel extends Model
{
    /**
     * SessionID
     *
     * @var string login servisinden alınan session id
     */
    public $SessionID;

    /**
     * ShipmentInfo
     *
     * @var ShipmentInfo
     */
    public $ShipmentInfo;

    /**
     * ReturnLabelLink
     *
     * @var bool
     */
    public $ReturnLabelLink;

    /**
     * ReturnLabelImage
     *
     * @var bool
     */
    public $ReturnLabelImage;

    /**
     * PaperSize
     *
     * @var string
     */
    public $PaperSize;

}
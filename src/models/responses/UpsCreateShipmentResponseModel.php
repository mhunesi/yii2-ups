<?php

namespace mhunesi\ups\models\responses;

class UpsCreateShipmentResponseModel extends UpsBaseResponseModel
{
    public $CargoTrackingNo;

    /**
     * labelReturnType
     *
     * @var string
     */
    public $LabelReturnType;

    /**
     * labelImage
     *
     * @var string
     */
    public $LabelImage;

    /**
     * labelImageType
     *
     * @var string
     */
    public $LabelImageType;

    /**
     * labelUrl
     *
     * @var string
     */
    public $LabelUrl;
}
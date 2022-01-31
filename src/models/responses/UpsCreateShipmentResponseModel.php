<?php

namespace mhunesi\ups\models\responses;

class UpsCreateShipmentResponseModel extends UpsBaseResponseModel
{
    public $CargoTrackingNo;

    /**
     * labelImage
     *
     * @var array
     */
    public $LabelZpl;

    /**
     * labelImage
     *
     * @var array
     */
    public $LabelPng;

    /**
     * labelUrl
     *
     * @var string
     */
    public $LabelUrl;
}
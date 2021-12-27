<?php

namespace mhunesi\ups\models\responses;

class UpsTrackingResponseModel extends UpsBaseResponseModel
{
    /**
     * TrackingNo
     *
     * @var string
     */
    public $TrackingNo;

    /**
     * CargoCompany
     *
     * @var string
     */
    public $CargoCompany;

    /**
     * trackingHistory
     *
     * @var array
     */
    public $TrackingHistory;
}
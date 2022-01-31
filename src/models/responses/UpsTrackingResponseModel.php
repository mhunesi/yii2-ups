<?php

namespace mhunesi\ups\models\responses;

class UpsTrackingResponseModel extends UpsBaseResponseModel
{
    /**
     * TrackingNo
     *
     * @var string
     */
    public $TrackingNumber;

    /**
     * trackingHistory
     *
     * @var array
     */
    public $TrackingHistory;
}
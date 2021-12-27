<?php

namespace mhunesi\ups\models\responses;

use yii\base\Model;

class UpsBaseResponseModel extends Model
{
    /**
     * status
     *
     * @var bool requesting başarılı olup olmadığı bilgisi
     */
    public $status;

    /**
     * statusCode
     *
     * @var int htto response status code
     */
    public $statusCode;

    /**
     * errorCode
     *
     * @var string servisten http haricinde dönen spesifik
     * bir hata kodu varsa bu alana set edilecektir
     */
    public $errorCode;

    /**
     * errorMessage
     *
     * @var string hata mesajı
     */
    public $errorMessage;

    /**
     * response
     *
     * @var object success durumda dönen response body
     */
    public $response;

    /**
     * requestAsXML
     *
     * @var string
     */
    public $requestAsXML;

    /**
     * responseAsXML
     *
     * @var string
     */
    public $responseAsXML;

    /**
     * client
     *
     * @var GuzzleHttp\Client|SoapClient
     */
    public $client;

}
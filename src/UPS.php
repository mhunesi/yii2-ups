<?php

namespace mhunesi\ups;

use DateTime;
use mhunesi\ideasoft\models\requests\invoices\TrackingHistory;
use mhunesi\ups\models\requests\UpsShipmentModel;
use mhunesi\ups\models\responses\TrackingProccess;
use mhunesi\ups\models\responses\UpsBaseResponseModel;
use mhunesi\ups\models\responses\UpsCreateShipmentResponseModel;
use mhunesi\ups\models\responses\UpsTrackingResponseModel;
use mhunesi\ups\models\types\LabelImageType;
use mhunesi\ups\models\types\LabelReturnType;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * This is just an example.
 */
class UPS extends Component
{
    /**
     * CustomerNumber
     *
     * @var string
     */
    public $CustomerNumber;

    /**
     * Username
     *
     * @var string
     */
    public $Username;

    /**
     * Password
     *
     * @var string
     */
    public $Password;
    /**
     * IsTestInstance
     *
     * @var string
     */
    public $IsTestInstance;

    /**
     * soapClient
     *
     * @var \SoapClient
     */
    protected $soapClient;

    /**
     * sessionID
     *
     * @var string
     */
    protected $sessionID;

    /**
     * base_url_test
     *
     * @var string
     */
    private $base_url_test = "https://ws.ups.com.tr/wsCreateShipmenttest/wsCreateShipment.asmx?wsdl";

    /**
     * base_url
     *
     * @var string
     */
    private $base_url = "http://ws.ups.com.tr/wsCreateShipment/wsCreateShipment.asmx?wsdl";

    /**
     * trackingUrl
     *
     * @var string
     */
    private $trackingUrl = "https://ws.ups.com.tr/QueryPackageInfo/wsQueryPackagesInfo.asmx?wsdl";

    /**
     * @throws \SoapFault
     */
    public function init()
    {
        $url = $this->IsTestInstance ? $this->base_url_test : $this->base_url;
        $this->prepareClient($url);
        $this->sessionID = $this->login();
    }

    /**
     * @param $url
     * @throws \SoapFault
     */
    private function prepareClient($url)
    {
        $context = stream_context_create(array(
            'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
        ));

        $this->soapClient = new \SoapClient($url, [
            'cache_wsdl' => WSDL_CACHE_NONE,
            'trace' => true,
            'stream_context' => $context,
        ]);
    }

    /**
     * @return mixed
     */
    public function login()
    {
        $login = [
            "CustomerNumber" => $this->CustomerNumber,
            "UserName" => $this->Username,
            "Password" => $this->Password
        ];
        $session = $this->soapClient->Login_Type1($login);
        return $session->Login_Type1Result->SessionID;
    }

    /**
     * @return string
     */
    public function getSessionID()
    {
        return $this->sessionID;
    }

    /**
     * @param UpsShipmentModel $shipmentRequest
     * @return UpsCreateShipmentResponseModel
     */
    public function createShipment(UpsShipmentModel $shipmentRequest): UpsCreateShipmentResponseModel
    {
        $response = new UpsCreateShipmentResponseModel();

        try {
            $shipment = ArrayHelper::toArray($shipmentRequest);
            $_response = $this->soapClient->CreateShipment_Type3_ZPL_Types($shipment);
            if ($_response->CreateShipment_Type3_ZPL_TypesResult->ErrorCode != 0) {
                throw new \Exception($_response->CreateShipment_Type3_ZPL_TypesResult->ErrorDefinition,
                    $response->CreateShipment_Type3_ZPL_TypesResult->ErrorCode);
            }
            $response->status = true;
            $response->response = $_response;
            $response->CargoTrackingNo = $_response->CreateShipment_Type3_ZPL_TypesResult->ShipmentNo;
            $response->LabelReturnType = LabelReturnType::IMAGE;
            $response->LabelImageType = LabelImageType::ZPL;
            $response->LabelImage = $_response->CreateShipment_Type3_ZPL_TypesResult->ZplResult;
            //$response->labelImage=$response->CreateShipment_Type3_ZPL_TypesResult->BarkodArrayPng;

        } catch (\Throwable $th) {
            $response->status = false;
            $response->errorMessage = $th->getMessage();
            $response->statusCode = $th->getCode();
        } catch (\SoapFault $th) {
            $response->status = false;
            $response->errorMessage = $th->getMessage();
            $response->statusCode = $th->getCode();

        } finally {
            $response->requestAsXML = $this->soapClient->__getLastRequest();
            $response->responseAsXML = $this->soapClient->__getLastResponse();
            $response->client = $this->soapClient;
            return $response;
        }

    }

    public function cancelShipment($customerReference, $cargoTrackingNumber)
    {
        $cancel = [
            "sessionId" => $this->sessionID,
            "customerCode" => $customerReference,
            "waybillNumber" => $cargoTrackingNumber,
        ];

        $response = new UpsBaseResponseModel();

        try {
            $soapResponse = $this->soapClient->Cancel_Shipment_V1($cancel);
            if (isset($soapResponse->Cancel_Shipment_V1Result->ErrorDefinition)) {
                throw new \Exception($soapResponse->Cancel_Shipment_V1Result->ErrorDefinition);
            }
            $response->status = true;
        } catch (\SoapFault $th) {
            $response->status = false;
            $response->errorMessage = $th->getMessage();
            $response->statusCode = $th->getCode();

        } catch (\Exception $th) {
            $response->status = false;
            $response->errorMessage = $th->getMessage();
            $response->statusCode = $th->getCode();

        } finally {
            $response->requestAsXML = $this->soapClient->__getLastRequest();
            $response->responseAsXML = $this->soapClient->__getLastResponse();
            $response->client = $this->soapClient;
            return $response;
        }
    }

    public function tracking($cargoTrackingNumber)
    {
        $this->prepareClient($this->trackingUrl);

        $response = new UpsTrackingResponseModel();

        try {
            $cancel = [
                "SessionID" => $this->sessionID,
                "InformationLevel" => 10,
                "TrackingNumber" => $cargoTrackingNumber
            ];
            $_response = $this->soapClient->GetTransactionsByTrackingNumber_V1($cancel);
            $trackingHistory = [];
            foreach ($_response->GetTransactionsByTrackingNumber_V1Result->PackageTransaction as $key => $tracking) {
                $datetime = new DateTime(str_replace("-", "", $tracking->ProcessTimeStamp));
                $trackingHistory[] = new TrackingProccess([
                    "Date" => $datetime->format("Y-m-d"),
                    "Time" => $datetime->format("H:i:s"),
                    "StatusCode" => $tracking->StatusCode,
                    "Description" => $tracking->ProcessDescription1
                ]);
            }
            $response->TrackingHistory = $trackingHistory;

        } catch (\SoapFault $th) {
            $response->status = false;
            $response->errorMessage = $th->getMessage();
            $response->statusCode = $th->getCode();
        } catch (\Throwable $th) {
            $response->status = false;
            $response->errorMessage = $th->getMessage();
            $response->statusCode = $th->getCode();
        } finally {
            $response->requestAsXML = $this->soapClient->__getLastRequest();
            $response->responseAsXML = $this->soapClient->__getLastResponse();
            $response->client = $this->soapClient;
            return $response;
        }
    }
}

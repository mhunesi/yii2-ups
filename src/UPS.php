<?php

namespace mhunesi\ups;

use DateTime;
use mhunesi\ups\models\requests\UpsShipmentModel;
use mhunesi\ups\models\responses\TrackingProccess;
use mhunesi\ups\models\responses\UpsBaseResponseModel;
use mhunesi\ups\models\responses\UpsCreateShipmentResponseModel;
use mhunesi\ups\models\responses\UpsTrackingResponseModel;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * This is just an example.
 * Test Url : https://ws.ups.com.tr/wsCreateShipmenttest/wsCreateShipment.asmx?wsdl
 */
class UPS extends Component
{
    /**
     * CustomerNumber
     *
     * @var string
     */
    public $customerNumber;

    /**
     * Username
     *
     * @var string
     */
    public $username;

    /**
     * Password
     *
     * @var string
     */
    public $password;

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
     * @var string
     */
    public $apiUrl = "http://ws.ups.com.tr/wsCreateShipment/wsCreateShipment.asmx?wsdl";

    /**
     * trackingUrl
     * @var string
     */
    public $trackingApiUrl = "https://ws.ups.com.tr/QueryPackageInfo/wsQueryPackagesInfo.asmx?wsdl";

    /**
     * @throws \SoapFault
     */
    public function init()
    {
        $this->prepareClient($this->apiUrl);
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
            "CustomerNumber" => $this->customerNumber,
            "UserName" => $this->username,
            "Password" => $this->password
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
            if ((int)$_response->CreateShipment_Type3_ZPL_TypesResult->ErrorCode !== 0) {
                throw new \Exception($_response->CreateShipment_Type3_ZPL_TypesResult->ErrorDefinition,
                    $_response->CreateShipment_Type3_ZPL_TypesResult->ErrorCode);
            }
            $response->status = true;
            $response->response = $_response;
            $response->CargoTrackingNo = $_response->CreateShipment_Type3_ZPL_TypesResult->ShipmentNo;
            $response->LabelZpl = (array)$_response->CreateShipment_Type3_ZPL_TypesResult->ZplResult->string;
            $response->LabelPng =(array)($_response->CreateShipment_Type3_ZPL_TypesResult->BarkodArrayPng->string ?? []);
            $response->LabelUrl = $_response->CreateShipment_Type3_ZPL_TypesResult->LinkForLabelPrinting;
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

    public function tracking(string $cargoTrackingNumber)
    {
        $this->prepareClient($this->trackingApiUrl);

        $response = new UpsTrackingResponseModel();

        try {
            $data = [
                "SessionID" => $this->sessionID,
                "InformationLevel" => 10,
                "TrackingNumber" => $cargoTrackingNumber
            ];
            $_response = $this->soapClient->GetTransactionsByTrackingNumber_V1($data);

            $trackingHistory = [];

            foreach ($_response->GetTransactionsByTrackingNumber_V1Result->PackageTransaction as $key => $tracking) {
                $datetime = new DateTime(str_replace("-", "", $tracking->ProcessTimeStamp));
                $trackingHistory[] = new TrackingProccess([
                    "Date" => $datetime->format("Y-m-d H:i:s"),
                    "StatusCode" => $tracking->StatusCode,
                    "Description" => $this->prepareDescription($tracking),
                    "Location" => $tracking->OperationBranchName,
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

    public function trackingList($cargoTrackingNumbers, $trnType = 'ALL_TRANSACTIONS',$referansType = 'WAYBILL_TYPE')
    {
        $this->prepareClient($this->trackingApiUrl);

        $response = new UpsTrackingResponseModel();

        try {
            $data = [
                "SessionID" => $this->sessionID,
                "InformationLevel" => 10,
                'trnType' => $trnType,
                "refList" => [
                    'referansType' => $referansType,
                    'referansList' => $cargoTrackingNumbers
                ]
            ];
            $_response = $this->soapClient->GetTransactionsByList_V2($data);

            $trackingHistory = [];

            foreach (array_filter($_response->GetTransactionsByList_V2Result->PackageTransactionwithDeliveryDetailV2) as $key => $tracking) {
                $datetime = new DateTime(str_replace("-", "", $tracking->ProcessTimeStamp));

                $trackingHistory[trim($tracking->TrackingNumber)][] = new TrackingProccess([
                    "Date" => $datetime->format("Y-m-d H:i:s"),
                    "StatusCode" => $tracking->StatusCode,
                    "Description" => $this->prepareDescription($tracking),
                    "Location" => $tracking->OperationBranchName,
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

    private function prepareDescription($process)
    {
        switch ((int)$process->StatusCode) {
            case 31 :
                return "ÇAĞRI SONUCU ALINDI";
                break;
            case 6 :
                return $process->ProcessDescription2;
                break;
            case 5 :
                return "KURYE GERİ GETİRDİ ({$process->ProcessDescription2}})";
                break;
            case 4 :
                return "KURYE DAĞITMAK ÜZERE ÇIKARDI ({$process->ProcessDescription2}})";
                break;
            case 3 :
                return "ILERIKI BIR TARIHTE TESLIMAT ICIN BEKLETILIYOR";
                break;
            case 2 :
                return "ALICIYA TESLİM EDİLDİ: {$process->SignedPersonName} {$process->SignedPersonSurname} ({$process->SignedPersonRelation})	";
                break;
        }

        throw new \Exception("Unknown StatusCode : {$process->StatusCode}");
    }
}

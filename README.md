Yii2 UPS Integration
====================
Ups Kargo şirketi ile kargo entegrasyonlarının gerçekleşmesi için gerekli imlementasyonun sağlanmasını amaçlanmıştır.

## 1 - Component Olarak eklenmesi

````php
'ups' => [
    'class' => 'mhunesi\ups\UPS',
    'customerNumber' => 'customerNumber',
    'username' => 'username',
    'password' => 'password',
    'isTestInstance' => true  
]
````

## 2- Nesne üretimi

````php
 $ups = Yii::$app->ups;
````

## Session ID alma işlemi

tüm request'lerden önce bir sessionID alma request'i göndermek gerekir. bu sessionID sonraki request'lerde
kullanılacaktır.

````php
$sessionID = $ups->login();
````

## 2- Create Shipment işlemi

````php
/**
 * dimettion bilgisi yoksa bile en az 1 paket eklenmeli
 */
$packageCount = count($shipment->dimetions)==0? 1 : count($shipment->dimetions); 
$serviceDesicion = UpsServiceLevel::Standard;
$packageType = UpsPackingType::NonDocument; //tahmin ederim ki nonDox tur ama teyid etmekte fayda var
$paymentType  = GoodsPaymentType::DDP; ///DDP olmadığı durumları konuşalım
/////paket boyutlarını tanımlayalım
$dimetions = [];
foreach ($shipment->dimetions as $key => $value) 
{
    $upsDimetions[]=[
        "DescriptionOfGoods"=>$value->description,
        "Length"=>$value->length,
        "Height"=>$value->height,
        "Width"=>$value->width,
        "Weight"=>$value->weight,
    ];
}

$shipmentInfo = new ShipmentInfo
(
    [
    "ShipperAccountNumber"=>$this->component->customerNumber,
    "ShipperName"=> $shipment->sender->SenderName,
    "ShipperContactName"=> $shipment->sender->ContactName,
    "ShipperAddress"=> $shipment->sender->address->fullAdress,
    "ShipperCityCode"=> "34",
    "ShipperAreaCode"=> "5662",
    "ShipperPostalCode"=> $shipment->sender->address->zipcode,
    "ShipperPhoneNumber"=> $shipment->sender->phone,
    "ShipperEMail"=> $shipment->sender->email,
    "ShipperExpenseCode"=> "?",
    "ConsigneeAccountNumber"=> "",
    "ConsigneeName"=> $shipment->receiver->ReceiverName,
    "ConsigneeContactName"=> $shipment->receiver->ContactName,
    "ConsigneeAddress"=> $shipment->receiver->address->fullAdress,
    "ConsigneeCityCode"=> "01", ///bu kodlar UPS 'ten toplu olarak alınmalıdır.
    "ConsigneeAreaCode"=> "12", /// Bu kodlar UPS'ten toplu alarak alınmalıdır
    "ConsigneePostalCode"=>$shipment->receiver->address->zipcode,
    "ConsigneePhoneNumber"=> $shipment->receiver->phone,
    "ConsigneeEMail"=> $shipment->receiver->email,
    "ConsigneeExpenseCode"=> "",
    "ServiceLevel"=>$serviceDesicion, ///sevis pakedinin ne olacağını ifade eder önemli 
    "PaymentType"=> $paymentType, 1-2-3 ne olabilir 
    "PackageType"=> $packageType,
    "NumberOfPackages"=>$packageCount,
    "CustomerReference"=> $shipment->customerReference,
    "CustomerInvoiceNumber"=> $shipment->InvoiceNo,
    "DescriptionOfGoods"=> $shipment->productsDescription,
    "DeliveryNotificationEmail"=> $shipment->sender->email,
    "IdControlFlag"=>"0",
    "PhonePrealertFlag"=>"0",
    "SmsToShipper"=>"0",
    "SmsToConsignee"=>"0",
    "InsuranceValue"=>"0",
    "InsuranceValueCurrency"=>"",
    "ValueOfGoods"=>"0",
    "ValueOfGoodsCurrency"=>"",
    "ValueOfGoodsPaymentType"=>"",
    "DeliveryByTally"=>"0",
    "ThirdPartyAccountNumber"=>"",
    "ThirdPartyExpenseCode"=>"0",
    "PackageDimensions" => $dimetions,
    ]
); 

$sessionID = $this->ups->login();
$shipmentRequest = new UpsShipmentModel();
$shipmentRequest->SessionID  = $sessionID;
$shipmentRequest->ShipmentInfo = $shipmentInfo;
$shipmentRequest->ReturnLabelLink =false;
$shipmentRequest->ReturnLabelImage= true;
$shipmentRequest->PaperSize="4X6";

$createShipmentResponse = $this->ups->createShipment($shipmentRequest);
if($createShipmentResponse->status)
{
    echo($createShipmentResponse->cargoTrackingNo);
    foreach ($createShipmentResponse->labelImage->string as $key => $value) {
        file_put_contents($createShipmentResponse->cargoTrackingNo.$key."_.png",$value);
    }
}else
{
    print_r($createShipmentResponse->errorMessage);
    $this->log($createShipmentResponse->requestAsXML,$result->responseAsXML);
}
````

## 3- Cancel işlemi

```php 
$sessionID = $this->ups->login();
$customerRefferance="123459678";
$cargoTrackingNumber="1Z3X9A7768036475220";
$upsBaseModel = $this->ups->cancelShipment($sessionID,$customerRefferance,$cargoTrackingNumber);
if(!$upsBaseModel->status)
{
    print_r($upsBaseModel->errorMessage);
    $this->log($upsBaseModel->requestAsXML,$upsBaseModel->responseAsXML);
}
````

## 4- Tracking işlemi

````php 
$cargo = Yii::$app->cargo;
$shipment = new ShipmentModel();
$shipment->cargoTrackingNumber="1ZA786886800730327";
$response = $cargo->tracking($shipment);
if(!$response->status)
{
    print_r($response->TrackingHistory);
    //$this->log($response->requestAsXML,$response->responseAsXML);
}
````
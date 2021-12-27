<?php

namespace mhunesi\ups\models\requests;

use yii\base\Model;

class ShipmentInfo extends Model
{
    /**
     * ShipperAccountNumber
     *
     * @var string
     */
    public $ShipperAccountNumber = "";

    /**
     * ShipperName
     *
     * @var string
     */
    public $ShipperName = "";

    /**
     * ShipperContactName
     *
     * @var string
     */
    public $ShipperContactName = "";

    /**
     * ShipperAddress
     *
     * @var string
     */
    public $ShipperAddress = "";

    /**
     * ShipperCityCode
     *
     * @var string
     */
    public $ShipperCityCode = "";

    /**
     * ShipperAreaCode
     *
     * @var string
     */
    public $ShipperAreaCode = "";

    /**
     * ShipperPostalCode
     *
     * @var string
     */
    public $ShipperPostalCode = "";

    /**
     * ShipperPhoneNumber
     *
     * @var string
     */
    public $ShipperPhoneNumber = "";

    /**
     * ShipperEMail
     *
     * @var string
     */
    public $ShipperEMail = "";

    /**
     * ShipperExpenseCode
     *
     * @var string
     */
    public $ShipperExpenseCode = "";

    /**
     * ConsigneeAccountNumber
     *
     * @var string
     */
    public $ConsigneeAccountNumber = "";

    /**
     * ConsigneeName
     *
     * @var string
     */
    public $ConsigneeName = "";

    /**
     * ConsigneeContactName
     *
     * @var string
     */
    public $ConsigneeContactName = "";

    /**
     * ConsigneeAddress
     *
     * @var string
     */
    public $ConsigneeAddress = "";

    /**
     * ConsigneeCityCode
     *
     * @var string
     */
    public $ConsigneeCityCode = "";

    /**
     * ConsigneeAreaCode
     *
     * @var string
     */
    public $ConsigneeAreaCode = "";

    /**
     * ConsigneePostalCode
     *
     * @var string
     */
    public $ConsigneePostalCode = "";

    /**
     * ConsigneePhoneNumber
     *
     * @var string
     */
    public $ConsigneePhoneNumber = "";

    /**
     * ConsigneeEMail
     *
     * @var string
     */
    public $ConsigneeEMail = "";

    /**
     * ConsigneeExpenseCode
     *
     * @var string
     */
    public $ConsigneeExpenseCode = "";

    /**
     * ServiceLevel
     *
     * @var integer
     */
    public $ServiceLevel;

    /**
     * PaymentType
     *
     * @var string
     */
    public $PaymentType = "";

    /**
     * PackageType
     *
     * @var string
     */
    public $PackageType = "";

    /**
     * NumberOfPackages
     *
     * @var string
     */
    public $NumberOfPackages = "";

    /**
     * CustomerReference
     *
     * @var string
     */
    public $CustomerReference = "";

    /**
     * CustomerInvoiceNumber
     *
     * @var string
     */
    public $CustomerInvoiceNumber = "";

    /**
     * DescriptionOfGoods
     *
     * @var string
     */
    public $DescriptionOfGoods = "";

    /**
     * DeliveryNotificationEmail
     *
     * @var string
     */
    public $DeliveryNotificationEmail = "";

    /**
     * IdControlFlag
     *
     * @var string
     */
    public $IdControlFlag = "";

    /**
     * PhonePrealertFlag
     *
     * @var string
     */
    public $PhonePrealertFlag = "";

    /**
     * SmsToShipper
     *
     * @var string
     */
    public $SmsToShipper = "";

    /**
     * SmsToConsignee
     *
     * @var string
     */
    public $SmsToConsignee = "";

    /**
     * InsuranceValue
     *
     * @var string
     */
    public $InsuranceValue = "";

    /**
     * InsuranceValueCurrency
     *
     * @var string
     */
    public $InsuranceValueCurrency = "";

    /**
     * ValueOfGoods
     *
     * @var string
     */
    public $ValueOfGoods = "";

    /**
     * ValueOfGoodsCurrency
     *
     * @var string
     */
    public $ValueOfGoodsCurrency = "";

    /**
     * ValueOfGoodsPaymentType
     *
     * @var string
     */
    public $ValueOfGoodsPaymentType = "";

    /**
     * DeliveryByTally
     *
     * @var string
     */
    public $DeliveryByTally = "";

    /**
     * ThirdPartyAccountNumber
     *
     * @var string
     */
    public $ThirdPartyAccountNumber = "";

    /**
     * ThirdPartyExpenseCode
     *
     * @var string
     */
    public $ThirdPartyExpenseCode = "";

    /**
     * PackageDimensions
     *
     * @var array
     */
    public $PackageDimensions;
}
<?php namespace  Esecurity\Projects\Classes;


use Config;
use Esecurity\Projects\Models\Settings;
use angelleye\PayPal\PayPal;


/**
 * Description of paypalPayments
 *
 * @author programmer
 */
class PaypalPayments
{

    private $PayPal_pro;

    public function __construct()
    {

        // setup PayPal api context
        //$paypal_conf = Config::get('paypal');
        $paypal_conf = array(
            // set your paypal credential
            'Sandbox' => Settings::get('sandbox') == '1' ? true : false,
            'APIUsername' => Settings::get('api_username'),
            'APIPassword' => Settings::get('api_password'),
            'APISignature' => Settings::get('api_signature'),
            'ApplicationID' => Settings::get('application_id'),
        );

        $this->PayPal_pro = new PayPal($paypal_conf);
    }

    public function postPayment($dataPayment)
    {
        $DPFields = array(
            'paymentaction' => 'Sale',                        // How you want to obtain payment.  Authorization indicates the payment is a basic auth subject to settlement with Auth & Capture.  Sale indicates that this is a final sale for which you are requesting payment.  Default is Sale.
            'ipaddress' => $_SERVER['REMOTE_ADDR'],                            // Required.  IP address of the payer's browser.
            'returnfmfdetails' => '1'                    // Flag to determine whether you want the results returned by FMF.  1 or 0.  Default is 0.
        );

        $CCDetails = array(
            'creditcardtype' => $dataPayment['creditcardtype'],                    // Required. Type of credit card.  Visa, MasterCard, Discover, Amex, Maestro, Solo.  If Maestro or Solo, the currency code must be GBP.  In addition, either start date or issue number must be specified.
            'acct' => $dataPayment['acct'],                                // Required.  Credit card number.  No spaces or punctuation.
            'expdate' => $dataPayment['month'] . $dataPayment['year'],                            // Required.  Credit card expiration date.  Format is MMYYYY
            'cvv2' => $dataPayment['cvv2'],                                // Requirements determined by your PayPal account settings.  Security digits for credit card.
            'startdate' => '',                            // Month and year that Maestro or Solo card was issued.  MMYYYY
            'issuenumber' => ''                            // Issue number of Maestro or Solo card.  Two numeric digits max.
        );

        $PayerInfo = array(
            'email' => $dataPayment['email'],                                // Email address of payer.
            'payerid' => $dataPayment['payerid'],                            // Unique PayPal customer ID for payer.
            'payerstatus' => '',                        // Status of payer.  Values are verified or unverified
            'business' => $dataPayment['company']                            // Payer's business name.
        );

        $PayerName = array(
            'salutation' => '',                        // Payer's salutation.  20 char max.
            'firstname' => $dataPayment['name'],                            // Payer's first name.  25 char max.
            'middlename' => '',                        // Payer's middle name.  25 char max.
            'lastname' => '',                            // Payer's last name.  25 char max.
            'suffix' => ''                                // Payer's suffix.  12 char max.
        );

        $BillingAddress = array(
            'street' => $dataPayment['street_addr'],                        // Required.  First street address.
            'street2' => '',                        // Second street address.
            'city' => $dataPayment['city'],                            // Required.  Name of City.
            'state' => $dataPayment['codeState'],                            // Required. Name of State or Province.
            'countrycode' => $dataPayment['codeCountry'],                    // Required.  Country code.
            'zip' => $dataPayment['zip'],                            // Required.  Postal code of payer.
            'phonenum' => $dataPayment['phone']                        // Phone Number of payer.  20 char max.
        );

        $ShippingAddress = array(
            'shiptoname' => '',                    // Required if shipping is included.  Person's name associated with this address.  32 char max.
            'shiptostreet' => '',                    // Required if shipping is included.  First street address.  100 char max.
            'shiptostreet2' => '',                    // Second street address.  100 char max.
            'shiptocity' => '',                    // Required if shipping is included.  Name of city.  40 char max.
            'shiptostate' => '',                    // Required if shipping is included.  Name of state or province.  40 char max.
            'shiptozip' => '',                        // Required if shipping is included.  Postal code of shipping address.  20 char max.
            'shiptocountrycode' => '',                // Required if shipping is included.  Country code of shipping address.  2 char max.
            'shiptophonenum' => ''                    // Phone number for shipping address.  20 char max.
        );

        $PaymentDetails = array(
            'amt' => $dataPayment['amount'],            // Required.  Total amount of order, including shipping, handling, and tax.
            'currencycode' => 'CAD',                    // Required.  Three-letter currency code.  Default is USD.
            'itemamt' => '',                        // Required if you include itemized cart details. (L_AMTn, etc.)  Subtotal of items not including S&H, or tax.
            'shippingamt' => '',                    // Total shipping costs for the order.  If you specify shippingamt, you must also specify itemamt.
            'handlingamt' => '',                    // Total handling costs for the order.  If you specify handlingamt, you must also specify itemamt.
            'taxamt' => '',                        // Required if you specify itemized cart tax details. Sum of tax for all items on the order.  Total sales tax.
            'desc' => '',                            // Description of the order the customer is purchasing.  127 char max.
            'custom' => 'TEST',                        // Free-form field for your own use.  256 char max.
            'invnum' => $dataPayment['invoice'],                        // Your own invoice or tracking number
            'buttonsource' => '',                    // An ID code for use by 3rd party apps to identify transactions.
            'notifyurl' => ''                        // URL for receiving Instant Payment Notifications.  This overrides what your profile is set to use.
        );

        $OrderItems = array();
        $Item = array(
            'l_name' => '',                        // Item Name.  127 char max.
            'l_desc' => '',                        // Item description.  127 char max.
            'l_amt' => '',                            // Cost of individual item.
            'l_number' => '',                        // Item Number.  127 char max.
            'l_qty' => '',                            // Item quantity.  Must be any positive integer.
            'l_taxamt' => '',                        // Item's sales tax amount.
            'l_ebayitemnumber' => '',                // eBay auction number of item.
            'l_ebayitemauctiontxnid' => '',        // eBay transaction ID of purchased item.
            'l_ebayitemorderid' => ''                // eBay order ID for the item.
        );
        array_push($OrderItems, $Item);

        $PayPalRequestData = array(
            'DPFields' => $DPFields,
            'CCDetails' => $CCDetails,
            'PayerInfo' => $PayerInfo,
            'PayerName' => $PayerName,
            'BillingAddress' => $BillingAddress,
            'PaymentDetails' => $PaymentDetails,
            'OrderItems' => $OrderItems
        );

        $PayPalResult = $this->PayPal_pro->DoDirectPayment($PayPalRequestData);
        return $PayPalResult;
    }

    public function getBalance()
    {

        $GBFields = array('returnallcurrencies' => '');
        $PayPalRequestData = array('GBFields' => $GBFields);
        $PayPalResult = $this->PayPal_pro->GetBalance($PayPalRequestData);
        return $PayPalResult;
    }

    public function getTransactionSearch()
    {

        $StartDate = gmdate("Y-m-d\\TH:i:sZ", strtotime('now - 60 day'));

        $TSFields = array(
            'startdate' => $StartDate,                    // Required.  The earliest transaction date you want returned.  Must be in UTC/GMT format.  2008-08-30T05:00:00.00Z
            'enddate' => '',                            // The latest transaction date you want to be included.
            'email' => '',                                // Search by the buyer's email address.
            'receiver' => '',                            // Search by the receiver's email address.
            'receiptid' => '',                            // Search by the PayPal account optional receipt ID.
            'transactionid' => '',                        // Search by the PayPal transaction ID.
            'invnum' => '',                            // Search by your custom invoice or tracking number.
            'acct' => '',                                // Search by a credit card number, as set by you in your original transaction.
            'auctionitemnumber' => '',                    // Search by auction item number.
            'transactionclass' => '',                    // Search by classification of transaction.  Possible values are: All, Sent, Received, MassPay, MoneyRequest, FundsAdded, FundsWithdrawn, Referral, Fee, Subscription, Dividend, Billpay, Refund, CurrencyConversions, BalanceTransfer, Reversal, Shipping, BalanceAffecting, ECheck
            'amt' => '',                                // Search by transaction amount.
            'currencycode' => '',                        // Search by currency code.
            'status' => '',                            // Search by transaction status.  Possible values: Pending, Processing, Success, Denied, Reversed
            'profileid' => ''                            // Recurring Payments profile ID.  Currently undocumented but has tested to work.
        );

        $PayerName = array(
            'salutation' => '',                        // Search by payer's salutation.
            'firstname' => '',                            // Search by payer's first name.
            'middlename' => '',                        // Search by payer's middle name.
            'lastname' => '',                            // Search by payer's last name.
            'suffix' => ''                                // Search by payer's suffix.
        );

        $PayPalRequest = array(
            'TSFields' => $TSFields,
            'PayerName' => $PayerName
        );

        $PayPalResult = $this->PayPal_pro->TransactionSearch($PayPalRequest);
        return $PayPalResult;
    }
}

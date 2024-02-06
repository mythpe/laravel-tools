<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

/**
 * Urway Getaway configuration
 * @Version: 1.3.2
 */

use Myth\LaravelTools\Models\Getaway\GetawayOrder;
use Myth\LaravelTools\Models\Getaway\GetawayTransaction;

return [
    /**
     * | Payment Gateway Terminal ID
     */
    'terminal_id'         => (string) env('PAYMENT_GATEWAY_TERMINAL_ID', ''),
    /**
     * | Payment Gateway Password
     */
    'password'            => (string) env('PAYMENT_GATEWAY_PASSWORD', ''),
    /**
     * | Payment Gateway API Key
     */
    'api_key'             => (string) env('PAYMENT_GATEWAY_API_KEY', ''),
    /**
     * | Payment page country.
     */
    'country'             => 'SA',
    /**
     * | Payment page currency code.
     */
    'currency_code'       => 'SAR',
    /**
     * | Purchase page language.
     * | AR- Arabic
     * | null/EN - English
     */
    'language'            => 'AR',
    /**
     * | Name of route to process payment
     */
    'callback_route_name' => '4myth-getaway.processPayment',
    /**
     * | Name of folder to store log
     */
    'folder_log_name'     => 'getaway',
    /**
     * | Base Url of API
     */
    'base_url'            => [
        'dev'  => 'https://payments-dev.urway-tech.com/URWAYPGService',
        'prod' => 'https://payments.urway-tech.com/URWAYPGService',
    ],
    /**
     * | Urls of API
     */
    'urls'                => [
        /**
         * | Transaction Prefix
         */
        'transaction' => 'transaction/jsonProcess/JSONrequest',
        /**
         * | Redirect Prefix
         */
        'redirect'    => 'direct.jsp',
    ],
    /**
     * | Actions of Transaction in API
     */
    'actions'             => [
        'purchase'            => 1,
        'refund'              => 2,
        'authorization'       => 4,
        'capture'             => 5,
        'void_authorization'  => 9,
        'transaction_inquiry' => 10,
    ],
    /**
     * | Inquiry Types of Transaction in API
     */
    'inquiry_types'       => [
        'purchase'           => 1,
        'refund'             => 2,
        'authorization'      => 4,
        'capture'            => 5,
        'void_authorization' => 9,
    ],
    /**
     * | API Payment Types.Contains what is the payment type used for transaction.
     * | Eg: CreditCard,DebitCard,ApplePay,STCPay
     */
    'payment_types'       => [
        'credit_card' => 'DebitCard',
        'debit_card'  => 'CreditCard',
        'apple_pay'   => 'ApplePay',
        'sadad'       => 'SADAD',
        'stc_pay'     => 'STCPay',
    ],
    /**
     * | API Card Brands.
     * | Contains card brand used for transaction.
     * | Eg: MADA,MASTER,VISA,AMEX
     */
    'card_brands'         => [
        'mada'   => 'MADA',
        'master' => 'MASTER',
        'visa'   => 'VISA',
        'amex'   => 'AMEX',
    ],
    /**
     * | Statuses of Order Class.
     */
    'statuses'            => [
        /**
         * | New Model with trackable Model.
         * | without reference_id
         */
        'initial'       => 'initial',
        /**
         * | New Model without trackable Model.
         * | Must have trackable data.
         * | without reference_id
         */
        'initial_data'  => 'initial_data',
        /**
         * | The order is paid.
         */
        'paid'          => 'paid',
        /**
         * | The order is failed process.
         */
        'failed'        => 'failed',
        /**
         * | the transaction is unsuccessful.
         */
        'un_successful' => 'UnSuccessful',
    ],
    /**
     * | Class of Order.
     */
    'order_class'         => GetawayOrder::class,
    /**
     * | Track Prefix of Order.
     * | Eg: Rfd-{$this->id}
     */
    'order_track_prefix'  => '',
    /**
     * | API Development Mode.
     * | This for development, will use the url from config('4myth-getaway.base_url.dev').
     */
    'development'         => !1,
    /**
     * | Morph Name of Order.
     */
    'morph_name'          => 'trackable',
    /**
     * | Class of Transaction.
     */
    'transaction_class'   => GetawayTransaction::class,
    /**
     * | Codes of Transactions.
     */
    'codes'               => [
        '000'  => 'Transaction Successful',
        '001'  => 'Pending for Authorisation',
        '101'  => '-Field is blank in a request',
        '102'  => 'Internal Mapping for ISO not set',
        '103'  => 'ISO message field configuration not found',
        '104'  => 'Response Code not found in ISO message',
        '105'  => 'Problem while creating or parsing ISO Message',
        '201'  => 'Terminal does not exists',
        '202'  => 'Merchant does not exists',
        '203'  => 'Institution does not exists',
        '204'  => 'Card prefix is not belong to corresponding card Type',
        '205'  => 'Card not allowed for this transaction',
        '206'  => 'Negative IP, Customer is not allowed to perform Transaction',
        '207'  => 'Original Transaction not found',
        '208'  => 'Transaction Flow not set for Transaction Type',
        '209'  => 'Terminal status is Deactive, Transaction Declined',
        '210'  => 'Terminal status is Closed, Transaction Declined',
        '211'  => 'Terminal status is Invalid, Transaction Declined',
        '212'  => 'Merchant status is Deactive, Transaction Declined',
        '213'  => 'Merchant status is Closed, Transaction Declined',
        '214'  => 'Merchant status is Invalid, Transaction Declined',
        '215'  => 'Institution status is Deactive, Transaction Declined',
        '216'  => 'Institution status is Closed, Transaction Declined',
        '217'  => 'Institution status is Invalid, Transaction Declined',
        '218'  => 'MOD10 Check Failed',
        '219'  => 'Card Type not supported by Merchant',
        '220'  => 'CVV Check Failed, CVV value not present',
        '221'  => 'AVS Capture Check Failed, Could not find Customer Address',
        '222'  => 'Customer Info Check failed, Could not find Customer Information',
        '223'  => 'Card expiry date is not greater than current date',
        '224'  => 'Invalid Login Attempts exceeded',
        '225'  => 'Wrong Terminal password, Please Re-Initiate transaction',
        '226'  => 'Negative Country, Customer is not allowed to perform Transaction',
        '227'  => 'Card type not supported by institution',
        '228'  => 'Multiple captures not allowed',
        '229'  => 'Original transaction was done by different terminal, relative transaction not allowed for this terminal',
        '230'  => 'Instrument Type not supported',
        '231'  => 'Card Number doesnot belong to instrument Type present in Bin',
        '232'  => 'Instrument Type is not allowed for given Merchant',
        '233'  => 'Recurring instrument Type doesnot matches with payment method',
        '234'  => 'Card Data doesnot belong to instrument Type present in Global Instrument Table',
        '235'  => 'Global Instrument Table doesnot contain values for given ID',
        '237'  => 'Payment Session Timeout',
        '238'  => 'Transaction already initiated',
        '239'  => 'Merchant is inactive',
        '240'  => 'Mada Card Brand not support for recurring transaction',
        '301'  => 'Transaction is not allowed for given Terminal',
        '302'  => 'Transaction is not allowed for given Merchant',
        '303'  => 'Transaction is not allowed for given Institution',
        '304'  => 'Currency not supported for given Terminal',
        '305'  => 'Currency not supported for given Merchant',
        '306'  => 'Currency not supported for given Institution',
        '307'  => 'Velocity Check Failed, Velocity Profile not found, Level - Terminal',
        '308'  => 'Velocity Check Failed, Velocity Profile not found, Level - Merchant',
        '309'  => 'Velocity Check Failed, Velocity Profile not found, Level - Institution',
        '310'  => 'Transaction Profile not set for Terminal, Unable to check Transaction Profile',
        '311'  => 'Transaction Profile not set for Merchant, Unable to check Transaction Profile',
        '312'  => 'Transaction Profile not set for Institution, Unable to check Transaction Profile',
        '313'  => 'Currency Profile not set for Terminal, Unable to check Currency Profile',
        '314'  => 'Currency Profile not set for Merchant, Unable to check Currency Profile',
        '315'  => 'Currency Profile not set for Institution, Unable to check Currency Profile',
        '316'  => 'Velocity Profile not set for Terminal, Unable to check Velocity Profile',
        '317'  => 'Velocity Profile not set for Merchant, Unable to check Velocity Profile',
        '318'  => 'Velocity Profile not set for Institution, Unable to check Velocity Profile',
        '319'  => 'Refund Limit exceeds for Terminal',
        '320'  => 'Refund Limit exceeds for Merchant',
        '321'  => 'Refund Limit exceeds for Institution',
        '322'  => 'Velocity Check Failed, Transaction amount below Minimum amount allowed, Level - Terminal',
        '323'  => 'Velocity Check Failed, Transaction amount below Minimum amount allowed, Level - Merchant',
        '324'  => 'Velocity Check Failed, Transaction amount below Minimum amount allowed, Level - Institution',
        '325'  => 'Velocity Check Failed, Transaction amount exceeds Maximum amount allowed, Level - Terminal',
        '326'  => 'Velocity Check Failed, Transaction amount exceeds Maximum amount allowed, Level - Merchant',
        '327'  => 'Velocity Check Failed, Transaction amount exceeds Maximum amount allowed, Level - Institution',
        '328'  => 'Velocity Check Failed, Level - Terminal',
        '329'  => 'Velocity Check Failed, Level - Merchant',
        '330'  => 'Velocity Check Failed, Level - Institution',
        '331'  => 'Velocity Check Failed, Transaction exceeds, Daily Total transaction count, Level - Terminal',
        '332'  => 'Velocity Check Failed, Transaction exceeds, Daily Total transaction count, Level - Merchant',
        '333'  => 'Velocity Check Failed, Transaction exceeds, Daily Total transaction count, Level - Institution',
        '334'  => 'Velocity Check Failed, Transaction amount exceeds, Daily Total transaction amount allowed, Level - Terminal',
        '335'  => 'Velocity Check Failed, Transaction amount exceeds, Daily Total transaction amount allowed, Level - Merchant',
        '336'  => 'Velocity Check Failed, Transaction amount exceeds, Daily Total transaction amount allowed, Level - Institution',
        '337'  => 'Velocity Check Failed, Transaction exceeds Total transaction count of this Card, Level - Terminal',
        '338'  => 'Velocity Check Failed, Transaction exceeds Total transaction count of this Card, Level - Merchant',
        '339'  => 'Velocity Check Failed, Transaction exceeds Total transaction count of this Card, Level - Institution',
        '340'  => 'Velocity Check Failed, Transaction exceeds, Weekly Total transaction count, Level - Terminal',
        '341'  => 'Velocity Check Failed, Transaction exceeds, Monthly Total transaction count, Level - Terminal',
        '342'  => 'Velocity Check Failed, Transaction exceeds, Weekly Total transaction count, Level - Merchant',
        '343'  => 'Velocity Check Failed, Transaction exceeds, Monthly Total transaction count, Level - Merchant',
        '344'  => 'Velocity Check Failed, Transaction exceeds, Weekly Total transaction count, Level - Institution',
        '345'  => 'Velocity Check Failed, Transaction exceeds, Monthly Total transaction count, Level - Institution',
        '346'  => 'Velocity Check Failed, Transaction amount exceeds, Weekly Total transaction amount allowed, Level - Terminal',
        '347'  => 'Velocity Check Failed, Transaction amount exceeds, Monthly Total transaction amount allowed, Level - Terminal',
        '348'  => 'Velocity Check Failed, Transaction amount exceeds, Weekly Total transaction amount allowed, Level - Merchant',
        '349'  => 'Velocity Check Failed, Transaction amount exceeds, Monthly Total transaction amount allowed, Level - Merchant',
        '350'  => 'Velocity Check Failed, Transaction amount exceeds, Weekly Total transaction amount allowed, Level - Institution',
        '351'  => 'Velocity Check Failed, Transaction amount exceeds, Monthly Total transaction amount allowed, Level - Institution',
        '352'  => 'Invalid Length Of Beneficiary Bank Clearing Code',
        '353'  => 'Invalid Length Of Beneficiary Bank',
        '354'  => 'Sum of beneficiary amount and transaction amount should be same',
        '355'  => 'Internal Error occurred while connecting to B2B destination',
        '356'  => 'B2b transaction partially proceed',
        '357'  => 'More than 10 benificiary not supported for Riyadh bank',
        '358'  => 'Token not found in vault',
        '359'  => 'Unable to generate Token,Error occurred',
        '360'  => 'STC PAY not enabled for terminal',
        '361'  => 'STC pay transaction Failed',
        '362'  => 'STC pay dose not support Apple pay transaction',
        '363'  => 'Non 3D terminal is not allowed to process STCPAY transaction',
        '364'  => 'Transaction failed due to maximum OTP retry limit reach',
        '365'  => 'Error Occurred While Getting Response from STCPAY',
        '371'  => 'Please provide subscription id for recurring request',
        '372'  => 'Subscription id not valid or not available',
        '373'  => 'Please provide valid subscription type for recurring request',
        '374'  => 'Recurring transaction date should be greater than or equal to payment start date',
        '375'  => 'Failed to cancel Subscription',
        '376'  => 'Subscription already cancelled',
        '377'  => 'Failed to renew Subscription',
        '378'  => 'No of recurring transactions cannot be less than processed transaction',
        '379'  => 'For this subscriptionid installment/subscription already completed. cannot cancel now',
        '380'  => 'Failed To generate schedule for B2B paymentn',
        '381'  => 'Error occurred while parsing B2B XML response',
        '382'  => 'B2B transaction failed',
        '383'  => 'Customer Account Number Is Required',
        '384'  => 'Customer Name Is Not Available In Request',
        '385'  => 'Beneficiary Name Is Not Available In Request Or Wrong Length',
        '386'  => 'Beneficiary Account Number Is Required Or Wrong Length',
        '387'  => 'Beneficiary BankCode Is Required Or Wrong Length',
        '388'  => 'Invalid Sub Interface Code',
        '389'  => 'B2B transactions Not Enabled For Terminal',
        '390'  => 'Multiple Beneficiary Not Supported For Selected Interface',
        '391'  => 'Beneficiary Not Available in request',
        '392'  => 'Invalid Date Format For Payment Start Date',
        '393'  => 'Sub Interface Not Supported for Selected destination',
        '394'  => 'PG service down',
        '395'  => 'Beneficiary amount is invalid',
        '396'  => 'Sum of beneficiary amount and transaction amount should be same',
        '397'  => 'B2B Payment start date should be greater than current date',
        '398'  => 'Invalid Payment Details',
        '399'  => 'Invalid Length Of Beneficiary Address',
        '400'  => 'Invalid Length Of Beneficiary Bank Address',
        '401'  => 'Destination is not configured',
        '402'  => 'Can not lookup Destination to send message',
        '403'  => 'Unable to route Message to Destination',
        '404'  => 'Unable to get routing details',
        '405'  => 'Destination does Not Logged on',
        '5001' => 'Invalid Request or Information wrong',
        '5002' => 'Error while connecting to Sadad server',
        '5004' => 'Username or Password not configured for sadad',
        '5005' => 'Got Sadad Number Successfully',
        '5006' => 'Sadad Payment details is not available in request',
        '5007' => 'Sadad Payment details are empty in array',
        '5008' => 'Invalid Customer Full Name',
        '5009' => 'Invalid Customer Mobile Number',
        '501'  => 'Refer to card issuer',
        '5010' => 'Invalid Customer Email Address',
        '5011' => 'Invalid Customer Previous Balance',
        '5012' => 'Customer Tax Number Length Should be 15',
        '5013' => 'Invalid Issue Date',
        '5014' => 'Invalid Date Format For Issue Date',
        '5015' => 'Issue date should be greater than current date',
        '5016' => 'Invalid Expire Date',
        '5017' => 'Invalid Date Format For Expire Date',
        '5018' => 'Expire date should be greater than Issue date',
        '5019' => 'In BillItemList name should be required',
        '502'  => 'Refer to card issuer, special condition',
        '5020' => 'In BillItemList quantity should be required',
        '5021' => 'In BillItemList unitPrice should be required',
        '5022' => 'Invalid Unit Price',
        '5023' => 'In BillItemList discount should be required',
        '5024' => 'Invalid Discount',
        '5025' => 'In BillItemList discount type should be required',
        '5026' => 'Discount type should be fixed or perc',
        '5027' => 'In BillItemList vat should be required',
        '5028' => 'IsPartialAllowed should be yes or no',
        '5029' => 'Invalid Customer Id Type',
        '503'  => 'Invalid Merchant or Merchant ID or Inactive Merchant',
        '5030' => 'Invalid BillItemList',
        '5031' => 'Invalid Entity Activity Id',
        '5032' => 'Invalid Mini Partial Amount',
        '5034' => 'sendLinkMode required',
        '5035' => 'Invalid sendLinkMode',
        '5036' => 'Invalid smsLanguage',
        '504'  => 'Pick-up card',
        '505'  => 'Do not honour',
        '506'  => 'Error',
        '507'  => 'Pick-up card, special condition',
        '508'  => 'Honour with identification',
        '509'  => 'Request in progress',
        '510'  => 'Approved, partial',
        '511'  => 'Approved, VIP',
        '512'  => 'Invalid transaction',
        '513'  => 'Invalid amount',
        '514'  => 'Invalid card number',
        '515'  => 'No such issuer',
        '516'  => 'Approved, update track 3',
        '517'  => 'Operator Cancelled',
        '518'  => 'Customer dispute',
        '519'  => 'Re enter transaction',
        '520'  => 'Invalid response',
        '521'  => 'No action taken',
        '522'  => 'Suspected malfunction',
        '523'  => 'Unacceptable transaction fee',
        '524'  => 'File update not supported',
        '525'  => 'Unable to locate record',
        '526'  => 'Duplicate record',
        '527'  => 'File update edit error',
        '528'  => 'File update file locked',
        '530'  => 'File update failed',
        '531'  => 'Bank not supported',
        '532'  => 'Completed partially',
        '533'  => 'Expired card, pick-up',
        '534'  => 'Suspected fraud, pick-up',
        '535'  => 'Contact acquirer, pick-up',
        '536'  => 'Restricted card, pick-up',
        '537'  => 'Call acquirer security, pick-up',
        '538'  => 'PIN tries exceeded, pick-up',
        '539'  => 'No credit account',
        '540'  => 'Function not supported',
        '541'  => 'Lost card (Contact Bank)',
        '542'  => 'No universal account',
        '543'  => 'Stolen card',
        '544'  => 'No investment account',
        '545'  => 'Incorrect OTP value or reference',
        '551'  => 'Not sufficient funds (Client to Contact Bank)',
        '552'  => 'No check account',
        '553'  => 'No savings account',
        '554'  => 'Expired card (Contact Bank)',
        '555'  => 'Incorrect PIN',
        '556'  => 'No card record',
        '557'  => 'Transaction not permitted to cardholder',
        '558'  => 'Transaction not permitted on terminal',
        '559'  => 'Suspected fraud',
        '560'  => 'Contact acquirer',
        '561'  => 'Exceeds withdrawal limit',
        '562'  => 'Restricted card',
        '563'  => 'Security violation',
        '564'  => 'Original amount incorrect',
        '565'  => 'Exceeds withdrawal frequency',
        '566'  => 'Call acquirer security',
        '567'  => 'Hard capture',
        '568'  => 'Response received too late',
        '575'  => 'PIN tries exceeded',
        '576'  => 'Approved country club',
        '577'  => 'Intervene, bank approval required',
        '578'  => 'Original transaction could not be found',
        '579'  => 'approved administrative transaction',
        '580'  => 'Approved national negative file hit OK',
        '581'  => 'Approved commercial',
        '582'  => 'No security module',
        '583'  => 'No accounts',
        '584'  => 'No PBF',
        '585'  => 'PBF update error',
        '586'  => 'Invalid authorisation type',
        '587'  => 'Bad Track 2 bank offline',
        '588'  => 'PTLF error',
        '589'  => 'Invalid route service',
        '590'  => 'Cut-off in progress',
        '591'  => 'Issuer or switch inoperative',
        '592'  => 'Routing error',
        '593'  => 'Violation of law',
        '594'  => 'Duplicate transaction',
        '595'  => 'Reconcile error',
        '596'  => 'Communication System malfunction',
        '597'  => 'Communication Error',
        '598'  => 'Exceeds cash limit',
        '599'  => 'Host Response,Please check bank response code',
        '600'  => 'Transaction service down',
        '601'  => 'System Error, Please contact System Admin.',
        '602'  => 'System Error,Please try again',
        '603'  => 'Transaction timed out.',
        '604'  => 'Invalid Card Number.',
        '605'  => 'Invalid CVV.',
        '606'  => 'Invalid Track Id.',
        '607'  => 'Invalid Terminal Id.',
        '608'  => 'Invalid Address.',
        '609'  => 'Invalid Terminal Password.',
        '610'  => 'Invalid Action Code.',
        '611'  => 'Invalid Currency Code.',
        '612'  => 'Invalid Transaction Amount.',
        '613'  => 'Invalid Transaction Reference.',
        '614'  => 'Invalid UserFields.',
        '615'  => 'Invalid City.',
        '616'  => 'Invalid characters encountered.',
        '617'  => 'Invalid Card Expiry Date.',
        '618'  => 'Invalid State',
        '619'  => 'Invalid Country',
        '620'  => 'Invalid Cardholder Name.',
        '621'  => 'Invaild ZipCode.',
        '622'  => 'Invalid IP Address.',
        '623'  => 'Invalid Email Address.',
        '624'  => 'Transaction cancelled by the user.',
        '625'  => '3D Secure Check Failed, Cannot continue transaction',
        '626'  => 'Invalid CVV,CVV Mandatory.',
        '627'  => 'Capture not allowed, Mismatch in Capture and Original Auth Transaction Amount.',
        '628'  => 'Transaction has not been Captured/Purchase, Refund not allowed.',
        '629'  => 'Refund Amount exceeds the Captured/Purchase Amount.',
        '630'  => 'Transaction is Void, Capture not allowed.',
        '631'  => 'Transaction has been Captured, Void Auth not allowed.',
        '632'  => 'Original Transaction not found.',
        '633'  => 'Transaction already Refunded, Duplicate refund not allowed.',
        '634'  => 'Transaction is Void, Refund not allowed.',
        '635'  => 'Transaction has been Captured, Multiple captures not allowed.',
        '636'  => 'Transaction has been Voided , Multiple voids not allowed.',
        '637'  => 'A purchase transaction cannot be captured. It should be an Auth transaction.',
        '638'  => 'Purchase transaction cannot be Voided.',
        '639'  => 'Invalid Void Transaction, Void and Original Auth Transaction Amount mismatched.',
        '640'  => 'Refund transaction in progress, Cannot process duplicate transaction',
        '641'  => 'Capture transaction in progress, Cannot process duplicate transaction',
        '642'  => 'Void Auth transaction in progress, cannot process duplicate transaction',
        '644'  => 'Transaction is fully refunded, refund not allowed',
        '645'  => 'Transaction is chargeback transaction, refund not allowed',
        '646'  => 'Transaction is chargeback transaction, refund amount exceeds allowed amount',
        '647'  => 'Invalid subscription type',
        '648'  => 'Invalid payment type',
        '649'  => 'Invalid payment cycle',
        '650'  => 'Invalid payment start date',
        '651'  => 'Invalid payment days',
        '652'  => 'Invalid payment Method',
        '653'  => 'Terminal not allow for recurring payment',
        '654'  => 'Invalid Recurring Amount',
        '655'  => 'Invalid payment type',
        '656'  => 'Invalid No of recurring payment',
        '657'  => 'Recurring cycle limit exceeds, cannot set recurringing for more than 2 years',
        '658'  => 'Amount 0.00 is not supported for Pre-auth transaction',
        '659'  => 'Request authentication failed',
        '660'  => 'Invalid tran message id or track id',
        '661'  => 'Invalid original action code',
        '662'  => 'Original transaction was done by different terminal',
        '663'  => 'Transaction inquiry failed',
        '664'  => 'Currency Code is not matching with transaction currency',
        '665'  => 'TrackId is not matching with transaction trackid',
        '670'  => 'Transaction has been Refunded, Void Purchase not allowed',
        '671'  => 'Void Purchase not allowed for PreAuth Transaction',
        '672'  => 'Transaction is Purchase, Void Refund not allowed',
        '673'  => 'Transaction is Pre-Auth, Void Refund not allowed',
        '674'  => 'Transaction is Void Purchase, Void Refund not allowed',
        '675'  => 'Transaction is Capture, Void Refund not allowed',
        '676'  => 'Transaction is Void Auth, Void Refund not allowed',
        '677'  => 'Void Purchase not allowed, Mismatch in Void Purchase and Original Purchase Transaction Amount',
        '678'  => 'Void Refund not allowed, Mismatch in Void Refund and Original Refund Transaction Amount',
        '679'  => 'Invalid Payment Data,Apple pay',
        '680'  => 'Recurring cycle limit exceeds, cannot set recurringing for more than 30 years',
        '699'  => 'Transaction timed out from bank',
        '701'  => 'Error while processing ApplePay payment Token request',
        '702'  => 'No Such Payment',
        '703'  => 'Payment Cancelled',
        '704'  => 'Payment Expired',
        '705'  => 'Already Paid',
        '706'  => 'Please complete your Customer Information',
        '707'  => 'Account is not allowed for transfers',
        '708'  => 'Payment information mismatch with the OtpReference',
        '709'  => 'Your Account status is not valid to perform this transaction',
        '710'  => 'STCPay System Error, please try again',
        '711'  => 'Merchant Not Found',
        '712'  => 'Required StcPaypmtReference',
        '799'  => 'TM time out',
        '901'  => 'Merchant not authorize to perform tokenization request',
        '902'  => 'Tokenization not enabled for Merchant',
        '903'  => 'Error In 3D Authentication of Tokenize request',
        '904'  => 'Invalid Tokenize ressponse',
        '905'  => 'Invalid Token operation',
        '906'  => 'Invalid Card Token',
        '907'  => 'Plesae provide valid mobile number',
        '908'  => 'This Currency not allowed for STS Pay',
        '909'  => 'This transaction type not supported for destination',
        '915'  => 'Maximum Amount Limit Exceeds for transaction',
        '916'  => 'Terminal is not supported for link base api payment',
        '917'  => 'Link Flag Invalid',
        '918'  => 'Expiry days is not greater than 4',
        '919'  => 'PaymentFor field is invalid or length is greater than 50 character',
        '920'  => 'Link for linked based trasnaction is not created',
        '921'  => 'Invalid Link Id',
        '922'  => 'Link Base transaction already success,failure or deleted',
        '923'  => 'PaymentFor request field is necessary for link base',
        '924'  => 'Merchant id not supported for link base payment',
        '925'  => 'Merchant status is not active',
        '926'  => 'Terminal status is not active',
        '927'  => 'User Field 5 is mandatory for link base',
        '928'  => 'Expiry Days field is not valid',
        '929'  => 'Partial payment allowed field is invalid',
        '930'  => 'Email Id field is mandatory for link base api',
        '931'  => 'Please provide valid mobile number in udf4',
        '932'  => 'Excessive refund not enabled Terminal level',
        '933'  => 'Excessive refund amount limit not set Terminal level',
        '935'  => 'Terminal MID or MID Password not configured',
        '936'  => 'STCPAY Direct Integration not supported for given terminal',
        '937'  => 'Card brand not supported for given terminal',
        '938'  => 'transaction id not match with existing tranid',
        '939'  => 'Linkbase partial amount is not valid',
        '940'  => 'Link Base Partial Amount is greater than actual amount',
        '941'  => 'card type not found in applepay card token',
        '942'  => 'payment link send options not configured',
        '943'  => 'Either Email Address or Contact Number Field is required',
        '944'  => 'send link via SMS limit exceeded',
        '945'  => 'invalid length of tran description field for b2b',
        'B501' => 'Invalid CIF number',
        'B502' => 'Beneficiary is not active',
        'B503' => 'Sub-user not found',
        'B504' => 'Beneficiary not found',
        'B505' => 'Beneficiary code is required',
        'B506' => 'Currency is not allowed for international transfers',
        'B507' => 'User ID does not exist',
        'B508' => 'User ID does not match the provided CIF & Beneficiary Code',
        'B509' => 'Sub-user id is mandatory',
        'B510' => 'Sub-user not linked to main user',
        'B511' => 'Commission amount missing',
        'B512' => 'Commission currency missing',
        'B513' => 'Commission code incompatible with beneficiary our charges',
        'B514' => 'Commission code incompatible',
        'B515' => 'Credit account not within the bank',
        'B516' => 'Credit Currency missing',
        'B517' => 'Current amount currency missing',
        'B518' => 'Debit currency missing',
        'B519' => 'Debit currency incompatible with account currency',
        'B521' => 'Invalid field name',
        'B522' => 'Function is disabled from service provider',
        'B523' => 'Transaction id not supplied',
        'B524' => 'Invalid commission code',
        'B525' => 'Unauthorized Override - Account balance will fall below locked amount',
        'B526' => 'Override was encountered',
        'B527' => 'Invalid beneficiary account',
        'B528' => 'Missing beneficiary account',
        'B529' => 'Beneficiary account is required',
        'B530' => 'Invalid customer',
        'B531' => 'Beneficiary name is required',
        'B533' => 'Beneficiary bank code not supplied',
        'B534' => 'Invalid bank code',
        'B538' => 'Invalid SWIFT code',
        'B539' => 'Beneficiary currency is required',
        'B541' => 'Alinma beneficiary account is required',
        'B542' => 'Customer own account not allowed as a credit account',
        'B543' => 'Beneficiary country is required',
        'B544' => 'Detail of charge is required',
        'B545' => 'Corresponding bank code is required',
        'B546' => 'Beneficiary proof of identification is required',
        'B548' => 'Invalid beneficiary type',
        'B549' => 'User ID provided is not a main user ID',
        'B550' => 'Beneficiary type is required',
        'B551' => 'Cannot create international beneficiary with SAR currency',
        'B552' => 'Invalid fund transfer purpose',
        'B553' => 'Invalid detail of charge',
        'B554' => 'Inma Beneficiary cannot be created with own account',
        'B555' => 'Invalid beneficiary name',
        'B556' => 'Invalid account type',
        'B557' => 'Invalid Alinma account length',
        'B559' => 'Invalid beneficiary name length',
        'B561' => 'Currency is not allowed for international transfers',
        'B562' => 'Creation of international beneficiary is not allowed',
        'B563' => 'Creation of Alinma beneficiary is not allowed',
        'B564' => 'Creation of local (KSA) beneficiary is not allowed',
        'B565' => 'Adhoc beneficiary information could not be validated',
        'B566' => 'Unknown database errors',
        'B567' => 'Unrecoverable integration error',
        'B568' => 'Transaction requires supervisor override',
    ],
];

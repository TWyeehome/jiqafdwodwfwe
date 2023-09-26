<?php
// 介接資訊
$oService = new NetworkService();    // // 初始化網路服務物件。
$oService->ServiceURL = $ticket_issue;
$szHashKey = $ticket_key;
$szHashIV = $ticket_hiv;
// POST 參數
$szPlatformID = '';
$szData = '';
$arData = [];
while ($issue_row = $issue->fetch_assoc()) {
    // 票券價格
    $test_item = 'VEK00414'; // 測試票 
    $discount_item = 'VCB05928'; // 超級早鳥票 
    $early_item = 'VSW23052'; // 早鳥票 測試: VAC74717 正式: VSW23052
    $normal_item = 'VSD88140'; // 全票
    $family_item = 'VWP18660'; // 親子套票
    // 檢查折扣碼
    if ($issue_row['coupon']) {
        // 檢查折價券是否存在
        $result = $db->query("SELECT discount, end_time FROM coupon WHERE code='" . $issue_row['coupon'] . "'");
        if ($result->num_rows) {
            $row = $result->fetch_assoc();
            if ($row['end_time'] == '') {
                // 9 折
                if ($row['discount'] == 0.9) {
                    $test_item = 'VTV22191'; // 測試票 
                    $normal_item = 'VPV13995'; // 全票
                };
                // 85 折
                if ($row['discount'] == 0.85) {
                    $test_item = 'VUI20311'; // 測試票 
                    $normal_item = 'VKP79582'; // 全票
                };
                // 8 折
                if ($row['discount'] == 0.8) {
                    $test_item = 'VXW37637'; // 測試票 
                    $normal_item = 'VUT70676'; // 全票
                };
            } else {
                if (get_today() <= $row['end_time']) {
                    // 9 折
                    if ($row['discount'] == 0.9) {
                        $test_item = 'VTV22191'; // 測試票 
                        $normal_item = 'VPV13995'; // 全票
                    };
                    // 85 折
                    if ($row['discount'] == 0.85) {
                        $test_item = 'VUI20311'; // 測試票 
                        $normal_item = 'VKP79582'; // 全票
                    };
                    // 8 折
                    if ($row['discount'] == 0.8) {
                        $test_item = 'VXW37637'; // 測試票 
                        $normal_item = 'VUT70676'; // 全票
                    };
                };
            };
        };
    };
    // 品項
    $ticket_info = [];
    // 測試
    if ($issue_row['ticket_test']) {
        array_push($ticket_info, [
            'ItemNo' => $test_item, // 商品編號
            'TicketAmount' => $issue_row['ticket_test'], // 票券發行張數
            'StartDate' => '', // 票券生效日
            'ExpireDate' => '' // 贈品券到期日期
        ]);
    };
    // 超級早鳥票
    if ($issue_row['ticket_discount']) {
        array_push($ticket_info, [
            'ItemNo' => $discount_item, // 商品編號
            'TicketAmount' => $issue_row['ticket_discount'], // 票券發行張數
            'StartDate' => '', // 票券生效日
            'ExpireDate' => '' // 贈品券到期日期
        ]);
    };
    // 早鳥
    if ($issue_row['ticket_early']) {
        array_push($ticket_info, [
            'ItemNo' => $early_item, // 商品編號
            'TicketAmount' => $issue_row['ticket_early'], // 票券發行張數
            'StartDate' => '', // 票券生效日
            'ExpireDate' => '' // 贈品券到期日期
        ]);
    };
    // 全票
    if ($issue_row['ticket_normal']) {
        array_push($ticket_info, [
            'ItemNo' => $normal_item, // 商品編號
            'TicketAmount' => $issue_row['ticket_normal'], // 票券發行張數
            'StartDate' => '', // 票券生效日
            'ExpireDate' => '' // 贈品券到期日期
        ]);
    };
    // 親子套票
    if ($issue_row['ticket_family']) {
        array_push($ticket_info, [
            'ItemNo' => $family_item, // 商品編號
            'TicketAmount' => $issue_row['ticket_family'], // 票券發行張數
            'StartDate' => '', // 票券生效日
            'ExpireDate' => '' // 贈品券到期日期
        ]);
    };
    // 取得會員資料
    $member = $db->query("SELECT username, phone_number, email FROM attend WHERE member_code='" . $_COOKIE['member_code'] . "'");
    $member_row = $member->fetch_assoc();
    // call API
    $arData = [ 
        'MerchantID' => $merchantid, // 特店編號
        'MerchantTradeNo' => $issue_row['order_code'], // 特店訂單編號
        'FreeTradeNo' => '', // 贈品單號
        'IssueType' => '2', // 出券類型 1：超商票券 2：紙本票券 3：電子票券
        'PrintType' => '2', // 列印方式 1：綠界列印 2：廠商列印
        'IsImmediate' => '1', // 是否即期使用 1：即期 2：非即期
        'RefundNotifyURL' => 'http://ecticket.ecpay.com.tw/RefundNotify', // 退款結果主動通知 URL
        'Operator' => 'Michael', // 建立人員
        'CustomerName' => $member_row['username'],
        'CustomerPhone' => $member_row['phone_number'], // 購買人手機
        'CustomerEmail' => $member_row['email'], // 購買人電子郵件信箱
        'CustomerAddress' => '', // 購買人地址
        'TicketInfo' => $ticket_info, // 商品
    ];

    /******************************************************************************************************************************************/
    // 算檢查碼
    // 轉 Json 格式
    $szData = json_encode($arData);
    function _getMacValue($szHashKey, $szHashIV, $szData)
    {
        $encode_str = $szHashKey . $szData . $szHashIV;
        $encode_str = strtolower(urlencode($encode_str));
        return strtoupper(hash('sha256', $encode_str));
    };
    $CMV = _getMacValue($szHashKey, $szHashIV, $szData);
    //做 urlencode
    $szData = urlencode($szData);
    //定義 AES
    $oCrypter = new AESCrypter($szHashKey, $szHashIV);
    // 加密 Data 參數內容
    $szData = $oCrypter->Encrypt($szData);
    // 要 POST 的參數
    $arParameters = [
        'PlatformID' => $szPlatformID,
        'MerchantID' => $merchantid,
        'RqHeader' => ['Timestamp' => time()],
        'Data' => $szData,
        'CheckMacValue' => $CMV
    ];
    // 轉 Json 格式
    $arParameters = json_encode($arParameters);
    // 傳遞參數至遠端。
    $szResult = $oService->ServerPost($arParameters);
    // 判斷回傳是否為 Json 格式
    $ResultisJson = isJson($szResult);
    if ($ResultisJson == TRUE) {
        $DataisNull = json_decode($szResult, true);
        if (isset($DataisNull['Data'])) {
            if ($DataisNull['Data'] !== '') {
                // 將 Data 解密
                $DataDec = $oCrypter->Decrypt($DataisNull['Data']);
                $DataDec1 = json_decode($DataDec, true);
                if (isset($DataDec1['RtnCode'])) {
                    if ($DataDec1['RtnCode'] === 1) {
                        $db->query("UPDATE invoice SET ticket_order='" . $DataDec1['TicketTradeNo'] . "', issued='" . 'true' . "' WHERE order_code='" . $issue_row['order_code'] . "'");
                        echo $DataDec;
                    } else {
                        echo $DataDec;
                    };
                } else {
                    exit(json_encode(['result' => 'fail', 'message' => 'Data 未含有 RtnCode']));
                };
            } else {
                exit(json_encode(['result' => 'fail', 'message' => 'Data 回傳空值']));
            };
        } else {
            exit(json_encode(['result' => 'fail', 'message' => '回傳沒有 Data']));
        };
    } else {
        exit(json_encode(['result' => 'fail', 'message' => '回傳格錯誤，非 Json 格式']));
    };
};
// 判斷是否為 json
function isJson($data = '', $assoc = false)
{
    $data = json_decode($data, $assoc);
    if ($data && (is_object($data)) || (is_array($data) && !empty($data))) {
        return $data;
    };
    return false;
};
/************************************服務類別*************************************************/
// 呼叫網路服務的類別。
class NetworkService
{
    // 網路服務類別呼叫的位址。
    public $ServiceURL = 'ServiceURL';
    // 網路服務類別的建構式。
    function __construct()
    {
        $this->NetworkService();
    }
    // 網路服務類別的實體。
    function NetworkService()
    {
    }
    // 提供伺服器端呼叫遠端伺服器 Web API 的方法。
    function ServerPost($parameters)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->ServiceURL);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Content-Length: ' . strlen($parameters)));
        $rs = curl_exec($ch);
        curl_close($ch);
        return $rs;
    }
}

// AES 加解密服務的類別。
class AesCrypter
{
    private $Key = 'f629eece3cca4283';
    private $IV = '078802cacc3b4c63';
    // AES 加解密服務類別的建構式。
    function __construct($key, $iv)
    {
        $this->AesCrypter($key, $iv);
    }
    // AES 加解密服務類別的實體。
    function AesCrypter($key, $iv)
    {
        $this->Key = $key;
        $this->IV = $iv;
    }
    // 加密服務的方法。
    function Encrypt($data)
    {
        $szData = openssl_encrypt($data, 'AES-128-CBC', $this->Key, OPENSSL_RAW_DATA, $this->IV);
        $szData = base64_encode($szData);
        return $szData;
    }
    // 解密服務的方法。
    function Decrypt($data)
    {
        $szValue = openssl_decrypt(base64_decode($data), 'AES-128-CBC', $this->Key, OPENSSL_RAW_DATA, $this->IV);
        $szValue = urldecode($szValue);
        return $szValue;
    }
}

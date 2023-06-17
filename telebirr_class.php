<?php


require_once('../../../wp-load.php');


class TelebirrClass
{
	private $publicKey;
	private $appKey;
	private $appId;
	private $api;
	private $shortCode;
	private $notifyUrl;
	private $returnUrl;
	private $timeoutExpress;
	private $receiveName;
	private $totalAmount;
	private $subject;


	function __construct(
		
		$totalAmount,
		$notifyUrl,
		$returnUrl,
		$subject
	)
	{
		$options = get_option( 'telebirr_options' );
		$this->publicKey = $options['telebirr_field_public_key'];
		$this->appKey =$options['telebirr_field_api_key'];
		$this->appId = $options['telebirr_field_api_id'];
		$this->api = $options['telebirr_field_api_url'];
		$this->shortCode = $options['telebirr_field_api_shortcode'];
		$this->notifyUrl = $notifyUrl;
		$this->returnUrl = $returnUrl;
		$this->timeoutExpress = (int)$options['telebirr_field_qr_timeout'];
		$this->receiveName = $options['telebirr_field_reciver_name'];
		$this->totalAmount = $totalAmount;
		$this->subject = $subject;
	}


	private static $data = null;

	private   function getData()
	{
		$nonce = time();
		$result = md5(rand());

		self::$data =   [
			'outTradeNo' => $result,
			'subject' => $this->subject,
			'totalAmount' => $this->totalAmount,
			'shortCode' => $this->shortCode,
			'notifyUrl' => $this->notifyUrl,
			'returnUrl' => $this->returnUrl,
			'receiveName' => $this->receiveName,
			'appId' => $this->appId,
			'timeoutExpress' => $this->timeoutExpress,
			'nonce' => $result,
			'timestamp' => $nonce,
			'appKey' => $this->appKey
		];

	}
	
	public function getSign()
	{
		$this->getData();

		ksort(self::$data);
		$StringA = '';
		foreach (self::$data as $k => $v) {
			if ($StringA == '') {
				$StringA = $k . '=' . $v;
			} else {
				$StringA = $StringA . '&' . $k . '=' . $v;
			}
		}
		$StringB = hash("sha256", $StringA);

		return strtoupper($StringB);

	}

	/**
	 * getPaymentUrl returns the to pay url
	 */

	public function getPyamentUrl()
	{
		

		$sign = $this->getSign();
		$ussd = $this->encryptRSA();
		$requestMessage = [
			'appid' => $this->appId,
			'sign' => $sign,
			'ussd' => $ussd
		];
		$curl = curl_init($this->api);
		curl_setopt($curl, CURLOPT_URL, $this->api);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		$headers = array(
			"Accept: application/json",
			"Content-Type: application/json",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
		$data = json_encode($requestMessage);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		
		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		
		$resp = curl_exec($curl);
		curl_close($curl);
		// var_dump($resp);
		
		return json_decode($resp, true);
	}

	/**
	 * encryptRSA encrypt the data using the public key
	 * 
	 * @data	the data tobe encrypted
	 * @public	public key from telebirr
	 */

	public function encryptRSA()
	{
		$public = $this->publicKey;
		$pubPem = chunk_split($public, 64, "\n");
		$pubPem = "-----BEGIN PUBLIC KEY-----\n" . $pubPem . "-----END PUBLIC KEY-----\n";
		$public_key = openssl_pkey_get_public($pubPem);
	
		if (!$public_key) {
			die('invalid public key');
		}
		$crypto = '';
		foreach (str_split(json_encode(self::$data), 117) as $chunk) {
			$return = openssl_public_encrypt($chunk, $cryptoItem, $public_key);
			if (!$return) {
				return ('fail');
			}
			$crypto .= $cryptoItem;
		}
		$ussd = base64_encode($crypto);
		return $ussd;
	}
}

?>



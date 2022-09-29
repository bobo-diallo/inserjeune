<?php

namespace App\Services;


class ConnectServerService {
	public static array $headers = array('Content-Type:application/json');
	public static int $httpResponseCode = 404;

	public static function call(
		$url,
		string $method = 'GET',
		$params = null,
		$certificat = null,
		int $timeout = 30
	): ResponseAPI {
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false
		));

		// Verification certificat
		if ($certificat != null) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($curl, CURLOPT_CAINFO, $certificat);
		}

		// les données sont mises en parametres
		if ($params != null) {
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
			// curl_setopt($curl, CURLOPT_POST, count($params));
		}

		if (self::$headers != null) curl_setopt($curl, CURLOPT_HTTPHEADER, self::$headers);

		$beginDate = time();
		$responseData = curl_exec($curl);
		$duree = time() - $beginDate;

		self::$httpResponseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$curlCode = curl_errno($curl);
		$curlMessage = curl_error($curl);

		LoggerWs::logReponseWs($curlCode, $curlMessage, self::$httpResponseCode, $responseData, $duree);


		if (self::$httpResponseCode >= 200 & self::$httpResponseCode < 300) {
			$responseAPI = new ResponseAPI();
			$responseAPI->responseData = $responseData;
		} else {
			$responseAPI = new ResponseAPI(1, 'Erreur appel API', $responseData);
		}

		curl_close($curl);

		return $responseAPI;
	}

	public static function callFormData(
		$url,
		string $method = 'GET',
		$params = null,
		$certificat = null,
		int $timeout = 30
	): ResponseAPI {
		if ($params != null) $params = http_build_query($params);

		return self::call($url, $method, $params, $certificat, $timeout);

	}

	public static function callUrlEncoding(
		$url,
		string $method = 'GET',
		$params = null,
		$certificat = null,
		int $timeout = 30
	): ResponseAPI {
		if ($params != null) {
			$params = http_build_query($params);
			$url .= "?$params";
		}

		return self::call($url, $method, $params, $certificat, $timeout);
	}

	public static function callInputJson(
		$url,
		string $method = 'GET',
		$params = null,
		$certificat = null,
		int $timeout = 30
	) {
		if ($method != 'GET') {
			$params = json_encode($params);
		} else if ($params != null) {
			$url .= "?" . http_build_query($params);
		}

		return self::call($url, $method, $params, $certificat, $timeout);

	}

}

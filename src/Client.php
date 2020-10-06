<?php

namespace Histrix\API;

class Client
{
	private $handle = null;
	private $curlOptions = [ CURLOPT_RETURNTRANSFER => 1 ];
	private $token = null;
	private $url;
	private $id = null;
	private $secret = null;
	private $userdata = [];

	public function __construct($url, $username, $password, $grant_type, $auth)
	{
		if($url == '' || $username == '' || $password == '' || $grant_type == '')
		{
			throw new \Exception("Bad Request");
		}

		if(!is_array($auth) || !isset($auth[0]) || !isset($auth[1]) )
		{
			throw new \Exception("No client authentication provided, you must provide id/secret in order to continue", 1);
		}

		$this->id = $auth[0];
		$this->secret = $auth[1];
		$this->url = $url;
		$this->userdata['username'] = $username;
		$this->userdata['password'] = $password;
		$this->userdata['grant_type'] = $grant_type;

		$this->handle = curl_init();

		return $this->checkToken();
	}

	public function __destruct()
	{
		curl_close($this->handle);
	}

	private function request($endpoint, $post=false, $data = [])
	{
		$response = false;

		$url = $this->url . $endpoint;

		if($post == true)
		{
			$this->curlOptions[CURLOPT_POST] = 1;
			$this->curlOptions[CURLOPT_POSTFIELDS] = $data;
		}
		else
		{
			unset($this->curlOptions[CURLOPT_POST]);
			unset($this->curlOptions[CURLOPT_POSTFIELDS]);
			$this->curlOptions[CURLOPT_HTTPGET] = true;

			if( !empty($data))
			{
				$url .= '?' . http_build_query($data);
			}
		}

		$this->curlOptions[CURLOPT_URL] = $url;

		curl_setopt_array($this->handle, $this->curlOptions);

		$response = curl_exec($this->handle);
		$err = curl_error($this->handle);

	    if($err != '')
	    {
	    	throw new \Exception("Request Error: $err", 1);
		}

	    $response = json_decode($response, true);

	    if( json_last_error() !== JSON_ERROR_NONE )
	    {
	    	throw new \Exception("Malformat response", 1);
	    }

		return $response;
	}

  	//Verifico que el token exista, si no lo esta solicito uno.
  	private function checkToken()
  	{
  		$isTokenOk = false;

		if( is_null($this->token) )
		{
			$headers = [ 'Authorization: Basic ' . base64_encode($this->id.':'.$this->secret) ];
			$this->curlOptions[CURLOPT_HTTPHEADER] = $headers;
			$this->curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;

			$response = $this->request("/token", true, $this->userdata);

			if( isset($response['access_token']) )
	        {
	        	$this->token = $response['access_token'];
	        	$isTokenOk = true;
	        }
		}
		else
		{
			$headers = [ 'Authorization: Bearer ' . $this->token ];
			$this->curlOptions[CURLOPT_HTTPHEADER] = $headers;
			unset($this->curlOptions[CURLOPT_HTTPAUTH]);
			$isTokenOk = true;
		}

 		return $isTokenOk;
  	}


  	// Public Methods

  	public function get($endpoint, $params = [])
  	{
		$response = [];

		if( !$this->checkToken() )
		{
			throw new \Exception("Error Processing Request");
		}
		else
		{
			$response = $this->request($endpoint, false, $params);
		}

		return $response;
  	}

}

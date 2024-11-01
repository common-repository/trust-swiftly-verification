<?php
namespace TrustSwiftly\Api;

use GuzzleHttp\ClientInterface as GuzzleInterface;
use Exception;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Request;
use TrustSwiftly\Exceptions\ApiException;
use TrustSwiftly\Exceptions\RequestTimeOut;
use TrustSwiftly\Managers\ConfigManager;
use TrustSwiftly\Managers\BaseResponseManager;
use TrustSwiftly\Exceptions\HttpException;

/**
 * Base Client
 */
class Client {


    const TIMEOUT = 120;
    const CONNECT_TIMEOUT=10;

    /**
     * @var $base_url
     */
    protected $base_url;

    /**
     * @var GuzzleInterface
     */
    protected $client;

    /**
     * @var ConfigManager
     */
    protected $config;

    /**
     * @var $response
     */
    protected $response;

    /**
     * @var $status
     */
    protected $status;


    /**
     * @param GuzzleInterface $client
     * @param ConfigManager $config
     */
    public function __construct(GuzzleInterface $client, ConfigManager $config)
    {
        $this->client = $client;
        $this->config = $config;

        $this->setBaseUrl($config->getBaseUrl());
    }

    /**
     * @param $url
     */
    protected function setBaseUrl($url){
        $this->base_url=$url;
    }

    /**
     * @param $url
     * @param array|null $data
     * @param array|null $params
     * @return BaseResponseManager
     * @throws HttpException
     * @throws RequestTimeOut
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($url, array $data = null, array $params = null)
    {
        return $this->request( 'get',$url, $data, $params);
    }

    /**
     * @param $url
     * @param array|null $data
     * @param array|null $params
     * @return BaseResponseManager
     * @throws HttpException
     * @throws RequestTimeOut
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($url, array $data = null, array $params = null)
    {
        return $this->request( 'post',$url, $data, $params);
    }

    /**
     * @param $url
     * @param array|null $data
     * @param array|null $params
     * @return BaseResponseManager
     * @throws HttpException
     * @throws RequestTimeOut
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put($url, array $data = null, array $params = null)
    {
        return $this->request( 'put',$url, $data, $params);
    }

    /**
     * @param $url
     * @param array|null $data
     * @param array|null $params
     * @return BaseResponseManager
     * @throws HttpException
     * @throws RequestTimeOut
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function patch($url, array $data = null, array $params = null)
    {
        return $this->request( 'patch',$url, $data, $params);
    }

    /**
     * @param $url
     * @param array|null $data
     * @param array|null $params
     * @return BaseResponseManager
     * @throws HttpException
     * @throws RequestTimeOut
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($url, array $data = null, array $params = null)
    {
        return $this->request( 'delete',$url, $data, $params);
    }

    /**
     * @return string[]
     */
    public function getAuthHeader(){
        return ['Authorization'=>'Bearer '.$this->config->getApiKey()];
    }

    /**
     * @param string $method
     * @param string $url
     * @param array|null $data
     * @param array|null $params
     * @param array $headers
     * @param int|null $timeout
     * @param int|null $connect_timeout
     * @return BaseResponseManager
     * @throws HttpException
     * @throws RequestTimeOut
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $method, string $url,array $data = null, array $params = null, array $headers = [],int $timeout = null,int $connect_timeout = null) {
        try {
            $defaultHeader=[
                'Content-type' => 'application/json',
            ];
            $headers=array_merge($headers,$this->getAuthHeader(),$defaultHeader);

            $body = is_null($data)?'':json_encode($data);

            $options = [
                'timeout' => is_null($timeout) ? self::TIMEOUT:$timeout,
                'connect_timeout' => is_null($connect_timeout) ? self::CONNECT_TIMEOUT:$connect_timeout,
                'body' => $body,
                'verify'=>false
            ];
            if ($params) {
                $options['query'] = $params;
            }

            $response = $this->client->send(new Request($method, $url, $headers), $options);
        } catch (BadResponseException $exception) {
            $response = $exception->getResponse();
        } catch (GuzzleConnectException $exception) {
            throw new RequestTimeOut('HTTP request timed out', 0, $exception);
        } catch (\Exception $exception) {
            var_dump($exception);
            throw new HttpException('Unable to complete the HTTP request', 0, $exception);
        }
        
        return new BaseResponseManager($response->getStatusCode(), (string)$response->getBody(), $response->getHeaders());
    }

    /**
     * @param $response
     * @return bool
     * @throws ApiException
     */
    public function checkResponse($response){
        $responseBody=$response->getResponseBody();
        $responseArray=json_decode($responseBody,true);
        if(empty($responseArray)){
            $responseArray = [];
        }
        if (array_key_exists('error',$responseArray)){
            throw new ApiException($responseArray['error']['error_message']);
        }elseif (array_key_exists('error_type',$responseArray)){
            throw new ApiException($responseArray['error_message']);
        }else{
            return true;
        }
    }

}
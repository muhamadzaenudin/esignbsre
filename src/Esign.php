<?php

namespace Muhamadzaenudin\Esignbsre;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use RuntimeException;

class Esign
{
    private $client;
    private $baseUrl;
    private $username;
    private $password;
    private $timeout = 30;
    private $type = 'invisible';

    public function __construct($configServer)
    {
        $this->client = new Client();
        $this->baseUrl = $configServer['base_url'];
        $this->username = $configServer['username'];
        $this->password = $configServer['password'];
    }

    public function statusUser($nik = '')
    {
        try {
            $response = $this->client->request('GET', "{$this->getBaseUrl()}/api/user/status/$nik", [
                'auth' => $this->getAuth(),
                'timeout' => $this->timeout,
            ]);
            $body = json_decode($response->getBody(), true);
            return Response::success($body, $body['message']);
        } catch (ConnectException $e) {
            return Response::error(
                'Tidak bisa terhubung ke server BSrE',
                $e->getMessage(),
                Response::STATUS_TIMEOUT
            );
        } catch (RequestException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500;
            $errorData = $e->hasResponse() ? json_decode($e->getResponse()->getBody(), true) : $e->getMessage();
            return Response::error(
                'Periksa koneksi ke server atau NIK tidak boleh kosong',
                $errorData,
                $status
            );
        }
    }

    public function sign($configSign)
    {
        $multipart = new Data($this->type, $configSign);
        $file = $configSign['saveTo'] . $configSign['filename'] . '.pdf';
        try {
            $response = $this->client->request('POST', "{$this->getBaseUrl()}/api/sign/pdf", [
                'auth' => $this->getAuth(),
                'timeout' => $this->timeout,
                'multipart' => $multipart->getData(),
                'sink' => $file,
            ]);
            $body = json_decode($response->getBody(), true);
            $bodyMessage = "Dokumen tertanda tangani dengan id_dokumen {$response->getHeader('id_dokumen')[0]}, ";
            $bodyMessage .= "terunduh {$configSign['filename']}.pdf";
            return Response::success($body, $bodyMessage);
        } catch (ConnectException $e) {
            return Response::error(
                'Tidak bisa terhubung ke server BSrE',
                $e->getMessage(),
                Response::STATUS_TIMEOUT
            );
        } catch (ClientException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500;
            $errorData = $e->hasResponse()
                ? ($this->isJson($e)
                    ? json_decode($e->getResponse()->getBody(), true)
                    : $e->getResponse()->getBody()->getContents())
                : $e->getMessage();
            if (file_exists($file)) unlink($file);
            return Response::error(
                'Periksa koneksi ke server dan parameter sign',
                $errorData,
                $status
            );
        }
    }

    public function verify($configVerify)
    {
        try {
            $response = $this->client->request('POST', "{$this->getBaseUrl()}/api/sign/verify", [
                'auth' => $this->getAuth(),
                'timeout' => $this->timeout,
                'multipart' => [
                    [
                        'name' => 'signed_file',
                        'contents' => Psr7\Utils::tryFopen($configVerify['signed_file'], 'r')
                    ],
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            return Response::success($body, json_decode($response->getBody()->getContents(), true));
        } catch (ConnectException $e) {
            return Response::error(
                'Tidak bisa terhubung ke server BSrE',
                $e->getMessage(),
                Response::STATUS_TIMEOUT
            );
        } catch (ClientException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 500;
            $errorData = $e->hasResponse()
                ? ($this->isJson($e)
                    ? json_decode($e->getResponse()->getBody(), true)
                    : $e->getResponse()->getBody()->getContents())
                : $e->getMessage();
            return Response::error(
                'Periksa koneksi ke server dan parameter verify',
                $errorData,
                $status
            );
        } catch (RuntimeException $e) {
            return Response::error(
                'Periksa alamat dan file yang akan diverifikasi',
                $e->getMessage(),
                422
            );
        }
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    private function getAuth()
    {
        return [$this->username, $this->password];
    }

    private function getBaseUrl()
    {
        return rtrim($this->baseUrl, "/");
    }

    private function isJson($string): bool
    {
        if (!is_string($string)) return false;

        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}

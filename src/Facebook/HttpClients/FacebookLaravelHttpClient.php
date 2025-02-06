<?php

namespace Facebook\HttpClients;

use Facebook\Http\GraphRawResponse;
use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Support\Facades\Http; // Usa o HTTP Client do Laravel

class FacebookLaravelHttpClient implements FacebookHttpClientInterface
{
    /**
     * @inheritdoc
     */
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        // Configuração inicial do cliente HTTP
        $client = Http::withHeaders($headers)
            ->timeout($timeOut)
            ->connectTimeout(10)
            ->withOptions(['verify' => __DIR__ . '/certs/DigiCertHighAssuranceEVRootCA.pem']);

        try {
            // Escolher o método da requisição HTTP
            if ($method === 'GET') {
                $response = $client->get($url);
            } elseif ($method === 'POST') {
                $response = $client->post($url, $body);
            } elseif ($method === 'PUT') {
                $response = $client->put($url, $body);
            } elseif ($method === 'DELETE') {
                $response = $client->delete($url);
            } else {
                throw new FacebookSDKException("Método HTTP não suportado: {$method}");
            }
        } catch (\Exception $e) {
            // Tratamento de erros
            throw new FacebookSDKException($e->getMessage(), $e->getCode());
        }

        // Constrói a resposta
        $rawHeaders = $this->getHeadersAsString($response->headers());
        $rawBody = $response->body();
        $httpStatusCode = $response->status();

        return new GraphRawResponse($rawHeaders, $rawBody, $httpStatusCode);
    }

    /**
     * Retorna os cabeçalhos como string.
     *
     * @param array $responseHeaders Os cabeçalhos da resposta.
     *
     * @return string
     */
    public function getHeadersAsString(array $responseHeaders)
    {
        $rawHeaders = [];
        foreach ($responseHeaders as $name => $values) {
            $rawHeaders[] = $name . ": " . implode(", ", $values);
        }

        return implode("\r\n", $rawHeaders);
    }
}
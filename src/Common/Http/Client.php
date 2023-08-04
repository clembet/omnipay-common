<?php

namespace Omnipay\Common\Http;

use GuzzleHttp\Psr7\Utils;
use function GuzzleHttp\Psr7\str;
use Psr\Http\Client\ClientInterface as Psr18ClientInterface;
use Omnipay\Common\Http\ClientInterface;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;
use Omnipay\Common\Http\Exception\NetworkException;
use Omnipay\Common\Http\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Client implements ClientInterface, Psr18ClientInterface
{
    /**
     * The Http Client which implements `public function sendRequest(RequestInterface $request)`
     * Note: Will be changed to PSR-18 when released
     *
     * @var Psr18ClientInterface
     */
    private $httpClient;

    /**
     * @var Psr17RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var Psr17StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct($httpClient = null, RequestFactory $requestFactory = null, StreamFactory $streamFactory = null)
    {
        $this->httpClient = $httpClient ?: Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();   
    }

    /**
     * @param $method
     * @param $uri
     * @param array $headers
     * @param string|array|resource|StreamInterface|null $body
     * @param string $protocolVersion
     * @return ResponseInterface
     * @throws \Http\Client\Exception
     */
    public function request(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ): ResponseInterface {
        $request = $this->requestFactory->createRequest($method, $uri)->withProtocolVersion($protocolVersion);
        $idxHeader = 0;
        foreach ($headers as $name => $value) {
			$request = $request->withHeader($name, $value);//if($idxHeader > 0)$request->withAddedHeader($name, $value);
            ++$idxHeader;
		}
        $bodyStream = null;
        if($body != null && is_string($body) && is_array(json_decode($body, true)))
        {
            //$bodyStream = Utils::streamFor($body);
            $bodyStream = $this->streamFactory->createStream($body);
        }
		if ($bodyStream instanceof StreamInterface) {
			$request = $request->withBody($bodyStream);
		}

        return $this->sendRequest($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Http\Client\Exception
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest($request);
        } catch (\Http\Client\Exception\NetworkException $networkException) {
            throw new NetworkException($networkException->getMessage(), $request, $networkException);
        } catch (\Exception $exception) {
            throw new RequestException($exception->getMessage(), $request, $exception);
        }
    }
}

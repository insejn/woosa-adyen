<?php

namespace Woosa\Adyen\Adyen\HttpClient;

interface ClientInterface
{
    /**
     * @param \Adyen\Service $service
     * @param $requestUrl
     * @param $params
     * @return mixed
     */
    public function requestJson(\Woosa\Adyen\Adyen\Service $service, $requestUrl, $params);
    /**
     * @param \Adyen\Service $service
     * @param $requestUrl
     * @param $params
     * @return mixed
     */
    public function requestPost(\Woosa\Adyen\Adyen\Service $service, $requestUrl, $params);
}

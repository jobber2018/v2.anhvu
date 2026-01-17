<?php

namespace Supplier\Service;

use Laminas\Http\Request;
use Laminas\Http\Client as HttpClient;
use Laminas\Json\Json;

class TaxLookupService
{
    private HttpClient $http;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
        $this->http->setOptions([
            'timeout' => 8,
        ]);
    }

    /**
     * HÀM CACHE
     */
    private function cachedGetCompany(string $taxCode, bool $isLatests): ?array
    {
        $cacheKey = $taxCode . '_' . ($isLatests ? '1' : '0');
        $cacheFile = sys_get_temp_dir() . "/tax_lookup_{$cacheKey}.json";
        $ttl = 3600; // cache 1 giờ

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        // nếu chưa có cache → gọi API thực
        $data = $this->callApi($taxCode, $isLatests);

        if ($data) {
            file_put_contents($cacheFile, json_encode($data));
        }

        return $data;
    }

    /**
     * HÀM GỌI API THẬT
     */
    private function callApi(string $taxCode, bool $isLatests): ?array
    {
        $url = sprintf(
            'https://actapp.misa.vn/g1/api/graph/v1/company/get_company_tax_code?taxCode=%s&isLatests=%s',
            urlencode($taxCode),
            $isLatests ? 'true' : 'false'
        );

        $this->http->setUri($url);
        $this->http->setMethod(Request::METHOD_GET);

        try {
            $response = $this->http->send();
        } catch (\Exception $e) {
            return null;
        }

        if (!$response->isSuccess()) {
            return null;
        }

        return Json::decode($response->getBody(), Json::TYPE_ARRAY);
    }

    /**
     * API PUBLIC gọi từ Controller
     */
    public function getCompanyByTaxCode(string $taxCode, bool $isLatests = false): ?array
    {
        return $this->cachedGetCompany($taxCode, $isLatests);
    }
}
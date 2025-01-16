<?php

declare(strict_types=1);

namespace Etobi\Autodns\Service;

use GuzzleHttp\Client;

class AutoDnsXmlService
{
    public const BASEURI = 'https://gateway.schlundtech.de/';

    public function __construct(
        private readonly string $gateway = 'https://gateway.schlundtech.de/',
        private readonly string $username = '',
        private readonly string $password = '',
        private readonly int $context = 10,
    )
    {

    }

    public function getDomains(): array
    {
        $responseXml = $this->task('0105');
        $domains = [];
        foreach ($responseXml?->result?->data?->domain ?? [] as $xmlDomain) {
            $domains[] = [
                'name' => (string)$xmlDomain->name,
                'created' => (string)$xmlDomain->created,
                'payable' => (string)$xmlDomain->payable,
                'updated' => (string)$xmlDomain->updated,
            ];
        }
        return $domains;
    }

    public function getZoneInfo(string $name, string $systemNs): array
    {
        $responseXml = $this->task(
            '0205',
            '
                <zone>
                    <name>' . $name . '</name>
                </zone>
            '
        );
        $zone = $responseXml->result?->data?->zone;
        $rrs = [];
        $nss = [];
        foreach ($zone?->rr ?? [] as $xmlRr) {
            $rrs[] = [
                'name' => (string)$xmlRr->name,
                'type' => (string)$xmlRr->type,
                'value' => (string)$xmlRr->value,
                'pref' => (string)$xmlRr->pref,
                'ttl' => (string)$xmlRr?->ttl,
            ];
        }
        foreach ($zone?->nserver ?? [] as $xmlRr) {
            $nss[] = (string)$xmlRr->name;
        }
        return [
            'main' => (string)$zone->main?->value,
            'www_include' => (string)$zone->www_include,
            'nserver' => $nss,
            'rr' => $rrs
        ];
    }

    public function getZones(string $zoneName = null): array
    {
        $responseXml = $this->task(
            '0205',
            '
            <view>
                <limit>' . ($zoneName ? '1' : '9999') . '</limit>
            </view>
            <key>mainip</key>
            <key>primary</key>
            <key>secondary1</key>
            <key>secondary2</key>
            <key>secondary3</key>
            <key>soa</key>
            <order>
                <key>name</key>
                <mode>ASC</mode>
            </order>
            '
            . (
                $zoneName
                    ? '
                        <where>
                            <key>name</key>
                            <operator>eq</operator>
                            <value>' . $zoneName . '</value>
                        </where>
                    '
                    : ''
            )
        );

        $zones = [];
        foreach ($responseXml?->result?->data?->zone ?? [] as $xmlZone) {
            $zones[] = [
                'name' => (string)$xmlZone->name,
                'idn' => (string)$xmlZone->idn,
                'mainip' => (string)$xmlZone->mainip,
                'primary' => (string)$xmlZone->primary,
                'secondary1' => (string)$xmlZone->secondary1,
                'secondary2' => (string)$xmlZone->secondary2,
                'secondary3' => (string)$xmlZone->secondary3,
                'system_ns' => (string)$xmlZone->system_ns,
                'created' => (string)$xmlZone->created,
                'changed' => (string)$xmlZone->changed,
                'soa' => [
                    'refresh' => (string)$xmlZone->soa->refresh,
                    'retry' => (string)$xmlZone->soa->retry,
                    'expire' => (string)$xmlZone->soa->expire,
                    'ttl' => (string)$xmlZone->soa->ttl,
                    'email' => (string)$xmlZone->soa->email,
                    'default' => (string)$xmlZone->soa->default,
                ]
            ];
        }
        return $zones;
    }

    protected function task(string $code, string $parameter = ''): \SimpleXMLElement
    {
        $request = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<request>
    <auth>
        <user>' . $this->username . '</user>
        <password>' . $this->password . '</password>
        <context>' . $this->context . '</context>
    </auth>
    <task>
        <code>' . $code . '</code>
        ' . $parameter . '
    </task>
</request>
';

        $client = new Client([
            'base_uri' => $this->gateway,
            'timeout' => 2.0,
            'headers' => [
                'Accept' => 'application/xml',
                'Content-Type' => 'application/xml',
            ]
        ]);
        $response = $client->post(
            '',
            [
                'body' => $request,
            ]
        );
        return simplexml_load_string($response->getBody()->getContents());
    }
}

<?php

declare(strict_types=1);

namespace Etobi\Autodns\Service;

use GuzzleHttp\Client;

class AutoDnsXmlService
{
    public const BASEURI = 'https://gateway.schlundtech.de/';
    public const CONTEXT = 10;

    /*
     * TODO
     * addDkim
     * addDmarc
     * getDkim
     * getDmarc
     */
    public function __construct(
        private readonly string $gateway = self::BASEURI,
        private readonly string $username = '',
        private readonly string $password = '',
        private readonly int $context = self::CONTEXT,
    ) {
    }

    protected function task(string $code, string $parameter = ''): AutoDnsXmlResponse
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

        return new AutoDnsXmlResponse(
            \simplexml_load_string(
                $response->getBody()->getContents()
            )
        );
    }

    public function getDomains(): array
    {
        $response = $this->task('0105');
        $domains = [];
        foreach ($response->getResult()?->data?->domain ?? [] as $xmlDomain) {
            $domains[] = [
                'name' => (string)$xmlDomain->name,
                'created' => (string)$xmlDomain->created,
                'payable' => (string)$xmlDomain->payable,
                'updated' => (string)$xmlDomain->updated,
            ];
        }
        return $domains;
    }

    public function getZoneInfo(string $zoneName): array
    {
        $response = $this->task(
            '0205',
            '
                <zone>
                    <name>' . $zoneName . '</name>
                </zone>
            '
        );
        $zone = $response->getResult()?->data?->zone;
        $rrs = [];
        $nss = [];

        $rrs[] = [
            'name' => '',
            'type' => 'A',
            'value' => (string)$zone?->main?->value,
            'pref' => '',
            'ttl' => (string)$zone?->main?->ttl,
            'main' => true,
        ];
        if ((bool)$zone?->www_include) {
            $rrs[] = [
                'name' => 'www',
                'type' => 'A',
                'value' => (string)$zone?->main?->value,
                'pref' => '',
                'ttl' => (string)$zone?->main?->ttl,
                'www_include' => true,
            ];
        }

        foreach ($zone?->rr ?? [] as $xmlRr) {
            $rrs[] = [
                'name' => (string)$xmlRr?->name ?? '',
                'type' => (string)$xmlRr?->type ?? '?',
                'value' => (string)$xmlRr?->value ?? '',
                'pref' => (string)$xmlRr?->pref ?? '',
                'ttl' => (string)$xmlRr?->ttl ?? '',
            ];
        }
        foreach ($zone?->nserver ?? [] as $xmlRr) {
            $nss[] = (string)$xmlRr->name;
        }

        return [
            'nserver' => $nss,
            'rr' => $rrs,
            'response' => $response,
        ];
    }

    public function getZones(?string $zoneName = null): array
    {
        $response = $this->task(
            '0205',
            '
            <view>
                <limit>' . ($zoneName !== null ? '1' : '9999') . '</limit>
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
                $zoneName !== null
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
        foreach ($response->getResult()?->data?->zone ?? [] as $xmlZone) {
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
        return [
            'zones' => $zones,
            'response' => $response
        ];
    }


    public function setMainip(string $zoneName, string $ip, null|int|string $ttl = null): AutoDnsXmlResponse
    {
        return $this->task(
            '0202001',
            '
                <zone>
                    <name>' . $zoneName . '</name>
                </zone>
                <default>
                    <main>
                        <value>' . $ip . '</value>
                        ' . ($ttl !== null ? '<ttl>' . $ttl . '</ttl>' : '') . '
                    </main>
                </default>
            '
        );
    }

    public function addRecord(
        string $zoneName,
        string $type,
        string $value,
        ?string $name = null,
        null|string|int $ttl = null,
        ?string $pref = null
    ) {
        return $this->task(
            '0202001',
            '
                <zone>
                    <name>' . $zoneName . '</name>
                </zone>
                <default>
                  <rr_add>
                    <name>' . ($name ?? '') . '</name>
                    <type>' . $type . '</type>
                    <value>' . $value . '</value>
                    ' . ($ttl !== null ? '<ttl>' . $ttl . '</ttl>' : '') . '
                    ' . ($pref !== null ? '<pref>' . $pref . '</pref>' : '') . '
                  </rr_add>
                </default>
            '
        );
    }

    public function removeRecord(
        string $zoneName,
        string $type,
        string $value,
        ?string $name = null,
        null|string|int $ttl = null,
        ?string $pref = null
    ) {
        $response = $this->task(
            '0202001',
            '
                <zone>
                    <name>' . $zoneName . '</name>
                </zone>
                <default>
                  <rr_rem>
                    <name>' . ($name ?? '') . '</name>
                    <type>' . $type . '</type>
                    <value>' . $value . '</value>
                    ' . ($ttl !== null ? '<ttl>' . $ttl . '</ttl>' : '') . '
                    ' . ($pref !== null ? '<pref>' . $pref . '</pref>' : '') . '
                  </rr_rem>
                </default>
            '
        );
        return $response;
    }

    public function updateRecord(
        string $zoneName,
        string $type,
        string $oldvalue,
        string $newvalue,
        ?string $name = null,
        null|string|int $ttl = null,
        ?string $pref = null
    ) {
        return $this->task(
            '0202001',
            '
                <zone>
                    <name>' . $zoneName . '</name>
                </zone>
                <default>
                  <rr_rem>
                    <name>' . ($name ?? '') . '</name>
                    <type>' . $type . '</type>
                    <value>' . $oldvalue . '</value>
                    ' . ($ttl !== null ? '<ttl>' . $ttl . '</ttl>' : '') . '
                    ' . ($pref !== null ? '<pref>' . $pref . '</pref>' : '') . '
                  </rr_rem>
                  <rr_add>
                    <name>' . ($name ?? '') . '</name>
                    <type>' . $type . '</type>
                    <value>' . $newvalue . '</value>
                    ' . ($ttl !== null ? '<ttl>' . $ttl . '</ttl>' : '') . '
                    ' . ($pref !== null ? '<pref>' . $pref . '</pref>' : '') . '
                  </rr_add>
                </default>
            '
        );
    }

    public function searchAndReplace(
        string $zoneName,
        string $type,
        string $search,
        string $replace,
    ) {
        return $this->task(
            '0202001',
            '
                <zone>
                    <name>' . $zoneName . '</name>
                </zone>
                <default>
                  <search_and_replace>
                    <type>' . $type . '</type>
                    <search>' . $search . '</search>
                    <replace>' . $replace . '</replace>
                  </search_and_replace>
                </default>
            '
        );
    }

    public function updateSoa(string $zoneName, null|string|int $ttl = null): AutoDnsXmlResponse
    {
        return $this->task(
            '0202001',
            '
                <zone>
                    <name>' . $zoneName . '</name>
                </zone>
                <default>
                    <soa>
                        ' . ($ttl !== null ? '<ttl>' . $ttl . '</ttl>' : '') . '
                        <!--refresh>43200</refresh-->
                        <!--retry>7200</retry-->
                        <!--expire>1209600</expire-->
                        <!--email>soa@examle.com</email-->
                    </soa>
                </default>
            '
        );
    }
}

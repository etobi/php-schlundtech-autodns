<?php

declare(strict_types=1);

namespace Etobi\Autodns\Service;

class AutoDnsXmlResponse
{
    public function __construct(
        private readonly \SimpleXMLElement $rawResponse,
    ) {
        // TODO convert to array?
    }

    public function getResult(): ?\SimpleXMLElement
    {
        return $this->rawResponse?->result;
    }

    public function isStatusTypeSuccess(): bool
    {
        return (string)$this->rawResponse?->result?->status?->type === 'success';
    }

    public function getMessages(?\SimpleXMLElement $msg = null): array
    {
        $errorMessages = [];
        $msg = $msg ?? $this->rawResponse?->result?->msg;
        if ($msg?->msg !== null) {
            $errorMessages = array_merge(
                $errorMessages,
                $this->getMessages($msg?->msg)
            );
        }
        if ($msg !== null && $msg?->code !== null) {
            $errorMessages[] = [
                'type' => (string)$msg?->type,
                'text' => (string)$msg?->text,
                'code' => (string)$msg?->code
            ];
        }
        return $errorMessages;
    }
}

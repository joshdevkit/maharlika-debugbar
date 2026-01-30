<?php

namespace Maharlika\Debugbar\DataCollector;

class SessionCollector implements CollectorInterface
{
    protected ?array $sessionData = null;

    public function getName(): string
    {
        return 'session';
    }

    public function setSessionData(array $data): void
    {
        $this->sessionData = $data;
    }

    public function getData(): array
    {
        if ($this->sessionData === null) {
            return [
                'active' => false,
            ];
        }

        return [
            'active' => true,
            'data' => $this->filterSensitiveData($this->sessionData),
            'count' => count($this->sessionData),
        ];
    }

    protected function filterSensitiveData(array $data): array
    {
        $sensitive = ['password', 'token', 'secret', 'api_key', 'private_key'];
        $filtered = [];

        foreach ($data as $key => $value) {
            if ($this->isSensitive($key, $sensitive)) {
                $filtered[$key] = '********';
            } elseif (is_array($value)) {
                $filtered[$key] = $this->filterSensitiveData($value);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    protected function isSensitive(string $key, array $sensitive): bool
    {
        $key = strtolower($key);
        foreach ($sensitive as $word) {
            if (str_contains($key, $word)) {
                return true;
            }
        }
        return false;
    }

    public function reset(): void
    {
        $this->sessionData = null;
    }
}
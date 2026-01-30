<?php

namespace Maharlika\Debugbar\DataCollector;

use Maharlika\Http\Request;

class RequestCollector implements CollectorInterface
{
    protected ?Request $request = null;
    protected ?object $response = null;

    public function getName(): string
    {
        return 'request';
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setResponse(object $response): void
    {
        $this->response = $response;
    }

    public function getData(): array
    {
        if (!$this->request) {
            return [];
        }

        $data = [
            'method' => $this->request->method(),
            'uri' => $this->request->getRequestUri(),
            'url' => $this->request->url(),
            'path' => $this->request->path(),
            'ip' => $this->request->ip(),
            'ajax' => $this->request->ajax(),
            'query' => $this->request->query->all(),
            'request' => $this->request->request->all(),
            'files' => $this->getFileInfo(),
            'cookies' => $this->request->cookies->all(),
            'headers' => $this->request->headers->all(),
            'server' => $this->getServerInfo(),
        ];

        if ($this->response) {
            $data['response'] = $this->getResponseInfo();
        }

        return $data;
    }

    protected function getFileInfo(): array
    {
        $files = [];
        foreach ($this->request->files->all() as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $f) {
                    $files[$key][] = [
                        'name' => $f->getClientOriginalName(),
                        'size' => $f->getSize(),
                        'type' => $f->getMimeType(),
                    ];
                }
            } else {
                $files[$key] = [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }
        return $files;
    }

    protected function getServerInfo(): array
    {
        return [
            'PHP_VERSION' => PHP_VERSION,
            'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? '',
            'HTTPS' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ];
    }

    protected function getResponseInfo(): array
    {
        $info = [];

        if (method_exists($this->response, 'getStatusCode')) {
            $info['status'] = $this->response->getStatusCode();
        }

        if (method_exists($this->response, 'headers')) {
            $info['headers'] = $this->response->headers->all();
        }

        return $info;
    }

    public function reset(): void
    {
        $this->request = null;
        $this->response = null;
    }
}
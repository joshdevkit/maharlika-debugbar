<?php

namespace Maharlika\Debugbar\Middleware;

use Closure;
use Maharlika\Debugbar\DebugBar;
use Maharlika\Http\Request;

class InjectDebugbar
{
    protected DebugBar $debugbar;

    public function __construct(DebugBar $debugbar)
    {
        $this->debugbar = $debugbar;
    }

    public function handle(Request $request, Closure $next): mixed
    {
        // Start collecting request data
        if ($this->debugbar->hasCollector('request')) {
            $this->debugbar->getCollector('request')->setRequest($request);
        }

        // Execute the request
        $response = $next($request);

        // Don't inject debugbar for non-HTML responses or AJAX requests (unless configured)
        if (!$this->shouldInject($request, $response)) {
            return $response;
        }

        // Collect response data
        if ($this->debugbar->hasCollector('request')) {
            $this->debugbar->getCollector('request')->setResponse($response);
        }

        // Save data to storage
        $this->debugbar->save();

        // Inject debugbar HTML
        $content = $response->getContent();
        $debugbarHtml = $this->renderDebugbar();

        // Inject before </body> tag
        if (str_contains($content, '</body>')) {
            $content = str_replace('</body>', $debugbarHtml . '</body>', $content);
            $response->setContent($content);
        }

        return $response;
    }

    protected function shouldInject(Request $request, mixed $response): bool
    {
        if (!$this->debugbar->isEnabled()) {
            return false;
        }

        // Check if response has content method
        if (!method_exists($response, 'getContent')) {
            return false;
        }

        // Check content type
        $contentType = '';
        if (method_exists($response, 'headers')) {
            $contentType = $response->headers->get('Content-Type', '');
        }

        // Only inject into HTML responses
        if (!empty($contentType) && !str_contains($contentType, 'text/html')) {
            return false;
        }

        // Check for AJAX
        if ($request->ajax() && !config('debugbar.capture_ajax', true)) {
            return false;
        }

        return true;
    }

    protected function renderDebugbar(): string
    {
        $data = $this->debugbar->collect();
        $dataJson = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $html = view('debugbar::debugbar', [
            'data' => $data,
            'dataJson' => $dataJson,
            'requestId' => $this->debugbar->getRequestId(),
        ])->render();

        return $html;
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Ensures JSON request bodies are available via $request->input() in Lumen.
 */
class ParseJsonBodyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldParseJson($request)) {
            $content = $request->getContent();

            if ($content !== '' && $content !== false) {
                $data = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $request->merge($data);
                }
            }
        }

        return $next($request);
    }

    protected function shouldParseJson(Request $request): bool
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        $contentType = (string) $request->header('Content-Type', '');

        return str_contains($contentType, 'json')
            || $request->getContent() !== '';
    }
}

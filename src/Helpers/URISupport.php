<?php

declare(strict_types=1);

namespace AltDesign\AltRedirect\Helpers;

use AltDesign\AltRedirect\Contracts\RepositoryInterface;
use Illuminate\Support\Str;

class URISupport
{
    /**
     * Returns the current path for simple redirect matching.
     *
     * @return string $path
     */
    public static function path(): string
    {
        $request = request();

        return Str::replace(
            $request->root(),
            '',
            $request->url()
        );
    }

    /**
     * Returns the current URI with filtered query strings for regex redirect matching.
     *
     * @return string $uri
     */
    public static function uriWithFilteredQueryStrings(): string
    {
        $request = request();
        $path = self::path();
        $queryString = $request->getQueryString();

        if (! $queryString) {
            return $path;
        }

        $stripKeys = [];
        try {
            $repository = app(RepositoryInterface::class);
            foreach ($repository->all('query-strings') as $item) {
                if ($item['strip'] ?? false) {
                    $stripKeys[] = strtolower($item['query_string']);
                }
            }
        } catch (\Exception $e) {
            // Fallback if repository is not available
        }

        parse_str($queryString, $params);
        $filteredParams = [];
        foreach ($params as $key => $value) {
            if (! in_array(strtolower((string) $key), $stripKeys)) {
                $filteredParams[$key] = $value;
            }
        }

        if (empty($filteredParams)) {
            return $path;
        }

        return $path.'?'.http_build_query($filteredParams);
    }
}

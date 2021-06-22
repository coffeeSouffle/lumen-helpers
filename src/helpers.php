<?php

use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path = '')
    {
        return rtrim(app()->basePath('public/'.$path), DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @param bool $secure
     *
     * @return string
     */
    function asset($path, $secure = null)
    {
        return URL::asset($path, $secure);
    }
}

if (!function_exists('mix')) {
    /**
     * Create a new redirect response to the previous location.
     *
     * @param int $status
     * @param array $headers
     * @param mixed $fallback
     *
     * @return RedirectResponse
     */
    function mix($path, $manifestDirectory = '')
    {
        static $manifests = [];
        if (! starts_with($path, '/')) {
            $path = "/{$path}";
        }
        if ($manifestDirectory && ! starts_with($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }
        $manifestKey = $manifestDirectory ? $manifestDirectory : '/';
        if (file_exists(public_path($manifestDirectory.'hot'))) {
            return new HtmlString("//localhost:8080{$path}");
        }
        if (in_array($manifestKey, $manifests)) {
            $manifest = $manifests[$manifestKey];
        } else {
            if (! file_exists($manifestPath = public_path($manifestDirectory.'/mix-manifest.json'))) {
                // var_dump($path, $manifestDirectory, $manifestPath);exit;
                throw new Exception('The Mix manifest does not exist.');
            }
            $manifests[$manifestKey] = $manifest = json_decode(
                file_get_contents($manifestPath), true
            );
        }

        if (! array_key_exists($path, $manifest)) {
            throw new Exception(
                "Unable to locate Mix file: {$path}. Please check your ".
                'webpack.mix.js output paths and try again.'
            );
        }
        return new HtmlString($manifestDirectory.$manifest[$path]);
    }
}

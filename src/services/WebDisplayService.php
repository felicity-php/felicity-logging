<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace felicity\logging\services;

use Twig_Environment;
use felicity\twig\Twig;
use Twig_Loader_Filesystem;
use felicity\datamodel\ModelCollection;

/**
 * Class WebDisplayService
 */
class WebDisplayService
{
    /** @var Twig_Environment */
    private static $twig;

    /**
     * WebDisplayService constructor
     * @param string $srcDir
     */
    public function __construct(string $srcDir)
    {
        if (self::$twig instanceof Twig_Environment) {
            return;
        }

        $srcDir = rtrim($srcDir, '/');

        $loader = new Twig_Loader_Filesystem("{$srcDir}/templates");

        self::$twig = new Twig_Environment($loader, [
            'optimizations' => 0,
        ]);

        self::$twig->addFilter(new \Twig_Filter(
            'felicityLoggingDisplay',
            function ($item) {
                if (\is_string($item)) {
                    return $item;
                }

                try {
                    return '<pre>' . var_export($item, true) . '</pre>';
                } catch (\Exception $e) {
                    return '<pre>' . print_r($item, true) . '</pre>';
                }
            }
        ));
    }

    /**
     * Gets the HTML display
     * @param ModelCollection $logs
     * @param float $elapsedTime
     * @return string
     * @throws \Exception
     */
    public function getHtmlDisplay(ModelCollection $logs, float $elapsedTime) : string
    {
        $html = self::$twig->render('WebDisplay.twig', [
            'phpVersion' => PHP_VERSION,
            'httpStatusCode' => http_response_code(),
            'logs' => $logs,
            'elapsedTime' => $elapsedTime,
            'memoryPeakUsage' => $this->formatBytes(memory_get_peak_usage()),
        ]);

        $options = [
            'cssMinifier' => '\Minify_CSSmin::minify',
            'jsMinifier' => '\JSMin\JSMin::minify',
        ];

        return \Minify_HTML::minify($html, $options);
    }

    /**
     * @param $bytes
     * @param int $precision
     * @return string
     */
    public function formatBytes($bytes, $precision = 2) : string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

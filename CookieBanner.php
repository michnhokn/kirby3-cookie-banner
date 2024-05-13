<?php

namespace Michnhokn;

use Kirby\Cms\Plugin;
use Kirby\Cms\PluginAsset;
use Kirby\Cms\PluginAssets;
use Kirby\Data\Json;
use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Cookie;
use Kirby\Toolkit\A;

class CookieBanner
{
    public const COOKIE_NAME = 'cookie_status';

    private static function getCookie(): ?string
    {
        $cookie = Cookie::get(self::COOKIE_NAME);
        if (!is_string($cookie) or empty($cookie)) {
            return null;
        }
        return $cookie;
    }

    public static function isFeatureAllowed(string $featureName): bool
    {
        if (($cookie = self::getCookie()) === null) {
            return false;
        }
        return str_contains($cookie, $featureName);
    }

    public static function allowedFeatures(): array
    {
        if (($cookie = self::getCookie()) === null) {
            return [];
        }

        return array_values(
            array_unique(
                array_filter(
                    explode(',', $cookie)
                )
            )
        );
    }

    public static function availableFeatures(array $additionalFeatures = []): array
    {
        $configFeatures = option('michnhokn.cookie-banner.features', []);
        return A::merge($configFeatures, $additionalFeatures);
    }

    public static function clear(): void
    {
        Cookie::remove(self::COOKIE_NAME);
    }

    public static function translate(string $key): string
    {
        if (option('languages') === true) {
            return t("michnhokn.cookie-banner.$key");
        }
        return option("michnhokn.cookie-banner.translations.$key");
    }

    public static function realAsset(string $type = 'css'): ?string
    {
        $manifest = Json::read(__DIR__ . "/dist/.vite/manifest.json");

        if (empty($manifest)) {
            throw new NotFoundException(['fallback' => 'manifest.json file missing in kirby-cookie-banner plugin']);
        }

        if ($type === 'css') {
            return __DIR__ . "/dist/" . $manifest['src/cookie-banner.js']['css'][0];
        }

        if ($type === 'js') {
            return __DIR__ . "/dist/" . $manifest['src/cookie-banner.js']['file'];
        }

        return null;
    }

    public static function asset(string $name): string
    {
        /** @var Plugin $plugin */
        $plugin = kirby()->plugins()['michnhokn/cookie-banner'];
        return $plugin->asset($name)->url();
    }
}

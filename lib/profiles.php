<?php

/** Separate work items keep negotiated formats in separate HTTP requests. */
abstract class cache_warmup_profiles
{
    private const ACCEPT = [
        'default' => 'image/jpeg,image/png,*/*;q=0.1',
        'webp' => 'image/webp,*/*;q=0.1',
        'avif' => 'image/avif,image/webp,*/*;q=0.1',
    ];

    public static function getProfiles(): array
    {
        $helper = 'FriendsOfRedaxo\\MediaNegotiator\\Helper';
        if (!rex_addon::get('media_negotiator')->isAvailable()
            || !class_exists($helper)
            || !is_callable([$helper, 'webpPossible'])
            || !is_callable([$helper, 'avifPossible'])) {
            return ['original'];
        }

        $profiles = ['default'];
        if ($helper::webpPossible()) {
            $profiles[] = 'webp';
        }
        if (!(bool) rex_config::get('media_negotiator', 'disable_avif', false)
            && 'webp' !== rex_config::get('media_negotiator', 'preferred_format', 'avif')
            && $helper::avifPossible()) {
            $profiles[] = 'avif';
        }
        return $profiles;
    }

    public static function expand(array $group): array
    {
        $items = [];
        $profiles = self::getProfiles();
        if (['original'] === $profiles) {
            // Keep the existing two-column work items when negotiation is unavailable.
            return ['count' => count($group['items']), 'items' => $group['items']];
        }
        foreach ($group['items'] as $item) {
            foreach ($profiles as $profile) {
                $items[] = [$item[0], $item[1], $profile];
            }
        }
        return ['count' => count($items), 'items' => $items];
    }

    public static function run(string $profile, callable $generate)
    {
        // Backwards compatibility for existing two-column work items and EP users.
        if ('original' === $profile) {
            return $generate();
        }
        if (!isset(self::ACCEPT[$profile]) || !in_array($profile, self::getProfiles(), true)) {
            throw new InvalidArgumentException('Unsupported warmup profile: ' . $profile);
        }

        $previous = [];
        foreach (['HTTP_ACCEPT', 'HTTP_USER_AGENT'] as $key) {
            $previous[$key] = $_SERVER[$key] ?? null;
        }
        $_SERVER['HTTP_ACCEPT'] = self::ACCEPT[$profile];
        // Do not let Media Negotiator's optional UA fallback turn default into WebP/AVIF.
        $_SERVER['HTTP_USER_AGENT'] = 'REDAXO CacheWarmup';
        try {
            return $generate();
        } finally {
            foreach ($previous as $key => $value) {
                if (null === $value) {
                    unset($_SERVER[$key]);
                } else {
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
}

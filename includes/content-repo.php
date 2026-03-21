<?php

declare(strict_types=1);

function content_file_path(): string
{
    return dirname(__DIR__) . '/data/content.json';
}

function content_seed_path(): string
{
    return dirname(__DIR__) . '/data/content.seed.json';
}

function content_defaults(): array
{
    static $defaults;

    if ($defaults !== null) {
        return $defaults;
    }

    $decoded = json_decode(file_get_contents(content_seed_path()) ?: '{}', true);
    $defaults = is_array($decoded) ? $decoded : [];

    return $defaults;
}

function content_get_path(array $data, string $path, $default = null)
{
    $normalized = preg_replace('/\[(\d+)\]/', '.$1', $path) ?: $path;
    $segments = array_filter(explode('.', $normalized), static fn (string $segment): bool => $segment !== '');
    $current = $data;

    foreach ($segments as $segment) {
        if (!is_array($current) || !array_key_exists($segment, $current)) {
            return $default;
        }
        $current = $current[$segment];
    }

    return $current;
}

function normalize_image_reference($image, array $fallback = []): array
{
    $fallbackValue = (string) ($fallback['value'] ?? $fallback['url'] ?? '');
    $fallbackType = (string) ($fallback['source_type'] ?? 'url');
    $fallbackAlt = (string) ($fallback['alt'] ?? '');

    if (is_string($image)) {
        $trimmed = trim($image);
        return [
            'source_type' => $trimmed === '' ? $fallbackType : 'url',
            'value' => $trimmed === '' ? $fallbackValue : $trimmed,
            'alt' => $fallbackAlt,
        ];
    }

    if (!is_array($image)) {
        return [
            'source_type' => $fallbackType,
            'value' => $fallbackValue,
            'alt' => $fallbackAlt,
        ];
    }

    $value = trim((string) ($image['value'] ?? $image['url'] ?? $fallbackValue));
    $sourceType = trim((string) ($image['source_type'] ?? $fallbackType));
    if ($sourceType === '') {
        $sourceType = str_starts_with($value, '/public/uploads/') ? 'upload' : 'url';
    }

    return [
        'source_type' => $sourceType,
        'value' => $value,
        'alt' => trim((string) ($image['alt'] ?? $fallbackAlt)),
    ];
}


function normalize_contact_details($contact, array $fallback = []): array
{
    $fields = ['title', 'description', 'whatsapp', 'email', 'instagram', 'facebook', 'tiktok', 'youtube'];
    $normalized = [];

    foreach ($fields as $field) {
        $normalized[$field] = trim((string) (is_array($contact) ? ($contact[$field] ?? ($fallback[$field] ?? '')) : ($fallback[$field] ?? '')));
    }

    return $normalized;
}

function normalize_art_item($item, array $fallback = []): array
{
    $fallbackImage = normalize_image_reference($fallback['image'] ?? []);

    return [
        'image' => normalize_image_reference(is_array($item) ? ($item['image'] ?? []) : [], $fallbackImage),
        'alt' => trim((string) (is_array($item) ? ($item['alt'] ?? '') : '')),
        'title' => trim((string) (is_array($item) ? ($item['title'] ?? '') : ($fallback['title'] ?? ''))),
        'subtitle' => trim((string) (is_array($item) ? ($item['subtitle'] ?? '') : ($fallback['subtitle'] ?? ''))),
        'description' => trim((string) (is_array($item) ? ($item['description'] ?? '') : ($fallback['description'] ?? ''))),
        'link_label' => trim((string) (is_array($item) ? ($item['link_label'] ?? '') : ($fallback['link_label'] ?? ''))),
        'link_url' => trim((string) (is_array($item) ? ($item['link_url'] ?? '') : ($fallback['link_url'] ?? ''))),
    ];
}

function normalize_academia_section($section, array $fallback = []): array
{
    $fallbackImage = normalize_image_reference($fallback['image'] ?? []);

    return [
        'title_prefix' => trim((string) (is_array($section) ? ($section['title_prefix'] ?? '') : ($fallback['title_prefix'] ?? ''))),
        'title_highlight' => trim((string) (is_array($section) ? ($section['title_highlight'] ?? '') : ($fallback['title_highlight'] ?? ''))),
        'description' => trim((string) (is_array($section) ? ($section['description'] ?? '') : ($fallback['description'] ?? ''))),
        'button' => trim((string) (is_array($section) ? ($section['button'] ?? '') : ($fallback['button'] ?? ''))),
        'link_url' => trim((string) (is_array($section) ? ($section['link_url'] ?? '') : ($fallback['link_url'] ?? ''))),
        'image' => normalize_image_reference(is_array($section) ? ($section['image'] ?? []) : [], $fallbackImage),
    ];
}

function normalize_content_structure(array $content): array
{
    $defaults = content_defaults();
    $normalized = array_replace_recursive($defaults, $content);

    $normalized['site']['contact'] = normalize_contact_details(
        $normalized['site']['contact'] ?? [],
        $defaults['site']['contact'] ?? []
    );

    $normalized['hero']['featured_image'] = normalize_image_reference(
        $normalized['hero']['featured_image'] ?? [],
        $defaults['hero']['featured_image'] ?? []
    );
    $normalized['tabs']['academia']['image'] = normalize_image_reference(
        $normalized['tabs']['academia']['image'] ?? [],
        $defaults['tabs']['academia']['image'] ?? []
    );

    $normalized['tabs']['academia']['button'] = trim((string) ($normalized['tabs']['academia']['button'] ?? ''));
    $normalized['tabs']['academia']['link_url'] = trim((string) ($normalized['tabs']['academia']['link_url'] ?? ($defaults['tabs']['academia']['link_url'] ?? '')));

    $defaultAcademiaSection = $defaults['tabs']['academia']['sections'][0] ?? [];
    $academiaSections = is_array($normalized['tabs']['academia']['sections'] ?? null) ? $normalized['tabs']['academia']['sections'] : [];
    $normalized['tabs']['academia']['sections'] = array_values(array_filter(array_map(static function ($section) use ($defaultAcademiaSection): array {
        return normalize_academia_section($section, $defaultAcademiaSection);
    }, $academiaSections), static function (array $section): bool {
        return $section['title_prefix'] !== '' || $section['title_highlight'] !== '' || $section['description'] !== '' || $section['image']['value'] !== '';
    }));

    $backgrounds = is_array($normalized['backgrounds'] ?? null) ? $normalized['backgrounds'] : [];
    $defaultBackground = $defaults['backgrounds'][0]['image'] ?? [];
    $normalized['backgrounds'] = array_values(array_filter(array_map(static function ($background) use ($defaultBackground): array {
        $image = normalize_image_reference(is_array($background) ? ($background['image'] ?? $background['url'] ?? []) : [], $defaultBackground);
        return $image['value'] === '' ? [] : ['image' => $image];
    }, $backgrounds), static fn (array $background): bool => $background !== []));

    $defaultGalleryItem = $defaults['tabs']['obras']['items'][0] ?? [];
    $galleryItems = is_array($normalized['tabs']['obras']['items'] ?? null) ? $normalized['tabs']['obras']['items'] : [];
    $normalized['tabs']['obras']['items'] = array_values(array_filter(array_map(static function ($item) use ($defaultGalleryItem): array {
        return normalize_art_item($item, $defaultGalleryItem);
    }, $galleryItems), static function (array $item): bool {
        return $item['title'] !== '' || $item['image']['value'] !== '' || $item['description'] !== '';
    }));

    $defaultMarketItem = $defaults['tabs']['mercado']['items'][0] ?? $defaultGalleryItem;
    $marketItems = is_array($normalized['tabs']['mercado']['items'] ?? null) ? $normalized['tabs']['mercado']['items'] : [];
    $normalized['tabs']['mercado']['items'] = array_values(array_filter(array_map(static function ($item) use ($defaultMarketItem): array {
        return normalize_art_item($item, $defaultMarketItem);
    }, $marketItems), static function (array $item): bool {
        return $item['title'] !== '' || $item['image']['value'] !== '' || $item['description'] !== '';
    }));

    return $normalized;
}

function read_content_file(): array
{
    $target = content_file_path();
    if (!file_exists($target)) {
        return normalize_content_structure([]);
    }

    $decoded = json_decode(file_get_contents($target) ?: '{}', true);

    return normalize_content_structure(is_array($decoded) ? $decoded : []);
}

function save_content_file(array $data): bool
{
    $target = content_file_path();
    $tmp = $target . '.tmp.' . uniqid('', true);
    $lockFile = $target . '.lock';
    $normalized = normalize_content_structure($data);
    $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($json === false) {
        return false;
    }

    $lockHandle = @fopen($lockFile, 'c');
    if ($lockHandle === false) {
        return false;
    }

    if (!flock($lockHandle, LOCK_EX)) {
        fclose($lockHandle);
        return false;
    }

    $tmpHandle = @fopen($tmp, 'wb');
    if ($tmpHandle === false) {
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
        return false;
    }

    $bytes = fwrite($tmpHandle, $json . PHP_EOL);
    if ($bytes === false) {
        fclose($tmpHandle);
        @unlink($tmp);
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
        return false;
    }

    fflush($tmpHandle);
    fclose($tmpHandle);

    if (!@rename($tmp, $target)) {
        @unlink($tmp);
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
        return false;
    }

    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);

    return true;
}

function set_value_by_path(array &$data, string $path, $value): void
{
    $normalized = preg_replace('/\[(\d+)\]/', '.$1', $path) ?: $path;
    $segments = array_filter(explode('.', $normalized), static fn ($segment) => $segment !== '');

    $current = &$data;
    foreach ($segments as $index => $segment) {
        $isLast = $index === array_key_last($segments);
        if ($isLast) {
            $current[$segment] = $value;
            return;
        }

        if (!isset($current[$segment]) || !is_array($current[$segment])) {
            $current[$segment] = [];
        }

        $current = &$current[$segment];
    }
}

function delete_value_by_path(array &$data, string $path): void
{
    $normalized = preg_replace('/\[(\d+)\]/', '.$1', $path) ?: $path;
    $segments = array_filter(explode('.', $normalized), static fn ($segment) => $segment !== '');
    if ($segments === []) {
        return;
    }

    $current = &$data;
    foreach ($segments as $index => $segment) {
        $isLast = $index === array_key_last($segments);
        if ($isLast) {
            if (is_array($current) && array_key_exists($segment, $current)) {
                unset($current[$segment]);
                if (array_is_list($current)) {
                    $current = array_values($current);
                }
            }
            return;
        }

        if (!isset($current[$segment]) || !is_array($current[$segment])) {
            return;
        }

        $current = &$current[$segment];
    }
}

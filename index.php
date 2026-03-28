<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/content-repo.php';
require_once __DIR__ . '/includes/url.php';

$isLoggedIn = current_user() !== null;
$content = read_content_file();

function esc($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function image_url($image): string
{
    return esc((string) ($image['value'] ?? ''));
}

function whatsapp_number(string $number): string
{
    return preg_replace('/\D+/', '', $number) ?? '';
}

function whatsapp_link(string $number, string $message): string
{
    $normalized = whatsapp_number($number);
    if ($normalized === '') {
        return '';
    }

    return 'https://wa.me/' . $normalized . '?text=' . rawurlencode($message);
}

function normalize_external_link(string $value): string
{
    $trimmed = trim($value);
    if ($trimmed === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $trimmed)) {
        return $trimmed;
    }

    return 'https://' . ltrim($trimmed, '/');
}

$backgrounds = $content['backgrounds'] ?? [];
$stats = $content['stats'] ?? [];
$galleryItems = $content['tabs']['obras']['items'] ?? [];
$marketItems = $content['tabs']['mercado']['items'] ?? [];
$contact = $content['site']['contact'] ?? [];
$contactLinks = [];

if (!empty($contact['whatsapp'])) {
    $contactLinks[] = [
        'label' => 'WhatsApp',
        'value' => $contact['whatsapp'],
        'url' => whatsapp_link((string) $contact['whatsapp'], 'Hola, quiero consultar sobre una obra de ' . ($content['site']['name'] ?? 'la galería') . '.'),
        'icon' => 'whatsapp',
    ];
}

if (!empty($contact['email'])) {
    $email = trim((string) $contact['email']);
    $contactLinks[] = [
        'label' => 'Email',
        'value' => $email,
        'url' => 'mailto:' . $email,
        'icon' => 'email',
    ];
}

foreach ([
    'instagram' => ['label' => 'Instagram', 'icon' => 'instagram'],
    'facebook' => ['label' => 'Facebook', 'icon' => 'facebook'],
    'tiktok' => ['label' => 'TikTok', 'icon' => 'tiktok'],
    'youtube' => ['label' => 'YouTube', 'icon' => 'youtube'],
] as $field => $meta) {
    $value = trim((string) ($contact[$field] ?? ''));
    if ($value === '') {
        continue;
    }

    $contactLinks[] = [
        'label' => $meta['label'],
        'value' => $value,
        'url' => normalize_external_link($value),
        'icon' => $meta['icon'],
    ];
}

function render_contact_icon(string $icon): string
{
    return match ($icon) {
        'whatsapp' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.04a9.92 9.92 0 0 0-8.58 14.9L2 22l5.2-1.36A9.96 9.96 0 1 0 12 2.04Zm0 18.14a8.1 8.1 0 0 1-4.12-1.13l-.3-.18-3.08.81.82-3-.2-.31A8.12 8.12 0 1 1 12 20.18Zm4.46-6.04c-.24-.12-1.43-.7-1.66-.78-.22-.08-.38-.12-.54.12-.16.23-.62.78-.76.94-.14.15-.28.17-.52.06-.24-.12-1-.37-1.9-1.16-.7-.62-1.17-1.38-1.3-1.61-.14-.23-.02-.35.1-.47.1-.1.23-.28.34-.42.12-.13.16-.23.24-.38.08-.16.04-.29-.02-.4-.06-.12-.54-1.3-.74-1.78-.2-.46-.4-.4-.54-.4h-.46c-.16 0-.4.06-.61.3-.22.23-.84.82-.84 2s.86 2.32.98 2.48c.12.15 1.68 2.56 4.08 3.59.57.25 1.02.4 1.37.5.58.18 1.1.16 1.52.1.46-.07 1.43-.58 1.63-1.14.2-.56.2-1.03.14-1.14-.05-.12-.21-.18-.45-.3Z"/></svg>',
        'email' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.75"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>',
        'facebook' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.42 21v-7.56h2.54l.38-2.95h-2.92V8.6c0-.85.24-1.43 1.46-1.43H16V4.53c-.19-.03-.84-.08-1.6-.08-1.58 0-2.66.96-2.66 2.74v1.53H9.5v2.95h2.24V21h1.68Z"/></svg>',
        'tiktok' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14.5 3c.32 1.71 1.35 3.14 2.9 4.02.83.47 1.72.72 2.6.76v2.52a8.3 8.3 0 0 1-3.42-.74v5.14a5.7 5.7 0 1 1-5.7-5.7c.24 0 .48.02.72.05v2.56a3.04 3.04 0 1 0 2.9 3.03V3h2Z"/></svg>',
        'youtube' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.8 8.52a2.9 2.9 0 0 0-2.04-2.05C17.96 6 12 6 12 6s-5.96 0-7.76.47A2.9 2.9 0 0 0 2.2 8.52 30.7 30.7 0 0 0 2 12a30.7 30.7 0 0 0 .2 3.48 2.9 2.9 0 0 0 2.04 2.05C6.04 18 12 18 12 18s5.96 0 7.76-.47a2.9 2.9 0 0 0 2.04-2.05c.14-1.15.2-2.31.2-3.48s-.06-2.33-.2-3.48ZM10 14.73V9.27L15 12l-5 2.73Z"/></svg>',
        default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg>',
    };
}
?>
<!DOCTYPE html>
<html lang="<?= esc($content['site']['lang'] ?? 'es') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($content['site']['title'] ?? 'Galería') ?></title>
    <meta name="description" content="<?= esc($content['site']['seo']['description'] ?? '') ?>">
    <meta name="keywords" content="<?= esc($content['site']['seo']['keywords'] ?? '') ?>">
    <meta property="og:title" content="<?= esc($content['site']['title'] ?? 'Galería') ?>">
    <meta property="og:description" content="<?= esc($content['site']['seo']['description'] ?? '') ?>">
    <meta property="og:image" content="<?= esc($content['site']['seo']['og_image'] ?? ($content['hero']['featured_image']['value'] ?? '')) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['"Playfair Display"', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: { art: { gold: '#C5A059', neon: '#00F2FF', deep: '#0A0A0B' } },
                },
            },
        };
    </script>
    <style>
        :root { --blur: 20px; }
        body { margin: 0; overflow-x: hidden; background: #000; font-family: 'Inter', sans-serif; }
        .bg-layer { position: absolute; inset: 0; background-size: cover; background-position: center; opacity: 0; transition: opacity 2.5s ease-in-out; }
        .bg-layer.active { opacity: 0.6; }
        .overlay { position: fixed; inset: 0; background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.85) 100%); z-index: -1; }
        .glass { background: rgba(255,255,255,.03); backdrop-filter: blur(var(--blur)); border: 1px solid rgba(255,255,255,.08); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }
        .glass-hover:hover { background: rgba(255,255,255,.07); border-color: rgba(0,242,255,.3); transform: translateY(-5px); transition: all .4s cubic-bezier(.175,.885,.32,1.275); }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: slideUp .8s ease forwards; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .nav-btn.active { background: rgba(255,255,255,.1); color: #00F2FF; }
        .editable-wrapper { position: relative; }
        .edit-icon, .delete-icon, .item-edit-btn { display: none; position: absolute; top: .75rem; z-index: 5; border-radius: 9999px; height: 1.8rem; align-items: center; justify-content: center; cursor: pointer; font-size: .8rem; }
        .edit-icon { right: .75rem; background: #00F2FF; color: #000; }
        .delete-icon { right: 3rem; background: rgba(239,68,68,.9); color: #fff; }
        .item-edit-btn { right: 5.25rem; padding: 0 .75rem; background: rgba(255,255,255,.9); color: #000; font-weight: 700; font-size: .65rem; letter-spacing: .08em; text-transform: uppercase; }
        .collection-toolbar { display:none; }
        body.edit-mode [data-edit-key], body.edit-mode [data-edit-link-key] { outline: 1px dashed rgba(0,242,255,.45); outline-offset: 4px; border-radius: .2rem; }
        body.edit-mode [data-edit-type="text"] { cursor: text; }
        body.edit-mode .edit-icon, body.edit-mode .delete-icon, body.edit-mode .item-edit-btn, body.edit-mode .collection-toolbar { display: inline-flex; }
        body.edit-mode .collection-toolbar { display:flex; }
        .field-message { display:none; font-size:.7rem; margin-top:.3rem; }
        .field-message.ok { color: #34d399; display:block; }
        .field-message.error { color: #f87171; display:block; }
        .preserve-breaks { white-space: pre-line; }
    </style>
</head>
<body class="text-white selection:bg-art-neon selection:text-black" data-auth="<?= $isLoggedIn ? '1' : '0' ?>">
<div id="bg-container" class="fixed inset-0 -z-10">
    <?php foreach ($backgrounds as $index => $background): ?>
        <div class="bg-layer<?= $index === 0 ? ' active' : '' ?>" style="background-image: url('<?= image_url($background['image'] ?? []) ?>')"></div>
    <?php endforeach; ?>
</div>
<div class="overlay"></div>

<?php if ($isLoggedIn): ?>
    <div class="fixed top-4 right-4 z-50 flex gap-2 flex-wrap justify-end max-w-[90vw]">
        <a href="<?= esc(url_for('/admin.php')) ?>" class="bg-white/80 text-black px-4 py-2 rounded-full text-xs font-bold">Ir al admin</a>
        <button id="toggleEditBtn" class="bg-white/80 text-black px-4 py-2 rounded-full text-xs font-bold">✏️ Editar</button>
        <button id="saveContentBtn" class="hidden bg-art-neon text-black px-4 py-2 rounded-full text-xs font-bold">Guardar cambios</button>
    </div>
<?php endif; ?>

<header class="p-8 md:p-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
    <div class="space-y-1 editable-wrapper">
        <h1 class="font-serif text-4xl md:text-6xl tracking-tighter" data-edit-key="site.name" data-edit-type="text"><?= esc($content['site']['name'] ?? '') ?></h1>
        <span class="edit-icon" data-edit-target="site.name">✎</span>
        <p class="text-art-neon uppercase tracking-[0.3em] text-xs" data-edit-key="site.tagline" data-edit-type="text"><?= esc($content['site']['tagline'] ?? '') ?></p>
        <span class="field-message" data-message-for="site.name"></span>
    </div>
    <div class="glass px-6 py-3 rounded-full flex items-center gap-4 editable-wrapper">
        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
        <span class="text-[10px] uppercase tracking-widest opacity-70" data-edit-key="site.availability" data-edit-type="text"><?= esc($content['site']['availability'] ?? '') ?></span>
        <span class="edit-icon" data-edit-target="site.availability">✎</span>
    </div>
</header>

<main class="max-w-7xl mx-auto px-6 pb-32">
    <div id="inicio" class="tab-content active">
        <div class="grid lg:grid-cols-2 gap-12 items-center min-h-[60vh]">
            <div class="space-y-8">
                <h2 class="font-serif text-5xl md:text-7xl leading-tight">
                    <span data-edit-key="hero.headline_prefix" data-edit-type="text"><?= esc($content['hero']['headline_prefix'] ?? '') ?></span>
                    <span class="italic text-art-gold" data-edit-key="hero.headline_highlight" data-edit-type="text"><?= esc($content['hero']['headline_highlight'] ?? '') ?></span>
                    <span data-edit-key="hero.headline_suffix" data-edit-type="text"><?= esc($content['hero']['headline_suffix'] ?? '') ?></span>
                </h2>
                <p class="text-lg text-gray-300 leading-relaxed font-light preserve-breaks" data-edit-key="hero.description" data-edit-type="text"><?= esc($content['hero']['description'] ?? '') ?></p>
                <div class="flex gap-4">
                    <?php foreach ($stats as $i => $stat): ?>
                        <div class="glass p-6 rounded-2xl flex-1">
                            <h3 class="text-art-neon text-2xl font-serif" data-edit-key="stats[<?= $i ?>].value" data-edit-type="text"><?= esc($stat['value'] ?? '') ?></h3>
                            <p class="text-xs uppercase tracking-tighter opacity-50" data-edit-key="stats[<?= $i ?>].label" data-edit-type="text"><?= esc($stat['label'] ?? '') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="relative group editable-wrapper">
                <img src="<?= image_url($content['hero']['featured_image'] ?? []) ?>" data-edit-key="hero.featured_image" data-edit-type="image" data-source-type="<?= esc($content['hero']['featured_image']['source_type'] ?? 'url') ?>" class="rounded-3xl border border-white/10 shadow-2xl transition-transform duration-700 group-hover:scale-[1.02]" alt="<?= esc($content['hero']['featured_image']['alt'] ?? '') ?>">
                <span class="edit-icon" data-edit-target="hero.featured_image">✎</span>
                <div class="absolute bottom-6 left-6 glass p-4 rounded-xl max-w-xs">
                    <p class="text-xs italic preserve-breaks" data-edit-key="hero.quote" data-edit-type="text">&ldquo;<?= esc($content['hero']['quote'] ?? '') ?>&rdquo;</p>
                </div>
            </div>
        </div>
    </div>

    <div id="obras" class="tab-content space-y-8">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h2 class="font-serif text-4xl"><span data-edit-key="tabs.obras.title_prefix" data-edit-type="text"><?= esc($content['tabs']['obras']['title_prefix'] ?? '') ?></span> <span class="italic" data-edit-key="tabs.obras.title_highlight" data-edit-type="text"><?= esc($content['tabs']['obras']['title_highlight'] ?? '') ?></span></h2>
            <?php if ($isLoggedIn): ?>
                <div class="collection-toolbar gap-2" data-collection-toolbar="tabs.obras.items">
                    <button type="button" class="px-4 py-2 rounded-full bg-art-neon text-black text-xs font-bold" data-add-collection="tabs.obras.items">+ Agregar obra</button>
                </div>
            <?php endif; ?>
        </div>
        <div id="galleryCollection" class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6">
            <?php foreach ($galleryItems as $i => $item): ?>
                <article class="glass glass-hover p-4 rounded-3xl break-inside-avoid editable-wrapper" data-collection-item="tabs.obras.items" data-index="<?= $i ?>">
                    <?php if ($isLoggedIn): ?><button type="button" class="delete-icon" data-delete-collection="tabs.obras.items" data-index="<?= $i ?>">✕</button><?php endif; ?>
                    <?php if ($isLoggedIn): ?><button type="button" class="item-edit-btn" data-edit-collection="tabs.obras.items" data-index="<?= $i ?>">Editar</button><?php endif; ?>
                    <img src="<?= image_url($item['image'] ?? []) ?>" data-edit-key="tabs.obras.items[<?= $i ?>].image" data-edit-type="image" data-source-type="<?= esc($item['image']['source_type'] ?? 'url') ?>" class="rounded-2xl w-full mb-4" alt="<?= esc($item['alt'] ?? '') ?>">
                    <span class="edit-icon" data-edit-target="tabs.obras.items[<?= $i ?>].image">✎</span>
                    <h3 class="font-serif text-xl" data-edit-key="tabs.obras.items[<?= $i ?>].title" data-edit-type="text"><?= esc($item['title'] ?? '') ?></h3>
                    <p class="text-xs text-art-neon mb-2" data-edit-key="tabs.obras.items[<?= $i ?>].subtitle" data-edit-type="text"><?= esc($item['subtitle'] ?? '') ?></p>
                    <p class="text-sm opacity-60 mb-4 preserve-breaks" data-edit-key="tabs.obras.items[<?= $i ?>].description" data-edit-type="text"><?= esc($item['description'] ?? '') ?></p>
                    <a href="<?= esc($item['link_url'] ?? '#') ?>" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 text-sm text-art-neon" data-edit-link-key="tabs.obras.items[<?= $i ?>].link_url">
                        <span data-edit-key="tabs.obras.items[<?= $i ?>].link_label" data-edit-type="text"><?= esc($item['link_label'] ?? 'Ver más') ?></span>
                    </a>
                    <span class="edit-icon" data-edit-link-target="tabs.obras.items[<?= $i ?>].link_url">🔗</span>
                    <span class="field-message" data-message-for="tabs.obras.items[<?= $i ?>].title"></span>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="mercado" class="tab-content space-y-8">
        <div class="glass p-12 rounded-[3rem] text-center space-y-6">
            <h2 class="font-serif text-5xl"><span data-edit-key="tabs.mercado.title_prefix" data-edit-type="text"><?= esc($content['tabs']['mercado']['title_prefix'] ?? '') ?></span> <span class="italic" data-edit-key="tabs.mercado.title_highlight" data-edit-type="text"><?= esc($content['tabs']['mercado']['title_highlight'] ?? '') ?></span></h2>
            <p class="max-w-2xl mx-auto opacity-70 preserve-breaks" data-edit-key="tabs.mercado.description" data-edit-type="text"><?= esc($content['tabs']['mercado']['description'] ?? '') ?></p>
            <?php if ($isLoggedIn): ?>
                <div class="collection-toolbar justify-center gap-2" data-collection-toolbar="tabs.mercado.items">
                    <button type="button" class="px-4 py-2 rounded-full bg-art-neon text-black text-xs font-bold" data-add-collection="tabs.mercado.items">+ Agregar item market</button>
                </div>
            <?php endif; ?>
            <div id="marketCollection" class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6 pt-8">
                <?php foreach ($marketItems as $i => $item): ?>
                    <?php
                        $marketTitle = trim((string) ($item['title'] ?? 'esta obra'));
                        $marketLinkUrl = trim((string) ($item['link_url'] ?? ''));
                        $marketLinkLabel = trim((string) ($item['link_label'] ?? ''));
                        if ($marketLinkUrl === '' && !empty($contact['whatsapp'])) {
                            $marketLinkUrl = whatsapp_link((string) $contact['whatsapp'], 'Hola, me interesa comprar la obra ' . $marketTitle . '. ¿Está disponible?');
                            if ($marketLinkLabel === '') {
                                $marketLinkLabel = 'Consultar por WhatsApp';
                            }
                        }
                        if ($marketLinkLabel === '') {
                            $marketLinkLabel = 'Ver más';
                        }
                    ?>
                    <article class="glass glass-hover p-4 rounded-3xl break-inside-avoid editable-wrapper" data-collection-item="tabs.mercado.items" data-index="<?= $i ?>">
                        <?php if ($isLoggedIn): ?><button type="button" class="delete-icon" data-delete-collection="tabs.mercado.items" data-index="<?= $i ?>">✕</button><?php endif; ?>
                        <?php if ($isLoggedIn): ?><button type="button" class="item-edit-btn" data-edit-collection="tabs.mercado.items" data-index="<?= $i ?>">Editar</button><?php endif; ?>
                        <img src="<?= image_url($item['image'] ?? []) ?>" data-edit-key="tabs.mercado.items[<?= $i ?>].image" data-edit-type="image" data-source-type="<?= esc($item['image']['source_type'] ?? 'url') ?>" class="rounded-2xl h-full w-auto max-w-full mb-4 mx-auto" alt="<?= esc($item['alt'] ?? '') ?>">
                        <span class="edit-icon" data-edit-target="tabs.mercado.items[<?= $i ?>].image">✎</span>
                        <p class="text-sm font-bold" data-edit-key="tabs.mercado.items[<?= $i ?>].title" data-edit-type="text"><?= esc($item['title'] ?? '') ?></p>
                        <p class="text-[10px] text-art-neon uppercase tracking-[0.2em] mb-3" data-edit-key="tabs.mercado.items[<?= $i ?>].subtitle" data-edit-type="text"><?= esc($item['subtitle'] ?? '') ?></p>
                        <p class="text-sm opacity-60 mb-4 preserve-breaks" data-edit-key="tabs.mercado.items[<?= $i ?>].description" data-edit-type="text"><?= esc($item['description'] ?? '') ?></p>
                        <a href="<?= esc($marketLinkUrl ?: '#') ?>" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 text-sm text-art-neon" data-edit-link-key="tabs.mercado.items[<?= $i ?>].link_url">
                            <span data-edit-key="tabs.mercado.items[<?= $i ?>].link_label" data-edit-type="text"><?= esc($marketLinkLabel) ?></span>
                        </a>
                        <span class="edit-icon" data-edit-link-target="tabs.mercado.items[<?= $i ?>].link_url">🔗</span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="academia" class="tab-content space-y-8">
        <div class="flex flex-col lg:flex-row gap-8 items-stretch">
            <div class="glass p-10 rounded-[3rem] flex-1 space-y-6 flex flex-col justify-center">
                <h2 class="font-serif text-5xl"><span data-edit-key="tabs.academia.title_prefix" data-edit-type="text"><?= esc($content['tabs']['academia']['title_prefix'] ?? '') ?></span> <span class="text-art-neon italic" data-edit-key="tabs.academia.title_highlight" data-edit-type="text"><?= esc($content['tabs']['academia']['title_highlight'] ?? '') ?></span></h2>
                <p class="opacity-70 preserve-breaks" data-edit-key="tabs.academia.description" data-edit-type="text"><?= esc($content['tabs']['academia']['description'] ?? '') ?></p>
                <a href="<?= esc($content['tabs']['academia']['link_url'] ?? '#') ?>" target="_blank" rel="noreferrer" class="bg-art-neon text-black px-8 py-4 rounded-full font-bold self-start uppercase text-xs tracking-widest" data-edit-link-key="tabs.academia.link_url">
                    <span data-edit-key="tabs.academia.button" data-edit-type="text"><?= esc($content['tabs']['academia']['button'] ?? '') ?></span>
                </a>
                <span class="edit-icon self-start static" data-edit-link-target="tabs.academia.link_url">🔗</span>
            </div>
            <div class="flex-1 glass rounded-[3rem] overflow-hidden min-h-[400px] editable-wrapper">
                <img src="<?= image_url($content['tabs']['academia']['image'] ?? []) ?>" data-edit-key="tabs.academia.image" data-edit-type="image" data-source-type="<?= esc($content['tabs']['academia']['image']['source_type'] ?? 'url') ?>" class="w-full h-full object-cover opacity-50" alt="<?= esc($content['tabs']['academia']['image']['alt'] ?? '') ?>">
                <span class="edit-icon" data-edit-target="tabs.academia.image">✎</span>
            </div>
        </div>

        <?php foreach (($content['tabs']['academia']['sections'] ?? []) as $i => $section): ?>
            <div class="flex flex-col lg:flex-row gap-8 items-stretch">
                <div class="glass p-10 rounded-[3rem] flex-1 space-y-6 flex flex-col justify-center">
                    <h3 class="font-serif text-4xl"><span><?= esc($section['title_prefix'] ?? '') ?></span> <span class="text-art-neon italic"><?= esc($section['title_highlight'] ?? '') ?></span></h3>
                    <p class="opacity-70 preserve-breaks"><?= esc($section['description'] ?? '') ?></p>
                    <?php if (($section['button'] ?? '') !== ''): ?>
                        <a href="<?= esc($section['link_url'] ?? '#') ?>" target="_blank" rel="noreferrer" class="bg-art-neon text-black px-8 py-4 rounded-full font-bold self-start uppercase text-xs tracking-widest">
                            <?= esc($section['button'] ?? '') ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="flex-1 glass rounded-[3rem] overflow-hidden min-h-[320px]">
                    <img src="<?= image_url($section['image'] ?? []) ?>" class="w-full h-full object-cover opacity-50" alt="<?= esc($section['image']['alt'] ?? '') ?>">
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>


<?php if ($contactLinks !== []): ?>
    <section class="max-w-7xl mx-auto px-6 pb-24" aria-label="<?= esc($contact['title'] ?? 'Contacto') ?>">
        <div class="flex flex-wrap items-center justify-center gap-3 md:gap-4">
            <?php foreach ($contactLinks as $contactLink): ?>
                <a
                    href="<?= esc($contactLink['url']) ?>"
                    target="_blank"
                    rel="noreferrer"
                    aria-label="<?= esc($contactLink['label'] . ': ' . $contactLink['value']) ?>"
                    title="<?= esc($contactLink['label']) ?>"
                    class="glass glass-hover w-11 h-11 md:w-12 md:h-12 rounded-full flex items-center justify-center text-white/80 hover:text-art-neon transition-colors duration-300"
                >
                    <span class="w-5 h-5 md:w-6 md:h-6"><?= render_contact_icon((string) ($contactLink['icon'] ?? '')) ?></span>
                    <span class="sr-only"><?= esc($contactLink['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<nav class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50">
    <div class="glass px-4 py-3 rounded-full flex gap-2 border-white/20">
        <button onclick="showTab('inicio')" data-tab="inicio" class="nav-btn active px-6 py-2 rounded-full text-[10px] font-bold uppercase" data-edit-key="site.nav.inicio" data-edit-type="text"><?= esc($content['site']['nav']['inicio'] ?? 'Bio') ?></button>
        <button onclick="showTab('obras')" data-tab="obras" class="nav-btn px-6 py-2 rounded-full text-[10px] font-bold uppercase" data-edit-key="site.nav.obras" data-edit-type="text"><?= esc($content['site']['nav']['obras'] ?? 'Galería') ?></button>
        <button onclick="showTab('mercado')" data-tab="mercado" class="nav-btn px-6 py-2 rounded-full text-[10px] font-bold uppercase" data-edit-key="site.nav.mercado" data-edit-type="text"><?= esc($content['site']['nav']['mercado'] ?? 'Mercado') ?></button>
        <button onclick="showTab('academia')" data-tab="academia" class="nav-btn px-6 py-2 rounded-full text-[10px] font-bold uppercase" data-edit-key="site.nav.academia" data-edit-type="text"><?= esc($content['site']['nav']['academia'] ?? 'Academia') ?></button>
    </div>
</nav>

<div id="imageModal" class="hidden fixed inset-0 bg-black/70 z-[120] items-center justify-center px-4 py-8 overflow-y-auto">
    <div class="glass rounded-2xl p-6 max-w-xl w-full max-h-[calc(100vh-2rem)] overflow-y-auto space-y-4">
        <h3 class="font-serif text-2xl">Editar imagen</h3>
        <div class="flex gap-2 text-sm">
            <button type="button" data-mode="url" class="modal-mode bg-white/10 px-3 py-2 rounded">URL</button>
            <button type="button" data-mode="upload" class="modal-mode bg-white/10 px-3 py-2 rounded">Subir archivo</button>
            <button type="button" data-mode="library" class="modal-mode bg-white/10 px-3 py-2 rounded">Biblioteca</button>
        </div>
        <div id="urlPane" class="space-y-2">
            <label class="block text-xs">URL de imagen</label>
            <input id="imageUrlInput" type="url" class="w-full text-black px-3 py-2 rounded" placeholder="https://...">
            <p class="text-[11px] text-white/60">También puedes usar este media manager para completar el formulario de galería o market.</p>
        </div>
        <div id="uploadPane" class="hidden space-y-2">
            <label class="block text-xs">Archivo (jpg/png/webp, máx 5MB)</label>
            <input id="imageFileInput" type="file" accept="image/png,image/jpeg,image/webp" class="w-full text-xs">
        </div>
        <div id="libraryPane" class="hidden space-y-3">
            <p id="libraryStatus" class="text-xs text-white/70">Explora y selecciona una imagen.</p>
            <div id="libraryGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-72 overflow-y-auto pr-1"></div>
            <div class="flex justify-end">
                <button id="confirmLibrarySelection" type="button" class="px-4 py-2 rounded bg-art-neon text-black font-bold">Usar imagen seleccionada</button>
            </div>
        </div>
        <p id="modalFeedback" class="text-xs"></p>
        <div class="flex justify-end gap-2">
            <button id="cancelModal" class="px-4 py-2 rounded bg-white/10">Cancelar</button>
            <button id="saveModal" class="px-4 py-2 rounded bg-art-neon text-black font-bold">Guardar</button>
        </div>
    </div>
</div>

<div id="linkModal" class="hidden fixed inset-0 bg-black/70 z-[100] items-center justify-center px-4 py-8 overflow-y-auto">
    <div class="glass rounded-2xl p-6 max-w-lg w-full max-h-[calc(100vh-2rem)] overflow-y-auto space-y-4">
        <h3 class="font-serif text-2xl">Editar enlace</h3>
        <label class="block space-y-2">
            <span class="text-xs">URL del enlace</span>
            <input id="linkUrlInput" type="url" class="w-full text-black px-3 py-2 rounded" placeholder="https://...">
        </label>
        <p id="linkFeedback" class="text-xs"></p>
        <div class="flex justify-end gap-2">
            <button id="cancelLinkModal" class="px-4 py-2 rounded bg-white/10">Cancelar</button>
            <button id="saveLinkModal" class="px-4 py-2 rounded bg-art-neon text-black font-bold">Guardar enlace</button>
        </div>
    </div>
</div>

<div id="collectionItemModal" class="hidden fixed inset-0 bg-black/70 z-[110] items-center justify-center px-4 py-8 overflow-y-auto">
    <div class="glass rounded-2xl p-6 max-w-2xl w-full max-h-[calc(100vh-2rem)] overflow-y-auto space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p id="collectionModalEyebrow" class="text-xs uppercase tracking-[0.3em] text-art-neon"></p>
                <h3 id="collectionModalTitle" class="font-serif text-2xl">Editar elemento</h3>
            </div>
            <button type="button" id="closeCollectionItemModalTop" class="px-3 py-2 rounded bg-white/10">✕</button>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <label class="block space-y-2">
                <span class="text-xs">Título</span>
                <input id="collectionTitleInput" type="text" class="w-full text-black px-3 py-2 rounded" placeholder="Título">
            </label>
            <label class="block space-y-2">
                <span class="text-xs">Subtítulo</span>
                <input id="collectionSubtitleInput" type="text" class="w-full text-black px-3 py-2 rounded" placeholder="Subtítulo">
            </label>
            <label class="block space-y-2 md:col-span-2">
                <span class="text-xs">Descripción</span>
                <textarea id="collectionDescriptionInput" rows="4" class="w-full text-black px-3 py-2 rounded" placeholder="Descripción"></textarea>
            </label>
            <div class="md:col-span-2 space-y-3">
                <label class="block space-y-2">
                    <span class="text-xs">Imagen (URL o ruta subida)</span>
                    <input id="collectionImageInput" type="text" class="w-full text-black px-3 py-2 rounded" placeholder="https://... o /public/uploads/...">
                </label>
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
                    <p class="text-[11px] text-white/60">Abre el media manager para subir, explorar biblioteca o pegar una URL sin salir del formulario.</p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="openCollectionMediaManagerBtn" class="px-4 py-2 rounded bg-art-neon text-black font-bold">Abrir media manager</button>
                        <button type="button" id="clearCollectionImageBtn" class="px-4 py-2 rounded bg-white/10">Limpiar imagen</button>
                    </div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                    <p class="text-[11px] uppercase tracking-[0.2em] text-white/50 mb-2">Vista previa</p>
                    <img id="collectionImagePreview" src="" alt="Vista previa del formulario" class="w-full h-56 object-cover rounded-xl bg-black/30 hidden">
                    <div id="collectionImageEmpty" class="rounded-xl border border-dashed border-white/10 px-4 py-8 text-center text-xs text-white/45">Todavía no seleccionaste una imagen.</div>
                </div>
            </div>
            <label class="block space-y-2">
                <span class="text-xs">Alt de imagen</span>
                <input id="collectionAltInput" type="text" class="w-full text-black px-3 py-2 rounded" placeholder="Texto alternativo">
            </label>
            <label class="block space-y-2">
                <span class="text-xs">Etiqueta del enlace</span>
                <input id="collectionLinkLabelInput" type="text" class="w-full text-black px-3 py-2 rounded" placeholder="Ver más">
            </label>
            <label class="block space-y-2 md:col-span-2">
                <span class="text-xs">URL del enlace</span>
                <input id="collectionLinkUrlInput" type="url" class="w-full text-black px-3 py-2 rounded" placeholder="https://...">
            </label>
        </div>

        <p id="collectionItemFeedback" class="text-xs"></p>

        <div class="flex justify-end gap-2">
            <button id="cancelCollectionItemModal" class="px-4 py-2 rounded bg-white/10">Cancelar</button>
            <button id="saveCollectionItemModal" class="px-4 py-2 rounded bg-art-neon text-black font-bold">Guardar elemento</button>
        </div>
    </div>
</div>

<script>
window.APP_IS_AUTHENTICATED = document.body.dataset.auth === '1';
window.APP_CONTENT_STATE = <?= json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.ADMIN_EDITOR_ENDPOINTS = {
    saveContent: <?= json_encode(url_for('/api/save-content.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    uploadImage: <?= json_encode(url_for('/api/upload-image.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    listImages: <?= json_encode(url_for('/api/list-images.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
};
</script>
<script src="<?= esc(url_for('/public/js/admin-editor.js')) ?>"></script>

</body>
</html>

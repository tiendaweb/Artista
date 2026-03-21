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
    ];
}

if (!empty($contact['email'])) {
    $email = trim((string) $contact['email']);
    $contactLinks[] = [
        'label' => 'Email',
        'value' => $email,
        'url' => 'mailto:' . $email,
    ];
}

foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'youtube' => 'YouTube'] as $field => $label) {
    $value = trim((string) ($contact[$field] ?? ''));
    if ($value === '') {
        continue;
    }

    $contactLinks[] = [
        'label' => $label,
        'value' => $value,
        'url' => normalize_external_link($value),
    ];
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
                <p class="text-lg text-gray-300 leading-relaxed font-light" data-edit-key="hero.description" data-edit-type="text"><?= esc($content['hero']['description'] ?? '') ?></p>
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
                    <p class="text-xs italic" data-edit-key="hero.quote" data-edit-type="text">&ldquo;<?= esc($content['hero']['quote'] ?? '') ?>&rdquo;</p>
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
                    <p class="text-sm opacity-60 mb-4" data-edit-key="tabs.obras.items[<?= $i ?>].description" data-edit-type="text"><?= esc($item['description'] ?? '') ?></p>
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
            <p class="max-w-2xl mx-auto opacity-70" data-edit-key="tabs.mercado.description" data-edit-type="text"><?= esc($content['tabs']['mercado']['description'] ?? '') ?></p>
            <?php if ($isLoggedIn): ?>
                <div class="collection-toolbar justify-center gap-2" data-collection-toolbar="tabs.mercado.items">
                    <button type="button" class="px-4 py-2 rounded-full bg-art-neon text-black text-xs font-bold" data-add-collection="tabs.mercado.items">+ Agregar item market</button>
                </div>
            <?php endif; ?>
            <div id="marketCollection" class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 pt-8">
                <div class="p-8 border border-white/5 rounded-3xl bg-white/5 flex flex-col items-center justify-center hover:bg-art-neon/10 transition">
                    <span class="text-4xl mb-4" data-edit-key="tabs.mercado.cta_symbol" data-edit-type="text"><?= esc($content['tabs']['mercado']['cta_symbol'] ?? '+') ?></span>
                    <p class="text-xs font-bold tracking-widest uppercase" data-edit-key="tabs.mercado.cta_label" data-edit-type="text"><?= esc($content['tabs']['mercado']['cta_label'] ?? '') ?></p>
                </div>
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
                    <article class="glass p-4 rounded-2xl editable-wrapper" data-collection-item="tabs.mercado.items" data-index="<?= $i ?>">
                        <?php if ($isLoggedIn): ?><button type="button" class="delete-icon" data-delete-collection="tabs.mercado.items" data-index="<?= $i ?>">✕</button><?php endif; ?>
                        <?php if ($isLoggedIn): ?><button type="button" class="item-edit-btn" data-edit-collection="tabs.mercado.items" data-index="<?= $i ?>">Editar</button><?php endif; ?>
                        <div class="aspect-square bg-gray-800 rounded-xl mb-4 overflow-hidden">
                            <img src="<?= image_url($item['image'] ?? []) ?>" data-edit-key="tabs.mercado.items[<?= $i ?>].image" data-edit-type="image" data-source-type="<?= esc($item['image']['source_type'] ?? 'url') ?>" class="w-full h-full object-cover" alt="<?= esc($item['alt'] ?? '') ?>">
                        </div>
                        <span class="edit-icon" data-edit-target="tabs.mercado.items[<?= $i ?>].image">✎</span>
                        <p class="text-sm font-bold" data-edit-key="tabs.mercado.items[<?= $i ?>].title" data-edit-type="text"><?= esc($item['title'] ?? '') ?></p>
                        <p class="text-[10px] text-art-neon uppercase tracking-[0.2em] mb-3" data-edit-key="tabs.mercado.items[<?= $i ?>].subtitle" data-edit-type="text"><?= esc($item['subtitle'] ?? '') ?></p>
                        <p class="text-sm opacity-60 mb-4" data-edit-key="tabs.mercado.items[<?= $i ?>].description" data-edit-type="text"><?= esc($item['description'] ?? '') ?></p>
                        <a href="<?= esc($marketLinkUrl ?: '#') ?>" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 text-sm text-art-neon" data-edit-link-key="tabs.mercado.items[<?= $i ?>].link_url">
                            <span data-edit-key="tabs.mercado.items[<?= $i ?>].link_label" data-edit-type="text"><?= esc($marketLinkLabel) ?></span>
                        </a>
                        <span class="edit-icon" data-edit-link-target="tabs.mercado.items[<?= $i ?>].link_url">🔗</span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="academia" class="tab-content">
        <div class="flex flex-col lg:flex-row gap-8 items-stretch">
            <div class="glass p-10 rounded-[3rem] flex-1 space-y-6 flex flex-col justify-center">
                <h2 class="font-serif text-5xl"><span data-edit-key="tabs.academia.title_prefix" data-edit-type="text"><?= esc($content['tabs']['academia']['title_prefix'] ?? '') ?></span> <span class="text-art-neon italic" data-edit-key="tabs.academia.title_highlight" data-edit-type="text"><?= esc($content['tabs']['academia']['title_highlight'] ?? '') ?></span></h2>
                <p class="opacity-70" data-edit-key="tabs.academia.description" data-edit-type="text"><?= esc($content['tabs']['academia']['description'] ?? '') ?></p>
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
    </div>
</main>


<?php if ($contactLinks !== []): ?>
    <section class="max-w-7xl mx-auto px-6 pb-24">
        <div class="glass rounded-[2.5rem] p-8 md:p-10 space-y-6">
            <div class="max-w-2xl space-y-2">
                <p class="text-art-neon uppercase tracking-[0.3em] text-xs"><?= esc($contact['title'] ?? 'Contacto') ?></p>
                <?php if (!empty($contact['description'])): ?>
                    <p class="text-sm md:text-base text-gray-300"><?= esc($contact['description']) ?></p>
                <?php endif; ?>
            </div>
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($contactLinks as $contactLink): ?>
                    <a href="<?= esc($contactLink['url']) ?>" target="_blank" rel="noreferrer" class="glass glass-hover rounded-3xl px-5 py-4 flex flex-col gap-2">
                        <span class="text-[11px] uppercase tracking-[0.25em] text-art-neon"><?= esc($contactLink['label']) ?></span>
                        <span class="text-sm md:text-base break-all"><?= esc($contactLink['value']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
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

<div id="imageModal" class="hidden fixed inset-0 bg-black/70 z-[100] items-center justify-center px-4">
    <div class="glass rounded-2xl p-6 max-w-xl w-full space-y-4">
        <h3 class="font-serif text-2xl">Editar imagen</h3>
        <div class="flex gap-2 text-sm">
            <button type="button" data-mode="url" class="modal-mode bg-white/10 px-3 py-2 rounded">URL</button>
            <button type="button" data-mode="upload" class="modal-mode bg-white/10 px-3 py-2 rounded">Subir archivo</button>
            <button type="button" data-mode="library" class="modal-mode bg-white/10 px-3 py-2 rounded">Biblioteca</button>
        </div>
        <div id="urlPane" class="space-y-2">
            <label class="block text-xs">URL de imagen</label>
            <input id="imageUrlInput" type="url" class="w-full text-black px-3 py-2 rounded" placeholder="https://...">
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

<div id="linkModal" class="hidden fixed inset-0 bg-black/70 z-[100] items-center justify-center px-4">
    <div class="glass rounded-2xl p-6 max-w-lg w-full space-y-4">
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
    <div class="glass rounded-2xl p-6 max-w-2xl w-full space-y-4">
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
            <label class="block space-y-2 md:col-span-2">
                <span class="text-xs">Imagen (URL o ruta subida)</span>
                <input id="collectionImageInput" type="text" class="w-full text-black px-3 py-2 rounded" placeholder="https://... o /public/uploads/...">
            </label>
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

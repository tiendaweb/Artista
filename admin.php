<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/url.php';
require_once __DIR__ . '/includes/content-repo.php';

require_auth();
$user = current_user();
$content = read_content_file();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            color-scheme: dark;
            --page-bg: radial-gradient(circle at 12% 18%, rgba(56, 189, 248, 0.22), transparent 0 24%), radial-gradient(circle at 88% 14%, rgba(168, 85, 247, 0.18), transparent 0 22%), radial-gradient(circle at 50% 82%, rgba(14, 165, 233, 0.12), transparent 0 28%), linear-gradient(160deg, #030712 0%, #07111f 38%, #02040a 100%);
            --panel-bg: linear-gradient(160deg, rgba(8, 15, 32, 0.78), rgba(15, 23, 42, 0.38));
            --panel-border: rgba(255, 255, 255, 0.14);
            --panel-highlight: rgba(125, 211, 252, 0.22);
            --text-soft: rgba(226, 232, 240, 0.68);
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            color: rgb(241 245 249);
            background: var(--page-bg);
            background-attachment: fixed;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.06), transparent 16%, transparent 84%, rgba(255,255,255,0.03));
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            inset: 24px;
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 32px;
            pointer-events: none;
            opacity: .55;
            mask: linear-gradient(black, transparent 88%);
        }
        .glass {
            position: relative;
            background: var(--panel-bg);
            backdrop-filter: blur(28px) saturate(160%);
            -webkit-backdrop-filter: blur(28px) saturate(160%);
            border: 1px solid var(--panel-border);
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.42), inset 0 1px 0 rgba(255,255,255,0.07);
        }
        .glass::before {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: inherit;
            background: linear-gradient(180deg, rgba(255,255,255,0.10), rgba(255,255,255,0.01) 24%, transparent 60%);
            pointer-events: none;
            opacity: .55;
        }
        .glass-card {
            position: relative;
            background: linear-gradient(180deg, rgba(255,255,255,0.09), rgba(255,255,255,0.03));
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .admin-layout {
            position: relative;
            z-index: 1;
            max-width: 1600px;
            margin: 0 auto;
            padding: 1rem;
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
        }
        .sidebar-shell {
            position: fixed;
            inset: 1rem auto 1rem 1rem;
            width: min(340px, calc(100vw - 2rem));
            max-width: calc(100vw - 2rem);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
            transform: translateX(-115%);
            transition: transform .35s ease, opacity .35s ease;
            opacity: 0;
            z-index: 50;
        }
        .sidebar-shell.open { transform: translateX(0); opacity: 1; }
        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.58);
            backdrop-filter: blur(6px);
            z-index: 40;
        }
        .admin-main {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .hero-banner {
            position: relative;
            overflow: hidden;
            min-height: 220px;
        }
        .hero-banner::after {
            content: '';
            position: absolute;
            inset: auto -20% -35% 20%;
            height: 180px;
            background: radial-gradient(circle, rgba(34, 211, 238, 0.22), transparent 68%);
            pointer-events: none;
        }
        .hero-orb {
            position: absolute;
            border-radius: 999px;
            filter: blur(8px);
            opacity: .9;
            pointer-events: none;
        }
        .hero-orb.one {
            width: 180px;
            height: 180px;
            top: -48px;
            right: 8%;
            background: radial-gradient(circle, rgba(125,211,252,.3), rgba(125,211,252,0));
        }
        .hero-orb.two {
            width: 220px;
            height: 220px;
            bottom: -110px;
            right: 22%;
            background: radial-gradient(circle, rgba(192,132,252,.22), rgba(192,132,252,0));
        }
        .metric-tile {
            position: relative;
            overflow: hidden;
        }
        .metric-tile::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(125, 211, 252, 0.08), transparent 60%);
            pointer-events: none;
        }
        .admin-tab {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .95rem 1rem;
            border-radius: 1.1rem;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.03);
            color: rgba(226, 232, 240, 0.9);
            text-align: left;
            transition: all .2s ease;
        }
        .admin-tab:hover { border-color: rgba(125, 211, 252, 0.32); background: rgba(255,255,255,0.06); }
        .admin-tab.active {
            background: linear-gradient(135deg, rgba(34, 211, 238, 0.2), rgba(129, 140, 248, 0.16));
            border-color: rgba(125, 211, 252, 0.4);
            box-shadow: 0 12px 30px rgba(34, 211, 238, 0.12), inset 0 1px 0 rgba(255,255,255,0.08);
            color: white;
        }
        .admin-panel { display:none; }
        .admin-panel.active { display:block; }
        .section-card {
            border-radius: 1.75rem;
            padding: 1.25rem;
            border: 1px solid rgba(255,255,255,0.1);
            background: linear-gradient(180deg, rgba(2,6,23,.28), rgba(15,23,42,.44));
            box-shadow: inset 0 1px 0 rgba(255,255,255,.04);
        }
        .os-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .8rem;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.05);
            color: rgba(226,232,240,.82);
            font-size: .72rem;
            letter-spacing: .14em;
            text-transform: uppercase;
        }
        .window-dots {
            display: inline-flex;
            gap: .5rem;
            align-items: center;
        }
        .window-dots span {
            width: .7rem;
            height: .7rem;
            border-radius: 999px;
            display: inline-block;
            box-shadow: 0 0 12px rgba(255,255,255,.08);
        }
        .window-dots span:nth-child(1) { background: #fb7185; }
        .window-dots span:nth-child(2) { background: #fbbf24; }
        .window-dots span:nth-child(3) { background: #4ade80; }
        .section-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .section-heading p { color: var(--text-soft); }
        .input-shell, .textarea-shell {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(15, 23, 42, 0.62);
            padding: .85rem 1rem;
            color: white;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .input-shell:focus, .textarea-shell:focus {
            border-color: rgba(103, 232, 249, .7);
            box-shadow: 0 0 0 4px rgba(34, 211, 238, .14);
            background: rgba(15, 23, 42, 0.82);
        }
        .pill-btn {
            border-radius: 1rem;
            padding: .85rem 1.15rem;
            font-weight: 600;
            transition: transform .15s ease, filter .2s ease;
        }
        .pill-btn:hover { transform: translateY(-1px); filter: brightness(1.05); }
        .media-dropzone.dragover { border-color: rgba(103, 232, 249, .95); background: rgba(34, 211, 238, .15); }
        .media-target-btn.active { border-color: rgba(103, 232, 249, .95); background: rgba(34, 211, 238, .18); color: white; }
        @media (min-width: 1024px) {
            .admin-layout { grid-template-columns: 320px minmax(0, 1fr); padding: 1.5rem; }
            .sidebar-shell {
                position: sticky;
                top: 1.5rem;
                inset: auto;
                width: 100%;
                max-width: none;
                transform: none;
                opacity: 1;
                z-index: 1;
                max-height: calc(100vh - 3rem);
                overflow: auto;
            }
        }
    </style>
</head>
<body class="min-h-screen text-slate-100">
    <div id="adminSidebarBackdrop" class="sidebar-backdrop hidden lg:hidden"></div>
    <div class="admin-layout">
        <aside id="adminSidebar" class="sidebar-shell glass rounded-[2rem]">
            <div class="space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-4">
                        <div class="window-dots"><span></span><span></span><span></span></div>
                        <div>
                            <p class="text-cyan-200 text-[11px] uppercase tracking-[0.32em]">CLAUDIA NUÑEZ</p>
                            <h1 class="mt-3 text-2xl font-semibold leading-tight">PANEL DE CONTROL</h1>
                        </div>
                        </div>
                    <button type="button" id="closeSidebarBtn" class="lg:hidden rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200">✕</button>
                </div>
               
            </div>

            <nav class="space-y-3">
                <button type="button" class="admin-tab active" data-admin-tab="general"><span> General</span></button>
                <button type="button" class="admin-tab" data-admin-tab="galeria"><span> Galería</span></button>
                <button type="button" class="admin-tab" data-admin-tab="market"><span> Tienda</span></button>
                
                <button type="button" class="admin-tab" data-admin-tab="media"><span> Archivos</span></button>
                <button type="button" class="admin-tab" data-admin-tab="seo"><span> SEO</span></button>
                
            </nav>

            
        </aside>

        <main class="admin-main">
           <a href="<?= htmlspecialchars(url_for('/'), ENT_QUOTES, 'UTF-8') ?>" class="pill-btn rounded-2xl bg-cyan-300 text-slate-950">Ver sitio</a>
                        <a href="<?= htmlspecialchars(url_for('/logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="pill-btn rounded-2xl border border-white/15 bg-white/5 text-slate-100">Cerrar sesión</a>
                    

            <div id="adminAlert" class="hidden rounded-xl p-4 text-sm"></div>

            <section id="panel-general" class="admin-panel active glass rounded-[2rem] p-5 md:p-8 space-y-6">
                <div class="grid xl:grid-cols-[1.2fr,0.8fr] gap-6">
                    <article class="section-card space-y-5">
                        <div class="section-heading">
                            <div>
                                <h2 class="text-2xl font-semibold">Ajustes generales</h2>
                                
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Nombre del sitio</span>
                                <input type="text" id="siteNameInput" class="input-shell">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Tagline</span>
                                <input type="text" id="siteTaglineInput" class="input-shell">
                            </label>
                            <label class="block space-y-2 md:col-span-2">
                                <span class="text-sm text-slate-300">Disponibilidad</span>
                                <input type="text" id="availabilityInput" class="input-shell">
                            </label>
                        </div>
                        <div>
                            <button type="button" id="saveGeneralBtn" class="pill-btn rounded-2xl bg-cyan-300 text-slate-950">Guardar ajustes</button>
                        </div>
                    </article>

                    <article class="section-card space-y-5">
                        <div>
                            <h2 class="text-2xl font-semibold">Academia</h2>
                            
                        </div>
                        <div class="grid gap-4">
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Título inicial</span>
                                <input type="text" id="academiaTitlePrefixInput" class="input-shell">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Título destacado</span>
                                <input type="text" id="academiaTitleHighlightInput" class="input-shell">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Descripción</span>
                                <textarea id="academiaDescriptionInput" rows="4" class="textarea-shell"></textarea>
                            </label>
                            <div class="grid md:grid-cols-2 gap-4">
                                <label class="block space-y-2">
                                    <span class="text-sm text-slate-300">Texto del botón</span>
                                    <input type="text" id="academiaButtonInput" class="input-shell">
                                </label>
                                <label class="block space-y-2">
                                    <span class="text-sm text-slate-300">URL del botón</span>
                                    <input type="url" id="academiaLinkUrlInput" class="input-shell">
                                </label>
                            </div>
                        </div>
                        <div>
                            <button type="button" id="saveAcademiaBtn" class="pill-btn rounded-2xl bg-fuchsia-300 text-slate-950">Guardar academia</button>
                        </div>
                    </article>
                </div>

                <article class="section-card space-y-5">
                    <div class="section-heading">
                        <div>
                            <h2 class="text-2xl font-semibold">Datos de contacto y redes</h2>
                            
                        </div>
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm text-slate-300">Título del bloque</span>
                            <input type="text" id="contactTitleInput" class="input-shell">
                        </label>
                        <label class="block space-y-2 md:col-span-2">
                            <span class="text-sm text-slate-300">Descripción</span>
                            <textarea id="contactDescriptionInput" rows="3" class="textarea-shell"></textarea>
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm text-slate-300">WhatsApp</span>
                            <input type="text" id="contactWhatsappInput" placeholder="5492233011023" class="input-shell">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm text-slate-300">Email</span>
                            <input type="email" id="contactEmailInput" placeholder="hola@dominio.com" class="input-shell">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm text-slate-300">Instagram</span>
                            <input type="text" id="contactInstagramInput" placeholder="https://instagram.com/..." class="input-shell">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm text-slate-300">Facebook</span>
                            <input type="text" id="contactFacebookInput" placeholder="https://facebook.com/..." class="input-shell">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm text-slate-300">TikTok</span>
                            <input type="text" id="contactTiktokInput" placeholder="https://tiktok.com/@..." class="input-shell">
                        </label>
                        <label class="block space-y-2">
                            <span class="text-sm text-slate-300">YouTube</span>
                            <input type="text" id="contactYoutubeInput" placeholder="https://youtube.com/..." class="input-shell">
                        </label>
                    </div>
                    <div>
                        <button type="button" id="saveContactBtn" class="pill-btn rounded-2xl bg-white text-slate-950">Guardar contacto y redes</button>
                    </div>
                </article>

                <article class="section-card space-y-5">
                    <div class="section-heading">
                        <div>
                            <h2 class="text-2xl font-semibold">Imágenes clave del sitio</h2>
                            
                        </div>
                    </div>
                    <div class="grid xl:grid-cols-3 gap-5">
                        <article class="glass-card rounded-[1.6rem] p-5 space-y-4">
                            <div>
                                <h3 class="font-semibold text-lg">Imagen destacada del hero</h3>
                                
                            </div>
                            <img id="heroFeaturedPreview" src="" alt="Vista previa hero" class="w-full h-44 rounded-2xl object-cover border border-white/10 bg-slate-900/50">
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">URL o ruta</span>
                                <input type="text" id="heroFeaturedInput" data-image-input-key="hero.featured_image" class="input-shell">
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" class="media-target-btn pill-btn rounded-2xl bg-white/10 border border-white/20 text-slate-100" data-media-target-key="hero.featured_image" data-media-target-type="image-object" data-media-target-input="heroFeaturedInput">Abrir media manager</button>
                                <button type="button" class="pill-btn rounded-2xl bg-white/10 border border-white/20 text-slate-100" data-copy-from-input="heroFeaturedInput">Copiar enlace</button>
                            </div>
                        </article>
                        <article class="glass-card rounded-[1.6rem] p-5 space-y-4">
                            <div>
                                <h3 class="font-semibold text-lg">Imagen de Academia</h3>
                                
                            </div>
                            <img id="academiaImagePreview" src="" alt="Vista previa academia" class="w-full h-44 rounded-2xl object-cover border border-white/10 bg-slate-900/50">
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">URL o ruta</span>
                                <input type="text" id="academiaImageInput" data-image-input-key="tabs.academia.image" class="input-shell">
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" class="media-target-btn pill-btn rounded-2xl bg-white/10 border border-white/20 text-slate-100" data-media-target-key="tabs.academia.image" data-media-target-type="image-object" data-media-target-input="academiaImageInput">Abrir media manager</button>
                                <button type="button" class="pill-btn rounded-2xl bg-white/10 border border-white/20 text-slate-100" data-copy-from-input="academiaImageInput">Copiar enlace</button>
                            </div>
                        </article>
                    </div>
                    <div>
                        <button type="button" id="saveSiteImagesBtn" class="pill-btn rounded-2xl bg-cyan-300 text-slate-950">Guardar imágenes globales</button>
                    </div>
                </article>

                <article class="section-card space-y-5">
                    <div class="section-heading">
                        <div>
                            <h2 class="text-2xl font-semibold">Fondos del sitio</h2>
                            
                        </div>
                        <button type="button" id="addBackgroundBtn" class="pill-btn rounded-2xl bg-white text-slate-950">+ Agregar fondo</button>
                    </div>
                    <div id="backgroundCrud" class="grid xl:grid-cols-2 gap-5"></div>
                    <div>
                        <button type="button" id="saveBackgroundsBtn" class="pill-btn rounded-2xl bg-cyan-300 text-slate-950">Guardar fondos</button>
                    </div>
                </article>

                <article class="section-card space-y-5">
                    <div>
                        <h2 class="text-2xl font-semibold">Cambiar contraseña</h2>
                    </div>
                    <form id="passwordForm" class="grid md:grid-cols-3 gap-4">
                        <input type="password" name="current_password" required placeholder="Contraseña actual" class="input-shell">
                        <input type="password" name="new_password" required placeholder="Nueva contraseña" class="input-shell">
                        <input type="password" name="confirm_password" required placeholder="Confirmar nueva contraseña" class="input-shell">
                        <div class="md:col-span-3">
                            <button class="pill-btn rounded-2xl bg-white text-slate-950">Actualizar contraseña</button>
                        </div>
                    </form>
                </article>
            </section>

            <section id="panel-seo" class="admin-panel glass rounded-[2rem] p-5 md:p-8 space-y-6">
                <article class="section-card space-y-5">
                    <div class="section-heading">
                        <div>
                            <h2 class="text-2xl font-semibold">Datos SEO</h2>
                            
                        </div>
                    </div>
                    <div class="grid xl:grid-cols-[minmax(0,1fr),360px] gap-5 items-start">
                        <div class="space-y-4">
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">SEO Title</span>
                                <input type="text" id="seoTitleInput" class="input-shell">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">SEO Description</span>
                                <textarea id="seoDescriptionInput" rows="5" class="textarea-shell"></textarea>
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">SEO Keywords</span>
                                <input type="text" id="seoKeywordsInput" class="input-shell">
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" class="media-target-btn pill-btn rounded-2xl bg-white/10 border border-white/20 text-slate-100" data-media-target-key="site.seo.og_image" data-media-target-type="plain-string" data-media-target-input="seoOgImageInput">Abrir media manager</button>
                                <button type="button" class="pill-btn rounded-2xl bg-white/10 border border-white/20 text-slate-100" data-copy-from-input="seoOgImageInput">Copiar enlace</button>
                                <button type="button" id="saveSeoBtn" class="pill-btn rounded-2xl bg-fuchsia-300 text-slate-950">Guardar SEO</button>
                            </div>
                        </div>
                        <div class="glass-card rounded-[1.6rem] p-5 space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold">OG image</h3>
                                
                            </div>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">OG Image URL</span>
                                <input type="text" id="seoOgImageInput" class="input-shell">
                            </label>
                            <img id="seoOgPreview" src="" alt="Vista previa SEO OG" class="w-full h-56 rounded-2xl object-cover border border-white/10 bg-slate-900/50">
                        </div>
                    </div>
                </article>
            </section>

            <section id="panel-galeria" class="admin-panel glass rounded-[2rem] p-5 md:p-8 space-y-6">
                <article class="section-card space-y-5">
                    <div class="grid xl:grid-cols-[0.9fr,1.1fr] gap-5">
                        <div class="space-y-4">
                            <div>
                                <h2 class="text-2xl font-semibold">Cabecera de Galería</h2>
                                
                            </div>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Título inicial</span>
                                <input type="text" id="galleryTitlePrefixInput" class="input-shell">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Título destacado</span>
                                <input type="text" id="galleryTitleHighlightInput" class="input-shell">
                            </label>
                        </div>
                        <div class="glass-card rounded-[1.6rem] p-5 flex flex-col justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.28em] text-slate-400">Curaduría</p>
                                <h3 class="mt-2 text-xl font-semibold">CRUD de obras</h3>
                                
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" id="addGalleryItemBtn" class="pill-btn rounded-2xl bg-cyan-300 text-slate-950">+ Agregar obra</button>
                                <button type="button" id="saveGalleryBtn" class="pill-btn rounded-2xl bg-white text-slate-950">Guardar galería</button>
                            </div>
                        </div>
                    </div>
                    <div id="galleryCrud" class="grid xl:grid-cols-2 gap-5"></div>
                </article>
            </section>

            <section id="panel-market" class="admin-panel glass rounded-[2rem] p-5 md:p-8 space-y-6">
                <article class="section-card space-y-5">
                    <div class="grid xl:grid-cols-[0.9fr,1.1fr] gap-5">
                        <div class="space-y-4">
                            <div>
                                <h2 class="text-2xl font-semibold">Cabecera de Market</h2>
                                
                            </div>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Título inicial</span>
                                <input type="text" id="marketTitlePrefixInput" class="input-shell">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Título destacado</span>
                                <input type="text" id="marketTitleHighlightInput" class="input-shell">
                            </label>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Descripción</span>
                                <textarea id="marketDescriptionInput" rows="4" class="textarea-shell"></textarea>
                            </label>
                        </div>
                        <div class="glass-card rounded-[1.6rem] p-5 flex flex-col justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.28em] text-slate-400">Venta asistida</p>
                                <h3 class="mt-2 text-xl font-semibold">Items destacados</h3>
                                
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" id="addMarketItemBtn" class="pill-btn rounded-2xl bg-cyan-300 text-slate-950">+ Agregar item</button>
                                <button type="button" id="saveMarketBtn" class="pill-btn rounded-2xl bg-white text-slate-950">Guardar market</button>
                            </div>
                        </div>
                    </div>
                    <div id="marketCrud" class="grid xl:grid-cols-2 gap-5"></div>
                </article>
            </section>

            <section id="panel-media" class="admin-panel glass rounded-[2rem] p-5 md:p-8 space-y-6">
                <article class="section-card">
                    <div class="flex flex-col xl:flex-row gap-6">
                        <div class="xl:w-[360px] space-y-4">
                            <div>
                                <h2 class="text-2xl font-semibold">Administrador de imágenes</h2>
                                
                            </div>
                            <label id="mediaDropzone" for="mediaUploadInput" class="media-dropzone block rounded-3xl border-2 border-dashed border-white/20 bg-slate-950/40 p-6 text-center cursor-pointer transition">
                                <div class="space-y-3">
                                    <div class="text-4xl">🖼️</div>
                                    <div>
                                        <p class="font-semibold">Arrastrar y soltar imágenes aquí</p>
                                        <p class="text-sm text-slate-300">JPG, PNG o WEBP · máx. 5MB</p>
                                    </div>
                                </div>
                            </label>
                            <input id="mediaUploadInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden">
                            <div class="flex flex-wrap gap-3">
                                <button type="button" id="openMediaFileBtn" class="pill-btn rounded-2xl bg-cyan-300 text-slate-950">Seleccionar imagen</button>
                                <button type="button" id="refreshMediaLibraryBtn" class="pill-btn rounded-2xl bg-white/10 border border-white/20 text-slate-100">Actualizar biblioteca</button>
                            </div>
                            <div id="mediaUploadStatus" class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">Esperando subida.</div>
                            <label class="block space-y-2">
                                <span class="text-sm text-slate-300">Último enlace subido</span>
                                <input id="mediaLatestUrl" type="text" readonly class="input-shell text-slate-100">
                            </label>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" id="copyLatestMediaBtn" class="pill-btn rounded-2xl bg-white/10 border border-white/20 text-slate-100">Copiar último enlace</button>
                                <button type="button" id="useLatestMediaForTargetBtn" class="pill-btn rounded-2xl bg-white/10 border border-white/20 text-slate-100">Usar en campo seleccionado</button>
                            </div>
                        </div>
                        <div class="flex-1 space-y-4">
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <div>
                                    <h3 class="text-lg font-semibold">Biblioteca</h3>
                                    <p id="mediaLibraryStatus" class="text-sm text-slate-300">Cargando imágenes...</p>
                                </div>
                                <div class="text-sm text-slate-400">Seleccionar · copiar · asignar</div>
                            </div>
                            <div id="mediaLibraryGrid" class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4"></div>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </div>

<div id="fieldMediaModal" class="hidden fixed inset-0 bg-black/70 z-[100] items-center justify-center px-4 py-8 overflow-y-auto">
    <div class="glass rounded-2xl p-6 max-w-5xl w-full space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-2xl font-semibold">Seleccionar imagen para el campo</h3>
                <p id="fieldMediaTargetLabel" class="text-sm text-slate-300 mt-1">Sin selección.</p>
            </div>
            <button type="button" id="closeFieldMediaModalBtn" class="rounded-xl bg-white/10 border border-white/20 px-4 py-2 hover:bg-white/20">Cerrar</button>
        </div>
        <div class="grid xl:grid-cols-[320px,minmax(0,1fr)] gap-5">
            <div class="space-y-4">
                <label id="fieldMediaDropzone" for="fieldMediaFileInput" class="media-dropzone block rounded-3xl border-2 border-dashed border-white/20 bg-slate-950/40 p-6 text-center cursor-pointer transition">
                    <div class="space-y-3">
                        <div class="text-4xl">⬆️</div>
                        <div>
                            <p class="font-semibold">Subir imagen para este campo</p>
                            <p class="text-sm text-slate-300">Drag & drop</p>
                        </div>
                    </div>
                </label>
                <input id="fieldMediaFileInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden">
                <label class="block space-y-2">
                    <span class="text-sm text-slate-300">URL manual</span>
                    <input id="fieldMediaManualUrl" type="text" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 text-slate-100 outline-none" placeholder="https://... o /public/uploads/...">
                </label>
                <div class="flex flex-wrap gap-3">
                    <button type="button" id="applyFieldManualUrlBtn" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-4 py-3 hover:bg-cyan-200">Usar URL manual</button>
                    <button type="button" id="copyFieldManualUrlBtn" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20">Copiar enlace</button>
                </div>
                <div id="fieldMediaStatus" class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">Listo para asignar.</div>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <p id="fieldMediaLibraryStatus" class="text-sm text-slate-300">Cargando imágenes...</p>
                    <button type="button" id="refreshFieldMediaLibraryBtn" class="rounded-xl bg-white/10 border border-white/20 px-4 py-2 hover:bg-white/20">Actualizar biblioteca</button>
                </div>
                <div id="fieldMediaLibraryGrid" class="grid md:grid-cols-2 xl:grid-cols-3 gap-4 max-h-[60vh] overflow-y-auto pr-1"></div>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <button type="button" id="cancelFieldMediaModalBtn" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20">Cancelar</button>
        </div>
    </div>
</div>

<script>
const adminState = <?= json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const endpoints = {
    saveContent: <?= json_encode(url_for('/api/save-content.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    adminAction: <?= json_encode(url_for('/api/admin-action.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    listImages: <?= json_encode(url_for('/api/list-images.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    uploadImage: <?= json_encode(url_for('/api/upload-image.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
};

let mediaLibrary = [];
let mediaLibraryLoading = false;
let selectedMediaUrl = '';
let fieldMediaTarget = null;

function pathSegments(key) {
    return key.replace(/\[(\d+)\]/g, '.$1').split('.');
}

function getByPath(obj, key, fallback = '') {
    return pathSegments(key).reduce((acc, segment) => (acc && acc[segment] !== undefined ? acc[segment] : undefined), obj) ?? fallback;
}

function setByPath(obj, key, value) {
    const segs = pathSegments(key);
    let cur = obj;
    for (let i = 0; i < segs.length - 1; i += 1) {
        if (cur[segs[i]] === undefined) cur[segs[i]] = /^\d+$/.test(segs[i + 1]) ? [] : {};
        cur = cur[segs[i]];
    }
    cur[segs[segs.length - 1]] = value;
}

function normalizeImageUrl(url) {
    if (!url) return '';
    if (/^https?:\/\//i.test(url)) return url;
    try {
        return new URL(url, window.location.origin).toString();
    } catch (error) {
        return url;
    }
}

function imagePreviewFallback(label = 'Sin imagen') {
    return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="600" height="360"><rect width="100%" height="100%" fill="#0f172a"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="sans-serif" font-size="24">${label}</text></svg>`);
}

async function copyToClipboard(text) {
    if (!text) throw new Error('No hay enlace para copiar.');
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);
        return;
    }
    const helper = document.createElement('textarea');
    helper.value = text;
    document.body.appendChild(helper);
    helper.select();
    document.execCommand('copy');
    helper.remove();
}

function showAlert(message, type = 'success') {
    const alert = document.getElementById('adminAlert');
    alert.textContent = message;
    alert.className = `rounded-xl p-4 text-sm ${type === 'success' ? 'bg-emerald-400/10 text-emerald-200 border border-emerald-300/40' : 'bg-red-400/10 text-red-200 border border-red-300/40'}`;
    alert.classList.remove('hidden');
}

async function saveContentState(message) {
    const response = await fetch(endpoints.saveContent, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(adminState),
    });
    const result = await response.json();
    if (!response.ok || !result.ok) {
        throw new Error(result.error || 'No se pudo guardar');
    }
    if (result.content) {
        Object.keys(adminState).forEach((key) => delete adminState[key]);
        Object.assign(adminState, result.content);
    }
    if (message) showAlert(message, 'success');
}

function inferSourceType(value) {
    return /^https?:\/\//i.test(value) ? 'url' : (value ? 'upload' : 'url');
}

function setImageValueByKey(key, value, type = 'image-object') {
    if (type === 'plain-string') {
        setByPath(adminState, key, value);
        return;
    }

    const current = getByPath(adminState, key, {});
    setByPath(adminState, key, {
        ...current,
        source_type: inferSourceType(value),
        value,
        alt: current?.alt || '',
    });
}

function syncInputValue(inputId, value) {
    const input = document.getElementById(inputId);
    if (input) input.value = value;
}

function updateStandalonePreviews() {
    const heroValue = getByPath(adminState, 'hero.featured_image.value', '');
    const academiaValue = getByPath(adminState, 'tabs.academia.image.value', '');
    const seoValue = getByPath(adminState, 'site.seo.og_image', '');

    syncInputValue('heroFeaturedInput', heroValue);
    syncInputValue('academiaImageInput', academiaValue);
    syncInputValue('seoOgImageInput', seoValue);

    document.getElementById('heroFeaturedPreview').src = heroValue || imagePreviewFallback('Hero');
    document.getElementById('academiaImagePreview').src = academiaValue || imagePreviewFallback('Academia');
    document.getElementById('seoOgPreview').src = seoValue || imagePreviewFallback('SEO OG');
}

function emptyItem(title, options = {}) {
    const { linkLabel = 'Ver más', linkUrl = '' } = options;
    return {
        image: { source_type: 'url', value: '', alt: '' },
        alt: '',
        title,
        subtitle: '',
        description: '',
        link_label: linkLabel,
        link_url: linkUrl,
    };
}

function emptyBackground() {
    return {
        image: { source_type: 'url', value: '' },
    };
}

function renderMediaCard(image, actions = {}) {
    const resolvedUrl = image.url || '';
    return `
        <article class="rounded-3xl border border-white/10 bg-slate-950/40 overflow-hidden">
            <img src="${resolvedUrl}" alt="${image.name || 'Imagen subida'}" class="w-full h-48 object-cover bg-slate-900/50">
            <div class="p-4 space-y-3">
                <div>
                    <p class="font-medium truncate">${image.name || 'Imagen subida'}</p>
                    <p class="text-xs text-slate-400 truncate">${resolvedUrl}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    ${actions.copy !== false ? `<button type="button" class="rounded-xl bg-white/10 border border-white/20 px-3 py-2 text-sm hover:bg-white/20" data-copy-url="${resolvedUrl}">Copiar enlace</button>` : ''}
                    ${actions.assign ? `<button type="button" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-3 py-2 text-sm hover:bg-cyan-200" data-assign-url="${resolvedUrl}">Usar imagen</button>` : ''}
                    ${actions.select ? `<button type="button" class="rounded-xl bg-white/10 border border-white/20 px-3 py-2 text-sm hover:bg-white/20" data-select-url="${resolvedUrl}">Previsualizar</button>` : ''}
                </div>
            </div>
        </article>
    `;
}

function renderCrudCard(item, index, collectionKey) {
    const isMarket = collectionKey === 'tabs.mercado.items';
    const imageKey = `${collectionKey}[${index}].image`;
    const linkLabelPlaceholder = isMarket ? 'Consultar por WhatsApp' : 'Ver más';
    const linkHelpText = isMarket
        ? 'Si dejas la URL vacía, el sitio abrirá WhatsApp con una consulta por esta obra usando el número configurado.'
        : 'Si completas una URL, el botón llevará a ese enlace.';
    const imageValue = item.image?.value || '';
    const preview = imageValue || imagePreviewFallback('Sin imagen');

    return `
        <article class="rounded-3xl border border-white/10 bg-slate-950/40 p-5 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <h3 class="font-semibold text-lg">${isMarket ? 'Item market' : 'Obra'} #${index + 1}</h3>
                <button type="button" class="rounded-xl bg-red-500/80 px-3 py-2 text-sm" data-remove-item="${collectionKey}" data-index="${index}">Eliminar</button>
            </div>
            <img src="${preview}" alt="Vista previa ${isMarket ? 'market' : 'obra'} ${index + 1}" class="w-full h-48 rounded-2xl object-cover border border-white/10 bg-slate-900/50">
            <label class="block space-y-2"><span class="text-sm text-slate-300">Título</span><input type="text" value="${item.title || ''}" data-input-key="${collectionKey}[${index}].title" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
            <label class="block space-y-2"><span class="text-sm text-slate-300">Subtítulo</span><input type="text" value="${item.subtitle || ''}" data-input-key="${collectionKey}[${index}].subtitle" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
            <label class="block space-y-2"><span class="text-sm text-slate-300">Descripción</span><textarea rows="3" data-input-key="${collectionKey}[${index}].description" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3">${item.description || ''}</textarea></label>
            <label class="block space-y-2"><span class="text-sm text-slate-300">Imagen (URL o ruta subida)</span><input type="text" value="${imageValue}" data-image-value-key="${imageKey}" id="image-input-${collectionKey.replace(/[^a-z0-9]+/gi, '-')}-${index}" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="media-target-btn rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-media-target-key="${imageKey}" data-media-target-type="image-object" data-media-target-input="image-input-${collectionKey.replace(/[^a-z0-9]+/gi, '-')}-${index}">Abrir media manager</button>
                <button type="button" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-copy-from-input="image-input-${collectionKey.replace(/[^a-z0-9]+/gi, '-')}-${index}">Copiar enlace</button>
            </div>
            <label class="block space-y-2"><span class="text-sm text-slate-300">Etiqueta del enlace</span><input type="text" value="${item.link_label || linkLabelPlaceholder}" data-input-key="${collectionKey}[${index}].link_label" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
            <label class="block space-y-2"><span class="text-sm text-slate-300">URL del enlace</span><input type="url" value="${item.link_url || ''}" data-input-key="${collectionKey}[${index}].link_url" placeholder="${isMarket ? 'Opcional: si queda vacío usa WhatsApp' : 'https://...'}" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
            <p class="text-xs text-slate-400">${linkHelpText}</p>
        </article>
    `;
}

function renderBackgroundCard(background, index) {
    const imageValue = background?.image?.value || '';
    const preview = imageValue || imagePreviewFallback('Sin imagen');
    const inputId = `background-image-${index}`;

    return `
        <article class="rounded-3xl border border-white/10 bg-slate-950/40 p-5 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <h3 class="font-semibold text-lg">Fondo #${index + 1}</h3>
                <button type="button" class="rounded-xl bg-red-500/80 px-3 py-2 text-sm" data-remove-background="${index}">Eliminar</button>
            </div>
            <img src="${preview}" alt="Vista previa del fondo ${index + 1}" class="w-full h-40 rounded-2xl object-cover border border-white/10 bg-slate-900/50">
            <label class="block space-y-2">
                <span class="text-sm text-slate-300">Imagen de fondo (URL o ruta subida)</span>
                <input type="text" value="${imageValue}" id="${inputId}" data-background-key="backgrounds[${index}].image" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3">
            </label>
            <div class="flex gap-3 flex-wrap">
                <button type="button" class="media-target-btn rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-media-target-key="backgrounds[${index}].image" data-media-target-type="image-object" data-media-target-input="${inputId}">Abrir media manager</button>
                <button type="button" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-copy-from-input="${inputId}">Copiar enlace</button>
            </div>
        </article>
    `;
}

function renderBackgrounds() {
    const backgrounds = getByPath(adminState, 'backgrounds', []);
    document.getElementById('backgroundCrud').innerHTML = backgrounds.map((background, index) => renderBackgroundCard(background, index)).join('');

    document.querySelectorAll('[data-background-key]').forEach((input) => {
        input.oninput = () => {
            const value = input.value.trim();
            setByPath(adminState, input.dataset.backgroundKey, {
                source_type: inferSourceType(value),
                value,
            });
            const card = input.closest('article');
            const preview = card?.querySelector('img');
            if (preview) preview.src = value || imagePreviewFallback('Sin imagen');
        };
    });

    document.querySelectorAll('[data-remove-background]').forEach((button) => {
        button.onclick = async () => {
            getByPath(adminState, 'backgrounds', []).splice(Number(button.dataset.removeBackground), 1);
            renderBackgrounds();
            bindMediaTargetButtons();
            bindCopyButtons();
            try {
                await saveContentState('Fondo eliminado.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        };
    });
}

function bindCrudInputs() {
    document.querySelectorAll('[data-input-key]').forEach((input) => {
        input.oninput = () => setByPath(adminState, input.dataset.inputKey, input.value);
    });
    document.querySelectorAll('[data-image-value-key]').forEach((input) => {
        input.oninput = () => {
            const key = input.dataset.imageValueKey;
            const value = input.value.trim();
            setByPath(adminState, key, { source_type: inferSourceType(value), value, alt: getByPath(adminState, `${key}.alt`, '') });
            const card = input.closest('article');
            const preview = card?.querySelector('img');
            if (preview) preview.src = value || imagePreviewFallback('Sin imagen');
        };
    });
    document.querySelectorAll('[data-remove-item]').forEach((button) => {
        button.onclick = async () => {
            const items = getByPath(adminState, button.dataset.removeItem, []);
            items.splice(Number(button.dataset.index), 1);
            renderCrudSections();
            try {
                await saveContentState('Elemento eliminado.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        };
    });
}

function renderCrudSections() {
    document.getElementById('galleryCrud').innerHTML = getByPath(adminState, 'tabs.obras.items', []).map((item, index) => renderCrudCard(item, index, 'tabs.obras.items')).join('');
    document.getElementById('marketCrud').innerHTML = getByPath(adminState, 'tabs.mercado.items', []).map((item, index) => renderCrudCard(item, index, 'tabs.mercado.items')).join('');
    bindCrudInputs();
    bindMediaTargetButtons();
    bindCopyButtons();
}

function hydrateGeneralFields() {
    document.getElementById('siteNameInput').value = getByPath(adminState, 'site.name', '');
    document.getElementById('siteTaglineInput').value = getByPath(adminState, 'site.tagline', '');
    document.getElementById('availabilityInput').value = getByPath(adminState, 'site.availability', '');
    document.getElementById('contactTitleInput').value = getByPath(adminState, 'site.contact.title', 'Contacto');
    document.getElementById('contactDescriptionInput').value = getByPath(adminState, 'site.contact.description', '');
    document.getElementById('contactWhatsappInput').value = getByPath(adminState, 'site.contact.whatsapp', '5492233011023');
    document.getElementById('contactEmailInput').value = getByPath(adminState, 'site.contact.email', '');
    document.getElementById('contactInstagramInput').value = getByPath(adminState, 'site.contact.instagram', '');
    document.getElementById('contactFacebookInput').value = getByPath(adminState, 'site.contact.facebook', '');
    document.getElementById('contactTiktokInput').value = getByPath(adminState, 'site.contact.tiktok', '');
    document.getElementById('contactYoutubeInput').value = getByPath(adminState, 'site.contact.youtube', '');
    document.getElementById('academiaTitlePrefixInput').value = getByPath(adminState, 'tabs.academia.title_prefix', '');
    document.getElementById('academiaTitleHighlightInput').value = getByPath(adminState, 'tabs.academia.title_highlight', '');
    document.getElementById('academiaDescriptionInput').value = getByPath(adminState, 'tabs.academia.description', '');
    document.getElementById('academiaButtonInput').value = getByPath(adminState, 'tabs.academia.button', '');
    document.getElementById('academiaLinkUrlInput').value = getByPath(adminState, 'tabs.academia.link_url', '');
    document.getElementById('galleryTitlePrefixInput').value = getByPath(adminState, 'tabs.obras.title_prefix', '');
    document.getElementById('galleryTitleHighlightInput').value = getByPath(adminState, 'tabs.obras.title_highlight', '');
    document.getElementById('marketTitlePrefixInput').value = getByPath(adminState, 'tabs.mercado.title_prefix', '');
    document.getElementById('marketTitleHighlightInput').value = getByPath(adminState, 'tabs.mercado.title_highlight', '');
    document.getElementById('marketDescriptionInput').value = getByPath(adminState, 'tabs.mercado.description', '');
    document.getElementById('seoTitleInput').value = getByPath(adminState, 'site.title', '');
    document.getElementById('seoDescriptionInput').value = getByPath(adminState, 'site.seo.description', '');
    document.getElementById('seoKeywordsInput').value = getByPath(adminState, 'site.seo.keywords', '');
    document.getElementById('seoOgImageInput').value = getByPath(adminState, 'site.seo.og_image', '');
    updateStandalonePreviews();
}

function updateMediaStatus(targetId, message, kind = 'info') {
    const target = document.getElementById(targetId);
    if (!target) return;
    const color = kind === 'success'
        ? 'text-emerald-200 border-emerald-300/40'
        : kind === 'error'
            ? 'text-red-200 border-red-300/40'
            : 'text-slate-300 border-white/10';
    target.className = `rounded-2xl border bg-slate-950/40 px-4 py-3 text-sm ${color}`;
    target.textContent = message;
}

async function loadMediaLibrary(options = {}) {
    if (mediaLibraryLoading) return;
    mediaLibraryLoading = true;
    const {
        statusIds = ['mediaLibraryStatus'],
        renderGlobal = true,
        renderField = false,
        selectedUrl = '',
    } = options;

    statusIds.forEach((id) => {
        const node = document.getElementById(id);
        if (node) node.textContent = 'Cargando imágenes...';
    });

    try {
        const response = await fetch(endpoints.listImages, { method: 'GET' });
        const result = await response.json();
        if (!response.ok || !result.ok || !Array.isArray(result.images)) {
            throw new Error(result.error || 'No se pudo cargar la biblioteca.');
        }
        mediaLibrary = result.images;
        if (renderGlobal) renderMediaLibraryGrid();
        if (renderField) renderFieldMediaLibraryGrid(selectedUrl);
        const emptyMessage = result.images.length === 0 ? 'No hay imágenes subidas todavía.' : 'Biblioteca actualizada.';
        statusIds.forEach((id) => {
            const node = document.getElementById(id);
            if (node) node.textContent = emptyMessage;
        });
    } catch (error) {
        statusIds.forEach((id) => {
            const node = document.getElementById(id);
            if (node) node.textContent = error.message || 'Error al cargar la biblioteca.';
        });
        if (renderGlobal) document.getElementById('mediaLibraryGrid').innerHTML = '';
        if (renderField) document.getElementById('fieldMediaLibraryGrid').innerHTML = '';
    } finally {
        mediaLibraryLoading = false;
    }
}

function renderMediaLibraryGrid() {
    const grid = document.getElementById('mediaLibraryGrid');
    if (!grid) return;
    grid.innerHTML = mediaLibrary.map((image) => renderMediaCard(image, { copy: true, assign: true, select: true })).join('');
    bindLibraryActionButtons(grid, false);
}

function renderFieldMediaLibraryGrid(selectedUrl = '') {
    const grid = document.getElementById('fieldMediaLibraryGrid');
    const normalizedSelectedUrl = normalizeImageUrl(selectedUrl);
    grid.innerHTML = mediaLibrary.map((image) => {
        const isSelected = normalizeImageUrl(image.url) === normalizedSelectedUrl;
        return `
            <article class="rounded-3xl border ${isSelected ? 'border-cyan-300' : 'border-white/10'} bg-slate-950/40 overflow-hidden">
                <img src="${image.url}" alt="${image.name || 'Imagen subida'}" class="w-full h-40 object-cover bg-slate-900/50">
                <div class="p-4 space-y-3">
                    <div>
                        <p class="font-medium truncate">${image.name || 'Imagen subida'}</p>
                        <p class="text-xs text-slate-400 truncate">${image.url}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-3 py-2 text-sm hover:bg-cyan-200" data-assign-url="${image.url}">Usar imagen</button>
                        <button type="button" class="rounded-xl bg-white/10 border border-white/20 px-3 py-2 text-sm hover:bg-white/20" data-copy-url="${image.url}">Copiar enlace</button>
                    </div>
                </div>
            </article>
        `;
    }).join('');
    bindLibraryActionButtons(grid, true);
}

function bindLibraryActionButtons(container, insideFieldModal = false) {
    container.querySelectorAll('[data-copy-url]').forEach((button) => {
        button.onclick = async () => {
            try {
                await copyToClipboard(button.dataset.copyUrl);
                if (insideFieldModal) {
                    updateMediaStatus('fieldMediaStatus', 'Enlace copiado al portapapeles.', 'success');
                } else {
                    showAlert('Enlace copiado.', 'success');
                }
            } catch (error) {
                if (insideFieldModal) {
                    updateMediaStatus('fieldMediaStatus', error.message, 'error');
                } else {
                    showAlert(error.message, 'error');
                }
            }
        };
    });

    container.querySelectorAll('[data-select-url]').forEach((button) => {
        button.onclick = () => {
            selectedMediaUrl = button.dataset.selectUrl || '';
            document.getElementById('mediaLatestUrl').value = selectedMediaUrl;
            updateMediaStatus('mediaUploadStatus', 'Imagen seleccionada. Puedes copiar el enlace o asignarla al campo activo.', 'success');
        };
    });

    container.querySelectorAll('[data-assign-url]').forEach((button) => {
        button.onclick = async () => {
            if (!fieldMediaTarget) {
                selectedMediaUrl = button.dataset.assignUrl || '';
                document.getElementById('mediaLatestUrl').value = selectedMediaUrl;
                updateMediaStatus('mediaUploadStatus', 'Selecciona primero un campo con “Abrir media manager” para asignar esta imagen.', 'info');
                return;
            }
            await assignImageToField(button.dataset.assignUrl || '', { closeModal: insideFieldModal });
        };
    });
}

async function uploadImageFile(file, options = {}) {
    const { key = '', statusTargetId = 'mediaUploadStatus', refreshField = false } = options;
    if (!file) throw new Error('Selecciona un archivo.');
    if (file.size > 5 * 1024 * 1024) throw new Error('El archivo supera 5MB.');

    updateMediaStatus(statusTargetId, `Subiendo ${file.name}...`, 'info');

    const form = new FormData();
    if (key) form.append('key', key);
    form.append('image', file);

    const response = await fetch(endpoints.uploadImage, { method: 'POST', body: form });
    const result = await response.json();
    if (!response.ok || !result.ok) {
        throw new Error(result.error || 'No se pudo subir la imagen.');
    }

    selectedMediaUrl = result.url || '';
    document.getElementById('mediaLatestUrl').value = selectedMediaUrl;
    updateMediaStatus(statusTargetId, 'Imagen subida correctamente. Ya puedes copiar el enlace o usarla en un campo.', 'success');
    await loadMediaLibrary({
        statusIds: refreshField ? ['mediaLibraryStatus', 'fieldMediaLibraryStatus'] : ['mediaLibraryStatus'],
        renderGlobal: true,
        renderField: refreshField,
        selectedUrl: selectedMediaUrl,
    });

    return result;
}

function openFieldMediaModal(target) {
    fieldMediaTarget = target;
    document.querySelectorAll('.media-target-btn').forEach((button) => {
        button.classList.toggle('active', button.dataset.mediaTargetKey === target.key && button.dataset.mediaTargetInput === target.inputId);
    });
    document.getElementById('fieldMediaTargetLabel').textContent = `Campo seleccionado: ${target.label}`;
    document.getElementById('fieldMediaManualUrl').value = target.currentValue || '';
    updateMediaStatus('fieldMediaStatus', 'Selecciona una imagen de la biblioteca, sube una nueva o pega una URL manual.', 'info');
    document.getElementById('fieldMediaModal').classList.remove('hidden');
    document.getElementById('fieldMediaModal').classList.add('flex');
    loadMediaLibrary({ statusIds: ['mediaLibraryStatus', 'fieldMediaLibraryStatus'], renderGlobal: true, renderField: true, selectedUrl: target.currentValue || '' });
}

function closeFieldMediaModal() {
    document.getElementById('fieldMediaModal').classList.add('hidden');
    document.getElementById('fieldMediaModal').classList.remove('flex');
}

async function assignImageToField(url, options = {}) {
    const { closeModal = false } = options;
    if (!fieldMediaTarget) {
        updateMediaStatus('mediaUploadStatus', 'No hay ningún campo seleccionado.', 'error');
        return;
    }
    if (!url) {
        updateMediaStatus('fieldMediaStatus', 'Selecciona o escribe una URL válida.', 'error');
        return;
    }

    setImageValueByKey(fieldMediaTarget.key, url, fieldMediaTarget.type);
    syncInputValue(fieldMediaTarget.inputId, url);
    fieldMediaTarget.currentValue = url;

    if (fieldMediaTarget.inputId === 'heroFeaturedInput' || fieldMediaTarget.inputId === 'academiaImageInput' || fieldMediaTarget.inputId === 'seoOgImageInput') {
        updateStandalonePreviews();
    }

    if (fieldMediaTarget.key.startsWith('backgrounds[')) {
        renderBackgrounds();
    } else if (fieldMediaTarget.key.includes('tabs.obras.items[') || fieldMediaTarget.key.includes('tabs.mercado.items[')) {
        renderCrudSections();
    }

    try {
        await saveContentState('Imagen asignada correctamente.');
        updateMediaStatus('fieldMediaStatus', 'Imagen asignada y guardada.', 'success');
        updateMediaStatus('mediaUploadStatus', 'Imagen asignada al campo seleccionado.', 'success');
        if (closeModal) closeFieldMediaModal();
    } catch (error) {
        updateMediaStatus('fieldMediaStatus', error.message, 'error');
    }
}

function bindMediaTargetButtons() {
    document.querySelectorAll('[data-media-target-key]').forEach((button) => {
        button.onclick = () => {
            const inputId = button.dataset.mediaTargetInput;
            const input = document.getElementById(inputId);
            const labelSource = button.closest('article')?.querySelector('h3')?.textContent || input?.previousElementSibling?.textContent || button.dataset.mediaTargetKey;
            openFieldMediaModal({
                key: button.dataset.mediaTargetKey,
                type: button.dataset.mediaTargetType || 'image-object',
                inputId,
                currentValue: input?.value?.trim() || '',
                label: labelSource.trim(),
            });
        };
    });
}

function bindCopyButtons() {
    document.querySelectorAll('[data-copy-from-input]').forEach((button) => {
        button.onclick = async () => {
            const input = document.getElementById(button.dataset.copyFromInput);
            try {
                await copyToClipboard(input?.value?.trim() || '');
                showAlert('Enlace copiado.', 'success');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        };
    });
}

function setupDropzone(dropzoneId, inputId, onFile) {
    const dropzone = document.getElementById(dropzoneId);
    const input = document.getElementById(inputId);
    if (!dropzone || !input) return;

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', async (event) => {
        const file = event.dataTransfer?.files?.[0];
        if (!file) return;
        await onFile(file);
    });

    input.addEventListener('change', async () => {
        const file = input.files?.[0];
        if (!file) return;
        await onFile(file);
        input.value = '';
    });
}

function closeSidebar() {
    document.getElementById('adminSidebar')?.classList.remove('open');
    document.getElementById('adminSidebarBackdrop')?.classList.add('hidden');
}

function openSidebar() {
    document.getElementById('adminSidebar')?.classList.add('open');
    document.getElementById('adminSidebarBackdrop')?.classList.remove('hidden');
}

document.getElementById('openSidebarBtn')?.addEventListener('click', openSidebar);
document.getElementById('closeSidebarBtn')?.addEventListener('click', closeSidebar);
document.getElementById('adminSidebarBackdrop')?.addEventListener('click', closeSidebar);

document.querySelectorAll('[data-admin-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelectorAll('[data-admin-tab]').forEach((tab) => tab.classList.remove('active'));
        document.querySelectorAll('.admin-panel').forEach((panel) => panel.classList.remove('active'));
        button.classList.add('active');
        document.getElementById(`panel-${button.dataset.adminTab}`)?.classList.add('active');
        closeSidebar();
        if (button.dataset.adminTab === 'media') {
            loadMediaLibrary({ statusIds: ['mediaLibraryStatus'], renderGlobal: true, renderField: false });
        }
    });
});

document.getElementById('saveGeneralBtn').addEventListener('click', async () => {
    setByPath(adminState, 'site.name', document.getElementById('siteNameInput').value.trim());
    setByPath(adminState, 'site.tagline', document.getElementById('siteTaglineInput').value.trim());
    setByPath(adminState, 'site.availability', document.getElementById('availabilityInput').value.trim());
    try {
        await saveContentState('Ajustes guardados.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});


document.getElementById('saveAcademiaBtn').addEventListener('click', async () => {
    setByPath(adminState, 'tabs.academia.title_prefix', document.getElementById('academiaTitlePrefixInput').value.trim());
    setByPath(adminState, 'tabs.academia.title_highlight', document.getElementById('academiaTitleHighlightInput').value.trim());
    setByPath(adminState, 'tabs.academia.description', document.getElementById('academiaDescriptionInput').value.trim());
    setByPath(adminState, 'tabs.academia.button', document.getElementById('academiaButtonInput').value.trim());
    setByPath(adminState, 'tabs.academia.link_url', document.getElementById('academiaLinkUrlInput').value.trim());
    try {
        await saveContentState('Academia guardada.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('saveContactBtn').addEventListener('click', async () => {
    setByPath(adminState, 'site.contact.title', document.getElementById('contactTitleInput').value.trim() || 'Contacto');
    setByPath(adminState, 'site.contact.description', document.getElementById('contactDescriptionInput').value.trim());
    setByPath(adminState, 'site.contact.whatsapp', document.getElementById('contactWhatsappInput').value.trim());
    setByPath(adminState, 'site.contact.email', document.getElementById('contactEmailInput').value.trim());
    setByPath(adminState, 'site.contact.instagram', document.getElementById('contactInstagramInput').value.trim());
    setByPath(adminState, 'site.contact.facebook', document.getElementById('contactFacebookInput').value.trim());
    setByPath(adminState, 'site.contact.tiktok', document.getElementById('contactTiktokInput').value.trim());
    setByPath(adminState, 'site.contact.youtube', document.getElementById('contactYoutubeInput').value.trim());
    try {
        await saveContentState('Contacto y redes guardados.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('saveSiteImagesBtn').addEventListener('click', async () => {
    setImageValueByKey('hero.featured_image', document.getElementById('heroFeaturedInput').value.trim(), 'image-object');
    setImageValueByKey('tabs.academia.image', document.getElementById('academiaImageInput').value.trim(), 'image-object');
    updateStandalonePreviews();
    try {
        await saveContentState('Imágenes globales guardadas.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('heroFeaturedInput').addEventListener('input', updateStandalonePreviews);
document.getElementById('academiaImageInput').addEventListener('input', updateStandalonePreviews);
document.getElementById('seoOgImageInput').addEventListener('input', updateStandalonePreviews);

document.getElementById('saveSeoBtn').addEventListener('click', async () => {
    setByPath(adminState, 'site.title', document.getElementById('seoTitleInput').value.trim());
    setByPath(adminState, 'site.seo.description', document.getElementById('seoDescriptionInput').value.trim());
    setByPath(adminState, 'site.seo.keywords', document.getElementById('seoKeywordsInput').value.trim());
    setByPath(adminState, 'site.seo.og_image', document.getElementById('seoOgImageInput').value.trim());
    updateStandalonePreviews();
    try {
        await saveContentState('SEO guardado.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('passwordForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const payload = Object.fromEntries(form.entries());
    payload.action = 'change_password';

    try {
        const response = await fetch(endpoints.adminAction, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json();
        if (!response.ok || !result.ok) throw new Error(result.error || 'No se pudo actualizar la contraseña.');
        showAlert(result.message || 'Contraseña actualizada correctamente.', 'success');
        event.currentTarget.reset();
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('addGalleryItemBtn').addEventListener('click', async () => {
    getByPath(adminState, 'tabs.obras.items', []).push(emptyItem('Nueva obra', { linkLabel: 'Ver más', linkUrl: '' }));
    renderCrudSections();
    try {
        await saveContentState('Obra agregada.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('addMarketItemBtn').addEventListener('click', async () => {
    getByPath(adminState, 'tabs.mercado.items', []).push(emptyItem('Nuevo artista', { linkLabel: 'Consultar por WhatsApp', linkUrl: '' }));
    renderCrudSections();
    try {
        await saveContentState('Item de market agregado.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('saveGalleryBtn').addEventListener('click', async () => {
    setByPath(adminState, 'tabs.obras.title_prefix', document.getElementById('galleryTitlePrefixInput').value.trim());
    setByPath(adminState, 'tabs.obras.title_highlight', document.getElementById('galleryTitleHighlightInput').value.trim());
    try {
        await saveContentState('Galería guardada.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('saveMarketBtn').addEventListener('click', async () => {
    setByPath(adminState, 'tabs.mercado.title_prefix', document.getElementById('marketTitlePrefixInput').value.trim());
    setByPath(adminState, 'tabs.mercado.title_highlight', document.getElementById('marketTitleHighlightInput').value.trim());
    setByPath(adminState, 'tabs.mercado.description', document.getElementById('marketDescriptionInput').value.trim());
    try {
        await saveContentState('Market guardado.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('addBackgroundBtn').addEventListener('click', async () => {
    getByPath(adminState, 'backgrounds', []).push(emptyBackground());
    renderBackgrounds();
    bindMediaTargetButtons();
    bindCopyButtons();
    try {
        await saveContentState('Fondo agregado.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('saveBackgroundsBtn').addEventListener('click', async () => {
    try {
        await saveContentState('Fondos guardados.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('openMediaFileBtn').addEventListener('click', () => document.getElementById('mediaUploadInput').click());
document.getElementById('refreshMediaLibraryBtn').addEventListener('click', () => {
    loadMediaLibrary({ statusIds: ['mediaLibraryStatus'], renderGlobal: true, renderField: false });
});
document.getElementById('copyLatestMediaBtn').addEventListener('click', async () => {
    try {
        await copyToClipboard(document.getElementById('mediaLatestUrl').value.trim());
        updateMediaStatus('mediaUploadStatus', 'Último enlace copiado al portapapeles.', 'success');
    } catch (error) {
        updateMediaStatus('mediaUploadStatus', error.message, 'error');
    }
});
document.getElementById('useLatestMediaForTargetBtn').addEventListener('click', async () => {
    await assignImageToField(document.getElementById('mediaLatestUrl').value.trim());
});

document.getElementById('closeFieldMediaModalBtn').addEventListener('click', closeFieldMediaModal);
document.getElementById('cancelFieldMediaModalBtn').addEventListener('click', closeFieldMediaModal);
document.getElementById('fieldMediaModal').addEventListener('click', (event) => {
    if (event.target.id === 'fieldMediaModal') closeFieldMediaModal();
});
document.getElementById('refreshFieldMediaLibraryBtn').addEventListener('click', () => {
    loadMediaLibrary({
        statusIds: ['mediaLibraryStatus', 'fieldMediaLibraryStatus'],
        renderGlobal: true,
        renderField: true,
        selectedUrl: fieldMediaTarget?.currentValue || '',
    });
});
document.getElementById('applyFieldManualUrlBtn').addEventListener('click', async () => {
    await assignImageToField(document.getElementById('fieldMediaManualUrl').value.trim(), { closeModal: true });
});
document.getElementById('copyFieldManualUrlBtn').addEventListener('click', async () => {
    try {
        await copyToClipboard(document.getElementById('fieldMediaManualUrl').value.trim());
        updateMediaStatus('fieldMediaStatus', 'Enlace copiado al portapapeles.', 'success');
    } catch (error) {
        updateMediaStatus('fieldMediaStatus', error.message, 'error');
    }
});

setupDropzone('mediaDropzone', 'mediaUploadInput', async (file) => {
    try {
        await uploadImageFile(file, { statusTargetId: 'mediaUploadStatus', refreshField: !!fieldMediaTarget });
    } catch (error) {
        updateMediaStatus('mediaUploadStatus', error.message, 'error');
    }
});

setupDropzone('fieldMediaDropzone', 'fieldMediaFileInput', async (file) => {
    try {
        const result = await uploadImageFile(file, { statusTargetId: 'fieldMediaStatus', refreshField: true });
        document.getElementById('fieldMediaManualUrl').value = result.url || '';
        await assignImageToField(result.url || '', { closeModal: true });
    } catch (error) {
        updateMediaStatus('fieldMediaStatus', error.message, 'error');
    }
});

hydrateGeneralFields();
renderCrudSections();
renderBackgrounds();
bindMediaTargetButtons();
bindCopyButtons();
loadMediaLibrary({ statusIds: ['mediaLibraryStatus'], renderGlobal: true, renderField: false });
</script>
</body>
</html>

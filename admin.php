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
        body { background: radial-gradient(circle at top, #15324d 0%, #090d18 55%, #04050b 100%); }
        .glass { background: linear-gradient(135deg, rgba(255,255,255,0.16), rgba(255,255,255,0.04)); backdrop-filter: blur(18px); border: 1px solid rgba(255,255,255,0.25); }
        .admin-tab.active { background: rgba(34,211,238,.25); color: white; border-color: rgba(34,211,238,.5); }
        .admin-panel { display:none; }
        .admin-panel.active { display:block; }
        .media-dropzone.dragover { border-color: rgba(103, 232, 249, .95); background: rgba(34, 211, 238, .15); }
        .media-target-btn.active { border-color: rgba(103, 232, 249, .95); background: rgba(34, 211, 238, .18); color: white; }
    </style>
</head>
<body class="min-h-screen text-slate-100">
    <div class="max-w-7xl mx-auto p-6 md:p-10 space-y-8">
        <header class="glass rounded-3xl p-6 md:p-8 flex flex-col lg:flex-row justify-between gap-4">
            <div>
                <p class="text-cyan-200 text-xs uppercase tracking-[0.2em]">Artista CMS</p>
                <h1 class="text-3xl md:text-4xl font-semibold">Dashboard Administrativo</h1>
                <p class="text-slate-300 mt-2">Sesión iniciada como <?= htmlspecialchars((string) ($user['name'] ?? $user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="flex flex-wrap gap-3 items-start">
                <a href="<?= htmlspecialchars(url_for('/'), ENT_QUOTES, 'UTF-8') ?>" class="rounded-xl bg-white/10 border border-white/20 px-4 py-2 hover:bg-white/20">Ver sitio</a>
                <a href="<?= htmlspecialchars(url_for('/logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="rounded-xl bg-white/10 border border-white/20 px-4 py-2 hover:bg-white/20">Cerrar sesión</a>
            </div>
        </header>

        <section class="glass rounded-3xl p-4 md:p-6">
            <div class="flex flex-wrap gap-3">
                <button type="button" class="admin-tab active rounded-2xl border border-white/20 px-4 py-3" data-admin-tab="general">General</button>
                <button type="button" class="admin-tab rounded-2xl border border-white/20 px-4 py-3" data-admin-tab="seo">SEO</button>
                <button type="button" class="admin-tab rounded-2xl border border-white/20 px-4 py-3" data-admin-tab="galeria">Galería</button>
                <button type="button" class="admin-tab rounded-2xl border border-white/20 px-4 py-3" data-admin-tab="market">Market</button>
                <button type="button" class="admin-tab rounded-2xl border border-white/20 px-4 py-3" data-admin-tab="media">Media Manager</button>
            </div>
        </section>

        <div id="adminAlert" class="hidden rounded-xl p-4 text-sm"></div>

        <section id="panel-general" class="admin-panel active glass rounded-3xl p-6 md:p-8 space-y-8">
            <div>
                <h2 class="text-xl font-semibold mb-5">Ajustes generales</h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <label class="block space-y-2">
                        <span class="text-sm text-slate-300">Nombre del sitio</span>
                        <input type="text" id="siteNameInput" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm text-slate-300">Tagline</span>
                        <input type="text" id="siteTaglineInput" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                    <label class="block space-y-2 md:col-span-2">
                        <span class="text-sm text-slate-300">Disponibilidad</span>
                        <input type="text" id="availabilityInput" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                </div>
                <div class="mt-5">
                    <button type="button" id="saveGeneralBtn" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-5 py-3 hover:bg-cyan-200">Guardar ajustes</button>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8 space-y-5">
                <div>
                    <h2 class="text-xl font-semibold">Datos de contacto y redes</h2>
                    <p class="text-sm text-slate-300 mt-1">Si un campo queda vacío, no se mostrará en el sitio. El WhatsApp también se usa como fallback en Market cuando un ítem no tiene enlace propio.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <label class="block space-y-2 md:col-span-2">
                        <span class="text-sm text-slate-300">Título del bloque</span>
                        <input type="text" id="contactTitleInput" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                    <label class="block space-y-2 md:col-span-2">
                        <span class="text-sm text-slate-300">Descripción</span>
                        <textarea id="contactDescriptionInput" rows="3" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none"></textarea>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm text-slate-300">WhatsApp</span>
                        <input type="text" id="contactWhatsappInput" placeholder="5492233011023" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm text-slate-300">Email</span>
                        <input type="email" id="contactEmailInput" placeholder="hola@dominio.com" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm text-slate-300">Instagram</span>
                        <input type="text" id="contactInstagramInput" placeholder="https://instagram.com/..." class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm text-slate-300">Facebook</span>
                        <input type="text" id="contactFacebookInput" placeholder="https://facebook.com/..." class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm text-slate-300">TikTok</span>
                        <input type="text" id="contactTiktokInput" placeholder="https://tiktok.com/@..." class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-sm text-slate-300">YouTube</span>
                        <input type="text" id="contactYoutubeInput" placeholder="https://youtube.com/..." class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    </label>
                </div>
                <div>
                    <button type="button" id="saveContactBtn" class="rounded-xl bg-white text-slate-900 font-semibold px-5 py-3 hover:bg-slate-100">Guardar contacto y redes</button>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8 space-y-5">
                <div>
                    <h2 class="text-xl font-semibold">Imágenes globales del sitio</h2>
                    <p class="text-sm text-slate-300 mt-1">Todos los campos de imagen principales ahora pueden abrir el media manager, subir archivos o reutilizar la biblioteca.</p>
                </div>
                <div class="grid xl:grid-cols-3 gap-5">
                    <article class="rounded-3xl border border-white/10 bg-slate-950/40 p-5 space-y-4">
                        <div>
                            <h3 class="font-semibold text-lg">Imagen destacada del hero</h3>
                            <p class="text-sm text-slate-400">Imagen principal de portada.</p>
                        </div>
                        <img id="heroFeaturedPreview" src="" alt="Vista previa hero" class="w-full h-44 rounded-2xl object-cover border border-white/10 bg-slate-900/50">
                        <label class="block space-y-2">
                            <span class="text-sm text-slate-300">URL o ruta</span>
                            <input type="text" id="heroFeaturedInput" data-image-input-key="hero.featured_image" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3">
                        </label>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" class="media-target-btn rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-media-target-key="hero.featured_image" data-media-target-type="image-object" data-media-target-input="heroFeaturedInput">Abrir media manager</button>
                            <button type="button" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-copy-from-input="heroFeaturedInput">Copiar enlace</button>
                        </div>
                    </article>
                    <article class="rounded-3xl border border-white/10 bg-slate-950/40 p-5 space-y-4">
                        <div>
                            <h3 class="font-semibold text-lg">Imagen de Academia</h3>
                            <p class="text-sm text-slate-400">Imagen del bloque de clases.</p>
                        </div>
                        <img id="academiaImagePreview" src="" alt="Vista previa academia" class="w-full h-44 rounded-2xl object-cover border border-white/10 bg-slate-900/50">
                        <label class="block space-y-2">
                            <span class="text-sm text-slate-300">URL o ruta</span>
                            <input type="text" id="academiaImageInput" data-image-input-key="tabs.academia.image" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3">
                        </label>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" class="media-target-btn rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-media-target-key="tabs.academia.image" data-media-target-type="image-object" data-media-target-input="academiaImageInput">Abrir media manager</button>
                            <button type="button" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-copy-from-input="academiaImageInput">Copiar enlace</button>
                        </div>
                    </article>
                </div>
                <div>
                    <button type="button" id="saveSiteImagesBtn" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-5 py-3 hover:bg-cyan-200">Guardar imágenes globales</button>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8 space-y-5">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <h2 class="text-xl font-semibold">Fondos del sitio</h2>
                        <p class="text-sm text-slate-300">Elige las imágenes que rotan de fondo en la portada.</p>
                    </div>
                    <button type="button" id="addBackgroundBtn" class="rounded-xl bg-white text-slate-900 font-semibold px-5 py-3 hover:bg-slate-100">+ Agregar fondo</button>
                </div>
                <div id="backgroundCrud" class="grid xl:grid-cols-2 gap-5"></div>
                <div>
                    <button type="button" id="saveBackgroundsBtn" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-5 py-3 hover:bg-cyan-200">Guardar fondos</button>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8">
                <h2 class="text-xl font-semibold mb-5">Cambiar contraseña</h2>
                <form id="passwordForm" class="grid md:grid-cols-3 gap-4">
                    <input type="password" name="current_password" required placeholder="Contraseña actual" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    <input type="password" name="new_password" required placeholder="Nueva contraseña" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    <input type="password" name="confirm_password" required placeholder="Confirmar nueva contraseña" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                    <div class="md:col-span-3">
                        <button class="rounded-xl bg-white text-slate-900 font-semibold px-5 py-3 hover:bg-slate-100">Actualizar contraseña</button>
                    </div>
                </form>
            </div>
        </section>

        <section id="panel-seo" class="admin-panel glass rounded-3xl p-6 md:p-8 space-y-4">
            <h2 class="text-xl font-semibold mb-2">Datos SEO</h2>
            <label class="block space-y-2">
                <span class="text-sm text-slate-300">SEO Title</span>
                <input type="text" id="seoTitleInput" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
            </label>
            <label class="block space-y-2">
                <span class="text-sm text-slate-300">SEO Description</span>
                <textarea id="seoDescriptionInput" rows="4" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none"></textarea>
            </label>
            <label class="block space-y-2">
                <span class="text-sm text-slate-300">SEO Keywords</span>
                <input type="text" id="seoKeywordsInput" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
            </label>
            <div class="grid lg:grid-cols-[minmax(0,1fr),320px] gap-5 items-start">
                <label class="block space-y-2">
                    <span class="text-sm text-slate-300">OG Image URL</span>
                    <input type="text" id="seoOgImageInput" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
                </label>
                <img id="seoOgPreview" src="" alt="Vista previa SEO OG" class="w-full h-40 rounded-2xl object-cover border border-white/10 bg-slate-900/50">
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" class="media-target-btn rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-media-target-key="site.seo.og_image" data-media-target-type="plain-string" data-media-target-input="seoOgImageInput">Abrir media manager</button>
                <button type="button" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-copy-from-input="seoOgImageInput">Copiar enlace</button>
                <button type="button" id="saveSeoBtn" class="rounded-xl bg-fuchsia-300 text-slate-900 font-semibold px-5 py-3 hover:bg-fuchsia-200">Guardar SEO</button>
            </div>
        </section>

        <section id="panel-galeria" class="admin-panel glass rounded-3xl p-6 md:p-8 space-y-6">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-xl font-semibold">CRUD Galería</h2>
                    <p class="text-sm text-slate-300">Administra las obras visibles en la sección frontal.</p>
                </div>
                <button type="button" id="addGalleryItemBtn" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-5 py-3 hover:bg-cyan-200">+ Agregar obra</button>
            </div>
            <div id="galleryCrud" class="grid xl:grid-cols-2 gap-5"></div>
            <div><button type="button" id="saveGalleryBtn" class="rounded-xl bg-white text-slate-900 font-semibold px-5 py-3 hover:bg-slate-100">Guardar galería</button></div>
        </section>

        <section id="panel-market" class="admin-panel glass rounded-3xl p-6 md:p-8 space-y-6">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-xl font-semibold">CRUD Market</h2>
                    <p class="text-sm text-slate-300">Administra los artistas/obras destacados del market.</p>
                </div>
                <button type="button" id="addMarketItemBtn" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-5 py-3 hover:bg-cyan-200">+ Agregar item</button>
            </div>
            <div id="marketCrud" class="grid xl:grid-cols-2 gap-5"></div>
            <div><button type="button" id="saveMarketBtn" class="rounded-xl bg-white text-slate-900 font-semibold px-5 py-3 hover:bg-slate-100">Guardar market</button></div>
        </section>

        <section id="panel-media" class="admin-panel glass rounded-3xl p-6 md:p-8 space-y-6">
            <div class="flex flex-col xl:flex-row gap-6">
                <div class="xl:w-[360px] space-y-4">
                    <div>
                        <h2 class="text-xl font-semibold">Administrador de imágenes</h2>
                        <p class="text-sm text-slate-300 mt-1">Arrastra, suelta o selecciona imágenes. Luego podrás copiar el enlace o asignarlas a cualquier campo.</p>
                    </div>
                    <label id="mediaDropzone" for="mediaUploadInput" class="media-dropzone block rounded-3xl border-2 border-dashed border-white/20 bg-slate-950/40 p-6 text-center cursor-pointer transition">
                        <div class="space-y-3">
                            <div class="text-4xl">🖼️</div>
                            <div>
                                <p class="font-semibold">Arrastrar y soltar imágenes aquí</p>
                                <p class="text-sm text-slate-300">O haz clic para seleccionar archivos JPG, PNG o WEBP de hasta 5MB.</p>
                            </div>
                        </div>
                    </label>
                    <input id="mediaUploadInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden">
                    <div class="flex flex-wrap gap-3">
                        <button type="button" id="openMediaFileBtn" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-5 py-3 hover:bg-cyan-200">Seleccionar imagen</button>
                        <button type="button" id="refreshMediaLibraryBtn" class="rounded-xl bg-white/10 border border-white/20 px-5 py-3 hover:bg-white/20">Actualizar biblioteca</button>
                    </div>
                    <div id="mediaUploadStatus" class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">Aquí verás el estado de la subida y el enlace listo para copiar.</div>
                    <label class="block space-y-2">
                        <span class="text-sm text-slate-300">Último enlace subido</span>
                        <input id="mediaLatestUrl" type="text" readonly class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 text-slate-100 outline-none">
                    </label>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" id="copyLatestMediaBtn" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20">Copiar último enlace</button>
                        <button type="button" id="useLatestMediaForTargetBtn" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20">Usar en campo seleccionado</button>
                    </div>
                </div>
                <div class="flex-1 space-y-4">
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <h3 class="text-lg font-semibold">Biblioteca</h3>
                            <p id="mediaLibraryStatus" class="text-sm text-slate-300">Cargando imágenes...</p>
                        </div>
                        <div class="text-sm text-slate-400">Haz clic en una tarjeta para previsualizar, copiar o asignar.</div>
                    </div>
                    <div id="mediaLibraryGrid" class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4"></div>
                </div>
            </div>
        </section>
    </div>

<div id="fieldMediaModal" class="hidden fixed inset-0 bg-black/70 z-[100] items-center justify-center px-4 py-8 overflow-y-auto">
    <div class="glass rounded-2xl p-6 max-w-5xl w-full space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-cyan-200 text-xs uppercase tracking-[0.2em]">Media Manager</p>
                <h3 class="text-2xl font-semibold">Seleccionar imagen para el campo</h3>
                <p id="fieldMediaTargetLabel" class="text-sm text-slate-300 mt-1">Sin campo seleccionado.</p>
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
                            <p class="text-sm text-slate-300">Puedes arrastrar y soltar aquí.</p>
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
                <div id="fieldMediaStatus" class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">Selecciona o sube una imagen para asignarla a este campo.</div>
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

document.querySelectorAll('[data-admin-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelectorAll('[data-admin-tab]').forEach((tab) => tab.classList.remove('active'));
        document.querySelectorAll('.admin-panel').forEach((panel) => panel.classList.remove('active'));
        button.classList.add('active');
        document.getElementById(`panel-${button.dataset.adminTab}`)?.classList.add('active');
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
    try {
        await saveContentState('Galería guardada.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('saveMarketBtn').addEventListener('click', async () => {
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

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
            <label class="block space-y-2">
                <span class="text-sm text-slate-300">OG Image URL</span>
                <input type="url" id="seoOgImageInput" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3 outline-none">
            </label>
            <button type="button" id="saveSeoBtn" class="rounded-xl bg-fuchsia-300 text-slate-900 font-semibold px-5 py-3 hover:bg-fuchsia-200">Guardar SEO</button>
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
    </div>

<div id="backgroundLibraryModal" class="hidden fixed inset-0 bg-black/70 z-[100] items-center justify-center px-4">
    <div class="glass rounded-2xl p-6 max-w-4xl w-full space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-cyan-200 text-xs uppercase tracking-[0.2em]">Biblioteca</p>
                <h3 class="text-2xl font-semibold">Elegir imagen de fondo</h3>
            </div>
            <button type="button" id="closeBackgroundLibraryBtn" class="rounded-xl bg-white/10 border border-white/20 px-4 py-2 hover:bg-white/20">Cerrar</button>
        </div>
        <p id="backgroundLibraryStatus" class="text-xs text-slate-300">Cargando imágenes...</p>
        <div id="backgroundLibraryGrid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 max-h-[60vh] overflow-y-auto pr-1"></div>
        <div class="flex justify-end gap-2">
            <button type="button" id="cancelBackgroundLibraryBtn" class="rounded-xl bg-white/10 border border-white/20 px-4 py-2 hover:bg-white/20">Cancelar</button>
            <button type="button" id="confirmBackgroundLibraryBtn" class="rounded-xl bg-cyan-300 text-slate-900 font-semibold px-4 py-2 hover:bg-cyan-200">Usar imagen</button>
        </div>
    </div>
</div>

<script>
const adminState = <?= json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const endpoints = {
    saveContent: <?= json_encode(url_for('/api/save-content.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    adminAction: <?= json_encode(url_for('/api/admin-action.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    listImages: <?= json_encode(url_for('/api/list-images.php'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
};

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

function emptyItem(title) {
    return {
        image: { source_type: 'url', value: '', alt: '' },
        alt: '',
        title,
        subtitle: '',
        description: '',
        link_label: 'Ver más',
        link_url: 'https://',
    };
}

function emptyBackground() {
    return {
        image: { source_type: 'url', value: '' },
    };
}

function renderCrudCard(item, index, collectionKey) {
    const isMarket = collectionKey === 'tabs.mercado.items';
    return `
        <article class="rounded-3xl border border-white/10 bg-slate-950/40 p-5 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <h3 class="font-semibold text-lg">${isMarket ? 'Item market' : 'Obra'} #${index + 1}</h3>
                <button type="button" class="rounded-xl bg-red-500/80 px-3 py-2 text-sm" data-remove-item="${collectionKey}" data-index="${index}">Eliminar</button>
            </div>
            <label class="block space-y-2"><span class="text-sm text-slate-300">Título</span><input type="text" value="${item.title || ''}" data-input-key="${collectionKey}[${index}].title" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
            <label class="block space-y-2"><span class="text-sm text-slate-300">Subtítulo</span><input type="text" value="${item.subtitle || ''}" data-input-key="${collectionKey}[${index}].subtitle" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
            <label class="block space-y-2"><span class="text-sm text-slate-300">Descripción</span><textarea rows="3" data-input-key="${collectionKey}[${index}].description" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3">${item.description || ''}</textarea></label>
            <label class="block space-y-2"><span class="text-sm text-slate-300">Imagen (URL o ruta subida)</span><input type="text" value="${item.image?.value || ''}" data-image-value-key="${collectionKey}[${index}].image" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
            <label class="block space-y-2"><span class="text-sm text-slate-300">Etiqueta del enlace</span><input type="text" value="${item.link_label || ''}" data-input-key="${collectionKey}[${index}].link_label" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
            <label class="block space-y-2"><span class="text-sm text-slate-300">URL del enlace</span><input type="url" value="${item.link_url || ''}" data-input-key="${collectionKey}[${index}].link_url" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3"></label>
        </article>
    `;
}

let backgroundModalIndex = -1;
let backgroundLibraryLoading = false;
let selectedBackgroundUrl = '';

function normalizeImageUrl(url) {
    if (!url) return '';
    if (/^https?:\/\//i.test(url)) return url;
    try {
        return new URL(url, window.location.origin).toString();
    } catch (error) {
        return url;
    }
}

function renderBackgroundCard(background, index) {
    const imageValue = background?.image?.value || '';
    const preview = imageValue || 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="600" height="360"><rect width="100%" height="100%" fill="#0f172a"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="sans-serif" font-size="24">Sin imagen</text></svg>');

    return `
        <article class="rounded-3xl border border-white/10 bg-slate-950/40 p-5 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <h3 class="font-semibold text-lg">Fondo #${index + 1}</h3>
                <button type="button" class="rounded-xl bg-red-500/80 px-3 py-2 text-sm" data-remove-background="${index}">Eliminar</button>
            </div>
            <img src="${preview}" alt="Vista previa del fondo ${index + 1}" class="w-full h-40 rounded-2xl object-cover border border-white/10 bg-slate-900/50">
            <label class="block space-y-2">
                <span class="text-sm text-slate-300">Imagen de fondo (URL o ruta subida)</span>
                <input type="text" value="${imageValue}" data-background-key="backgrounds[${index}].image" class="w-full rounded-xl border border-white/20 bg-slate-900/60 px-4 py-3">
            </label>
            <div class="flex gap-3 flex-wrap">
                <button type="button" class="rounded-xl bg-white/10 border border-white/20 px-4 py-3 hover:bg-white/20" data-open-background-library="${index}">Elegir de biblioteca</button>
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
                source_type: /^https?:\/\//i.test(value) ? 'url' : (value ? 'upload' : 'url'),
                value,
            });
            const card = input.closest('article');
            const preview = card?.querySelector('img');
            if (preview) {
                preview.src = value || 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="600" height="360"><rect width="100%" height="100%" fill="#0f172a"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="sans-serif" font-size="24">Sin imagen</text></svg>');
            }
        };
    });

    document.querySelectorAll('[data-remove-background]').forEach((button) => {
        button.onclick = async () => {
            getByPath(adminState, 'backgrounds', []).splice(Number(button.dataset.removeBackground), 1);
            renderBackgrounds();
            try {
                await saveContentState('Fondo eliminado.');
            } catch (error) {
                showAlert(error.message, 'error');
            }
        };
    });

    document.querySelectorAll('[data-open-background-library]').forEach((button) => {
        button.onclick = () => openBackgroundLibraryModal(Number(button.dataset.openBackgroundLibrary));
    });
}

function renderBackgroundLibrary(images = [], selectedUrl = '') {
    const grid = document.getElementById('backgroundLibraryGrid');
    const normalizedSelectedUrl = normalizeImageUrl(selectedUrl);
    selectedBackgroundUrl = '';
    grid.innerHTML = '';

    images.forEach((image) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.url = image.url;
        button.className = 'border border-white/20 rounded-lg overflow-hidden bg-white/5 transition hover:border-cyan-300';

        const thumb = document.createElement('img');
        thumb.src = image.url;
        thumb.alt = image.name || 'Imagen subida';
        thumb.className = 'w-full h-24 object-cover block';

        const name = document.createElement('span');
        name.className = 'block text-[10px] p-2 truncate text-left';
        name.textContent = image.name || image.url;

        button.appendChild(thumb);
        button.appendChild(name);
        button.addEventListener('click', () => {
            selectedBackgroundUrl = image.url;
            grid.querySelectorAll('[data-url]').forEach((candidate) => candidate.classList.remove('ring-2', 'ring-cyan-300'));
            button.classList.add('ring-2', 'ring-cyan-300');
        });

        if (normalizeImageUrl(image.url) === normalizedSelectedUrl) {
            selectedBackgroundUrl = image.url;
            button.classList.add('ring-2', 'ring-cyan-300');
        }

        grid.appendChild(button);
    });
}

async function loadBackgroundLibrary(currentSrc = '') {
    if (backgroundLibraryLoading) return;
    backgroundLibraryLoading = true;

    const status = document.getElementById('backgroundLibraryStatus');
    status.textContent = 'Cargando imágenes...';
    status.className = 'text-xs text-slate-300';

    try {
        const response = await fetch(endpoints.listImages, { method: 'GET' });
        const result = await response.json();

        if (!response.ok || !result.ok || !Array.isArray(result.images)) {
            throw new Error(result.error || 'No se pudo cargar la biblioteca.');
        }

        renderBackgroundLibrary(result.images, currentSrc);
        status.textContent = result.images.length === 0 ? 'No hay imágenes subidas todavía.' : 'Selecciona una imagen para este fondo.';
        status.className = result.images.length === 0 ? 'text-xs text-yellow-300' : 'text-xs text-slate-300';
    } catch (error) {
        status.textContent = error.message || 'Error al cargar la biblioteca.';
        status.className = 'text-xs text-red-300';
        document.getElementById('backgroundLibraryGrid').innerHTML = '';
    } finally {
        backgroundLibraryLoading = false;
    }
}

function openBackgroundLibraryModal(index) {
    backgroundModalIndex = index;
    const currentValue = getByPath(adminState, `backgrounds[${index}].image.value`, '');
    document.getElementById('backgroundLibraryModal').classList.remove('hidden');
    document.getElementById('backgroundLibraryModal').classList.add('flex');
    loadBackgroundLibrary(currentValue);
}

function closeBackgroundLibraryModal() {
    backgroundModalIndex = -1;
    selectedBackgroundUrl = '';
    document.getElementById('backgroundLibraryModal').classList.add('hidden');
    document.getElementById('backgroundLibraryModal').classList.remove('flex');
}

function bindCrudInputs() {
    document.querySelectorAll('[data-input-key]').forEach((input) => {
        input.oninput = () => setByPath(adminState, input.dataset.inputKey, input.value);
    });
    document.querySelectorAll('[data-image-value-key]').forEach((input) => {
        input.oninput = () => {
            const key = input.dataset.imageValueKey;
            setByPath(adminState, key, { source_type: /^https?:\/\//i.test(input.value) ? 'url' : 'upload', value: input.value, alt: '' });
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
}

function hydrateGeneralFields() {
    document.getElementById('siteNameInput').value = getByPath(adminState, 'site.name', '');
    document.getElementById('siteTaglineInput').value = getByPath(adminState, 'site.tagline', '');
    document.getElementById('availabilityInput').value = getByPath(adminState, 'site.availability', '');
    document.getElementById('seoTitleInput').value = getByPath(adminState, 'site.title', '');
    document.getElementById('seoDescriptionInput').value = getByPath(adminState, 'site.seo.description', '');
    document.getElementById('seoKeywordsInput').value = getByPath(adminState, 'site.seo.keywords', '');
    document.getElementById('seoOgImageInput').value = getByPath(adminState, 'site.seo.og_image', '');
}

document.querySelectorAll('[data-admin-tab]').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelectorAll('[data-admin-tab]').forEach((tab) => tab.classList.remove('active'));
        document.querySelectorAll('.admin-panel').forEach((panel) => panel.classList.remove('active'));
        button.classList.add('active');
        document.getElementById(`panel-${button.dataset.adminTab}`)?.classList.add('active');
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

document.getElementById('saveSeoBtn').addEventListener('click', async () => {
    setByPath(adminState, 'site.title', document.getElementById('seoTitleInput').value.trim());
    setByPath(adminState, 'site.seo.description', document.getElementById('seoDescriptionInput').value.trim());
    setByPath(adminState, 'site.seo.keywords', document.getElementById('seoKeywordsInput').value.trim());
    setByPath(adminState, 'site.seo.og_image', document.getElementById('seoOgImageInput').value.trim());
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
    getByPath(adminState, 'tabs.obras.items', []).push(emptyItem('Nueva obra'));
    renderCrudSections();
    try {
        await saveContentState('Obra agregada.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('addMarketItemBtn').addEventListener('click', async () => {
    getByPath(adminState, 'tabs.mercado.items', []).push(emptyItem('Nuevo artista'));
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

hydrateGeneralFields();
renderCrudSections();
renderBackgrounds();

document.getElementById('addBackgroundBtn').addEventListener('click', async () => {
    getByPath(adminState, 'backgrounds', []).push(emptyBackground());
    renderBackgrounds();
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

document.getElementById('closeBackgroundLibraryBtn').addEventListener('click', closeBackgroundLibraryModal);
document.getElementById('cancelBackgroundLibraryBtn').addEventListener('click', closeBackgroundLibraryModal);
document.getElementById('confirmBackgroundLibraryBtn').addEventListener('click', async () => {
    if (backgroundModalIndex < 0 || !selectedBackgroundUrl) {
        showAlert('Selecciona una imagen de la biblioteca.', 'error');
        return;
    }

    setByPath(adminState, `backgrounds[${backgroundModalIndex}].image`, {
        source_type: 'upload',
        value: selectedBackgroundUrl,
    });
    renderBackgrounds();
    closeBackgroundLibraryModal();

    try {
        await saveContentState('Fondo actualizado.');
    } catch (error) {
        showAlert(error.message, 'error');
    }
});

document.getElementById('backgroundLibraryModal').addEventListener('click', (event) => {
    if (event.target.id === 'backgroundLibraryModal') closeBackgroundLibraryModal();
});
</script>
</body>
</html>

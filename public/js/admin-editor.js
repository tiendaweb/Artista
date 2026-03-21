function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach((el) => el.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach((el) => el.classList.remove('active'));
    document.getElementById(tabId)?.classList.add('active');
    document.querySelector(`[data-tab="${tabId}"]`)?.classList.add('active');
}

const layers = document.querySelectorAll('.bg-layer');
let currentLayer = 0;
if (layers.length > 1) {
    setInterval(() => {
        layers[currentLayer].classList.remove('active');
        currentLayer = (currentLayer + 1) % layers.length;
        layers[currentLayer].classList.add('active');
    }, 20000);
}

const isAuthenticated = window.APP_IS_AUTHENTICATED === true;
const contentState = window.APP_CONTENT_STATE || {};
let editMode = false;
let currentImageKey = '';
let currentLinkKey = '';
let currentCollectionKey = '';
let currentCollectionIndex = -1;
let imageMode = 'url';
let libraryLoading = false;
let selectedLibraryUrl = '';
const COLLECTION_MODAL_IMAGE_KEY = '__collection_modal_image__';

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
    } catch (_) {
        return url;
    }
}

function fieldMessage(key, msg, ok) {
    const target = document.querySelector(`[data-message-for="${key}"]`);
    if (!target) return;
    target.textContent = msg;
    target.className = `field-message ${ok ? 'ok' : 'error'}`;
}

function normalizeEditableText(element) {
    return String(element?.innerText || '')
        .replace(/\r\n?/g, '\n')
        .replace(/\n{3,}/g, '\n\n')
        .trim();
}


async function persistContent(changedKeys = []) {
    const response = await fetch(window.ADMIN_EDITOR_ENDPOINTS.saveContent, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(contentState),
    });
    const result = await response.json();
    if (!response.ok || !result.ok) {
        changedKeys.forEach((k) => fieldMessage(k, result.error || 'Error al guardar', false));
        throw new Error(result.error || 'Error de guardado');
    }
    if (result.content) {
        Object.keys(contentState).forEach((key) => delete contentState[key]);
        Object.assign(contentState, result.content);
    }
    changedKeys.forEach((k) => fieldMessage(k, 'Guardado', true));
}

function renderLibrary(images = [], selectedUrl = '') {
    const grid = document.getElementById('libraryGrid');
    const normalizedSelectedUrl = normalizeImageUrl(selectedUrl);
    grid.innerHTML = '';

    images.forEach((image) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.url = image.url;
        button.className = 'border border-white/20 rounded-lg overflow-hidden bg-white/5 transition hover:border-art-neon';

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
            selectedLibraryUrl = image.url;
            grid.querySelectorAll('[data-url]').forEach((candidate) => candidate.classList.remove('ring-2', 'ring-art-neon'));
            button.classList.add('ring-2', 'ring-art-neon');
        });

        if (normalizeImageUrl(image.url) === normalizedSelectedUrl) {
            selectedLibraryUrl = image.url;
            button.classList.add('ring-2', 'ring-art-neon');
        }

        grid.appendChild(button);
    });
}

async function loadImageLibrary(currentSrc = '') {
    if (libraryLoading) return;
    libraryLoading = true;
    const status = document.getElementById('libraryStatus');
    status.textContent = 'Cargando imágenes...';
    status.className = 'text-xs text-white/70';

    try {
        const response = await fetch(window.ADMIN_EDITOR_ENDPOINTS.listImages, { method: 'GET' });
        const result = await response.json();
        if (!response.ok || !result.ok || !Array.isArray(result.images)) {
            throw new Error(result.error || 'No se pudo cargar la biblioteca.');
        }

        renderLibrary(result.images, currentSrc);
        status.textContent = result.images.length === 0 ? 'No hay imágenes subidas todavía.' : 'Selecciona una imagen de la biblioteca.';
        status.className = result.images.length === 0 ? 'text-xs text-yellow-300' : 'text-xs text-white/70';
    } catch (error) {
        status.textContent = error.message || 'Error cargando la biblioteca.';
        status.className = 'text-xs text-red-400';
        document.getElementById('libraryGrid').innerHTML = '';
    } finally {
        libraryLoading = false;
    }
}

function switchImageMode(mode) {
    imageMode = ['upload', 'library'].includes(mode) ? mode : 'url';
    document.getElementById('urlPane').classList.toggle('hidden', imageMode !== 'url');
    document.getElementById('uploadPane').classList.toggle('hidden', imageMode !== 'upload');
    document.getElementById('libraryPane').classList.toggle('hidden', imageMode !== 'library');
    document.querySelectorAll('.modal-mode').forEach((btn) => {
        btn.classList.toggle('bg-art-neon', btn.dataset.mode === imageMode);
        btn.classList.toggle('text-black', btn.dataset.mode === imageMode);
    });
}

function isCollectionModalImageEdit() {
    return currentImageKey === COLLECTION_MODAL_IMAGE_KEY;
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.getElementById('imageModal').classList.remove('flex');
}

function syncCollectionImagePreview(url = '') {
    const preview = document.getElementById('collectionImagePreview');
    const emptyState = document.getElementById('collectionImageEmpty');
    if (!preview || !emptyState) return;

    if (url) {
        preview.src = url;
        preview.classList.remove('hidden');
        emptyState.classList.add('hidden');
        return;
    }

    preview.src = '';
    preview.classList.add('hidden');
    emptyState.classList.remove('hidden');
}

function applyImageToCollectionForm(url = '', sourceType = 'url') {
    document.getElementById('collectionImageInput').value = url;
    syncCollectionImagePreview(url);
    document.getElementById('modalFeedback').textContent = url
        ? 'Imagen lista para usar en este formulario.'
        : 'Imagen eliminada del formulario.';
    document.getElementById('modalFeedback').className = url ? 'text-xs text-green-400' : 'text-xs text-white/60';
    if (url) {
        document.getElementById('collectionItemFeedback').textContent = `Imagen vinculada desde ${sourceType === 'library' ? 'la biblioteca' : sourceType === 'upload' ? 'una subida' : 'una URL manual'}.`;
        document.getElementById('collectionItemFeedback').className = 'text-xs text-green-400';
    }
}

function openImageModalForCollection(preferredMode = 'library') {
    currentImageKey = COLLECTION_MODAL_IMAGE_KEY;
    document.getElementById('imageModal').classList.remove('hidden');
    document.getElementById('imageModal').classList.add('flex');
    document.getElementById('imageUrlInput').value = document.getElementById('collectionImageInput').value.trim();
    document.getElementById('imageFileInput').value = '';
    selectedLibraryUrl = '';
    document.getElementById('modalFeedback').textContent = 'Selecciona una imagen para este elemento.';
    document.getElementById('modalFeedback').className = 'text-xs text-white/70';
    switchImageMode(preferredMode);
    if (preferredMode === 'library') {
        loadImageLibrary(document.getElementById('collectionImageInput').value.trim());
    }
}

function whatsappNumber(number = '') {
    return String(number || '').replace(/\D+/g, '');
}

function buildWhatsappUrl(number = '', message = '') {
    const normalized = whatsappNumber(number);
    if (!normalized) return '';
    return `https://wa.me/${normalized}?text=${encodeURIComponent(message)}`;
}

function createEmptyCollectionItem(collectionKey) {
    const isMarket = collectionKey === 'tabs.mercado.items';
    return {
        image: { source_type: 'url', value: '', alt: '' },
        alt: '',
        title: isMarket ? 'Nuevo artista' : 'Nueva obra',
        subtitle: '',
        description: '',
        link_label: isMarket ? 'Consultar por WhatsApp' : 'Ver más',
        link_url: '',
    };
}

function getCollectionLabels(collectionKey) {
    return collectionKey === 'tabs.mercado.items'
        ? { singular: 'item market', plural: 'market' }
        : { singular: 'obra', plural: 'galería' };
}

function closeCollectionItemModal() {
    document.getElementById('collectionItemModal').classList.add('hidden');
    document.getElementById('collectionItemModal').classList.remove('flex');
    document.getElementById('collectionItemFeedback').textContent = '';
    syncCollectionImagePreview('');
    currentCollectionKey = '';
    currentCollectionIndex = -1;
}

function openCollectionItemModal(collectionKey, index = -1) {
    currentCollectionKey = collectionKey;
    currentCollectionIndex = index;

    const labels = getCollectionLabels(collectionKey);
    const item = index >= 0
        ? getByPath(contentState, `${collectionKey}[${index}]`, createEmptyCollectionItem(collectionKey))
        : createEmptyCollectionItem(collectionKey);

    document.getElementById('collectionModalEyebrow').textContent = labels.plural;
    document.getElementById('collectionModalTitle').textContent = index >= 0
        ? `Editar ${labels.singular}`
        : `Agregar ${labels.singular}`;
    document.getElementById('collectionTitleInput').value = item.title || '';
    document.getElementById('collectionSubtitleInput').value = item.subtitle || '';
    document.getElementById('collectionDescriptionInput').value = item.description || '';
    document.getElementById('collectionImageInput').value = item.image?.value || '';
    syncCollectionImagePreview(item.image?.value || '');
    document.getElementById('collectionAltInput').value = item.alt || item.image?.alt || '';
    document.getElementById('collectionLinkLabelInput').value = item.link_label || (collectionKey === 'tabs.mercado.items' ? 'Consultar por WhatsApp' : 'Ver más');
    document.getElementById('collectionLinkUrlInput').value = item.link_url || '';
    document.getElementById('collectionItemFeedback').textContent = '';
    document.getElementById('collectionItemFeedback').className = 'text-xs';

    document.getElementById('collectionItemModal').classList.remove('hidden');
    document.getElementById('collectionItemModal').classList.add('flex');
}

function buildCollectionItemPayload() {
    const imageValue = document.getElementById('collectionImageInput').value.trim();

    return {
        image: {
            source_type: /^https?:\/\//i.test(imageValue) ? 'url' : (imageValue ? 'upload' : 'url'),
            value: imageValue,
            alt: document.getElementById('collectionAltInput').value.trim(),
        },
        alt: document.getElementById('collectionAltInput').value.trim(),
        title: document.getElementById('collectionTitleInput').value.trim(),
        subtitle: document.getElementById('collectionSubtitleInput').value.trim(),
        description: document.getElementById('collectionDescriptionInput').value.trim(),
        link_label: document.getElementById('collectionLinkLabelInput').value.trim() || (currentCollectionKey === 'tabs.mercado.items' ? 'Consultar por WhatsApp' : 'Ver más'),
        link_url: document.getElementById('collectionLinkUrlInput').value.trim(),
    };
}

function renderCollectionItem(item, index, collectionKey) {
    const isMarket = collectionKey === 'tabs.mercado.items';
    const titleKey = `${collectionKey}[${index}].title`;
    const subtitleKey = `${collectionKey}[${index}].subtitle`;
    const descriptionKey = `${collectionKey}[${index}].description`;
    const imageKey = `${collectionKey}[${index}].image`;
    const linkLabelKey = `${collectionKey}[${index}].link_label`;
    const linkUrlKey = `${collectionKey}[${index}].link_url`;
    const imageSrc = item.image?.value || '';
    const sourceType = item.image?.source_type || 'url';
    const whatsapp = getByPath(contentState, 'site.contact.whatsapp', '');
    const fallbackMarketUrl = isMarket && !item.link_url
        ? buildWhatsappUrl(whatsapp, `Hola, me interesa comprar la obra ${item.title || 'esta obra'}. ¿Está disponible?`)
        : '';
    const actionUrl = item.link_url || fallbackMarketUrl || '#';
    const actionLabel = item.link_label || (isMarket && fallbackMarketUrl ? 'Consultar por WhatsApp' : 'Ver más');
    const deleteButton = isAuthenticated
        ? `<button type="button" class="delete-icon" data-delete-collection="${collectionKey}" data-index="${index}">✕</button>`
        : '';
    const editButton = isAuthenticated
        ? `<button type="button" class="item-edit-btn" data-edit-collection="${collectionKey}" data-index="${index}">Editar</button>`
        : '';

    if (isMarket) {
        return `
            <article class="glass p-4 rounded-2xl editable-wrapper" data-collection-item="${collectionKey}" data-index="${index}">
                ${deleteButton}
                ${editButton}
                <div class="aspect-square bg-gray-800 rounded-xl mb-4 overflow-hidden">
                    <img src="${imageSrc}" data-edit-key="${imageKey}" data-edit-type="image" data-source-type="${sourceType}" class="w-full h-full object-cover" alt="${item.alt || ''}">
                </div>
                <span class="edit-icon" data-edit-target="${imageKey}">✎</span>
                <p class="text-sm font-bold" data-edit-key="${titleKey}" data-edit-type="text">${item.title || ''}</p>
                <p class="text-[10px] text-art-neon uppercase tracking-[0.2em] mb-3" data-edit-key="${subtitleKey}" data-edit-type="text">${item.subtitle || ''}</p>
                <p class="text-sm opacity-60 mb-4 preserve-breaks" data-edit-key="${descriptionKey}" data-edit-type="text">${item.description || ''}</p>
                <a href="${actionUrl}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 text-sm text-art-neon" data-edit-link-key="${linkUrlKey}">
                    <span data-edit-key="${linkLabelKey}" data-edit-type="text">${actionLabel}</span>
                </a>
                <span class="edit-icon" data-edit-link-target="${linkUrlKey}">🔗</span>
            </article>
        `;
    }

    return `
        <article class="glass glass-hover p-4 rounded-3xl break-inside-avoid editable-wrapper" data-collection-item="${collectionKey}" data-index="${index}">
            ${deleteButton}
            ${editButton}
            <img src="${imageSrc}" data-edit-key="${imageKey}" data-edit-type="image" data-source-type="${sourceType}" class="rounded-2xl w-full mb-4" alt="${item.alt || ''}">
            <span class="edit-icon" data-edit-target="${imageKey}">✎</span>
            <h3 class="font-serif text-xl" data-edit-key="${titleKey}" data-edit-type="text">${item.title || ''}</h3>
            <p class="text-xs text-art-neon mb-2" data-edit-key="${subtitleKey}" data-edit-type="text">${item.subtitle || ''}</p>
            <p class="text-sm opacity-60 mb-4 preserve-breaks" data-edit-key="${descriptionKey}" data-edit-type="text">${item.description || ''}</p>
            <a href="${actionUrl}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 text-sm text-art-neon" data-edit-link-key="${linkUrlKey}">
                <span data-edit-key="${linkLabelKey}" data-edit-type="text">${actionLabel}</span>
            </a>
            <span class="edit-icon" data-edit-link-target="${linkUrlKey}">🔗</span>
            <span class="field-message" data-message-for="${titleKey}"></span>
        </article>
    `;
}

function bindEditInteractions() {
    if (!isAuthenticated) return;

    document.querySelectorAll('.edit-icon[data-edit-target]').forEach((btn) => {
        btn.onclick = () => {
            const key = btn.dataset.editTarget;
            const imageEl = document.querySelector(`[data-edit-key="${key}"][data-edit-type="image"]`);
            if (!imageEl) {
                const textEl = document.querySelector(`[data-edit-key="${key}"][data-edit-type="text"]`);
                if (textEl && editMode) textEl.focus();
                return;
            }
            currentImageKey = key;
            document.getElementById('imageModal').classList.remove('hidden');
            document.getElementById('imageModal').classList.add('flex');
            document.getElementById('imageUrlInput').value = imageEl.getAttribute('src') || '';
            document.getElementById('imageFileInput').value = '';
            selectedLibraryUrl = '';
            switchImageMode(imageEl.dataset.sourceType || 'url');
            document.getElementById('modalFeedback').textContent = '';
            if (imageEl.dataset.sourceType === 'upload') {
                switchImageMode('library');
                loadImageLibrary(imageEl.getAttribute('src') || '');
            }
        };
    });

    document.querySelectorAll('.edit-icon[data-edit-link-target]').forEach((btn) => {
        btn.onclick = () => {
            currentLinkKey = btn.dataset.editLinkTarget;
            document.getElementById('linkModal').classList.remove('hidden');
            document.getElementById('linkModal').classList.add('flex');
            document.getElementById('linkUrlInput').value = getByPath(contentState, currentLinkKey, '');
            document.getElementById('linkFeedback').textContent = '';
        };
    });

    document.querySelectorAll('[data-delete-collection]').forEach((button) => {
        button.onclick = async () => {
            const collectionKey = button.dataset.deleteCollection;
            const index = Number(button.dataset.index);
            const items = getByPath(contentState, collectionKey, []);
            if (!Array.isArray(items)) return;
            items.splice(index, 1);
            setByPath(contentState, collectionKey, items);
            renderCollections();
            try {
                await persistContent([collectionKey]);
            } catch (error) {
                alert(error.message);
            }
        };
    });

    document.querySelectorAll('[data-edit-collection]').forEach((button) => {
        button.onclick = () => {
            openCollectionItemModal(button.dataset.editCollection, Number(button.dataset.index));
        };
    });

    document.querySelectorAll('[data-add-collection]').forEach((button) => {
        button.onclick = () => {
            openCollectionItemModal(button.dataset.addCollection);
        };
    });

    document.querySelectorAll('[data-edit-type="text"]').forEach((el) => {
        el.contentEditable = editMode ? 'true' : 'false';
    });
}

function renderCollections() {
    const gallery = getByPath(contentState, 'tabs.obras.items', []);
    const market = getByPath(contentState, 'tabs.mercado.items', []);

    const galleryContainer = document.getElementById('galleryCollection');
    if (galleryContainer) {
        galleryContainer.innerHTML = gallery.map((item, index) => renderCollectionItem(item, index, 'tabs.obras.items')).join('');
    }

    const marketContainer = document.getElementById('marketCollection');
    if (marketContainer) {
        marketContainer.innerHTML = market.map((item, index) => renderCollectionItem(item, index, 'tabs.mercado.items')).join('');
    }

    bindEditInteractions();
}

if (isAuthenticated) {
    const toggleBtn = document.getElementById('toggleEditBtn');
    const saveBtn = document.getElementById('saveContentBtn');
    renderCollections();

    toggleBtn.addEventListener('click', () => {
        editMode = !editMode;
        document.body.classList.toggle('edit-mode', editMode);
        saveBtn.classList.toggle('hidden', !editMode);
        toggleBtn.textContent = editMode ? '✅ Modo edición activo' : '✏️ Editar';
        document.querySelectorAll('[data-edit-type="text"]').forEach((el) => {
            el.contentEditable = editMode ? 'true' : 'false';
        });
    });

    saveBtn.addEventListener('click', async () => {
        const changed = [];
        let hasError = false;
        document.querySelectorAll('[data-edit-type="text"]').forEach((el) => {
            const key = el.dataset.editKey;
            const value = normalizeEditableText(el);
            if (!value) {
                fieldMessage(key, 'Este campo no puede quedar vacío.', false);
                hasError = true;
                return;
            }
            setByPath(contentState, key, value);
            changed.push(key);
        });
        if (hasError) return;
        try {
            await persistContent(changed);
        } catch (_) {
            // field messages already set
        }
    });

    document.querySelectorAll('.modal-mode').forEach((button) => {
        button.addEventListener('click', () => {
            switchImageMode(button.dataset.mode);
            if (button.dataset.mode === 'library') {
                const initialImage = isCollectionModalImageEdit()
                    ? document.getElementById('collectionImageInput').value.trim()
                    : (document.querySelector(`[data-edit-key="${currentImageKey}"][data-edit-type="image"]`)?.getAttribute('src') || '');
                loadImageLibrary(initialImage);
            }
        });
    });

    document.getElementById('confirmLibrarySelection').addEventListener('click', async () => {
        if (imageMode !== 'library') return;
        const feedback = document.getElementById('modalFeedback');
        if (!selectedLibraryUrl) {
            feedback.textContent = 'Selecciona una imagen de la biblioteca.';
            feedback.className = 'text-xs text-red-400';
            return;
        }

        if (isCollectionModalImageEdit()) {
            applyImageToCollectionForm(selectedLibraryUrl, 'library');
            closeImageModal();
            return;
        }

        setByPath(contentState, currentImageKey, { source_type: 'library', value: selectedLibraryUrl, alt: '' });
        renderCollections();
        const imageEl = document.querySelector(`[data-edit-key="${currentImageKey}"][data-edit-type="image"]`);
        if (imageEl) imageEl.src = selectedLibraryUrl;
        try {
            await persistContent([currentImageKey]);
            feedback.textContent = 'Imagen actualizada desde biblioteca.';
            feedback.className = 'text-xs text-green-400';
        } catch (e) {
            feedback.textContent = e.message;
            feedback.className = 'text-xs text-red-400';
        }
    });

    document.getElementById('cancelModal').addEventListener('click', closeImageModal);

    document.getElementById('saveModal').addEventListener('click', async () => {
        const feedback = document.getElementById('modalFeedback');
        if (imageMode === 'url') {
            const newUrl = document.getElementById('imageUrlInput').value.trim();
            if (!/^https?:\/\//i.test(newUrl)) {
                feedback.textContent = 'Ingresa una URL válida (http/https).';
                feedback.className = 'text-xs text-red-400';
                return;
            }

            if (isCollectionModalImageEdit()) {
                applyImageToCollectionForm(newUrl, 'url');
                closeImageModal();
                return;
            }

            setByPath(contentState, currentImageKey, { source_type: 'url', value: newUrl, alt: '' });
            renderCollections();
            try {
                await persistContent([currentImageKey]);
                feedback.textContent = 'Imagen actualizada.';
                feedback.className = 'text-xs text-green-400';
            } catch (e) {
                feedback.textContent = e.message;
                feedback.className = 'text-xs text-red-400';
            }
            return;
        }

        if (imageMode === 'library') {
            document.getElementById('confirmLibrarySelection').click();
            return;
        }

        const file = document.getElementById('imageFileInput').files[0];
        if (!file) {
            feedback.textContent = 'Selecciona un archivo.';
            feedback.className = 'text-xs text-red-400';
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            feedback.textContent = 'El archivo supera 5MB.';
            feedback.className = 'text-xs text-red-400';
            return;
        }

        const form = new FormData();
        if (!isCollectionModalImageEdit()) {
            form.append('key', currentImageKey);
        }
        form.append('image', file);

        const response = await fetch(window.ADMIN_EDITOR_ENDPOINTS.uploadImage, { method: 'POST', body: form });
        const result = await response.json();
        if (!response.ok || !result.ok) {
            feedback.textContent = result.error || 'No se pudo subir la imagen.';
            feedback.className = 'text-xs text-red-400';
            return;
        }

        if (isCollectionModalImageEdit()) {
            applyImageToCollectionForm(result.url, 'upload');
            closeImageModal();
            return;
        }

        setByPath(contentState, currentImageKey, { source_type: 'upload', value: result.url, alt: '' });
        renderCollections();
        fieldMessage(currentImageKey, 'Imagen guardada', true);
        feedback.textContent = 'Imagen subida correctamente.';
        feedback.className = 'text-xs text-green-400';
    });

    document.getElementById('cancelLinkModal').addEventListener('click', () => {
        document.getElementById('linkModal').classList.add('hidden');
        document.getElementById('linkModal').classList.remove('flex');
    });

    document.getElementById('saveLinkModal').addEventListener('click', async () => {
        const feedback = document.getElementById('linkFeedback');
        const newUrl = document.getElementById('linkUrlInput').value.trim();
        if (!/^https?:\/\//i.test(newUrl)) {
            feedback.textContent = 'Ingresa una URL válida (http/https).';
            feedback.className = 'text-xs text-red-400';
            return;
        }
        setByPath(contentState, currentLinkKey, newUrl);
        document.querySelectorAll(`[data-edit-link-key="${currentLinkKey}"]`).forEach((link) => {
            link.setAttribute('href', newUrl);
        });
        try {
            await persistContent([currentLinkKey]);
            feedback.textContent = 'Enlace actualizado.';
            feedback.className = 'text-xs text-green-400';
        } catch (error) {
            feedback.textContent = error.message;
            feedback.className = 'text-xs text-red-400';
        }
    });

    document.getElementById('collectionImageInput').addEventListener('input', (event) => {
        syncCollectionImagePreview(event.currentTarget.value.trim());
    });

    document.getElementById('openCollectionMediaManagerBtn').addEventListener('click', () => {
        openImageModalForCollection('library');
    });

    document.getElementById('clearCollectionImageBtn').addEventListener('click', () => {
        document.getElementById('collectionImageInput').value = '';
        syncCollectionImagePreview('');
        document.getElementById('collectionItemFeedback').textContent = 'Imagen removida del formulario.';
        document.getElementById('collectionItemFeedback').className = 'text-xs text-white/60';
    });

    const collectionModalCloseButtons = ['cancelCollectionItemModal', 'closeCollectionItemModalTop'];
    collectionModalCloseButtons.forEach((buttonId) => {
        document.getElementById(buttonId).addEventListener('click', () => {
            closeCollectionItemModal();
        });
    });

    document.getElementById('saveCollectionItemModal').addEventListener('click', async () => {
        const feedback = document.getElementById('collectionItemFeedback');
        const payload = buildCollectionItemPayload();
        const labels = getCollectionLabels(currentCollectionKey);

        if (!currentCollectionKey) {
            feedback.textContent = 'No se encontró la colección a guardar.';
            feedback.className = 'text-xs text-red-400';
            return;
        }

        if (!payload.title) {
            feedback.textContent = 'El título es obligatorio.';
            feedback.className = 'text-xs text-red-400';
            return;
        }

        if (payload.link_url && !/^https?:\/\//i.test(payload.link_url)) {
            feedback.textContent = 'La URL del enlace debe comenzar con http:// o https://.';
            feedback.className = 'text-xs text-red-400';
            return;
        }

        const items = [...getByPath(contentState, currentCollectionKey, [])];
        if (currentCollectionIndex >= 0) {
            items[currentCollectionIndex] = payload;
        } else {
            items.push(payload);
        }

        setByPath(contentState, currentCollectionKey, items);
        renderCollections();

        try {
            await persistContent([currentCollectionKey]);
            closeCollectionItemModal();
            alert(`El ${labels.singular} se guardó correctamente.`);
        } catch (error) {
            feedback.textContent = error.message || 'No se pudo guardar el elemento.';
            feedback.className = 'text-xs text-red-400';
        }
    });

    bindEditInteractions();
} else {
    renderCollections();
}

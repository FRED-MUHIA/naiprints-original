const BASE_SIZE = { width: 600, height: 700 };
const DESIGNER_CONFIG = window.DESIGNER_CONFIG || {};
const DESIGNER_MOCKUPS = DESIGNER_CONFIG.mockups || {};
const DESIGNER_COLORS = DESIGNER_CONFIG.colors || {};
const DESIGNER_PRODUCTS = DESIGNER_CONFIG.products || {};
const DESIGNER_PRINT_AREAS = DESIGNER_CONFIG.print_areas || {};
const SAVED_DESIGNS_STORAGE_KEY = 'nai_designer_saved_designs';
const LEGACY_HISTORY_STORAGE_KEY = 'nai_designer_project_history';
const CART_STORAGE_KEY = 'nai_cart';
const UPLOAD_STORAGE_KEY = 'nai_designer_uploads';
const HISTORY_TTL_MS = Number.POSITIVE_INFINITY;
const HISTORY_MAX_ENTRIES = 50;
const UPLOAD_MAX_ENTRIES = 40;
const VIEW_EXPORT_SCALE = 3;
const SHOW_GUIDE_HELP = true;
const SHOW_GUIDE_OVERLAY = true;
const SHOW_GUIDE_HANDLE = true;
const GUIDE_TEXT = {
    title: 'DRAG TO MOVE',
    subtitle: 'DESIGN AREA',
};
const GUIDE_TEXT_COLOR = 'rgba(15, 23, 42, 0.55)';
const GUIDE_ACTIVE_FILL = 'rgba(15, 23, 42, 0.03)';

const isNonEmptyString = (value) => typeof value === 'string' && value.trim() !== '';

const NAMED_COLOR_HEX = {
    white: '#ffffff',
    black: '#000000',
    red: '#ff0000',
    orange: '#ffa500',
    yellow: '#ffff00',
    green: '#008000',
    blue: '#0000ff',
    purple: '#800080',
    violet: '#800080',
    pink: '#ffc0cb',
    magenta: '#ff00ff',
    grey: '#808080',
    gray: '#808080',
    navy: '#000080',
    brown: '#a52a2a',
    cream: '#fffdd0',
    ivory: '#fffff0',
    maroon: '#800000',
    teal: '#008080',
    cyan: '#00ffff',
    lime: '#00ff00',
    gold: '#ffd700',
    silver: '#c0c0c0',
};

const normalizeHexColor = (value) => {
    if (!isNonEmptyString(value)) {
        return '';
    }
    const raw = value.trim().toLowerCase();
    if (NAMED_COLOR_HEX[raw]) {
        return NAMED_COLOR_HEX[raw];
    }
    const hex = raw.startsWith('#') ? raw.slice(1) : raw;
    if (/^[0-9a-f]{3}$/.test(hex)) {
        return `#${hex
            .split('')
            .map((char) => `${char}${char}`)
            .join('')}`;
    }
    if (/^[0-9a-f]{6}$/.test(hex)) {
        return `#${hex}`;
    }
    return '';
};

const coerceNumber = (value, fallback = null) => {
    const num = Number(value);
    return Number.isFinite(num) ? num : fallback;
};

const cloneSerializable = (value, fallback = null) => {
    try {
        return JSON.parse(JSON.stringify(value ?? fallback));
    } catch (error) {
        return fallback;
    }
};

const normalizePrintAreas = (input) => {
    if (!input || typeof input !== 'object') {
        return {};
    }
    const normalized = {};
    Object.entries(input).forEach(([productId, views]) => {
        if (!views || typeof views !== 'object') {
            return;
        }
        Object.entries(views).forEach(([viewId, area]) => {
            if (!area || typeof area !== 'object') {
                return;
            }
            const x = coerceNumber(area.x);
            const y = coerceNumber(area.y);
            const w = coerceNumber(area.w);
            const h = coerceNumber(area.h);
            if (x === null || y === null || w === null || h === null || w <= 0 || h <= 0) {
                return;
            }
            if (!normalized[productId]) {
                normalized[productId] = {};
            }
            normalized[productId][viewId] = { x, y, w, h };
        });
    });
    return normalized;
};

const slugifyKey = (value) => {
    if (!isNonEmptyString(value)) {
        return '';
    }
    return value
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
};

const buildColorLookupKeys = (color = {}) => {
    const keys = new Set();

    if (isNonEmptyString(color?.value)) {
        const raw = color.value.trim().toLowerCase();
        keys.add(raw);
        keys.add(raw.replace(/^#/, ''));
        const normalized = normalizeHexColor(raw);
        if (normalized) {
            keys.add(normalized);
            keys.add(normalized.slice(1));
        }
    }

    if (isNonEmptyString(color?.name)) {
        const name = color.name.trim().toLowerCase();
        keys.add(name);
        keys.add(slugifyKey(name));
    }

    return Array.from(keys).filter(Boolean);
};

const pickFirstString = (...values) => {
    for (const value of values) {
        if (isNonEmptyString(value)) {
            return value.trim();
        }
    }
    return '';
};

const resolveFromColorMap = (map, colorKeys) => {
    if (!map || typeof map !== 'object') {
        return '';
    }

    const normalizedColorKeys = colorKeys.map((key) => key.toLowerCase());

    for (const [mapKey, mapValue] of Object.entries(map)) {
        if (!isNonEmptyString(mapValue)) {
            continue;
        }

        const candidates = new Set();
        const rawKey = String(mapKey).trim().toLowerCase();
        candidates.add(rawKey);
        candidates.add(rawKey.replace(/^#/, ''));
        candidates.add(slugifyKey(rawKey));
        const normalizedHex = normalizeHexColor(rawKey);
        if (normalizedHex) {
            candidates.add(normalizedHex);
            candidates.add(normalizedHex.slice(1));
        }

        const hasMatch = normalizedColorKeys.some((colorKey) => candidates.has(colorKey));
        if (hasMatch) {
            return mapValue.trim();
        }
    }

    return '';
};

const resolveMockupImage = (mockupConfig, color) => {
    const colorKeys = buildColorLookupKeys(color);

    if (isNonEmptyString(mockupConfig)) {
        return { url: mockupConfig.trim(), matchedColor: false };
    }

    if (Array.isArray(mockupConfig)) {
        let fallback = '';

        for (const item of mockupConfig) {
            if (isNonEmptyString(item)) {
                fallback ||= item.trim();
                continue;
            }

            if (!item || typeof item !== 'object') {
                continue;
            }

            const itemUrl = pickFirstString(item.url, item.src, item.image, item.default);
            if (!itemUrl) {
                continue;
            }

            const itemKeys = buildColorLookupKeys({
                name: item.color || item.name || item.key,
                value: item.hex || item.value,
            });
            const isMatch = itemKeys.some((key) => colorKeys.includes(key));
            if (isMatch) {
                return { url: itemUrl, matchedColor: true };
            }

            fallback ||= itemUrl;
        }

        return { url: fallback, matchedColor: false };
    }

    if (!mockupConfig || typeof mockupConfig !== 'object') {
        return { url: '', matchedColor: false };
    }

    const colorMapped = resolveFromColorMap(mockupConfig.colors, colorKeys);
    if (colorMapped) {
        return { url: colorMapped, matchedColor: true };
    }

    const directMapped = resolveFromColorMap(mockupConfig, colorKeys);
    if (directMapped) {
        return { url: directMapped, matchedColor: true };
    }

    return {
        url: pickFirstString(mockupConfig.default, mockupConfig.image, mockupConfig.url, mockupConfig.src),
        matchedColor: false,
    };
};

const stripNonCurrentColorFills = (svg) => {
    if (!svg) {
        return '';
    }
    return svg.replace(/fill="(?!currentColor)[^"]*"/g, 'fill="none"');
};

const stripCurrentColorFills = (svg) => {
    if (!svg) {
        return '';
    }
    return svg.replace(/fill="currentColor"/g, 'fill="none"');
};

const getRelativeLuminance = (hex) => {
    const normalized = normalizeHexColor(hex);
    if (!normalized) {
        return null;
    }
    const value = normalized.slice(1);
    const r = parseInt(value.slice(0, 2), 16) / 255;
    const g = parseInt(value.slice(2, 4), 16) / 255;
    const b = parseInt(value.slice(4, 6), 16) / 255;
    const adjust = (channel) =>
        channel <= 0.03928 ? channel / 12.92 : Math.pow((channel + 0.055) / 1.055, 2.4);
    const [rs, gs, bs] = [adjust(r), adjust(g), adjust(b)];
    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
};

const isLightColor = (hex) => {
    const luminance = getRelativeLuminance(hex);
    if (luminance === null) {
        return false;
    }
    return luminance > 0.72;
};

const applySvgColor = (svg, color) => {
    if (!svg || !color) {
        return svg;
    }
    if (/<svg\b[^>]*\bstyle="/i.test(svg)) {
        return svg.replace(
            /<svg\b([^>]*?)\bstyle="([^"]*)"/i,
            (_, before, style) => `<svg${before}style="${style}; color: ${color};"`
        );
    }
    return svg.replace(/<svg\b([^>]*)>/i, (_, attrs) => `<svg${attrs} style="color: ${color};">`);
};

const parseColorToken = (token) => {
    if (!isNonEmptyString(token)) {
        return null;
    }
    const raw = token.trim();
    const splitMatch = raw.match(/[:|]/);
    if (splitMatch) {
        const splitIndex = raw.indexOf(splitMatch[0]);
        const name = raw.slice(0, splitIndex).trim();
        const value = raw.slice(splitIndex + 1).trim();
        if (!value) {
            return null;
        }
        const normalized = normalizeHexColor(value);
        return normalized ? { name: name || value, value: normalized } : null;
    }

    const hexMatch = raw.match(/#?[0-9a-fA-F]{3,6}/);
    if (hexMatch) {
        const value = normalizeHexColor(hexMatch[0]);
        const name = raw.replace(hexMatch[0], '').trim();
        return { name: name || value, value };
    }

    const named = normalizeHexColor(raw);
    return named ? { name: raw, value: named } : null;
};

const normalizeColorInput = (input) => {
    if (!input) {
        return [];
    }
    if (Array.isArray(input)) {
        return input
            .map((entry) => {
                if (isNonEmptyString(entry)) {
                    return parseColorToken(entry);
                }
                if (entry && typeof entry === 'object') {
                    const name = entry.name || entry.label || entry.title || entry.key;
                    const value = normalizeHexColor(entry.value || entry.hex || entry.color || name || '');
                    return value ? { name: name || value, value } : null;
                }
                return null;
            })
            .filter(Boolean);
    }
    if (typeof input === 'object') {
        return Object.entries(input)
            .map(([key, value]) => {
                const normalized = normalizeHexColor(value || key);
                return normalized ? { name: key, value: normalized } : null;
            })
            .filter(Boolean);
    }
    if (typeof input === 'string') {
        return input
            .split(/[\n,]+/)
            .map((entry) => parseColorToken(entry))
            .filter(Boolean);
    }
    return [];
};

const getProductColors = (productId, fallback) => {
    const override = normalizeColorInput(DESIGNER_COLORS?.[productId]);
    return override.length ? override : fallback;
};

const normalizeProductOverrides = (input) => {
    if (!input) {
        return [];
    }
    if (Array.isArray(input)) {
        return input
            .map((entry) => {
                if (!entry || typeof entry !== 'object') {
                    return null;
                }
                const id = entry.id || entry.key;
                if (!id) {
                    return null;
                }
                return {
                    id,
                    name: entry.name || entry.label || entry.title || '',
                    icon: entry.icon || '',
                };
            })
            .filter(Boolean);
    }
    if (typeof input === 'object') {
        return Object.entries(input)
            .map(([id, value]) => {
                if (!value || typeof value !== 'object') {
                    return { id, name: String(value || ''), icon: '' };
                }
                return {
                    id,
                    name: value.name || value.label || value.title || '',
                    icon: value.icon || '',
                };
            })
            .filter((entry) => entry.id);
    }
    return [];
};

const applyPrintAreaOverrides = (products, overrides) => {
    if (!overrides || typeof overrides !== 'object') {
        return products;
    }
    return products.map((product) => {
        const productOverrides = overrides[product.id];
        if (!productOverrides) {
            return product;
        }
        const views = product.views.map((view) => {
            const override = productOverrides[view.id];
            if (!override) {
                return view;
            }
            return {
                ...view,
                printArea: {
                    ...view.printArea,
                    ...override,
                },
            };
        });
        return {
            ...product,
            views,
        };
    });
};

const buildProductList = (baseProducts, overrides) => {
    const overrideList = normalizeProductOverrides(overrides);
    if (!overrideList.length) {
        return baseProducts;
    }
    const baseMap = new Map(baseProducts.map((product) => [product.id, product]));
    const merged = [];
    const used = new Set();

    overrideList.forEach((override) => {
        const base = baseMap.get(override.id);
        if (!base) {
            return;
        }
        merged.push({
            ...base,
            name: override.name || base.name,
            icon: override.icon || base.icon,
        });
        used.add(override.id);
    });

    baseProducts.forEach((product) => {
        if (!used.has(product.id)) {
            merged.push(product);
        }
    });

    return merged;
};

const isIconImage = (icon) => {
    if (!isNonEmptyString(icon)) {
        return false;
    }
    return /^(https?:\/\/|\/|\.\/|\.\.\/)/.test(icon) || /\.(png|jpe?g|svg|webp|gif)$/i.test(icon);
};

const buildSvg = {
    tshirt: (variant) => `
        <svg viewBox="0 0 600 700" xmlns="http://www.w3.org/2000/svg">
            <path fill="currentColor" d="M120 140l70-50h220l70 50 70 40-40 70-60-30v380a40 40 0 0 1-40 40H160a40 40 0 0 1-40-40V220l-60 30-40-70 70-40z"/>
            <path fill="rgba(255,255,255,0.18)" d="M185 150h230l55 35-25 45-48-22v350H203V208l-48 22-25-45 55-35z"/>
            <circle cx="300" cy="135" r="35" fill="rgba(0,0,0,0.15)"/>
        </svg>`,
    polo: () => `
        <svg viewBox="0 0 600 700" xmlns="http://www.w3.org/2000/svg">
            <path fill="currentColor" d="M140 160l60-60h200l60 60 80 40-40 70-60-28v370a40 40 0 0 1-40 40H200a40 40 0 0 1-40-40V242l-60 28-40-70 80-40z"/>
            <path fill="rgba(255,255,255,0.18)" d="M230 120h140l20 40-40 30h-100l-40-30 20-40z"/>
            <rect x="275" y="170" width="50" height="70" rx="10" fill="rgba(0,0,0,0.12)"/>
        </svg>`,
    hoodie: () => `
        <svg viewBox="0 0 600 700" xmlns="http://www.w3.org/2000/svg">
            <path fill="currentColor" d="M150 200l60-80h180l60 80 80 40-40 80-50-20v330a40 40 0 0 1-40 40H200a40 40 0 0 1-40-40V300l-50 20-40-80 80-40z"/>
            <path fill="rgba(0,0,0,0.15)" d="M250 140h100l40 60-40 30h-100l-40-30 40-60z"/>
            <rect x="235" y="420" width="130" height="80" rx="32" fill="rgba(255,255,255,0.2)"/>
        </svg>`,
    cap: () => `
        <svg viewBox="0 0 600 700" xmlns="http://www.w3.org/2000/svg">
            <path fill="currentColor" d="M140 420c20-140 140-210 260-180 90 20 160 90 160 180H140z"/>
            <path fill="rgba(255,255,255,0.2)" d="M190 420c10-90 90-150 190-130 70 15 120 60 120 130H190z"/>
            <path fill="rgba(0,0,0,0.2)" d="M80 430h440c20 0 40 20 40 40v20H40v-20c0-20 20-40 40-40z"/>
        </svg>`,
    mug: () => `
        <svg viewBox="0 0 600 700" xmlns="http://www.w3.org/2000/svg">
            <rect x="160" y="170" width="250" height="330" rx="30" fill="currentColor"/>
            <rect x="190" y="200" width="190" height="270" rx="20" fill="rgba(255,255,255,0.18)"/>
            <path fill="currentColor" d="M410 230h40c50 0 90 40 90 90s-40 90-90 90h-40v-60h40c17 0 30-13 30-30s-13-30-30-30h-40v-60z"/>
            <rect x="150" y="520" width="270" height="30" rx="15" fill="rgba(0,0,0,0.18)"/>
        </svg>`,
    pillow: () => `
        <svg viewBox="0 0 600 700" xmlns="http://www.w3.org/2000/svg">
            <rect x="120" y="160" width="360" height="380" rx="80" fill="currentColor"/>
            <rect x="160" y="200" width="280" height="300" rx="60" fill="rgba(255,255,255,0.15)"/>
        </svg>`,
};

const productCatalog = [
    {
        id: 'tshirt',
        name: 'T-Shirt',
        icon: '👕',
        colors: getProductColors('tshirt', [
            { name: 'White', value: '#f8fafc' },
            { name: 'Black', value: '#0f172a' },
            { name: 'Navy', value: '#1e293b' },
            { name: 'Red', value: '#ef4444' },
        ]),
        views: [
            { id: 'front', label: 'Front', printArea: { x: 157, y: 175, w: 260, h: 280 } },
            { id: 'back', label: 'Back', printArea: { x: 145, y: 146, w: 260, h: 300 } },
        ],
        svg: buildSvg.tshirt,
        mockups: DESIGNER_MOCKUPS.tshirt || {},
    },
    {
        id: 'polo',
        name: 'Polo Shirt',
        icon: '🧥',
        colors: getProductColors('polo', [
            { name: 'Sand', value: '#f3e8e0' },
            { name: 'Forest', value: '#14532d' },
            { name: 'Ink', value: '#0f172a' },
        ]),
        views: [
            { id: 'front', label: 'Front', printArea: { x: 185, y: 240, w: 230, h: 260 } },
            { id: 'back', label: 'Back', printArea: { x: 185, y: 230, w: 230, h: 280 } },
        ],
        svg: buildSvg.polo,
        mockups: DESIGNER_MOCKUPS.polo || {},
    },
    {
        id: 'hoodie',
        name: 'Hoodie',
        icon: '🧢',
        colors: getProductColors('hoodie', [
            { name: 'Sky', value: '#93c5fd' },
            { name: 'Charcoal', value: '#111827' },
            { name: 'Cream', value: '#fef3c7' },
        ]),
        views: [
            { id: 'front', label: 'Front', printArea: { x: 180, y: 260, w: 240, h: 260 } },
            { id: 'back', label: 'Back', printArea: { x: 180, y: 250, w: 240, h: 280 } },
        ],
        svg: buildSvg.hoodie,
        mockups: DESIGNER_MOCKUPS.hoodie || {},
    },
    {
        id: 'cap',
        name: 'Cap',
        icon: '🧢',
        colors: getProductColors('cap', [
            { name: 'Black', value: '#0b1026' },
            { name: 'Stone', value: '#e2e8f0' },
            { name: 'Olive', value: '#4d7c0f' },
        ]),
        views: [
            { id: 'front', label: 'Front', printArea: { x: 200, y: 310, w: 200, h: 130 } },
        ],
        svg: buildSvg.cap,
        mockups: DESIGNER_MOCKUPS.cap || {},
    },
    {
        id: 'mug',
        name: 'Cup / Mug',
        icon: '☕',
        colors: getProductColors('mug', [
            { name: 'White', value: '#f8fafc' },
            { name: 'Slate', value: '#334155' },
            { name: 'Amber', value: '#f59e0b' },
        ]),
        views: [
            { id: 'front', label: 'Front', printArea: { x: 190, y: 240, w: 190, h: 200 } },
        ],
        svg: buildSvg.mug,
        mockups: DESIGNER_MOCKUPS.mug || {},
    },
    {
        id: 'pillow',
        name: 'Pillow',
        icon: '🛏️',
        colors: getProductColors('pillow', [
            { name: 'Ivory', value: '#fffbeb' },
            { name: 'Rose', value: '#fbcfe8' },
            { name: 'Ink', value: '#1f2937' },
        ]),
        views: [
            { id: 'front', label: 'Front', printArea: { x: 180, y: 230, w: 240, h: 240 } },
            { id: 'back', label: 'Back', printArea: { x: 180, y: 230, w: 240, h: 240 } },
        ],
        svg: buildSvg.pillow,
        mockups: DESIGNER_MOCKUPS.pillow || {},
    },
];

const mountDesigner = () => {
    const { createApp, ref, reactive, computed, onMounted, onBeforeUnmount, watch } = window.Vue;

    createApp({
        setup() {
        const printAreaOverrides = normalizePrintAreas(DESIGNER_PRINT_AREAS);
        const products = ref(
            applyPrintAreaOverrides(buildProductList(productCatalog, DESIGNER_PRODUCTS), printAreaOverrides)
        );
        const selectedProductId = ref(products.value[0].id);
        const selectedViewId = ref(products.value[0].views[0].id);
        const selectedColor = ref(products.value[0].colors[0]);
        const textValue = ref('Your Text');
        const fonts = ref(['Albert Sans', 'Bebas Neue', 'Montserrat', 'Playfair Display', 'Pacifico']);
        const selectedFont = ref(fonts.value[0]);

        const canvasRef = ref(null);
        const blendCanvasRef = ref(null);
        const stageRef = ref(null);
        const menuUploadRef = ref(null);
        const zoom = ref(1.02);
        const layers = ref([]);
        const activeLayerId = ref(null);
        const activeObject = ref(null);
        const activeProps = reactive({ opacity: 1, rotation: 0 });
        const isEditingArea = ref(true);
        const customPrintAreas = reactive({});
        const openMenu = ref('');
        const activeRail = ref('Products');
        const projectHistory = ref([]);
        const uploads = ref([]);
        const designerNotice = ref('');
        const mobilePanelOpen = ref(false);
        let historyRefreshTimer = null;
        let designerNoticeTimer = null;
        const viewDesigns = reactive({});

        const toolItems = [
            { id: 'upload', label: 'Upload', icon: '^' },
            { id: 'text', label: 'Text', icon: 'T' },
            { id: 'rect', label: 'Rectangle', icon: '[]' },
            { id: 'circle', label: 'Circle', icon: '()' },
            { id: 'line', label: 'Line', icon: '/' },
        ];
        const selectedToolId = ref(toolItems[0].id);
        const textSize = ref(36);
        const textColor = ref('#111827');
        const shapeFill = ref('#22c55e');
        const shapeStroke = ref('#111827');
        const shapeStrokeWidth = ref(2);
        const lineStroke = ref('#2563eb');
        const lineStrokeWidth = ref(4);
        const photoBounds = ref(null);
        let photoLoadId = 0;

        let canvas = null;
        let guideRect = null;
        let clipRect = null;
        let guideLabel = null;
        let guideHandle = null;
        const history = ref([]);
        const historyIndex = ref(-1);
        let isRestoring = false;
        let clipboard = null;
        let pasteOffset = 20;
        const areaInputs = reactive({ x: 0, y: 0, w: 0, h: 0 });

        const currentProduct = computed(() =>
            products.value.find((product) => product.id === selectedProductId.value)
        );
        const currentView = computed(() =>
            currentProduct.value.views.find((view) => view.id === selectedViewId.value)
        );
        const resolvedMockupImage = computed(() =>
            resolveMockupImage(currentProduct.value.mockups?.[currentView.value.id], selectedColor.value)
        );
        const currentMockupImage = computed(() => resolvedMockupImage.value.url || '');
        const hasPhotoMockup = computed(() => Boolean(currentMockupImage.value));
        const currentMockup = computed(() => currentProduct.value.svg(currentView.value.id));
        const currentMockupBase = computed(() => stripNonCurrentColorFills(currentMockup.value));
        const currentMockupOverlay = computed(() => stripCurrentColorFills(currentMockup.value));

        const canUndo = computed(() => historyIndex.value > 0);
        const canRedo = computed(() => historyIndex.value < history.value.length - 1);

        const readStoredHistory = () => {
            try {
                const raw =
                    localStorage.getItem(SAVED_DESIGNS_STORAGE_KEY) ||
                    localStorage.getItem(LEGACY_HISTORY_STORAGE_KEY);
                if (!raw) return [];
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                return [];
            }
        };

        const readStoredUploads = () => {
            try {
                const raw = localStorage.getItem(UPLOAD_STORAGE_KEY);
                if (!raw) {
                    return [];
                }
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                return [];
            }
        };

        const pruneHistory = (entries) => {
            const now = Date.now();
            return entries
                .filter((entry) => entry && (!entry.timestamp || now - entry.timestamp <= HISTORY_TTL_MS))
                .slice(0, HISTORY_MAX_ENTRIES);
        };

        const writeStoredHistory = (entries) => {
            try {
                localStorage.setItem(SAVED_DESIGNS_STORAGE_KEY, JSON.stringify(entries));
            } catch (error) {
                // Ignore storage errors.
            }
        };

        const writeStoredUploads = (entries) => {
            try {
                localStorage.setItem(UPLOAD_STORAGE_KEY, JSON.stringify(entries));
            } catch (error) {
                // Ignore storage errors.
            }
        };

        const refreshProjectHistory = () => {
            const pruned = pruneHistory(readStoredHistory());
            projectHistory.value = pruned;
            writeStoredHistory(pruned);
        };

        const refreshUploads = () => {
            uploads.value = readStoredUploads();
        };

        const formatHistoryTime = (timestamp) => {
            if (!timestamp) {
                return '';
            }
            return new Date(timestamp).toLocaleString();
        };

        const formatHistoryExpiry = (timestamp) => {
            if (!timestamp) {
                return '';
            }
            if (!Number.isFinite(HISTORY_TTL_MS)) {
                return 'Saved';
            }
            const remaining = timestamp + HISTORY_TTL_MS - Date.now();
            if (remaining <= 0) {
                return 'Expired';
            }
            const hours = Math.max(1, Math.ceil(remaining / (60 * 60 * 1000)));
            return `Expires in ${hours}h`;
        };

        const showDesignerNotice = (message) => {
            designerNotice.value = message;
            if (designerNoticeTimer) {
                clearTimeout(designerNoticeTimer);
            }
            designerNoticeTimer = window.setTimeout(() => {
                designerNotice.value = '';
            }, 2600);
        };

        const getEntrySignature = (entry) =>
            JSON.stringify({
                productId: entry?.productId || '',
                colorValue: entry?.colorValue || '',
                viewDesigns: entry?.viewDesigns || (entry?.snapshot ? { [entry.viewId || 'front']: entry.snapshot } : {}),
                printAreas: entry?.printAreas || {},
            });

        const saveProjectSnapshot = async () => {
            if (!canvas || !currentProduct.value || !currentView.value) {
                return null;
            }
            const entry = await buildSavedDesignEntry();
            if (!entry) {
                return null;
            }

            const existing = pruneHistory(readStoredHistory());
            const latest = existing[0];
            if (latest && getEntrySignature(latest) === getEntrySignature(entry)) {
                showDesignerNotice('Design already saved.');
                return latest;
            }

            const nextEntries = [entry, ...existing].slice(0, HISTORY_MAX_ENTRIES);
            writeStoredHistory(nextEntries);
            projectHistory.value = nextEntries;
            showDesignerNotice('Design saved.');
            return entry;
        };


        const isTypingTarget = (target) => {
            if (!target) {
                return false;
            }
            const tag = target.tagName?.toLowerCase?.() || '';
            if (['input', 'textarea', 'select'].includes(tag)) {
                return true;
            }
            return Boolean(target.isContentEditable);
        };

        const getActiveEditableObject = () => {
            if (!canvas) {
                return null;
            }
            const active = canvas.getActiveObject();
            if (!active || active.isGuide) {
                return null;
            }
            return active;
        };

        const copySelection = () => {
            if (!canvas) {
                return;
            }
            const active = canvas.getActiveObject();
            if (!active || active.isGuide) {
                return;
            }
            active.clone((cloned) => {
                clipboard = cloned;
            }, ['dataId', 'name', 'locked', 'isGuide']);
        };

        const pasteSelection = () => {
            if (!canvas || !clipboard) {
                return;
            }
            clipboard.clone((cloned) => {
                canvas.discardActiveObject();
                const offset = pasteOffset;
                pasteOffset = Math.min(pasteOffset + 10, 60);

                if (cloned.type === 'activeSelection') {
                    cloned.canvas = canvas;
                    cloned.forEachObject((obj, index) => {
                        obj.set({
                            left: (obj.left || 0) + offset,
                            top: (obj.top || 0) + offset,
                        });
                        obj.dataId = `copy-${Date.now()}-${index}`;
                        canvas.add(obj);
                    });
                    const selection = new window.fabric.ActiveSelection(cloned.getObjects(), { canvas });
                    canvas.setActiveObject(selection);
                } else {
                    cloned.set({
                        left: (cloned.left || 0) + offset,
                        top: (cloned.top || 0) + offset,
                    });
                    cloned.dataId = `copy-${Date.now()}`;
                    canvas.add(cloned);
                    canvas.setActiveObject(cloned);
                }

                canvas.requestRenderAll();
                pushHistory();
                syncLayers();
            }, ['dataId', 'name', 'locked', 'isGuide']);
        };

        const cutSelection = () => {
            if (!canvas) {
                return;
            }
            const active = canvas.getActiveObject();
            if (!active || active.isGuide) {
                return;
            }
            copySelection();
            if (active.type === 'activeSelection') {
                active.forEachObject((obj) => {
                    canvas.remove(obj);
                });
            } else {
                canvas.remove(active);
            }
            canvas.discardActiveObject();
            canvas.requestRenderAll();
            pushHistory();
            syncLayers();
        };

        const deleteSelection = () => {
            if (!canvas) {
                return;
            }
            const active = canvas.getActiveObject();
            if (!active || active.isGuide) {
                return;
            }
            if (active.type === 'activeSelection') {
                active.forEachObject((obj) => {
                    canvas.remove(obj);
                });
            } else {
                canvas.remove(active);
            }
            canvas.discardActiveObject();
            canvas.requestRenderAll();
            pushHistory();
            syncLayers();
        };

        const nudgeSelection = (dx, dy) => {
            const active = getActiveEditableObject();
            if (!active) {
                return;
            }
            active.set({
                left: (active.left || 0) + dx,
                top: (active.top || 0) + dy,
            });
            active.setCoords?.();
            canvas.requestRenderAll();
            pushHistory();
            syncLayers();
        };

        const rotateSelection = (delta) => {
            const active = getActiveEditableObject();
            if (!active) {
                return;
            }
            active.rotate((active.angle || 0) + delta);
            active.setCoords?.();
            canvas.requestRenderAll();
            pushHistory();
            syncLayers();
        };

        const scaleSelection = (factor) => {
            const active = getActiveEditableObject();
            if (!active) {
                return;
            }
            const nextScaleX = Math.max(0.05, (active.scaleX || 1) * factor);
            const nextScaleY = Math.max(0.05, (active.scaleY || 1) * factor);
            active.set({
                scaleX: nextScaleX,
                scaleY: nextScaleY,
            });
            active.setCoords?.();
            canvas.requestRenderAll();
            pushHistory();
            syncLayers();
        };

        const moveLayer = (direction) => {
            const active = getActiveEditableObject();
            if (!active) {
                return;
            }
            if (direction === 'forward') {
                canvas.bringForward(active);
            } else if (direction === 'backward') {
                canvas.sendBackwards(active);
            } else if (direction === 'front') {
                canvas.bringToFront(active);
            } else if (direction === 'back') {
                canvas.sendToBack(active);
            }
            canvas.requestRenderAll();
            pushHistory();
            syncLayers();
        };

        const selectRail = (label) => {
            activeRail.value = label;
            if (window.matchMedia?.('(max-width: 640px)')?.matches) {
                mobilePanelOpen.value = true;
            }
            if (label === 'Projects') {
                refreshProjectHistory();
            }
            if (label === 'Uploads') {
                refreshUploads();
            }
        };

        const closeMobilePanel = () => {
            mobilePanelOpen.value = false;
        };

        const selectTool = (toolId) => {
            selectedToolId.value = toolId;
        };

        const triggerMenuUpload = () => {
            if (menuUploadRef.value) {
                menuUploadRef.value.click();
            }
        };

        const deleteHistoryEntry = (entryId) => {
            const nextEntries = readStoredHistory().filter((entry) => entry?.id !== entryId);
            writeStoredHistory(nextEntries);
            projectHistory.value = pruneHistory(nextEntries);
        };

        const loadHistoryEntry = async (entryId) => {
            const entries = pruneHistory(readStoredHistory());
            const entry = entries.find((item) => item?.id === entryId);
            if (!entry) {
                refreshProjectHistory();
                return;
            }

            const product = products.value.find((item) => item.id === entry.productId) || products.value[0];
            selectedProductId.value = product.id;
            selectedViewId.value =
                product.views.find((view) => view.id === entry.viewId)?.id || product.views[0]?.id;
            const colorMatch = product.colors.find(
                (color) => color.value?.toLowerCase() === entry.colorValue?.toLowerCase()
            );
            selectedColor.value = colorMatch || product.colors[0];

            if (!canvas) {
                return;
            }

            const savedViews =
                cloneSerializable(entry.viewDesigns, null) ||
                (entry.snapshot ? { [entry.viewId || selectedViewId.value]: entry.snapshot } : {});
            Object.keys(viewDesigns).forEach((key) => delete viewDesigns[key]);
            viewDesigns[product.id] = savedViews;

            Object.keys(customPrintAreas).forEach((key) => delete customPrintAreas[key]);
            if (entry.printAreas && typeof entry.printAreas === 'object') {
                customPrintAreas[product.id] = cloneSerializable(entry.printAreas, {});
            }

            await refreshPhotoBounds();
            await loadViewDesign(product.id, selectedViewId.value);
            showDesignerNotice('Design loaded.');
        };

        const syncLayers = () => {
            if (!canvas) {
                layers.value = [];
                return;
            }
            const items = canvas
                .getObjects()
                .filter((obj) => !obj.isGuide)
                .map((obj) => ({
                    id: obj.dataId,
                    name: obj.name || obj.type,
                    locked: !obj.selectable || obj.locked,
                }))
                .reverse();
            layers.value = items;
        };

        const updateActiveObject = (obj) => {
            activeObject.value = obj || null;
            activeLayerId.value = obj?.dataId || null;
            if (obj) {
                activeProps.opacity = obj.opacity ?? 1;
                activeProps.rotation = obj.angle ?? 0;
            }
        };

        const pushHistory = () => {
            if (!canvas || isRestoring) {
                return;
            }
            const json = canvas.toJSON(['dataId', 'name', 'locked', 'isGuide']);
            const snapshot = history.value.slice(0, historyIndex.value + 1);
            snapshot.push(json);
            history.value = snapshot;
            historyIndex.value = history.value.length - 1;
        };

        const restoreHistory = (index) => {
            if (!canvas || !history.value[index]) {
                return;
            }
            isRestoring = true;
            canvas.loadFromJSON(history.value[index], () => {
                canvas.getObjects().forEach((obj) => {
                    if (obj.isGuide) {
                        obj.selectable = false;
                        obj.evented = false;
                        obj.excludeFromExport = true;
                    }
                });
                applyPrintArea();
                canvas.requestRenderAll();
                syncLayers();
                updateActiveObject(canvas.getActiveObject());
                isRestoring = false;
            });
        };

        const undo = () => {
            if (!canUndo.value) {
                return;
            }
            historyIndex.value -= 1;
            restoreHistory(historyIndex.value);
        };

        const redo = () => {
            if (!canRedo.value) {
                return;
            }
            historyIndex.value += 1;
            restoreHistory(historyIndex.value);
        };

        const getCurrentPrintArea = () => {
            const productAreas = customPrintAreas[selectedProductId.value];
            const saved = productAreas?.[selectedViewId.value];
            if (saved) {
                return saved;
            }
            return currentView.value.printArea;
        };

        const getPhotoBounds = () => photoBounds.value;

        const isUsingCustomArea = () => {
            const productAreas = customPrintAreas[selectedProductId.value] || {};
            return Boolean(productAreas[selectedViewId.value]);
        };

        const mapAreaToPhoto = (area) => {
            const bounds = getPhotoBounds();
            if (!bounds || !area) {
                return area;
            }
            if (bounds.w <= 0 || bounds.h <= 0) {
                return area;
            }
            return {
                x: bounds.x + (area.x / BASE_SIZE.width) * bounds.w,
                y: bounds.y + (area.y / BASE_SIZE.height) * bounds.h,
                w: (area.w / BASE_SIZE.width) * bounds.w,
                h: (area.h / BASE_SIZE.height) * bounds.h,
            };
        };

        const mapAreaFromPhoto = (area) => {
            const bounds = getPhotoBounds();
            if (!bounds || !area) {
                return area;
            }
            if (bounds.w <= 0 || bounds.h <= 0) {
                return area;
            }
            return {
                x: ((area.x - bounds.x) / bounds.w) * BASE_SIZE.width,
                y: ((area.y - bounds.y) / bounds.h) * BASE_SIZE.height,
                w: (area.w / bounds.w) * BASE_SIZE.width,
                h: (area.h / bounds.h) * BASE_SIZE.height,
            };
        };

        const getDisplayPrintArea = (baseArea) => {
            if (!baseArea) {
                return baseArea;
            }
            const bounds = getPhotoBounds();
            if (!hasPhotoMockup.value || !bounds) {
                if (!isUsingCustomArea()) {
                    return {
                        x: (BASE_SIZE.width - baseArea.w) / 2,
                        y: (BASE_SIZE.height - baseArea.h) / 2,
                        w: baseArea.w,
                        h: baseArea.h,
                    };
                }
                return baseArea;
            }
            if (!isUsingCustomArea()) {
                const width = (baseArea.w / BASE_SIZE.width) * bounds.w;
                const height = (baseArea.h / BASE_SIZE.height) * bounds.h;
                return {
                    x: bounds.x + (bounds.w - width) / 2,
                    y: bounds.y + (bounds.h - height) / 2,
                    w: width,
                    h: height,
                };
            }
            return mapAreaToPhoto(baseArea);
        };

        const getGuideAreaFromRect = () => {
            if (!guideRect) {
                return null;
            }
            const displayArea = {
                x: guideRect.left,
                y: guideRect.top,
                w: guideRect.getScaledWidth(),
                h: guideRect.getScaledHeight(),
            };
            return hasPhotoMockup.value && getPhotoBounds() ? mapAreaFromPhoto(displayArea) : displayArea;
        };

        const setAreaInputs = (area) => {
            if (!area) {
                return;
            }
            areaInputs.x = Math.round(area.x);
            areaInputs.y = Math.round(area.y);
            areaInputs.w = Math.round(area.w);
            areaInputs.h = Math.round(area.h);
        };

        const updateGuideOverlay = (baseArea, displayArea = baseArea) => {
            if (!canvas || !baseArea || !displayArea) {
                return;
            }
            setAreaInputs(baseArea);
            if (!SHOW_GUIDE_HELP) {
                if (guideLabel) {
                    canvas.remove(guideLabel);
                    guideLabel = null;
                }
            } else {
            const guideTextColor = GUIDE_TEXT_COLOR;
            if (!guideLabel) {
                const title = new window.fabric.Text(GUIDE_TEXT.title, {
                    left: 0,
                    top: -10,
                    fontSize: 18,
                    fontWeight: 'bold',
                    fill: guideTextColor,
                    originX: 'center',
                    originY: 'center',
                });
                const subtitle = new window.fabric.Text(GUIDE_TEXT.subtitle, {
                    left: 0,
                    top: 12,
                    fontSize: 12,
                    fontWeight: '600',
                    fill: guideTextColor,
                    originX: 'center',
                    originY: 'center',
                });
                guideLabel = new window.fabric.Group([title, subtitle], {
                    left: displayArea.x + displayArea.w / 2,
                    top: displayArea.y + displayArea.h / 2,
                    selectable: false,
                    evented: false,
                    excludeFromExport: true,
                    originX: 'center',
                    originY: 'center',
                });
                guideLabel.isGuide = true;
                canvas.add(guideLabel);
            } else {
                guideLabel.set({
                    left: displayArea.x + displayArea.w / 2,
                    top: displayArea.y + displayArea.h / 2,
                });
                guideLabel.getObjects().forEach((obj) => {
                    obj.set({ fill: guideTextColor });
                });
            }
            }

            if (!SHOW_GUIDE_HANDLE) {
                if (guideHandle) {
                    canvas.remove(guideHandle);
                    guideHandle = null;
                }
            } else if (!guideHandle) {
                const handleRect = new window.fabric.Rect({
                    width: 32,
                    height: 32,
                    rx: 6,
                    ry: 6,
                    fill: 'rgba(15,23,42,0.75)',
                    selectable: false,
                    evented: false,
                    originX: 'center',
                    originY: 'center',
                });
                const handleText = new window.fabric.Text('↘', {
                    fontSize: 16,
                    fill: '#ffffff',
                    originX: 'center',
                    originY: 'center',
                });
                guideHandle = new window.fabric.Group([handleRect, handleText], {
                    left: displayArea.x + displayArea.w - 18,
                    top: displayArea.y + displayArea.h - 18,
                    selectable: false,
                    evented: false,
                    excludeFromExport: true,
                    originX: 'center',
                    originY: 'center',
                });
                guideHandle.isGuide = true;
                canvas.add(guideHandle);
            } else {
                guideHandle.set({
                    left: displayArea.x + displayArea.w - 18,
                    top: displayArea.y + displayArea.h - 18,
                });
            }

            const activeOpacity = isEditingArea.value ? 1 : 0.6;
            if (guideLabel) {
                guideLabel.set({ visible: true, opacity: activeOpacity });
                guideLabel.bringToFront();
            }
            if (guideHandle) {
                guideHandle.set({ visible: true, opacity: activeOpacity });
                guideHandle.bringToFront();
            }
        };

        const setGuideControlsVisible = (visible) => {
            if (!guideRect) {
                return;
            }
            guideRect.set({
                hasControls: visible,
                cornerColor: visible ? '#ffffff' : 'rgba(255,255,255,0)',
                cornerStrokeColor: visible ? 'rgba(15, 23, 42, 0.35)' : 'rgba(255,255,255,0)',
                borderColor: visible ? 'rgba(255, 255, 255, 0.75)' : 'rgba(255,255,255,0)',
            });
            guideRect.setControlsVisibility({ mtr: false });
            guideRect.setCoords?.();
        };

        const setGuideInteractivity = (enabled) => {
            if (!guideRect) {
                return;
            }
            guideRect.set({
                selectable: enabled,
                evented: enabled,
                hoverCursor: enabled ? 'move' : 'default',
            });
            setGuideControlsVisible(enabled);
            isEditingArea.value = enabled;
        };

        const syncGuideInteractivity = () => {
            if (!canvas || !guideRect) {
                return;
            }
            const hasArtwork = canvas.getObjects().some((obj) => !obj.isGuide);
            setGuideInteractivity(!hasArtwork);
        };

        const serializeCanvas = () => {
            if (!canvas) {
                return null;
            }
            const json = canvas.toJSON(['dataId', 'name', 'locked', 'isGuide']);
            if (json?.objects) {
                json.objects = json.objects.filter((obj) => !obj.isGuide);
            }
            return json;
        };

        const loadCanvasFromJson = (json) =>
            new Promise((resolve) => {
                if (!canvas) {
                    resolve();
                    return;
                }
                isRestoring = true;
                canvas.clear();
                guideRect = null;
                clipRect = null;
                guideLabel = null;
                guideHandle = null;
                if (!json) {
                    applyPrintArea();
                    canvas.requestRenderAll();
                    syncLayers();
                    updateActiveObject(null);
                    isRestoring = false;
                    resolve();
                    return;
                }
                canvas.loadFromJSON(json, () => {
                    canvas.getObjects().forEach((obj) => {
                        if (obj.isGuide) {
                            canvas.remove(obj);
                        }
                    });
                    applyPrintArea();
                    canvas.requestRenderAll();
                    syncLayers();
                    updateActiveObject(null);
                    isRestoring = false;
                    resolve();
                });
            });

        const saveCurrentViewDesign = () => {
            if (!canvas || !currentProduct.value || !currentView.value) {
                return;
            }
            const json = serializeCanvas();
            if (!json) {
                return;
            }
            const productId = selectedProductId.value;
            const viewId = selectedViewId.value;
            if (!viewDesigns[productId]) {
                viewDesigns[productId] = {};
            }
            viewDesigns[productId][viewId] = json;
        };

        const loadViewDesign = async (productId, viewId) => {
            const json = viewDesigns[productId]?.[viewId] || null;
            await loadCanvasFromJson(json);
        };

        const buildSavedDesignEntry = async () => {
            if (!canvas || !currentProduct.value || !currentView.value) {
                return null;
            }

            saveCurrentViewDesign();
            const product = currentProduct.value;
            const view = currentView.value;
            const productDesigns = cloneSerializable(viewDesigns[product.id] || {}, {});
            if (!productDesigns[view.id]) {
                productDesigns[view.id] = serializeCanvas();
            }
            await refreshPhotoBounds();

            return {
                id: `design-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
                timestamp: Date.now(),
                productId: product.id,
                productName: product.name,
                viewId: view.id,
                viewLabel: view.label,
                colorName: selectedColor.value?.name || '',
                colorValue: selectedColor.value?.value || '',
                printAreas: cloneSerializable(customPrintAreas[product.id] || {}, {}),
                viewDesigns: productDesigns,
                preview: await renderCurrentMockupDataUrl('image/jpeg', 0.86),
            };
        };

        const readCart = () => {
            try {
                const parsed = JSON.parse(localStorage.getItem(CART_STORAGE_KEY) || '[]');
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                return [];
            }
        };

        const writeCart = (items) => {
            try {
                localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(items));
                return true;
            } catch (error) {
                showDesignerNotice('Cart storage is full. Try removing older cart items.');
                return false;
            }
        };

        const getSavedEntryViewCount = (entry) =>
            Object.keys(entry?.viewDesigns || {}).filter(
                (viewId) => (entry.viewDesigns?.[viewId]?.objects || []).length > 0
            ).length;

        const buildDesignerCartItem = (entry) => {
            const colorLabel = entry.colorName || entry.colorValue || 'Default color';
            const viewCount = Math.max(1, getSavedEntryViewCount(entry));
            return {
                id: `designer-${entry.id}`,
                name: `Custom ${entry.productName || 'product'} design`,
                price: Number(DESIGNER_CONFIG.design_price || 0),
                qty: 1,
                image: '',
                url: DESIGNER_CONFIG.checkout_url || '/checkout',
                selection: `${entry.viewLabel || 'Design'} view`,
                customNote: `${entry.productName || 'Product'} / ${colorLabel} / ${viewCount} saved view${viewCount === 1 ? '' : 's'}`,
                customImageData: entry.preview || '',
                customImageName: `${entry.productId || 'design'}-${entry.id}.jpg`,
                color: colorLabel,
                designerDesignId: entry.id,
            };
        };

        const mergeDesignerCartItem = (items, entry) => {
            const item = buildDesignerCartItem(entry);
            const existing = items.find((cartItem) => cartItem.id === item.id);
            if (existing) {
                existing.qty = (Number(existing.qty) || 1) + 1;
                return;
            }
            items.push(item);
        };

        const addCurrentDesignToCart = async () => {
            const entry = await saveProjectSnapshot();
            if (!entry) {
                return;
            }

            const cartItems = readCart();
            mergeDesignerCartItem(cartItems, entry);
            if (writeCart(cartItems)) {
                showDesignerNotice('Design added to cart.');
            }
        };

        const addSavedDesignToCart = (entryId) => {
            const entry = pruneHistory(readStoredHistory()).find((item) => item?.id === entryId);
            if (!entry) {
                refreshProjectHistory();
                return;
            }
            const cartItems = readCart();
            mergeDesignerCartItem(cartItems, entry);
            if (writeCart(cartItems)) {
                showDesignerNotice('Saved design added to cart.');
            }
        };

        const addAllSavedDesignsToCart = () => {
            const entries = pruneHistory(readStoredHistory());
            if (!entries.length) {
                showDesignerNotice('No saved designs yet.');
                return;
            }
            const cartItems = readCart();
            entries.forEach((entry) => mergeDesignerCartItem(cartItems, entry));
            if (writeCart(cartItems)) {
                showDesignerNotice(`${entries.length} saved design${entries.length === 1 ? '' : 's'} added to cart.`);
            }
        };

        const constrainGuideRect = (rect) => {
            if (!rect) {
                return;
            }
            const bounds = rect.getBoundingRect(true, true);
            const limit = getPhotoBounds();
            const maxBounds = limit
                ? { left: limit.x, top: limit.y, width: limit.w, height: limit.h }
                : { left: 0, top: 0, width: BASE_SIZE.width, height: BASE_SIZE.height };
            let left = rect.left;
            let top = rect.top;
            if (bounds.left < maxBounds.left) {
                left += maxBounds.left - bounds.left;
            }
            if (bounds.top < maxBounds.top) {
                top += maxBounds.top - bounds.top;
            }
            if (bounds.left + bounds.width > maxBounds.left + maxBounds.width) {
                left -= bounds.left + bounds.width - (maxBounds.left + maxBounds.width);
            }
            if (bounds.top + bounds.height > maxBounds.top + maxBounds.height) {
                top -= bounds.top + bounds.height - (maxBounds.top + maxBounds.height);
            }
            rect.set({ left, top });
        };

        const commitGuideRect = () => {
            if (!guideRect || !currentView.value) {
                return;
            }
            const width = guideRect.getScaledWidth();
            const height = guideRect.getScaledHeight();
            guideRect.set({
                width,
                height,
                scaleX: 1,
                scaleY: 1,
            });
            guideRect.setCoords();

            const displayArea = {
                x: guideRect.left,
                y: guideRect.top,
                w: width,
                h: height,
            };
            const baseArea = hasPhotoMockup.value && getPhotoBounds()
                ? mapAreaFromPhoto(displayArea)
                : displayArea;

            const productAreas = customPrintAreas[selectedProductId.value] || {};
            customPrintAreas[selectedProductId.value] = {
                ...productAreas,
                [selectedViewId.value]: baseArea,
            };

            clipRect?.set({
                left: displayArea.x,
                top: displayArea.y,
                width: displayArea.w,
                height: displayArea.h,
            });
            canvas.requestRenderAll();
            updateGuideOverlay(baseArea, displayArea);
        };

        const applyPrintArea = () => {
            if (!canvas || !currentView.value) {
                return;
            }
            const baseArea = getCurrentPrintArea();
            setAreaInputs(baseArea);
            const area = getDisplayPrintArea(baseArea);
            const baseStroke = isLightColor(selectedColor.value?.value)
                ? 'rgba(15, 23, 42, 0.4)'
                : '#ffffff';
            const guideStyle = {
                fill: GUIDE_ACTIVE_FILL,
                stroke: baseStroke,
                strokeWidth: 1,
                strokeDashArray: [3, 3],
            };
            if (!clipRect) {
                clipRect = new window.fabric.Rect({
                    left: area.x,
                    top: area.y,
                    width: area.w,
                    height: area.h,
                    absolutePositioned: true,
                });
            } else {
                clipRect.set({
                    left: area.x,
                    top: area.y,
                    width: area.w,
                    height: area.h,
                });
            }
            if (!SHOW_GUIDE_OVERLAY) {
                if (guideRect) {
                    canvas.remove(guideRect);
                    guideRect = null;
                }
                if (guideLabel) {
                    canvas.remove(guideLabel);
                    guideLabel = null;
                }
                if (guideHandle) {
                    canvas.remove(guideHandle);
                    guideHandle = null;
                }
                canvas.clipPath = clipRect;
                canvas.requestRenderAll();
                return;
            }
            if (!guideRect) {
                guideRect = new window.fabric.Rect({
                    left: area.x,
                    top: area.y,
                    width: area.w,
                    height: area.h,
                    fill: guideStyle.fill,
                    stroke: guideStyle.stroke,
                    strokeWidth: guideStyle.strokeWidth,
                    strokeDashArray: guideStyle.strokeDashArray,
                    selectable: true,
                    evented: true,
                    hasControls: true,
                    cornerStyle: 'circle',
                    cornerSize: 14,
                    cornerColor: 'rgba(255,255,255,0)',
                    cornerStrokeColor: 'rgba(255,255,255,0)',
                    transparentCorners: true,
                    borderColor: 'rgba(255,255,255,0)',
                    padding: 6,
                    hoverCursor: 'move',
                    excludeFromExport: true,
                });
                guideRect.isGuide = true;
                guideRect.on('mouseover', () => {
                    if (guideLabel) {
                        guideLabel.getObjects().forEach((obj) => {
                            obj.set({ fill: GUIDE_TEXT_COLOR });
                        });
                        canvas.requestRenderAll();
                    }
                });
                guideRect.on('mouseout', () => {
                    if (guideLabel) {
                        guideLabel.getObjects().forEach((obj) => {
                            obj.set({ fill: GUIDE_TEXT_COLOR });
                        });
                        canvas.requestRenderAll();
                    }
                });
                guideRect.on('mousedown', () => {
                    if (!isEditingArea.value) {
                        isEditingArea.value = true;
                    }
                    if (guideLabel) {
                        guideLabel.getObjects().forEach((obj) => {
                            obj.set({ fill: GUIDE_TEXT_COLOR });
                        });
                    }
                    guideLabel?.bringToFront();
                    guideHandle?.bringToFront();
                    setGuideControlsVisible(true);
                    canvas.setActiveObject(guideRect);
                    canvas.requestRenderAll();
                });
                guideRect.setControlsVisibility({ mtr: false });
                guideRect.on('moving', () => {
                    const nextBase = getGuideAreaFromRect();
                    updateGuideOverlay(nextBase, getDisplayPrintArea(nextBase));
                });
                guideRect.on('scaling', () => {
                    const nextBase = getGuideAreaFromRect();
                    updateGuideOverlay(nextBase, getDisplayPrintArea(nextBase));
                });
                guideRect.on('modified', () => {
                    commitGuideRect();
                    const nextBase = getGuideAreaFromRect();
                    updateGuideOverlay(nextBase, getDisplayPrintArea(nextBase));
                });
                canvas.add(guideRect);
            } else {
                guideRect.set({
                    left: area.x,
                    top: area.y,
                    width: area.w,
                    height: area.h,
                    fill: guideStyle.fill,
                    stroke: guideStyle.stroke,
                    strokeWidth: guideStyle.strokeWidth,
                    strokeDashArray: guideStyle.strokeDashArray,
                    cornerStyle: 'circle',
                    cornerSize: 14,
                    cornerColor: 'rgba(255,255,255,0)',
                    cornerStrokeColor: 'rgba(255,255,255,0)',
                    transparentCorners: true,
                    borderColor: 'rgba(255,255,255,0)',
                    padding: 6,
                    hoverCursor: 'move',
                });
            }
            updateGuideOverlay(baseArea, area);
            guideRect.set({
                selectable: true,
                evented: true,
                lockRotation: true,
                lockScalingFlip: true,
                lockUniScaling: false,
            });
            syncGuideInteractivity();
            guideRect.sendToBack();
            guideLabel?.bringToFront();
            guideHandle?.bringToFront();
            canvas.clipPath = clipRect;
            canvas.requestRenderAll();
        };

        const resetPrintArea = () => {
            const productAreas = customPrintAreas[selectedProductId.value] || {};
            if (productAreas[selectedViewId.value]) {
                const nextAreas = { ...productAreas };
                delete nextAreas[selectedViewId.value];
                customPrintAreas[selectedProductId.value] = nextAreas;
            }
            applyPrintArea();
        };

        const applyAreaInputs = () => {
            if (!currentView.value) {
                return;
            }
            let x = coerceNumber(areaInputs.x, 0) ?? 0;
            let y = coerceNumber(areaInputs.y, 0) ?? 0;
            let w = coerceNumber(areaInputs.w, 0) ?? 0;
            let h = coerceNumber(areaInputs.h, 0) ?? 0;
            w = Math.max(1, w);
            h = Math.max(1, h);
            x = Math.max(0, Math.min(x, BASE_SIZE.width - w));
            y = Math.max(0, Math.min(y, BASE_SIZE.height - h));

            const productAreas = customPrintAreas[selectedProductId.value] || {};
            customPrintAreas[selectedProductId.value] = {
                ...productAreas,
                [selectedViewId.value]: { x, y, w, h },
            };
            applyPrintArea();
        };

        const constrainWithinArea = (obj) => {
            if (!obj || !currentView.value) {
                return;
            }
            const area = getDisplayPrintArea(getCurrentPrintArea());
            const bounds = obj.getBoundingRect(true, true);
            let left = obj.left;
            let top = obj.top;
            const centerX = bounds.left + bounds.width / 2;
            const centerY = bounds.top + bounds.height / 2;
            const canFitX = bounds.width <= area.w;
            const canFitY = bounds.height <= area.h;

            if (canFitX) {
                if (bounds.left < area.x) {
                    left += area.x - bounds.left;
                }
                if (bounds.left + bounds.width > area.x + area.w) {
                    left -= bounds.left + bounds.width - (area.x + area.w);
                }
            } else {
                if (centerX < area.x) {
                    left += area.x - centerX;
                }
                if (centerX > area.x + area.w) {
                    left -= centerX - (area.x + area.w);
                }
            }

            if (canFitY) {
                if (bounds.top < area.y) {
                    top += area.y - bounds.top;
                }
                if (bounds.top + bounds.height > area.y + area.h) {
                    top -= bounds.top + bounds.height - (area.y + area.h);
                }
            } else {
                if (centerY < area.y) {
                    top += area.y - centerY;
                }
                if (centerY > area.y + area.h) {
                    top -= centerY - (area.y + area.h);
                }
            }

            obj.set({ left, top });
        };


        const addText = () => {
            if (!canvas) {
                return;
            }
            const area = getDisplayPrintArea(getCurrentPrintArea());
            const textbox = new window.fabric.Textbox(textValue.value || 'Your Text', {
                left: area.x + area.w / 2 - 120,
                top: area.y + area.h / 2 - 20,
                width: Math.min(240, area.w),
                fontSize: textSize.value,
                fill: textColor.value,
                fontFamily: selectedFont.value,
            });
            const centerX = area.x + area.w / 2;
            const centerY = area.y + area.h / 2;
            textbox.set({
                left: centerX - textbox.getScaledWidth() / 2,
                top: centerY - textbox.getScaledHeight() / 2,
            });
            textbox.dataId = `text-${Date.now()}`;
            textbox.name = 'Text Layer';
            canvas.add(textbox);
            canvas.setActiveObject(textbox);
            pushHistory();
        };

        const applyTextFont = () => {
            if (!canvas) {
                return;
            }
            const obj = canvas.getActiveObject();
            if (obj && obj.type === 'textbox') {
                obj.set('fontFamily', selectedFont.value);
                obj.set('fontSize', textSize.value);
                obj.set('fill', textColor.value);
                canvas.requestRenderAll();
                pushHistory();
            }
        };

        const addRectangle = () => {
            if (!canvas || !currentView.value) {
                return;
            }
            const area = getDisplayPrintArea(getCurrentPrintArea());
            const rect = new window.fabric.Rect({
                left: area.x + area.w / 2 - 80,
                top: area.y + area.h / 2 - 60,
                width: 160,
                height: 120,
                fill: shapeFill.value,
                stroke: shapeStroke.value,
                strokeWidth: shapeStrokeWidth.value,
                rx: 8,
                ry: 8,
            });
            rect.dataId = `shape-${Date.now()}`;
            rect.name = 'Rectangle';
            canvas.add(rect);
            canvas.setActiveObject(rect);
            pushHistory();
        };

        const addCircle = () => {
            if (!canvas || !currentView.value) {
                return;
            }
            const area = getDisplayPrintArea(getCurrentPrintArea());
            const circle = new window.fabric.Circle({
                left: area.x + area.w / 2 - 60,
                top: area.y + area.h / 2 - 60,
                radius: 60,
                fill: shapeFill.value,
                stroke: shapeStroke.value,
                strokeWidth: shapeStrokeWidth.value,
            });
            circle.dataId = `shape-${Date.now()}`;
            circle.name = 'Circle';
            canvas.add(circle);
            canvas.setActiveObject(circle);
            pushHistory();
        };

        const addLine = () => {
            if (!canvas || !currentView.value) {
                return;
            }
            const area = getDisplayPrintArea(getCurrentPrintArea());
            const line = new window.fabric.Line(
                [
                    area.x + area.w / 2 - 80,
                    area.y + area.h / 2,
                    area.x + area.w / 2 + 80,
                    area.y + area.h / 2,
                ],
                {
                    stroke: lineStroke.value,
                    strokeWidth: lineStrokeWidth.value,
                    strokeLineCap: 'round',
                }
            );
            line.dataId = `shape-${Date.now()}`;
            line.name = 'Line';
            canvas.add(line);
            canvas.setActiveObject(line);
            pushHistory();
        };

        const addImageFromUrl = (url) => {
            if (!canvas || !url) {
                return;
            }
            window.fabric.Image.fromURL(url, (img) => {
                const area = getDisplayPrintArea(getCurrentPrintArea());
                const maxScaleX = (area.w * 0.8) / img.width;
                const maxScaleY = (area.h * 0.8) / img.height;
                const scale = Math.min(maxScaleX, maxScaleY, 1) || 0.5;
                const centerX = area.x + area.w / 2;
                const centerY = area.y + area.h / 2;
                img.set({
                    originX: 'center',
                    originY: 'center',
                    left: centerX,
                    top: centerY,
                    scaleX: scale,
                    scaleY: scale,
                    selectable: true,
                    hasControls: true,
                    hasBorders: true,
                    cornerStyle: 'circle',
                    cornerSize: 12,
                    cornerColor: '#e11d2e',
                    cornerStrokeColor: '#ffffff',
                    transparentCorners: false,
                    borderColor: '#e11d2e',
                    padding: 6,
                });
                img.setCoords?.();
                constrainWithinArea(img);
                img.dataId = `image-${Date.now()}`;
                img.name = 'Artwork';
                canvas.add(img);
                syncGuideInteractivity();
                guideRect?.sendToBack();
                guideLabel?.bringToFront();
                guideHandle?.bringToFront();
                canvas.setActiveObject(img);
                canvas.requestRenderAll();
                pushHistory();
            });
        };

        const storeUpload = (file, url) => {
            if (!url) {
                return;
            }
            const entry = {
                id: `upload-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
                timestamp: Date.now(),
                name: file?.name || 'Upload',
                url,
            };
            const existing = readStoredUploads();
            const nextEntries = [entry, ...existing].slice(0, UPLOAD_MAX_ENTRIES);
            writeStoredUploads(nextEntries);
            uploads.value = nextEntries;
        };

        const handleImageFile = (file) => {
            if (!canvas) {
                return;
            }
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                const dataUrl = e.target.result;
                addImageFromUrl(dataUrl);
                storeUpload(file, dataUrl);
            };
            reader.readAsDataURL(file);
        };

        const handleUpload = (event) => {
            const file = event.target.files?.[0];
            if (!file) {
                return;
            }
            handleImageFile(file);
            event.target.value = '';
        };

        const selectProduct = async (productId) => {
            saveCurrentViewDesign();
            selectedProductId.value = productId;
            const product = products.value.find((item) => item.id === productId);
            selectedViewId.value = product.views[0].id;
            selectedColor.value = product.colors[0];
            isEditingArea.value = true;
            await loadViewDesign(productId, selectedViewId.value);
            pushHistory();
        };

        const selectView = async (viewId) => {
            saveCurrentViewDesign();
            selectedViewId.value = viewId;
            await loadViewDesign(selectedProductId.value, viewId);
        };

        const nextView = () => {
            const views = currentProduct.value?.views || [];
            if (views.length < 2) {
                return;
            }
            saveCurrentViewDesign();
            const currentIndex = views.findIndex((view) => view.id === selectedViewId.value);
            const nextIndex = currentIndex >= 0 ? (currentIndex + 1) % views.length : 0;
            selectedViewId.value = views[nextIndex].id;
            loadViewDesign(selectedProductId.value, selectedViewId.value);
        };

        const previousView = () => {
            const views = currentProduct.value?.views || [];
            if (views.length < 2) {
                return;
            }
            saveCurrentViewDesign();
            const currentIndex = views.findIndex((view) => view.id === selectedViewId.value);
            const prevIndex = currentIndex >= 0 ? (currentIndex - 1 + views.length) % views.length : 0;
            selectedViewId.value = views[prevIndex].id;
            loadViewDesign(selectedProductId.value, selectedViewId.value);
        };

        const selectColor = (color) => {
            selectedColor.value = color;
            applyPrintArea();
        };

        const selectLayer = (id) => {
            if (!canvas) {
                return;
            }
            const obj = canvas.getObjects().find((item) => item.dataId === id);
            if (obj) {
                canvas.setActiveObject(obj);
                canvas.requestRenderAll();
            }
        };

        const toggleLock = (id) => {
            if (!canvas) {
                return;
            }
            const obj = canvas.getObjects().find((item) => item.dataId === id);
            if (!obj) {
                return;
            }
            const locked = obj.selectable === false;
            obj.set({
                selectable: locked,
                evented: locked,
                locked: !locked,
            });
            canvas.discardActiveObject();
            canvas.requestRenderAll();
            syncLayers();
        };

        const removeLayer = (id) => {
            if (!canvas) {
                return;
            }
            const obj = canvas.getObjects().find((item) => item.dataId === id);
            if (obj) {
                canvas.remove(obj);
                canvas.requestRenderAll();
                pushHistory();
                syncLayers();
            }
        };

        const applyOpacity = () => {
            if (!canvas || !activeObject.value) {
                return;
            }
            activeObject.value.set('opacity', activeProps.opacity);
            canvas.requestRenderAll();
            pushHistory();
        };

        const applyRotation = () => {
            if (!canvas || !activeObject.value) {
                return;
            }
            activeObject.value.set('angle', activeProps.rotation);
            canvas.requestRenderAll();
            pushHistory();
        };

        const flipHorizontal = () => {
            if (!canvas || !activeObject.value) {
                return;
            }
            activeObject.value.set('flipX', !activeObject.value.flipX);
            canvas.requestRenderAll();
            pushHistory();
        };

        const flipVertical = () => {
            if (!canvas || !activeObject.value) {
                return;
            }
            activeObject.value.set('flipY', !activeObject.value.flipY);
            canvas.requestRenderAll();
            pushHistory();
        };


        const zoomIn = () => {
            setZoom(zoom.value + 0.1);
        };

        const zoomOut = () => {
            setZoom(zoom.value - 0.1);
        };

        const setZoom = (value) => {
            if (!canvas) {
                return;
            }
            zoom.value = Math.min(2.5, Math.max(0.5, value));
            const center = new window.fabric.Point(BASE_SIZE.width / 2, BASE_SIZE.height / 2);
            canvas.zoomToPoint(center, zoom.value);
            canvas.requestRenderAll();
        };

        const snapToCenter = () => {
            if (!canvas || !activeObject.value || !currentView.value) {
                return;
            }
            const area = getDisplayPrintArea(getCurrentPrintArea());
            activeObject.value.set({
                left: area.x + area.w / 2 - activeObject.value.getScaledWidth() / 2,
                top: area.y + area.h / 2 - activeObject.value.getScaledHeight() / 2,
            });
            canvas.requestRenderAll();
            pushHistory();
        };

        const downloadData = (dataUrl, filename) => {
            const link = document.createElement('a');
            link.href = dataUrl;
            link.download = filename;
            link.click();
        };

        const downloadBlob = (blob, filename) => {
            const url = URL.createObjectURL(blob);
            downloadData(url, filename);
            setTimeout(() => URL.revokeObjectURL(url), 0);
        };

        const downloadPng = () => {
            if (!canvas) {
                return;
            }
            const dataUrl = canvas.toDataURL({ format: 'png' });
            downloadData(dataUrl, `${currentProduct.value.name}-design.png`);
        };

        const downloadSvg = () => {
            if (!canvas) {
                return;
            }
            const svgMarkup = canvas.toSVG();
            const blob = new Blob([svgMarkup], { type: 'image/svg+xml;charset=utf-8' });
            downloadBlob(blob, `${currentProduct.value.name}-design.svg`);
        };


        const downloadPrint = () => {
            if (!canvas || !currentView.value) {
                return;
            }
            const area = getDisplayPrintArea(getCurrentPrintArea());
            const dataUrl = canvas.toDataURL({
                format: 'png',
                left: area.x,
                top: area.y,
                width: area.w,
                height: area.h,
                multiplier: VIEW_EXPORT_SCALE,
            });
            downloadData(dataUrl, `${currentProduct.value.name}-print.png`);
        };

        const downloadAllViewsZip = async () => {
            if (!canvas || !currentProduct.value) {
                return;
            }
            const zipApi = window.JSZip;
            if (!zipApi) {
                downloadPrint();
                return;
            }

            saveCurrentViewDesign();
            const product = currentProduct.value;
            const originalViewId = selectedViewId.value;
            const originalJson = serializeCanvas();

            const zip = new zipApi();
            for (const view of product.views) {
                selectedViewId.value = view.id;
                await loadViewDesign(product.id, view.id);
                const area = getDisplayPrintArea(getCurrentPrintArea());
                const dataUrl = canvas.toDataURL({
                    format: 'png',
                    left: area.x,
                    top: area.y,
                    width: area.w,
                    height: area.h,
                    multiplier: VIEW_EXPORT_SCALE,
                });
                const base64 = dataUrl.split(',')[1];
                const safeName = product.name.replace(/\s+/g, '-').toLowerCase();
                zip.file(`${safeName}-${view.id}-print.png`, base64, { base64: true });
            }

            selectedViewId.value = originalViewId;
            await loadCanvasFromJson(originalJson);
            applyPrintArea();

            const blob = await zip.generateAsync({ type: 'blob' });
            downloadBlob(blob, `${product.name}-designs.zip`);
        };

        const downloadPdf = async () => {
            if (!canvas) {
                return;
            }
            const pdfApi = window.jspdf;
            if (!pdfApi?.jsPDF) {
                downloadJpg();
                return;
            }
            const output = document.createElement('canvas');
            output.width = BASE_SIZE.width;
            output.height = BASE_SIZE.height;
            const ctx = output.getContext('2d');
            await drawMockupComposite(ctx);

            const dataUrl = output.toDataURL('image/jpeg', 0.92);
            const pdf = new pdfApi.jsPDF({
                orientation: BASE_SIZE.width >= BASE_SIZE.height ? 'landscape' : 'portrait',
                unit: 'px',
                format: [BASE_SIZE.width, BASE_SIZE.height],
            });
            pdf.addImage(dataUrl, 'JPEG', 0, 0, BASE_SIZE.width, BASE_SIZE.height);
            pdf.save(`${currentProduct.value.name}-mockup.pdf`);
        };

        const renderPhotoMockup = async () => {
            const img = new Image();
            img.src = currentMockupImage.value;
            await img.decode();
            return img;
        };

        const renderSvgLayer = async (svgMarkup) => {
            const img = new Image();
            const coloredSvg = applySvgColor(svgMarkup, selectedColor.value?.value || '');
            const svgData = `data:image/svg+xml;utf8,${encodeURIComponent(coloredSvg)}`;
            img.src = svgData;
            await img.decode();
            return img;
        };

        const drawMockupComposite = async (ctx) => {
            const designData = canvas.toDataURL({ format: 'png' });
            const designImage = new Image();
            designImage.src = designData;
            await designImage.decode();

            if (hasPhotoMockup.value && currentMockupImage.value) {
                const photo = await renderPhotoMockup();
                const fillColor = selectedColor.value?.value || '#ffffff';
                ctx.fillStyle = fillColor;
                ctx.fillRect(0, 0, BASE_SIZE.width, BASE_SIZE.height);
                ctx.globalCompositeOperation = 'multiply';
                ctx.globalAlpha = 0.6;
                ctx.drawImage(designImage, 0, 0, BASE_SIZE.width, BASE_SIZE.height);
                ctx.globalCompositeOperation = 'source-over';
                ctx.globalAlpha = 1;
                ctx.drawImage(designImage, 0, 0, BASE_SIZE.width, BASE_SIZE.height);
                const bounds = getPhotoBounds();
                if (bounds) {
                    ctx.drawImage(photo, bounds.x, bounds.y, bounds.w, bounds.h);
                } else {
                    const scale = Math.min(BASE_SIZE.width / photo.width, BASE_SIZE.height / photo.height);
                    const scaledW = photo.width * scale;
                    const scaledH = photo.height * scale;
                    const x = (BASE_SIZE.width - scaledW) / 2;
                    const y = (BASE_SIZE.height - scaledH) / 2;
                    ctx.drawImage(photo, x, y, scaledW, scaledH);
                }
                return;
            }

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, BASE_SIZE.width, BASE_SIZE.height);
            const baseLayer = await renderSvgLayer(currentMockupBase.value);
            const overlayLayer = await renderSvgLayer(currentMockupOverlay.value);
            ctx.drawImage(baseLayer, 0, 0, BASE_SIZE.width, BASE_SIZE.height);
            ctx.globalCompositeOperation = 'multiply';
            ctx.globalAlpha = 0.6;
            ctx.drawImage(designImage, 0, 0, BASE_SIZE.width, BASE_SIZE.height);
            ctx.globalCompositeOperation = 'source-over';
            ctx.globalAlpha = 1;
            ctx.drawImage(designImage, 0, 0, BASE_SIZE.width, BASE_SIZE.height);
            ctx.drawImage(overlayLayer, 0, 0, BASE_SIZE.width, BASE_SIZE.height);
        };

        const renderCurrentMockupDataUrl = async (mimeType = 'image/jpeg', quality = 0.9) => {
            const output = document.createElement('canvas');
            output.width = BASE_SIZE.width;
            output.height = BASE_SIZE.height;
            const ctx = output.getContext('2d');
            await drawMockupComposite(ctx);
            return output.toDataURL(mimeType, quality);
        };

        const refreshPhotoBounds = async () => {
            photoBounds.value = null;
            if (!hasPhotoMockup.value || !currentMockupImage.value) {
                if (canvas) {
                    applyPrintArea();
                }
                return;
            }
            const stageEl = stageRef.value;
            const photoEl = stageEl?.querySelector?.('.mockup-photo-img');
            const stageRect = stageEl?.getBoundingClientRect?.();
            const naturalWidth = photoEl?.naturalWidth || 0;
            const naturalHeight = photoEl?.naturalHeight || 0;
            if (stageRect?.width && stageRect?.height && naturalWidth && naturalHeight) {
                const scale = Math.min(stageRect.width / naturalWidth, stageRect.height / naturalHeight);
                const scaledW = naturalWidth * scale;
                const scaledH = naturalHeight * scale;
                const xStage = (stageRect.width - scaledW) / 2;
                const yStage = (stageRect.height - scaledH) / 2;
                const scaleX = BASE_SIZE.width / stageRect.width;
                const scaleY = BASE_SIZE.height / stageRect.height;
                photoBounds.value = {
                    x: xStage * scaleX,
                    y: yStage * scaleY,
                    w: scaledW * scaleX,
                    h: scaledH * scaleY,
                };
                if (canvas) {
                    applyPrintArea();
                }
                return;
            }
            const currentId = (photoLoadId += 1);
            const img = new Image();
            img.src = currentMockupImage.value;
            try {
                await img.decode();
            } catch (error) {
                return;
            }
            if (currentId !== photoLoadId) {
                return;
            }
            const scale = Math.min(BASE_SIZE.width / img.width, BASE_SIZE.height / img.height);
            const scaledW = img.width * scale;
            const scaledH = img.height * scale;
            photoBounds.value = {
                x: (BASE_SIZE.width - scaledW) / 2,
                y: (BASE_SIZE.height - scaledH) / 2,
                w: scaledW,
                h: scaledH,
            };
            if (canvas) {
                applyPrintArea();
            }
        };

        const downloadJpg = async () => {
            if (!canvas) {
                return;
            }
            const dataUrl = await renderCurrentMockupDataUrl('image/jpeg', 0.92);
            downloadData(dataUrl, `${currentProduct.value.name}-mockup.jpg`);
        };

        const toggleMenu = (key) => {
            openMenu.value = openMenu.value === key ? '' : key;
        };

        const closeMenus = (event) => {
            if (event?.target?.closest?.('.canva-menu')) {
                return;
            }
            openMenu.value = '';
        };


        const handleStageDragOver = (event) => {
            event.preventDefault();
            if (event.dataTransfer) {
                event.dataTransfer.dropEffect = 'copy';
            }
        };

        const handleStageDrop = (event) => {
            event.preventDefault();
            event.stopPropagation();
            const transfer = event.dataTransfer;
            const files = Array.from(transfer?.files || []);
            const imageFile = files.find((file) => file.type?.startsWith('image/')) || files[0];
            if (imageFile) {
                handleImageFile(imageFile);
                return;
            }
            const uploadUrl =
                transfer?.getData?.('application/x-nai-upload') ||
                transfer?.getData?.('text/uri-list') ||
                transfer?.getData?.('text/plain');
            if (uploadUrl) {
                addImageFromUrl(uploadUrl);
            }
        };

        const placeUpload = (entry) => {
            if (!entry?.url) {
                return;
            }
            addImageFromUrl(entry.url);
        };

        const onUploadDragStart = (entry, event) => {
            if (!entry?.url || !event?.dataTransfer) {
                return;
            }
            event.dataTransfer.setData('application/x-nai-upload', entry.url);
            event.dataTransfer.setData('text/uri-list', entry.url);
            event.dataTransfer.setData('text/plain', entry.url);
            event.dataTransfer.effectAllowed = 'copy';
        };

        onMounted(() => {
            canvas = new window.fabric.Canvas(canvasRef.value, {
                backgroundColor: 'transparent',
                preserveObjectStacking: true,
            });
            canvas.setWidth(BASE_SIZE.width);
            canvas.setHeight(BASE_SIZE.height);
            const blendCanvas = blendCanvasRef.value;
            if (blendCanvas) {
                blendCanvas.width = BASE_SIZE.width;
                blendCanvas.height = BASE_SIZE.height;
            }

            const stageEl = stageRef.value;
            if (stageEl) {
                stageEl.addEventListener('dragover', handleStageDragOver);
                stageEl.addEventListener('drop', handleStageDrop);
            }

            applyPrintArea();
            refreshPhotoBounds();
            refreshUploads();
            pushHistory();

            const syncBlendCanvas = () => {
                const blend = blendCanvasRef.value;
                if (!blend || !canvas) {
                    return;
                }
                const ctx = blend.getContext('2d');
                if (!ctx) {
                    return;
                }
                const snapshot = canvas.toCanvasElement({
                    multiplier: 1,
                    enableRetinaScaling: true,
                });
                ctx.clearRect(0, 0, blend.width, blend.height);
                ctx.drawImage(snapshot, 0, 0, blend.width, blend.height);
            };

            canvas.on('after:render', syncBlendCanvas);

            canvas.on('object:moving', (event) => {
                constrainWithinArea(event.target);
            });
            canvas.on('object:scaling', (event) => {
                constrainWithinArea(event.target);
            });
            canvas.on('object:modified', () => {
                if (isRestoring) {
                    return;
                }
                const target = canvas.getActiveObject();
                if (target?.isGuide) {
                    return;
                }
                pushHistory();
                syncLayers();
            });
            canvas.on('object:added', (event) => {
                if (isRestoring) {
                    return;
                }
                if (event?.target?.isGuide) {
                    return;
                }
                syncGuideInteractivity();
                syncLayers();
                pushHistory();
            });
            canvas.on('object:removed', (event) => {
                if (isRestoring) {
                    return;
                }
                if (event?.target?.isGuide) {
                    return;
                }
                syncGuideInteractivity();
                syncLayers();
                pushHistory();
            });
            const handleSelection = (obj) => {
                updateActiveObject(obj);
                const isGuide = obj === guideRect;
                if (guideRect) {
                    const allowGuide = isGuide || !obj;
                    guideRect.set({
                        selectable: allowGuide,
                        evented: allowGuide,
                    });
                }
                setGuideControlsVisible(isGuide);
                if (guideRect && !isGuide) {
                    guideRect.sendToBack();
                    guideLabel?.bringToFront();
                    guideHandle?.bringToFront();
                    canvas.requestRenderAll();
                }
            };
            canvas.on('selection:created', (event) => handleSelection(event.selected?.[0] || null));
            canvas.on('selection:updated', (event) => handleSelection(event.selected?.[0] || null));
            canvas.on('selection:cleared', () => handleSelection(null));

            window.addEventListener('keydown', (event) => {
                if (isTypingTarget(event.target)) {
                    return;
                }
                if (canvas?.getActiveObject?.()?.isEditing) {
                    return;
                }
                if ((event.ctrlKey || event.metaKey) && event.key === 'z') {
                    event.preventDefault();
                    undo();
                }
                if ((event.ctrlKey || event.metaKey) && event.key === 'y') {
                    event.preventDefault();
                    redo();
                }
                if (event.key === 'Delete' || event.key === 'Backspace') {
                    event.preventDefault();
                    deleteSelection();
                }
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'c') {
                    event.preventDefault();
                    copySelection();
                }
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'v') {
                    event.preventDefault();
                    pasteSelection();
                }
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'x') {
                    event.preventDefault();
                    cutSelection();
                }
                if (event.altKey && event.key.toLowerCase() === 'd') {
                    event.preventDefault();
                    copySelection();
                    pasteSelection();
                }
                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    nudgeSelection(0, event.shiftKey ? -10 : -1);
                }
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    nudgeSelection(0, event.shiftKey ? 10 : 1);
                }
                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    nudgeSelection(event.shiftKey ? -10 : -1, 0);
                }
                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    nudgeSelection(event.shiftKey ? 10 : 1, 0);
                }
                if (!event.ctrlKey && !event.metaKey && event.key.toLowerCase() === 'r') {
                    event.preventDefault();
                    rotateSelection(event.shiftKey ? 5 : 15);
                }
                if (event.key === '+' || event.key === '=') {
                    event.preventDefault();
                    scaleSelection(event.shiftKey ? 1.01 : 1.05);
                }
                if (event.key === '-' || event.key === '_') {
                    event.preventDefault();
                    scaleSelection(event.shiftKey ? 0.99 : 0.95);
                }
                if ((event.ctrlKey || event.metaKey) && event.key === '[') {
                    event.preventDefault();
                    moveLayer('backward');
                }
                if ((event.ctrlKey || event.metaKey) && event.key === ']') {
                    event.preventDefault();
                    moveLayer('forward');
                }
            });
            document.addEventListener('click', closeMenus);
            refreshProjectHistory();
            historyRefreshTimer = setInterval(refreshProjectHistory, 60 * 1000);
        });

        onBeforeUnmount(() => {
            document.removeEventListener('click', closeMenus);
            if (historyRefreshTimer) {
                clearInterval(historyRefreshTimer);
            }
            if (designerNoticeTimer) {
                clearTimeout(designerNoticeTimer);
            }
            const stageEl = stageRef.value;
            if (stageEl) {
                stageEl.removeEventListener('dragover', handleStageDragOver);
                stageEl.removeEventListener('drop', handleStageDrop);
            }
        });

        watch(selectedViewId, () => {
            applyPrintArea();
        });

        watch(currentMockupImage, () => {
            refreshPhotoBounds();
        });

        return {
            products,
            selectedProductId,
            selectedViewId,
            selectedColor,
            currentProduct,
            currentView,
            currentMockupImage,
            hasPhotoMockup,
            currentMockup,
            currentMockupBase,
            currentMockupOverlay,
            textValue,
            fonts,
            selectedFont,
            canvasRef,
            blendCanvasRef,
            stageRef,
            menuUploadRef,
            zoom,
            layers,
            activeLayerId,
            activeObject,
            activeProps,
            canUndo,
            canRedo,
            areaInputs,
            openMenu,
            activeRail,
            projectHistory,
            uploads,
            designerNotice,
            mobilePanelOpen,
            toolItems,
            selectedToolId,
            textSize,
            textColor,
            shapeFill,
            shapeStroke,
            shapeStrokeWidth,
            lineStroke,
            lineStrokeWidth,
            isIconImage,
            selectProduct,
            selectView,
            selectColor,
            nextView,
            previousView,
            resetPrintArea,
            applyAreaInputs,
            addText,
            applyTextFont,
            handleUpload,
            addRectangle,
            addCircle,
            addLine,
            selectLayer,
            toggleLock,
            removeLayer,
            deleteSelection,
            applyOpacity,
            applyRotation,
            flipHorizontal,
            flipVertical,
            zoomIn,
            zoomOut,
            snapToCenter,
            downloadPng,
            downloadJpg,
            downloadPdf,
            downloadSvg,
            downloadPrint,
            downloadAllViewsZip,
            toggleMenu,
            selectRail,
            closeMobilePanel,
            selectTool,
            triggerMenuUpload,
            saveProjectSnapshot,
            addCurrentDesignToCart,
            addSavedDesignToCart,
            addAllSavedDesignsToCart,
            loadHistoryEntry,
            deleteHistoryEntry,
            formatHistoryTime,
            formatHistoryExpiry,
            placeUpload,
            onUploadDragStart,
            undo,
            redo,
        };
        },
    }).mount('#designer-app');
};

if (window.Vue && window.fabric && document.getElementById('designer-app')) {
    mountDesigner();
}

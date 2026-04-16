import { createApp, computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue/dist/vue.esm-bundler.js';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import { jsPDF } from 'jspdf';

const root = document.getElementById('jersey-designer-app');
const BOOT = window.JERSEY_DESIGNER_BOOT || {};
const fabricLib = window.fabric;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const LOCAL_KEY = 'nai_jersey_designer_autosave';

const defaults = [
    { id: 1, name: 'Football Jersey (Short Sleeve)', jersey_type: 'football_short', base_price: 1800, model_url: '/models/jerseys/football-short.glb', supports_sleeves: true, metadata: { views: ['front', 'back', 'sleeve_left', 'sleeve_right'] } },
    { id: 2, name: 'Long Sleeve Jersey', jersey_type: 'football_long', base_price: 2050, model_url: '/models/jerseys/football-long.glb', supports_sleeves: true, metadata: { views: ['front', 'back', 'sleeve_left', 'sleeve_right'] } },
    { id: 3, name: 'Basketball Jersey (Sleeveless)', jersey_type: 'basketball', base_price: 1700, model_url: '/models/jerseys/basketball.glb', supports_sleeves: false, metadata: { views: ['front', 'back'] } },
    { id: 4, name: 'Training Jersey', jersey_type: 'training', base_price: 1500, model_url: '/models/jerseys/training.glb', supports_sleeves: true, metadata: { views: ['front', 'back', 'sleeve_left', 'sleeve_right'] } },
];
const templates = [
    { key: 'solid', label: 'Solid' },
    { key: 'stripes', label: 'Stripes' },
    { key: 'gradient', label: 'Gradient' },
    { key: 'diagonal', label: 'Diagonal' },
    { key: 'esports', label: 'Esports' },
];

const api = {
    get: async (url) => (await window.axios.get(url, { headers: { Accept: 'application/json' } })).data,
    post: async (url, payload, extraHeaders = {}) =>
        (
            await window.axios.post(url, payload, {
                headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json', ...extraHeaders },
            })
        ).data,
};

const c = (v) => Math.max(0, Math.min(255, Math.round(v)));
const rgb = (hex) => {
    const h = (hex || '').replace('#', '');
    if (!/^[0-9a-fA-F]{6}$/.test(h)) return { r: 15, g: 23, b: 42 };
    return { r: parseInt(h.slice(0, 2), 16), g: parseInt(h.slice(2, 4), 16), b: parseInt(h.slice(4, 6), 16) };
};
const lighten = (hex, amount = 0.2) => {
    const { r, g, b } = rgb(hex);
    return `rgb(${c(r + (255 - r) * amount)},${c(g + (255 - g) * amount)},${c(b + (255 - b) * amount)})`;
};
const darken = (hex, amount = 0.2) => {
    const { r, g, b } = rgb(hex);
    return `rgb(${c(r * (1 - amount))},${c(g * (1 - amount))},${c(b * (1 - amount))})`;
};

const patternCanvas = (type, baseColor) => {
    const canvas = document.createElement('canvas');
    canvas.width = 1024;
    canvas.height = 1024;
    const ctx = canvas.getContext('2d');
    if (!ctx) return canvas;
    ctx.fillStyle = baseColor;
    ctx.fillRect(0, 0, 1024, 1024);
    if (type === 'stripes') {
        ctx.fillStyle = darken(baseColor, 0.18);
        for (let i = -120; i < 1200; i += 170) ctx.fillRect(i, 0, 82, 1024);
    } else if (type === 'gradient') {
        const g = ctx.createLinearGradient(0, 0, 1024, 1024);
        g.addColorStop(0, lighten(baseColor, 0.35));
        g.addColorStop(0.5, baseColor);
        g.addColorStop(1, darken(baseColor, 0.2));
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, 1024, 1024);
    } else if (type === 'diagonal') {
        ctx.strokeStyle = lighten(baseColor, 0.35);
        ctx.lineWidth = 20;
        for (let i = -1024; i < 1024; i += 84) {
            ctx.beginPath();
            ctx.moveTo(i, 0);
            ctx.lineTo(i + 1024, 1024);
            ctx.stroke();
        }
    } else if (type === 'esports') {
        ctx.fillStyle = darken(baseColor, 0.3);
        for (let row = 0; row < 6; row += 1) {
            const y = row * 170;
            ctx.beginPath();
            ctx.moveTo(0, y + 70);
            ctx.lineTo(220, y + 20);
            ctx.lineTo(510, y + 130);
            ctx.lineTo(840, y + 40);
            ctx.lineTo(1024, y + 98);
            ctx.lineTo(1024, y + 170);
            ctx.lineTo(0, y + 170);
            ctx.closePath();
            ctx.fill();
        }
    }
    return canvas;
};

const weaveTexture = () => {
    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 256;
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;
    ctx.fillStyle = '#7d7d7d';
    ctx.fillRect(0, 0, 256, 256);
    for (let i = 0; i < 256; i += 4) {
        ctx.strokeStyle = i % 8 === 0 ? '#989898' : '#666';
        ctx.beginPath();
        ctx.moveTo(i, 0);
        ctx.lineTo(i, 256);
        ctx.stroke();
    }
    for (let i = 0; i < 256; i += 4) {
        ctx.strokeStyle = i % 8 === 0 ? '#8d8d8d' : '#5d5d5d';
        ctx.beginPath();
        ctx.moveTo(0, i);
        ctx.lineTo(256, i);
        ctx.stroke();
    }
    const t = new THREE.CanvasTexture(canvas);
    t.wrapS = THREE.RepeatWrapping;
    t.wrapT = THREE.RepeatWrapping;
    t.repeat.set(8, 8);
    return t;
};

const clothDetailTexture = () => {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;

    ctx.fillStyle = '#7a7a7a';
    ctx.fillRect(0, 0, 512, 512);

    for (let y = 0; y < 512; y += 1) {
        for (let x = 0; x < 512; x += 1) {
            const value = 118 + Math.floor(Math.sin((x + y) * 0.07) * 12) + Math.floor(Math.cos((x - y) * 0.09) * 11);
            ctx.fillStyle = `rgb(${value},${value},${value})`;
            ctx.fillRect(x, y, 1, 1);
        }
    }

    for (let i = 0; i < 512; i += 6) {
        ctx.strokeStyle = i % 12 === 0 ? 'rgba(255,255,255,0.16)' : 'rgba(0,0,0,0.14)';
        ctx.beginPath();
        ctx.moveTo(i, 0);
        ctx.lineTo(i, 512);
        ctx.stroke();
    }
    for (let i = 0; i < 512; i += 6) {
        ctx.strokeStyle = i % 12 === 0 ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.12)';
        ctx.beginPath();
        ctx.moveTo(0, i);
        ctx.lineTo(512, i);
        ctx.stroke();
    }

    const t = new THREE.CanvasTexture(canvas);
    t.wrapS = THREE.RepeatWrapping;
    t.wrapT = THREE.RepeatWrapping;
    t.repeat.set(6, 6);
    return t;
};

const studioBackgroundTexture = () => {
    const canvas = document.createElement('canvas');
    canvas.width = 1024;
    canvas.height = 1024;
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;

    const radial = ctx.createRadialGradient(512, 410, 120, 512, 640, 900);
    radial.addColorStop(0, '#1f2a60');
    radial.addColorStop(0.45, '#111b47');
    radial.addColorStop(1, '#070e2d');
    ctx.fillStyle = radial;
    ctx.fillRect(0, 0, 1024, 1024);

    const topGlow = ctx.createLinearGradient(0, 0, 0, 320);
    topGlow.addColorStop(0, 'rgba(255,255,255,0.12)');
    topGlow.addColorStop(1, 'rgba(255,255,255,0)');
    ctx.fillStyle = topGlow;
    ctx.fillRect(0, 0, 1024, 320);

    for (let i = 0; i < 90; i += 1) {
        const x = (i * 137) % 1024;
        const y = (i * 83) % 1024;
        ctx.fillStyle = 'rgba(255,255,255,0.018)';
        ctx.beginPath();
        ctx.arc(x, y, (i % 4) + 1, 0, Math.PI * 2);
        ctx.fill();
    }

    const t = new THREE.CanvasTexture(canvas);
    t.colorSpace = THREE.SRGBColorSpace;
    return t;
};

const floorShadowTexture = () => {
    const canvas = document.createElement('canvas');
    canvas.width = 1024;
    canvas.height = 1024;
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;

    const radial = ctx.createRadialGradient(512, 512, 80, 512, 512, 512);
    radial.addColorStop(0, 'rgba(255,255,255,0.24)');
    radial.addColorStop(0.32, 'rgba(170,190,255,0.13)');
    radial.addColorStop(0.68, 'rgba(30,47,95,0.08)');
    radial.addColorStop(1, 'rgba(5,10,26,0)');
    ctx.fillStyle = radial;
    ctx.fillRect(0, 0, 1024, 1024);

    const t = new THREE.CanvasTexture(canvas);
    t.colorSpace = THREE.SRGBColorSpace;
    return t;
};

const wireframeBackgroundTexture = () => {
    const canvas = document.createElement('canvas');
    canvas.width = 1024;
    canvas.height = 1024;
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;

    const g = ctx.createLinearGradient(0, 0, 1024, 1024);
    g.addColorStop(0, '#2f3136');
    g.addColorStop(1, '#1f2228');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, 1024, 1024);

    const t = new THREE.CanvasTexture(canvas);
    t.colorSpace = THREE.SRGBColorSpace;
    return t;
};

const smoothstep = (edge0, edge1, value) => {
    if (edge0 === edge1) return 0;
    const t = Math.max(0, Math.min(1, (value - edge0) / (edge1 - edge0)));
    return t * t * (3 - 2 * t);
};

const sculptTorsoGeometry = (geometry) => {
    const position = geometry.attributes.position;
    for (let i = 0; i < position.count; i += 1) {
        const x = position.getX(i);
        const y = position.getY(i);
        const z = position.getZ(i);
        const radius = Math.max(Math.sqrt(x * x + z * z), 0.0001);
        const theta = Math.atan2(z, x);
        const yNorm = (y + 1.3) / 2.6;

        const shoulder = smoothstep(0.62, 1, yNorm) * 0.21;
        const chest = Math.exp(-Math.pow((yNorm - 0.62) / 0.25, 2)) * 0.11;
        const waist = Math.exp(-Math.pow((yNorm - 0.34) / 0.2, 2)) * 0.14;
        const hip = Math.exp(-Math.pow((yNorm - 0.1) / 0.24, 2)) * 0.06;
        const sidePanel = Math.cos(theta * 2) * (0.045 + yNorm * 0.02);
        const wrinkles = Math.sin(theta * 4 + y * 3.1) * 0.025 * (0.35 + yNorm * 0.65);

        let radiusScale = 1 + shoulder + chest - waist + hip + sidePanel + wrinkles;
        radiusScale = Math.max(0.68, radiusScale);
        const newRadius = radius * radiusScale;

        const chestPush = Math.cos(theta) * Math.exp(-Math.pow((yNorm - 0.58) / 0.28, 2)) * 0.08;
        const lowerFold = Math.sin(theta * 2.5 + y * 4.4) * Math.exp(-Math.pow((yNorm - 0.08) / 0.2, 2)) * 0.05;
        const xShift = Math.sin(y * 1.7) * 0.03;

        position.setX(i, Math.cos(theta) * newRadius + xShift);
        position.setY(i, y + Math.sin(theta * 1.8) * 0.015 * (1 - yNorm));
        position.setZ(i, Math.sin(theta) * newRadius + chestPush + lowerFold);
    }
    position.needsUpdate = true;
    geometry.computeVertexNormals();
    return geometry;
};

const sculptSleeveGeometry = (geometry) => {
    const position = geometry.attributes.position;
    for (let i = 0; i < position.count; i += 1) {
        const x = position.getX(i);
        const y = position.getY(i);
        const z = position.getZ(i);
        const radius = Math.max(Math.sqrt(x * x + z * z), 0.0001);
        const theta = Math.atan2(z, x);
        const yNorm = (y + 0.55) / 1.1;
        const taper = 0.9 + yNorm * 0.24;
        const wrinkles = Math.sin(theta * 3.6 + yNorm * 7.8) * 0.06;
        const newRadius = radius * Math.max(0.7, taper + wrinkles);

        const bendForward = yNorm * yNorm * 0.22;
        const sag = yNorm * -0.07;
        position.setX(i, Math.cos(theta) * newRadius);
        position.setY(i, y + sag);
        position.setZ(i, Math.sin(theta) * newRadius + bendForward);
    }
    position.needsUpdate = true;
    geometry.computeVertexNormals();
    return geometry;
};

const createMannequinModel = () => {
    const group = new THREE.Group();
    group.name = 'mannequin-group';
    const mat = new THREE.MeshPhysicalMaterial({
        color: '#090b10',
        roughness: 0.28,
        metalness: 0.62,
        clearcoat: 0.22,
        clearcoatRoughness: 0.24,
        envMapIntensity: 1.45,
    });

    const addPart = (mesh) => {
        mesh.userData.kitRole = 'mannequin';
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        group.add(mesh);
        return mesh;
    };

    const head = addPart(new THREE.Mesh(new THREE.SphereGeometry(0.28, 32, 24), mat));
    head.position.set(0, 1.88, 0.06);
    head.scale.set(0.9, 1.08, 0.9);

    const neck = addPart(new THREE.Mesh(new THREE.CylinderGeometry(0.115, 0.13, 0.23, 24), mat));
    neck.position.set(0, 1.58, 0.04);

    const torso = addPart(new THREE.Mesh(new THREE.CylinderGeometry(0.62, 0.78, 2.14, 36, 20), mat));
    torso.position.set(0, 0.2, 0.04);

    const pelvis = addPart(new THREE.Mesh(new THREE.SphereGeometry(0.48, 28, 18), mat));
    pelvis.position.set(0, -0.94, 0.05);
    pelvis.scale.set(1.04, 0.72, 0.85);

    const upperLegGeometry = new THREE.CylinderGeometry(0.18, 0.19, 0.95, 22, 12);
    const lowerLegGeometry = new THREE.CylinderGeometry(0.13, 0.15, 0.98, 22, 12);
    const footGeometry = new THREE.SphereGeometry(0.16, 18, 12);

    const leftUpper = addPart(new THREE.Mesh(upperLegGeometry, mat));
    leftUpper.position.set(-0.25, -1.42, 0.04);
    leftUpper.rotation.x = 0.03;
    const rightUpper = addPart(new THREE.Mesh(upperLegGeometry, mat));
    rightUpper.position.set(0.25, -1.42, 0.04);
    rightUpper.rotation.x = 0.03;

    const leftLower = addPart(new THREE.Mesh(lowerLegGeometry, mat));
    leftLower.position.set(-0.25, -2.26, 0.04);
    const rightLower = addPart(new THREE.Mesh(lowerLegGeometry, mat));
    rightLower.position.set(0.25, -2.26, 0.04);

    const leftFoot = addPart(new THREE.Mesh(footGeometry, mat));
    leftFoot.position.set(-0.25, -2.84, 0.18);
    leftFoot.scale.set(1.4, 0.72, 2.2);
    const rightFoot = addPart(new THREE.Mesh(footGeometry, mat));
    rightFoot.position.set(0.25, -2.84, 0.18);
    rightFoot.scale.set(1.4, 0.72, 2.2);

    return group;
};

const curvedPanelGeometry = (width, height, curvature = 0.08, segments = 36) => {
    const geometry = new THREE.PlaneGeometry(width, height, segments, 1);
    const position = geometry.attributes.position;
    const halfWidth = Math.max(width / 2, 0.0001);
    for (let i = 0; i < position.count; i += 1) {
        const x = position.getX(i);
        const normalized = x / halfWidth;
        const depth = Math.cos(normalized * Math.PI * 0.5) * curvature - curvature;
        position.setZ(i, depth);
    }
    position.needsUpdate = true;
    geometry.computeVertexNormals();
    return geometry;
};

export const mountJerseyDesigner = () => {
    if (!root || !fabricLib) return;

    createApp({
        setup() {
            const threeMountRef = ref(null);
            const frontCanvasRef = ref(null);
            const backCanvasRef = ref(null);
            const sleeveCanvasRef = ref(null);
            const jerseys = ref([...defaults]);
            const selectedJerseyId = ref(defaults[0].id);
            const selectedTemplate = ref('solid');
            const activeView = ref('front');
            const activeArtboard = ref('front');
            const activeWorkspace = ref('editor');
            const renderStyle = ref('wireframe');
            const loadDesignId = ref('');
            const saveStatus = ref('Ready');
            const mockupStatus = ref('');
            const lightingMode = ref('studio');
            const sizeOptions = ['S', 'M', 'L', 'XL', 'XXL'];
            const sportsFonts = ref(['Nunito Sans', 'Rajdhani', 'Space Grotesk', 'Bebas Neue', 'Montserrat', 'Oswald']);
            const sizeQuantities = reactive({ S: 0, M: 0, L: 0, XL: 0, XXL: 0 });
            const orderCustomer = reactive({ name: '', email: '', phone: '' });
            const savedDesign = reactive({ id: null, public_id: null });
            const colors = reactive({ body: '#0f172a', sleeve: '#1f2937', collar: '#f8fafc' });
            const player = reactive({ name: '', number: '', fontFamily: 'Nunito Sans', size: 118, spacing: 90, color: '#ffffff', outline: '#111827' });
            const fabrics = reactive({ front: null, back: null, sleeve: null });
            const history = reactive({ front: { stack: [], index: -1 }, back: { stack: [], index: -1 }, sleeve: { stack: [], index: -1 } });
            const previewImages = reactive({ front: '', back: '', sleeve: '', three: '' });

            let scene, camera, renderer, controls, stadiumLight, keyLight, fillLight, rimLight, floorMesh, floorGlow, jerseyGroup;
            let bodyMat, sleeveMat, collarMat, collarInnerMat, cuffMat, sidePanelMat, seamMat, raglanMat, chevronMat, frontTex, backTex, sleeveTex, activePatternTex, frameId, autosaveId;
            let targetY = 0;
            const selectedJersey = computed(() => jerseys.value.find((j) => String(j.id) === String(selectedJerseyId.value)) || jerseys.value[0]);
            const supportsSleeves = computed(() => Boolean(selectedJersey.value?.supports_sleeves));
            const availableViews = computed(() => selectedJersey.value?.metadata?.views || (supportsSleeves.value ? ['front', 'back', 'sleeve_left', 'sleeve_right'] : ['front', 'back']));
            const availableArtboards = computed(() => (supportsSleeves.value ? ['front', 'back', 'sleeve'] : ['front', 'back']));
            const logoCount = computed(() => ['front', 'back', 'sleeve'].map((k) => fabrics[k]).filter(Boolean).reduce((n, c) => n + c.getObjects().filter((o) => o._layerType === 'logo' || o.type === 'image').length, 0));
            const hasCustomPlayer = computed(() => (player.name || '').trim() !== '' || (player.number || '').trim() !== '');
            const unitPrice = computed(() => Number(selectedJersey.value?.base_price || 0) + logoCount.value * Number(BOOT.pricing?.extra_logo_fee || 120) + (hasCustomPlayer.value ? Number(BOOT.pricing?.custom_name_number_fee || 180) : 0));
            const totalQuantity = computed(() => sizeOptions.reduce((n, size) => n + Math.max(0, Number(sizeQuantities[size] || 0)), 0));
            const totalPrice = computed(() => unitPrice.value * totalQuantity.value);
            const canUndo = computed(() => (history[activeArtboard.value]?.index || 0) > 0);
            const canRedo = computed(() => {
                const h = history[activeArtboard.value];
                return h && h.index < h.stack.length - 1;
            });
            const lightingModeLabel = computed(() => (lightingMode.value === 'studio' ? 'Studio' : 'Stadium'));
            const renderStyleLabel = computed(() => (renderStyle.value === 'wireframe' ? 'Wireframe' : 'Realistic'));
            const prettyArea = (a) => ({ front: 'Front', back: 'Back', sleeve: 'Sleeve', sleeve_left: 'Sleeve Left', sleeve_right: 'Sleeve Right' }[a] || 'Sleeve');
            const activeCanvas = () => fabrics[activeArtboard.value];
            const removeWireOverlay = (mesh) => {
                const overlay = mesh?.userData?.wireOverlay;
                if (!overlay) return;
                mesh.remove(overlay);
                if (overlay.material?.dispose) overlay.material.dispose();
                mesh.userData.wireOverlay = null;
            };
            const addWireOverlay = (mesh) => {
                if (!mesh || !mesh.geometry || mesh.userData?.wireOverlay) return;
                const overlay = new THREE.Mesh(
                    mesh.geometry,
                    new THREE.MeshBasicMaterial({
                        color: '#f5f5ef',
                        wireframe: true,
                        transparent: true,
                        opacity: 0.86,
                        depthWrite: false,
                        polygonOffset: true,
                        polygonOffsetFactor: -2,
                        polygonOffsetUnits: -2,
                    })
                );
                overlay.name = 'wire-overlay';
                overlay.userData.isWireOverlay = true;
                overlay.castShadow = false;
                overlay.receiveShadow = false;
                overlay.renderOrder = 20;
                mesh.userData.wireOverlay = overlay;
                mesh.add(overlay);
            };
            const applyRenderStyle = () => {
                const isWireframe = renderStyle.value === 'wireframe';
                if (scene) {
                    scene.background = isWireframe ? wireframeBackgroundTexture() || new THREE.Color('#23272f') : studioBackgroundTexture() || new THREE.Color('#0b1026');
                }
                if (jerseyGroup) {
                    jerseyGroup.traverse((node) => {
                        if (!node.isMesh) return;
                        if (node.userData?.isWireOverlay) return;
                        if (node.userData.baseVisible === undefined) node.userData.baseVisible = node.visible;
                        if (node.userData.originalMaterial === undefined) node.userData.originalMaterial = node.material;
                        const isHiddenRole = ['decal', 'mannequin', 'detail'].includes(node.userData?.kitRole);

                        if (isWireframe) {
                            node.visible = isHiddenRole ? false : true;
                            if (!isHiddenRole) {
                                if (!node.userData.wireFillMaterial) {
                                    node.userData.wireFillMaterial = new THREE.MeshStandardMaterial({
                                        color: '#9d9d94',
                                        roughness: 0.7,
                                        metalness: 0.02,
                                    });
                                }
                                node.material = node.userData.wireFillMaterial;
                                addWireOverlay(node);
                            } else {
                                removeWireOverlay(node);
                            }
                        } else {
                            node.visible = node.userData.baseVisible ?? true;
                            if (node.userData.originalMaterial) node.material = node.userData.originalMaterial;
                            removeWireOverlay(node);
                        }
                    });
                }
                if (camera && controls) {
                    if (isWireframe) {
                        camera.position.set(0.72, 0.46, 6.9);
                        controls.target.set(0, -0.58, 0.02);
                    } else {
                        camera.position.set(0, 0.58, 6.35);
                        controls.target.set(0, 0.18, 0);
                    }
                    controls.update();
                }
                setLightMode();
                if (activeWorkspace.value === 'preview') {
                    refreshPreviewTab();
                }
            };
            const toggleRenderStyle = () => {
                renderStyle.value = renderStyle.value === 'wireframe' ? 'realistic' : 'wireframe';
                applyRenderStyle();
                persistLocal();
            };
            const refreshPreviewTab = () => {
                const toPreview = (canvas) => {
                    if (!canvas) return '';
                    try {
                        return canvas.toDataURL({ format: 'png', multiplier: 0.35, enableRetinaScaling: false });
                    } catch {
                        return '';
                    }
                };

                previewImages.front = toPreview(fabrics.front);
                previewImages.back = toPreview(fabrics.back);
                previewImages.sleeve = toPreview(fabrics.sleeve);

                if (renderer && scene && camera) {
                    try {
                        renderer.render(scene, camera);
                        previewImages.three = renderer.domElement.toDataURL('image/png');
                    } catch {
                        previewImages.three = '';
                    }
                }
            };
            const setWorkspace = (workspace) => {
                const nextSpace = workspace === 'preview' ? 'preview' : 'editor';
                if (nextSpace === 'preview') {
                    refreshPreviewTab();
                }
                activeWorkspace.value = nextSpace;
                if (nextSpace === 'editor') {
                    nextTick(() => onResize());
                }
            };
            const modelCandidates = (jersey) => {
                const seen = new Set();
                const urls = [];
                const push = (value) => {
                    const raw = String(value || '').trim();
                    if (!raw) return;
                    const normalized = raw.startsWith('/') ? raw : `/${raw}`;
                    if (seen.has(normalized)) return;
                    seen.add(normalized);
                    urls.push(normalized);
                };

                if (!jersey) return urls;
                push(jersey.model_url);
                const metadata = jersey.metadata && typeof jersey.metadata === 'object' ? jersey.metadata : {};
                if (Array.isArray(metadata.model_urls)) metadata.model_urls.forEach(push);
                if (typeof metadata.model_url === 'string') push(metadata.model_url);
                if (jersey.slug) {
                    push(`/models/jerseys/${jersey.slug}.glb`);
                    push(`/models/jerseys/${jersey.slug}.gltf`);
                }
                return urls;
            };

            const persistLocal = () => {
                try {
                    localStorage.setItem(LOCAL_KEY, JSON.stringify(buildPayload()));
                } catch {}
            };
            const restoreLocal = () => {
                try {
                    return JSON.parse(localStorage.getItem(LOCAL_KEY) || 'null');
                } catch {
                    return null;
                }
            };

            const snapHistory = (area) => {
                const c2 = fabrics[area];
                if (!c2) return;
                const h = history[area];
                const state = JSON.stringify(c2.toJSON(['_layerType', '_layerName']));
                if (state === h.stack[h.index]) return;
                h.stack = h.stack.slice(0, h.index + 1);
                h.stack.push(state);
                h.index = h.stack.length - 1;
            };
            const applyHistory = (area, index) => {
                const c2 = fabrics[area];
                const h = history[area];
                if (!c2 || !h || index < 0 || index >= h.stack.length) return;
                h.index = index;
                c2.loadFromJSON(h.stack[index], () => {
                    ensureTransparentCanvas(c2);
                    c2.renderAll();
                    syncTextures();
                });
            };
            const attachGuides = (canvas) => {
                canvas.on('object:moving', (event) => {
                    const obj = event.target;
                    if (!obj) return;
                    const cp = obj.getCenterPoint();
                    if (Math.abs(cp.x - canvas.width / 2) <= 14) cp.x = canvas.width / 2;
                    if (Math.abs(cp.y - canvas.height / 2) <= 14) cp.y = canvas.height / 2;
                    obj.setPositionByOrigin(cp, 'center', 'center');
                    canvas.clearContext(canvas.contextTop);
                    const t = canvas.contextTop;
                    if (cp.x === canvas.width / 2) {
                        t.strokeStyle = 'rgba(225,29,46,.85)';
                        t.beginPath();
                        t.moveTo(cp.x, 0);
                        t.lineTo(cp.x, canvas.height);
                        t.stroke();
                    }
                    if (cp.y === canvas.height / 2) {
                        t.strokeStyle = 'rgba(225,29,46,.85)';
                        t.beginPath();
                        t.moveTo(0, cp.y);
                        t.lineTo(canvas.width, cp.y);
                        t.stroke();
                    }
                });
                canvas.on('mouse:up', () => canvas.clearContext(canvas.contextTop));
            };
            const ensureTransparentCanvas = (canvas) => {
                if (!canvas) return;
                canvas.backgroundColor = 'rgba(0,0,0,0)';
            };
            const createCanvas = (el, area) => {
                const c2 = new fabricLib.Canvas(el, { width: 1024, height: 1024, preserveObjectStacking: true, selection: true });
                ensureTransparentCanvas(c2);
                attachGuides(c2);
                ['object:added', 'object:modified', 'object:removed'].forEach((evt) =>
                    c2.on(evt, () => {
                        snapHistory(area);
                        syncTextures();
                        persistLocal();
                    })
                );
                snapHistory(area);
                return c2;
            };

            const syncTextures = () => {
                if (frontTex) frontTex.needsUpdate = true;
                if (backTex) backTex.needsUpdate = true;
                if (sleeveTex) sleeveTex.needsUpdate = true;
                if (activeWorkspace.value === 'preview') {
                    refreshPreviewTab();
                }
            };
            const applyMaterials = () => {
                if (!bodyMat || !sleeveMat || !collarMat) return;
                bodyMat.color = new THREE.Color(colors.body);
                sleeveMat.color = new THREE.Color(colors.sleeve);
                collarMat.color = new THREE.Color(colors.collar);
                if (collarInnerMat) collarInnerMat.color = new THREE.Color(darken(colors.collar, 0.22));
                if (cuffMat) cuffMat.color = new THREE.Color(darken(colors.sleeve, 0.16));
                if (sidePanelMat) sidePanelMat.color = new THREE.Color(darken(colors.body, 0.18));
                if (seamMat) seamMat.color = new THREE.Color(lighten(colors.body, 0.2));
                if (raglanMat) raglanMat.color = new THREE.Color(lighten(colors.sleeve, 0.08));
                if (chevronMat) chevronMat.color = new THREE.Color(lighten(colors.collar, 0.06));
                if (selectedTemplate.value === 'solid') {
                    bodyMat.map = null;
                } else {
                    if (activePatternTex) activePatternTex.dispose();
                    activePatternTex = new THREE.CanvasTexture(patternCanvas(selectedTemplate.value, colors.body));
                    activePatternTex.wrapS = THREE.RepeatWrapping;
                    activePatternTex.wrapT = THREE.RepeatWrapping;
                    activePatternTex.repeat.set(1.7, 1.7);
                    bodyMat.map = activePatternTex;
                }
                bodyMat.needsUpdate = true;
                sleeveMat.needsUpdate = true;
                collarMat.needsUpdate = true;
                if (activeWorkspace.value === 'preview') {
                    refreshPreviewTab();
                }
            };
            const createDecalMaterial = (map) =>
                new THREE.MeshStandardMaterial({
                    map,
                    alphaMap: map,
                    transparent: true,
                    alphaTest: 0.02,
                    depthWrite: false,
                    polygonOffset: true,
                    polygonOffsetFactor: -1,
                    polygonOffsetUnits: -1,
                    roughness: 0.92,
                    metalness: 0,
                    side: THREE.DoubleSide,
                });

            const mountDecals = (container, bounds) => {
                const size = bounds.getSize(new THREE.Vector3());
                const center = bounds.getCenter(new THREE.Vector3());

                const torsoWidth = size.x * 0.54;
                const torsoHeight = size.y * 0.56;
                const torsoCurvature = Math.max(size.x * 0.06, 0.04);
                const front = new THREE.Mesh(curvedPanelGeometry(torsoWidth, torsoHeight, torsoCurvature), createDecalMaterial(frontTex));
                const back = new THREE.Mesh(curvedPanelGeometry(torsoWidth, torsoHeight, torsoCurvature), createDecalMaterial(backTex));
                front.userData.kitRole = 'decal';
                back.userData.kitRole = 'decal';
                front.position.set(center.x, center.y + size.y * 0.03, bounds.max.z + 0.018);
                back.position.set(center.x, center.y + size.y * 0.03, bounds.min.z - 0.018);
                back.rotation.y = Math.PI;

                const sleeveWidth = size.x * 0.2;
                const sleeveHeight = size.y * 0.24;
                const sleeveCurve = Math.max(size.x * 0.03, 0.02);
                const sleeveLeft = new THREE.Mesh(curvedPanelGeometry(sleeveWidth, sleeveHeight, sleeveCurve, 24), createDecalMaterial(sleeveTex));
                const sleeveRight = new THREE.Mesh(curvedPanelGeometry(sleeveWidth, sleeveHeight, sleeveCurve, 24), createDecalMaterial(sleeveTex));
                sleeveLeft.userData.kitRole = 'decal';
                sleeveRight.userData.kitRole = 'decal';
                sleeveLeft.position.set(bounds.min.x - 0.016, center.y + size.y * 0.02, center.z);
                sleeveLeft.rotation.y = Math.PI / 2;
                sleeveRight.position.set(bounds.max.x + 0.016, center.y + size.y * 0.02, center.z);
                sleeveRight.rotation.y = -Math.PI / 2;

                container.add(front);
                container.add(back);
                if (supportsSleeves.value) {
                    container.add(sleeveLeft);
                    container.add(sleeveRight);
                }
            };
            const fallbackModel = () => {
                const weave = weaveTexture();
                const clothDetail = clothDetailTexture();
                [weave, clothDetail].filter(Boolean).forEach((texture) => {
                    texture.anisotropy = 8;
                });

                bodyMat = new THREE.MeshPhysicalMaterial({
                    color: colors.body,
                    roughness: 0.78,
                    metalness: 0.02,
                    bumpMap: weave || null,
                    bumpScale: 0.028,
                    roughnessMap: clothDetail || null,
                    clearcoat: 0.06,
                    clearcoatRoughness: 0.86,
                    sheen: 0.72,
                    sheenColor: new THREE.Color(lighten(colors.body, 0.35)),
                    envMapIntensity: 0.95,
                });
                sleeveMat = new THREE.MeshPhysicalMaterial({
                    color: colors.sleeve,
                    roughness: 0.8,
                    metalness: 0.02,
                    bumpMap: weave || null,
                    bumpScale: 0.024,
                    roughnessMap: clothDetail || null,
                    clearcoat: 0.04,
                    clearcoatRoughness: 0.88,
                    sheen: 0.62,
                    sheenColor: new THREE.Color(lighten(colors.sleeve, 0.3)),
                    envMapIntensity: 0.85,
                });
                collarMat = new THREE.MeshPhysicalMaterial({
                    color: colors.collar,
                    roughness: 0.5,
                    metalness: 0.05,
                    bumpMap: weave || null,
                    bumpScale: 0.012,
                    clearcoat: 0.16,
                    clearcoatRoughness: 0.58,
                    sheen: 0.32,
                    sheenColor: new THREE.Color('#ffffff'),
                    envMapIntensity: 0.9,
                });
                cuffMat = sleeveMat.clone();
                cuffMat.color = new THREE.Color(darken(colors.sleeve, 0.16));
                cuffMat.roughness = 0.66;
                cuffMat.clearcoat = 0.08;
                cuffMat.clearcoatRoughness = 0.74;
                cuffMat.sheen = 0.38;
                raglanMat = new THREE.MeshPhysicalMaterial({
                    color: lighten(colors.sleeve, 0.08),
                    roughness: 0.64,
                    metalness: 0.03,
                    bumpMap: weave || null,
                    bumpScale: 0.012,
                    clearcoat: 0.08,
                    clearcoatRoughness: 0.7,
                    sheen: 0.45,
                    sheenColor: new THREE.Color(lighten(colors.sleeve, 0.24)),
                });
                chevronMat = new THREE.MeshStandardMaterial({
                    color: lighten(colors.collar, 0.06),
                    roughness: 0.4,
                    metalness: 0.14,
                    transparent: true,
                    opacity: 0.88,
                });

                const rootGroup = new THREE.Group();
                const g = new THREE.Group();

                const addJerseyPart = (mesh, role = 'jersey') => {
                    mesh.castShadow = true;
                    mesh.receiveShadow = true;
                    mesh.userData.kitRole = role;
                    g.add(mesh);
                    return mesh;
                };

                const torsoGeometry = sculptTorsoGeometry(new THREE.CylinderGeometry(0.84, 1.18, 2.6, 72, 34, true));
                const torso = addJerseyPart(new THREE.Mesh(torsoGeometry, bodyMat));
                torso.position.y = 0.08;

                if (supportsSleeves.value) {
                    const sleeveGeometry = sculptSleeveGeometry(new THREE.CylinderGeometry(0.24, 0.44, 1.1, 40, 16, true));
                    const l = addJerseyPart(new THREE.Mesh(sleeveGeometry, sleeveMat), 'jersey');
                    l.position.set(-1.06, 0.82, -0.04);
                    l.rotation.z = Math.PI / 2.22;
                    l.rotation.y = 0.14;

                    const r = l.clone();
                    r.position.x = 1.06;
                    r.rotation.z = -Math.PI / 2.22;
                    r.rotation.y = -0.14;
                    addJerseyPart(r, 'jersey');

                    const cuffGeometry = new THREE.TorusGeometry(0.235, 0.042, 16, 46);
                    const cuffLeft = addJerseyPart(new THREE.Mesh(cuffGeometry, cuffMat), 'jersey');
                    cuffLeft.position.copy(l.position);
                    cuffLeft.rotation.set(Math.PI / 2, 0.28, Math.PI / 2.2);

                    const cuffRight = cuffLeft.clone();
                    cuffRight.position.copy(r.position);
                    cuffRight.rotation.set(Math.PI / 2, -0.28, -Math.PI / 2.2);
                    addJerseyPart(cuffRight, 'jersey');
                }

                const collarOuter = addJerseyPart(new THREE.Mesh(new THREE.TorusGeometry(0.28, 0.09, 24, 60, Math.PI * 2), collarMat), 'jersey');
                collarOuter.position.set(0, 1.38, 0.03);
                collarOuter.rotation.x = Math.PI / 2;

                const collarInner = addJerseyPart(new THREE.Mesh(
                    new THREE.TorusGeometry(0.18, 0.03, 16, 48, Math.PI * 2),
                    (collarInnerMat = new THREE.MeshStandardMaterial({
                        color: darken(colors.collar, 0.22),
                        roughness: 0.62,
                        metalness: 0.02,
                        bumpMap: weave || null,
                        bumpScale: 0.008,
                    }))
                ), 'jersey');
                collarInner.position.set(0, 1.35, 0.024);
                collarInner.rotation.x = Math.PI / 2;

                const hemBand = addJerseyPart(new THREE.Mesh(new THREE.TorusGeometry(1.04, 0.047, 16, 72), cuffMat), 'jersey');
                hemBand.position.set(0, -1.18, 0.01);
                hemBand.rotation.x = Math.PI / 2;

                sidePanelMat = new THREE.MeshStandardMaterial({
                    color: darken(colors.body, 0.18),
                    roughness: 0.76,
                    metalness: 0.01,
                    bumpMap: weave || null,
                    bumpScale: 0.016,
                    transparent: true,
                    opacity: 0.88,
                });
                const sidePanelGeom = new THREE.PlaneGeometry(0.22, 2.1, 16, 28);
                const sideLeft = addJerseyPart(new THREE.Mesh(sidePanelGeom, sidePanelMat), 'detail');
                sideLeft.position.set(-0.93, -0.02, 0);
                sideLeft.rotation.y = Math.PI / 2.25;
                const sideRight = sideLeft.clone();
                sideRight.position.x = 0.93;
                sideRight.rotation.y = -Math.PI / 2.25;
                addJerseyPart(sideRight, 'detail');

                seamMat = new THREE.MeshStandardMaterial({
                    color: lighten(colors.body, 0.2),
                    roughness: 0.44,
                    metalness: 0.08,
                    transparent: true,
                    opacity: 0.46,
                });
                const seamLeft = addJerseyPart(new THREE.Mesh(new THREE.TorusGeometry(0.22, 0.012, 8, 36, Math.PI), seamMat), 'detail');
                seamLeft.position.set(-0.91, 1.0, 0.03);
                seamLeft.rotation.set(Math.PI / 2.0, Math.PI / 2.12, 0.2);
                const seamRight = seamLeft.clone();
                seamRight.position.x = 0.91;
                seamRight.rotation.y = -Math.PI / 2.12;
                seamRight.rotation.z = -0.2;
                addJerseyPart(seamRight, 'detail');

                if (supportsSleeves.value) {
                    const raglanGeom = new THREE.PlaneGeometry(0.5, 0.84, 1, 1);
                    const raglanLeftFront = addJerseyPart(new THREE.Mesh(raglanGeom, raglanMat), 'detail');
                    raglanLeftFront.position.set(-0.42, 0.97, 0.58);
                    raglanLeftFront.rotation.set(0.06, 0.42, -0.66);
                    const raglanRightFront = raglanLeftFront.clone();
                    raglanRightFront.position.set(0.42, 0.97, 0.58);
                    raglanRightFront.rotation.set(0.06, -0.42, 0.66);
                    addJerseyPart(raglanRightFront, 'detail');

                    const raglanLeftBack = raglanLeftFront.clone();
                    raglanLeftBack.position.set(-0.42, 0.97, -0.58);
                    raglanLeftBack.rotation.set(-0.06, Math.PI - 0.42, 0.66);
                    addJerseyPart(raglanLeftBack, 'detail');
                    const raglanRightBack = raglanLeftFront.clone();
                    raglanRightBack.position.set(0.42, 0.97, -0.58);
                    raglanRightBack.rotation.set(-0.06, -Math.PI + 0.42, -0.66);
                    addJerseyPart(raglanRightBack, 'detail');
                }

                const chevronGeom = new THREE.BoxGeometry(0.36, 0.048, 0.018);
                const chevLeftFront = addJerseyPart(new THREE.Mesh(chevronGeom, chevronMat), 'detail');
                chevLeftFront.position.set(-0.18, 1.03, 0.61);
                chevLeftFront.rotation.z = 0.72;
                const chevRightFront = chevLeftFront.clone();
                chevRightFront.position.x = 0.18;
                chevRightFront.rotation.z = -0.72;
                addJerseyPart(chevRightFront, 'detail');

                mountDecals(g, new THREE.Box3().setFromObject(g));

                const shortMat = new THREE.MeshPhysicalMaterial({
                    color: lighten(colors.body, 0.12),
                    roughness: 0.74,
                    metalness: 0.02,
                    bumpMap: weave || null,
                    bumpScale: 0.018,
                    roughnessMap: clothDetail || null,
                    sheen: 0.4,
                    sheenColor: new THREE.Color(lighten(colors.body, 0.24)),
                });
                const sockMat = new THREE.MeshPhysicalMaterial({
                    color: '#f3f4f6',
                    roughness: 0.66,
                    metalness: 0,
                    bumpMap: weave || null,
                    bumpScale: 0.01,
                    roughnessMap: clothDetail || null,
                });
                const bootMat = new THREE.MeshPhysicalMaterial({
                    color: '#5bf0e7',
                    roughness: 0.34,
                    metalness: 0.1,
                    clearcoat: 0.3,
                    clearcoatRoughness: 0.22,
                });

                const addSymmetricPair = (baseMesh, role, leftX, rightX, y, z, scaleX = 1, scaleY = 1, scaleZ = 1) => {
                    const left = addJerseyPart(baseMesh, role);
                    left.position.set(leftX, y, z);
                    left.scale.set(scaleX, scaleY, scaleZ);
                    const right = baseMesh.clone();
                    addJerseyPart(right, role);
                    right.position.set(rightX, y, z);
                    right.scale.set(scaleX, scaleY, scaleZ);
                    return [left, right];
                };

                const shortsLeg = new THREE.Mesh(new THREE.CylinderGeometry(0.37, 0.42, 0.95, 32, 14, true), shortMat);
                addSymmetricPair(shortsLeg, 'shorts', -0.28, 0.28, -1.66, 0.01, 0.92, 1, 0.92);
                const shortsWaist = addJerseyPart(new THREE.Mesh(new THREE.TorusGeometry(0.56, 0.06, 16, 60), shortMat), 'shorts');
                shortsWaist.position.set(0, -1.15, 0.02);
                shortsWaist.rotation.x = Math.PI / 2;

                const sock = new THREE.Mesh(new THREE.CylinderGeometry(0.17, 0.14, 1.05, 28, 12, true), sockMat);
                const [sockLeft, sockRight] = addSymmetricPair(sock, 'socks', -0.31, 0.29, -2.42, 0.03, 0.92, 1, 0.92);
                sockLeft.position.z = 0.07;
                sockRight.position.z = -0.03;

                const boot = new THREE.Mesh(new THREE.SphereGeometry(0.19, 26, 16), bootMat);
                const [bootLeft, bootRight] = addSymmetricPair(boot, 'boots', -0.3, 0.3, -3.03, 0.17, 1.45, 0.66, 2.05);
                bootLeft.position.set(-0.35, -3.03, 0.2);
                bootRight.position.set(0.27, -2.98, -0.06);
                bootLeft.rotation.set(-0.04, 0.24, 0.03);
                bootRight.rotation.set(-0.04, -0.26, -0.02);

                rootGroup.add(g);
                rootGroup.position.y = 0.02;
                return rootGroup;
            };
            const loadModel = async (jersey) => {
                if (jerseyGroup && scene) scene.remove(jerseyGroup);
                const candidates = modelCandidates(jersey);
                const loader = new GLTFLoader();

                for (const modelUrl of candidates) {
                    try {
                        const gltf = await loader.loadAsync(modelUrl);
                        const g = gltf.scene || gltf.scenes?.[0];
                        if (!g) continue;
                        bodyMat = null;
                        sleeveMat = null;
                        collarMat = null;
                        collarInnerMat = null;
                        cuffMat = null;
                        sidePanelMat = null;
                        seamMat = null;
                        raglanMat = null;
                        chevronMat = null;
                        g.scale.setScalar(1.4);
                        g.position.y = -0.25;
                        g.traverse((n) => {
                            if (!n.isMesh) return;
                            n.castShadow = true;
                            n.receiveShadow = true;
                            n.userData.kitRole = n.userData.kitRole || 'jersey';
                            const nm = (n.name || '').toLowerCase();
                            if (nm.includes('sleeve')) sleeveMat = n.material;
                            else if (nm.includes('collar') || nm.includes('neck')) collarMat = n.material;
                            else bodyMat = n.material;
                        });
                        bodyMat ||= new THREE.MeshStandardMaterial({ color: colors.body });
                        sleeveMat ||= bodyMat;
                        collarMat ||= bodyMat;
                        mountDecals(g, new THREE.Box3().setFromObject(g));
                        jerseyGroup = g;
                        scene.add(jerseyGroup);
                        mockupStatus.value = '';
                        applyMaterials();
                        applyRenderStyle();
                        return;
                    } catch {
                        // Try the next candidate path.
                    }
                }

                jerseyGroup = fallbackModel();
                scene.add(jerseyGroup);
                mockupStatus.value = 'Built-in realistic mockup active. Add GLB files in /public/models/jerseys for exact product geometry.';
                applyMaterials();
                applyRenderStyle();
            };
            const setLightMode = () => {
                if (!keyLight || !fillLight || !rimLight || !stadiumLight || !floorMesh) return;
                if (renderStyle.value === 'wireframe') {
                    keyLight.intensity = 1.74;
                    fillLight.intensity = 1.08;
                    rimLight.intensity = 0.95;
                    stadiumLight.intensity = 0.3;
                    floorMesh.material.color = new THREE.Color('#343a45');
                    floorMesh.material.opacity = 0.82;
                    if (floorGlow) floorGlow.visible = false;
                    return;
                }

                if (lightingMode.value === 'studio') {
                    keyLight.intensity = 1.55;
                    fillLight.intensity = 1.02;
                    rimLight.intensity = 1.08;
                    stadiumLight.intensity = 0.28;
                    floorMesh.material.color = new THREE.Color('#1f2937');
                    floorMesh.material.opacity = 0.32;
                    if (floorGlow) {
                        floorGlow.visible = true;
                        floorGlow.material.opacity = 0.34;
                    }
                } else {
                    keyLight.intensity = 2.28;
                    fillLight.intensity = 1.46;
                    rimLight.intensity = 1.34;
                    stadiumLight.intensity = 0.96;
                    floorMesh.material.color = new THREE.Color('#1f2937');
                    floorMesh.material.opacity = 0.65;
                    if (floorGlow) {
                        floorGlow.visible = true;
                        floorGlow.material.opacity = 0.58;
                    }
                }
            };
            const focusView = (view) => {
                if (view === 'front') targetY = -0.18;
                else if (view === 'back') targetY = Math.PI - 0.42;
                else if (view === 'sleeve_left') targetY = Math.PI / 2 - 0.16;
                else if (view === 'sleeve_right') targetY = -Math.PI / 2 - 0.16;
            };
            const setView = (view) => {
                activeView.value = view;
                activeArtboard.value = view === 'front' || view === 'back' ? view : 'sleeve';
                focusView(view);
                if (activeWorkspace.value === 'preview') {
                    refreshPreviewTab();
                }
            };
            const onResize = () => {
                if (!renderer || !camera || !threeMountRef.value) return;
                const w = threeMountRef.value.clientWidth;
                const h = threeMountRef.value.clientHeight;
                renderer.setSize(w, h);
                camera.aspect = w / h;
                camera.updateProjectionMatrix();
            };
            const initThree = async () => {
                const m = threeMountRef.value;
                if (!m) return;
                scene = new THREE.Scene();
                scene.background = renderStyle.value === 'wireframe' ? wireframeBackgroundTexture() || new THREE.Color('#23272f') : studioBackgroundTexture() || new THREE.Color('#0b1026');
                camera = new THREE.PerspectiveCamera(42, m.clientWidth / m.clientHeight, 0.1, 100);
                camera.position.set(0, 0.58, 6.35);
                renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, preserveDrawingBuffer: true });
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
                renderer.setSize(m.clientWidth, m.clientHeight);
                renderer.outputColorSpace = THREE.SRGBColorSpace;
                renderer.toneMapping = THREE.ACESFilmicToneMapping;
                renderer.toneMappingExposure = 1.14;
                renderer.shadowMap.enabled = true;
                renderer.shadowMap.type = THREE.PCFSoftShadowMap;
                m.innerHTML = '';
                m.appendChild(renderer.domElement);
                const pmrem = new THREE.PMREMGenerator(renderer);
                scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
                controls = new OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true;
                controls.enablePan = false;
                controls.maxDistance = 11.5;
                controls.minDistance = 2.6;
                controls.minPolarAngle = 0.24;
                controls.maxPolarAngle = Math.PI / 1.37;
                controls.target.set(0, 0.18, 0);
                keyLight = new THREE.DirectionalLight('#fff', 1.2);
                keyLight.position.set(3.4, 4.8, 4.2);
                keyLight.castShadow = true;
                keyLight.shadow.mapSize.width = 2048;
                keyLight.shadow.mapSize.height = 2048;
                scene.add(keyLight);
                fillLight = new THREE.DirectionalLight('#9ec5ff', 0.8);
                fillLight.position.set(-3.2, 2.2, -3.4);
                scene.add(fillLight);
                rimLight = new THREE.DirectionalLight('#8fb3ff', 0.72);
                rimLight.position.set(0.8, 3.1, -4.8);
                scene.add(rimLight);
                stadiumLight = new THREE.HemisphereLight('#b5d0ff', '#24344b', 0.35);
                scene.add(stadiumLight);
                floorMesh = new THREE.Mesh(new THREE.CircleGeometry(8.4, 64), new THREE.MeshStandardMaterial({ color: '#1f2937', transparent: true, opacity: 0.32, roughness: 0.75, metalness: 0.1 }));
                floorMesh.rotation.x = -Math.PI / 2;
                floorMesh.position.y = -2.95;
                floorMesh.receiveShadow = true;
                scene.add(floorMesh);
                floorGlow = new THREE.Mesh(
                    new THREE.CircleGeometry(6.2, 64),
                    new THREE.MeshBasicMaterial({
                        map: floorShadowTexture(),
                        transparent: true,
                        opacity: 0.34,
                        depthWrite: false,
                    })
                );
                floorGlow.rotation.x = -Math.PI / 2;
                floorGlow.position.y = -2.91;
                scene.add(floorGlow);
                await loadModel(selectedJersey.value);
                setLightMode();
                const tick = () => {
                    frameId = window.requestAnimationFrame(tick);
                    if (jerseyGroup) jerseyGroup.rotation.y += (targetY - jerseyGroup.rotation.y) * 0.08;
                    controls?.update();
                    renderer?.render(scene, camera);
                };
                tick();
                window.addEventListener('resize', onResize);
            };

            const buildPayload = () => ({
                design_id: savedDesign.id,
                jersey_id: selectedJersey.value?.id,
                design_name: `${selectedJersey.value?.name || 'Jersey'} Design`,
                jersey_type: selectedJersey.value?.jersey_type || 'football_short',
                colors: { ...colors },
                patterns: { template: selectedTemplate.value },
                text_layers: { ...player },
                logos: { count: logoCount.value },
                positions: { active_view: activeView.value },
                artboards: {
                    front: fabrics.front ? fabrics.front.toJSON(['_layerType', '_layerName']) : null,
                    back: fabrics.back ? fabrics.back.toJSON(['_layerType', '_layerName']) : null,
                    sleeve: fabrics.sleeve ? fabrics.sleeve.toJSON(['_layerType', '_layerName']) : null,
                },
                extras: { sizes: { ...sizeQuantities }, customer: { ...orderCustomer }, active_artboard: activeArtboard.value, render_style: renderStyle.value },
            });
            const applyPayload = (payload) => {
                if (!payload) return;
                Object.assign(colors, payload.colors || {});
                selectedTemplate.value = payload.patterns?.template || selectedTemplate.value;
                Object.assign(player, payload.text_layers || {});
                if (payload.extras?.render_style) renderStyle.value = payload.extras.render_style === 'wireframe' ? 'wireframe' : 'realistic';
                if (payload.extras?.sizes) sizeOptions.forEach((s) => (sizeQuantities[s] = Number(payload.extras.sizes[s] || 0)));
                if (payload.extras?.customer) Object.assign(orderCustomer, payload.extras.customer);
                applyMaterials();
                applyRenderStyle();
                const loadJson = (area, json) =>
                    new Promise((resolve) => {
                        if (!json || !fabrics[area]) return resolve();
                        fabrics[area].loadFromJSON(json, () => {
                            ensureTransparentCanvas(fabrics[area]);
                            fabrics[area].renderAll();
                            snapHistory(area);
                            resolve();
                        });
                    });
                Promise.all([loadJson('front', payload.artboards?.front), loadJson('back', payload.artboards?.back), loadJson('sleeve', payload.artboards?.sleeve)]).then(syncTextures);
            };

            const saveDesign = async (autosave = false) => {
                const payload = buildPayload();
                payload.autosave = Boolean(autosave);
                saveStatus.value = autosave ? 'Autosaving...' : 'Saving design...';
                try {
                    const res = await api.post(BOOT.save_url || '/design/save', payload);
                    savedDesign.id = res?.data?.id || savedDesign.id;
                    savedDesign.public_id = res?.data?.public_id || savedDesign.public_id;
                    saveStatus.value = autosave ? `Autosaved ${new Date().toLocaleTimeString()}` : `Saved ${savedDesign.public_id || savedDesign.id}`;
                    if (!loadDesignId.value && savedDesign.public_id) loadDesignId.value = String(savedDesign.public_id);
                } catch {
                    saveStatus.value = autosave ? 'Autosave failed' : 'Save failed';
                }
            };
            const loadDesign = async () => {
                const id = (loadDesignId.value || '').trim();
                if (!id) return (saveStatus.value = 'Enter a design ID.');
                saveStatus.value = 'Loading design...';
                try {
                    const res = await api.get(`${BOOT.load_url_base || '/design'}/${encodeURIComponent(id)}`);
                    const d = res?.data;
                    if (!d) return (saveStatus.value = 'Design not found.');
                    savedDesign.id = d.id;
                    savedDesign.public_id = d.public_id;
                    applyPayload(d.payload || {});
                    saveStatus.value = `Loaded ${d.public_id || d.id}`;
                } catch {
                    saveStatus.value = 'Load failed.';
                }
            };

            const removeByTag = (canvas, tag) => canvas.getObjects().filter((o) => o._layerType === tag).forEach((o) => canvas.remove(o));
            const addText = (canvas, text, opts) => {
                const t = new fabricLib.Textbox(text, { left: opts.left, top: opts.top, originX: 'center', originY: 'center', fontFamily: opts.fontFamily, fontSize: opts.fontSize, charSpacing: opts.charSpacing, fill: opts.color, stroke: opts.outline, strokeWidth: 2, editable: true, textAlign: 'center', _layerType: opts.layerType });
                canvas.add(t);
                canvas.setActiveObject(t);
            };
            const applyPlayerText = () => {
                const f = fabrics.front;
                const b = fabrics.back;
                if (!f || !b) return;
                removeByTag(f, 'player-number-front');
                removeByTag(b, 'player-name');
                removeByTag(b, 'player-number-back');
                if ((player.number || '').trim()) {
                    addText(f, player.number.trim(), { left: 512, top: 520, fontFamily: player.fontFamily, fontSize: Number(player.size) || 118, charSpacing: Number(player.spacing) || 90, color: player.color, outline: player.outline, layerType: 'player-number-front' });
                    addText(b, player.number.trim(), { left: 512, top: 560, fontFamily: player.fontFamily, fontSize: Number(player.size) || 118, charSpacing: Number(player.spacing) || 90, color: player.color, outline: player.outline, layerType: 'player-number-back' });
                }
                if ((player.name || '').trim()) addText(b, player.name.trim().toUpperCase(), { left: 512, top: 360, fontFamily: player.fontFamily, fontSize: Math.max(42, Number(player.size) * 0.38), charSpacing: Number(player.spacing) || 90, color: player.color, outline: player.outline, layerType: 'player-name' });
                f.renderAll();
                b.renderAll();
                snapHistory('front');
                snapHistory('back');
                syncTextures();
                persistLocal();
            };

            const uploadLogo = async (event) => {
                const file = event.target?.files?.[0];
                if (!file) return;
                const fd = new FormData();
                fd.append('logo', file);
                saveStatus.value = 'Uploading logo...';
                try {
                    const res = await api.post(BOOT.upload_logo_url || '/upload-logo', fd, { 'Content-Type': 'multipart/form-data' });
                    const url = res?.data?.url;
                    if (!url) throw new Error('No URL');
                    const c2 = activeCanvas();
                    fabricLib.Image.fromURL(url, (img) => {
                        if (!img || !c2) return;
                        img.set({ left: 512, top: 512, originX: 'center', originY: 'center', scaleX: 0.38, scaleY: 0.38, _layerType: 'logo' });
                        c2.add(img);
                        c2.setActiveObject(img);
                        c2.renderAll();
                        saveStatus.value = 'Logo uploaded.';
                    }, { crossOrigin: 'anonymous' });
                } catch {
                    saveStatus.value = 'Logo upload failed.';
                } finally {
                    event.target.value = '';
                }
            };

            const exportPng = () => {
                if (!renderer || !scene || !camera) return;
                renderer.render(scene, camera);
                const a = document.createElement('a');
                a.href = renderer.domElement.toDataURL('image/png');
                a.download = `jersey-${selectedJersey.value?.slug || 'design'}-mockup.png`;
                a.click();
            };
            const exportPdf = () => {
                if (!renderer || !scene || !camera) return;
                renderer.render(scene, camera);
                const pdf = new jsPDF({ orientation: 'landscape', unit: 'px', format: [1600, 900] });
                pdf.addImage(renderer.domElement.toDataURL('image/png'), 'PNG', 0, 0, 1600, 900);
                pdf.save(`jersey-${selectedJersey.value?.slug || 'design'}-print.pdf`);
            };
            const placeOrder = async () => {
                if (totalQuantity.value <= 0) return (saveStatus.value = 'Add quantities before checkout.');
                if (!(orderCustomer.name || '').trim()) return (saveStatus.value = 'Enter customer name before checkout.');
                saveStatus.value = 'Submitting order...';
                try {
                    const res = await api.post(BOOT.order_url || '/order', {
                        jersey_id: selectedJersey.value?.id,
                        design_id: savedDesign.id,
                        customer_name: orderCustomer.name,
                        customer_email: orderCustomer.email,
                        customer_phone: orderCustomer.phone,
                        sizes: { ...sizeQuantities },
                        extra_logos: logoCount.value,
                        custom_name_number: hasCustomPlayer.value,
                        notes: `Template: ${selectedTemplate.value}; View: ${activeView.value}`,
                    });
                    saveStatus.value = `Order ${res?.data?.order_id || ''} placed successfully.`;
                } catch {
                    saveStatus.value = 'Order failed.';
                }
            };

            const undo = () => { const h = history[activeArtboard.value]; if (h && h.index > 0) applyHistory(activeArtboard.value, h.index - 1); };
            const redo = () => { const h = history[activeArtboard.value]; if (h && h.index < h.stack.length - 1) applyHistory(activeArtboard.value, h.index + 1); };
            const clearActiveArtboard = () => {
                const c2 = activeCanvas();
                if (!c2) return;
                c2.getObjects().forEach((o) => c2.remove(o));
                ensureTransparentCanvas(c2);
                c2.renderAll();
                snapHistory(activeArtboard.value);
                syncTextures();
                persistLocal();
            };
            const toggleLightingMode = () => {
                lightingMode.value = lightingMode.value === 'studio' ? 'stadium' : 'studio';
                setLightMode();
            };
            const setArtboard = (area) => { activeArtboard.value = area; };
            const applyTemplate = (key) => { selectedTemplate.value = key; applyMaterials(); persistLocal(); };
            const applyColors = () => { applyMaterials(); persistLocal(); };
            const selectJersey = async (id) => {
                selectedJerseyId.value = id;
                if (!supportsSleeves.value && activeArtboard.value === 'sleeve') activeArtboard.value = 'front';
                await loadModel(selectedJersey.value);
                applyMaterials();
                setView('front');
                persistLocal();
            };

            const fetchJerseys = async () => {
                try {
                    const res = await api.get(BOOT.catalog_url || '/jerseys');
                    const rows = Array.isArray(res?.data) ? res.data : [];
                    if (rows.length) {
                        jerseys.value = rows;
                        selectedJerseyId.value = rows[0].id;
                    }
                } catch {}
            };
            onMounted(async () => {
                await fetchJerseys();
                fabrics.front = createCanvas(frontCanvasRef.value, 'front');
                fabrics.back = createCanvas(backCanvasRef.value, 'back');
                fabrics.sleeve = createCanvas(sleeveCanvasRef.value, 'sleeve');
                frontTex = new THREE.CanvasTexture(fabrics.front.lowerCanvasEl);
                backTex = new THREE.CanvasTexture(fabrics.back.lowerCanvasEl);
                sleeveTex = new THREE.CanvasTexture(fabrics.sleeve.lowerCanvasEl);
                [frontTex, backTex, sleeveTex].forEach((t) => {
                    t.flipY = false;
                    t.anisotropy = 8;
                });
                await initThree();
                applyMaterials();
                setView('front');
                refreshPreviewTab();
                const cached = restoreLocal();
                if (cached) {
                    applyPayload(cached);
                    saveStatus.value = 'Recovered local autosave.';
                }
                autosaveId = window.setInterval(() => saveDesign(true), Math.max(8000, Number(BOOT.autosave_interval || 15000)));
            });

            onBeforeUnmount(() => {
                if (autosaveId) window.clearInterval(autosaveId);
                if (frameId) window.cancelAnimationFrame(frameId);
                window.removeEventListener('resize', onResize);
                controls?.dispose();
                renderer?.dispose();
            });

            watch(() => [colors.body, colors.sleeve, colors.collar, selectedTemplate.value], applyMaterials);

            return {
                threeMountRef,
                frontCanvasRef,
                backCanvasRef,
                sleeveCanvasRef,
                jerseys,
                selectedJerseyId,
                selectedTemplate,
                activeView,
                activeArtboard,
                activeWorkspace,
                loadDesignId,
                saveStatus,
                mockupStatus,
                previewImages,
                colors,
                player,
                sportsFonts,
                templates,
                supportsSleeves,
                availableViews,
                availableArtboards,
                sizeOptions,
                sizeQuantities,
                orderCustomer,
                unitPrice,
                totalQuantity,
                totalPrice,
                canUndo,
                canRedo,
                lightingModeLabel,
                prettyArea,
                selectJersey,
                setWorkspace,
                setView,
                setArtboard,
                focusView,
                applyTemplate,
                applyColors,
                applyPlayerText,
                uploadLogo,
                saveDesign,
                loadDesign,
                exportPng,
                exportPdf,
                placeOrder,
                clearActiveArtboard,
                undo,
                redo,
                toggleLightingMode,
                renderStyle,
                renderStyleLabel,
                toggleRenderStyle,
            };
        },
    }).mount(root);
};

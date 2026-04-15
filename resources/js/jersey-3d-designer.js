import { createApp, computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue/dist/vue.esm-bundler.js';
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

            let scene, camera, renderer, controls, stadiumLight, keyLight, fillLight, floorMesh, jerseyGroup;
            let bodyMat, sleeveMat, collarMat, frontTex, backTex, sleeveTex, activePatternTex, frameId, autosaveId;
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
            const prettyArea = (a) => ({ front: 'Front', back: 'Back', sleeve: 'Sleeve', sleeve_left: 'Sleeve Left', sleeve_right: 'Sleeve Right' }[a] || 'Sleeve');
            const activeCanvas = () => fabrics[activeArtboard.value];
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
            };
            const applyMaterials = () => {
                if (!bodyMat || !sleeveMat || !collarMat) return;
                bodyMat.color = new THREE.Color(colors.body);
                sleeveMat.color = new THREE.Color(colors.sleeve);
                collarMat.color = new THREE.Color(colors.collar);
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
                front.position.set(center.x, center.y + size.y * 0.03, bounds.max.z + 0.018);
                back.position.set(center.x, center.y + size.y * 0.03, bounds.min.z - 0.018);
                back.rotation.y = Math.PI;

                const sleeveWidth = size.x * 0.2;
                const sleeveHeight = size.y * 0.24;
                const sleeveCurve = Math.max(size.x * 0.03, 0.02);
                const sleeveLeft = new THREE.Mesh(curvedPanelGeometry(sleeveWidth, sleeveHeight, sleeveCurve, 24), createDecalMaterial(sleeveTex));
                const sleeveRight = new THREE.Mesh(curvedPanelGeometry(sleeveWidth, sleeveHeight, sleeveCurve, 24), createDecalMaterial(sleeveTex));
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
                bodyMat = new THREE.MeshStandardMaterial({ color: colors.body, roughness: 0.65, metalness: 0.04, bumpMap: weaveTexture(), bumpScale: 0.03 });
                sleeveMat = new THREE.MeshStandardMaterial({ color: colors.sleeve, roughness: 0.72, metalness: 0.04, bumpMap: weaveTexture(), bumpScale: 0.02 });
                collarMat = new THREE.MeshStandardMaterial({ color: colors.collar, roughness: 0.4, metalness: 0.08 });
                const g = new THREE.Group();
                g.add(new THREE.Mesh(new THREE.CylinderGeometry(0.85, 1.15, 2.5, 42, 1, true), bodyMat));
                if (supportsSleeves.value) {
                    const l = new THREE.Mesh(new THREE.CylinderGeometry(0.28, 0.42, 0.95, 28), sleeveMat);
                    l.position.set(-1.1, 0.8, 0);
                    l.rotation.z = Math.PI / 2.3;
                    const r = l.clone();
                    r.position.x = 1.1;
                    r.rotation.z = -Math.PI / 2.3;
                    g.add(l);
                    g.add(r);
                }
                const col = new THREE.Mesh(new THREE.TorusGeometry(0.28, 0.08, 24, 48, Math.PI * 2), collarMat);
                col.position.y = 1.28;
                col.rotation.x = Math.PI / 2;
                g.add(col);
                mountDecals(g, new THREE.Box3().setFromObject(g));
                return g;
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
                        g.scale.setScalar(1.4);
                        g.position.y = -0.25;
                        g.traverse((n) => {
                            if (!n.isMesh) return;
                            n.castShadow = true;
                            n.receiveShadow = true;
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
                        return;
                    } catch {
                        // Try the next candidate path.
                    }
                }

                jerseyGroup = fallbackModel();
                scene.add(jerseyGroup);
                mockupStatus.value = 'Fallback mockup in use. Add GLB files in /public/models/jerseys for photoreal view.';
                applyMaterials();
            };
            const setLightMode = () => {
                if (!keyLight || !fillLight || !stadiumLight || !floorMesh) return;
                if (lightingMode.value === 'studio') {
                    keyLight.intensity = 1.2;
                    fillLight.intensity = 0.8;
                    stadiumLight.intensity = 0.35;
                    floorMesh.material.opacity = 0.32;
                } else {
                    keyLight.intensity = 2.1;
                    fillLight.intensity = 1.3;
                    stadiumLight.intensity = 1.15;
                    floorMesh.material.opacity = 0.65;
                }
            };
            const focusView = (view) => {
                if (view === 'front') targetY = 0;
                else if (view === 'back') targetY = Math.PI;
                else if (view === 'sleeve_left') targetY = Math.PI / 2;
                else if (view === 'sleeve_right') targetY = -Math.PI / 2;
            };
            const setView = (view) => {
                activeView.value = view;
                activeArtboard.value = view === 'front' || view === 'back' ? view : 'sleeve';
                focusView(view);
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
                scene.background = new THREE.Color('#0b1026');
                camera = new THREE.PerspectiveCamera(40, m.clientWidth / m.clientHeight, 0.1, 100);
                camera.position.set(0, 1.35, 5.2);
                renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, preserveDrawingBuffer: true });
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
                renderer.setSize(m.clientWidth, m.clientHeight);
                renderer.shadowMap.enabled = true;
                renderer.shadowMap.type = THREE.PCFSoftShadowMap;
                m.innerHTML = '';
                m.appendChild(renderer.domElement);
                const pmrem = new THREE.PMREMGenerator(renderer);
                scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
                controls = new OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true;
                controls.enablePan = false;
                controls.maxDistance = 9.5;
                controls.minDistance = 2.3;
                controls.minPolarAngle = 0.35;
                controls.maxPolarAngle = Math.PI / 1.58;
                keyLight = new THREE.DirectionalLight('#fff', 1.2);
                keyLight.position.set(3.4, 4.8, 4.2);
                keyLight.castShadow = true;
                keyLight.shadow.mapSize.width = 2048;
                keyLight.shadow.mapSize.height = 2048;
                scene.add(keyLight);
                fillLight = new THREE.DirectionalLight('#9ec5ff', 0.8);
                fillLight.position.set(-3.2, 2.2, -3.4);
                scene.add(fillLight);
                stadiumLight = new THREE.HemisphereLight('#b5d0ff', '#24344b', 0.35);
                scene.add(stadiumLight);
                floorMesh = new THREE.Mesh(new THREE.CircleGeometry(6.8, 64), new THREE.MeshStandardMaterial({ color: '#1f2937', transparent: true, opacity: 0.32, roughness: 0.75, metalness: 0.1 }));
                floorMesh.rotation.x = -Math.PI / 2;
                floorMesh.position.y = -1.8;
                floorMesh.receiveShadow = true;
                scene.add(floorMesh);
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
                extras: { sizes: { ...sizeQuantities }, customer: { ...orderCustomer }, active_artboard: activeArtboard.value },
            });
            const applyPayload = (payload) => {
                if (!payload) return;
                Object.assign(colors, payload.colors || {});
                selectedTemplate.value = payload.patterns?.template || selectedTemplate.value;
                Object.assign(player, payload.text_layers || {});
                if (payload.extras?.sizes) sizeOptions.forEach((s) => (sizeQuantities[s] = Number(payload.extras.sizes[s] || 0)));
                if (payload.extras?.customer) Object.assign(orderCustomer, payload.extras.customer);
                applyMaterials();
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
                loadDesignId,
                saveStatus,
                mockupStatus,
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
            };
        },
    }).mount(root);
};

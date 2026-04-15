Place high-quality jersey models in this folder.

Primary file names used by defaults:
- football-short.glb
- football-long.glb
- basketball.glb
- training.glb

Optional auto-detected names:
- <jersey-slug>.glb
- <jersey-slug>.gltf

The 3D designer now tries multiple model URLs (from `model_url`, metadata model URLs, and slug-based names).
If none are found, it falls back to a procedural mockup so the designer still works.

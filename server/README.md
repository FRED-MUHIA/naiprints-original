# Designer API (Optional)

This is a lightweight Express server for uploads and mockup metadata. It is not required for the demo UI but can be used to store assets in a real deployment.

## Setup

```bash
cd server
npm install
npm run dev
```

## Endpoints

- `POST /api/uploads` → Upload artwork (`multipart/form-data`, field: `file`)
- `GET /api/mockups` → Placeholder endpoint for mockup metadata

Uploaded assets are served from `/uploads`.

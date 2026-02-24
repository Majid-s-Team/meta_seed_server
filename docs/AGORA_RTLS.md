# Agora RTLS Ingress (OBS stream key)

The server generates **stream keys** via Agora’s RTLS Ingress API so OBS can push to:

- **Server URL:** `rtmp://rtls-ingress-prod-ap.agoramdn.com/live`
- **Stream key:** Created by `POST https://api.sd-rtn.com/{region}/v1/projects/{appId}/rtls/ingress/streamkeys`

## Required .env

- **AGORA_APP_ID** – Agora project app ID  
- **AGORA_RTLS_CUSTOMER_ID** – Customer ID (Agora Console → Developer Toolkit → RESTful API)
- **AGORA_RTLS_CUSTOMER_SECRET** – Customer Secret (download key_and_secret.txt from same page)

Without them you get 401 Invalid authentication credentials.

## Optional .env (defaults shown)

| Variable | Default | Description |
|----------|---------|-------------|
| `AGORA_RTLS_API_BASE` | `https://api.sd-rtn.com` | RTLS API base URL |
| `AGORA_RTLS_REGION` | `ap` | Region: `ap` (Asia), `cn`, `eu`, `na` (must match ingress server) |
| `AGORA_RTLS_RTMP_URL` | `rtmp://rtls-ingress-prod-ap.agoramdn.com/live` | OBS “Server” URL |

## Flow

1. **Create/update livestream** (admin web or API) with `broadcast_type=rtmp`. If `rtmp_stream_key` is empty, the server calls the Agora RTLS API and stores the returned key (and sets `rtmp_url` to the configured RTLS URL).
2. **Broadcast page:** Opening the broadcast panel for an RTMP stream with no key triggers generation and save, then the page shows the same Server URL + Stream key.
3. **OBS:** Set Service = Custom..., Server = `rtmp://rtls-ingress-prod-ap.agoramdn.com/live`, Stream Key = the generated key.

## API reference

- [Create streaming key](https://docs.agora.io/en/media-gateway/reference/rest-api/endpoints/streaming-key/create-streaming-key)

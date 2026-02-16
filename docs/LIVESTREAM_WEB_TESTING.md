# Livestream Web Testing Interface

Blade-based test pages to verify livestream flow: admin controls (create, go live, end) and user join + Agora video playback.

---

## 1. Repository architecture summary

- **Laravel:** 10.x
- **Auth:** Laravel Sanctum (API Bearer tokens). No session-based web auth; test pages use a token input.
- **Roles:** `User.role` = `admin` | `user`; admin routes protected by `role:admin` middleware.
- **Routes:** API in `routes/api.php` (prefix `/api`); web in `routes/web.php`.
- **Controllers:** API under `App\Http\Controllers\Api`; web test under `App\Http\Controllers`.
- **Views:** Blade only (no Inertia/React). Single layout for test pages: `layouts/test.blade.php`.
- **Response format:** JSON `{ status_code, message, data }`.
- **DB:** MySQL; livestreams + livestream_bookings tables (already present).

---

## 2. Files created / modified

| Action | Path |
|--------|------|
| Created | `app/Http/Controllers/LivestreamTestController.php` |
| Created | `resources/views/layouts/test.blade.php` |
| Created | `resources/views/livestream-test/admin.blade.php` |
| Created | `resources/views/livestream-test/user.blade.php` |
| Created | `resources/views/livestream-test/publisher.blade.php` |
| Modified | `routes/web.php` (added 3 routes) |
| Modified | `routes/api.php` (added `GET livestreams/live`) |
| Modified | `app/Http/Controllers/Api/LivestreamController.php` (added `live()`, join `?test=1`) |
| Created | `docs/LIVESTREAM_WEB_TESTING.md` (this file) |

---

## 3. Database

Tables already exist from the livestream module:

- **livestreams:** id, title, description, scheduled_at, status (scheduled | live | ended), agora_channel, price, max_participants, created_by, timestamps
- **livestream_bookings:** id, user_id, livestream_id, booked_at, timestamps

No new migrations added.

---

## 4. Agora service

`App\Services\AgoraService` already exists:

- `generateRtcToken(channelName, userId)` — 2h expiry, uses `AGORA_APP_ID` and `AGORA_APP_CERTIFICATE` from env.

---

## 5. Controllers

- **LivestreamTestController** (web): `adminPage()`, `userPage()` — return Blade views only; no API logic.
- **Api\LivestreamController**: existing + `live()` (list LIVE streams) and join with `?test=1` (skip booking check).
- **Api\Admin\LivestreamController**: unchanged (create, update, goLive, endStream, participants).

---

## 6. Routes added

**Web (no auth):**

- `GET /admin/livestream-test` → Admin test page
- `GET /livestream-test` → User test page (list live, join, play video)
- `GET /livestream-test/publisher` → Publisher test page (camera/mic → Agora)

**API (existing + new):**

- `GET /api/livestreams/live` → List streams with `status = live` (auth:sanctum)
- `POST /api/livestreams/{id}/join?test=1` → Join without booking check (auth:sanctum)
- `GET /api/livestreams/test-live` → List live streams, no auth (only when `LIVESTREAM_LOCAL_TEST=true`, else 404)
- `GET /api/livestreams/{id}/test-credentials` → Agora credentials, no auth (only when `LIVESTREAM_LOCAL_TEST=true`, else 404)

---

## 7. Blade test pages

### Admin: `/admin/livestream-test`

- **Set API token:** Paste Bearer token from `POST /api/login` (admin user); stored in localStorage.
- **Create stream:** Title, Agora channel, scheduled time → calls `POST /api/admin/livestreams`.
- **Refresh list:** Calls `GET /api/admin/livestreams`, shows all streams and status.
- **Go LIVE:** Stream ID + button → `POST /api/admin/livestreams/{id}/go-live`.
- **End stream:** Stream ID + button → `POST /api/admin/livestreams/{id}/end-stream`.

### User: `/livestream-test`

- **Set API token:** Paste Bearer token from `POST /api/login` (any user); stored in localStorage.
- **Refresh list:** Calls `GET /api/livestreams/live`, shows only LIVE streams.
- **Join:** Click “Join” on a row or enter ID + “Join Stream” → calls `POST /api/livestreams/{id}/join?test=1`, then connects Agora Web SDK and plays remote video in the black player area.
- **Leave:** Leaves Agora channel and clears video.

### Publisher: `/livestream-test/publisher`

- **Set API token:** Paste Bearer token from `POST /api/login` (any user); stored in localStorage.
- **Start publishing:** Enter a **live** stream ID (from admin: create stream → Go LIVE) → calls `POST /api/livestreams/{id}/join?test=1` for credentials, then Agora Web SDK captures camera + microphone and publishes to the channel.
- **Local preview:** Your video appears on the page; viewers see the same stream on the User test page when they Join.
- **Stop publishing:** Leaves channel and stops tracks.

---

## 8. Join endpoint (test mode)

- **URL:** `POST /api/livestreams/{id}/join?test=1`
- **Headers:** `Authorization: Bearer {token}`
- **Behaviour:** If `test=1`, booking is not checked; stream must still be LIVE. Returns Agora credentials.

**Example response (200):**

```json
{
  "status_code": 200,
  "message": "SUCCESS",
  "data": {
    "app_id": "your-agora-app-id",
    "channel": "test-channel-123",
    "token": "006..."
  }
}
```

---

## 9. Local test mode (no API token)

When `LIVESTREAM_LOCAL_TEST=true` in `.env`:

- **Publisher** and **User** test pages can get Agora credentials **without** an API token.
- Endpoints used (return 404 when local test is off):
  - `GET /api/livestreams/test-live` — list live streams (no auth).
  - `GET /api/livestreams/{id}/test-credentials` — return `app_id`, `channel`, `token` (or `token: null` if no certificate).
- **Production:** Set `LIVESTREAM_LOCAL_TEST=false` or omit it; then token is required for join and live list.

---

## 10. Instructions to start testing

1. **Env**
   - Set `AGORA_APP_ID` and `AGORA_APP_CERTIFICATE` in `.env` (no spaces after `=`).
   - Optional: set `LIVESTREAM_LOCAL_TEST=true` for token-free testing.

2. **Run app**
   ```bash
   php artisan serve
   ```

3. **Get API token**
   - Use Postman/Swagger/curl: `POST http://localhost:8000/api/login` with `email` and `password`.
   - Create an admin user if needed (e.g. via `php artisan tinker` or your app’s register).

4. **Admin test page**
   - Open: `http://localhost:8000/admin/livestream-test`
   - Paste **admin** token → Set Token.
   - Create a stream (title, channel, date/time) → Create Stream.
   - Note the stream ID; click **Go LIVE** for that ID.
   - Use **Refresh List** to see status “live”.

5. **User test page**
   - Open: `http://localhost:8000/livestream-test`
   - Paste **any** user token → Set Token.
   - Click **Refresh List** → you should see the live stream.
   - Click **Join** on that stream (or enter ID and **Join Stream**).
   - Agora connects; when the host (e.g. OBS) publishes video, it appears in the black player area.
   - **Leave** to disconnect.

6. **Publisher test (browser host)**
   - Open: `http://localhost:8000/livestream-test/publisher`
   - Set API token (any user), enter the **live** stream ID, click **Start Publishing**.
   - Allow camera/mic; your local preview appears and you are publishing to the Agora channel.
   - On the **User test** page, join the same stream to see the publisher’s video.

7. **End-to-end**
   - **Option A – Browser:** Admin creates stream → Go LIVE → open Publisher page → Start Publishing → open User page → Join.
   - **Option B – OBS:** Use Agora’s OBS plugin or ingest to the same `app_id` and `channel`. Admin: Create stream → Go LIVE. User: Join on test page to see OBS stream.

8. **With local test mode**
   - Set `LIVESTREAM_LOCAL_TEST=true` in `.env`, refresh config (`php artisan config:clear`).
   - Publisher: open `/livestream-test/publisher`, enter **live** stream ID only (no token), click Start Publishing.
   - User: open `/livestream-test`, click Refresh List (no token), then Join on a stream.

---

## 11. Agora: no “Testing mode” toggle — use “APP ID” only project

Agora Console does **not** have a “Testing mode” switch. Whether you can join **without a token** depends on how the project was **created**:

- **APP ID + Token (recommended):** Token is required. You cannot turn this off later. If you see “dynamic use static key” or CAN_NOT_GET_GATEWAY_SERVER, the project was created this way.
- **APP ID only:** No certificate/token required. You must choose this **when creating a new project** in [Agora Console](https://console.agora.io) → Create Project → under authentication, select **「APP ID」** (not “APP ID + Token”). Then use that project’s App ID in `.env` as `AGORA_APP_ID`; you can leave `AGORA_APP_CERTIFICATE` empty for local testing.

So: to test without token, **create a new project** with “APP ID” only and use its App ID in `.env`.

---

## 12. Debug tips

- **"Invalid token, authorized failed"** — Usually wrong or mismatched Agora credentials. Check `.env`: no spaces after `=` in `AGORA_APP_ID` and `AGORA_APP_CERTIFICATE`; values must match the Agora project.
- **No camera permission popup** — Ensure you’re not blocking the popup; refresh and try again. Check browser console for errors before the permission step (e.g. join failing first).
- **Channel name** — Publisher and User pages show **Channel:** when connected; use the same stream ID on both so they use the same Agora channel.
- **Console logs** — Publisher logs: `[Publisher] Getting credentials`, `Joining channel`, `Permission granted`, `Joined channel`, `Publishing live`. Use these to see where the flow stops.
- **Retry** — On join or publish failure, the publisher page leaves the Start button enabled so you can fix credentials and try again.

---

## 13. Issues fixed (publishing & testing flow)

- **Token/config:** Agora `app_id` and `app_certificate` are trimmed when read so spaces in `.env` don’t cause invalid token.
- **Token errors:** "Invalid token" is caught and a clear message is shown (check APP_ID/CERTIFICATE); Start remains clickable for retry.
- **Local test mode:** When `LIVESTREAM_LOCAL_TEST=true`, publisher and viewer can get credentials without API token via `test-credentials` and `test-live`; production stays protected when the flag is off.
- **Camera flow:** Publisher still requests camera/mic with `createMicrophoneAndCameraTracks()` then shows local preview, then joins and publishes; errors don’t leave the page stuck.
- **UX:** Status line and channel name are shown; errors are user-friendly; failsafe allows retry after join or publish failure.

---

## Agora Web SDK

The user test page loads Agora Web SDK 4.x from `https://download.agora.io/sdk/release/AgoraRTC_N-4.18.0.js`. If that fails (e.g. CORS or 404), replace the script in `resources/views/livestream-test/user.blade.php` with another build (e.g. from [Agora docs](https://docs.agora.io/en/video-calling/get-started/get-started-sdk?platform=web) or your own build).

---

## Safety

- Existing APIs and auth are unchanged.
- Only new web routes and one new API route (`GET livestreams/live`); join extended with optional `?test=1`.
- Test pages are unauthenticated web routes; they call the API with a user-supplied token.

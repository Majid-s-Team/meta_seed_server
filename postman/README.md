# MetaSeed API – Postman Collection

This folder contains a **Postman Collection** and **Environment** for the full MetaSeed API and complete scenarios.

## Files

| File | Description |
|------|-------------|
| `MetaSeed_API_Collection.json` | Postman Collection v2.1 – all endpoints and scenario folders |
| `MetaSeed_Environment.json` | Environment with `base_url`, `token`, `livestream_id`, `event_id`, `user_id` |

## Import in Postman

1. Open Postman → **Import** → drag or select `MetaSeed_API_Collection.json` and `MetaSeed_Environment.json`.
2. Select the **MetaSeed Local** environment in the top-right dropdown.
3. Set `base_url` if needed (default: `http://localhost:8000`). After **Login**, the collection script can auto-set `token`.

## Complete Scenarios (run in order when needed)

### Scenario 1: Auth and profile

1. **Register** – create a new user (or use existing).
2. **Login** – get token (saved to env by test script if present).
3. **Get Profile** – confirm authenticated user.

### Scenario 2: Events and wallet

1. **List Categories** – get `category_id`.
2. **List Events** – get `event_id`.
3. **Get Wallet** – check balance (coins).
4. **Purchase Coins** (optional) – `product_id`: `coins_10`, `coins_50`, `coins_100`.
5. **Book Event** – book event (debits coins).

### Scenario 3: Event bookings

1. **List My Bookings** – list user’s event bookings.
2. **Show Booking** – details of one booking.

### Scenario 4: Livestream (user flow)

1. **Upcoming Livestreams** – list scheduled streams.
2. **Show Livestream** – details (set `livestream_id` in env).
3. **Book Livestream** – book (paid streams debit wallet).
4. When stream is live: **Join Livestream** – get Agora token.
5. Optional: **Viewer Joined** / **Viewer Left** – analytics.

### Scenario 5: Admin – livestream lifecycle

1. **Login** with an **admin** user (token must have admin role).
2. **Create Livestream** – e.g. `agora_channel`: `channel_abc_123`, `broadcast_type`: `agora_rtc` or `rtmp`.
3. **List Admin Livestreams** – get `livestream_id`.
4. **Go Live** – set status to live.
5. **Livestream Participants** – list viewers (optional).
6. **End Stream** – end the stream.

### Scenario 6: Agora webhook (RTMP auto-detection)

- **Webhook – RTMP stream started** – send `event: live_stream_connected` and `channel` matching a livestream’s `agora_channel` (triggers auto go-live when configured).
- **Webhook – RTMP stream stopped** – send `event: live_stream_disconnected` (triggers auto end-stream when configured).

If `AGORA_WEBHOOK_SECRET` is set, add header `X-Agora-Signature: sha256=<hmac_sha256(body, secret)>`.

### Scenario 7: Admin – events, categories, users

- **Create/Update Event**, **Change Event Status** (active / inactive / completed).
- **Create/Update/Delete Category**.
- **List Users**, **All Transactions**, **Toggle User Active**, **Analytics**, **Booking History**.

## Collection structure

- **1 – Auth** – register, login, forgot-password, verify-otp, reset-password (no token).
- **2 – Profile** – get/update profile, change-password, toggle-active, upload-media.
- **3 – Events** – list, show, book.
- **4 – Event Bookings** – list, show.
- **5 – Categories** – list (admin: create/update/delete in folders 10).
- **6 – Static Pages** – list, show, create, update, delete.
- **7 – Wallet** – get wallet, purchase coins, transactions.
- **8 – Livestreams (user)** – test-live, test-credentials, upcoming, live, show, book, join, viewer-joined, viewer-left.
- **9 – Admin – Events** – create, update, change status.
- **10 – Admin – Categories** – create, update, delete.
- **11 – Admin – Livestreams** – list, create, update, go-live, end-stream, participants.
- **12 – Admin – Users & Analytics** – users, transactions, toggle, analytics, bookings.
- **13 – Agora Webhook** – sample payloads for stream started/stopped (no auth).

## Base URL and auth

- API prefix: `/api`. Full base: e.g. `http://localhost:8000` (no `/api` in variable).
- Auth: Bearer token. Admin routes require a user with admin role.
- No-auth routes: register, login, forgot-password, verify-otp, reset-password, Agora webhook, and (when `LIVESTREAM_LOCAL_TEST=true`) test-live / test-credentials.

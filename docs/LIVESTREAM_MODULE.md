# Livestream Module (Agora)

## 1. Repository architecture summary

- **Laravel version:** 10.x
- **Authentication:** Laravel Sanctum (API tokens)
- **API structure:** All controllers under `App\Http\Controllers\Api`; routes in `routes/api.php`
- **Route organization:** Public routes first, then `auth:sanctum` group; admin actions under `role:admin` middleware
- **User roles:** `User` model has `role` (`admin` | `user`); enforced via `RoleMiddleware` (`role:admin`)
- **Database:** MySQL; migrations use separate tables + later `add_foreign_keys_*` migrations
- **Coding patterns:** Controllers use `ApiResponseTrait` (`successResponse`, `errorResponse`, `apiResponse`) and `ResponseCode` constants; inline `Validator` in some controllers; no API Resources (arrays/models returned)
- **Service layer:** None before this module; `App\Services\AgoraService` added for token generation
- **Response format:** `{ "status_code": int, "message": string, "data": mixed }`
- **Middleware:** `auth:sanctum`, `role:admin` (alias in `Kernel`)

---

## 2. Files created

| Type | Path |
|------|------|
| Migration | `database/migrations/2026_02_13_000001_create_livestreams_table.php` |
| Migration | `database/migrations/2026_02_13_000002_create_livestream_bookings_table.php` |
| Migration | `database/migrations/2026_02_13_000003_add_foreign_keys_to_livestreams_table.php` |
| Migration | `database/migrations/2026_02_13_000004_add_foreign_keys_to_livestream_bookings_table.php` |
| Model | `app/Models/Livestream.php` |
| Model | `app/Models/LivestreamBooking.php` |
| Service | `app/Services/AgoraService.php` |
| Form Request | `app/Http/Requests/StoreLivestreamRequest.php` |
| Form Request | `app/Http/Requests/UpdateLivestreamRequest.php` |
| Controller | `app/Http/Controllers/Api/Admin/LivestreamController.php` |
| Controller | `app/Http/Controllers/Api/LivestreamController.php` |
| Config | `config/services.php` (agora block added) |
| Routes | `routes/api.php` (livestream routes added) |
| Doc | `docs/LIVESTREAM_MODULE.md` (this file) |

**Modified:** `app/Models/User.php` (added `livestreamBookings()`), `composer.json` (added `taylanunutmaz/agora-token-builder`).

---

## 3. ENV config

Add to `.env`:

```env
AGORA_APP_ID=
AGORA_APP_CERTIFICATE=
```

Used in `config/services.php` under `services.agora`. Run `composer install` (PHP 8.1+) so `taylanunutmaz/agora-token-builder` is installed for token generation.

---

## 4. Migrations

- **livestreams:** id, title, description (nullable), scheduled_at (dateTime), status (enum: scheduled|live|ended), agora_channel, price, max_participants, created_by, timestamps.
- **livestream_bookings:** id, user_id, livestream_id, booked_at, timestamps.
- Foreign keys: livestreams.created_by → users.id; livestream_bookings.user_id → users.id, livestream_id → livestreams.id.

Run: `php artisan migrate`

---

## 5. Models

- **Livestream:** fillable, casts (scheduled_at, price), `creator()`, `bookings()`.
- **LivestreamBooking:** fillable, casts (booked_at), `user()`, `livestream()`.
- **User:** added `livestreamBookings()`.

---

## 6. AgoraService

- **Path:** `App\Services\AgoraService`
- **Methods:** `generateRtcToken(string $channelName, $userId): string` (2h expiry), `getAppId(): string`
- Uses `config('services.agora.app_id')` and `config('services.agora.app_certificate')`.

---

## 7. Routes added

**User (auth:sanctum):**

- `GET  /api/livestreams/upcoming` → upcoming
- `GET  /api/livestreams/{id}` → show
- `POST /api/livestreams/{id}/book` → book
- `POST /api/livestreams/{id}/join` → join (returns app_id, channel, token)

**Admin (auth:sanctum + role:admin):**

- `GET  /api/admin/livestreams` → index
- `POST /api/admin/livestreams` → store
- `PUT  /api/admin/livestreams/{id}` → update
- `POST /api/admin/livestreams/{id}/go-live` → goLive
- `POST /api/admin/livestreams/{id}/end-stream` → endStream
- `GET  /api/admin/livestreams/{id}/participants` → participants

---

## 8. Example API requests

**Admin – schedule livestream**

```http
POST /api/admin/livestreams
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Q&A Live",
  "description": "Weekly Q&A",
  "scheduled_at": "2026-02-20 19:00:00",
  "agora_channel": "qa-live-2026",
  "price": 0,
  "max_participants": 50
}
```

**Admin – go live**

```http
POST /api/admin/livestreams/1/go-live
Authorization: Bearer {admin_token}
```

**User – book stream**

```http
POST /api/livestreams/1/book
Authorization: Bearer {user_token}
```

**User – join (get Agora token)**

```http
POST /api/livestreams/1/join
Authorization: Bearer {user_token}
```

---

## 9. Example API responses

**Join success (200):**

```json
{
  "status_code": 200,
  "message": "SUCCESS",
  "data": {
    "app_id": "your-agora-app-id",
    "channel": "qa-live-2026",
    "token": "006..."
  }
}
```

**Upcoming list (200):**

```json
{
  "status_code": 200,
  "message": "SUCCESS",
  "data": [
    {
      "id": 1,
      "title": "Q&A Live",
      "description": "Weekly Q&A",
      "scheduled_at": "2026-02-20T19:00:00.000000Z",
      "status": "scheduled",
      "agora_channel": "qa-live-2026",
      "price": "0.00",
      "max_participants": 50,
      "isBooked": true
    }
  ]
}
```

**Error – stream not live (400):**

```json
{
  "status_code": 400,
  "message": "Stream is not live",
  "data": null
}
```

**Error – not booked (403):**

```json
{
  "status_code": 403,
  "message": "You must book this stream to join",
  "data": null
}
```

---

## 10. Testing with Swagger

An OpenAPI 3.0 (Swagger) spec is provided so you can test every livestream endpoint.

### Files

| File | Purpose |
|------|---------|
| `docs/openapi-livestream.yaml` | OpenAPI 3.0 spec (Auth + User + Admin livestream endpoints) |
| `public/docs/openapi-livestream.yaml` | Same spec, served for Swagger UI |
| `public/docs/index.html` | Swagger UI page |

### Option A – Swagger UI in the browser

1. Start the app:
   ```bash
   php artisan serve
   ```
2. Open in the browser:
   ```
   http://localhost:8000/docs/
   ```
3. **Login:** Use the **POST /login** endpoint with an existing user (e.g. `email` + `password`). Copy the `token` from the response.
4. **Authorize:** Click **Authorize**, paste the token (no “Bearer ” prefix needed; Swagger adds it), then **Authorize** and **Close**.
5. Call any livestream endpoint. For **admin** routes (schedule, go live, end, participants) use a user with `role: admin`.

### Option B – Postman

1. In Postman: **Import** → **Link** or **File**.
2. Use either:
   - **URL:** `http://localhost:8000/docs/openapi-livestream.yaml` (with the app running), or  
   - **File:** `docs/openapi-livestream.yaml`
3. Set **Base URL** in Postman to `http://localhost:8000/api` (or your deployed API URL).
4. Create a request to **POST /login** and copy the token. In the collection or request, set **Authorization** → **Bearer Token** to that value.
5. Run the livestream requests. Use an admin user’s token for admin endpoints.

### Suggested test flow

1. **Login** (user or admin) → copy token → **Authorize** in Swagger (or set in Postman).
2. **Admin:** **POST /admin/livestreams** – create a stream.
3. **User:** **GET /livestreams/upcoming** – list upcoming.
4. **User:** **POST /livestreams/{id}/book** – book the stream.
5. **Admin:** **POST /admin/livestreams/{id}/go-live** – set stream to live.
6. **User:** **POST /livestreams/{id}/join** – get Agora `app_id`, `channel`, `token`.
7. **Admin:** **GET /admin/livestreams/{id}/participants** – list who booked.
8. **Admin:** **POST /admin/livestreams/{id}/end-stream** – end the stream.

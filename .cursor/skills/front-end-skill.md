🧠 ROLE
You are a senior system architect and Laravel engineer working on the MetaSeat platform.
This platform powers a mobile application for live events and livestream sports broadcasting.
You must analyze the existing Laravel project and implement a complete, production-ready Admin Panel aligned with the mobile app design and business flow.
DO NOT rewrite the project.
DO NOT break existing APIs.

🧩 PHASE 1 — ANALYZE THE PROJECT
Analyze and document:
Architecture
authentication & Sanctum flow


API structure


models & relationships


wallet & coin system


booking & ticket flow


livestream flow


notifications (if present)


Database Structure
Key tables include:
• users
 • wallets
 • transactions
 • events
 • event_bookings
 • event_categories
 • livestreams
 • livestream_bookings
 • static_pages
Explain relationships between them.

🧩 PHASE 2 — BUSINESS FLOW UNDERSTANDING
The system allows users to:
• purchase coins
 • book event tickets
 • join livestream events
 • watch live sports broadcasts
 • manage wallet & bookings
Revenue comes from:
• coin purchases
 • event ticket sales
 • livestream access purchases
Admin controls the entire ecosystem.

🧩 PHASE 3 — LIVESTREAM SYSTEM COMPATIBILITY
Ensure the admin panel integrates with the livestream system:
• livestream scheduling
 • go LIVE control
 • participant limits
 • stream status automation
 • Agora channel usage compatibility
 • future OBS broadcasting support

🧩 PHASE 4 — IMPLEMENT ADMIN PANEL
Create a secure and scalable admin panel.
Use Laravel Blade + TailwindCSS for fast, responsive UI.
Create Admin middleware & authentication.

1️⃣ DASHBOARD
Show:
• total users
 • total events
 • upcoming events
 • live streams
 • tickets sold
 • total revenue
 • today revenue
 • wallet purchases
Include charts:
 • ticket sales trend
 • revenue trend

2️⃣ EVENT MANAGEMENT
CRUD events:
Fields:
title


category


description


date & time


total seats


available seats


price (coins)


online/offline


status


cover image (add if missing)


Features:
 ✔ schedule events
 ✔ manage seats
 ✔ activate/deactivate
 ✔ view bookings

3️⃣ LIVESTREAM MANAGEMENT
Manage livestreams:
Fields:
title


scheduled time


price


max participants


channel name


status


thumbnail (add if missing)


Features:
 ✔ schedule stream
 ✔ go LIVE toggle
 ✔ auto-complete after event
 ✔ view participants
 ✔ bookings count

4️⃣ BOOKINGS MANAGEMENT
Event bookings:
✔ attendee list
 ✔ filter by event/date
 ✔ export list
Livestream bookings:
✔ viewer list
 ✔ participant history

5️⃣ USER & WALLET MANAGEMENT
Admin can:
✔ view users
 ✔ search users
 ✔ view wallet balance
 ✔ view transaction history
 ✔ view bookings
 ✔ block/deactivate users

6️⃣ EARNINGS & TRANSACTIONS
Dashboard metrics:
• total revenue
 • revenue per event
 • revenue per livestream
 • coin purchase history
Transaction table:
user


coins purchased


amount


date


transaction id


View-only access.

7️⃣ COIN PACKAGES MANAGEMENT (if applicable)
Allow admin to:
✔ create coin packages
 ✔ set pricing
 ✔ enable/disable

8️⃣ CMS MANAGEMENT
Manage editable pages:
✔ Privacy Policy
 ✔ Terms & Conditions
 ✔ About App
 ✔ FAQs
Use rich text editor.
Expose content via API.

9️⃣ NOTIFICATIONS (if supported)
Admin can send:
✔ push notifications
 ✔ event reminders
 ✔ promotional alerts

🧩 PHASE 5 — DESIGN SYSTEM (MUST MATCH MOBILE APP)
STYLE:
 • dark premium UI
 • sports/event immersive aesthetic
COLORS:
 background: #0B0B0F
 card background: #15151E
 primary gradient: #6A5CFF → #4A90FF
 text primary: #FFFFFF
 text secondary: #A1A1AA
 live indicator: #FF3B3B
TYPOGRAPHY:
 Use Inter font.
UI ELEMENTS:
Cards:
 • rounded corners (14px)
 • dark glass style
Buttons:
 • gradient primary
 • pill shape
 • glow hover
Sidebar:
 • dark theme
 • active glow highlight
Tables:
 • dark modern rows
 • soft hover
Dashboard:
 • metric cards
 • modern charts
Ensure admin panel visually matches MetaSeat branding.

🧩 PHASE 6 — SECURITY & PERFORMANCE
✔ validate all inputs
 ✔ protect admin routes
 ✔ sanitize CMS content
 ✔ prevent unauthorized access
 ✔ eager load relationships
 ✔ paginate large tables

🧩 PHASE 7 — DATABASE IMPROVEMENTS (IF NEEDED)
If missing, add:
• event cover image
 • livestream thumbnail
 • booking status
 • payment metadata
Do NOT break existing data.

🧩 PHASE 8 — OUTPUT REQUIRED
Provide:
new migrations (if any)


models & relationships


controllers & routes


middleware & policies


Blade views & layout


dashboard analytics queries


admin route list


test checklist


sample admin credentials


explanation of revenue flow



🧩 IMPORTANT RULES
✔ do not break mobile APIs
 ✔ maintain backward compatibility
 ✔ follow Laravel best practices
 ✔ keep code modular & scalable


# 👗 Pastimes — Pre-Loved Fashion Marketplace

> **"Give clothes a second life."**
> South Africa's premium second-hand clothing e-store built for buying, selling, and trading pre-loved fashion.

---

## 🚀 Project Overview

Pastimes is a full-stack PHP/MySQL web application that creates a seamless marketplace for second-hand fashion in South Africa. Users can browse listings as guests, register to sell or trade items, and admins can manage the platform.

---

## ✨ Features

### 👤 User Accounts
- Secure registration and login with **bcrypt password hashing**
- Profile management (update username, email, password)
- Three roles: **Guest**, **Registered User**, **Administrator**
- Account statuses: Active, Banned, Suspended

### 📦 Listings
- Create item listings with title, description, price, category, condition, and image upload
- Drag-and-drop image uploader (JPG, PNG, GIF, WEBP — max 5MB)
- Categories: Vintage, Shoes, Clothing, Electronics, Accessories, Other
- Conditions: New, Like New, Good, Fair, Worn
- Items appear on global catalog immediately after listing

### 🔍 Search & Filter
- Keyword search across title, description, and seller name
- Filter by category, condition, and price range (min/max)
- All filters work simultaneously and are combinable

### 🔄 Trade Engine
- Propose a trade on any available listing you don't own
- Select one of your own available items to offer in exchange
- Receiving user gets a real-time notification
- Accept → both items marked **Traded**; Decline → both remain **Available**
- Full trade history (incoming + outgoing) in the dashboard

### 🛒 Cart & Checkout
- Add any available item to a persistent database-backed cart
- Review cart, remove items, see running total
- Mock checkout: creates Order records, marks items as **Sold**, notifies sellers, clears cart

### 🔔 Notifications
- In-app notification system for trade offers, acceptances, declines, and purchases
- Unread count badge in the navbar
- Mark all as read when viewed

### 🛡️ Admin Panel
- View site-wide statistics (users, listings, trades, orders)
- Ban, Suspend, or Activate user accounts
- Delete any listing from the platform
- Admin accounts are protected from modification

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Styling | Custom dark-mode design system (glassmorphism) |
| Fonts | Google Fonts — Outfit + Inter |
| Backend | PHP 8+ |
| Database | MySQL / MariaDB via XAMPP |
| Server | Apache via XAMPP |

---

## 📁 Project Structure

```
/Pastimes
├── index.php               # Homepage — hero, stats, featured listings
├── register.php            # User registration
├── login.php               # User login
├── logout.php              # Session destroy
├── catalog.php             # Full catalog with search + filters
├── item.php                # Single item detail + trade modal
├── add_listing.php         # Create a new listing
├── dashboard.php           # User dashboard (5 tabs)
├── cart.php                # Cart + mock checkout
├── admin.php               # Admin panel
│
├── includes/
│   ├── db.php              # PDO database connection singleton
│   ├── auth.php            # Session helpers & access guards
│   ├── functions.php       # Shared utility functions
│   ├── header.php          # Shared navbar + HTML head
│   └── footer.php          # Shared footer + JS
│
├── process/
│   ├── register_process.php
│   ├── login_process.php
│   ├── add_listing_process.php
│   ├── trade_process.php
│   ├── cart_process.php
│   ├── checkout_process.php
│   ├── admin_process.php
│   └── profile_process.php
│
├── css/
│   └── style.css           # Full design system (dark mode, glassmorphism)
│
├── js/
│   ├── validation.js       # Frontend form validation
│   └── trade-modal.js      # Trade proposal modal logic
│
└── uploads/                # User-uploaded product images
```

---

## ⚙️ How to Run Locally

### 1. Clone the Repository
```bash
git clone https://github.com/KamvelihleAthabileDyantyi17/Pastimes.git
```

### 2. Move to XAMPP htdocs
Place the folder at:
```
C:\xampp\htdocs\Pastimes\
```

### 3. Start XAMPP
Open the **XAMPP Control Panel** and start both:
- ✅ Apache
- ✅ MySQL

### 4. Run Database Setup
Visit the setup script **once** in your browser:
```
http://localhost/Pastimes/setup.php
```
This creates the `pastimes_db` database, all 7 tables, and seeds demo data.
> ⚠️ **Delete `setup.php` after it runs successfully.**

### 5. Launch the Site
```
http://localhost/Pastimes
```

---

## 🔑 Demo Credentials

| Role | Email | Password |
|---|---|---|
| Admin | `admin@pastimes.co.za` | `Admin@123` |
| Demo User 1 | `thrift@pastimes.co.za` | `Demo@123` |
| Demo User 2 | `vintage@pastimes.co.za` | `Demo@123` |

---

## 🗃️ Database Schema

7 core tables: `Users`, `Categories`, `Products`, `Trades`, `Cart`, `Orders`, `Notifications`

All queries use **PDO prepared statements** to prevent SQL injection.

---

## 🔒 Security

- Passwords hashed with `password_hash()` using **bcrypt**
- All user input sanitised with `htmlspecialchars` + `strip_tags`
- PDO prepared statements on every database query
- File uploads restricted to image MIME types only
- Owner-only checks before editing or deleting listings
- Admin-only pages redirect non-admins to the homepage

---

## 🔮 Future Enhancements

- **Payment Gateway** — Integration with PayFast or Ozow
- **Rating System** — Peer-to-peer seller reviews
- **Chat System** — In-app messaging between buyers and sellers
- **Mobile App** — React Native companion app

---

## 📝 License

This project is open source. Feel free to contribute!

## 🤝 Contributing

Contributions are welcome! Fork the repository and open a pull request.

## 📧 Contact

For questions or suggestions, reach out via GitHub Issues.

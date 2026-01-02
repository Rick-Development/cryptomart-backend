# CryptoMart API - New Features Implementation Summary

## 📋 Overview
This document summarizes the implementation of new features for the CryptoMart application, including Banners, Announcements, Support Tickets, and Notifications systems.

---

## 🎯 Features Implemented

### 1. 📢 Ad Banners System
**Purpose:** Display promotional banners in the mobile app (Dashboard, Home, P2P, Gift Cards sections)

#### Database
- **Table:** `banners`
- **Columns:**
  - `id` - Primary key
  - `uuid` - Unique identifier
  - `image` - Banner image path
  - `link` - Optional URL link
  - `type` - Banner placement (dashboard, home, p2p, gift_card)
  - `status` - Active/Inactive boolean
  - `timestamps`

#### Admin Panel
- **Route:** `/admin/banner`
- **Features:**
  - ✅ List all banners with pagination
  - ✅ Add new banner (image upload, type selection, optional link)
  - ✅ Edit existing banner
  - ✅ Delete banner
  - ✅ Toggle status (Enable/Disable)
- **Controller:** `App\Http\Controllers\Admin\BannerController`
- **View:** `resources/views/admin/sections/banner/index.blade.php`

#### User API
- **Endpoint:** `GET /api/user/banner`
- **Authentication:** Required (Bearer token)
- **Response Format:**
```json
{
    "message": {
        "success": ["Banners fetched successfully"]
    },
    "data": {
        "banners": [
            {
                "type": "dashboard",
                "image": "http://example.com/banner-images/uuid.jpg",
                "link": "https://example.com",
                "created_at": "2026-01-02T00:00:00.000000Z"
            }
        ]
    },
    "type": "success"
}
```

---

### 2. 📣 Announcement System
**Purpose:** Display news, updates, and important information to users

#### Database
- **Tables:** 
  - `announcements` - Stores announcement content
  - `announcement_categories` - Categorizes announcements

#### Admin Panel
- **Routes:**
  - `/admin/setup-sections/announcement` - Manage announcements
  - `/admin/setup-sections/announcement/categories` - Manage categories
- **Features:**
  - ✅ Create/Edit/Delete announcements
  - ✅ Multi-language support (stored in DB)
  - ✅ Category management
  - ✅ Image upload support
  - ✅ Tags support
  - ✅ Status toggle
- **Controller:** `App\Http\Controllers\Frontend\AnnouncementController`

#### User API
**1. Get All Announcements**
- **Endpoint:** `GET /api/user/announcement`
- **Authentication:** Required
- **Response:** Returns announcements in **English only** with formatted data

**2. Get Announcement Categories**
- **Endpoint:** `GET /api/user/announcement/categories`
- **Authentication:** Required
- **Response:** Returns categories in **English only**

**3. Get Specific Announcement**
- **Endpoint:** `GET /api/user/announcement/{slug}`
- **Authentication:** Required
- **Response:** Returns full announcement details in **English only**

**Response Format (English Only):**
```json
{
    "message": {
        "success": ["Announcements fetched successfully"]
    },
    "data": {
        "announcements": {
            "data": [
                {
                    "id": 1,
                    "slug": "new-feature-release",
                    "title": "New Feature Release",
                    "description": "We are excited to announce...",
                    "image": "http://example.com/site-section/image.jpg",
                    "tags": ["update", "feature"],
                    "category": "Product Updates",
                    "created_at": "2026-01-02T00:00:00.000000Z"
                }
            ]
        }
    },
    "type": "success"
}
```

---

### 3. 🎫 Support Ticket System
**Purpose:** Allow users to create and manage support tickets

#### Database
- **Tables:**
  - `user_support_tickets` - Ticket records
  - `user_support_chats` - Conversation messages
  - `user_support_ticket_attachments` - File attachments

#### Admin Panel
- **Route:** `/admin/support-ticket/index`
- **Features:**
  - ✅ View all tickets
  - ✅ Reply to tickets
  - ✅ Change ticket status
  - ✅ View attachments
- **Controller:** `App\Http\Controllers\Admin\SupportTicketController`

#### User API
**1. Get My Tickets**
- **Endpoint:** `GET /api/user/support-ticket`
- **Authentication:** Required

**2. Create New Ticket**
- **Endpoint:** `POST /api/user/support-ticket`
- **Authentication:** Required
- **Body (multipart/form-data):**
  - `subject` (required, string, max:255)
  - `desc` (required, string, max:5000)
  - `attachment[]` (optional, file, max:200MB)

**3. Get Conversation**
- **Endpoint:** `GET /api/user/support-ticket/conversation/{token}`
- **Authentication:** Required

**4. Send Reply**
- **Endpoint:** `POST /api/user/support-ticket/message/send`
- **Authentication:** Required
- **Body (JSON):**
```json
{
    "support_token": "TICKET_TOKEN_HERE",
    "message": "This is my reply message."
}
```

---

### 4. 🎁 Gift Card System
**Purpose:** Fetch available gift card categories from Reloadly

#### Source
- **Service:** `App\Services\ReloadlyService`
- **API:** `https://giftcards.reloadly.com/product-categories`

#### User API
- **Endpoint:** `GET /api/user/gift-cards/categories`
- **Authentication:** Required
- **Controller:** `App\Http\Controllers\Api\V1\User\GiftCardController`
- **Response Format:**
```json
{
    "message": "Gift card categories fetched successfully",
    "data": {
        "categories": [
            {
                "id": 1,
                "name": "E-commerce"
            },
            {
                "id": 2,
                "name": "Electronics"
            }
        ]
    },
    "type": "success"
}
```

---

### 5. 🔔 Notification System (FCM)
#### User API
- **Endpoint:** `GET /api/user/notifications`
    - Fetches list of notifications for the user.
- **Endpoint:** `POST /api/user/fcm-token/update`
    - Updates the user's FCM device token.
    - **Payload:** `{"token": "device_token_string"}`
    - **Example cURL:**
        ```bash
        curl -X POST {{base_url}}/api/user/fcm-token/update \
        -H "Authorization: Bearer {{token}}" \
        -H "Content-Type: application/json" \
        -d '{"token": "sample_fcm_token"}'
        ```

### Quidax Instant Orders (Buy/Sell)
Allows users to buy and sell crypto instantly using their local NGN wallet.

#### User API
- **Endpoint:** `POST /api/user/instant/buy`
    - Initiates a Buy Order (Get Quote).
    - **Payload:**
        ```json
        {
            "from_currency": "ngn",
            "to_currency": "btc",
            "amount": 5000,
            "type": "buy"
        }
        ```
    - **Response:** Returns `order` object with `id` and `total` amount.

- **Endpoint:** `POST /api/user/instant/sell`
    - Initiates a Sell Order (Get Quote).
    - **Payload:**
        ```json
        {
            "from_currency": "btc",
            "to_currency": "ngn",
            "amount": 0.001,
            "type": "sell"
        }
        ```

- **Endpoint:** `POST /api/user/instant/confirm`
    - Confirms the order. **Debits/Credits Local Wallet**.
    - **Payload:**
        ```json
        {
            "order_id": "quidax_order_id"
        }
        ```

---

## 🔧 Technical Implementation

### Response Format
All API endpoints use the standardized `Response` helper class:

**Success Response:**
```php
Response::success(['Success message'], ['data' => $data]);
```

**Error Response:**
```php
Response::error(['Error message'], [], 404);
```

### Language Handling
- **Admin Panel:** Multi-language support (stored in database)
- **User API:** **English only** - All responses extract English (`en`) language data
- **Fallback:** If English not available, uses default language code

### Authentication
All user API endpoints require:
- **Header:** `Authorization: Bearer {token}`
- **Header:** `Accept: application/json`

---

## 📁 Files Created/Modified

### New Files
1. `/database/migrations/2026_01_02_003605_create_banners_table.php`
2. `/app/Models/Banner.php`
3. `/app/Http/Controllers/Admin/BannerController.php`
4. `/app/Http/Controllers/Api/V1/User/BannerController.php`
5. `/app/Http/Controllers/Api/V1/User/AnnouncementController.php`
6. `/app/Http/Controllers/Api/V1/User/SupportTicketController.php`
7. `/resources/views/admin/sections/banner/index.blade.php`
8. `/postman_collection_new_features.json`

### Modified Files
1. `/routes/admin.php` - Added banner routes
2. `/routes/api/user.php` - Added API routes for banners, announcements, support tickets
3. `/resources/views/admin/partials/side-nav.blade.php` - Added sidebar links

---

## 🧪 Testing

### cURL Examples

**1. Get Banners:**
```bash
curl --location 'http://127.0.0.1:8000/api/user/banner' \
--header 'Authorization: Bearer YOUR_TOKEN' \
--header 'Accept: application/json'
```

**2. Get Announcements:**
```bash
curl --location 'http://127.0.0.1:8000/api/user/announcement' \
--header 'Authorization: Bearer YOUR_TOKEN' \
--header 'Accept: application/json'
```

**3. Create Support Ticket:**
```bash
curl --location 'http://127.0.0.1:8000/api/user/support-ticket' \
--header 'Authorization: Bearer YOUR_TOKEN' \
--header 'Accept: application/json' \
--form 'subject="Payment Issue"' \
--form 'desc="I cannot see my deposit."'
```

**4. Get Gift Card Categories:**
```bash
curl --location 'http://127.0.0.1:8000/api/user/gift-card/categories' \
--header 'Authorization: Bearer YOUR_TOKEN' \
--header 'Accept: application/json'
```

**5. Get Notifications:**
```bash
curl --location 'http://127.0.0.1:8000/api/user/notifications' \
--header 'Authorization: Bearer YOUR_TOKEN' \
--header 'Accept: application/json'
```

**6. Update FCM Token:**
```bash
curl --location 'http://127.0.0.1:8000/api/user/fcm-token/update' \
--header 'Authorization: Bearer YOUR_TOKEN' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "token": "DEVICE_FCM_TOKEN_HERE"
}'
```

---

## 📦 Postman Collection
Import the file: `postman_collection_new_features.json`

**Environment Variables:**
- `{{base_url}}` - Your API base URL (e.g., `http://127.0.0.1:8000`)
- `{{token}}` - Your authentication token

---

## ✅ Checklist

- [x] Database migrations created and run
- [x] Models created with proper fillable fields
- [x] Admin CRUD controllers implemented
- [x] Admin views created
- [x] Admin routes registered
- [x] Admin sidebar links added
- [x] User API controllers created
- [x] User API routes registered
- [x] Response helpers used consistently
- [x] English-only language extraction for announcements
- [x] Postman collection created
- [x] cURL examples provided
- [x] Documentation completed

---

## 🚀 Next Steps

1. **Test all endpoints** with actual data
2. **Verify image uploads** work correctly
3. **Test file attachments** for support tickets
4. **Confirm email notifications** are sent (if enabled)
5. **Review admin panel** functionality
6. **Test pagination** on list endpoints
7. **Verify authentication** middleware is working

---

## 📞 Support

For any issues or questions regarding these features, please refer to:
- Admin Panel: `/admin/banner`, `/admin/support-ticket/index`
- API Documentation: This file
- Postman Collection: `postman_collection_new_features.json`

---

**Last Updated:** 2026-01-02
**Version:** 1.0.0

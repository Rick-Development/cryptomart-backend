# Notification System Audit Report

## Executive Summary
The notification system is currently **fragmented** and **partially disabled**. 
- **Critical missing notifications**: Users are **not** notified for Crypto Trades (Buy/Sell), Webhook events, or Login.
- **Missing Channels**: most transaction alerts only send **Email**. Push Notifications (FCM) and In-App (Database) alerts are largely missing.
- **Technical Risk**: The code uses the **Legacy Firebase (FCM) API**, which is deprecated by Google. This will stop working if not migrated to the HTTP v1 API.

---

## 1. Login Notifications
**Status**: 🔴 **Disabled**
- **Findings**: The `UserAuthController.php` has the `loginNotify($user)` call **commented out** (Lines 163, 214).
- **Impact**: Users receive no Email or Push notification when logging in.

## 2. Crypto Trading (Busha)
**Status**: 🔴 **Missing**
- **Findings**: The `BushaWebhookController` handles `order.updated` events (fills/cancellations) and updates wallet balances, but **does not trigger any notification**.
- **Impact**: Users have no record of successful trades unless they check their wallet balance manually.

## 3. Safe Haven (Transfers)
**Status**: 🔴 **Missing/Incomplete** (To be confirmed in code)
- **Findings**: Similar to Busha, webhook handlers likely update the database but fail to trigger the `notify()` method or `Notify` trait methods.

## 4. Money Out / Transactions
**Status**: 🟡 **Partial (Email Only)**
- **Findings**: Classes like `MoneyOutNotification` and `OwnBankReceiverNotification` only return `['mail']` in the `via()` method.
- **Impact**: No record in the "Notifications" tab of the app (Database) and no Push Notification (FCM).

## 5. System Architecture & Technical Debt
**Status**: 🟡 **Fragmented**
The codebase uses two competing systems:
1.  **Laravel Notifications (`app/Notifications/*`)**: Used for Money Out/Fund Transfer. Clean but currently configured for **Email only**.
2.  **`Notify` Trait (`app/Traits/Notify.php`)**: Used for Login/Auth. Supports Email, SMS, DB, and FCM.
    - **Critical Issue**: The `userFirebasePushNotification` method uses `https://fcm.googleapis.com/fcm/send`. This is the **Legacy API**. Google is shutting this down. It must be migrated to `fcm.googleapis.com/v1`.

---

## Recommendations / Implementation Plan

1.  **Enable Login Notifications**: Uncomment logic in `UserAuthController`.
2.  **Implement Trade Notifications**:
    - Create `TradeExecutedNotification`.
    - Trigger it in `BushaWebhookController` and `SafeHavenWebhookController`.
    - Ensure it supports `['mail', 'database']` and FCM.
3.  **Unify Channels**:
    - Update `MoneyOutNotification` and others to include `database` and `fcm` channels.
4.  **Fix FCM (Push)**:
    - Upgrade `Notify` trait to use Google HTTP v1 API.

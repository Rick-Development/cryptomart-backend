# Notification System Audit & Documentation

The objective is to audit the entire notification system (Email, In-App/Firebase, Database) and provide a comprehensive document detailing the current state, issues, and recommendations.

## Steps

1.  **Audit Laravel Notifications (`app/Notifications`)**
    *   Analyze all classes in `app/Notifications/User` and `app/Notifications/Admin`.
    *   Identify which channels (`mail`, `database`, `fcm`, `sms`) are implemented in each.
    *   Identify missing critical channels (e.g., Transaction alerts often miss In-App/Push).

2.  **Audit `Notify` Trait (`app/Traits/Notify.php` / `app/Models/Traits/Notify.php`)**
    *   Analyze the custom notification implementation.
    *   Review Firebase Cloud Messaging (FCM) integration (Legacy vs V1).
    *   Review In-App notification storage logic (`InAppNotification` model).

3.  **Analyze Usage Patterns**
    *   Identify where `Authorization` notifications (OTP, Welcome) are handled.
    *   Identify where `Transaction` notifications (Transfer, Withdrawal) are handled.
    *   Determine if there is a split between `Notification` classes and the `Notify` trait.

4.  **Create Documentation Artifact**
    *   **Overview**: Architecture description.
    *   **Inventory**: List of notifications and their channels.
    *   **Issues**: Identified gaps (e.g., missing push notifications for money out).
    *   **Recommendations**: Steps to unify and fix.

5.  **Review with User**
    *   Present the findings and get approval for specific fixes.

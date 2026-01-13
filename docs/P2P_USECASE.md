# P2P System Use Case Documentation

This document outlines the core use cases and functional flows for the P2P (Peer-to-Peer) trading system implemented within the platform.

## 1. Actor Roles

| Role | Description |
| :--- | :--- |
| **Maker (Advertiser)** | A user who creates an advertisement (Ad) to buy or sell assets. |
| **Taker (Trader)** | A user who responds to an existing Ad to initiate a trade. |
| **Merchant** | A high-volume trader with advanced KYC verification and higher permissions. |
| **Admin** | System administrator responsible for dispute resolution and configuration. |

---

## 2. Core Use Cases

### 2.1 Advertisement Management (Maker)
*   **Create Buy Ad**: Maker offers to buy crypto using fiat (e.g., Buy USDT with NGN).
*   **Create Sell Ad**: Maker offers to sell crypto for fiat. **Escrow Requirement**: Crypto is locked from the Maker's wallet upon Ad creation.
*   **Configure Pricing**: 
    *   *Fixed*: Set a specific price.
    *   *Floating*: Price tracks market rates with a margin percentage.
*   **Manage Status**: Toggle Ads online/offline to control visibility in the marketplace.

### 2.2 Marketplace Interaction (Taker)
*   **Browse Ads**: Filter Ads by asset (USDT, BTC), fiat (NGN, USD), payment method, or amount.
*   **Initiate Trade**: Select an Ad and enter the desired amount. The system validates if the Taker meets the Maker's requirements (e.g., KYC level).

### 2.3 Order Lifecycle (Escrow Flow)

#### Workflow: Sell Ad (Maker sells to Taker)
1.  **Order Initiation**: Taker opens an order. Maker's crypto is already locked in escrow.
2.  **Payment Stage**: Taker pays the Maker via the specified external payment method (e.g., Bank Transfer).
3.  **Payment Mark**: Taker marks the order as "Paid".
4.  **Verification**: Maker verifies receipt of fiat in their bank account.
5.  **Release**: Maker clicks "Release". System moves crypto from escrow to Taker's wallet.

#### Workflow: Buy Ad (Maker buys from Taker)
1.  **Order Initiation**: Taker opens an order. **Escrow Requirement**: Taker's crypto is locked upon order creation.
2.  **Payment Stage**: Maker pays the Taker via external payment method.
3.  **Payment Mark**: Maker marks the order as "Paid".
4.  **Verification**: Taker verifies receipt of fiat.
5.  **Release**: Taker clicks "Release". System moves crypto from escrow to Maker's wallet.

---

## 3. Communication & Support

### 3.1 Order Chat
*   Users can chat in real-time within the order context to share payment proof or clarify details.
*   Automated messages (Auto-Reply) can be configured by the Maker.

### 3.2 Dispute (Appeal) System
*   If a payment is made but crypto isn't released (or vice versa), either party can raise an **Appeal**.
*   **Admin Intervention**: Admins review chat history and evidence (uploaded screenshots) to manually release funds to the correct party or refund the escrow.

---

## 4. Safety & Compliance

### 4.1 KYC Enforcement
*   **Tier 1**: Can browse and perform limited trades.
*   **Tier 2**: Required to create advertisements.
*   **Tier 3 (Merchant)**: Higher limits and verified badge.

### 4.2 Merchant Performance Stats
*   **Completion Rate**: Ratio of successfully finished trades to total initiated trades.
*   **Avg. Release Time**: Speed at which a user releases crypto after payment.
*   **Rating/Feedback**: Star ratings and comments left by counterparties after trade completion.

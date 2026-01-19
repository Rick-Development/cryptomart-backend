# Crypto Loans System Specification

## 1. Overview & Core Logic

### High-Level Concept
The Crypto Loans module enables users to:
1.  **Lend (Invest)**: Users provide USDT for a fixed duration to earn interest.
2.  **Borrow**: Users borrow USDT/USDC/BTC by collateralizing their existing crypto assets.

**Key Distinction**: This is **NOT** a public P2P order book.
-   Lending offers are hidden.
-   Borrowing requests are matched internally by the system.
-   Users do not see "ads" or other users' names.

### Primary Flows

#### Lending Flow (Investor)
1.  **Entry**: "Lend" button.
2.  **Input**:
    -   **Asset**: USDT, USDC, or BTC.
    -   **Investment Amount**: Amount of the selected asset.
    -   **Duration**: 30, 60, 90, or 180 days.
3.  **Validation**: Checks if User Wallet Balance >= Investment Amount.
4.  **Action**:
    -   Funds are **escrowed** (deducted from available balance, locked in system).
    -   Offer Status: **Pending**.
    -   User can cancel pending offers to release funds immediately.
5.  **Activation**: Offer becomes **Active** only when matched with a borrower. Interest accrual starts then.

#### Borrowing Flow (Borrower)
1.  **Entry**: "Borrow" button.
2.  **Input**:
    -   **Borrow Amount** (USDT, USDC, or BTC).
    -   **Collateral Asset** (e.g., SOL, BNB).
    -   **Collateral Amount**: Auto-calculated by system.
        -   **Formula**: `Collateral Value = Loan Amount + 25%` (125% Collateralization).
        -   User **cannot** edit this field.
3.  **Logic**:
    -   System searches for matching **Pending Lending Offers**.
    -   **Match Found**: Loan activates, funds disbursed to borrower, collateral locked.
    -   **No Match**: Request stays pending or returns error ("Try again later").

### My Loans Dashboard
-   **Borrower View**: Outstanding loans, repayment options, auto-collateral details.
-   **Lender View**: Pending offers (can cancel), Active loans (earning interest), Completed loans.

---

## 2. Use Case Specification

| ID | Title | Actor | Description |
| :--- | :--- | :--- | :--- |
| **UC-01** | **Lend Funds** | Lender | User enters amount & duration. System validates balance, escrows funds, and creates a **Pending** offer. |
| **UC-02** | **Cancel Offer** | Lender | User cancels a **Pending** offer. System releases escrowed funds back to wallet. |
| **UC-03** | **Activate Loan** | System | Matches a Borrow Request with a Pending Lending Offer. State changes to **Active**. Interest starts. |
| **UC-04** | **Borrow Funds** | Borrower | User requests loan, selects collateral. System calculates LTV (80%), validates collateral, and searches for a match. |
| **UC-05** | **Match Fail** | System | If no lending offer matches the borrow request, return error or keep request pending. |
| **UC-06** | **Disburse Loan** | System | On match: Lock collateral, transfer loan principal to Borrower. Status = **Active**. |
| **UC-07** | **View Loans** | User | View Active, Pending, and Completed loans in "My Loans" dashboard. |
| **UC-08** | **Repay Loan** | Borrower | Borrower repays Principal + Interest. System releases collateral. Lender gets paid. |
| **UC-09** | **Settlement** | System | Credits Lender with Principal + Interest upon repayment. |
| **UC-10** | **Loan Limit** | System | Prevent borrowing if user already has an active outstanding loan. |
| **UC-11** | **Liquidation** | System | Auto-sell collateral if value drops below **Liquidation Threshold**. Settle lender, return excess to borrower. |
| **UC-12** | **Interest** | System | Calculate interest at a fixed rate of **5% Monthly**. |

---

## 3. Component Architecture & Flow

### 3.1 Entry Layer
-   **Loans Entry**: Buttons for `Lend` | `Borrow`.
-   **Summary Widget**: Shows Total Outstanding, Total Collateral.

### 3.2 Lending Components
-   **Form**: Amount Input, Duration Selector, Balance Display (`+ Add Funds`).
-   **Escrow Service (Backend)**: Locks funds (Move from `Available` to `Locked`).
-   **Offers Manager**: Lists pending offers.

### 3.3 Borrowing Components
-   **Form**: Borrow Amount, Asset Selector, **Auto-Collateral Calculator** (Read-only for user).
-   **Validation Engine**: Checks "One Active Loan" rule, Collateral Balance.
-   **Matching Engine**: The core logic that pairs Borrow Request ↔ Lending Offer.

### 3.4 Loan Lifecycle Components
-   **Activation Service**: Updates state to `Active`, starts `Interest Timer`.
-   **Disbursement Service**: Transfers principal to borrower wallet.
-   **Repayment Module**: Handles user repayment action → Unlocks collateral.
-   **Liquidation Engine**: Monitors prices continuously. Triggers sell-off if LTV breaches limit.

---

## 4. Borrowable Assets & Custody (Quidax Integration)

### Supported Assets
-   **Lendable & Borrowable**: USDT, USDC, BTC.
    -   Users can invest (lend) these assets to earn interest.
    -   Users can borrow these assets by providing collateral.
-   **Collateral**: SOL, BNB, etc. (Configurable).

### Custody Model (Quidax)
-   **Principle**: All funds remain in Quidax wallets. No funds leave the exchange environment.
-   **Escrow Logic**: Implemented as **Logical Locks** in the Cryptomart database.
    -   *Available Balance* on Quidax is the source of truth.
    -   *Locked Balance* is tracked internally in Cryptomart DB.
    -   User cannot withdraw "Locked" funds.

### Integration Flow
1.  **Lending**:
    -   User clicks Lend.
    -   **Backend**: Checks Quidax balance.
    -   **DB Update**: `Available -= Amount`, `Locked += Amount`.
    -   **Quidax**: No transaction yet.
2.  **Matching & Disbursement**:
    -   Match found.
    -   **Quidax Transfer**: Backend triggers internal transfer from Lender User → Borrower User (or via logical pool).
    -   **Collateral**: Borrower's collateral is logically locked.
3.  **Repayment**:
    -   Borrower pays.
    -   **Quidax Transfer**: Funds move Borrower → Lender.
    -   Collateral unlocked.

---

## 5. Profit & Control Logic

### Revenue Model
1.  **Interest Spread**:
    -   **Total Interest**: **5% Monthly**.
    -   **Split**: Defined by platform (e.g., Lender gets 4%, Platform gets 1%).
2.  **Liquidation Fees**:
    -   Fee (e.g., 2%) taken from collateral during liquidation.
3.  **Origination Fees (Optional)**:
    -   Small upfront fee (e.g., 1%) on loan activation.

### Control Mechanisms
1.  **One-Loan Rule**: A user can only have **one** active loan at a time to prevent over-leverage.
2.  **Collateralization Rules**:
    -   **Requirement**: Collateral value must be **25% above** the borrowing amount.
    -   **Ratio**: 125% Collateral-to-Loan Ratio.
    -   **Liquidation Threshold**: Configurable (e.g., if value drops below 125%).
3.  **No P2P Interaction**: System controls matching to prevent manipulation.
4.  **Logical Escrow**: Prevents capital flight; funds require system permission to move.
5.  **Price Oracle**: Uses reliable feeds to prevent flash-crash liquidations.

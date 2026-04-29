# Affiliate System Setup And Test Checklist

## Setup

1. Run migrations:
   ```bash
   php artisan migrate
   ```
2. Confirm mail settings in `.env` so affiliate notifications can be delivered.
3. Open `/admin/affiliates` as an admin and set:
   - Default commission type: `percentage` or `fixed`
   - Default commission value
   - Minimum payout amount

## Admin Usage

1. Affiliates register at `/affiliate/register`.
2. Admin reviews new accounts at `/admin/affiliates`.
3. Approving an account generates the referral code and emails the affiliate.
4. Admin can open each affiliate profile to edit custom commission rates and payment details.
5. Pending commissions can be approved, rejected, or marked paid.
6. Payout requests can be approved, rejected, or marked paid.
7. CSV reports are available from `/admin/affiliates/export`.

## Manual Test Checklist

- Affiliate registration:
  - Submit valid registration.
  - Confirm duplicate email is rejected.
  - Confirm login is blocked until admin approval.
- Admin approval:
  - Approve affiliate.
  - Confirm referral code is generated.
  - Confirm affiliate can log in.
- Referral link tracking:
  - Visit `/?ref=AFFILIATECODE`.
  - Confirm `nai_affiliate_ref` and `nai_affiliate_click` cookies are created.
  - Visit a different referral link and confirm cookies change.
  - Confirm click count increases in affiliate/admin dashboards.
- Cookie tracking:
  - Place an order after clicking an affiliate link.
  - Confirm invoice has `affiliate_id`, `affiliate_click_id`, and `affiliate_code`.
- Commission creation:
  - Pay the invoice fully.
  - Confirm one pending commission is created.
  - Run a second payment callback/admin payment attempt and confirm no duplicate commission is created.
- Refund/cancel reversal:
  - Delete/cancel the invoice from admin.
  - Confirm unpaid/pending commission is marked rejected.
- Own purchase prevention:
  - Place an order using the affiliate's own email or phone.
  - Confirm no affiliate is attached and no commission is created.
- Payout request flow:
  - Approve a commission.
  - Request payout below minimum and confirm validation error.
  - Request payout at/above minimum and confirm pending request appears in admin.
  - Approve and mark payout paid.
- Reports:
  - Export CSV and confirm affiliate rows include clicks, paid orders, and commission totals.

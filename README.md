# Aura Spa

Aura Spa is a headless WordPress + React project. WordPress manages the business logic, WooCommerce flow, BookPro booking data, and PayFast payment orchestration. The React frontend sits in front of WordPress as the public UI layer.

## Project structure

- `frontend/` — Vite + React + TypeScript frontend
- `wp-content/plugins/` — WordPress plugins including BookPro and Aura Spa payment integration
- `wp-content/themes/` — theme files if added later
- `wp-config.php` — WordPress config
- `index.php` — WordPress bootstrap

## Core architecture

- WordPress backend
  - WooCommerce orders
  - BookPro appointment/service records
  - custom payment gateway and callbacks
  - REST API endpoints consumed by the frontend

- React frontend
  - presentation layer for the spa website
  - service selection and booking form
  - checkout initiation for WooCommerce/PayFast flow

## Local setup

### Frontend

```bash
cd frontend
npm install
npm run dev
```

### WordPress / Studio

This project is intended for a WordPress Studio environment.

- Start the local site in Studio
- Ensure WooCommerce + BookPro plugin logic is active
- Use the WordPress REST API endpoints from the frontend

## Booking/payment flow

1. React fetches available services from WordPress
2. User selects a service and submits a booking form
3. Frontend calls the WordPress booking-order API
4. WordPress creates a WooCommerce order
5. User is redirected to PayFast checkout
6. PayFast callback confirms payment
7. WordPress updates the BookPro booking/order record

## Production notes

- Keep WordPress as the source of truth for all booking/payment data
- Keep React focused on UI and user flow
- Do not create booking logic in the frontend that bypasses WordPress/WooCommerce

## Important environment notes

- MailPoet is intentionally not used in this SQLite-backed Studio setup because it crashes in that environment
- A SQLite-safe mail plugin such as WP Mail SMTP should be used instead

## Related folders

- `wp-content/plugins/auraspa-payfast-gateway/` — PayFast WooCommerce gateway scaffold
- `wp-content/plugins/ovabookpro/` — BookPro booking/service data model
- `frontend/src/` — frontend application source

## License

This project is a local WordPress project and follows the licensing of the included WordPress installation and plugin dependencies.

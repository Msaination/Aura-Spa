# Aura Spa Frontend

This is the React frontend for Aura Spa, built on:

- React + Vite
- TypeScript
- Tailwind CSS
- shadcn/ui-style component approach
- WordPress REST API as the backend source

## Setup

1. Install dependencies:
   ```bash
   npm install
   ```
2. Copy `.env.example` to `.env` and update your WordPress API URL if needed.
3. Run the app:
   ```bash
   npm run dev
   ```

## Default API setup

The frontend expects a WordPress instance exposing a REST namespace like:

- `http://localhost:8080/wp-json`
- `aurabookpro/v1`

## Project structure

- `src/components` — reusable UI pieces
- `src/lib` — helper functions and API calls
- `src/data` — mock or sample data
- `src/types` — shared TypeScript types

## Notes

This project is the public-facing layer for AuraBookPro while WordPress remains the backend/admin source of truth.

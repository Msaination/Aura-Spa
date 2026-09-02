# BookPro service import specification

This file is the final BookPro-ready seed list for the Aura Spa treatment catalog currently represented by `treatmentServices` in the frontend data.

## Target import format

The live importer already expects CSV rows shaped like:

`category,name,price,duration,note,type`

Recommended rules:

- `type` should always be `service` for this list
- `category` should match the BookPro service group or type bucket
- `price` should be stored as a numeric or "From Rxxx" string; the importer normalizes it automatically
- `duration` should be kept in the human-friendly format already used by the UI
- `note` is optional and should only be used for child packages or special bundles

## Import usage

Run the import with the project script:

```bash
cd /Users/mac/Studio/aura-spa
~/.local/bin/studio wp eval-file scripts/import_treatment_menu.php
```

This importer reads the frontend CSV file at:

`frontend/src/data/treatment-menu-import.csv`

For BookPro-specific service seeding, the equivalent source here is:

`scripts/bookpro_service_seed.csv`

## Service filtering

Use the `treatmentServices` list as the BookPro payload. Keep `treatmentProducts` separate and only import the product extras into WooCommerce as products, not BookPro services.

## Source of truth

The final DB-ready seed list in this folder is intentionally mapped to the current frontend catalog and is ready to be used as the authoritative BookPro service seed set.

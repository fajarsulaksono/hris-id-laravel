---
paths:
  - routes/api.php
---

# Routes

## API routes share one ApiController via route defaults
API routes are generated from ApiModules in routes/api.php. Each route carries ->defaults('module', $key) so ApiController reads the module key from $request->route('module') — do NOT declare it as a controller method param. view_* routes use apiRole:view_X; mutations use apiRole:manage_X (kept for all modules; controller aborts 403 when not mutable).

---
paths:
  - 'app/Http/Controllers/Api/**'
---

# Api

## API resources: use ->response() and call custom setters
Single resources in ApiController/AuthController are returned via (new XResource($m))->response() which in Laravel 12 returns Illuminate\Http\JsonResponse (assert JsonPath 'data.*'). JsonResources serialized via response()->json() omit the 'data' wrapper. To normalize code/name on create/update, call custom setters (setCode/setName) explicitly with ApiController::assign() — Laravel does NOT auto-invoke plain setX methods.

---
paths:
  - 'app/Http/Controllers/Api/**'
---

# Api

## API resources: use ->response() and call custom setters
Single resources in ApiController/AuthController are returned via (new XResource($m))->response() which in Laravel 12 returns Illuminate\Http\JsonResponse (assert JsonPath 'data.*'). JsonResources serialized via response()->json() omit the 'data' wrapper. To normalize code/name on create/update, call custom setters (setCode/setName) explicitly with ApiController::assign() — Laravel does NOT auto-invoke plain setX methods.

## Module self-service (attendances): identity & scope dari token
Modul API dengan `self_service: true` (mis. attendances via ability view_my_attendance/manage_my_attendance): ApiController memaksa employee_id = id user token (abaikan body), indeks/show di-scope ke record sendiri kecuali user punya ability `managed_by` (view_attendance) atau SUPER_ADMIN, dan `self_unique` (attendance_date) menolak duplikat tsb per karyawan (422). Waktu masuk/keluar dinormalisasi ke HH:MM via normalizeTimeFields agar konsisten antar DB.

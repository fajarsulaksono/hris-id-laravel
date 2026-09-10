---
paths:
  - 'app/Http/Resources/Api/**'
---

# Resources Api

## AuthUserResource exposes abilities for mobile role-aware UI
AuthUserResource wajib menyertakan daftar 'abilities' (dihitung via app(Security::class)->can per config('hris.abilities')) — dipakai app Flutter (hris-id-flutter) untuk menu role-aware tanpa hardcode nama role. Jangan hapus field ini.

<?php

namespace App\Domain\Encryptor;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Port dari EventListener\EncryptorSubscriber (SemartHris): mengenkripsi field
 * gaji/pajak secara transparan saat ditulis dan mendekripsinya saat dibaca.
 *
 * Nilai yang disimpan adalah hasil Encryptor::encrypt(), yang sudah menyertakan
 * kunci simetris acak sebagai sufiks (base64("cipher#key")), sehingga tidak
 * bergantung pada kolom kunci pendamping.
 *
 * @example protected $casts = ['benefit_value' => SalaryCast::class];
 */
class SalaryCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $value = (string) $value;

        if ($value === '' || ! preg_match('/#([0-9a-f]{40})$/', (string) base64_decode($value, true), $m)) {
            return $value ?: null;
        }

        return app(Encryptor::class)->decrypt($value, $m[1]);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return app(Encryptor::class)->encrypt((string) $value);
    }
}
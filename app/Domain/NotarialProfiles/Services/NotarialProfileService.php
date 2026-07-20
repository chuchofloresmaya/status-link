<?php

namespace App\Domain\NotarialProfiles\Services;

use App\Models\NotarialProfile;
use App\Models\Notary;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NotarialProfileService
{
    public function setDefault(NotarialProfile $profile): void
    {
        DB::transaction(function () use ($profile) {
            $profile->notary->notarialProfiles()->whereKeyNot($profile->id)->update(['is_default' => false]);
            $profile->update(['is_default' => true, 'is_active' => true]);
        });
    }

    public function toggleActive(NotarialProfile $profile): void
    {
        DB::transaction(function () use ($profile) {
            $profile->update(['is_active' => ! $profile->is_active]);
            if (! $profile->is_active && $profile->is_default) {
                $profile->update(['is_default' => false]);
                $replacement = $profile->notary->activeNotarialProfiles()->whereKeyNot($profile->id)->first();
                if ($replacement) {
                    $this->setDefault($replacement);
                }
            }
        });
    }

    public function storeForNotary(Notary $notary, array $data): NotarialProfile
    {
        return DB::transaction(function () use ($notary, $data) {
            $data = $this->prepareLogo($data);
            $makeDefault = ($data['is_default'] ?? false) || (($data['is_active'] ?? true) && ! $notary->defaultNotarialProfile()->exists());
            $profile = $notary->notarialProfiles()->create($data + ['is_default' => $makeDefault]);
            if ($makeDefault) {
                $this->setDefault($profile);
            }

            return $profile;
        });
    }

    public function updateProfile(NotarialProfile $profile, array $data): NotarialProfile
    {
        return DB::transaction(function () use ($profile, $data) {
            $old = $profile->logo_path;
            $data = $this->prepareLogo($data);
            $profile->update($data);
            if (isset($data['logo_path']) && $old && $old !== $data['logo_path']) {
                Storage::disk('public')->delete($old);
            }
            if ($data['is_default'] ?? false) {
                $this->setDefault($profile);
            }

            return $profile->refresh();
        });
    }

    private function prepareLogo(array $data): array
    {
        $logo = Arr::pull($data, 'logo');
        if ($logo) {
            $data['logo_path'] = $logo->store('notarial-profiles/logos', 'public');
        }

        return $data;
    }
}

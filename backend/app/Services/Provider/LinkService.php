<?php

namespace App\Services\Provider;

use App\Models\User;
use App\Models\ProviderLink;
use Exception;

class LinkService
{
    /**
     * Get all links for provider
     */
    public function getAll(User $user): array
    {
        if (!$user->providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        return $user->providerProfile->links()->orderBy('order')->get()->toArray();
    }

    /**
     * Add link
     */
    public function addLink(User $user, array $data): array
    {
        if (!$user->providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        // Check if URL already exists
        if ($user->providerProfile->links()->where('url', $data['url'])->exists()) {
            throw new Exception('This URL already exists.');
        }

        $maxOrder = $user->providerProfile->links()->max('order') ?? -1;

        try {
            $link = $user->providerProfile->links()->create([
                'title' => $data['title'],
                'url' => $data['url'],
                'order' => $maxOrder + 1,
            ]);

            return $link->toArray();
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'unique')) {
                throw new Exception('This URL already exists.');
            }
            throw $e;
        }
    }

        /**
     * Update link
     */
    public function updateLink(User $user, string $linkId, array $data): array
    {
        if (!$user->providerProfile) {
            throw new Exception('Provider profile not found.');
        }

        $link = $user->providerProfile->links()
            ->where('id', $linkId)
            ->first();

        if (!$link) {
            throw new Exception('Link not found.');
        }

        // Check if new URL already exists (excluding current link)
        if ($data['url'] !== $link->url && $user->providerProfile->links()
            ->where('url', $data['url'])
            ->where('id', '!=', $linkId)
            ->exists()) {
            throw new Exception('This URL already exists.');
        }

        $link->update([
            'title' => $data['title'],
            'url' => $data['url'],
        ]);

        return $link->toArray();
    }


    /**
     * Remove link
     */
    public function removeLink(User $user, string $linkId): void
    {
        $link = $user->providerProfile->links()
            ->where('id', $linkId)
            ->firstOrFail();

        $link->delete();
    }

    /**
     * Reorder links
     */
    public function reorderLinks(User $user, array $linkIds): array
    {
        foreach ($linkIds as $order => $linkId) {
            $user->providerProfile->links()
                ->where('id', $linkId)
                ->update(['order' => $order]);
        }

        return $this->getAll($user);
    }
}

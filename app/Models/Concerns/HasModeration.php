<?php

namespace App\Models\Concerns;

trait HasModeration
{
    public const MODERATION_PENDING = 'pending';
    public const MODERATION_APPROVED = 'approved';
    public const MODERATION_REJECTED = 'rejected';

    public function scopeApproved($query)
    {
        return $query->where($this->getTable().'.moderation_status', self::MODERATION_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where($this->getTable().'.moderation_status', self::MODERATION_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where($this->getTable().'.moderation_status', self::MODERATION_REJECTED);
    }

    public function isApproved(): bool
    {
        return $this->moderation_status === self::MODERATION_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->moderation_status === self::MODERATION_PENDING;
    }

    public function moderate(string $action, ?string $comment = null): void
    {
        $this->update([
            'moderation_status' => $action === 'approve'
                ? self::MODERATION_APPROVED
                : self::MODERATION_REJECTED,
            'moderated_at' => now(),
            'moderation_comment' => $comment,
        ]);
    }
}

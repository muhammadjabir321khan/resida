<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LeaseDocument extends Model
{
    use BelongsToLandlord;

    public const CATEGORY_LEASE = 'lease_agreement';

    public const CATEGORY_MOVE_IN = 'move_in';

    public const CATEGORY_IDENTIFICATION = 'identification';

    public const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'lease_id',
        'title',
        'category',
        'file_path',
        'original_filename',
        'is_visible_to_tenant',
    ];

    protected function casts(): array
    {
        return [
            'is_visible_to_tenant' => 'boolean',
        ];
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class, 'lease_id');
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            self::CATEGORY_LEASE => __('Lease agreement'),
            self::CATEGORY_MOVE_IN => __('Move-in checklist'),
            self::CATEGORY_IDENTIFICATION => __('Identification'),
            default => __('Other'),
        };
    }

    public function deleteFile(): void
    {
        if ($this->file_path) {
            Storage::disk('public')->delete($this->file_path);
        }
    }
}

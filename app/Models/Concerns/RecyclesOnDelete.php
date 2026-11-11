<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use LogicException;

/**
 * Recycle Bin behaviour: delete() only soft deletes, forceDelete() is impossible.
 * Records who deleted, optional reason, and cascades to child relations listed in
 * $recycleChildren (children are marked with the SAME deleted_at as the parent, so
 * restoring the parent only restores the children deleted with it).
 */
trait RecyclesOnDelete
{
    use SoftDeletes;

    /** deletion_reason marker on children recycled together with their parent. */
    const CASCADE_MARK = 'with-parent';

    /** Optional reason for the next delete(); read by the deleted hook. */
    public $pendingDeletionReason = null;

    /** @var mixed deleted_at value held between restoring and restored events */
    protected $restoringStamp = null;

    public static function bootRecyclesOnDelete()
    {
        static::deleted(function ($model) {
            if (!$model->trashed()) {
                return;
            }
            $stamp = $model->{$model->getDeletedAtColumn()};
            $meta = [
                'deleted_by' => Auth::id(),
                'deletion_reason' => $model->pendingDeletionReason,
            ];
            $model->newQueryWithoutScopes()->whereKey($model->getKey())->toBase()->update($meta);
            $model->forceFill($meta)->syncOriginalAttributes(array_keys($meta));

            foreach ($model->recycleChildren ?? [] as $relation) {
                $model->{$relation}()->toBase()->update([
                    'deleted_at' => $stamp,
                    'deleted_by' => $meta['deleted_by'],
                    'deletion_reason' => self::CASCADE_MARK,
                ]);
            }
        });

        static::restoring(function ($model) {
            $model->restoringStamp = $model->getRawOriginal($model->getDeletedAtColumn());
        });

        static::restored(function ($model) {
            $clear = ['deleted_at' => null, 'deleted_by' => null, 'deletion_reason' => null];
            if ($model->restoringStamp) {
                foreach ($model->recycleChildren ?? [] as $relation) {
                    $model->{$relation}()->withTrashed()
                        ->where($model->{$relation}()->getRelated()->getQualifiedDeletedAtColumn(), $model->restoringStamp)
                        ->where($model->{$relation}()->getRelated()->qualifyColumn('deletion_reason'), self::CASCADE_MARK)
                        ->toBase()->update($clear);
                }
            }
            $model->newQueryWithoutScopes()->whereKey($model->getKey())->toBase()
                ->update(['deleted_by' => null, 'deletion_reason' => null]);
            $model->forceFill(['deleted_by' => null, 'deletion_reason' => null])
                ->syncOriginalAttributes(['deleted_by', 'deletion_reason']);
            $model->restoringStamp = null;
        });
    }

    /** Soft delete with an optional reason. */
    public function recycle($reason = null)
    {
        $this->pendingDeletionReason = $reason;
        return $this->delete();
    }

    /** Permanent deletion is not allowed anywhere in the app. */
    public function forceDelete()
    {
        throw new LogicException(static::class . ': permanent deletion is disabled. Records can only be moved to the Recycle Bin.');
    }
}

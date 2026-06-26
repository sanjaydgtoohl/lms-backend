<?php

namespace App\Support;

use App\Models\User;

class UserAccessScope
{
    public static function isSuperAdmin(?User $user): bool
    {
        return $user !== null && $user->hasRole('Super Admin');
    }

    /**
     * Organisation IDs the user may access. Empty array for Super Admin means all organisations.
     *
     * @return array<int>
     */
    public static function getAccessibleOrganisationIds(User $user): array
    {
        if (self::isSuperAdmin($user)) {
            return [];
        }

        $pivotIds = $user->organisations()
            ->pluck('organisations.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $legacyIds = $user->organisation_id ? [(int) $user->organisation_id] : [];

        return array_values(array_unique(array_filter(array_merge($pivotIds, $legacyIds))));
    }

    /**
     * Primary / process organisation for multi-org users.
     */
    public static function getProcessOrganisationId(User $user): ?int
    {
        if ($user->organisation_id) {
            return (int) $user->organisation_id;
        }

        $accessible = self::getAccessibleOrganisationIds($user);

        return $accessible[0] ?? null;
    }

    /**
     * Resolve dashboard organisation filter for the authenticated user.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public static function resolveOrganisationFilter(User $user, array $filters): array
    {
        if (self::isSuperAdmin($user)) {
            return $filters;
        }

        $accessible = self::getAccessibleOrganisationIds($user);
        $requested = array_values(array_filter(array_map('intval', $filters['organisation_ids'] ?? [])));

        if (!empty($requested)) {
            $allowed = !empty($accessible)
                ? array_values(array_intersect($requested, $accessible))
                : $requested;

            $filters['organisation_ids'] = !empty($allowed)
                ? $allowed
                : self::defaultOrganisationScope($user, $accessible);
        } else {
            $filters['organisation_ids'] = self::defaultOrganisationScope($user, $accessible);
        }

        return $filters;
    }

    /**
     * Default org scope:
     * - single org user → that org
     * - multi org user → process organisation only
     * - no org assignment → empty (aggregate only)
     *
     * @param array<int> $accessible
     * @return array<int>
     */
    public static function defaultOrganisationScope(User $user, array $accessible): array
    {
        if (empty($accessible)) {
            return [];
        }

        if (count($accessible) === 1) {
            return $accessible;
        }

        $processOrg = self::getProcessOrganisationId($user);
        if ($processOrg !== null && in_array($processOrg, $accessible, true)) {
            return [$processOrg];
        }

        return [$accessible[0]];
    }

    /**
     * User IDs whose leads/pre-leads/briefs the current user can see:
     * self + all descendant child users. Empty for Super Admin (no restriction).
     *
     * @return array<int>
     */
    public static function getVisibleUserIds(User $user): array
    {
        if (self::isSuperAdmin($user)) {
            return [];
        }

        $ids = [(int) $user->id];
        self::collectDescendantIds($user, $ids);

        return array_values(array_unique($ids));
    }

    /**
     * @param array<int> $ids
     */
    private static function collectDescendantIds(User $user, array &$ids): void
    {
        $children = $user->children()->get(['users.id']);

        foreach ($children as $child) {
            $childId = (int) $child->id;
            if (in_array($childId, $ids, true)) {
                continue;
            }

            $ids[] = $childId;
            self::collectDescendantIds($child, $ids);
        }
    }

    /**
     * Apply visible-user constraint to a query on the given columns.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<int, string> $columns
     */
    public static function applyVisibleUserFilter($query, User $user, array $columns, ?string $table = null): void
    {
        $visibleUserIds = self::getVisibleUserIds($user);

        if (empty($visibleUserIds)) {
            return;
        }

        $query->where(function ($builder) use ($columns, $visibleUserIds, $table) {
            foreach ($columns as $index => $column) {
                $qualified = $table ? "{$table}.{$column}" : $column;

                if ($index === 0) {
                    $builder->whereIn($qualified, $visibleUserIds);
                    continue;
                }

                $builder->orWhereIn($qualified, $visibleUserIds);
            }
        });
    }
}

<?php

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected string $modelClass;

    public function __construct()
    {
        $this->modelClass = $this->getModelClass();
    }

    /**
     * Each repository must return its model class
     */
    abstract protected function getModelClass(): string;

    public function all(int $perPage = 15)
    {
        $modelClass = $this->modelClass;
        return $modelClass::paginate($perPage);
    }

    public function find(int $id): ?Model
    {
        $modelClass = $this->modelClass;
        return $modelClass::find($id);
    }

    public function create(array $data): Model
    {
        $modelClass = $this->modelClass;
        return $modelClass::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->find($id);
        if (!$model) return false;
        return $model->update($data);
    }

    public function delete(int $id): bool
    {
        $model = $this->find($id);
        if (!$model) return false;
        return $model->delete();
    }

    public function findWithRelations(int $id, array $relations = []): ?Model
    {
        $modelClass = $this->modelClass;
        return $modelClass::with($relations)->find($id);
    }

    public function search(array $criteria, int $perPage = 15)
    {
        $modelClass = $this->modelClass;
        $query = $modelClass::query();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query->paginate($perPage);
    }

    public function findBy(array $conditions): Collection
    {
        $modelClass = $this->modelClass;
        return $modelClass::where($conditions)->get();
    }

    public function findFirstBy(array $conditions): ?Model
    {
        $modelClass = $this->modelClass;
        return $modelClass::where($conditions)->first();
    }

    public function countBy(array $conditions): int
    {
        $modelClass = $this->modelClass;
        return $modelClass::where($conditions)->count();
    }
}
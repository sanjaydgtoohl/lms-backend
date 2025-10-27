<?php
namespace App\Repositories;

use App\Contracts\Repositories\AgencyGroupRepositoryInterface;
use App\Models\AgencyGroup;

class AgencyGroupRepository implements AgencyGroupRepositoryInterface
{
    protected $model;
    public function __construct(AgencyGroup $model) { $this->model = $model; }
    public function getAll() { return $this->model->orderBy('name')->paginate(10); }
    public function findById($id) { return $this->model->findOrFail($id); }
    public function create(array $data) { return $this->model->create($data); }
    public function update($id, array $data) { $model = $this->model->findOrFail($id); $model->update($data); return $model; }
    public function delete($id) { return $this->model->findOrFail($id)->delete(); }
    public function findBySlug(string $slug) { return $this->model->where('slug', $slug)->first(); }
}

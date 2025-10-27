<?php
namespace App\Services;

use App\Contracts\Repositories\AgencyGroupRepositoryInterface;
use Illuminate\Support\Str;

class AgencyGroupService
{
    protected $repo;
    public function __construct(AgencyGroupRepositoryInterface $repo) { $this->repo = $repo; }
    public function getAll() { return $this->repo->getAll(); }
    public function getById($id) { return $this->repo->findById($id); }
    public function delete($id) { return $this->repo->delete($id); }

    public function create(array $data) {
        $data['slug'] = $this->createUniqueSlug($data['name']);
        $data['status'] = $data['status'] ?? '1';
        return $this->repo->create($data);
    }
    public function update($id, array $data) {
        if (isset($data['name'])) {
            $model = $this->repo->findById($id);
            if ($model->name !== $data['name']) {
                $data['slug'] = $this->createUniqueSlug($data['name'], $id);
            }
        }
        return $this->repo->update($id, $data);
    }

    private function createUniqueSlug(string $name, $excludeId = null): string {
        $slug = Str::slug($name); $originalSlug = $slug; $count = 1;
        $existing = $this->repo->findBySlug($slug);
        while ($existing && $existing->id != $excludeId) {
            $slug = $originalSlug . '-' . $count++;
            $existing = $this->repo->findBySlug($slug);
        }
        return $slug;
    }
}

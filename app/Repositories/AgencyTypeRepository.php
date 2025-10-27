<?php
namespace App\Repositories;

use App\Contracts\Repositories\AgencyTypeRepositoryInterface;
use App\Models\AgencyType;

class AgencyTypeRepository implements AgencyTypeRepositoryInterface
{
    protected $model;
    public function __construct(AgencyType $model) { $this->model = $model; }
    public function getAll() { return $this->model->where('status', '1')->orderBy('name')->get(); }
}

<?php
namespace App\Services;

use App\Contracts\Repositories\AgencyTypeRepositoryInterface;

class AgencyTypeService
{
    protected $repo;
    public function __construct(AgencyTypeRepositoryInterface $repo) { $this->repo = $repo; }
    public function getAll() { return $this->repo->getAll(); }
}

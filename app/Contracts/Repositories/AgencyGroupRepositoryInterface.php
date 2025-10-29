<?php

namespace App\Contracts\Repositories;

interface AgencyGroupRepositoryInterface 
{
    public function allActive();
    public function findById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
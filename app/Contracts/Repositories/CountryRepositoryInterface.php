<?php

namespace App\Contracts\Repositories;

interface CountryRepositoryInterface 
{
    /**
     * Saari countries layein (dropdowns ke liye)
     */
    public function getAll();

    /**
     * Paginated list layein (table view ke liye)
     */
    public function getPaginated(int $perPage = 10);

    /**
     * ID se ek country layein
     */
    public function findById(int $id);

    /**
     * Nayi country banayein
     */
    public function create(array $data);

    /**
     * Country update karein
     */
    public function update(int $id, array $data);

    /**
     * Country delete karein (Yeh HARD delete hoga)
     */
    public function delete(int $id);
}


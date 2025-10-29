<?php

namespace App\Contracts\Repositories;

interface StateRepositoryInterface 
{
    /**
     * Saare states layein (dropdowns ke liye)
     */
    public function getAll();

    /**
     * Ek specific country ke saare states layein
     */
    public function getByCountry(int $countryId);

    /**
     * Paginated list layein (table view ke liye)
     */
    public function getPaginated(int $perPage = 10);

    /**
     * ID se ek state layein
     */
    public function findById(int $id);

    /**
     * Naya state banayein
     */
    public function create(array $data);

    /**
     * State update karein
     */
    public function update(int $id, array $data);

    /**
     * State delete karein (Yeh HARD delete hoga)
     */
    public function delete(int $id);
}

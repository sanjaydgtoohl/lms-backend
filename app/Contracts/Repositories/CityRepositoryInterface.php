<?php

namespace App\Contracts\Repositories;

interface CityRepositoryInterface 
{
    /**
     * Saare cities layein (dropdowns ke liye)
     */
    public function getAll();

    /**
     * Paginated list layein (table view ke liye)
     */
    public function getPaginated(int $perPage = 10);

    /**
     * Ek specific state ke saare cities layein
     */
    public function getByState(int $stateId);

    /**
     * Ek specific country ke saare cities layein
     */
    public function getByCountry(int $countryId);

    /**
     * ID se ek city layein
     */
    public function findById(int $id);

    /**
     * Nayi city banayein
     */
    public function create(array $data);

    /**
     * City update karein
     */
    public function update(int $id, array $data);

    /**
     * City delete karein (Yeh HARD delete hoga)
     */
    public function delete(int $id);
}

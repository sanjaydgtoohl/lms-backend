<?php

namespace App\Contracts\Repositories;

use App\Models\Industry;
interface IndustryRepositoryInterface 
{
    
    public function getAllIndustries();

    /**
     * Ek industry ko uske primary key (id) se fetch karega
     * @param int $id
     */
    public function getIndustryById($id);

    /**
     * Ek nayi industry create karega
     * @param array $data (Jaise ['industry_name' => 'New Name'])
     */
    public function createIndustry(array $data);

    /**
     * Ek maujooda industry ko update karega
     * @param int $id
     * @param array $data
     */
    public function updateIndustry($id, array $data);

    /**
     * Ek industry ko delete karega (ab yeh soft delete hoga)
     * @param int $id
     */
    public function deleteIndustry($id);
}
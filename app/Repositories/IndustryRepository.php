<?php

namespace App\Repositories;

use App\Contracts\Repositories\IndustryRepositoryInterface; // Interface ko import karein
use App\Models\Industry;                         // Model ko import karein

/**
 * Yeh class hamare Interface ko implement karti hai.
 * Yahan database se data laane, save karne, update karne
 * aur delete karne ka logic hai.
 */
class IndustryRepository implements IndustryRepositoryInterface 
{
    /**
     * @var Industry
     */
    protected $model;

    /**
     * Constructor Injection
     * Hum Laravel se bol rahe hain ki jab bhi IndustryRepository
     * banaye, toh usmein Industry Model ka object daal de.
     */
    public function __construct(Industry $industry)
    {
        $this->model = $industry;
    }

    /**
     * Saari industries laao (paginate karke)
     */
    public function getAllIndustries() 
    {
        // Model mein SoftDeletes trait add karne se,
        // yeh automatically sirf wahi records laayega jo delete nahi hue hain.
        return $this->model->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Ek industry ko ID se dhoondo
     */
    public function getIndustryById($id) 
    {
        // findOrFail ka matlab: dhoondo, agar nahi mila toh
        // automatically 404 Not Found error return karo.
        return $this->model->findOrFail($id);
    }

    /**
     * Nayi industry banao
     */
    public function createIndustry(array $data) 
    {
        return $this->model->create($data);
    }

    /**
     * Industry ko update karo
     */
    public function updateIndustry($id, array $data) 
    {
        // Pehle record ko dhoondo
        $industry = $this->model->findOrFail($id);
        
        // Phir usse update karo
        $industry->update($data);
        return $industry;
    }

    public function deleteIndustry($id) 
    {
        $industry = $this->model->findOrFail($id);
        return $industry->delete();
    }
}
<?php

namespace App\Repositories;

use App\Contracts\Repositories\IndustryRepositoryInterface; // Interface ko import karein
use App\Models\Industry;                              // Industry model ko import karein

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
     */
    public function __construct(Industry $industry)
    {
        $this->model = $industry;
    }

    /**
     * Saari industries laao (paginate karke)
     */
    public function getAllIndustries(int $perPage = 10) // <--- $perPage accept kiya
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($perPage); 
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
        // Only allow name, slug, status
        $filtered = [
            'name' => $data['name'] ?? null,
            'slug' => $data['slug'] ?? null,
            'status' => $data['status'] ?? 1,
        ];
        return $this->model->create($filtered);
    }

    /**
     * Industry ko update karo
     */
    public function updateIndustry($id, array $data)
    {
        $industry = $this->model->findOrFail($id);
        $filtered = [
            'name' => $data['name'] ?? $industry->name,
            'slug' => $data['slug'] ?? $industry->slug,
            'status' => $data['status'] ?? $industry->status,
        ];
        $industry->update($filtered);
        return $industry;
    }

    public function deleteIndustry($id)
    {
        $industry = $this->model->findOrFail($id);
        return $industry->delete();
    }
}

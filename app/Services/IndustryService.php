<?php

namespace App\Services;

use App\Contracts\Repositories\IndustryRepositoryInterface; // Interface ko import karein
use Illuminate\Support\Facades\Log; // Logging ke liye (optional)

/**
 * Yeh hamari Business Logic layer hai.
 * Controller isse call karega.
 * Service, Repository ko call karegi.
 */
class IndustryService
{
    protected $industryRepository;

    /**
     * Yahan hum Interface ko inject kar rahe hain.
     * Provider (Step 5) ki wajah se Laravel ko pata hai
     * ki iski jagah IndustryRepository deni hai.
     */
    public function __construct(IndustryRepositoryInterface $industryRepository)
    {
        $this->industryRepository = $industryRepository;
    }

    /**
     * Saari industries laane ki service
     */
    public function getAllIndustries()
    {
        // Abhi ke liye simple hai
        return $this->industryRepository->getAllIndustries();
    }

    /**
     * Nayi industry banane ki service
     */
    public function createNewIndustry(array $data)
    {
        /**
         * YAHAN BUSINESS LOGIC AATA HAI
         * Example: Agar aapko 'industry_name' ko save karne se pehle
         * hamesha UPPERCASE mein convert karna hai, toh aap woh yahan karenge.
         *
         * $data['industry_name'] = strtoupper($data['industry_name']);
         */
        
        // Logic ke baad, data ko repository ke paas bhej do save hone ke liye
        return $this->industryRepository->createIndustry($data);
    }

    /**
     * Ek industry laane ki service
     */
    public function getIndustry($id)
    {
        return $this->industryRepository->getIndustryById($id);
    }

    /**
     * Industry update karne ki service
     */
    public function updateIndustry($id, array $data)
    {
        // Yahan bhi update se pehle business logic aa sakta hai
        // $data['industry_name'] = strtoupper($data['industry_name']);

        return $this->industryRepository->updateIndustry($id, $data);
    }

    /**
     * Industry delete karne ki service
     */
    public function deleteIndustry($id)
    {
        // Yahan delete se pehle logic aa sakta hai
        // Jaise: check karo ki is industry mein koi user toh nahi hai?
        // Agar hai, toh delete mat karo aur error return karo.
        
        return $this->industryRepository->deleteIndustry($id);
    }
}
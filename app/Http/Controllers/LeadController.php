<?php

namespace App\Http\Controllers;

// ----- Zaroori Imports -----
use App\Models\Lead;
use App\Models\LeadContact;
use App\Services\ResponseService; // <-- Aapka Response Service
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // <-- YEH IMPORT ZAROORI HAI
use Illuminate\Support\Facades\Auth; // <-- 'user_id' check ke liye zaroori
use Throwable; // <-- Yeh Exception se behtar hai, sab kuch catch karta hai

class LeadController extends Controller
{
    /**
     * Response Service ko store karne ke liye property.
     */
    protected $responseService;

    /**
     * Controller ko initialize karte waqt ResponseService ko inject karein.
     */
    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * CREATE: Naya lead aur uske contacts store karein.
     */
    public function store(Request $request)
    {
        // --- Step 1: Validation Rules (Lumen ka tareeka) ---
        $rules = [
            'user_id' => 'nullable|exists:users,id',
            'brand_id'         => 'nullable|exists:brands,id',
            'agency_id'        => 'nullable|exists:agencies,id',
            'comment'          => 'nullable|string',
        
            'contactPersons'   => 'required|array|min:1',
            'contactPersons.*.full_name' => 'required|string|max:255',
            'contactPersons.*.email'     => 'nullable|email|max:255',
            'contactPersons.*.mobile_number' => 'required|string|max:20',
            // --- Yahan baaki contact fields ke rules add karein ---
            'contactPersons.*.profile_url' => 'nullable|string|max:255',
            'contactPersons.*.mobile_number_optional' => 'nullable|string|max:20',
            'contactPersons.*.type' => 'nullable|string|max:50',
            'contactPersons.*.designation_id' => 'nullable|exists:designations,id',
            'contactPersons.*.department_id' => 'nullable|exists:departments,id',
            'contactPersons.*.lead_sub_source_id' => 'nullable|exists:lead_sub_sources,id',
            'contactPersons.*.country_id' => 'nullable|exists:countries,id',
            'contactPersons.*.state_id' => 'nullable|exists:states,id',
            'contactPersons.*.city_id' => 'nullable|exists:cities,id',
            'contactPersons.*.zone_id' => 'nullable|exists:zones,id',
            'contactPersons.*.postal_code' => 'nullable|string|max:20',
        ];

        // --- Step 2: Validator Banana ---
        $validator = Validator::make($request->all(), $rules);

        // --- Step 3: Check Karna ki Validation Fail hua ya nahi ---
        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray());
        }

        // --- Step 4: Validated Data Haasil Karna ---
        $validatedData = $validator->validated();

        // --- Step 5: Try-Catch Block (Database Logic) ---
        try {
            $newLead = null; 

            // --- Step 6: Database Transaction ---
            DB::transaction(function () use ($validatedData, &$newLead) {
                
                $newLead = Lead::create([
                    'brand_id'  => $validatedData['brand_id'] ?? null,
                    'agency_id' => $validatedData['agency_id'] ?? null,
                   // 'user_id'   => $validatedData['assigned_user_id'], // Map form key to db column
                    'comment'   => $validatedData['comment'] ?? null,
                    'status'    => '1', 
                ]);

                foreach ($validatedData['contactPersons'] as $contact) {
                    $contact['lead_id'] = $newLead->id;
                    $contact['status'] = '1'; 
                    LeadContact::create($contact);
                }
            }); 

            // --- Step 7: Success Response ---
            if (!$newLead) {
                 throw new \RuntimeException('Failed to create lead record after transaction');
            }
            $newLead->load('contacts');

            return $this->responseService->created(
                $newLead,
                'Lead created successfully!'
            );

        } catch (Throwable $exception) {
            // --- Step 8: Exception Handling ---
            return $this->responseService->handleException($exception);
        }
    }

    /**
     * READ (List): Authenticated user ke saare leads list karein.
     */
    public function index()
    {
        try {
            $userId = Auth::id(); // Logged-in user ki ID
            
            $leads = Lead::where('user_id', $userId)
                         ->with('contacts') // Contacts ko saath mein load karein
                         ->latest() // Naye leads pehle
                         ->paginate(15); // Pagination

            return $this->responseService->paginated(
                $leads,
                'Leads retrieved successfully!'
            );

        } catch (Throwable $exception) {
            return $this->responseService->handleException($exception);
        }
    }

    /**
     * READ (Single): Ek specific lead dikhayein (user ownership check ke saath).
     */
    public function show($id)
    {
        try {
            $userId = Auth::id();

            // findOrFail - Agar lead nahi mila YA user_id match nahi hui, toh 404 error dega
            $lead = Lead::where('user_id', $userId)
                        ->with('contacts')
                        ->findOrFail($id);

            return $this->responseService->success(
                $lead,
                'Lead retrieved successfully!'
            );

        } catch (Throwable $exception) {
            // ModelNotFoundException (404) ko handleException() catch kar lega
            return $this->responseService->handleException($exception);
        }
    }

    /**
     * UPDATE: Ek existing lead aur uske contacts ko update karein.
     */
    public function update(Request $request, $id)
    {
        // --- Step 1: Validation Rules (Update ke liye) ---
        // 'sometimes' ka matlab hai ki field agar request mein hai, toh hi validate karo
        $rules = [
            'assigned_user_id' => 'sometimes|required|exists:users,id',
            'brand_id'         => 'sometimes|nullable|exists:brands,id',
            'agency_id'        => 'sometimes|nullable|exists:agencies,id',
            'comment'          => 'sometimes|nullable|string',
            
            // Agar contactPersons bhej rahe hain, toh unhe validate karo
            'contactPersons'   => 'sometimes|array|min:1', 
            'contactPersons.*.full_name' => 'required_with:contactPersons|string|max:255',
            'contactPersons.*.email'     => 'nullable|email|max:255',
            'contactPersons.*.mobile_number' => 'required_with:contactPersons|string|max:20',
            // ... baaki contact fields ke rules
        ];

        // --- Step 2: Validator Banana ---
        $validator = Validator::make($request->all(), $rules);

        // --- Step 3: Check Validation Fail ---
        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray());
        }

        // --- Step 4: Validated Data Haasil Karna ---
        $validatedData = $validator->validated();

        // --- Step 5: Try-Catch Block (Database Logic) ---
        try {
            $lead = null;

            DB::transaction(function () use ($validatedData, $id, &$lead) {
                
                $userId = Auth::id();
                
                // --- Step 5a: Lead Dhoondein (Ownership check ke saath) ---
                $lead = Lead::where('user_id', $userId)->findOrFail($id);

                // --- Step 5b: Lead Data Update Karein ---
                // 'user_id' map karein agar 'assigned_user_id' aaya hai
                $leadDataToUpdate = $validatedData;
                if (isset($leadDataToUpdate['assigned_user_id'])) {
                    $leadDataToUpdate['user_id'] = $leadDataToUpdate['assigned_user_id'];
                    unset($leadDataToUpdate['assigned_user_id']);
                }
                unset($leadDataToUpdate['contactPersons']); // Contacts alag se handle honge

                $lead->update($leadDataToUpdate);

                // --- Step 5c: Contacts Update Karein (Agar request mein aaye hain) ---
                if (isset($validatedData['contactPersons'])) {
                    
                    // 1. Puraane contacts ko soft delete karein
                    LeadContact::where('lead_id', $lead->id)->delete();

                    // 2. Naye contacts create karein
                    foreach ($validatedData['contactPersons'] as $contact) {
                        $contact['lead_id'] = $lead->id;
                        $contact['status'] = '1'; 
                        LeadContact::create($contact);
                    }
                }
            });

            // --- Step 6: Success Response ---
            $lead->load('contacts'); // Naye contacts ke saath data load karein

            return $this->responseService->updated(
                $lead,
                'Lead updated successfully!'
            );

        } catch (Throwable $exception) {
            // ModelNotFoundException (404) ya DB error ko handle karega
            return $this->responseService->handleException($exception);
        }
    }

    /**
     * DELETE: Ek lead ko soft delete karein (user ownership check ke saath).
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                
                $userId = Auth::id();
                
                // --- Step 1: Lead Dhoondein (Ownership check ke saath) ---
                $lead = Lead::where('user_id', $userId)->findOrFail($id);

                // --- Step 2: Pehle jude hue contacts ko soft-delete karein ---
                // (Kyunki DB cascade 'onDelete' soft-deletes par trigger nahi hota)
                $lead->contacts()->delete(); // Model SoftDeletes trait ka istemaal karega

                // --- Step 3: Ab main lead ko soft-delete karein ---
                $lead->delete();
            });

            // --- Step 4: Success Response ---
            return $this->responseService->deleted(
                'Lead deleted successfully!'
            );

        } catch (Throwable $exception) {
            // ModelNotFoundException (404) ko handle karega
            return $this->responseService->handleException($exception);
        }
    }
}
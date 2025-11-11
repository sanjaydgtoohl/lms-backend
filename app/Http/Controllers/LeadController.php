<?php

namespace App\Http\Controllers;

// ----- Required Imports -----
use App\Models\Lead;
use App\Models\LeadContact;
use App\Services\ResponseService; // <-- Response Service for API responses
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // <-- Required for request validation
use Illuminate\Support\Facades\Auth; // <-- Required for user_id verification
use Throwable; // <-- Better than Exception, catches everything

class LeadController extends Controller
{
    /**
     * The ResponseService instance.
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Create a new LeadController instance.
     *
     * @param ResponseService $responseService
     */
    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * CREATE: Store a new lead and its associated contacts.
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
            // --- Additional contact fields validation rules ---
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

        // --- Step 2: Create Validator Instance ---
        $validator = Validator::make($request->all(), $rules);

        // --- Step 3: Check for Validation Failures ---
        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray());
        }

        // --- Step 4: Get Validated Data ---
        $validatedData = $validator->validated();

        // --- Step 5: Try-Catch Block (Database Logic) ---
        try {
            $newLead = null; 

            // --- Step 6: Database Transaction ---
            DB::transaction(function () use ($validatedData, &$newLead) {
                
                $newLead = Lead::create([
                    'brand_id'  => $validatedData['brand_id'] ?? null,
                    'agency_id' => $validatedData['agency_id'] ?? null,
                   // 'user_id'   => $validatedData['assigned_user_id'],
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
     * Display a listing of leads for the authenticated user.
     *
     * GET /leads
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $userId = Auth::id(); // Get current user's ID
            
            $leads = Lead::where('user_id', $userId)
                         ->with('contacts') // Eager load contacts
                         ->latest()         // Order by latest first
                         ->paginate(15);    // Paginate results

            return $this->responseService->paginated(
                $leads,
                'Leads retrieved successfully!'
            );

        } catch (Throwable $exception) {
            return $this->responseService->handleException($exception);
        }
    }

    /**
     * Display the specified lead.
     *
     * GET /leads/{id}
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $userId = Auth::id();

            // Find lead with ownership check (throws 404 if not found or not owned)
            $lead = Lead::where('user_id', $userId)
                        ->with('contacts')
                        ->findOrFail($id);

            return $this->responseService->success(
                $lead,
                'Lead retrieved successfully!'
            );

        } catch (Throwable $exception) {
            // ModelNotFoundException (404) will be caught by handleException()
            return $this->responseService->handleException($exception);
        }
    }

    /**
     * Update the specified lead and its contacts.
     *
     * PUT /leads/{id}
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // --- Step 1: Define validation rules ---
        // 'sometimes' means the field will only be validated if it exists in the request
        $rules = [
            'assigned_user_id' => 'sometimes|required|exists:users,id',
            'brand_id'         => 'sometimes|nullable|exists:brands,id',
            'agency_id'        => 'sometimes|nullable|exists:agencies,id',
            'comment'          => 'sometimes|nullable|string',
            
            // Validate contact persons if they are provided in the request
            'contactPersons'   => 'sometimes|array|min:1', 
            'contactPersons.*.full_name' => 'required_with:contactPersons|string|max:255',
            'contactPersons.*.email'     => 'nullable|email|max:255',
            'contactPersons.*.mobile_number' => 'required_with:contactPersons|string|max:20',
            // ... other contact fields validation rules
        ];

        // --- Step 2: Create validator instance ---
        $validator = Validator::make($request->all(), $rules);

        // --- Step 3: Check for validation failures ---
        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray());
        }

        // --- Step 4: Get validated data ---
        $validatedData = $validator->validated();

        // --- Step 5: Try-Catch Block (Database Logic) ---
        try {
            $lead = null;

            DB::transaction(function () use ($validatedData, $id, &$lead) {
                
                $userId = Auth::id();
                
                // --- Step 5a: Find lead with ownership check ---
                $lead = Lead::where('user_id', $userId)->findOrFail($id);

                // --- Step 5b: Update lead data ---
                // Map 'assigned_user_id' to 'user_id' if provided
                $leadDataToUpdate = $validatedData;
                if (isset($leadDataToUpdate['assigned_user_id'])) {
                    $leadDataToUpdate['user_id'] = $leadDataToUpdate['assigned_user_id'];
                    unset($leadDataToUpdate['assigned_user_id']);
                }
                unset($leadDataToUpdate['contactPersons']); // Contacts will be handled separately

                $lead->update($leadDataToUpdate);

                // --- Step 5c: Update contacts if provided in request ---
                if (isset($validatedData['contactPersons'])) {
                    
                    // 1. Soft delete existing contacts
                    LeadContact::where('lead_id', $lead->id)->delete();

                    // 2. Create new contacts
                    foreach ($validatedData['contactPersons'] as $contact) {
                        $contact['lead_id'] = $lead->id;
                        $contact['status'] = '1'; 
                        LeadContact::create($contact);
                    }
                }
            });

            // --- Step 6: Success Response ---
            $lead->load('contacts'); // Load data with new contacts

            return $this->responseService->updated(
                $lead,
                'Lead updated successfully!'
            );

        } catch (Throwable $exception) {
            // Will handle ModelNotFoundException (404) or DB errors
            return $this->responseService->handleException($exception);
        }
    }

    /**
     * DELETE: Soft delete a lead (with user ownership verification).
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                
                $userId = Auth::id();
                
                // --- Step 1: Find Lead (with ownership check) ---
                $lead = Lead::where('user_id', $userId)->findOrFail($id);

                // --- Step 2: Soft-delete associated contacts first ---
                // (Because DB cascade 'onDelete' doesn't trigger on soft-deletes)
                $lead->contacts()->delete(); // Will use Model's SoftDeletes trait

                // --- Step 3: Soft-delete the main lead ---
                $lead->delete();
            });

            // --- Step 4: Success Response ---
            return $this->responseService->deleted(
                'Lead deleted successfully!'
            );

        } catch (Throwable $exception) {
            // Will handle ModelNotFoundException (404)
            return $this->responseService->handleException($exception);
        }
    }
}
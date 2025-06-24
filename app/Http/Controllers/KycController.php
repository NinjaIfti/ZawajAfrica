<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MonnifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class KycController extends Controller
{
    protected $monnifyService;

    public function __construct(MonnifyService $monnifyService)
    {
        $this->monnifyService = $monnifyService;
        $this->middleware('auth');
    }

    /**
     * Show KYC verification page
     */
    public function index(): Response
    {
        $user = Auth::user();
        
        return Inertia::render('Kyc/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'kyc_status' => $user->kyc_status,
                'kyc_bvn_verified' => $user->kyc_bvn_verified,
                'kyc_nin_verified' => $user->kyc_nin_verified,
                'masked_bvn' => $user->masked_bvn,
                'masked_nin' => $user->masked_nin,
                'kyc_completion_percentage' => $user->getKycCompletionPercentage(),
                'is_eligible_for_higher_limits' => $user->isEligibleForHigherLimits(),
                'kyc_verified_at' => $user->kyc_verified_at,
                'kyc_failure_reason' => $user->kyc_failure_reason,
            ]
        ]);
    }

    /**
     * Submit BVN for verification
     */
    public function submitBvn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bvn' => 'required|string|size:11|regex:/^\d{11}$/'
        ], [
            'bvn.required' => 'BVN is required',
            'bvn.size' => 'BVN must be exactly 11 digits',
            'bvn.regex' => 'BVN must contain only numbers'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $bvn = $request->bvn;

        // Validate BVN format using service
        if (!$this->monnifyService->validateBvn($bvn)) {
            return back()->withErrors(['bvn' => 'Invalid BVN format'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Step 1: BVN format validation (production-ready)
            Log::info('KYC: Processing BVN verification', [
                'user_id' => $user->id,
                'bvn_masked' => substr($bvn, 0, 3) . '****' . substr($bvn, -3)
            ]);

            // Step 2: Check if user already has a reserved account, if not create one
            if (empty($user->monnify_account_reference)) {
                $accountResult = $this->createReservedAccount($user);
                if (!$accountResult['status']) {
                    DB::rollBack();
                    return back()->withErrors(['bvn' => $accountResult['message']])->withInput();
                }
            }

            // Step 3: Update KYC with BVN
            $kycResult = $this->monnifyService->updateKycInfo($user->monnify_account_reference, [
                'bvn' => $bvn
            ]);

            if ($kycResult['status']) {
                // Update user record
                $user->update([
                    'bvn' => $bvn,
                    'kyc_bvn_verified' => true,
                    'kyc_status' => $user->kyc_nin_verified ? 'verified' : 'pending',
                    'kyc_verified_at' => $user->kyc_nin_verified ? now() : null,
                    'kyc_failure_reason' => null
                ]);

                DB::commit();

                Log::info('BVN verification successful', [
                    'user_id' => $user->id,
                    'account_reference' => $user->monnify_account_reference
                ]);

                return back()->with('success', 'BVN verified successfully! You now have access to higher transaction limits.');
            } else {
                DB::rollBack();
                
                Log::error('BVN verification failed', [
                    'user_id' => $user->id,
                    'error' => $kycResult['message']
                ]);

                return back()->withErrors(['bvn' => $kycResult['message']])->withInput();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('BVN submission exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['bvn' => 'An error occurred while verifying your BVN. Please try again.'])->withInput();
        }
    }

    /**
     * Submit NIN for verification
     */
    public function submitNin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nin' => 'required|string|size:11|regex:/^\d{11}$/'
        ], [
            'nin.required' => 'NIN is required',
            'nin.size' => 'NIN must be exactly 11 digits',
            'nin.regex' => 'NIN must contain only numbers'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $nin = $request->nin;

        // Validate NIN format using service
        if (!$this->monnifyService->validateNin($nin)) {
            return back()->withErrors(['nin' => 'Invalid NIN format'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Step 1: NIN format validation (production-ready)
            Log::info('KYC: Processing NIN verification', [
                'user_id' => $user->id,
                'nin_masked' => substr($nin, 0, 3) . '****' . substr($nin, -3)
            ]);

            // Step 2: Check if user already has a reserved account, if not create one
            if (empty($user->monnify_account_reference)) {
                $accountResult = $this->createReservedAccount($user);
                if (!$accountResult['status']) {
                    DB::rollBack();
                    return back()->withErrors(['nin' => $accountResult['message']])->withInput();
                }
            }

            // Step 3: Update KYC with NIN
            $kycResult = $this->monnifyService->updateKycInfo($user->monnify_account_reference, [
                'nin' => $nin
            ]);

            if ($kycResult['status']) {
                // Update user record
                $user->update([
                    'nin' => $nin,
                    'kyc_nin_verified' => true,
                    'kyc_status' => $user->kyc_bvn_verified ? 'verified' : 'pending',
                    'kyc_verified_at' => $user->kyc_bvn_verified ? now() : null,
                    'kyc_failure_reason' => null
                ]);

                DB::commit();

                Log::info('NIN verification successful', [
                    'user_id' => $user->id,
                    'account_reference' => $user->monnify_account_reference
                ]);

                return back()->with('success', 'NIN verified successfully! You now have access to higher transaction limits.');
            } else {
                DB::rollBack();
                
                Log::error('NIN verification failed', [
                    'user_id' => $user->id,
                    'error' => $kycResult['message']
                ]);

                return back()->withErrors(['nin' => $kycResult['message']])->withInput();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('NIN submission exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['nin' => 'An error occurred while verifying your NIN. Please try again.'])->withInput();
        }
    }

    /**
     * Submit both BVN and NIN for verification
     */
    public function submitBoth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bvn' => 'required|string|size:11|regex:/^\d{11}$/',
            'nin' => 'required|string|size:11|regex:/^\d{11}$/'
        ], [
            'bvn.required' => 'BVN is required',
            'bvn.size' => 'BVN must be exactly 11 digits',
            'bvn.regex' => 'BVN must contain only numbers',
            'nin.required' => 'NIN is required',
            'nin.size' => 'NIN must be exactly 11 digits',
            'nin.regex' => 'NIN must contain only numbers'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $bvn = $request->bvn;
        $nin = $request->nin;

        // Validate formats using service
        if (!$this->monnifyService->validateBvn($bvn)) {
            return back()->withErrors(['bvn' => 'Invalid BVN format'])->withInput();
        }

        if (!$this->monnifyService->validateNin($nin)) {
            return back()->withErrors(['nin' => 'Invalid NIN format'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Step 1: BVN/NIN format validation (production-ready)
            Log::info('KYC: Processing full KYC verification', [
                'user_id' => $user->id,
                'bvn_masked' => substr($bvn, 0, 3) . '****' . substr($bvn, -3),
                'nin_masked' => substr($nin, 0, 3) . '****' . substr($nin, -3)
            ]);

            // Step 2: Check if user already has a reserved account, if not create one
            if (empty($user->monnify_account_reference)) {
                $accountResult = $this->createReservedAccount($user);
                if (!$accountResult['status']) {
                    DB::rollBack();
                    return back()->withErrors(['error' => $accountResult['message']])->withInput();
                }
            }

            // Step 3: Update KYC with both BVN and NIN
            $kycResult = $this->monnifyService->updateKycInfo($user->monnify_account_reference, [
                'bvn' => $bvn,
                'nin' => $nin
            ]);

            if ($kycResult['status']) {
                // Update user record
                $user->update([
                    'bvn' => $bvn,
                    'nin' => $nin,
                    'kyc_bvn_verified' => true,
                    'kyc_nin_verified' => true,
                    'kyc_status' => 'verified',
                    'kyc_verified_at' => now(),
                    'kyc_failure_reason' => null
                ]);

                DB::commit();

                Log::info('Full KYC verification successful', [
                    'user_id' => $user->id,
                    'account_reference' => $user->monnify_account_reference
                ]);

                return back()->with('success', 'BVN and NIN verified successfully! You now have access to higher transaction limits and premium features.');
            } else {
                DB::rollBack();
                
                Log::error('Full KYC verification failed', [
                    'user_id' => $user->id,
                    'error' => $kycResult['message']
                ]);

                return back()->withErrors(['error' => $kycResult['message']])->withInput();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Full KYC submission exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'An error occurred while verifying your details. Please try again.'])->withInput();
        }
    }

    /**
     * Get KYC status for API calls
     */
    public function getStatus()
    {
        $user = Auth::user();
        
        return response()->json([
            'kyc_status' => $user->kyc_status,
            'kyc_bvn_verified' => $user->kyc_bvn_verified,
            'kyc_nin_verified' => $user->kyc_nin_verified,
            'kyc_completion_percentage' => $user->getKycCompletionPercentage(),
            'is_eligible_for_higher_limits' => $user->isEligibleForHigherLimits(),
            'kyc_verified_at' => $user->kyc_verified_at,
            'masked_bvn' => $user->masked_bvn,
            'masked_nin' => $user->masked_nin,
        ]);
    }

    /**
     * Create a reserved account for the user
     */
    private function createReservedAccount(User $user): array
    {
        $accountReference = $this->monnifyService->generateAccountReference($user->id);
        
        $customerData = [
            'accountReference' => $accountReference,
            'accountName' => $user->name,
            'customerEmail' => $user->email,
            'customerName' => $user->name,
        ];

        $result = $this->monnifyService->createReservedAccount($customerData);
        
        if ($result['status']) {
            $user->update([
                'monnify_account_reference' => $accountReference,
                'monnify_reserved_accounts' => $result['data']
            ]);

            Log::info('Reserved account created successfully', [
                'user_id' => $user->id,
                'account_reference' => $accountReference
            ]);
        }

        return $result;
    }
} 
<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class VerificationController extends Controller
{
    /**
     * Display the verification intro page.
     */
    public function intro(): Response
    {
        // Allow all users to access verification intro regardless of status
        // They can verify even if already verified or if previously rejected
        return Inertia::render('Verification/Intro');
    }

    /**
     * Display the document type selection page.
     */
    public function documentTypeSelection(): Response
    {
        // Allow all users to access document type selection
        return Inertia::render('Verification/DocumentTypeSelection');
    }

    /**
     * Display the document upload page.
     */
    public function documentUpload(Request $request): Response
    {
        // Allow all users to access document upload
        $documentType = $request->query('type', 'passport');
        
        return Inertia::render('Verification/DocumentUpload', [
            'documentType' => $documentType
        ]);
    }

    /**
     * Store verification documents.
     */
    public function store(Request $request): RedirectResponse
    {
        $validationRules = [
            'document_type' => 'required|string|in:national_id,passport,drivers_license,voters_register',
            'front_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ];
        
        // Make back_image required for all document types except voters_register
        if ($request->document_type !== 'voters_register') {
            $validationRules['back_image'] = 'required|image|mimes:jpeg,png,jpg|max:5120';
        } else {
            $validationRules['back_image'] = 'nullable|image|mimes:jpeg,png,jpg|max:5120';
        }
        
        $request->validate($validationRules);

        $user = auth()->user();
        
        // Delete any previous verification records (allowing resubmission)
        if ($user->verification) {
            // Delete old files if they exist
            if ($user->verification->front_image) {
                Storage::disk('public')->delete($user->verification->front_image);
            }
            if ($user->verification->back_image) {
                Storage::disk('public')->delete($user->verification->back_image);
            }
            $user->verification->delete();
        }

        // Store new files
        $frontImagePath = $request->file('front_image')->store('verification_documents', 'public');
        $backImagePath = null;
        
        if ($request->hasFile('back_image')) {
            $backImagePath = $request->file('back_image')->store('verification_documents', 'public');
        }

        // Create new verification record
        $verification = Verification::create([
            'user_id' => $user->id,
            'document_type' => $request->document_type,
            'front_image' => $frontImagePath,
            'back_image' => $backImagePath,
            'status' => 'pending',
        ]);

        // Update user record with verification type but keep them unverified until admin approval
        $user->update([
            'verification_type' => $request->document_type,
            'is_verified' => false, // Reset verification status, admin must approve
        ]);

        return redirect()->route('verification.complete');
    }

    /**
     * Display verification completion page.
     */
    public function complete(): Response
    {
        return Inertia::render('Verification/Complete');
    }

    /**
     * Display verification pending page.
     */
    public function pending(): Response
    {
        $user = auth()->user();
        
        // Allow users to see pending status but they can still navigate away
        // No forced redirects - verification is optional
        
        return Inertia::render('Verification/Pending');
    }
} 
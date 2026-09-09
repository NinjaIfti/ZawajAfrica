<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VerificationController extends Controller
{
    public function intro(): Response
    {
        return Inertia::render('Verification/Intro');
    }

    public function documentTypeSelection(): Response
    {
        return Inertia::render('Verification/DocumentTypeSelection');
    }

    public function documentUpload(Request $request): Response
    {
        return Inertia::render('Verification/DocumentUpload', [
            'documentType' => $request->query('type', 'passport'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'document_type' => ['required', Rule::in(['national_id', 'passport', 'drivers_license', 'voters_register'])],
            'front_image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120', 'dimensions:min_width=300,min_height=300,max_width=10000,max_height=10000'],
            'back_image' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120', 'dimensions:min_width=300,min_height=300,max_width=10000,max_height=10000'],
        ]);

        if ($validated['document_type'] !== 'voters_register' && !$request->hasFile('back_image')) {
            abort(422, 'A back image is required for this document type.');
        }

        $user = $request->user();
        $old = $user->verification;
        if ($old) {
            $this->deleteStoredFiles($old);
            $old->delete();
        }

        $directory = 'users/'.$user->getKey().'/'.bin2hex(random_bytes(16));
        $front = $request->file('front_image');
        $frontPath = $front->store($directory, 'kyc_private');
        $backPath = null;
        $back = $request->file('back_image');
        if ($back) {
            $backPath = $back->store($directory, 'kyc_private');
        }

        Verification::create([
            'user_id' => $user->getKey(),
            'document_type' => $validated['document_type'],
            'front_image' => $frontPath,
            'back_image' => $backPath,
            'storage_disk' => 'kyc_private',
            'front_mime_type' => $front->getMimeType(),
            'front_size' => $front->getSize(),
            'back_mime_type' => $back?->getMimeType(),
            'back_size' => $back?->getSize(),
            'status' => 'pending',
        ]);

        $user->forceFill([
            'verification_type' => $validated['document_type'],
            'is_verified' => false,
        ])->save();

        return redirect()->route('verification.complete');
    }

    public function document(Request $request, Verification $verification, string $side)
    {
        abort_unless(in_array($side, ['front', 'back'], true), 404);
        $this->authorize('view', $verification);

        $path = $side === 'front' ? $verification->front_image : $verification->back_image;
        abort_unless($path, 404);

        $disk = Storage::disk($verification->storage_disk ?: 'public');
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => $side === 'front' ? $verification->front_mime_type : $verification->back_mime_type,
            'Content-Disposition' => 'inline; filename="verification-'.$side.'.'.pathinfo($path, PATHINFO_EXTENSION).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function complete(): Response
    {
        return Inertia::render('Verification/Complete');
    }

    public function pending(): Response
    {
        return Inertia::render('Verification/Pending');
    }

    private function deleteStoredFiles(Verification $verification): void
    {
        $disk = Storage::disk($verification->storage_disk ?: 'public');
        foreach ([$verification->front_image, $verification->back_image] as $path) {
            if ($path) {
                $disk->delete($path);
            }
        }
    }
}

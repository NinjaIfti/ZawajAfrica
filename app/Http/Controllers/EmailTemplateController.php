<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailTemplateController extends Controller
{
    /**
     * Serve a specific campaign template
     */
    public function template($templateName)
    {
        $templatePath = 'email-templates.' . $templateName;
        
        // Check if template exists
        if (!view()->exists($templatePath)) {
            return response('Template not found', 404);
        }

        return response()
            ->view($templatePath)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Serve welcome email template
     */
    public function welcomeEmail()
    {
        return response()
            ->view('email-templates.welcome-email')
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Serve newsletter template
     */
    public function newsletter()
    {
        return response()
            ->view('email-templates.newsletter')
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
}

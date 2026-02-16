<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Serves Blade-based test pages for livestream verification.
 * No auth required; pages use API token input for API calls.
 */
class LivestreamTestController extends Controller
{
    /**
     * Admin test page: create stream, go live, end stream, show status.
     */
    public function adminPage(): View
    {
        return view('livestream-test.admin');
    }

    /**
     * User test page: list live streams, join, play video via Agora Web SDK.
     */
    public function userPage(): View
    {
        return view('livestream-test.user');
    }

    /**
     * Publisher test page: capture camera/mic and publish to Agora (host stream).
     */
    public function publisherPage(): View
    {
        return view('livestream-test.publisher');
    }
}

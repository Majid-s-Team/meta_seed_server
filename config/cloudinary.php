<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Either set CLOUDINARY_URL (format: cloudinary://api_key:api_secret@cloud_name)
    | or the three separate variables below. CLOUDINARY_URL takes precedence.
    |
    */

    'url' => env('CLOUDINARY_URL'),

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Upload folders (prefix in Cloudinary)
    |--------------------------------------------------------------------------
    */
    'folders' => [
        'events' => env('CLOUDINARY_FOLDER_EVENTS', 'metaseat/events'),
        'recordings' => env('CLOUDINARY_FOLDER_RECORDINGS', 'metaseat/recordings'),
        'recordings_thumbnails' => env('CLOUDINARY_FOLDER_RECORDINGS_THUMBNAILS', 'metaseat/recordings/thumbnails'),
    ],

];

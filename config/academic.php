<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Course Prerequisite Enforcement
    |--------------------------------------------------------------------------
    |
    | When enabled (true), students cannot register for or add a course unit
    | unless they have completed and passed all its prerequisite course units.
    | When disabled (false, default for Ugandan universities), prerequisites are
    | preserved in the background but do not act as hard blockers during course
    | enrollment and add/drop workflows.
    |
    */
    'enforce_prerequisites' => env('ENFORCE_COURSE_PREREQUISITES', false),

    /*
    |--------------------------------------------------------------------------
    | Course Registration Advisor Approval Policy
    |--------------------------------------------------------------------------
    |
    | When enabled (true), submitted student course registrations and add/drop
    | adjustments enter a pending queue awaiting manual approval by an Academic
    | Advisor or Head of Department.
    | When disabled (false, default for Ugandan universities), submitted
    | registrations are automatically confirmed/approved immediately upon submission,
    | and the manual advisor approval workflow is hidden from navigation.
    |
    */
    'require_registration_approval' => env('REQUIRE_REGISTRATION_APPROVAL', false),

];

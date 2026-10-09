<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MST-SDR-BFC-001 workflow settings
    |--------------------------------------------------------------------------
    |
    | The department name that grants Management Systems and Technology
    | authority (Initial Review, Feasibility, Development, Testing,
    | Deployment, Monitoring) per the approved business workflow. Matches
    | the `department` string mirrored from the ticketing system's identity
    | API exactly.
    |
    */

    'mst_department' => env('WORKFLOW_MST_DEPARTMENT', 'Management Systems and Technology'),

];

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChangeRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\DevelopmentController;
use App\Http\Controllers\FeasibilityAssessmentController;
use App\Http\Controllers\InitialReviewController;
use App\Http\Controllers\ManagementApprovalController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\OrgDirectoryController;
use App\Http\Controllers\ProjectCollaborationController;
use App\Http\Controllers\RequirementsDesignController;
use App\Http\Controllers\SoftwareProjectController;
use App\Http\Controllers\TestingQaController;
use App\Http\Controllers\UserAcceptanceTestingController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WorkflowTrackerController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get(
        '/projects',
        [SoftwareProjectController::class, 'index']
    )->name('software-projects.index');

    Route::get(
        '/requests/new',
        [SoftwareProjectController::class, 'welcome']
    )->name('software-projects.welcome');

    Route::prefix('org')
        ->name('org.')
        ->group(function () {
            Route::get('/business-units', [OrgDirectoryController::class, 'businessUnits'])->name('business-units');
            Route::get('/companies', [OrgDirectoryController::class, 'companies'])->name('companies');
            Route::get('/departments', [OrgDirectoryController::class, 'departments'])->name('departments');
        });

    Route::get(
        '/projects/create',
        [SoftwareProjectController::class, 'create']
    )->name('software-projects.create');

    Route::post(
        '/projects',
        [SoftwareProjectController::class, 'store']
    )->name('software-projects.store');

    Route::get(
        '/projects/{softwareProject}',
        [SoftwareProjectController::class, 'show']
    )->name('software-projects.show');

    Route::post(
        '/projects/{softwareProject}/submit',
        [SoftwareProjectController::class, 'submit']
    )->name('software-projects.submit');

    Route::post(
        '/projects/{softwareProject}/comments',
        [ProjectCollaborationController::class, 'storeComment']
    )->name('software-projects.comments.store');

    Route::post(
        '/projects/{softwareProject}/documents',
        [ProjectCollaborationController::class, 'storeDocument']
    )->name('software-projects.documents.store');

    Route::post(
        '/projects/{softwareProject}/documents/{document}/review',
        [ProjectCollaborationController::class, 'reviewDocument']
    )->name('software-projects.documents.review');

    Route::prefix('reviews/initial-review')
        ->name('reviews.initial-review.')
        ->group(function () {
            Route::get('/', [InitialReviewController::class, 'index'])->name('index');
            Route::get('/{softwareProject}', [InitialReviewController::class, 'show'])->name('show');
            Route::post('/{softwareProject}/pass', [InitialReviewController::class, 'pass'])->name('pass');
            Route::post('/{softwareProject}/return', [InitialReviewController::class, 'returnForRevision'])->name('return');
        });

    Route::prefix('reviews/feasibility-assessment')
        ->name('reviews.feasibility-assessment.')
        ->group(function () {
            Route::get('/', [FeasibilityAssessmentController::class, 'index'])->name('index');
            Route::get('/{softwareProject}', [FeasibilityAssessmentController::class, 'show'])->name('show');
            Route::post('/{softwareProject}', [FeasibilityAssessmentController::class, 'store'])->name('store');
        });

    Route::prefix('approvals/management')
        ->name('approvals.management.')
        ->group(function () {
            Route::get('/', [ManagementApprovalController::class, 'index'])->name('index');
            Route::get('/{softwareProject}', [ManagementApprovalController::class, 'show'])->name('show');
            Route::post('/{softwareProject}', [ManagementApprovalController::class, 'store'])->name('store');
        });

    Route::prefix('workflow/requirements-design')
        ->name('workflow.requirements-design.')
        ->group(function () {
            Route::get('/', [RequirementsDesignController::class, 'index'])->name('index');
            Route::get('/{softwareProject}', [RequirementsDesignController::class, 'show'])->name('show');

            Route::post(
                '/{softwareProject}/requirements-documents',
                [RequirementsDesignController::class, 'storeRequirementsDocument']
            )->name('requirements-documents.store');

            Route::post(
                '/{softwareProject}/requirements-documents/{requirementsDocument}/decide',
                [RequirementsDesignController::class, 'decideRequirementsDocument']
            )->name('requirements-documents.decide');

            Route::post(
                '/{softwareProject}/system-designs',
                [RequirementsDesignController::class, 'storeSystemDesign']
            )->name('system-designs.store');

            Route::post(
                '/{softwareProject}/system-designs/{systemDesign}/decide',
                [RequirementsDesignController::class, 'decideSystemDesign']
            )->name('system-designs.decide');

            Route::post(
                '/{softwareProject}/security-assessments',
                [RequirementsDesignController::class, 'storeSecurityAssessment']
            )->name('security-assessments.store');

            Route::post(
                '/{softwareProject}/security-assessments/{securityAssessment}/decide',
                [RequirementsDesignController::class, 'decideSecurityAssessment']
            )->name('security-assessments.decide');
        });

    Route::prefix('workflow/development')
        ->name('workflow.development.')
        ->group(function () {
            Route::get('/', [DevelopmentController::class, 'index'])->name('index');
            Route::get('/{softwareProject}', [DevelopmentController::class, 'show'])->name('show');

            Route::post(
                '/{softwareProject}/repositories',
                [DevelopmentController::class, 'storeRepository']
            )->name('repositories.store');

            Route::post(
                '/{softwareProject}/tasks',
                [DevelopmentController::class, 'storeTask']
            )->name('tasks.store');

            Route::post(
                '/{softwareProject}/tasks/{developmentTask}/status',
                [DevelopmentController::class, 'updateTaskStatus']
            )->name('tasks.status');

            Route::post(
                '/{softwareProject}/advance',
                [DevelopmentController::class, 'advance']
            )->name('advance');
        });

    Route::prefix('workflow/testing-qa')
        ->name('workflow.testing-qa.')
        ->group(function () {
            Route::get('/', [TestingQaController::class, 'index'])->name('index');
            Route::get('/{softwareProject}', [TestingQaController::class, 'show'])->name('show');

            Route::post(
                '/{softwareProject}/test-plans',
                [TestingQaController::class, 'storeTestPlan']
            )->name('test-plans.store');

            Route::post(
                '/{softwareProject}/test-plans/{testPlan}/test-cases',
                [TestingQaController::class, 'storeTestCase']
            )->name('test-cases.store');

            Route::post(
                '/{softwareProject}/test-cases/{testCase}/execute',
                [TestingQaController::class, 'executeTestCase']
            )->name('test-cases.execute');

            Route::post(
                '/{softwareProject}/test-cases/{testCase}/defects',
                [TestingQaController::class, 'storeDefect']
            )->name('defects.store');

            Route::post(
                '/{softwareProject}/defects/{defect}/status',
                [TestingQaController::class, 'updateDefectStatus']
            )->name('defects.status');

            Route::post(
                '/{softwareProject}/advance',
                [TestingQaController::class, 'advance']
            )->name('advance');
        });

    Route::prefix('workflow/uat')
        ->name('workflow.uat.')
        ->group(function () {
            Route::get('/', [UserAcceptanceTestingController::class, 'index'])->name('index');
            Route::get('/{softwareProject}', [UserAcceptanceTestingController::class, 'show'])->name('show');

            Route::post(
                '/{softwareProject}/sessions',
                [UserAcceptanceTestingController::class, 'storeSession']
            )->name('sessions.store');

            Route::post(
                '/{softwareProject}/sessions/{uatSession}/signoff',
                [UserAcceptanceTestingController::class, 'storeSignoff']
            )->name('sessions.signoff');
        });

    Route::prefix('workflow/deployment')
        ->name('workflow.deployment.')
        ->group(function () {
            Route::get('/', [DeploymentController::class, 'index'])->name('index');
            Route::get('/{softwareProject}', [DeploymentController::class, 'show'])->name('show');

            Route::post(
                '/{softwareProject}/deployments',
                [DeploymentController::class, 'storeDeployment']
            )->name('deployments.store');

            Route::post(
                '/{softwareProject}/deployments/{deployment}/checklist-items',
                [DeploymentController::class, 'storeChecklistItem']
            )->name('checklist-items.store');

            Route::post(
                '/{softwareProject}/checklist-items/{deploymentChecklist}/check',
                [DeploymentController::class, 'checkChecklistItem']
            )->name('checklist-items.check');

            Route::post(
                '/{softwareProject}/deployments/{deployment}/release-notes',
                [DeploymentController::class, 'storeReleaseNote']
            )->name('release-notes.store');

            Route::post(
                '/{softwareProject}/deployments/{deployment}/outcome',
                [DeploymentController::class, 'recordOutcome']
            )->name('outcome');

            Route::post(
                '/{softwareProject}/security-assessments',
                [DeploymentController::class, 'storeSecurityAssessment']
            )->name('security-assessments.store');
        });

    Route::prefix('workflow/monitoring')
        ->name('workflow.monitoring.')
        ->group(function () {
            Route::get('/', [MonitoringController::class, 'index'])->name('index');
            Route::get('/{softwareProject}', [MonitoringController::class, 'show'])->name('show');

            Route::post(
                '/{softwareProject}/monitoring-records',
                [MonitoringController::class, 'storeMonitoringRecord']
            )->name('monitoring-records.store');

            Route::post(
                '/{softwareProject}/kpis',
                [MonitoringController::class, 'storeKpi']
            )->name('kpis.store');

            Route::post(
                '/{softwareProject}/maintenance-records',
                [MonitoringController::class, 'storeMaintenanceRequest']
            )->name('maintenance-records.store');

            Route::post(
                '/{softwareProject}/maintenance-records/{maintenanceRecord}/status',
                [MonitoringController::class, 'updateMaintenanceStatus']
            )->name('maintenance-records.status');
        });

    Route::prefix('workflow/tracker')
        ->name('workflow.tracker.')
        ->group(function () {
            Route::get('/', [WorkflowTrackerController::class, 'index'])->name('index');
        });

    Route::prefix('admin/users')
        ->name('admin.users.')
        ->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::post('/{user}/role', [UserManagementController::class, 'updateRole'])->name('role.update');
        });

    Route::prefix('workflow/change-requests')
        ->name('workflow.change-requests.')
        ->group(function () {
            Route::get('/{softwareProject}', [ChangeRequestController::class, 'show'])->name('show');
            Route::post('/{softwareProject}', [ChangeRequestController::class, 'store'])->name('store');

            Route::post(
                '/{softwareProject}/{changeRequest}/approvals/{changeApproval}/decide',
                [ChangeRequestController::class, 'decide']
            )->name('decide');

            Route::post(
                '/{softwareProject}/{changeRequest}/resubmit',
                [ChangeRequestController::class, 'resubmit']
            )->name('resubmit');

            Route::post(
                '/{softwareProject}/{changeRequest}/implement',
                [ChangeRequestController::class, 'markImplemented']
            )->name('implement');

            Route::post(
                '/{softwareProject}/{changeRequest}/close',
                [ChangeRequestController::class, 'markClosed']
            )->name('close');
        });
});

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSoftwareProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The requestor is always whoever is signed in — never trusted from
     * the form, or anyone could file a request under someone else's name.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['requestor_id' => $this->user()?->id]);
    }

    public function rules(): array
    {
        return [

            /*
             * Project Identification
             */
            'project_id' => [
                'required',
                'string',
                'max:30',
                'unique:software_projects,project_id',
            ],

            'project_name' => [
                'required',
                'string',
                'max:255',
            ],

            'requesting_department_id' => [
                'required',
                'uuid',
                'exists:departments,id',
            ],

            'requestor_id' => [
                'required',
                'uuid',
            ],

            // Sponsor and PM must be real users: only they can act on
            // Management Approval and Requirements & Design.
            'project_sponsor_id' => [
                'required',
                'uuid',
                'exists:users,id',
            ],

            'project_manager_id' => [
                'nullable',
                'uuid',
                'exists:users,id',
            ],

            /*
             * Project Overview
             */
            'project_summary' => [
                'required',
                'string',
            ],

            'business_problem' => [
                'required',
                'string',
            ],

            'proposed_solution' => [
                'required',
                'string',
            ],

            'priority' => [
                'nullable',
                'string',
                'max:30',
            ],

            'target_start_date' => [
                'nullable',
                'date',
            ],

            'target_end_date' => [
                'nullable',
                'date',
                'after_or_equal:target_start_date',
            ],

            /*
             * Objectives
             */
            'objectives' => [
                'required',
                'array',
                'min:1',
            ],

            'objectives.*' => [
                'required',
                'string',
            ],

            /*
             * Scope
             */
            'in_scope' => [
                'required',
                'array',
                'min:1',
            ],

            'in_scope.*' => [
                'required',
                'string',
            ],

            'out_of_scope' => [
                'nullable',
                'array',
            ],

            'out_of_scope.*' => [
                'required',
                'string',
            ],

            /*
             * Requirements
             */
            'requirements' => [
                'required',
                'array',
                'min:1',
            ],

            'requirements.*.requirement_type' => [
                'required',
                'string',
                'in:FUNCTIONAL,NON_FUNCTIONAL,INTEGRATION,COMPLIANCE',
            ],

            'requirements.*.requirement_code' => [
                'required',
                'string',
                'max:50',
            ],

            'requirements.*.title' => [
                'required',
                'string',
                'max:255',
            ],

            'requirements.*.description' => [
                'required',
                'string',
            ],

            'requirements.*.priority' => [
                'nullable',
                'string',
                'max:30',
            ],

            /*
             * Required Attachments
             */
            'required_attachments' => [
                'required',
                'array',
            ],

            'required_attachments.*.attachment_type' => [
                'required',
                'string',
                'in:BUSINESS_WORKFLOW,BUSINESS_FLOWCHART,DESIGN_DOCUMENT',
            ],

            'required_attachments.*.is_required' => [
                'boolean',
            ],

            'required_attachments.*.attachment_status' => [
                'required',
                'string',
                'in:ATTACHED,NOT_ATTACHED,NOT_APPLICABLE',
            ],

            'required_attachments.*.file_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'required_attachments.*.file_path' => [
                'nullable',
                'string',
            ],

            'required_attachments.*.document_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'required_attachments.*.document_version' => [
                'nullable',
                'string',
                'max:30',
            ],

            'required_attachments.*.document_date' => [
                'nullable',
                'date',
            ],

            'required_attachments.*.requester_signed_off' => [
                'boolean',
            ],
        ];
    }
}
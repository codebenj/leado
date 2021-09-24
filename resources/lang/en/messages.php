<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Messages Language Lines
    |--------------------------------------------------------------------------
    |
    | Messages language lines are used in API response for all API Endpoints.
    |
    */

    'permission_revoke_success_response' => 'Permission successfully revoked',
    'permission_create_response' => 'Permission successfully created.',
    'permission_update_response' => 'Permission successfully updated.',
    'permission_delete_response' => 'Permission successfully deleted.',
    'permission_not_found_response' => 'Permission not found.',
    'role_has_been_used_response' => 'This Role is currently assigned to Users, You cannot delete/update the Role.',

    'role_create_response' => 'Role successfully created.',
    'role_update_response' => 'Role successfully updated.',
    'role_delete_response' => 'Role successfully deleted.',
    'role_not_found_response' => 'Role not found.',

    'lead_success_response' => 'Lead was successfully created.',
    'lead_delete_response' => 'Lead successfully deleted.',
    'lead_update_response' => 'Lead successfully updated.',
    'lead_status_update_response' => 'Lead Escalation successfully updated.',
    'lead_not_found_response' => 'Lead not found.',
    'lead_escalcation_not_found' => 'Lead Escalation not found.',
    'lead_sale_save' => 'Successfully updated',
    'unauthorize_response' => 'Unauthorized Access',
    'general_error_response' => 'Something went wrong. We\'ll fix this as soon as possible.',
    'escalation_format_error_response' => 'Invalid escalation format: :format',
    'unauthorized' => 'Unauthorized',
    'org_success_response' => 'Organisation successfully created.',
    'org_delete_response' => 'Organisation successfully deleted.',
    'org_update_response' => 'Organisation successfully updated.',
    'org_not_found_response' => 'Organisation not found.',
    'enquirer_message_sent' => 'Enquirer message successfully sent.',

    'dashboard_query_successful' => 'Dashboard Results.',
    'report_medium_breakdown' => 'Advertising Medium Breakdown.',
    'report_organisation_status_breakdown' => 'Organisation Status Breakdown.',
    'leads_won_breakdown' => 'Leads Won Breakdown.',
    'organisation_postcode_import' => 'Organisation postcode import is successful.',
    'org_locator_import' => 'Org. Locator import is successful.',
    'org_locator_import_failed' => 'Oops! There seems to be an issue with the file you are trying to import.',
    'org_has_leads' => 'You cannot delete an Org that has leads assigned.',
    'dashboard_query_successful' => 'Dashboard Results.',
    'logs_org_locator' => 'Logs from Org. Locator Import.',
    'logs_postcode' => 'Logs from Postcode Import.',
    'logs_deleted' => 'Log successfully deleted.',

    'escalation_reassign_status_error_response' => 'Status should be `Discontinued - Discontinued` or `Abandoned - Abandoned` only.',
    'lead_reassign_success_response' => 'This lead was successfully Reassigned.',
    'organisations_the_same_id' => 'This Org ID is already in use.',

    'lead_job_create' => 'Comment successfully saved.',
    'lead_job_not_found' => 'Lead Job is not found.',
    'lead_job_found' => 'Lead Job is found.',
    'lead_job_updated' => 'Lead Job has been updated.',
    'lead_job_deleted' => 'Lead Job has been deleted.',
    'lead_job_total_sale' => 'Lead Job total sales.',

    'cec_ar_confirmation' => 'You\'ve accepted this Leaf Stopper lead. Please call Enquirer within :time.',
    'in_progress_extended' => 'We received a response from you regarding this lead. Work In Progress. Thank you.',
    'hours' => 'hours',
    'days' => 'days',
    'day' => 'day',
    'months' => 'months',

    'notification_found' => 'Notifications List',
    'notification_not_found' => 'Notification not found',

    'Organisation_import' => 'Organisation import was successfull.',
    'Organisation_import_failed' => 'Organisation import failed.',

    'lead_webform_created' => 'New lead created.',
    'lead_webform_failed' => 'Lead creation failed.',

    'organisation_required' => 'Org required. Please assign an Organisation to proceed.',

    'cec_awaiting_response_confirmation' => '<strong>Please contact the Enquirer within :time.</strong> <br> If you do not update the status within this time frame, you will not receive any new leads until this lead resolved or the lead may be reassigned.',

    'cec_awaiting_response_tried_confirmation' => 'Your response has been received. Please contact the Enquirer as soon as possible and update the lead status',

    'inprogress_awaiting_response_confirmation' => 'You\'ve accepted this Leaf Stopper lead. Please call Enquirer within :time.',

    'invalid_escalation_level' => '<p>The Level / Status you have chosen on \'Step 1\' will start an escalation. You cannot start an escalation without assigning this lead to an Org. Please review and update.<br><b>Tip:</b> Level / Status options for leads that are not assigned to an Org are:<br><br>UNASSIGNED - UNASSIGNED<br>UNASSIGNED - SPECIAL OPPORTUNITY<br>UNASSIGNED - LOST</p>',

    // time settings messages
    'time_setting_success_response' => 'Time Setting successfully saved.',
    'time_setting_delete_response' => 'Time Setting successfully deleted.',
    'time_setting_invalid_dates' => 'Invalid date time range for start date time and stop date time.',


    'org_code_duplicate' => 'This Org Code has already been assigned to an existing Org. Please provide a new Org Code.',
];

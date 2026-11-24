<?php

$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/forgot-password', 'AuthController@forgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/logout', 'AuthController@logout');

// Reports
$router->get('/reports', 'IncidentController@index');
$router->get('/reports/new', 'IncidentController@create');
$router->post('/reports/new', 'IncidentController@create');
$router->get('/reports/{code}', 'IncidentController@show');

// M3: Verification actions
$router->post('/reports/{code}/verify', 'IncidentController@verify');
$router->post('/reports/{code}/reassess', 'IncidentController@reassess');
$router->post('/reports/{code}/reject', 'IncidentController@reject');
$router->post('/reports/{code}/duplicate', 'IncidentController@markDuplicate');
$router->post('/reports/{code}/escalate', 'IncidentController@escalate');
$router->post('/reports/{code}/reopen', 'IncidentController@reopen');

// Verification queue
$router->get('/verification', 'VerificationController@index');

// Prioritization queue (verified/triaged incidents awaiting assignment)
$router->get('/prioritization', 'PrioritizationController@index');

// M4: Response Teams (admin only)
$router->get('/teams', 'TeamController@index');
$router->get('/teams/new', 'TeamController@create');
$router->post('/teams/new', 'TeamController@create');
$router->get('/teams/{id}/edit', 'TeamController@edit');
$router->post('/teams/{id}/edit', 'TeamController@edit');
$router->post('/teams/{id}/delete', 'TeamController@delete');

// M4: Assignment
$router->post('/reports/{code}/assign', 'AssignmentController@assign');

// M4: Responder portal
$router->get('/responder', 'ResponderController@index');
$router->post('/responder/acknowledge/{id}', 'ResponderController@acknowledge');
$router->post('/responder/en-route/{id}', 'ResponderController@enRoute');
$router->post('/responder/on-scene/{id}', 'ResponderController@onScene');
$router->post('/responder/in-progress/{id}', 'ResponderController@inProgress');
$router->post('/responder/request-support/{id}', 'ResponderController@requestSupport');
$router->post('/responder/resolve/{id}', 'ResponderController@resolve');

// M4: Monitoring board
$router->get('/monitoring', 'MonitoringController@index');
$router->get('/monitoring/board', 'MonitoringController@board');
$router->post('/monitoring/support/{id}/ack', 'MonitoringController@ackSupport');

// M5: Analytics
$router->get('/analytics', 'AnalyticsController@index');
$router->get('/analytics/export', 'AnalyticsController@export');

// M7: Incident Map
$router->get('/map', 'MapController@index');
$router->get('/api/v1/map/data', 'MapController@data');

// M7: Personnel
$router->get('/personnel', 'PersonnelController@index');
$router->get('/personnel/new', 'PersonnelController@create');
$router->post('/personnel/new', 'PersonnelController@create');
$router->get('/personnel/{id}/edit', 'PersonnelController@edit');
$router->post('/personnel/{id}/edit', 'PersonnelController@edit');
$router->post('/personnel/{id}/reset-password', 'PersonnelController@resetPassword');
$router->post('/personnel/{id}/toggle-status', 'PersonnelController@toggleStatus');

// M7: Residents
$router->get('/residents', 'ResidentManagementController@index');
$router->get('/residents/export', 'ResidentManagementController@export');
$router->get('/residents/masterlist/export', 'ResidentManagementController@exportMasterlist');
$router->get('/residents/masterlist/new', 'ResidentManagementController@newMasterlistResident');
$router->post('/residents/masterlist/new', 'ResidentManagementController@newMasterlistResident');
$router->get('/residents/masterlist/{id}/edit', 'ResidentManagementController@editMasterlistResident');
$router->post('/residents/masterlist/{id}/edit', 'ResidentManagementController@editMasterlistResident');
$router->post('/residents/masterlist/{id}/archive', 'ResidentManagementController@archiveMasterlistResident');
$router->post('/residents/verification/{id}/verify', 'ResidentManagementController@verifyResidency');
$router->post('/residents/verification/{id}/reject', 'ResidentManagementController@rejectResidency');
$router->get('/residents/{id}', 'ResidentManagementController@show');
$router->post('/residents/{id}/approve', 'ResidentManagementController@approve');
$router->post('/residents/{id}/reject', 'ResidentManagementController@reject');

// M7: Announcements
$router->get('/announcements', 'AnnouncementController@index');
$router->get('/announcements/new', 'AnnouncementController@create');
$router->post('/announcements/new', 'AnnouncementController@create');
$router->get('/announcements/{id}/edit', 'AnnouncementController@edit');
$router->post('/announcements/{id}/edit', 'AnnouncementController@edit');
$router->post('/announcements/{id}/delete', 'AnnouncementController@delete');
$router->post('/announcements/broadcast', 'AnnouncementController@broadcast');

// M7: Audit Logs
$router->get('/audit-logs', 'AuditLogController@index');
$router->get('/audit-logs/export', 'AuditLogController@export');

// My Profile
$router->get('/profile', 'ProfileController@index');
$router->post('/profile', 'ProfileController@update');

// M7: Settings
$router->get('/settings', 'SettingsController@index');
$router->post('/settings/profile', 'SettingsController@updateProfile');
$router->post('/settings/system', 'SettingsController@updateSystem');
$router->get('/settings/contacts/new', 'SettingsController@createContact');
$router->post('/settings/contacts/new', 'SettingsController@createContact');
$router->get('/settings/contacts/{id}/edit', 'SettingsController@editContact');
$router->post('/settings/contacts/{id}/edit', 'SettingsController@editContact');
$router->post('/settings/contacts/{id}/toggle', 'SettingsController@toggleContact');
$router->post('/settings/contacts/{id}/delete', 'SettingsController@deleteContact');

// Notifications
$router->get('/notifications', 'NotificationController@index');
$router->post('/notifications/read-all', 'NotificationController@markAllRead');

// API v1
$router->get('/api/v1/notifications/unread-count', 'Api\NotificationApiController@unreadCount');
$router->get('/api/v1/notifications', 'Api\NotificationApiController@index');
$router->post('/api/v1/notifications/{id}/read', 'Api\NotificationApiController@markRead');
$router->post('/api/v1/notifications/read-all', 'Api\NotificationApiController@markAllRead');
$router->get('/api/v1/incidents/questions', 'Api\IncidentApiController@getQuestions');
$router->post('/api/v1/incidents', 'Api\IncidentApiController@store');
$router->get('/api/v1/incidents/{code}', 'Api\IncidentApiController@show');

// Resident API
$router->get('/api/v1/resident/incidents', 'Api\ResidentApiController@incidents');
$router->get('/api/v1/resident/incidents/{code}', 'Api\ResidentApiController@incidentDetail');

// Responder API
$router->get('/api/v1/responder/missions', 'Api\ResponderApiController@missions');
$router->get('/api/v1/responder/missions/{id}', 'Api\ResponderApiController@missionDetail');

// Notifications (mobile - responder/resident)
$router->get('/app/notifications', 'AppController@notifications');
$router->post('/app/notifications/read-all', 'AppController@markNotificationsRead');

// M6 PWA Mobile routes
$router->get('/app', 'AppController@entry');
$router->get('/app/login', 'AppController@login');
$router->post('/app/login', 'AppController@login');
$router->get('/app/register', 'AppController@register');
$router->post('/app/register', 'AppController@register');
$router->get('/app/forgot-password', 'AppController@forgotPassword');
$router->post('/app/forgot-password', 'AppController@forgotPassword');
$router->get('/app/offline', 'AppController@offline');
$router->get('/app/logout', 'AppController@logout');

// Resident PWA
$router->get('/app/resident', 'AppController@residentHome');
$router->get('/app/resident/track', 'AppController@residentTrack');
$router->get('/app/resident/track/{code}', 'AppController@residentTrackDetail');
$router->get('/app/resident/report', 'AppController@residentReport');
$router->post('/app/resident/report', 'AppController@residentReport');
$router->get('/app/resident/guides', 'AppController@residentGuides');
    $router->get('/app/resident/contacts', 'AppController@residentContacts');
    $router->get('/app/resident/profile', 'AppController@residentProfile');
    $router->post('/app/resident/profile', 'AppController@residentProfile');

// Responder PWA
$router->get('/app/responder', 'AppController@responderHome');
$router->get('/app/responder/missions/{id}', 'AppController@responderMissionDetail');
$router->get('/app/responder/contacts', 'AppController@responderContacts');
$router->get('/app/responder/profile', 'AppController@responderProfile');
$router->post('/app/responder/profile', 'AppController@responderProfile');

// M4 API: Mission actions (responder mobile)
$router->post('/api/v1/missions/{id}/acknowledge', 'Api\MissionApiController@acknowledge');
$router->post('/api/v1/missions/{id}/updates', 'Api\MissionApiController@update');
$router->post('/api/v1/missions/{id}/support-request', 'Api\MissionApiController@supportRequest');
$router->post('/api/v1/missions/{id}/resolve', 'Api\MissionApiController@resolve');

// Live responder tracking
$router->post('/api/v1/responder/location', 'Api\LocationApiController@update');
$router->post('/api/v1/responder/location/stop', 'Api\LocationApiController@stop');
$router->get('/api/v1/incidents/{code}/responder-location', 'Api\LocationApiController@forIncident');
$router->get('/api/v1/monitoring/locations', 'Api\LocationApiController@all');

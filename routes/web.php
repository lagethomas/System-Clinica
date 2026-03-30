<?php
/** @var App\Core\Router $router */

// Middleware Definitions
$auth  = \App\Middleware\AuthMiddleware::class;
$admin = \App\Middleware\AdminMiddleware::class;

// ── Public Routes (no auth required) ────────────────────────────
$router->add('GET',  '/login',  ['controller' => 'LoginController', 'method' => 'index']);
$router->add('POST', '/login',  ['controller' => 'LoginController', 'method' => 'attempt']);
$router->add('GET',  '/logout', ['controller' => 'LoginController', 'method' => 'logout']);
// SaaS Registration Public Routes
$router->add('GET',  '/register', ['controller' => 'RegisterController', 'method' => 'index']);
$router->add('POST', '/register', ['controller' => 'RegisterController', 'method' => 'doRegister']);

// ── Authenticated Routes ─────────────────────────────────────────
$router->add('GET', '/',          ['controller' => 'DashboardController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('GET', '/dashboard', ['controller' => 'DashboardController', 'method' => 'index', 'middlewares' => [$auth]]);

$router->add('GET', '/profile', ['controller' => 'ProfileController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/profile/save', ['controller' => 'ProfileController', 'method' => 'save', 'middlewares' => [$auth]]);

// Admin Routes
$router->add('GET', '/admin/users', ['controller' => 'Admin\\UsersController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('GET', '/users', ['controller' => 'Admin\\UsersController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/admin/users/save', ['controller' => 'Admin\\UsersController', 'method' => 'save', 'middlewares' => [$auth]]);
$router->add('POST', '/api/admin/users/delete', ['controller' => 'Admin\\UsersController', 'method' => 'delete', 'middlewares' => [$auth]]);
$router->add('POST', '/api/admin/users/send_credentials', ['controller' => 'Admin\\UsersController', 'method' => 'sendCredentials', 'middlewares' => [$auth]]);

$router->add('GET', '/admin/logs', ['controller' => 'Admin\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/logs', ['controller' => 'Admin\\LogsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/admin/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/settings', ['controller' => 'Admin\\SettingsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

// Company Settings Route (For Proprietario)
$router->add('GET', '/app/company-settings', ['controller' => 'CompanySettingsController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/company-settings/save', ['controller' => 'CompanySettingsController', 'method' => 'save', 'middlewares' => [$auth]]);

// Subscription & Billing (For Proprietario)
$router->add('GET', '/app/subscriptions', ['controller' => 'SubscriptionController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/company-subscription-action', ['controller' => 'SubscriptionController', 'method' => 'action', 'middlewares' => [$auth]]);

// SaaS Routes
$router->add('GET', '/admin/plans', ['controller' => 'Admin\\PlansController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/plans/save', ['controller' => 'Admin\\PlansController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/plans/delete', ['controller' => 'Admin\\PlansController', 'method' => 'delete', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/companies', ['controller' => 'Admin\\CompaniesController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/companies/save', ['controller' => 'Admin\\CompaniesController', 'method' => 'save', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/companies/delete', ['controller' => 'Admin\\CompaniesController', 'method' => 'delete', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/companies/details', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/generate', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'generateInvoice', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/delete', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'deleteInvoice', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/pay', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'payInvoice', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/invoices/update-date', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'updateDate', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/companies/update-expiration', ['controller' => 'Admin\\CompanyDetailsController', 'method' => 'updateExpiration', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/admin/financeiro', ['controller' => 'Admin\\FinancialController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('GET', '/admin/subscriptions', ['controller' => 'Admin\\SubscriptionsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/checkout/{id}', ['controller' => 'CheckoutController', 'method' => 'index']);
$router->add('GET', '/checkout/receipt/{id}', ['controller' => 'CheckoutController', 'method' => 'receipt']);
$router->add('GET', '/payment/callback', ['controller' => 'CheckoutController', 'method' => 'callback']);
$router->add('POST', '/api/webhook/mercadopago', ['controller' => 'CheckoutController', 'method' => 'webhook']);

$router->add('GET', '/api/notifications/read/{id}', ['controller' => 'NotificationController', 'method' => 'read', 'middlewares' => [$auth]]);
$router->add('GET', '/api/notifications/read_all', ['controller' => 'NotificationController', 'method' => 'readAll', 'middlewares' => [$auth]]);
$router->add('GET', '/api/notifications/clear_all', ['controller' => 'NotificationController', 'method' => 'clearAll', 'middlewares' => [$auth]]);

$router->add('GET', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/integrations', ['controller' => 'Admin\\IntegrationsController', 'method' => 'index', 'middlewares' => [$auth, $admin]]);
$router->add('POST', '/api/admin/test_email', ['controller' => 'Admin\\IntegrationsController', 'method' => 'testEmail', 'middlewares' => [$auth, $admin]]);

$router->add('GET', '/api/search', ['controller' => 'SearchController', 'method' => 'search', 'middlewares' => [$auth]]);

// Tutores (Clientes)
$router->add('GET', '/app/tutores', ['controller' => 'TutorController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/tutores/save', ['controller' => 'TutorController', 'method' => 'save', 'middlewares' => [$auth]]);
$router->add('POST', '/api/tutores/delete', ['controller' => 'TutorController', 'method' => 'delete', 'middlewares' => [$auth]]);
$router->add('GET', '/app/tutores/perfil/{id}', ['controller' => 'TutorController', 'method' => 'perfil', 'middlewares' => [$auth]]);
$router->add('GET', '/api/tutores/details', ['controller' => 'TutorController', 'method' => 'details', 'middlewares' => [$auth]]);
$router->add('POST', '/api/tutores/toggle-status', ['controller' => 'TutorController', 'method' => 'toggleStatus', 'middlewares' => [$auth]]);
$router->add('POST', '/api/tutores/upload-contract', ['controller' => 'TutorController', 'method' => 'uploadContract', 'middlewares' => [$auth]]);

// Pets
$router->add('GET', '/app/pets', ['controller' => 'PetController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('GET', '/app/pets/perfil/{id}', ['controller' => 'PetController', 'method' => 'perfil', 'middlewares' => [$auth]]);
$router->add('POST', '/api/pets/save', ['controller' => 'PetController', 'method' => 'save', 'middlewares' => [$auth]]);
$router->add('POST', '/api/pets/delete', ['controller' => 'PetController', 'method' => 'delete', 'middlewares' => [$auth]]);

// Consultas
$router->add('GET', '/app/consultas', ['controller' => 'ConsultaController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/consultas/save', ['controller' => 'ConsultaController', 'method' => 'save', 'middlewares' => [$auth]]);
$router->add('POST', '/api/consultas/delete', ['controller' => 'ConsultaController', 'method' => 'delete', 'middlewares' => [$auth]]);

// --- Financeiro ---
$router->add('GET', '/app/financeiro', ['controller' => 'FinanceiroController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('POST', '/api/financeiro/add', ['controller' => 'FinanceiroController', 'method' => 'addMovimentacao', 'middlewares' => [$auth]]);
$router->add('POST', '/api/financeiro/delete', ['controller' => 'FinanceiroController', 'method' => 'delete', 'middlewares' => [$auth]]);

// --- Portal do Tutor ---
$router->add('GET', '/app/tutor/dashboard', ['controller' => 'TutorDashboardController', 'method' => 'index', 'middlewares' => [$auth]]);
$router->add('GET', '/app/tutor/pet/{id}', ['controller' => 'TutorDashboardController', 'method' => 'petPerfil', 'middlewares' => [$auth]]);

// --- SaaS Slug Route (Keep this at the very end!) ---
$router->add('GET',  '/{slug}',       ['controller' => 'LoginController', 'method' => 'companyLogin']);
$router->add('GET',  '/{slug}/login', ['controller' => 'LoginController', 'method' => 'companyLogin']);
$router->add('POST', '/{slug}/login', ['controller' => 'LoginController', 'method' => 'attempt']);


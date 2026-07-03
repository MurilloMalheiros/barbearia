<?php

declare(strict_types=1);

use App\controllers\{
    HomeController,
    AuthController,
    DashboardController,
    GaleriaController,
    ServicoController,
    AgendamentoController,
    HorarioController,
    ConfigController,
};

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// ─── Serve uploads ───────────────────────────────────────────
if (preg_match('#^/uploads/(.+)$#', $uri, $m)) {
    (new GaleriaController())->serve($m[1]);
    return;
}

// ─── Rotas estáticas ─────────────────────────────────────────
$routes = [
    'GET' => [
        '/'             => [HomeController::class, 'index'],
        '/sitemap.xml'  => [HomeController::class, 'sitemap'],
        '/robots.txt'   => [HomeController::class, 'robots'],

        '/agendamento/horarios' => [AgendamentoController::class, 'horariosDisponiveis'],

        '/admin'                 => [DashboardController::class, 'index'],
        '/admin/'                => [DashboardController::class, 'index'],
        '/admin/login'           => [AuthController::class, 'loginForm'],
        '/admin/logout'          => [AuthController::class, 'logout'],
        '/admin/dashboard'       => [DashboardController::class, 'index'],

        '/admin/galeria'         => [GaleriaController::class, 'index'],
        '/admin/galeria/criar'   => [GaleriaController::class, 'create'],

        '/admin/servicos'        => [ServicoController::class, 'index'],
        '/admin/servicos/criar'  => [ServicoController::class, 'create'],

        '/admin/agendamentos'    => [AgendamentoController::class, 'index'],

        '/admin/horarios'        => [HorarioController::class, 'index'],

        '/admin/configuracoes'   => [ConfigController::class, 'index'],
    ],
    'POST' => [
        '/admin/login'               => [AuthController::class, 'loginPost'],
        '/agendamento'               => [AgendamentoController::class, 'store'],

        '/admin/galeria'             => [GaleriaController::class, 'store'],
        '/admin/servicos'            => [ServicoController::class, 'store'],
        '/admin/horarios'            => [HorarioController::class, 'update'],
        '/admin/configuracoes'       => [ConfigController::class, 'update'],
        '/admin/configuracoes/senha' => [ConfigController::class, 'updateSenha'],
    ],
];

// ─── Rotas dinâmicas ─────────────────────────────────────────
$dynamicRoutes = [
    'GET' => [
        '#^/admin/galeria/(\d+)/editar$#'  => [GaleriaController::class, 'edit'],
        '#^/admin/servicos/(\d+)/editar$#' => [ServicoController::class, 'edit'],
    ],
    'POST' => [
        '#^/admin/galeria/(\d+)$#'               => [GaleriaController::class, 'update'],
        '#^/admin/galeria/(\d+)/deletar$#'        => [GaleriaController::class, 'delete'],
        '#^/admin/servicos/(\d+)$#'              => [ServicoController::class, 'update'],
        '#^/admin/servicos/(\d+)/deletar$#'      => [ServicoController::class, 'delete'],
        '#^/admin/agendamentos/(\d+)/status$#'   => [AgendamentoController::class, 'updateStatus'],
        '#^/admin/agendamentos/(\d+)/deletar$#'  => [AgendamentoController::class, 'delete'],
    ],
];

// ─── Dispatch ────────────────────────────────────────────────
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if (isset($routes[$method][$uri])) {
    [$class, $action] = $routes[$method][$uri];
    (new $class())->$action();
    return;
}

foreach ($dynamicRoutes[$method] ?? [] as $pattern => [$class, $action]) {
    if (preg_match($pattern, $uri, $matches)) {
        array_shift($matches);
        $params = array_map('intval', $matches);
        (new $class())->$action(...$params);
        return;
    }
}

// 404
http_response_code(404);
require APP_PATH . '/views/errors/404.php';

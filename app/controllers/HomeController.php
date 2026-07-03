<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\{ServicoModel, ImagemModel, ConfiguracaoModel, VisitaModel, HorarioModel};

final class HomeController extends BaseController
{
    public function index(): void
    {
        if (!headers_sent()) {
            header('Cache-Control: public, max-age=120, stale-while-revalidate=300');
        }

        VisitaModel::registrar($this->clientIp(), $_SERVER['REQUEST_URI'] ?? '/');

        $config   = ConfiguracaoModel::all();
        $imagens  = ImagemModel::allAtivos(30);
        $servicos = ServicoModel::allAtivos();
        $diasAtivos = HorarioModel::diasAtivos();

        $this->renderWithLayout('public', 'public/home', compact(
            'config', 'imagens', 'servicos', 'diasAtivos'
        ));
    }

    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');
        $baseUrl = APP_URL;
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        echo '<url><loc>' . htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '/</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>';
        echo '</urlset>';
        exit;
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=UTF-8');
        echo "User-agent: *\nAllow: /\nDisallow: /admin/\nSitemap: " . APP_URL . "/sitemap.xml\n";
        exit;
    }
}

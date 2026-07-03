<?php
use App\models\{ConfiguracaoModel, Security};
if (!isset($content_view) || !is_string($content_view)) {
  throw new RuntimeException('View de conteúdo não definida para layout público.');
}
$s       = fn(string $k, string $d = '') => Security::e(ConfiguracaoModel::get($k, $d));
$baseUrl = APP_URL;

$schemaOrgData = [
    '@context'    => 'https://schema.org',
    '@type'       => 'HairSalon',
    'name'        => ConfiguracaoModel::get('empresa_nome') ?: 'Barbearia',
    'description' => ConfiguracaoModel::get('empresa_descricao') ?: '',
    'url'         => $baseUrl,
    'telephone'   => ConfiguracaoModel::get('empresa_whatsapp') ?: '',
    'address'     => [
        '@type'           => 'PostalAddress',
        'addressLocality' => ConfiguracaoModel::get('endereco') ?: '',
        'addressCountry'  => 'BR',
    ],
    'sameAs' => array_values(array_filter([
        ConfiguracaoModel::get('instagram') ?: null,
        ConfiguracaoModel::get('facebook')  ?: null,
    ])),
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <title><?= $s('seo_title', $s('empresa_nome') . ' - Barbearia') ?></title>
  <meta name="description" content="<?= $s('seo_description') ?>">
  <meta name="keywords"    content="<?= $s('seo_keywords') ?>">
  <link rel="canonical"    href="<?= Security::e($baseUrl) ?>/">

  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= Security::e($baseUrl) ?>/">
  <meta property="og:title"       content="<?= $s('seo_title') ?>">
  <meta property="og:description" content="<?= $s('seo_description') ?>">
  <meta property="og:image"       content="<?= $s('og_image') ? Security::e($baseUrl . '/uploads/' . ConfiguracaoModel::get('og_image')) : '' ?>">
  <meta property="og:locale"      content="pt_BR">
  <meta property="og:site_name"   content="<?= $s('empresa_nome') ?>">

  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= $s('seo_title') ?>">
  <meta name="twitter:description" content="<?= $s('seo_description') ?>">

  <link rel="icon" type="image/png" href="/assets/images/favicon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: { DEFAULT: '#0f0f0f', light: '#1a1a1a', dark: '#000000' },
            accent:  { DEFAULT: '#c9a84c', light: '#e6c97a', dark: '#a07c2e' },
            cream:   { DEFAULT: '#f9f5ec', dark: '#ede4d3' },
          },
          fontFamily: {
            serif: ['Playfair Display', 'Georgia', 'serif'],
            sans:  ['Inter', 'system-ui', 'sans-serif'],
          },
        }
      }
    }
  </script>

  <link rel="stylesheet" href="/assets/css/style.css">

  <script type="application/ld+json">
<?= json_encode($schemaOrgData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
  </script>

  <?php if ($ga = ConfiguracaoModel::get('google_analytics')): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= Security::e($ga) ?>"></script>
  <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= Security::e($ga) ?>');</script>
  <?php endif; ?>
</head>
<body class="font-sans bg-cream text-primary antialiased">

<?php require $content_view; ?>

<!-- WhatsApp Flutuante -->
<?php $wa = ConfiguracaoModel::get('empresa_whatsapp'); if ($wa): ?>
<a href="https://wa.me/<?= Security::e(preg_replace('/\D/', '', $wa)) ?>?text=Olá!%20Gostaria%20de%20agendar%20um%20horário."
   target="_blank" rel="noopener noreferrer"
   class="fixed bottom-6 right-6 z-50 bg-green-500 hover:bg-green-600 text-white rounded-full w-16 h-16 flex items-center justify-center shadow-2xl transition-all duration-300 hover:scale-110"
   aria-label="Falar no WhatsApp">
  <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
  </svg>
</a>
<?php endif; ?>

<script src="/assets/js/app.js"></script>
</body>
</html>

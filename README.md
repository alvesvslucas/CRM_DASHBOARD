# CRM Dashboard

Aplicação PHP para registrar e acompanhar métricas de vendas por data e setor, com autenticação e exportação de planilhas.

## Estrutura

| Caminho | Finalidade |
| --- | --- |
| `auth/login.php` | Entrada de autenticação |
| `dashboard/index.php` | Painel de métricas, protegido por sessão |
| `dashboard/estatisticas.php` e `dashboard/tv.php` | Visualizações do painel |
| `dashboard/exportar.php` | Geração de planilha com PhpSpreadsheet |
| `api/` | Endpoints de apoio |
| `composer.json` e `composer.lock` | Dependências PHP |
| `config/database.php` | Conexão PDO requerida pela aplicação |

## Requisitos

O lockfile atual inclui PhpSpreadsheet 4.1.0 e ZipStream 3.1.2. A dependência ZipStream exige **PHP `^8.2` em 64 bits**. As extensões exigidas incluem `ctype`, `dom`, `fileinfo`, `gd`, `iconv`, `libxml`, `mbstring`, `simplexml`, `xml`, `xmlreader`, `xmlwriter`, `zip` e `zlib`.

Também são necessários Composer, sessões PHP e o banco/driver PDO compatível com a configuração local.

## Preparar o ambiente

```sh
git clone https://github.com/alvesvslucas/CRM_DASHBOARD.git
cd CRM_DASHBOARD
composer install
composer check-platform-reqs
```

Use o lockfile para instalar as dependências. O autoload esperado pela exportação é `vendor/autoload.php`.

Prepare um banco de teste e configure `config/database.php` sem publicar credenciais. O login consulta `usuarios`, e o painel consulta `metricas_vendas`; ainda é necessário fornecer e validar o esquema completo e uma conta de teste.

## Abrir localmente

Com o banco e as dependências preparados:

```sh
php -S 127.0.0.1:8080 -t .
```

Acesse `http://127.0.0.1:8080/auth/login.php`. Após autenticação, o sistema encaminha para `dashboard/index.php`.

## Estado

Instalação Composer, banco e fluxos de cadastro/exportação não foram executados nesta revisão documental. O endpoint de exportação consultado precisa de revisão da autorização antes de uma demonstração com acesso externo ou dados reais.

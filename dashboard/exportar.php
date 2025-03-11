<?php
require '../vendor/autoload.php'; // O autoload deve ser chamado primeiro

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include '../config/database.php';

// Verifica se o formato de exportação foi selecionado
if (!isset($_GET['formato']) || !in_array($_GET['formato'], ['csv', 'excel'])) {
    die("Formato inválido!");
}

$formato = $_GET['formato'];

// Busca os dados do banco
$stmt = $pdo->query("SELECT data, setor, meta_total, meta_diaria, total_vendas, percentual_vendas, deficit FROM metricas_vendas ORDER BY data ASC");
$metricas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($formato === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Definição do cabeçalho
    $sheet->setCellValue('A1', 'Data');
    $sheet->setCellValue('B1', 'Setor');
    $sheet->setCellValue('C1', 'Meta Total');
    $sheet->setCellValue('D1', 'Meta Diária');
    $sheet->setCellValue('E1', 'Total Vendas');
    $sheet->setCellValue('F1', '% Vendas');
    $sheet->setCellValue('G1', 'Déficit');

    // Adiciona os dados à planilha
    $linha = 2;
    foreach ($metricas as $row) {
        $sheet->setCellValue("A{$linha}", $row['data']);
        $sheet->setCellValue("B{$linha}", ucfirst($row['setor']));
        $sheet->setCellValue("C{$linha}", $row['meta_total']);
        $sheet->setCellValue("D{$linha}", $row['meta_diaria']);
        $sheet->setCellValue("E{$linha}", $row['total_vendas']);
        $sheet->setCellValue("F{$linha}", $row['percentual_vendas']);
        $sheet->setCellValue("G{$linha}", $row['deficit']);
        $linha++;
    }

    // Gera e envia o arquivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="metricas.xlsx"');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
?>
